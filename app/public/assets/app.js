// ObservaStage : interface web (consomme l'API JSON de /api).

const $ = (sel, racine = document) => racine.querySelector(sel);
const $$ = (sel, racine = document) => [...racine.querySelectorAll(sel)];
const filtres = $('#filtres');
const etat = { onglet: 'tableau', page: 1, analyse: null };

const nombre = (v, dec = 0) =>
  v === null || v === undefined || v === '' ? '–'
    : Number(v).toLocaleString('fr-FR', { minimumFractionDigits: dec, maximumFractionDigits: dec });
const pct = (part, total) => (total ? nombre((100 * part) / total, 1) + ' %' : '–');
const echapper = (t) => String(t ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

async function api(chemin, options) {
  const reponse = await fetch(`api/${chemin}`, options);
  const donnees = await reponse.json();
  if (!reponse.ok) throw new Error(donnees.erreur || 'Erreur serveur');
  return donnees;
}

function message(texte, erreur = false) {
  const zone = $('#message');
  zone.textContent = texte;
  zone.className = 'message' + (erreur ? ' erreur' : '');
  zone.hidden = false;
  clearTimeout(message.minuteur);
  message.minuteur = setTimeout(() => (zone.hidden = true), erreur ? 7000 : 3500);
}

function criteres() {
  const params = new URLSearchParams();
  for (const [cle, valeur] of new FormData(filtres)) if (valeur) params.set(cle, valeur);
  if (etat.onglet !== 'recherche') { params.delete('texte'); params.delete('type_experience'); }
  return params;
}

// ------------------------------------------------------------ Onglets

function ouvrir(onglet) {
  etat.onglet = onglet;
  $$('.onglets button').forEach((b) => b.setAttribute('aria-selected', b.dataset.onglet === onglet));
  $$('.vue').forEach((v) => (v.hidden = v.id !== onglet));
  document.body.className = 'mode-' + onglet;
  rafraichir();
}

function rafraichir() {
  if (etat.onglet === 'tableau') chargerIndicateurs();
  if (etat.onglet === 'recherche') chargerStages();
  if (etat.onglet === 'import') chargerLots();
}

async function chargerOptions() {
  const options = await api('options.php');
  for (const [nom, valeurs] of Object.entries(options)) {
    const select = filtres.elements[nom];
    if (!select) continue;
    const premiere = select.options[0];
    select.replaceChildren(premiere, ...valeurs.map((v) => new Option(v, v)));
  }
}

// ------------------------------------------------------------ Tableau de bord

function barres(lignes, { libelle = 'libelle', valeur = 'stages', total = null } = {}) {
  if (!lignes.length) return '<p class="vide">Aucune donnée pour ces critères.</p>';
  const max = Math.max(...lignes.map((l) => Number(l[valeur])));
  return '<div class="barres">' + lignes.map((l) => {
    const v = Number(l[valeur]);
    const info = `${l[libelle]} : ${nombre(v)} stage${v > 1 ? 's' : ''}` + (total ? ` (${pct(v, total)})` : '');
    return `<span class="nom" title="${echapper(l[libelle])}">${echapper(l[libelle])}</span>
            <span class="piste" title="${echapper(info)}"><span class="barre" style="width:${(100 * v) / max}%"></span></span>
            <span class="nb">${nombre(v)}</span>`;
  }).join('') + '</div>';
}

const REGLES = {
  administrative: 'conventions validées administrativement',
  administrative_ou_pedagogique: 'conventions validées administrativement ou pédagogiquement',
  toutes: 'toutes les conventions importées',
};

async function chargerIndicateurs() {
  const d = await api('indicateurs.php?' + criteres());
  const g = d.global;
  const n = Number(g.stages);
  $('#regle').textContent = `Indicateurs calculés sur les ${REGLES[d.regle_validation] ?? d.regle_validation} `
    + `(${nombre(n)} sur ${nombre(d.importees)} importées). Heures : ${nombre(g.exclus_heures)} convention(s) exclue(s) pour valeurs aberrantes.`;

  const tuiles = [
    ['Stages', nombre(n), d.par_annee.length > 1 ? `${d.par_annee.length} années` : (d.par_annee[0]?.libelle ?? '')],
    ['Durée moyenne', `${nombre(g.semaines, 1)} sem.`, `${nombre(g.jours, 0)} jours · ${nombre(g.heures, 0)} heures`],
    ['À l\'étranger', pct(g.etranger, n), `${nombre(g.etranger)} stages`],
    ['Hauts-de-France', pct(g.hauts_de_france, n), `${nombre(g.hauts_de_france)} stages · ${pct(g.hauts_de_france, g.france)} des stages en France`],
    ['Stages « recherche »', nombre(g.recherche), 'mot-clé dans le sujet'],
  ];
  $('#tuiles').innerHTML = tuiles.map(([l, v, det]) =>
    `<div class="tuile"><div class="libelle">${l}</div><div class="valeur">${v}</div><div class="detail">${echapper(det)}</div></div>`).join('');

  const maxNiveau = Math.max(...d.par_niveau.map((l) => Number(l.stages)), 1);
  $('#par-niveau').innerHTML = d.par_niveau.length ? `
    <div class="table-defilante"><table>
      <thead><tr><th>Niveau</th><th>Stages</th><th class="num">Jours</th><th class="num">Semaines</th>
      <th class="num">Heures</th><th class="num">Étranger</th><th class="num">Hauts-de-France</th></tr></thead>
      <tbody>${d.par_niveau.map((l) => `<tr>
        <td>${echapper(l.niveau)}</td>
        <td title="${nombre(l.stages)} stages"><span class="mini-barre" style="width:${(120 * l.stages) / maxNiveau}px"></span>${nombre(l.stages)}</td>
        <td class="num">${nombre(l.jours)}</td><td class="num">${nombre(l.semaines, 1)}</td><td class="num">${nombre(l.heures)}</td>
        <td class="num">${nombre(l.pct_etranger, 1)} %</td><td class="num">${nombre(l.pct_hdf, 1)} %</td></tr>`).join('')}
      </tbody></table></div>` : '<p class="vide">Aucune donnée pour ces critères.</p>';

  $('#top-pays').innerHTML = barres(d.top_pays, { total: g.etranger });
  $('#thematiques').innerHTML = barres(d.thematiques.map((t) => ({ ...t, libelle: t.libelle.toLowerCase().replace(/^./, (c) => c.toUpperCase()) })), { total: n });
  $('#experience').innerHTML = barres(d.type_experience);
}

// ------------------------------------------------------------ Recherche

async function chargerStages() {
  const params = criteres();
  params.set('page', etat.page);
  const d = await api('stages.php?' + params);
  $('#nb-resultats').textContent = `${nombre(d.total)} stage${d.total > 1 ? 's' : ''}`;
  const cles = Object.keys(d.colonnes);
  $('#resultats').innerHTML = `<thead><tr>${cles.map((c) => `<th${c === 'duree_semaines' ? ' class="num"' : ''}>${d.colonnes[c]}</th>`).join('')}</tr></thead>
    <tbody>${d.lignes.map((l) => `<tr>${cles.map((c) => `<td${c === 'duree_semaines' ? ' class="num"' : ''}>${echapper(c === 'duree_semaines' ? nombre(l[c], 1) : l[c])}</td>`).join('')}</tr>`).join('')
      || `<tr><td colspan="${cles.length}" class="vide">Aucun stage ne correspond à ces critères.</td></tr>`}</tbody>`;

  const pages = Math.max(1, Math.ceil(d.total / d.par_page));
  $('#pagination').innerHTML = pages > 1 ? `
    <button data-page="${etat.page - 1}" ${etat.page <= 1 ? 'disabled' : ''}>‹ Précédent</button>
    <span>Page ${etat.page} / ${pages}</span>
    <button data-page="${etat.page + 1}" ${etat.page >= pages ? 'disabled' : ''}>Suivant ›</button>` : '';
}

// ------------------------------------------------------------ Import

const CONFIANCE = { profil: 'profil connu', sure: 'reconnue', a_verifier: 'à vérifier', connue: 'ignorée', ignoree: 'nouvelle' };

$('#form-fichier').addEventListener('submit', async (e) => {
  e.preventDefault();
  const bouton = $('button', e.target);
  bouton.disabled = true;
  bouton.textContent = 'Analyse en cours…';
  try {
    etat.analyse = await api('import_analyser.php', { method: 'POST', body: new FormData(e.target) });
    afficherMapping();
  } catch (err) {
    message(err.message, true);
  } finally {
    bouton.disabled = false;
    bouton.textContent = 'Analyser les colonnes';
  }
});

function afficherMapping() {
  const a = etat.analyse;
  const compte = (c) => a.colonnes.filter((col) => col.confiance === c).length;
  $('#resume-mapping').textContent = a.profil
    ? `${a.fichier} · ${nombre(a.lignes)} lignes · format reconnu : profil « ${a.profil.nom} » appliqué`
    : `${a.fichier} · ${nombre(a.lignes)} lignes · ${a.colonnes.length} colonnes : `
      + `${compte('sure')} reconnues, ${compte('a_verifier')} à vérifier, ${compte('ignoree')} nouvelles`;
  $('#nom-profil').value = a.profil?.nom ?? '';

  const options = (choisi) => '<option value="">— Ignorer (non stockée) —</option>'
    + a.champs.map((ch) => `<option value="${ch.code}" ${ch.code === choisi ? 'selected' : ''}>${echapper(ch.libelle)}${ch.obligatoire ? ' *' : ''}</option>`).join('');

  // Les colonnes à traiter d'abord : à vérifier, puis nouvelles.
  const ordre = { a_verifier: 0, ignoree: 1, sure: 2, profil: 2, connue: 3 };
  const colonnes = [...a.colonnes].sort((x, y) => ordre[x.confiance] - ordre[y.confiance] || x.index - y.index);

  $('#mapping').innerHTML = `<thead><tr><th>Colonne du fichier</th><th>Exemples de valeurs</th><th>Champ ObservaStage</th><th>Statut</th></tr></thead>
    <tbody>${colonnes.map((c) => `<tr class="${c.confiance}">
      <td><strong>${echapper(c.source)}</strong></td>
      <td>${c.masquee ? '<span class="masque">donnée personnelle : non affichée</span>'
        : `<span class="exemples">${c.exemples.map(echapper).join(' · ') || '<em>colonne vide</em>'}</span>`}</td>
      <td><select data-index="${c.index}">${options(c.champ)}</select></td>
      <td><span class="badge ${c.confiance}">${CONFIANCE[c.confiance]}</span></td></tr>`).join('')}</tbody>`;

  $('#etape-mapping').hidden = false;
  $('#etape-bilan').hidden = true;
  $('#etape-mapping').scrollIntoView({ behavior: 'smooth' });
}

$('#lancer-import').addEventListener('click', async (e) => {
  const a = etat.analyse;
  const mapping = new Array(a.colonnes.length).fill(null);
  $$('#mapping select').forEach((s) => (mapping[s.dataset.index] = s.value || null));
  const choisis = mapping.filter(Boolean);
  const doublon = choisis.find((c, i) => choisis.indexOf(c) !== i);
  if (doublon) return message(`Le champ « ${a.champs.find((c) => c.code === doublon).libelle} » est associé à plusieurs colonnes.`, true);
  const manquants = a.champs.filter((c) => c.obligatoire && !choisis.includes(c.code)).map((c) => c.libelle);
  if (manquants.length && !confirm(`Champs obligatoires non associés : ${manquants.join(', ')}.\nImporter quand même ?`)) return;

  e.target.disabled = true;
  e.target.textContent = 'Import en cours…';
  try {
    const bilan = await api('import_executer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ jeton: a.jeton, fichier: a.fichier, profil: $('#nom-profil').value, mapping }),
    });
    afficherBilan(bilan);
    $('#etape-mapping').hidden = true;
    await chargerOptions();
    chargerLots();
  } catch (err) {
    message(err.message, true);
  } finally {
    e.target.disabled = false;
    e.target.textContent = 'Importer';
  }
});

function tableauAnomalies(anomalies) {
  if (!anomalies.length) return '<p>Aucune anomalie détectée.</p>';
  return `<div class="table-defilante"><table><thead><tr><th>Gravité</th><th>Champ</th><th>Anomalie</th><th class="num">Nombre</th><th>Lignes du fichier</th></tr></thead>
    <tbody>${anomalies.map((x) => `<tr><td class="gravite-${x.gravite}">${x.gravite}</td><td>${echapper(x.champ)}</td>
      <td>${echapper(x.message)}</td><td class="num">${nombre(x.nombre)}</td>
      <td>${echapper(x.exemples_lignes)}${x.nombre > 8 ? '…' : ''}</td></tr>`).join('')}</tbody></table></div>`;
}

function afficherBilan(b) {
  $('#bilan').innerHTML = `<div class="bilan-chiffres">
      <div><strong>${nombre(b.lignes)}</strong>lignes lues</div>
      <div><strong>${nombre(b.crees)}</strong>conventions créées</div>
      <div><strong>${nombre(b.mis_a_jour)}</strong>mises à jour (déjà connues)</div>
      <div><strong>${nombre(b.rejetes)}</strong>lignes rejetées</div>
    </div>
    <h2>Rapport d'anomalies</h2>
    <p class="aide">Les valeurs aberrantes sont signalées et exclues des calculs concernés, jamais corrigées.</p>
    ${tableauAnomalies(b.anomalies)}`;
  $('#etape-bilan').hidden = false;
  message('Import terminé.');
}

async function chargerLots() {
  const lots = await api('lots.php');
  $('#lots').innerHTML = `<thead><tr><th>#</th><th>Date</th><th>Fichier</th><th>Profil</th><th class="num">Lignes</th>
    <th class="num">Créées</th><th class="num">Mises à jour</th><th class="num">Rejetées</th><th class="num">Anomalies</th></tr></thead>
    <tbody>${lots.map((l) => `<tr><td>${l.id}</td><td>${l.date}</td><td>${echapper(l.fichier)}</td><td>${echapper(l.profil)}</td>
      <td class="num">${nombre(l.nb_lignes)}</td><td class="num">${nombre(l.nb_crees)}</td><td class="num">${nombre(l.nb_mis_a_jour)}</td>
      <td class="num">${nombre(l.nb_rejetes)}</td><td class="num">${nombre(l.nb_anomalies)}</td></tr>`).join('')
      || '<tr><td colspan="9" class="vide">Aucun import pour l\'instant.</td></tr>'}</tbody>`;
}

// ------------------------------------------------------------ Événements

$$('.onglets button').forEach((b) => b.addEventListener('click', () => ouvrir(b.dataset.onglet)));
filtres.addEventListener('change', () => { etat.page = 1; rafraichir(); });
filtres.addEventListener('input', (e) => {
  if (e.target.name !== 'texte') return;
  clearTimeout(etat.saisie);
  etat.saisie = setTimeout(() => { etat.page = 1; rafraichir(); }, 300);
});
filtres.addEventListener('reset', () => setTimeout(() => { etat.page = 1; rafraichir(); }));
$('#pagination').addEventListener('click', (e) => {
  if (!e.target.dataset.page) return;
  etat.page = Number(e.target.dataset.page);
  chargerStages();
});
document.addEventListener('click', (e) => {
  const b = e.target.closest('[data-export]');
  if (!b) return;
  const params = criteres();
  params.set('objet', b.dataset.export);
  params.set('format', b.dataset.format);
  window.location = 'api/export.php?' + params;
});

chargerOptions().then(() => ouvrir('tableau')).catch((err) => message(err.message, true));
