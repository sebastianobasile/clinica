<?php
session_start();
$pass_view = "cambiami";   // ← cambia questa password per la vista pubblica

if (isset($_GET['logout_view'])) { unset($_SESSION['view_auth']); }
if (isset($_POST['vp']) && $_POST['vp'] === $pass_view) $_SESSION['view_auth'] = true;

if (!isset($_SESSION['view_auth'])): ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso – Cartella Clinica</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:#1a3a5c; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Source Sans 3',Arial,sans-serif; }
        .box { background:#fff; border-radius:10px; padding:38px 32px; width:320px; box-shadow:0 10px 40px rgba(0,0,0,.35); text-align:center; }
        .box h2 { font-family:'Merriweather',serif; color:#1a3a5c; font-size:1.1rem; margin-bottom:6px; }
        .box p  { font-size:.8rem; color:#888; margin-bottom:22px; }
        .box input { width:100%; border:1px solid #ccc; border-radius:6px; padding:9px 12px; font-size:1rem; text-align:center; letter-spacing:3px; margin-bottom:14px; outline:none; }
        .box input:focus { border-color:#1a3a5c; box-shadow:0 0 0 3px rgba(26,58,92,.12); }
        .box button { width:100%; background:#1a3a5c; color:#fff; border:none; border-radius:6px; padding:10px; font-size:.95rem; cursor:pointer; font-family:'Merriweather',serif; }
        .box button:hover { background:#16324f; }
        .err { color:#c0392b; font-size:.8rem; margin-top:10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>📋 Cartella Clinica</h2>
        <p>Accesso riservato — inserisci la password</p>
        <form method="POST">
            <input type="password" name="vp" placeholder="••••••••" autofocus>
            <button type="submit">Accedi</button>
        </form>
        <?php if (isset($_POST['vp'])): ?><div class="err">Password errata. Riprova.</div><?php endif; ?>
    </div>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anamnesi: Mario Rossi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blu: #1a3a5c;
            --rosso: #c0392b;
            --verde: #2c6e3f;
            --bordo: #c8d0d8;
            --bg-label: #f4f6f8;
            --pending-bg: #fff8e1;
            --pending-bd: #f0c040;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ══ SCHERMO ══════════════════════════════════════════════ */
        body { background: #d8dde4; font-family: 'Source Sans 3', Arial, sans-serif; font-size: 13px; color: #2c3e50; }

        .toolbar {
            background: var(--blu); padding: 9px 20px;
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            position: sticky; top: 0; z-index: 100;
        }
        .toolbar .brand { color: #fff; font-family: 'Merriweather', serif; font-size: .9rem; }
        .toolbar .controls { display: flex; gap: 8px; align-items: center; }
        .toolbar input[type="search"] { border: none; border-radius: 4px; padding: 4px 10px; font-size: .82rem; outline: none; width: 190px; }
        .btn-t {
            border: 1px solid rgba(255,255,255,.35); border-radius: 4px;
            background: transparent; color: #fff; padding: 4px 13px; font-size: .78rem;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-t:hover { background: rgba(255,255,255,.15); color: #fff; }
        .btn-t.primary { background: var(--rosso); border-color: var(--rosso); }
        .btn-t.primary:hover { background: #a93226; }

        .page-wrap { padding: 20px; }

        /* ══ DOCUMENTO ══════════════════════════════════════════ */
        .document-page {
            background: #fff; margin: 0 auto; max-width: 780px;
            padding: 28px 32px; box-shadow: 0 3px 20px rgba(0,0,0,.16); border-radius: 3px;
        }

        .doc-title {
            text-align: center; font-family: 'Merriweather', serif; font-size: 1.4rem; font-weight: 700;
            color: var(--rosso); text-transform: uppercase; letter-spacing: 2px;
            border-bottom: 3px double var(--rosso); padding-bottom: 8px; margin-bottom: 16px;
        }

        /* ══ PROFILO ═══════════════════════════════════════════ */
        .t-profilo { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12.5px; }
        .t-profilo td { border: 1px solid var(--bordo); padding: 5px 9px; vertical-align: top; }
        .t-profilo .lbl { background: var(--bg-label); font-weight: 700; width: 36%; color: var(--blu); }

        /* ══ FARMACI ════════════════════════════════════════════ */
        .sect-h { padding: 6px 10px; font-family: 'Merriweather', serif; font-weight: 700; font-size: .7rem; letter-spacing: 1.2px; text-transform: uppercase; color: #fff; }
        .sect-verde { background: var(--verde); }

        .t-farmaci { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
        .t-farmaci th { background: #e8f5ee; color: var(--verde); padding: 5px 8px; border: 1px solid #b2d8be; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .t-farmaci td { border: 1px solid var(--bordo); padding: 5px 8px; vertical-align: top; word-break: break-word; }
        .t-farmaci tr:nth-child(even) td { background: #f5fdf8; }

        /* ══ CATEGORIE ══════════════════════════════════════════ */
        .cat-wrap { margin-bottom: 14px; border: 1px solid var(--bordo); border-radius: 3px; overflow: hidden; }
        .cat-head {
            background: var(--blu); color: #fff; font-family: 'Merriweather', serif; font-weight: 700;
            font-size: .7rem; letter-spacing: 1.5px; text-transform: uppercase; padding: 6px 10px;
            display: flex; justify-content: space-between; align-items: center;
        }
        /* Bottone Drive nella testata categoria */
        .drive-btn {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35);
            color: #fff; border-radius: 4px; padding: 2px 8px;
            font-size: .68rem; font-weight: 600; letter-spacing: .3px;
            text-decoration: none; transition: background .15s;
            white-space: nowrap;
        }
        .drive-btn:hover { background: rgba(255,255,255,.32); color: #fff; }

        .t-esami { width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed; }
        .t-esami td { border: 1px solid var(--bordo); padding: 5px 8px; vertical-align: top; word-break: break-word; }
        .t-esami .td-d { width: 16%; font-weight: 700; color: var(--blu); text-align: center; white-space: nowrap; }
        .t-esami .td-s { width: 77%; line-height: 1.45; }
        .t-esami .td-l { width: 7%; text-align: center; }
        .t-esami tr:nth-child(even) td { background: #fafbfc; }
        .t-esami tr.pend td { background: var(--pending-bg) !important; }

        .badge-pend { display: inline-block; background: var(--pending-bd); color: #5a3900; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 8px; margin-right: 3px; }
        a.ref { font-size: 10px; color: var(--blu); border: 1px solid var(--blu); border-radius: 3px; padding: 1px 4px; text-decoration: none; }
        a.ref:hover { background: var(--blu); color: #fff; }

        .footer-doc { margin-top: 18px; font-size: 10px; color: #aaa; text-align: right; border-top: 1px solid #eee; padding-top: 5px; }
        mark.hl { background: #fff176; border-radius: 2px; padding: 0 1px; }

        /* ══ STAMPA ═════════════════════════════════════════════
           window.print() — nessun problema di taglio.
        ══════════════════════════════════════════════════════ */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: white !important; font-size: 11px !important; }
            .toolbar, .no-print { display: none !important; }
            .page-wrap { padding: 0 !important; }
            .document-page { box-shadow: none !important; max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; border-radius: 0 !important; }
            .cat-wrap           { page-break-inside: avoid; break-inside: avoid; }
            .t-esami tr         { page-break-inside: avoid; break-inside: avoid; }
            .t-farmaci, .t-profilo { page-break-inside: avoid; break-inside: avoid; }
            .cat-head           { background: var(--blu) !important; color: #fff !important; }
            .sect-verde         { background: var(--verde) !important; color: #fff !important; }
            .t-farmaci th       { background: #e8f5ee !important; color: var(--verde) !important; }
            .t-profilo .lbl     { background: var(--bg-label) !important; }
            .t-esami tr.pend td { background: #fff8e1 !important; }
            .badge-pend, a.ref, .drive-btn { display: none !important; }
        }
        @page { size: A4 portrait; margin: 12mm 12mm 12mm 12mm; }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <div class="brand">📋 Cartella Clinica</div>
    <div class="controls">
        <input type="search" id="cerca" placeholder="🔍 Cerca visita…" oninput="filtra(this.value)">
        <button class="btn-t primary" onclick="window.print()">🖨 Stampa / PDF</button>
        <a href="admin.php" class="btn-t">⚙ Admin</a>
        <a href="?logout_view=1" class="btn-t" style="border-color:rgba(255,100,100,.5);color:rgba(255,180,180,.9)" title="Esci dalla vista pubblica">⎋ Esci</a>
    </div>
</div>

<div class="page-wrap">
<div class="document-page" id="doc">

    <div class="doc-title">Anamnesi Medica</div>

    <!-- PROFILO -->
    <table class="t-profilo">
        <tr><td class="lbl">Paziente</td><td id="v-dati"></td></tr>
        <tr id="row-fisico" style="display:none"><td class="lbl">Dati Fisici</td><td id="v-fisico"></td></tr>
        <tr><td class="lbl">Gruppo Sanguigno</td><td id="v-gruppo"></td></tr>
        <tr id="row-mmg" style="display:none"><td class="lbl">Medico di Base</td><td id="v-mmg"></td></tr>
        <tr id="row-em" style="display:none"><td class="lbl">Contatto Emergenza</td><td id="v-em"></td></tr>
        <tr id="row-pat" style="display:none"><td class="lbl">Patologie Croniche</td><td id="v-pat"></td></tr>
        <tr><td class="lbl">Anamnesi Remota</td><td id="v-remota"></td></tr>
        <tr><td class="lbl">Allergie a Farmaci</td><td id="v-farmaci-all"></td></tr>
        <tr><td class="lbl">Altre Allergie</td><td id="v-altre"></td></tr>
        <tr id="row-note" style="display:none"><td class="lbl">Note Generali</td><td id="v-note"></td></tr>
    </table>

    <!-- FARMACI -->
    <div id="farmaci-wrap" style="margin-bottom:16px; display:none;">
        <div class="sect-h sect-verde">💊 Terapie in Corso</div>
        <table class="t-farmaci">
            <thead>
                <tr>
                    <th style="width:24%">Farmaco</th>
                    <th style="width:18%">Dosaggio</th>
                    <th style="width:22%">Frequenza</th>
                    <th style="width:14%">Dal</th>
                    <th style="width:22%">Note</th>
                </tr>
            </thead>
            <tbody id="farmaci-body"></tbody>
        </table>
    </div>

    <!-- CATEGORIE -->
    <div id="container-cat"></div>

    <div class="footer-doc">
        Documento generato il <span id="v-oggi"></span> — Uso personale riservato
    </div>

</div>
</div>

<script>
let db = {};

function fmtD(d) {
    if (!d) return '—';
    const p = d.split('-');
    return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d;
}

function setIf(id, val, row) {
    if (val) {
        document.getElementById(id).textContent = val;
        if (row) document.getElementById(row).style.display = '';
    }
}

async function carica() {
    const r = await fetch('anamnesi.json?v=' + Date.now());
    db = await r.json();
    const p = db.profilo || {};

    // Profilo base
    document.getElementById('v-dati').innerHTML =
        `<strong>${p.nome || ''}</strong>` +
        (p.nascita    ? ` &nbsp;–&nbsp; Nato: ${p.nascita}` : '') +
        (p.residenza  ? `<br>Residenza: ${p.residenza}` : '') +
        (p.cf         ? `<br>C.F.: ${p.cf}` : '') +
        (p.telefono   ? `<br>Tel: ${p.telefono}` : '');

    document.getElementById('v-gruppo').textContent       = p.gruppo_sanguigno || '—';
    document.getElementById('v-remota').textContent       = p.anamnesi_remota  || '—';
    document.getElementById('v-farmaci-all').textContent  = p.allergie_farmaci || '—';
    document.getElementById('v-altre').textContent        = p.altre_allergie   || '—';

    // Dati fisici (peso + altezza) — riga visibile solo se almeno uno compilato
    if (p.peso_kg || p.altezza_cm) {
        const parti = [];
        if (p.peso_kg)    parti.push(`Peso: <strong>${p.peso_kg} kg</strong>`);
        if (p.altezza_cm) parti.push(`Altezza: <strong>${p.altezza_cm} cm</strong>`);
        document.getElementById('v-fisico').innerHTML = parti.join(' &nbsp;|&nbsp; ');
        document.getElementById('row-fisico').style.display = '';
    }

    // Medico di base
    if (p.medico_base) {
        document.getElementById('v-mmg').textContent = p.medico_base + (p.medico_tel ? ' — ' + p.medico_tel : '');
        document.getElementById('row-mmg').style.display = '';
    }

    // Contatto emergenza
    if (p.contatto_emergenza || p.contatto_em_tel) {
        document.getElementById('v-em').textContent =
            (p.contatto_emergenza || '') + (p.contatto_em_tel ? ' — Tel: ' + p.contatto_em_tel : '');
        document.getElementById('row-em').style.display = '';
    }

    // Campi opzionali semplici
    setIf('v-pat',  p.patologie_croniche, 'row-pat');
    setIf('v-note', p.note_generali,      'row-note');

    document.getElementById('v-oggi').textContent = new Date().toLocaleDateString('it-IT', {
        day: '2-digit', month: 'long', year: 'numeric'
    });

    // Farmaci
    if (db.farmaci && db.farmaci.length) {
        document.getElementById('farmaci-wrap').style.display = '';
        document.getElementById('farmaci-body').innerHTML = db.farmaci.map(f => `
            <tr>
                <td><strong>${f.nome || ''}</strong></td>
                <td>${f.dosaggio || '—'}</td>
                <td>${f.frequenza || '—'}</td>
                <td>${f.dal ? fmtD(f.dal) : '—'}</td>
                <td style="font-size:11px;color:#555">${f.note || ''}</td>
            </tr>`).join('');
    }

    render('');
}

function render(q) {
    const cnt   = document.getElementById('container-cat');
    cnt.innerHTML = '';
    const cats  = db.ordine_categorie || [...new Set((db.esami || []).map(e => e.categoria))];
    const drive = db.cartelle_drive   || {};
    const ql    = q.toLowerCase().trim();

    cats.forEach(cat => {
        let rows = (db.esami || [])
            .filter(e => e.categoria === cat)
            .sort((a, b) => b.data.localeCompare(a.data));

        if (ql) {
            rows = rows.filter(e =>
                (e.descrizione || '').toLowerCase().includes(ql) ||
                (e.data || '').includes(ql) ||
                cat.toLowerCase().includes(ql)
            );
        }
        if (!rows.length) return;

        const righe = rows.map(i => {
            const pend  = i.completato === false;
            const raw   = i.descrizione || '';
            const esc   = ql.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const desc  = ql ? raw.replace(new RegExp(`(${esc})`, 'gi'), '<mark class="hl">$1</mark>') : raw;
            const badge = pend ? '<span class="badge-pend no-print">⏳</span>' : '';
            const link  = i.link ? `<a href="${i.link}" target="_blank" class="ref no-print">Link</a>` : '';
            return `<tr class="${pend ? 'pend' : ''}">
                <td class="td-d">${fmtD(i.data)}</td>
                <td class="td-s">${badge}${desc}</td>
                <td class="td-l">${link}</td>
            </tr>`;
        }).join('');

        // Bottone cartella Google Drive (se configurato)
        const driveUrl = drive[cat] || '';
        const driveBtn = driveUrl
            ? `<a href="${driveUrl}" target="_blank" class="drive-btn no-print">📁 Cartella Drive</a>`
            : '';

        cnt.innerHTML += `
            <div class="cat-wrap">
                <div class="cat-head">${cat}${driveBtn}</div>
                <table class="t-esami"><tbody>${righe}</tbody></table>
            </div>`;
    });
}

function filtra(v) { render(v); }
carica();
</script>
</body>
</html>