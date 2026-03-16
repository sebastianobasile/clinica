<?php
session_start();
$pass = "Cambiami_Admin";   // ← cambia questa password admin
$file = 'anamnesi.json';

/* ══════════════════════════════════════════════════════════════════
   AJAX ENDPOINTS
══════════════════════════════════════════════════════════════════ */

// Salva ordine + link Drive + rinomina categorie
if (isset($_GET['action']) && $_GET['action'] == 'save_cat') {
    if (!isset($_SESSION['auth'])) { http_response_code(403); exit; }
    $d  = json_decode(file_get_contents('php://input'), true);
    $db = json_decode(file_get_contents($file), true);
    // Rinomina: $d['rename'] = [ ['old'=>'TIROIDE','new'=>'TIROIDE NODULI'], ... ]
    foreach (($d['rename'] ?? []) as $r) {
        $old = $r['old'];
        $new = strtoupper(trim($r['new']));
        if ($old === $new || $new === '') continue;
        foreach ($db['esami'] as &$e) { if ($e['categoria'] === $old) $e['categoria'] = $new; } unset($e);
        if (isset($db['cartelle_drive'][$old])) {
            $db['cartelle_drive'][$new] = $db['cartelle_drive'][$old];
            unset($db['cartelle_drive'][$old]);
        }
    }
    $db['ordine_categorie'] = $d['ordine'];
    $db['cartelle_drive']   = $d['drive'];
    file_put_contents($file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

// Salva farmaci
if (isset($_GET['action']) && $_GET['action'] == 'save_farmaci') {
    if (!isset($_SESSION['auth'])) { http_response_code(403); exit; }
    $d  = json_decode(file_get_contents('php://input'), true);
    $db = json_decode(file_get_contents($file), true);
    $db['farmaci'] = $d['farmaci'];
    file_put_contents($file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════
   AUTH
══════════════════════════════════════════════════════════════════ */
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit(); }
if (isset($_POST['login']) && $_POST['p'] == $pass) $_SESSION['auth'] = true;

if (!isset($_SESSION['auth'])): ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #1a3a5c; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { background:#fff; border-radius:10px; padding:36px 32px; width:340px; box-shadow:0 8px 40px rgba(0,0,0,.3); }
        .login-card h4 { font-family:'Merriweather',serif; color:#1a3a5c; text-align:center; margin-bottom:24px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h4>🔐 Gestione Anamnesi</h4>
        <form method="POST">
            <input type="password" name="p" class="form-control mb-3" placeholder="Password" autofocus>
            <button name="login" class="btn btn-primary w-100">Accedi</button>
        </form>
    </div>
</body></html>
<?php exit; endif;

/* ══════════════════════════════════════════════════════════════════
   AZIONI POST / GET
══════════════════════════════════════════════════════════════════ */
$db = json_decode(file_get_contents($file), true);
if (!isset($db['farmaci']))       $db['farmaci']       = [];
if (!isset($db['cartelle_drive'])) $db['cartelle_drive'] = [];

// Salva profilo
if (isset($_POST['save_p'])) {
    $db['profilo'] = [
        "nome"                => trim($_POST['n']),
        "nascita"             => trim($_POST['nas']),
        "residenza"           => trim($_POST['res']),
        "cf"                  => trim($_POST['cf']),
        "gruppo_sanguigno"    => trim($_POST['gr']),
        "telefono"            => trim($_POST['tel']),
        "email"               => trim($_POST['email']),
        "medico_base"         => trim($_POST['mmg']),
        "medico_tel"          => trim($_POST['mmg_tel']),
        "contatto_emergenza"  => trim($_POST['em_nome']),
        "contatto_em_tel"     => trim($_POST['em_tel']),
        "peso_kg"             => trim($_POST['peso']),
        "altezza_cm"          => trim($_POST['altezza']),
        "patologie_croniche"  => trim($_POST['pat']),
        "anamnesi_remota"     => trim($_POST['rem']),
        "allergie_farmaci"    => trim($_POST['far']),
        "altre_allergie"      => trim($_POST['alt']),
        "note_generali"       => trim($_POST['note']),
    ];
    file_put_contents($file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php?ok=profilo"); exit;
}

// Salva esame
if (isset($_POST['save_e'])) {
    $id  = $_POST['id'] ?: (string)time();
    $new = [
        "id"         => $id,
        "categoria"  => strtoupper(trim($_POST['cat'])),
        "data"       => $_POST['dat'],
        "descrizione"=> $_POST['des'],
        "link"       => trim($_POST['lin']),
        "completato" => isset($_POST['com']),
    ];
    if ($_POST['id'])
        $db['esami'] = array_values(array_filter($db['esami'], fn($e) => $e['id'] != $id));
    array_unshift($db['esami'], $new);
    file_put_contents($file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php?ok=esame"); exit;
}

// Elimina esame
if (isset($_GET['del'])) {
    $db['esami'] = array_values(array_filter($db['esami'], fn($e) => $e['id'] != $_GET['del']));
    file_put_contents($file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php"); exit;
}

$edit    = null;
if (isset($_GET['edit']))
    foreach ($db['esami'] as $e) if ($e['id'] == $_GET['edit']) $edit = $e;

$pending  = array_filter($db['esami'], fn($e) => $e['completato'] === false);
$cats_all = array_unique(array_column($db['esami'], 'categoria'));
$ordine   = $db['ordine_categorie'] ?? array_values($cats_all);
foreach ($cats_all as $c) if (!in_array($c, $ordine)) $ordine[] = $c;
$p = $db['profilo'] ?? [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Gestione Anamnesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://js.nicedit.com/nicEdit-latest.js"></script>
    <style>
        :root {
            --blu:#1a3a5c; --rosso:#c0392b; --verde:#1e6e42;
            --orange:#b7600a; --purple:#6c3483; --bg:#eef1f5;
        }
        * { box-sizing:border-box; }
        body { background:var(--bg); font-family:'Source Sans 3',Arial,sans-serif; font-size:.88rem; color:#2c3e50; }

        /* topbar */
        .topbar { background:var(--blu); color:#fff; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:200; }
        .topbar .brand { font-family:'Merriweather',serif; font-size:.95rem; font-weight:700; }
        .topbar a { color:rgba(255,255,255,.8); text-decoration:none; font-size:.8rem; margin-left:10px; }
        .topbar a:hover { color:#fff; }
        .topbar a.btn-danger-sm { background:#c0392b; color:#fff; padding:4px 10px; border-radius:4px; }

        .wrap { max-width:980px; margin:16px auto; padding:0 12px; }

        /* sezione collassabile */
        .sc { background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.08); margin-bottom:16px; overflow:hidden; }
        .sc-head {
            padding:10px 16px; font-family:'Merriweather',serif; font-size:.8rem; font-weight:700;
            letter-spacing:.8px; text-transform:uppercase; color:#fff;
            display:flex; align-items:center; justify-content:space-between;
            cursor:pointer; user-select:none;
        }
        .sc-head .toggle-icon { font-size:.9rem; transition:transform .3s; }
        .sc-head.collapsed .toggle-icon { transform:rotate(-90deg); }
        .sc-body { padding:14px 16px; }
        .sc-body.hidden { display:none; }

        .h-blu    { background:var(--blu); }
        .h-verde  { background:var(--verde); }
        .h-dark   { background:#2c3e50; }
        .h-orange { background:var(--orange); }
        .h-purple { background:var(--purple); }
        .h-teal   { background:#117a8b; }

        /* stat */
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:8px; }
        .stat-box { background:var(--bg); border:1px solid #d0d8e4; border-radius:6px; padding:9px 10px; text-align:center; }
        .stat-box .num { font-size:1.5rem; font-weight:700; color:var(--blu); }
        .stat-box .lbl { font-size:.7rem; color:#888; text-transform:uppercase; letter-spacing:.4px; }

        /* pending alert */
        .alert-pend { background:#fff8e1; border:1px solid #f0c040; border-radius:6px; padding:10px 14px; margin-bottom:14px; }
        .alert-pend h6 { color:#7a5000; margin-bottom:6px; font-weight:700; }
        .pend-badge { display:inline-block; background:#f0c040; color:#5a3900; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:10px; margin:2px; }

        /* form label mini */
        label.lbl { font-size:.75rem; font-weight:700; color:#555; margin-bottom:2px; display:block; }
        .sep { border:0; border-top:1px dashed #d0d8e0; margin:10px 0; }

        /* drag categorie */
        .drag-item { background:#f4f6f8; border:1px solid #d0d8e0; border-radius:6px; padding:7px 10px; margin-bottom:5px; display:flex; align-items:center; gap:8px; cursor:grab; }
        .drag-item:active { cursor:grabbing; box-shadow:0 4px 14px rgba(0,0,0,.15); }
        .drag-item .handle { color:#bbb; }
        .drag-item .cat-name { font-weight:700; color:var(--blu); flex:0 0 auto; min-width:160px; }
        .drag-item .cat-count { font-size:.72rem; color:#999; flex:0 0 auto; }
        .drag-item .cat-rename {
            font-weight:700; color:var(--blu); font-size:.82rem;
            border:1px dashed #b0bbc8; border-radius:4px;
            padding:2px 7px; background:#fff; min-width:140px; flex:0 0 auto;
            transition: border-color .15s, box-shadow .15s;
        }
        .drag-item .cat-rename:focus { border-color:var(--blu); box-shadow:0 0 0 2px rgba(26,58,92,.15); outline:none; }
        .drag-item .drive-input { flex:1; border:1px solid #ccc; border-radius:4px; padding:3px 8px; font-size:.78rem; color:#333; min-width:0; }
        .drag-item .drive-input::placeholder { color:#bbb; }
        .drive-icon { font-size:.85rem; flex:0 0 auto; }
        .sortable-ghost { opacity:.35; }

        /* farmaci */
        .farm-row { background:#f8f9fa; border:1px solid #dee2e6; border-radius:5px; padding:7px 9px; margin-bottom:5px; display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .farm-row input { border:1px solid #ccc; border-radius:4px; padding:3px 7px; font-size:.8rem; }
        .farm-row input[type="text"] { flex:1; min-width:70px; }
        .btn-del-f { background:none; border:none; color:#c0392b; cursor:pointer; font-size:1rem; padding:1px 5px; }

        /* storico */
        .sto-table { width:100%; border-collapse:collapse; }
        .sto-table th { background:#f4f6f8; border-bottom:2px solid #dee2e6; padding:6px 8px; font-size:.75rem; font-weight:700; color:#666; text-transform:uppercase; }
        .sto-table td { border-bottom:1px solid #f0f0f0; padding:5px 8px; vertical-align:middle; }
        .sto-table tr:hover td { background:#f8f9fa; }
        .sto-table .pend-row td { background:#fffde7; }
        .cat-badge { display:inline-block; background:#e8f0ff; color:var(--blu); font-size:.68rem; font-weight:700; padding:2px 7px; border-radius:10px; }
        .btn-xs { font-size:.72rem; padding:2px 8px; border-radius:4px; border:none; cursor:pointer; }
        .btn-mod { background:#f39c12; color:#fff; }
        .btn-del { background:#e74c3c; color:#fff; }

        /* toast */
        .toast-ok { position:fixed; top:14px; right:14px; background:#1e6e42; color:#fff; padding:9px 18px; border-radius:6px; z-index:9999; font-size:.84rem; box-shadow:0 4px 14px rgba(0,0,0,.2); transition:opacity .4s; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="brand">⚙️ Gestione Anamnesi</div>
    <div>
        <a href="index.php">← Vista pubblica</a>
        <a href="?logout=1" class="btn-danger-sm">Logout</a>
    </div>
</div>

<?php if (isset($_GET['ok'])): ?>
<div class="toast-ok" id="toast">✔ Salvato con successo</div>
<script>setTimeout(()=>{ const t=document.getElementById('toast'); if(t){t.style.opacity='0';setTimeout(()=>t.remove(),500);} },2400);</script>
<?php endif; ?>

<div class="wrap">

    <!-- ── SOSPESI ──────────────────────────────────────────────── -->
    <?php if (!empty($pending)): ?>
    <div class="alert-pend">
        <h6>⏳ Visite in sospeso (<?= count($pending) ?>)</h6>
        <?php foreach ($pending as $pe): ?>
            <span class="pend-badge"><?= htmlspecialchars($pe['categoria']) ?> — <?= date("d/m/Y", strtotime($pe['data'])) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── STATISTICHE ────────────────────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-purple" onclick="toggle(this)">
            <span>📊 Statistiche rapide</span><span class="toggle-icon">▾</span>
        </div>
        <div class="sc-body">
            <?php
            $ultima=''; $date=array_column(array_filter($db['esami'],fn($e)=>$e['completato']),'data');
            if($date){sort($date);$ultima=end($date);}
            ?>
            <div class="stat-grid">
                <div class="stat-box"><div class="num"><?=count($db['esami'])?></div><div class="lbl">Visite totali</div></div>
                <div class="stat-box"><div class="num"><?=count($cats_all)?></div><div class="lbl">Categorie</div></div>
                <div class="stat-box"><div class="num"><?=count($pending)?></div><div class="lbl">In sospeso</div></div>
                <div class="stat-box"><div class="num"><?=count(array_filter($db['esami'],fn($e)=>substr($e['data'],0,4)==date('Y')))?></div><div class="lbl">Anno <?=date('Y')?></div></div>
                <div class="stat-box"><div class="num"><?=count($db['farmaci']??[])?></div><div class="lbl">Farmaci attivi</div></div>
                <div class="stat-box"><div class="num" style="font-size:.85rem;padding-top:10px"><?=$ultima?date("d/m/y",strtotime($ultima)):'—'?></div><div class="lbl">Ultima visita</div></div>
            </div>
        </div>
    </div>

    <!-- ── DATI PERSONALI (collassabile) ────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-blu collapsed" onclick="toggle(this)">
            <span>👤 Dati Personali — <?= htmlspecialchars($p['nome'] ?? 'paziente') ?></span>
            <span class="toggle-icon">▾</span>
        </div>
        <div class="sc-body hidden">
            <form method="POST">
                <!-- Riga 1: anagrafica base -->
                <div class="row g-2 mb-1">
                    <div class="col-md-5"><label class="lbl">Nome e Cognome</label><input type="text" name="n" class="form-control form-control-sm" value="<?=htmlspecialchars($p['nome']??'')?>"></div>
                    <div class="col-md-3"><label class="lbl">Data di nascita</label><input type="text" name="nas" class="form-control form-control-sm" value="<?=htmlspecialchars($p['nascita']??'')?>"></div>
                    <div class="col-md-4"><label class="lbl">Residenza</label><input type="text" name="res" class="form-control form-control-sm" value="<?=htmlspecialchars($p['residenza']??'')?>"></div>
                </div>
                <!-- Riga 2: dati fiscali e fisici -->
                <div class="row g-2 mb-1">
                    <div class="col-md-4"><label class="lbl">Codice Fiscale</label><input type="text" name="cf" class="form-control form-control-sm" value="<?=htmlspecialchars($p['cf']??'')?>"></div>
                    <div class="col-md-2"><label class="lbl">Gruppo Sanguigno</label><input type="text" name="gr" class="form-control form-control-sm" value="<?=htmlspecialchars($p['gruppo_sanguigno']??'')?>" placeholder="es. A+"></div>
                    <div class="col-md-2"><label class="lbl">Peso (kg)</label><input type="text" name="peso" class="form-control form-control-sm" value="<?=htmlspecialchars($p['peso_kg']??'')?>" placeholder="es. 78"></div>
                    <div class="col-md-2"><label class="lbl">Altezza (cm)</label><input type="text" name="altezza" class="form-control form-control-sm" value="<?=htmlspecialchars($p['altezza_cm']??'')?>" placeholder="es. 175"></div>
                    <div class="col-md-2"><label class="lbl">Telefono</label><input type="text" name="tel" class="form-control form-control-sm" value="<?=htmlspecialchars($p['telefono']??'')?>"></div>
                </div>
                <!-- Riga 3: email -->
                <div class="row g-2 mb-1">
                    <div class="col-md-4"><label class="lbl">E-mail</label><input type="email" name="email" class="form-control form-control-sm" value="<?=htmlspecialchars($p['email']??'')?>"></div>
                    <div class="col-md-4"><label class="lbl">Medico di Base</label><input type="text" name="mmg" class="form-control form-control-sm" value="<?=htmlspecialchars($p['medico_base']??'')?>" placeholder="Dott./Dott.ssa …"></div>
                    <div class="col-md-4"><label class="lbl">Tel. Medico di Base</label><input type="text" name="mmg_tel" class="form-control form-control-sm" value="<?=htmlspecialchars($p['medico_tel']??'')?>"></div>
                </div>
                <!-- Riga 4: emergenza -->
                <div class="row g-2 mb-1">
                    <div class="col-md-5"><label class="lbl">Contatto di emergenza (nome / rapporto)</label><input type="text" name="em_nome" class="form-control form-control-sm" value="<?=htmlspecialchars($p['contatto_emergenza']??'')?>" placeholder="es. Maria Basile – moglie"></div>
                    <div class="col-md-3"><label class="lbl">Tel. emergenza</label><input type="text" name="em_tel" class="form-control form-control-sm" value="<?=htmlspecialchars($p['contatto_em_tel']??'')?>"></div>
                </div>
                <hr class="sep">
                <!-- Riga 5: clinica -->
                <div class="row g-2 mb-1">
                    <div class="col-md-4"><label class="lbl">Patologie Croniche</label><textarea name="pat" class="form-control form-control-sm" rows="2"><?=htmlspecialchars($p['patologie_croniche']??'')?></textarea></div>
                    <div class="col-md-4"><label class="lbl">Anamnesi Remota (interventi / malattie passate)</label><textarea name="rem" class="form-control form-control-sm" rows="2"><?=htmlspecialchars($p['anamnesi_remota']??'')?></textarea></div>
                    <div class="col-md-4"><label class="lbl">Allergie a Farmaci</label><textarea name="far" class="form-control form-control-sm" rows="2"><?=htmlspecialchars($p['allergie_farmaci']??'')?></textarea></div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6"><label class="lbl">Altre Allergie / Intolleranze</label><textarea name="alt" class="form-control form-control-sm" rows="2"><?=htmlspecialchars($p['altre_allergie']??'')?></textarea></div>
                    <div class="col-md-6"><label class="lbl">Note Generali</label><textarea name="note" class="form-control form-control-sm" rows="2"><?=htmlspecialchars($p['note_generali']??'')?></textarea></div>
                </div>
                <div class="text-end mt-2">
                    <button name="save_p" class="btn btn-primary btn-sm">💾 Aggiorna Profilo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── FARMACI ───────────────────────────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-teal collapsed" onclick="toggle(this)">
            <span>💊 Terapie in Corso</span>
            <span style="display:flex;align-items:center;gap:10px">
                <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:4px;font-size:.74rem;cursor:pointer" onclick="event.stopPropagation();aggiungiFarmaco()">+ Aggiungi</span>
                <span class="toggle-icon">▾</span>
            </span>
        </div>
        <div class="sc-body hidden">
            <div id="lista-farmaci">
                <?php foreach ($db['farmaci'] as $f): ?>
                <div class="farm-row">
                    <input type="text" placeholder="Farmaco" value="<?=htmlspecialchars($f['nome']??'')?>" data-field="nome">
                    <input type="text" placeholder="Dosaggio" value="<?=htmlspecialchars($f['dosaggio']??'')?>" style="max-width:110px" data-field="dosaggio">
                    <input type="text" placeholder="Frequenza" value="<?=htmlspecialchars($f['frequenza']??'')?>" style="max-width:140px" data-field="frequenza">
                    <input type="date" value="<?=htmlspecialchars($f['dal']??'')?>" style="max-width:130px" data-field="dal" title="Inizio terapia">
                    <input type="text" placeholder="Note" value="<?=htmlspecialchars($f['note']??'')?>" style="max-width:180px" data-field="note">
                    <button class="btn-del-f" onclick="this.closest('.farm-row').remove()">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-end mt-2">
                <button onclick="salvaFarmaci()" class="btn btn-sm" style="background:#117a8b;color:#fff">💾 Salva Terapia</button>
            </div>
        </div>
    </div>

    <!-- ── CATEGORIE + LINK GOOGLE DRIVE ────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-orange collapsed" onclick="toggle(this)">
            <span>📁 Ordine Categorie &amp; Cartelle Google Drive</span>
            <span class="toggle-icon">▾</span>
        </div>
        <div class="sc-body hidden">
            <p class="text-muted" style="font-size:.78rem;margin-bottom:10px">
                Trascina per riordinare. <strong>Clicca sul nome</strong> della categoria per rinominarla (verrà aggiornata anche in tutti gli esami). Incolla il link della cartella Google Drive per ogni specialistica.
            </p>
            <div id="cat-sortable">
                <?php foreach ($ordine as $c):
                    $cnt   = count(array_filter($db['esami'], fn($e) => $e['categoria'] === $c));
                    $drive = htmlspecialchars($db['cartelle_drive'][$c] ?? '');
                ?>
                <div class="drag-item" data-cat="<?=htmlspecialchars($c)?>">
                    <span class="handle">⠿</span>
                    <input class="cat-rename" type="text" value="<?=htmlspecialchars($c)?>" title="Rinomina categoria" data-orig="<?=htmlspecialchars($c)?>">
                    <span class="cat-count"><?=$cnt?> visita/e</span>
                    <span class="drive-icon" title="Google Drive">📁</span>
                    <input class="drive-input" type="url" placeholder="Link cartella Google Drive (https://drive.google.com/…)" value="<?=$drive?>" data-drive="<?=htmlspecialchars($c)?>">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-end mt-2">
                <span id="ord-status" class="text-muted small me-2"></span>
                <button onclick="salvaCat()" class="btn btn-sm btn-warning">💾 Salva Ordine e Link Drive</button>
            </div>
        </div>
    </div>

    <!-- ── NUOVA VISITA / MODIFICA ──────────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-verde" onclick="toggle(this)">
            <span><?= $edit ? '✏️ Modifica Visita' : '➕ Nuova Visita' ?></span>
            <span class="toggle-icon">▾</span>
        </div>
        <div class="sc-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?=htmlspecialchars($edit['id']??'')?>">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="lbl">Categoria</label>
                        <input list="cats" name="cat" class="form-control form-control-sm" value="<?=htmlspecialchars($edit['categoria']??'')?>" placeholder="es. CARDIOLOGIA">
                        <datalist id="cats"><?php foreach(array_unique(array_column($db['esami'],'categoria')) as $c) echo "<option value='".htmlspecialchars($c)."'>"; ?></datalist>
                    </div>
                    <div class="col-md-3">
                        <label class="lbl">Data Visita</label>
                        <input type="date" name="dat" class="form-control form-control-sm" value="<?=htmlspecialchars($edit['data']??date('Y-m-d'))?>">
                    </div>
                    <div class="col-md-5">
                        <label class="lbl">Link PDF / Referto su Drive</label>
                        <input type="text" name="lin" class="form-control form-control-sm" value="<?=htmlspecialchars($edit['link']??'')?>" placeholder="https://drive.google.com/file/…">
                    </div>
                    <div class="col-12">
                        <label class="lbl">Descrizione <span style="font-weight:400;color:#999">(seleziona testo per grassetto / colore)</span></label>
                        <textarea name="des" id="area-desc" style="width:100%;height:100px;font-size:.87rem;"><?=$edit['descrizione']??''?></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-between align-items-center mt-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="com" id="cc" <?=($edit&&!$edit['completato'])?'':'checked'?>>
                            <label class="form-check-label fw-bold small" for="cc">Visita Effettuata</label>
                        </div>
                        <div>
                            <?php if($edit):?><a href="admin.php" class="btn btn-sm btn-outline-secondary me-2">Annulla</a><?php endif;?>
                            <button name="save_e" class="btn btn-success btn-sm px-4">💾 Salva Visita</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ── STORICO ──────────────────────────────────────────────── -->
    <div class="sc">
        <div class="sc-head h-dark" onclick="toggle(this)">
            <span>📋 Storico Completo (<?=count($db['esami'])?> record)</span>
            <span class="toggle-icon">▾</span>
        </div>
        <div class="sc-body" style="padding:0">
            <?php $esord=$db['esami']; usort($esord,fn($a,$b)=>strcmp($b['data'],$a['data'])); ?>
            <div style="overflow-x:auto">
            <table class="sto-table">
                <thead><tr><th class="ps-3">Data</th><th>Categoria</th><th>Descrizione</th><th style="width:100px;text-align:center">Azioni</th></tr></thead>
                <tbody>
                <?php foreach($esord as $e): ?>
                <tr class="<?=!$e['completato']?'pend-row':''?>">
                    <td class="ps-3 fw-bold text-nowrap" style="width:92px"><?=date("d/m/Y",strtotime($e['data']))?></td>
                    <td><span class="cat-badge"><?=htmlspecialchars($e['categoria'])?></span></td>
                    <td style="font-size:.79rem;max-width:400px"><?=$e['descrizione']?><?=!$e['completato']?' <span style="color:#b7600a;font-size:.7rem">⏳</span>':''?></td>
                    <td style="text-align:center;white-space:nowrap">
                        <a href="?edit=<?=$e['id']?>" class="btn-xs btn-mod">Mod</a>
                        <a href="?del=<?=$e['id']?>" class="btn-xs btn-del ms-1" onclick="return confirm('Eliminare?')">Del</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div><!-- /wrap -->

<script>
/* ── Accordion ──────────────────────────────────────────────────── */
function toggle(head) {
    const body = head.nextElementSibling;
    const icon = head.querySelector('.toggle-icon');
    const open = !body.classList.contains('hidden');
    body.classList.toggle('hidden', open);
    head.classList.toggle('collapsed', open);
}

/* ── NicEdit ─────────────────────────────────────────────────────── */
bkLib.onDomLoaded(function() {
    new nicEditor({ buttonList:['bold','forecolor'] }).panelInstance('area-desc');
});

/* ── Sortable categorie ──────────────────────────────────────────── */
new Sortable(document.getElementById('cat-sortable'), {
    animation: 180,
    handle: '.handle',
    ghostClass: 'sortable-ghost',
    onEnd: () => { document.getElementById('ord-status').textContent = '⚠ Non salvato'; }
});

async function salvaCat() {
    const items = document.querySelectorAll('#cat-sortable .drag-item');
    const ordine = [], drive = {}, rename = [];
    items.forEach(el => {
        const orig    = el.dataset.cat;
        const newName = (el.querySelector('.cat-rename').value.trim().toUpperCase()) || orig;
        el.dataset.cat = newName;
        el.querySelector('.cat-rename').dataset.orig = newName;
        ordine.push(newName);
        const url = el.querySelector('.drive-input').value.trim();
        if (url) drive[newName] = url;
        if (orig !== newName) rename.push({ old: orig, new: newName });
    });
    const r    = await fetch('admin.php?action=save_cat', { method:'POST', body:JSON.stringify({ordine,drive,rename}), headers:{'Content-Type':'application/json'} });
    const data = await r.json();
    const st   = document.getElementById('ord-status');
    st.textContent = data.ok ? '✔ Salvato' : '✗ Errore';
    st.style.color = data.ok ? '#1e6e42' : '#c0392b';
    setTimeout(() => { st.textContent=''; }, 2500);
}

/* ── Farmaci ─────────────────────────────────────────────────────── */
function aggiungiFarmaco() {
    const d = document.createElement('div');
    d.className = 'farm-row';
    d.innerHTML = `
        <input type="text"  placeholder="Farmaco"   data-field="nome"      style="flex:1;min-width:70px;border:1px solid #ccc;border-radius:4px;padding:3px 7px;font-size:.8rem">
        <input type="text"  placeholder="Dosaggio"  data-field="dosaggio"  style="max-width:110px;border:1px solid #ccc;border-radius:4px;padding:3px 7px;font-size:.8rem">
        <input type="text"  placeholder="Frequenza" data-field="frequenza" style="max-width:140px;border:1px solid #ccc;border-radius:4px;padding:3px 7px;font-size:.8rem">
        <input type="date"  data-field="dal"         title="Inizio terapia" style="max-width:130px;border:1px solid #ccc;border-radius:4px;padding:3px 7px;font-size:.8rem">
        <input type="text"  placeholder="Note"      data-field="note"      style="max-width:180px;border:1px solid #ccc;border-radius:4px;padding:3px 7px;font-size:.8rem">
        <button class="btn-del-f" onclick="this.closest('.farm-row').remove()">✕</button>`;
    document.getElementById('lista-farmaci').appendChild(d);
    d.querySelector('input').focus();
}

async function salvaFarmaci() {
    const farmaci = [];
    document.querySelectorAll('#lista-farmaci .farm-row').forEach(row => {
        const f = {};
        row.querySelectorAll('[data-field]').forEach(i => { f[i.dataset.field] = i.value; });
        if (f.nome?.trim()) farmaci.push(f);
    });
    const r = await fetch('admin.php?action=save_farmaci', { method:'POST', body:JSON.stringify({farmaci}), headers:{'Content-Type':'application/json'} });
    const data = await r.json();
    if (data.ok) {
        const t = document.createElement('div');
        t.className='toast-ok'; t.textContent='✔ Terapia salvata';
        document.body.appendChild(t);
        setTimeout(()=>{ t.style.opacity='0'; setTimeout(()=>t.remove(),500); }, 2000);
    }
}

/* ── Apri sezione se c'è edit in corso ──────────────────────────── */
<?php if ($edit): ?>
document.querySelectorAll('.sc-head').forEach(h => {
    if (h.textContent.includes('Nuova Visita') || h.textContent.includes('Modifica Visita')) {
        const b = h.nextElementSibling;
        b.classList.remove('hidden');
        h.classList.remove('collapsed');
    }
});
<?php endif; ?>
</script>
</body>
</html>