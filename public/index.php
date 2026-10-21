<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';

function respond(array $data, int $status=200, array $meta=[]): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header((($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') ? 'Cache-Control: public, max-age=30, stale-while-revalidate=60' : 'Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok'=>true,'data'=>$data,'meta'=>array_merge(['generated_at'=>gmdate('c')],$meta)], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function respondError(int $status, string $code, string $message): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok'=>false,'error'=>['code'=>$code,'message'=>$message],'meta'=>['generated_at'=>gmdate('c')]], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    exit;
}
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) $path = substr($path, strlen($scriptDir));
$path = '/' . trim($path, '/');
if ($path === '//') $path = '/';

// Public VAPID key is safe to expose; private key stays server-side.
if ($path === '/api/v1/push/public-key' && $method === 'GET') {
    $publicKey = (string)($appConfig['push']['public_key'] ?? '');
    if ($publicKey === '') respondError(503, 'push_not_configured', 'Web Push is not configured; local browser alerts remain available.');
    respond(['public_key'=>$publicKey], 200, ['feature'=>'web-push']);
}

// Store a browser subscription for standards-based Web Push delivery.
if ($path === '/api/v1/push/subscribe' && $method === 'POST') {
    $body = json_decode((string)file_get_contents('php://input'), true);
    $sub = is_array($body['subscription'] ?? null) ? $body['subscription'] : $body;
    $endpointUrl = $sub['endpoint'] ?? null;
    $keys = $sub['keys'] ?? null;
    $endpointHost = is_string($endpointUrl) ? strtolower((string)parse_url($endpointUrl, PHP_URL_HOST)) : '';
    $allowedPushHost = $endpointHost === 'fcm.googleapis.com' ||
        $endpointHost === 'web.push.apple.com' ||
        str_ends_with($endpointHost, '.push.services.mozilla.com') ||
        str_ends_with($endpointHost, '.notify.windows.com');
    if (!is_string($endpointUrl) || !filter_var($endpointUrl, FILTER_VALIDATE_URL) ||
        !str_starts_with(strtolower($endpointUrl), 'https://') || !$allowedPushHost ||
        !is_array($keys) || empty($keys['p256dh']) || empty($keys['auth'])) {
        respondError(422, 'invalid_subscription', 'A valid HTTPS push endpoint and p256dh/auth keys are required.');
    }
    $json = json_encode(['endpoint'=>$endpointUrl,'keys'=>['p256dh'=>(string)$keys['p256dh'],'auth'=>(string)$keys['auth']]], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $rawPreferences = is_array($body['preferences'] ?? null) ? $body['preferences'] : [];
    $preferences = [
        'orders' => !array_key_exists('orders',$rawPreferences) || $rawPreferences['orders'] === true,
        'dispatches' => !array_key_exists('dispatches',$rawPreferences) || $rawPreferences['dispatches'] === true,
        'attacks' => !array_key_exists('attacks',$rawPreferences) || $rawPreferences['attacks'] === true,
    ];
    $preferencesJson = json_encode($preferences);
    if ($json === false || $preferencesJson === false) respondError(422, 'invalid_subscription', 'Could not encode push subscription.');
    $hash = hash('sha256', $endpointUrl);
    $q = $pdo->prepare('INSERT INTO push_subscriptions(endpoint_hash,endpoint_url,subscription_json,preferences_json,created_at,last_seen_at) VALUES(?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE subscription_json=VALUES(subscription_json),preferences_json=VALUES(preferences_json),last_seen_at=UTC_TIMESTAMP()');
    $q->execute([$hash,$endpointUrl,$json,$preferencesJson]);
    respond(['subscribed'=>true], 201, ['feature'=>'web-push']);
}
if ($path === '/api/v1/push/subscribe' && $method === 'DELETE') {
    $body = json_decode((string)file_get_contents('php://input'), true);
    $endpointUrl = is_array($body) ? ($body['endpoint'] ?? '') : '';
    if (!is_string($endpointUrl) || !str_starts_with(strtolower($endpointUrl), 'https://')) respondError(422, 'invalid_endpoint', 'A valid HTTPS endpoint is required.');
    $q = $pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint_hash=?');
    $q->execute([hash('sha256',$endpointUrl)]);
    respond(['unsubscribed'=>true], 200, ['feature'=>'web-push']);
}
if ($method !== 'GET') {
    header('Allow: GET, POST, DELETE');
    respondError(405, 'method_not_allowed', 'Method not allowed for this endpoint.');
}

// The browser dashboard lives at the root. All API routes continue to return JSON.
if ($path === '/' || $path === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#10150f"><meta name="description" content="Galactic Command — live Helldivers 2 war intelligence, combat recommendations and galactic archives.">
<title>Galactic Command — War Room</title>
<link rel="stylesheet" href="assets/modules.css">
<style>
:root{color-scheme:dark;--bg:#0b0f0b;--panel:#141a13;--panel2:#1b2318;--line:#303b29;--text:#f0f3e8;--muted:#a5ae99;--acid:#c7ed63;--gold:#f2bd55;--red:#ff7770;--blue:#8ed9ef;--radius:16px;--shadow:0 12px 30px #0005}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(ellipse at 70% -10%,#354326 0,transparent 40%),var(--bg);color:var(--text);font:15px/1.5 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}a{color:inherit}button,input,select{font:inherit}button{cursor:pointer}.shell{max-width:1440px;margin:auto;padding:0 20px 44px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 0;border-bottom:1px solid var(--line)}.brand{display:flex;align-items:center;gap:12px;min-width:0}.logo{display:grid;place-items:center;width:44px;height:44px;background:var(--acid);color:#11170b;font-weight:1000;font-size:22px;clip-path:polygon(50% 0,94% 18%,88% 77%,50% 100%,12% 77%,6% 18%)}.brandname{font-weight:900;letter-spacing:.12em;font-size:14px}.brandname small{display:block;color:var(--muted);font-weight:600;letter-spacing:.16em;font-size:10px}.status{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px}.dot{width:8px;height:8px;border-radius:50%;background:var(--acid);box-shadow:0 0 12px #c7ed6380}.top-actions{display:flex;align-items:center;gap:8px}.btn{border:1px solid var(--line);border-radius:10px;padding:9px 12px;background:#ffffff08;color:var(--text);font-weight:700}.btn:hover{border-color:var(--acid)}.btn.primary{background:var(--acid);color:#11170b;border-color:var(--acid)}.hero{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(260px,.7fr);gap:18px;padding:26px 0 20px}.eyebrow{color:var(--acid);font-weight:900;letter-spacing:.2em;font-size:11px;text-transform:uppercase}.hero h1{font-size:clamp(30px,5vw,58px);line-height:1.02;letter-spacing:-.045em;margin:9px 0 12px;max-width:800px}.hero p{color:var(--muted);max-width:700px;margin:0}.war-card,.panel,.pillar{background:linear-gradient(145deg,#1b2417,#111610);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}.war-card{padding:20px;display:flex;flex-direction:column;justify-content:space-between;min-height:180px}.war-head{display:flex;justify-content:space-between;gap:12px;align-items:center}.war-title{font-size:12px;letter-spacing:.14em;font-weight:900;color:var(--muted)}.war-clock{font-variant-numeric:tabular-nums;font-size:12px;color:var(--acid)}.war-value{font-size:clamp(27px,4vw,42px);font-weight:900;letter-spacing:-.04em;margin:12px 0 2px}.war-sub{font-size:12px;color:var(--muted)}.meter{height:8px;background:#080b07;border:1px solid #34402c;border-radius:99px;overflow:hidden;margin:13px 0 8px}.meter span{display:block;height:100%;width:0;background:linear-gradient(90deg,#85b63d,var(--acid));border-radius:99px;transition:width .4s}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:4px 0 24px}.stat{padding:15px;border:1px solid var(--line);background:#ffffff04;border-radius:13px;min-width:0}.stat .label{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.1em;font-weight:800}.stat .value{font-size:clamp(19px,2.5vw,28px);font-weight:900;margin-top:4px;overflow-wrap:anywhere}.stat .hint{font-size:11px;color:var(--muted)}.section-head{display:flex;align-items:end;justify-content:space-between;gap:14px;margin:30px 0 14px}.section-head h2{font-size:19px;letter-spacing:-.02em;margin:0}.section-head p{margin:4px 0 0;color:var(--muted);font-size:12px}.pillars{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.pillar{padding:18px;min-height:142px;box-shadow:none}.pillar .number{color:var(--acid);font-size:11px;font-weight:900;letter-spacing:.18em}.pillar h3{margin:8px 0 5px;font-size:17px}.pillar p{margin:0;color:var(--muted);font-size:13px}.grid-main{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr);gap:14px;margin-top:14px}.panel{padding:18px;min-width:0}.panel-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-title h3{font-size:16px;margin:0}.small-note{font-size:11px;color:var(--muted)}.search-row{display:flex;gap:8px;margin-bottom:12px}.search{width:100%;min-width:0;border:1px solid var(--line);background:#090d08;color:var(--text);border-radius:10px;padding:10px 12px;outline:none}.search:focus{border-color:var(--acid)}.planet-list,.feed-list,.order-list{display:grid;gap:9px}.galaxy-map{position:relative;margin:0 0 12px;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:radial-gradient(ellipse at 50% 50%,#28371d 0, #11180f 55%,#090d08 100%);min-height:185px}.galaxy-map svg{display:block;width:100%;height:205px}.map-caption{position:absolute;left:10px;top:9px;font-size:9px;letter-spacing:.15em;color:var(--muted);font-weight:900;text-transform:uppercase}.map-dot{cursor:pointer;stroke:#c7ed6380;stroke-width:1.5}.map-dot:hover{stroke:#fff;stroke-width:3}.map-empty{padding:22px;text-align:center;color:var(--muted);font-size:12px}.planet{padding:12px;border:1px solid var(--line);border-radius:12px;background:#ffffff03}.planet-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.planet-name{font-weight:850}.planet-meta{font-size:11px;color:var(--muted);margin-top:2px}.tag{display:inline-flex;align-items:center;border:1px solid var(--line);border-radius:99px;padding:3px 8px;font-size:10px;font-weight:800;color:var(--muted);white-space:nowrap}.tag.hot{color:var(--gold);border-color:#7d5e2a}.tag.good{color:var(--acid);border-color:#52672b}.planet .meter{margin:9px 0 4px;height:6px}.planet-foot{display:flex;justify-content:space-between;gap:10px;color:var(--muted);font-size:11px}.feed-item{border-bottom:1px solid #ffffff10;padding:0 0 12px;margin-bottom:12px}.feed-item:last-child{border:0;margin:0;padding:0}.feed-item h4{font-size:13px;line-height:1.35;margin:0 0 4px}.feed-item p{font-size:12px;color:var(--muted);margin:0;overflow-wrap:anywhere}.feed-item .meta{font-size:10px;color:var(--acid);margin-bottom:4px;letter-spacing:.06em}.order{padding:13px;border:1px solid var(--line);border-radius:12px;background:#ffffff03}.order h4{margin:0 0 5px;font-size:14px}.order p{margin:0;color:var(--muted);font-size:12px}.order-bottom{display:flex;justify-content:space-between;gap:10px;margin-top:10px;font-size:11px;color:var(--muted)}.campaign-list{display:grid;gap:8px}.campaign{display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #ffffff10;padding:8px 0}.campaign:last-child{border:0}.campaign strong{font-size:12px}.campaign span{font-size:11px;color:var(--muted);text-align:right}.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}.empty,.error{padding:15px;border:1px dashed var(--line);border-radius:12px;color:var(--muted);font-size:12px}.error{border-color:#8c3934;color:#ffaaa5}.footer{display:flex;justify-content:space-between;gap:12px;padding:25px 0 0;margin-top:28px;border-top:1px solid var(--line);font-size:11px;color:var(--muted)}.loading{color:var(--muted);font-size:12px}.hide{display:none!important}.mobile-nav{display:none}
@media(max-width:900px){.hero{grid-template-columns:1fr}.war-card{min-height:150px}.grid-main{grid-template-columns:1fr}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.two-col{grid-template-columns:1fr}}
@media(max-width:600px){.shell{padding:0 13px 30px}.topbar{padding:13px 0}.brandname{font-size:12px}.logo{width:38px;height:38px}.status{display:none}.top-actions .btn{padding:8px 9px;font-size:12px}.hero{padding:23px 0 14px;gap:13px}.hero h1{font-size:36px}.hero p{font-size:13px}.war-card{padding:16px}.stats{gap:8px;margin-bottom:20px}.stat{padding:12px}.stat .label{font-size:9px}.stat .hint{font-size:10px}.section-head{margin:23px 0 12px}.section-head h2{font-size:17px}.pillars{grid-template-columns:1fr;gap:8px}.pillar{min-height:0;padding:14px}.pillar h3{margin:5px 0 3px}.panel{padding:14px}.two-col{gap:10px}.footer{flex-direction:column}.mobile-nav{position:sticky;bottom:8px;z-index:10;display:flex;justify-content:space-around;gap:5px;padding:7px;background:#10160feF;border:1px solid var(--line);border-radius:14px;backdrop-filter:blur(12px);margin-top:20px}.mobile-nav a{text-decoration:none;text-align:center;font-size:10px;color:var(--muted);padding:4px}.mobile-nav a span{display:block;font-size:16px}.planet-foot{font-size:10px}}
</style>
</head>
<body><div class="shell">
<header class="topbar"><div class="brand"><div class="logo">GC</div><div class="brandname">GALACTIC COMMAND<small>HELLDIVERS 2 INTELLIGENCE</small></div></div><div class="status"><span class="dot"></span><span id="connection">CONNECTING TO GALACTIC NETWORK</span></div><div class="top-actions"><button class="btn" id="langToggle" type="button" aria-label="Switch language">ES</button><button class="btn" id="refreshBtn" type="button">↻ Refresh</button><button class="btn primary" type="button" onclick="document.getElementById('planets').scrollIntoView({behavior:'smooth'})">War map ↓</button></div></header>
<section class="hero"><div><div class="eyebrow">Managed Democracy // Live Operations</div><h1>THE GALAXY<br>IS THE BATTLEFIELD.</h1><p>Live war tracking, combat intelligence and galactic archives. Your operational picture for the fight for Super Earth.</p></div><div class="war-card"><div class="war-head"><span class="war-title">GALACTIC WAR STATUS</span><span class="war-clock" id="warClock">LIVE FEED</span></div><div><div class="war-value" id="warStatus">ACQUIRING SIGNAL</div><div class="war-sub" id="warSub">Connecting to the war data feed…</div><div class="meter"><span id="warMeter"></span></div><div class="war-head"><span class="war-sub" id="warMeterLabel">Planetary data loading</span><span class="war-sub" id="warUpdated">—</span></div></div></div></section>
<section class="stats" aria-label="Galactic statistics"><div class="stat"><div class="label">Active Helldivers</div><div class="value" id="players">—</div><div class="hint">Across reported operations</div></div><div class="stat"><div class="label">Missions won</div><div class="value" id="missionsWon">—</div><div class="hint">Reported total</div></div><div class="stat"><div class="label">Enemies eliminated</div><div class="value" id="kills">—</div><div class="hint">All reported factions</div></div><div class="stat"><div class="label">Accuracy</div><div class="value" id="accuracy">—</div><div class="hint">Galactic war statistics</div></div></section>
<div class="section-head"><div><h2>Command divisions</h2><p>Three core pillars, connected through one operational view.</p></div></div><section class="pillars"><article class="pillar"><div class="number">01 / WAR</div><h3>Galactic War</h3><p>Planet control, liberation progress, campaigns, attacks and live global orders.</p></article><article class="pillar"><div class="number">02 / COMBAT INTELLIGENCE</div><h3>Mission readiness</h3><p>Track enemy pressure and operational context to inform your next loadout and mission plan.</p></article><article class="pillar"><div class="number">03 / GALACTIC ARCHIVES</div><h3>Archives & community intel</h3><p>Dispatches, official updates, war history and the wider community picture.</p></article></section>
<div class="grid-main"><section class="panel" id="planets"><div class="panel-title"><div><h3>War map / Planetary front</h3><div class="small-note" id="planetCount">Loading planetary intelligence…</div></div><span class="tag" id="planetLiveTag">LIVE DATA</span></div><div class="search-row"><input class="search" id="planetSearch" type="search" placeholder="Search planet, sector, biome or faction…" aria-label="Search planets"><select class="search" id="planetFilter" aria-label="Filter planets" style="max-width:145px"><option value="all">All planets</option><option value="attacked">Under attack</option><option value="event">Active event</option><option value="progress">Progress data</option></select></div><div class="galaxy-map" id="galaxyMap"><div class="map-caption">Schematic planetary theatre · select a marker</div><div class="map-empty">Waiting for planetary coordinates…</div></div><div class="planet-list" id="planetList"><div class="loading">Loading planetary data…</div></div><button class="btn" id="morePlanets" style="width:100%;margin-top:12px" type="button">Show more planets</button></section>
<div style="display:grid;gap:14px;align-content:start"><section class="panel" id="ordersPanel"><div class="panel-title"><h3>Orders of the day</h3><span class="tag hot">PRIORITY</span></div><div class="order-list" id="orders"><div class="loading">Retrieving current orders…</div></div></section><section class="panel"><div class="panel-title"><h3>Active campaigns</h3><span class="small-note" id="campaignCount">—</span></div><div class="campaign-list" id="campaigns"><div class="loading">Loading campaigns…</div></div></section></div></div>
<div class="two-col"><section class="panel"><div class="panel-title"><h3>Galactic dispatches</h3><span class="tag">FIELD REPORTS</span></div><div class="feed-list" id="dispatches"><div class="loading">Loading dispatches…</div></div></section><section class="panel"><div class="panel-title"><h3>Official updates</h3><span class="tag">STEAM NEWS</span></div><div class="feed-list" id="steamNews"><div class="loading">Loading official news…</div></div></section></div>
<div class="two-col"><section class="panel"><div class="panel-title"><h3>Space station</h3><span class="tag good">STRATEGIC ASSETS</span></div><div id="station"><div class="loading">Scanning strategic assets…</div></div></section><section class="panel"><div class="panel-title"><h3>Community intel</h3><span class="tag">WAR ROOM</span></div><p style="font-size:13px;color:var(--muted);margin:0 0 12px">A shared operational view of the galactic war. Community trends, detailed loadout recommendations and historical analytics will expand as those datasets become available through the companion API.</p><div class="campaign-list"><div class="campaign"><strong>War intelligence</strong><span id="warIntelState">Connecting…</span></div><div class="campaign"><strong>Combat recommendations</strong><span>Data module planned</span></div><div class="campaign"><strong>Galactic archives</strong><span id="archiveState">Connecting…</span></div></div></section></div>

<section class="panel module-wrap" id="loadout">
  <div class="panel-title"><div><h3 data-i18n="loadoutTitle">Combat intelligence / Loadout planner</h3><div class="small-note" data-i18n="loadoutSub">Mission-aware recommendations from a transparent rule set.</div></div><span class="tag good">TACTICAL ADVISOR</span></div>
  <div class="module-fields">
    <label><span data-i18n="enemyFaction">Enemy faction</span><select id="loFaction"><option value="terminids">Terminids</option><option value="automatons">Automatons</option><option value="illuminate">Illuminate</option><option value="unknown">Unknown / mixed</option></select></label>
    <label><span data-i18n="missionType">Mission type</span><select id="loMission"><option value="eradicate">Eradicate / high kills</option><option value="defense">Defense / hold position</option><option value="objectives">Objectives / operations</option><option value="recon">Recon / stealth</option><option value="extermination">Heavy targets</option><option value="escort">Escort / civilians</option></select></label>
    <label><span data-i18n="playstyle">Playstyle</span><select id="loStyle"><option value="balanced">Balanced</option><option value="antiarmor">Anti-armor</option><option value="crowd">Crowd control</option><option value="support">Support / team utility</option><option value="stealth">Stealth / mobility</option></select></label>
    <label><span data-i18n="difficulty">Difficulty</span><select id="loDifficulty"><option value="4">4 · Challenging</option><option value="5">5 · Hard</option><option value="6" selected>6 · Extreme</option><option value="7">7 · Suicide Mission</option><option value="8">8 · Impossible</option><option value="9">9 · Helldive</option><option value="10">10 · Super Helldive</option></select></label>
    <label><span data-i18n="squadSize">Squad size</span><select id="loSquad"><option value="solo">Solo</option><option value="duo">Duo</option><option value="squad" selected>Full squad</option></select></label>
    <label><span data-i18n="planetContext">Planet context (optional)</span><select id="loPlanet"><option value="">No planet selected</option></select></label>
  </div>
  <div class="module-actions"><button class="btn primary" id="loGenerate" type="button" data-i18n="generateLoadout">Generate loadout</button><button class="btn" id="loCopy" type="button" data-i18n="copyLoadout">Copy plan</button><span id="loStatus" class="module-status" aria-live="polite"></span></div>
  <div id="loadoutResult" class="module-result"><div class="module-empty">Choose your mission context to generate a recommended four-stratagem plan.</div></div>
  <div id="loadoutEquipment" class="module-grid" style="margin-top:10px"><div class="module-empty">Primary weapon and armor suggestions will appear here.</div></div>
  <div class="module-disclaimer" data-i18n="loadoutDisclaimer">Recommendations are heuristic suggestions based on tags and mission context—not live win-rate statistics, a guarantee, or an official build.</div>
</section>

<section class="module-grid module-wrap">
  <section class="panel" id="community">
    <div class="panel-title"><div><h3 data-i18n="communityTitle">Community intel / Operational picture</h3><div class="small-note" data-i18n="communitySub">Derived only from currently synchronized public feeds.</div></div><span class="tag">LIVE SNAPSHOT</span></div>
    <div class="module-kpis" id="communityKpis"><div class="module-empty">Waiting for synchronized data.</div></div>
    <div class="module-list" id="communityDetails" style="margin-top:12px"></div>
  </section>
  <section class="panel" id="alerts">
    <div class="panel-title"><div><h3 data-i18n="alertsTitle">Operational alerts</h3><div class="small-note" data-i18n="alertsSub">Optional browser notifications while this dashboard is open.</div></div><span class="tag hot">LOCAL ALERTS</span></div>
    <div class="alert-settings"><div><strong data-i18n="browserNotifications">Browser notifications</strong><p id="alertStatus" aria-live="polite">Notifications are disabled.</p></div><div class="module-actions"><button class="btn primary" id="enableAlerts" type="button" data-i18n="enableAlerts">Enable alerts</button><button class="btn" id="disablePush" type="button" data-i18n="disablePush">Disable push</button></div></div>
    <div class="module-list" style="margin-top:12px">
      <label class="module-row"><span><strong data-i18n="alertOrders">New major orders</strong><small data-i18n="alertOrdersSub">Notify when an order changes after initial sync.</small></span><input type="checkbox" id="alertOrderToggle" checked></label>
      <label class="module-row"><span><strong data-i18n="alertDispatches">New dispatches</strong><small data-i18n="alertDispatchesSub">Notify when a new galactic dispatch arrives.</small></span><input type="checkbox" id="alertDispatchToggle" checked></label>
      <label class="module-row"><span><strong data-i18n="alertAttacks">New planetary attacks</strong><small data-i18n="alertAttacksSub">Notify when a tracked planet becomes attacked.</small></span><input type="checkbox" id="alertAttackToggle" checked></label>
    </div>
    <p class="module-disclaimer" data-i18n="alertsDisclaimer">These are local browser alerts based on dashboard polling. True server push while the site is closed requires VAPID keys, subscription storage, and a push delivery service.</p>
  </section>
</section>

<section class="panel module-wrap" id="archives">
  <div class="panel-title"><div><h3 data-i18n="archivesTitle">Galactic archives / Timeline</h3><div class="small-note" data-i18n="archivesSub">Dispatches, official news, orders and current planetary events.</div></div><span class="tag">FIELD RECORDS</span></div>
  <div class="module-toolbar"><input class="search" id="archiveSearch" type="search" placeholder="Search archive..." aria-label="Search archives"><select class="search" id="archiveType" style="max-width:180px" aria-label="Filter archive"><option value="all">All records</option><option value="dispatch">Dispatches</option><option value="news">Official news</option><option value="order">Orders</option><option value="event">Planetary events</option><option value="history">Historical snapshots</option></select></div>
  <div id="archiveList" class="module-list"><div class="module-empty">Waiting for archive feeds.</div></div>
</section>

<section class="panel module-wrap" id="catalog">
  <div class="panel-title"><div><h3 data-i18n="catalogTitle">Field manual / Equipment & enemy catalog</h3><div class="small-note" data-i18n="catalogSub">Searchable reference catalog; recommendations are tagged, not live game telemetry.</div></div><span class="tag">CODEX</span></div>
  <div class="module-toolbar"><input class="search" id="catalogSearch" type="search" placeholder="Search weapons, armor, stratagems or enemies..." aria-label="Search catalog"></div>
  <div class="module-tabs" id="catalogTabs" role="group" aria-label="Catalog category">
    <button class="module-tab" data-catalog="stratagems" aria-pressed="true" type="button">Stratagems</button><button class="module-tab" data-catalog="weapons" aria-pressed="false" type="button">Weapons</button><button class="module-tab" data-catalog="armor" aria-pressed="false" type="button">Armor</button><button class="module-tab" data-catalog="enemies" aria-pressed="false" type="button">Enemies</button>
  </div>
  <div id="catalogList" class="module-grid"><div class="module-empty">Loading catalog…</div></div>
  <p class="module-disclaimer" data-i18n="catalogDisclaimer">Catalog entries are a maintained starter reference. Unlock availability, balance, and patch changes should be verified in-game.</p>
</section>

<nav class="mobile-nav" aria-label="Quick navigation"><a href="#planets"><span>◎</span>War map</a><a href="#orders"><span>⌁</span>Orders</a><a href="#loadout"><span>⚔</span>Loadout</a><a href="#archives"><span>▤</span>Archives</a><a href="#catalog"><span>◈</span>Codex</a></nav>
<footer class="footer"><span>GALACTIC COMMAND // FOR THE GLORY OF SUPER EARTH</span><span>Data provided by configured upstream feeds · <span id="footerUpdated">Waiting for sync</span></span></footer>
</div>
<script>
(() => {
  const $ = id => document.getElementById(id);
  const basePath = (() => { const p = window.location.pathname; if (p.endsWith('/')) return p; return p.replace(/[^/]*$/, ''); })();
  const endpoint = path => `${basePath}${path.replace(/^\//, '')}`;
  const state = { planets: [], shown: 12, lastUpdate: null, feeds: {}, feedMeta: {} };
  const fmt = n => { const v = Number(n); return Number.isFinite(v) ? new Intl.NumberFormat('en-US',{notation:v>=1e6?'compact':'standard',maximumFractionDigits:1}).format(v) : '—'; };
  const cleanText = s => String(s ?? '').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi,' ').replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi,' ').replace(/<[^>]*>/g,' ').replace(/&nbsp;/gi,' ').replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&quot;/gi,'"').replace(/&#39;|&apos;/gi,"'").replace(/\s+/g,' ').trim();
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const unbox = x => x && typeof x === 'object' && Object.prototype.hasOwnProperty.call(x,'data') ? x.data : x;
  async function get(path) { const r = await fetch(endpoint(path), {headers:{'Accept':'application/json'}, cache:'no-store'}); const j = await r.json(); if (!r.ok || j?.ok === false) throw new Error(j?.error?.message || `HTTP ${r.status}`); return {data:unbox(j),meta:j?.meta||{}}; }
  function safeUrl(value) { try { const u=new URL(String(value),window.location.origin); return ['https:','http:'].includes(u.protocol)?u.href:''; } catch (_) { return ''; } }
  function first(o, keys, fallback=null) { for (const k of keys) if (o && o[k] !== undefined && o[k] !== null) return o[k]; return fallback; }
  function arr(v) { if (Array.isArray(v)) return v; if (v && typeof v === 'object') { for (const k of ['items','results','data','planets','campaigns','assignments','dispatches','news']) if (Array.isArray(v[k])) return v[k]; } return []; }
  function nameOf(v) { if (typeof v === 'string') return v; if (v && typeof v === 'object') return first(v,['name','title','value','faction'], 'Unknown'); return v == null ? 'Unknown' : String(v); }
  function dateText(v) { if (!v) return ''; const n = typeof v === 'number' ? (v < 1e12 ? v*1000 : v) : Date.parse(v); if (!Number.isFinite(n)) return String(v); return new Date(n).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}); }
  function getStats(w) { return first(w,['statistics','stats','galaxyStats'],{}) || {}; }
  function setText(id,v) { $(id).textContent = v == null || v === '' ? '—' : String(v); }
  function healthPct(p) { const health=Number(first(p,['health','currentHealth'],NaN)), max=Number(first(p,['maxHealth','maximumHealth'],NaN)); if (Number.isFinite(health)&&Number.isFinite(max)&&max>0) return Math.max(0,Math.min(100,health/max*100)); const prog=Number(first(p,['liberation','liberationPercent','progress'],NaN)); return Number.isFinite(prog)?Math.max(0,Math.min(100,prog)):null; }
  function isAttacked(p) { const v=first(p,['attacking','isAttacked','underAttack'],false); if(Array.isArray(v)) return v.length>0; if(v && typeof v==='object') return Object.keys(v).length>0; return v===true || v===1 || v==='true'; }
  function hasEvent(p) { const ev=p?.event; return ev !== null && ev !== undefined && ev !== false && ev !== '' && !(typeof ev==='object' && !Object.keys(ev).length); }
  function renderGalaxyMap() {
    const points=state.planets.map(p=>{const pos=p.position;let x,y;if(Array.isArray(pos)){x=Number(pos[0]);y=Number(pos[1]);}else if(pos&&typeof pos==='object'){x=Number(first(pos,['x','X','horizontal','longitude'],NaN));y=Number(first(pos,['y','Y','vertical','latitude'],NaN));}return {p,x,y};}).filter(v=>Number.isFinite(v.x)&&Number.isFinite(v.y));
    const box=$('galaxyMap');if(!points.length){box.innerHTML='<div class="map-caption">Schematic planetary theatre · select a marker</div><div class="map-empty">The current planet payload does not expose readable x/y coordinates. Use the searchable planetary list below.</div>';return;}
    const xs=points.map(v=>v.x),ys=points.map(v=>v.y),minX=Math.min(...xs),maxX=Math.max(...xs),minY=Math.min(...ys),maxY=Math.max(...ys);const nx=v=>35+(maxX===minX?0:(v-minX)/(maxX-minX)*730),ny=v=>25+(maxY===minY?0:(v-minY)/(maxY-minY)*155);
    const dots=points.map(({p,x,y})=>{const attacked=isAttacked(p),event=hasEvent(p);return `<circle class="map-dot" cx="${nx(x)}" cy="${ny(y)}" r="${attacked?4.4: event?3.7:2.7}" fill="${attacked?'#f2bd55':event?'#8ed9ef':'#c7ed63'}" data-planet="${esc(p.name||'')}"><title>${esc(p.name||'Unknown planet')} · ${esc(p.sector||'Unknown sector')}</title></circle>`;}).join('');
    box.innerHTML=`<div class="map-caption">Schematic planetary theatre · select a marker</div><svg viewBox="0 0 800 205" role="img" aria-label="Schematic map of planetary positions"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="#ffffff" stroke-opacity=".07" stroke-width="1"/></pattern></defs><rect width="800" height="205" fill="url(#grid)"/><ellipse cx="400" cy="102" rx="290" ry="65" fill="none" stroke="#c7ed63" stroke-opacity=".12"/><ellipse cx="400" cy="102" rx="190" ry="40" fill="none" stroke="#c7ed63" stroke-opacity=".10"/>${dots}</svg>`;
    box.querySelectorAll('.map-dot').forEach(dot=>dot.addEventListener('click',()=>{$('planetSearch').value=dot.dataset.planet||'';state.shown=12;renderPlanets();$('planetList').scrollIntoView({behavior:'smooth',block:'start'});}));
  }
  function renderPlanets() {
    const query=$('planetSearch').value.trim().toLowerCase(), filter=$('planetFilter').value;
    const filtered=state.planets.filter(p=>{ const hay=[p.name,p.sector,nameOf(p.biome),nameOf(p.currentOwner),nameOf(p.initialOwner),...(Array.isArray(p.hazards)?p.hazards.map(nameOf):[])].join(' ').toLowerCase(); if(query&&!hay.includes(query))return false; if(filter==='attacked'&&!isAttacked(p))return false; if(filter==='event'&&!hasEvent(p))return false; if(filter==='progress'&&healthPct(p)===null)return false; return true; });
    setText('planetCount',`${filtered.length.toLocaleString()} planets matching · ${state.planets.length.toLocaleString()} total`);
    const shown=filtered.slice(0,state.shown);
    $('planetList').innerHTML=shown.length?shown.map(p=>{const pct=healthPct(p),owner=nameOf(p.currentOwner||p.initialOwner),biome=nameOf(p.biome),attacked=isAttacked(p),event=hasEvent(p); const h=pct===null?'Progress unavailable':`${Math.round(pct)}% health remaining`; return `<article class="planet"><div class="planet-top"><div><div class="planet-name">${esc(p.name||`Planet ${p.index??'—'}`)}</div><div class="planet-meta">${esc(p.sector||'Unknown sector')} · ${esc(biome)}</div></div><span class="tag ${attacked||event?'hot':'good'}">${attacked?'UNDER ATTACK':event?'ACTIVE EVENT':esc(owner)}</span></div><div class="meter"><span style="width:${pct===null?0:pct}%"></span></div><div class="planet-foot"><span>${esc(h)}</span><span>${esc(owner)}${p.index!==undefined?' · Index '+esc(p.index):''}</span></div></article>`;}).join(''):'<div class="empty">No planets match those filters. Try another search.</div>';
    $('morePlanets').classList.toggle('hide',filtered.length<=state.shown); $('morePlanets').textContent=`Show more planets (${Math.max(0,filtered.length-state.shown)} remaining)`;
  }
  function renderOrders(data) { const items=arr(data); $('orders').innerHTML=items.length?items.slice(0,4).map(o=>{const title=cleanText(first(o,['title','name'],'Major Order'));const desc=cleanText(first(o,['briefing','description','message'],'Current operational directive.'));const progress=Array.isArray(o.progress)?o.progress.join(' / '):first(o,['progressText','status'],'Active');const expiry=first(o,['expiration','expiresAt','expiry']);return `<article class="order"><h4>${esc(title)}</h4><p>${esc(desc)}</p><div class="order-bottom"><span>${esc(String(progress).slice(0,100))}</span><span>${expiry?'Ends '+esc(dateText(expiry)):'ORDER ACTIVE'}</span></div></article>`;}).join(''):'<div class="empty">No order data has been synchronized yet.</div>'; }
  function renderCampaigns(data) { const items=arr(data); setText('campaignCount',`${items.length} reported`); $('campaigns').innerHTML=items.length?items.slice(0,7).map(c=>{const p=c.planet||{};return `<div class="campaign"><strong>${esc(nameOf(p.name? p : first(c,['planet','name'],'Campaign')))}</strong><span>${esc(nameOf(first(c,['faction','type'],'Operation')))}${c.count!==undefined?' · '+esc(c.count):''}</span></div>`;}).join(''):'<div class="empty">No active campaigns in the latest synchronized payload.</div>'; }
  function renderDispatches(data) { const items=arr(data); $('dispatches').innerHTML=items.length?items.slice().sort((a,b)=>Number(first(b,['published','publishedAt','id'],0))-Number(first(a,['published','publishedAt','id'],0))).slice(0,5).map(d=>`<article class="feed-item"><div class="meta">DISPATCH ${esc(first(d,['id','Id'],'—'))} ${first(d,['published','Published'])?' · '+esc(dateText(first(d,['published','Published']))):''}</div><h4>${esc(cleanText(first(d,['title'],'Galactic dispatch')))}</h4><p>${esc(cleanText(first(d,['message','Message','content'],'No message text available.')))}</p></article>`).join(''):'<div class="empty">No dispatches synchronized yet.</div>'; }
  function renderSteam(data) { const items=arr(data); $('steamNews').innerHTML=items.length?items.slice(0,5).map(n=>{const title=cleanText(first(n,['title'],'Official update'));const url=safeUrl(first(n,['url','link']));return `<article class="feed-item"><div class="meta">${esc(first(n,['author'],'HELLDIVERS 2'))}${first(n,['publishedAt','published'])?' · '+esc(dateText(first(n,['publishedAt','published']))):''}</div><h4>${url?`<a href="${esc(url)}" target="_blank" rel="noopener noreferrer">${esc(title)}</a>`:esc(title)}</h4><p>${esc(cleanText(first(n,['content','description','summary'],'')).slice(0,240))}</p></article>`;}).join(''):'<div class="empty">No official news synchronized yet.</div>'; }
  function renderStations(data) { const items=arr(data); const s=items[0]||data; if(!s||typeof s!=='object'||!Object.keys(s).length){$('station').innerHTML='<div class="empty">No station data available.</div>';return;} const p=s.planet||{};const actions=Array.isArray(s.tacticalActions)?s.tacticalActions:[];$('station').innerHTML=`<div class="order"><h4>${esc(nameOf(p.name?p:first(s,['name'],'Mobile Space Station')))}</h4><p>Station ID32: ${esc(first(s,['id32'],'—'))}</p><div class="order-bottom"><span>${actions.length} tactical actions</span><span>${s.electionEnd?'Election ends '+esc(dateText(s.electionEnd)):'Strategic asset'}</span></div>${actions.length?`<div style="margin-top:10px">${actions.slice(0,4).map(a=>`<span class="tag" style="margin:0 5px 5px 0">${esc(nameOf(first(a,['name','title','id'],a)))}</span>`).join('')}</div>`:''}</div>`; }
  function renderWar(data, meta) { const w=data&&typeof data==='object'?data:{};const s=getStats(w);const players=first(s,['playerCount','players','activePlayers']);const won=first(s,['missionsWon','missions_won']);const killed=['terminidKills','automatonKills','illuminateKills'].reduce((sum,k)=>sum+(Number(s[k])||0),0);const acc=first(s,['accuracy','missionAccuracy']);setText('players',players===undefined?'—':fmt(players));setText('missionsWon',won===undefined?'—':fmt(won));setText('kills',killed?fmt(killed):'—');setText('accuracy',acc===undefined?'—':`${Number(acc)<=1?Math.round(Number(acc)*100):Math.round(Number(acc))}%`);const now=first(w,['now','time']);const planets=state.planets;const attacks=planets.filter(isAttacked).length;setText('warStatus',planets.length?`${attacks} FRONT${attacks===1?'':'S'} ACTIVE`:'WAR FEED ONLINE');setText('warSub',first(w,['clientVersion'])?`Client version ${w.clientVersion} · Galactic status received`:'Live galactic data received');setText('warMeterLabel',planets.length?`${attacks} planets under attack / ${planets.length} tracked`:'Awaiting planet feed');$('warMeter').style.width=planets.length?`${Math.min(100,attacks/planets.length*100)}%`:'0%';setText('warClock',now?dateText(now).toUpperCase():'LIVE FEED');setText('warUpdated',meta?.source_fetched_at?`SYNC ${dateText(meta.source_fetched_at)}`:'SYNCED');setText('warIntelState',planets.length?'Planet data loaded':'Awaiting sync');setText('archiveState','Dispatch feed '+(state.lastUpdate?'loaded':'pending')); }
  async function loadOne(path, success, fail) { try {const result=await get(path);const tail=path.split('/').pop();const aliases={'steam':'steam_news','steam-news':'steam_news','news':'dispatches','orders':'assignments'};state.feeds[aliases[tail]||tail]=result.data;state.feedMeta[path]=result.meta;success(result.data,result.meta);return result;}catch(err){if(fail){const fallback=await fail(err);return fallback??null;}return null;} }
  async function loadAll() { $('connection').textContent='SYNCING GALACTIC NETWORK';$('connection').style.color='';$('refreshBtn').disabled=true; $('refreshBtn').textContent='↻ Syncing'; const results=await Promise.allSettled([
    loadOne('api/v1/war',(d,m)=>{renderWar(d,m);}),
    loadOne('api/v1/planets',(d)=>{state.planets=arr(d);renderGalaxyMap();renderPlanets();if(state.feeds.war)renderWar(state.feeds.war,state.feedMeta['api/v1/war']);}),
    loadOne('api/v1/assignments',renderOrders,()=>loadOne('api/v1/orders',renderOrders,err=>{$('orders').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/campaigns',renderCampaigns,err=>{$('campaigns').innerHTML=`<div class="error">${esc(err.message)}</div>`;}),
    loadOne('api/v1/dispatches',renderDispatches,()=>loadOne('api/v1/news',renderDispatches,err=>{$('dispatches').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/steam',renderSteam,()=>loadOne('api/v1/steam-news',renderSteam,err=>{$('steamNews').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/space-stations',renderStations,err=>{$('station').innerHTML=`<div class="error">${esc(err.message)}</div>`;})
  ]); const ok=results.some(r=>r.status==='fulfilled'&&r.value!==null); state.lastUpdate=new Date(); $('connection').textContent=ok?'GALACTIC NETWORK CONNECTED':'API ONLINE · DATA SYNC REQUIRED';$('connection').style.color=ok?'var(--acid)':'var(--gold)';setText('footerUpdated',`Last checked ${state.lastUpdate.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}`);setText('warUpdated',state.lastUpdate.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}));window.dispatchEvent(new Event('gc:data-updated'));$('refreshBtn').disabled=false;$('refreshBtn').textContent='↻ Refresh'; }
  $('planetSearch').addEventListener('input',()=>{state.shown=12;renderPlanets();});$('planetFilter').addEventListener('change',()=>{state.shown=12;renderPlanets();});$('morePlanets').addEventListener('click',()=>{state.shown+=12;renderPlanets();});$('refreshBtn').addEventListener('click',loadAll);
  // API routes are queried independently so one unavailable feed does not blank the whole page.
  window.GCBridge = {state, get, arr, first, nameOf, esc, fmt, healthPct, isAttacked, hasEvent, dateText};
  loadAll(); setInterval(loadAll,120000);
})();
</script><script src="assets/modules.js" defer></script></body></html>
HTML;
    exit;
}

if ($path === '/api/v1/health') respond(['service'=>'Galactic Command API','status'=>'up']);

if ($path === '/api/v1/history') {
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 30)));
    $warQ = $pdo->prepare('SELECT captured_at,war_id,summary_json FROM war_snapshots ORDER BY id DESC LIMIT '.$limit);
    $warQ->execute();
    $war = array_map(static function(array $r): array {
        return ['captured_at'=>$r['captured_at'],'war_id'=>$r['war_id'],'summary'=>json_decode((string)$r['summary_json'],true) ?: []];
    }, $warQ->fetchAll());
    $planetQ = $pdo->prepare('SELECT ps.captured_at,p.upstream_index,p.name,ps.health,ps.max_health,ps.player_count FROM planet_snapshots ps JOIN planets p ON p.id=ps.planet_id ORDER BY ps.id DESC LIMIT '.$limit);
    $planetQ->execute();
    respond(['war_snapshots'=>$war,'planet_snapshots'=>$planetQ->fetchAll()],200,['feature'=>'history','limit'=>$limit]);
}

$map = [
    '/api/v1/war' => 'war',
    '/api/v1/planets' => 'planets',
    '/api/v1/assignments' => 'assignments',
    '/api/v1/orders' => 'assignments',
    '/api/v1/campaigns' => 'campaigns',
    '/api/v1/dispatches' => 'dispatches',
    '/api/v1/news' => 'dispatches',
    '/api/v1/steam' => 'steam_news',
    '/api/v1/steam-news' => 'steam_news',
    '/api/v1/space-stations' => 'space_stations',
    '/api/v1/planet-events' => 'planet_events',
];
try {
    if (isset($map[$path])) {
        $source = $map[$path];
        $q = $pdo->prepare('SELECT fetched_at,http_status,payload_json FROM source_payloads WHERE source_key=? AND entity_key IS NULL ORDER BY id DESC LIMIT 1');
        $q->execute([$source]); $r = $q->fetch();
        if (!$r) respondError(503, 'data_not_ready', 'No synchronized data is available yet.');
        $data = json_decode($r['payload_json'], true);
        respond(is_array($data) ? $data : [], 200, ['source_key'=>$source,'source_fetched_at'=>$r['fetched_at'],'source_http_status'=>(int)$r['http_status'],'contract_version'=>'raw-v1']);
    }
    if (preg_match('#^/api/v1/space-stations/([0-9]+)$#', $path, $m)) {
        $id32 = $m[1]; $q = $pdo->prepare("SELECT fetched_at,http_status,payload_json FROM source_payloads WHERE source_key='space_station_by_index' AND entity_key=? ORDER BY id DESC LIMIT 1");
        $q->execute([$id32]); $r = $q->fetch();
        if (!$r) respondError(503, 'data_not_ready', 'Station detail has not been synchronized. Run sync with --source=space_station_by_index --id32='.$id32.'.');
        respond(json_decode($r['payload_json'], true), 200, ['source_key'=>'space_station_by_index','id32'=>$id32,'source_fetched_at'=>$r['fetched_at']]);
    }
    if (preg_match('#^/api/v1/planets/([0-9]+)$#', $path, $m)) {
        $q = $pdo->prepare('SELECT raw_json FROM planets WHERE upstream_index=? LIMIT 1'); $q->execute([(int)$m[1]]); $r = $q->fetch();
        if (!$r) respondError(404, 'planet_not_found', 'Planet index not found.');
        respond(json_decode($r['raw_json'], true), 200, ['planet_index'=>(int)$m[1]]);
    }
    respondError(404, 'not_found', 'Endpoint not found.');
} catch (Throwable $e) {
    error_log('[Galactic Command API] '.$e->getMessage());
    respondError(500, 'internal_error', 'An unexpected server error occurred.');
}
