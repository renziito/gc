<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';

function respond(array $data, int $status=200, array $meta=[]): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=30, stale-while-revalidate=60');
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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    respondError(405, 'method_not_allowed', 'Only GET is supported.');
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) $path = substr($path, strlen($scriptDir));
$path = '/' . trim($path, '/');
if ($path === '//') $path = '/';

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
<style>
:root{color-scheme:dark;--bg:#0b0f0b;--panel:#141a13;--panel2:#1b2318;--line:#303b29;--text:#f0f3e8;--muted:#a5ae99;--acid:#c7ed63;--gold:#f2bd55;--red:#ff7770;--blue:#8ed9ef;--radius:16px;--shadow:0 12px 30px #0005}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(ellipse at 70% -10%,#354326 0,transparent 40%),var(--bg);color:var(--text);font:15px/1.5 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}a{color:inherit}button,input,select{font:inherit}button{cursor:pointer}.shell{max-width:1440px;margin:auto;padding:0 20px 44px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 0;border-bottom:1px solid var(--line)}.brand{display:flex;align-items:center;gap:12px;min-width:0}.logo{display:grid;place-items:center;width:44px;height:44px;background:var(--acid);color:#11170b;font-weight:1000;font-size:22px;clip-path:polygon(50% 0,94% 18%,88% 77%,50% 100%,12% 77%,6% 18%)}.brandname{font-weight:900;letter-spacing:.12em;font-size:14px}.brandname small{display:block;color:var(--muted);font-weight:600;letter-spacing:.16em;font-size:10px}.status{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px}.dot{width:8px;height:8px;border-radius:50%;background:var(--acid);box-shadow:0 0 12px #c7ed6380}.top-actions{display:flex;align-items:center;gap:8px}.btn{border:1px solid var(--line);border-radius:10px;padding:9px 12px;background:#ffffff08;color:var(--text);font-weight:700}.btn:hover{border-color:var(--acid)}.btn.primary{background:var(--acid);color:#11170b;border-color:var(--acid)}.hero{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(260px,.7fr);gap:18px;padding:26px 0 20px}.eyebrow{color:var(--acid);font-weight:900;letter-spacing:.2em;font-size:11px;text-transform:uppercase}.hero h1{font-size:clamp(30px,5vw,58px);line-height:1.02;letter-spacing:-.045em;margin:9px 0 12px;max-width:800px}.hero p{color:var(--muted);max-width:700px;margin:0}.war-card,.panel,.pillar{background:linear-gradient(145deg,#1b2417,#111610);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}.war-card{padding:20px;display:flex;flex-direction:column;justify-content:space-between;min-height:180px}.war-head{display:flex;justify-content:space-between;gap:12px;align-items:center}.war-title{font-size:12px;letter-spacing:.14em;font-weight:900;color:var(--muted)}.war-clock{font-variant-numeric:tabular-nums;font-size:12px;color:var(--acid)}.war-value{font-size:clamp(27px,4vw,42px);font-weight:900;letter-spacing:-.04em;margin:12px 0 2px}.war-sub{font-size:12px;color:var(--muted)}.meter{height:8px;background:#080b07;border:1px solid #34402c;border-radius:99px;overflow:hidden;margin:13px 0 8px}.meter span{display:block;height:100%;width:0;background:linear-gradient(90deg,#85b63d,var(--acid));border-radius:99px;transition:width .4s}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:4px 0 24px}.stat{padding:15px;border:1px solid var(--line);background:#ffffff04;border-radius:13px;min-width:0}.stat .label{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.1em;font-weight:800}.stat .value{font-size:clamp(19px,2.5vw,28px);font-weight:900;margin-top:4px;overflow-wrap:anywhere}.stat .hint{font-size:11px;color:var(--muted)}.section-head{display:flex;align-items:end;justify-content:space-between;gap:14px;margin:30px 0 14px}.section-head h2{font-size:19px;letter-spacing:-.02em;margin:0}.section-head p{margin:4px 0 0;color:var(--muted);font-size:12px}.pillars{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.pillar{padding:18px;min-height:142px;box-shadow:none}.pillar .number{color:var(--acid);font-size:11px;font-weight:900;letter-spacing:.18em}.pillar h3{margin:8px 0 5px;font-size:17px}.pillar p{margin:0;color:var(--muted);font-size:13px}.grid-main{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr);gap:14px;margin-top:14px}.panel{padding:18px;min-width:0}.panel-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.panel-title h3{font-size:16px;margin:0}.small-note{font-size:11px;color:var(--muted)}.search-row{display:flex;gap:8px;margin-bottom:12px}.search{width:100%;min-width:0;border:1px solid var(--line);background:#090d08;color:var(--text);border-radius:10px;padding:10px 12px;outline:none}.search:focus{border-color:var(--acid)}.planet-list,.feed-list,.order-list{display:grid;gap:9px}.galaxy-map{position:relative;margin:0 0 12px;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:radial-gradient(ellipse at 50% 50%,#28371d 0, #11180f 55%,#090d08 100%);min-height:185px}.galaxy-map svg{display:block;width:100%;height:205px}.map-caption{position:absolute;left:10px;top:9px;font-size:9px;letter-spacing:.15em;color:var(--muted);font-weight:900;text-transform:uppercase}.map-dot{cursor:pointer;stroke:#c7ed6380;stroke-width:1.5}.map-dot:hover{stroke:#fff;stroke-width:3}.map-empty{padding:22px;text-align:center;color:var(--muted);font-size:12px}.planet{padding:12px;border:1px solid var(--line);border-radius:12px;background:#ffffff03}.planet-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.planet-name{font-weight:850}.planet-meta{font-size:11px;color:var(--muted);margin-top:2px}.tag{display:inline-flex;align-items:center;border:1px solid var(--line);border-radius:99px;padding:3px 8px;font-size:10px;font-weight:800;color:var(--muted);white-space:nowrap}.tag.hot{color:var(--gold);border-color:#7d5e2a}.tag.good{color:var(--acid);border-color:#52672b}.planet .meter{margin:9px 0 4px;height:6px}.planet-foot{display:flex;justify-content:space-between;gap:10px;color:var(--muted);font-size:11px}.feed-item{border-bottom:1px solid #ffffff10;padding:0 0 12px;margin-bottom:12px}.feed-item:last-child{border:0;margin:0;padding:0}.feed-item h4{font-size:13px;line-height:1.35;margin:0 0 4px}.feed-item p{font-size:12px;color:var(--muted);margin:0;overflow-wrap:anywhere}.feed-item .meta{font-size:10px;color:var(--acid);margin-bottom:4px;letter-spacing:.06em}.order{padding:13px;border:1px solid var(--line);border-radius:12px;background:#ffffff03}.order h4{margin:0 0 5px;font-size:14px}.order p{margin:0;color:var(--muted);font-size:12px}.order-bottom{display:flex;justify-content:space-between;gap:10px;margin-top:10px;font-size:11px;color:var(--muted)}.campaign-list{display:grid;gap:8px}.campaign{display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #ffffff10;padding:8px 0}.campaign:last-child{border:0}.campaign strong{font-size:12px}.campaign span{font-size:11px;color:var(--muted);text-align:right}.two-col{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}.empty,.error{padding:15px;border:1px dashed var(--line);border-radius:12px;color:var(--muted);font-size:12px}.error{border-color:#8c3934;color:#ffaaa5}.footer{display:flex;justify-content:space-between;gap:12px;padding:25px 0 0;margin-top:28px;border-top:1px solid var(--line);font-size:11px;color:var(--muted)}.loading{color:var(--muted);font-size:12px}.hide{display:none!important}.mobile-nav{display:none}
@media(max-width:900px){.hero{grid-template-columns:1fr}.war-card{min-height:150px}.grid-main{grid-template-columns:1fr}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.two-col{grid-template-columns:1fr}}
@media(max-width:600px){.shell{padding:0 13px 30px}.topbar{padding:13px 0}.brandname{font-size:12px}.logo{width:38px;height:38px}.status{display:none}.top-actions .btn{padding:8px 9px;font-size:12px}.hero{padding:23px 0 14px;gap:13px}.hero h1{font-size:36px}.hero p{font-size:13px}.war-card{padding:16px}.stats{gap:8px;margin-bottom:20px}.stat{padding:12px}.stat .label{font-size:9px}.stat .hint{font-size:10px}.section-head{margin:23px 0 12px}.section-head h2{font-size:17px}.pillars{grid-template-columns:1fr;gap:8px}.pillar{min-height:0;padding:14px}.pillar h3{margin:5px 0 3px}.panel{padding:14px}.two-col{gap:10px}.footer{flex-direction:column}.mobile-nav{position:sticky;bottom:8px;z-index:10;display:flex;justify-content:space-around;gap:5px;padding:7px;background:#10160feF;border:1px solid var(--line);border-radius:14px;backdrop-filter:blur(12px);margin-top:20px}.mobile-nav a{text-decoration:none;text-align:center;font-size:10px;color:var(--muted);padding:4px}.mobile-nav a span{display:block;font-size:16px}.planet-foot{font-size:10px}}

/* Tactical Helldivers-inspired skin; preserve original map, feeds, API and push logic */
:root{--bg:#08090a;--panel:#111416;--panel2:#191d20;--line:#41474b;--text:#f2f1e8;--muted:#a7adb0;--acid:#f2d400;--gold:#ffad28;--red:#ff5c64;--blue:#6bc9ff;--radius:2px;--shadow:0 8px 22px #0009}
body{background:#08090a;color:var(--text);font-family:"Arial Narrow","Roboto Condensed",system-ui,sans-serif}
body:before{content:"";position:fixed;inset:0;pointer-events:none;z-index:-1;opacity:.18;background:repeating-linear-gradient(135deg,#ffffff08 0 2px,transparent 2px 13px),radial-gradient(ellipse at 50% 15%,#202a30 0,transparent 55%)}
.shell{max-width:1600px;padding:0 18px 32px}.topbar{position:sticky;top:0;z-index:30;margin:0 -18px;padding:10px 18px;background:#090a0bf5;border-bottom:2px solid #4b4d4e;box-shadow:0 4px 18px #000b}
.logo{width:48px;height:42px;clip-path:none;background:transparent;color:var(--acid);border:2px solid var(--acid)}.brandname{text-transform:uppercase;letter-spacing:.09em}.brandname small{color:var(--blue)}
.btn{border-radius:0;background:#17191a;border:1px solid #64686a;text-transform:uppercase;letter-spacing:.05em;font-size:11px;clip-path:polygon(0 0,calc(100% - 7px) 0,100% 7px,100% 100%,0 100%)}.btn:hover{border-color:var(--acid);color:var(--acid)}.btn.primary{background:var(--acid);color:#111;border-color:var(--acid)}
.hero{padding:20px 0 15px}.eyebrow{color:var(--acid)}.hero h1{text-transform:uppercase;letter-spacing:-.025em}
.war-card,.panel,.pillar,.stat{border-radius:0;background:linear-gradient(135deg,#171a1c,#0e1011);border:1px solid #555b5e;box-shadow:none;position:relative}
.war-card:before,.panel:before,.pillar:before{content:"";position:absolute;top:-1px;left:-1px;width:22px;height:4px;background:var(--acid)}
.war-value{color:var(--blue)}.meter{border-radius:0;background:#070808;border-color:#555b5e}.meter span{border-radius:0;background:repeating-linear-gradient(135deg,#f2d400 0 6px,#c9b100 6px 10px)}
.stats{gap:8px}.stat{padding:12px 14px}.stat .label{color:#c7ccce;letter-spacing:.1em;text-transform:uppercase}
.section-head{border-bottom:1px solid #454a4c;padding-bottom:9px}.section-head h2{text-transform:uppercase;letter-spacing:.1em}.pillars{gap:8px}.pillar{padding:13px 15px}.pillar .number{color:var(--acid)}.pillar h3{text-transform:uppercase;font-size:14px}
.panel{padding:14px;min-width:0}.panel-title{padding-bottom:8px;border-bottom:1px solid #3e4447}.panel-title h3{text-transform:uppercase;letter-spacing:.08em;font-size:14px}
.tag{border-radius:0;border-color:#5a6062;text-transform:uppercase;letter-spacing:.06em}.tag.hot{color:#ffb12e;border-color:#8d6223}.tag.good{color:#75d9ff;border-color:#316a82}
.search{border-radius:0;background:#080a0b;border-color:#4d5356}.search:focus{border-color:var(--acid)}
.galaxy-map{border-radius:0;border-color:#51575a;background:radial-gradient(ellipse at 50% 50%,#24333c 0,#111719 53%,#08090a 100%);min-height:260px}.galaxy-map svg{height:300px}.map-caption{color:var(--acid)}.map-dot{stroke:var(--blue)}
.planet,.order{border-radius:0;background:#151819;border-color:#484e51}.planet-name,.order h4{text-transform:uppercase}.planet .meter span{background:var(--blue)}
.feed-item{border-bottom:1px solid #363c3f}.feed-item .meta{color:var(--acid)}.feed-item h4{text-transform:uppercase}.campaign{border-bottom-color:#3b4144}.footer{border-top:1px solid #4b5053}
.incoming-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;margin:0 -18px;padding:7px 18px;background:#101315;border-bottom:1px solid #555b5e}
.incoming-card{display:flex;align-items:center;gap:9px;min-height:48px;padding:8px 10px;background:#191d20;border:1px solid #4a5154;border-left:3px solid var(--blue);color:#e6e8e7;text-align:left;overflow:hidden}
.incoming-card.order-msg{border-left-color:#ffad28}.incoming-card.dispatch-msg{border-left-color:#d95cff}.incoming-card.news-msg{border-left-color:var(--acid)}
.incoming-icon{font-size:17px;flex:none}.incoming-copy{min-width:0}.incoming-copy strong{display:block;color:var(--blue);font-size:9px;letter-spacing:.1em;text-transform:uppercase}.incoming-copy span{display:block;font-size:11px;line-height:1.25;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.incoming-close{margin-left:auto;flex:none;border:0;background:transparent;color:#858c8f;font-size:18px;padding:0 2px}
.language-switch{display:flex;gap:2px;margin-left:4px;padding:3px;border:1px solid #4b5154;background:#111416}.language-switch button{border:0;background:transparent;color:#9da4a7;padding:6px 8px;font-size:10px;font-weight:900;letter-spacing:.08em}.language-switch button.active{background:var(--acid);color:#111}
.major-order-banner{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;margin:0 0 15px;padding:13px 16px;background:repeating-linear-gradient(135deg,#211b0c 0 8px,#15130d 8px 16px);border:1px solid #806b22;border-left:5px solid var(--acid)}
.major-order-banner .eyebrow{font-size:9px;letter-spacing:.16em}.major-order-banner h2{font-size:16px;margin:3px 0 4px;text-transform:uppercase;line-height:1.25}.major-order-banner p{margin:0;color:#c5c2b4;font-size:12px}.major-order-banner .order-state{font-size:10px;color:var(--acid);font-weight:900;text-transform:uppercase;letter-spacing:.1em;text-align:right}
.loadout-controls{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0 12px}.loadout-controls select{max-width:100%;flex:1;min-width:130px}.loadout-result{padding:10px;background:#0c0e0f;border:1px solid #454b4e}.loadout-result strong{color:var(--acid);text-transform:uppercase}.loadout-result p{font-size:12px;color:#b8bec0;margin:6px 0 0}
@media(max-width:900px){.incoming-strip{grid-template-columns:repeat(2,minmax(0,1fr))}.galaxy-map svg{height:250px}}
@media(max-width:600px){.shell{padding:0 10px 24px}.topbar{margin:0 -10px;padding:9px 10px}.brandname{font-size:11px}.logo{width:38px;height:34px}.status{display:none}.top-actions .btn{padding:7px;font-size:9px}.top-actions .btn.primary{display:none}.incoming-strip{margin:0 -10px;padding:6px 10px;grid-template-columns:1fr 1fr;gap:5px}.incoming-card{min-height:42px;padding:6px}.incoming-copy span{font-size:10px}.incoming-icon{font-size:14px}.language-switch{margin-left:0}.hero{padding:15px 0 12px}.hero h1{font-size:30px}.stats{gap:6px}.stat{padding:9px}.stat .label{font-size:9px}.pillars{grid-template-columns:1fr}.panel{padding:11px}.two-col{grid-template-columns:1fr}.galaxy-map svg{height:220px}.major-order-banner{grid-template-columns:1fr}.major-order-banner .order-state{text-align:left}}


/* App-like single-page navigation: views swap in place, no long document scroll */
html,body{height:100%;overflow:hidden}
body{min-height:100dvh}
.shell{height:100dvh;max-width:none;display:flex;flex-direction:column;overflow:hidden;padding-bottom:0}
.topbar{flex:none}
.incoming-strip{flex:none}
.app-nav{display:flex;flex:none;align-items:stretch;gap:5px;padding:7px 0;border-bottom:1px solid #454b4e;background:#0b0d0e}
.app-nav button{flex:1;min-width:0;padding:11px 12px;border:1px solid #3b4144;background:#171a1c;color:#aeb4b7;text-align:left;font-size:11px;font-weight:900;letter-spacing:.09em;text-transform:uppercase;position:relative}
.app-nav button:before{content:attr(data-number);display:inline-block;margin-right:9px;color:#7b8589}
.app-nav button.active{background:linear-gradient(180deg,#262a2c,#151819);border-color:var(--acid);color:var(--acid)}
.app-nav button.active:after{content:"";position:absolute;bottom:-1px;left:0;right:0;height:3px;background:var(--acid)}
.app-views{position:relative;flex:1;min-height:0;overflow:hidden}
.app-view{display:none;position:absolute;inset:0;overflow:auto;overscroll-behavior:contain;padding:14px 0 20px;scrollbar-color:#4d5559 #101213}
.app-view.active{display:block;animation:view-enter .16s ease-out}
@keyframes view-enter{from{opacity:.65;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
.app-view .hero{padding-top:4px}
.app-view .grid-main{margin-top:0}
.app-view .two-col{margin-top:0}
.app-view .section-head{margin-top:10px}
.app-view .panel{margin-bottom:12px}
.app-view .footer{display:none}
.app-view .major-order-banner{margin-top:0}
.app-view .galaxy-map svg{height:clamp(260px,40vh,430px)}
.app-view .grid-main{grid-template-columns:minmax(0,1.5fr) minmax(290px,.8fr)}
.app-view .two-col{grid-template-columns:repeat(2,minmax(0,1fr))}
.app-view .pillars{grid-template-columns:repeat(3,minmax(0,1fr))}
@media(max-width:850px){.app-view .grid-main{grid-template-columns:1fr}.app-view .galaxy-map svg{height:260px}.app-view .pillars{grid-template-columns:1fr}.app-view .two-col{grid-template-columns:1fr}}
@media(max-width:600px){.app-nav{gap:3px;padding:5px 0}.app-nav button{padding:9px 5px;text-align:center;font-size:9px;letter-spacing:0}.app-nav button:before{display:block;margin:0 0 3px;font-size:9px}.app-view{padding-top:10px}.incoming-strip{grid-template-columns:repeat(2,minmax(0,1fr))}.top-actions .language-switch{display:flex}}

</style>
</head>
<body><div class="shell">
<header class="topbar"><div class="brand"><div class="logo">GC</div><div class="brandname">GALACTIC COMMAND<small>HELLDIVERS 2 INTELLIGENCE</small></div></div><div class="status"><span class="dot"></span><span id="connection">CONNECTING TO GALACTIC NETWORK</span></div><div class="top-actions"><button class="btn" id="refreshBtn" type="button">↻ Refresh</button><div class="language-switch" role="group" aria-label="Language"><button type="button" data-lang="en" class="active">EN</button><button type="button" data-lang="es">ES</button></div></div></header>
<div class="incoming-strip" id="incomingStrip" aria-label="Incoming messages">
<button class="incoming-card order-msg" type="button" data-jump="orders"><span class="incoming-icon">✦</span><span class="incoming-copy"><strong data-i18n="incomingOrder">New major order</strong><span id="incomingOrderText" data-i18n="incomingWaiting">Waiting for High Command…</span></span><span class="incoming-close" aria-hidden="true">›</span></button>
<button class="incoming-card dispatch-msg" type="button" data-jump="dispatches"><span class="incoming-icon">◉</span><span class="incoming-copy"><strong data-i18n="incomingDispatch">New dispatch</strong><span id="incomingDispatchText" data-i18n="incomingDispatchWait">Monitoring dispatch channel…</span></span><span class="incoming-close" aria-hidden="true">›</span></button>
<button class="incoming-card news-msg" type="button" data-jump="steamNews"><span class="incoming-icon">▣</span><span class="incoming-copy"><strong data-i18n="incomingNews">Official news</strong><span id="incomingNewsText" data-i18n="incomingNewsWait">Monitoring official feed…</span></span><span class="incoming-close" aria-hidden="true">›</span></button>
<button class="incoming-card" type="button" data-jump="campaigns"><span class="incoming-icon">⌖</span><span class="incoming-copy"><strong data-i18n="incomingCampaign">Campaign intel</strong><span data-i18n="incomingCampaignWait">Checking active campaigns…</span></span><span class="incoming-close" aria-hidden="true">›</span></button>
</div>
<section class="hero"><div><div class="eyebrow">Managed Democracy // Live Operations</div><h1>THE GALAXY<br>IS THE BATTLEFIELD.</h1><p>Live war tracking, combat intelligence and galactic archives. Your operational picture for the fight for Super Earth.</p></div><div class="war-card"><div class="war-head"><span class="war-title">GALACTIC WAR STATUS</span><span class="war-clock" id="warClock">LIVE FEED</span></div><div><div class="war-value" id="warStatus">ACQUIRING SIGNAL</div><div class="war-sub" id="warSub">Connecting to the war data feed…</div><div class="meter"><span id="warMeter"></span></div><div class="war-head"><span class="war-sub" id="warMeterLabel">Planetary data loading</span><span class="war-sub" id="warUpdated">—</span></div></div></div></section>
<section class="major-order-banner" id="majorOrderBanner" aria-live="polite">
<div><div class="eyebrow" data-i18n="supremeOrder">High Command // Supreme Order</div><h2 id="supremeOrderTitle" data-i18n="orderLoading">Retrieving current Supreme Order…</h2><p id="supremeOrderBrief" data-i18n="orderBriefLoading">The latest directive from Super Earth will appear here when available.</p></div>
<div class="order-state" id="supremeOrderState">HIGH COMMAND<br>LIVE FEED</div>
</section>
<section class="stats" aria-label="Galactic statistics"><div class="stat"><div class="label">Active Helldivers</div><div class="value" id="players">—</div><div class="hint">Across reported operations</div></div><div class="stat"><div class="label">Missions won</div><div class="value" id="missionsWon">—</div><div class="hint">Reported total</div></div><div class="stat"><div class="label">Enemies eliminated</div><div class="value" id="kills">—</div><div class="hint">All reported factions</div></div><div class="stat"><div class="label">Accuracy</div><div class="value" id="accuracy">—</div><div class="hint">Galactic war statistics</div></div></section>
<div class="section-head"><div><h2>Command divisions</h2><p>Three core pillars, connected through one operational view.</p></div></div><section class="pillars"><article class="pillar"><div class="number">01 / WAR</div><h3>Galactic War</h3><p>Planet control, liberation progress, campaigns, attacks and live global orders.</p></article><article class="pillar"><div class="number">02 / COMBAT INTELLIGENCE</div><h3>Mission readiness</h3><p>Track enemy pressure and operational context to inform your next loadout and mission plan.</p></article><article class="pillar"><div class="number">03 / GALACTIC ARCHIVES</div><h3>Archives & community intel</h3><p>Dispatches, official updates, war history and the wider community picture.</p></article></section>
<div class="grid-main"><section class="panel" id="planets"><div class="panel-title"><div><h3 data-i18n="planetaryFront">Galactic theatre / Planetary front</h3><div class="small-note" id="planetCount">Loading planetary intelligence…</div></div><span class="tag" id="planetLiveTag">LIVE DATA</span></div><div class="search-row"><input class="search" id="planetSearch" type="search" placeholder="Search planet, sector, biome or faction…" aria-label="Search planets"><select class="search" id="planetFilter" aria-label="Filter planets" style="max-width:145px"><option value="all">All planets</option><option value="attacked">Under attack</option><option value="event">Active event</option><option value="liberation">Liberation</option></select></div><div class="galaxy-map" id="galaxyMap"><div class="map-caption">Schematic planetary theatre · select a marker</div><div class="map-empty">Waiting for planetary coordinates…</div></div><div class="planet-list" id="planetList"><div class="loading">Loading planetary data…</div></div><button class="btn" id="morePlanets" style="width:100%;margin-top:12px" type="button">Show more planets</button></section>
<div style="display:grid;gap:14px;align-content:start"><section class="panel" id="orders"><div class="panel-title"><h3>Orders of the day</h3><span class="tag hot">PRIORITY</span></div><div class="order-list" id="orders"><div class="loading">Retrieving current orders…</div></div></section><section class="panel"><div class="panel-title"><h3>Active campaigns</h3><span class="small-note" id="campaignCount">—</span></div><div class="campaign-list" id="campaigns"><div class="loading">Loading campaigns…</div></div></section></div></div>
<section class="panel" id="loadoutSuggestion"><div class="panel-title"><div><h3 data-i18n="loadoutTitle">Combat intelligence / Loadout suggestion</h3><div class="small-note" data-i18n="loadoutSub">Quick tactical recommendation by enemy faction and combat role</div></div><span class="tag hot">FIELD GUIDE</span></div>
<div class="loadout-controls"><select class="search" id="loadoutFaction" aria-label="Enemy faction"><option value="automatons">Automatons</option><option value="terminids">Terminids</option><option value="illuminate">Illuminate</option></select><select class="search" id="loadoutRole" aria-label="Combat role"><option value="balanced">Balanced</option><option value="antiarmor">Anti-armor</option><option value="crowd">Crowd control</option><option value="support">Support</option><option value="stealth">Stealth / objectives</option></select><select class="search" id="loadoutDifficulty" aria-label="Difficulty"><option value="low">Difficulty 1–4</option><option value="mid" selected>Difficulty 5–7</option><option value="high">Difficulty 8–10</option></select></div>
<div class="loadout-result" id="loadoutResult" aria-live="polite"><strong>Balanced deployment</strong><p>Choose a faction, role and difficulty for a tactical starting point. Verify current balance and unlocks in-game.</p></div></section>
<div class="two-col"><section class="panel"><div class="panel-title"><h3>Galactic dispatches</h3><span class="tag">FIELD REPORTS</span></div><div class="feed-list" id="dispatches"><div class="loading">Loading dispatches…</div></div></section><section class="panel"><div class="panel-title"><h3>Official updates</h3><span class="tag">STEAM NEWS</span></div><div class="feed-list" id="steamNews"><div class="loading">Loading official news…</div></div></section></div>
<div class="two-col"><section class="panel"><div class="panel-title"><h3>Space station</h3><span class="tag good">STRATEGIC ASSETS</span></div><div id="station"><div class="loading">Scanning strategic assets…</div></div></section><section class="panel"><div class="panel-title"><h3>Community intel</h3><span class="tag">WAR ROOM</span></div><p style="font-size:13px;color:var(--muted);margin:0 0 12px">A shared operational view of the galactic war. Community trends, detailed loadout recommendations and historical analytics will expand as those datasets become available through the companion API.</p><div class="campaign-list"><div class="campaign"><strong>War intelligence</strong><span id="warIntelState">Connecting…</span></div><div class="campaign"><strong>Combat recommendations</strong><span>Data module planned</span></div><div class="campaign"><strong>Galactic archives</strong><span id="archiveState">Connecting…</span></div></div></section></div>

<footer class="footer"><span>GALACTIC COMMAND // FOR THE GLORY OF SUPER EARTH</span><span>Data provided by configured upstream feeds · <span id="footerUpdated">Waiting for sync</span></span></footer>
</div>
<script>
(() => {
  const $ = id => document.getElementById(id);
  const basePath = (() => { const p = window.location.pathname; if (p.endsWith('/')) return p; return p.replace(/[^/]*$/, ''); })();
  const endpoint = path => `${basePath}${path.replace(/^\//, '')}`;
  const state = { planets: [], shown: 12, lastUpdate: null };
  const i18n = {
    en:{incomingOrder:'New major order',incomingWaiting:'Waiting for High Command…',incomingDispatch:'New dispatch',incomingDispatchWait:'Monitoring dispatch channel…',incomingNews:'Official news',incomingNewsWait:'Monitoring official feed…',incomingCampaign:'Campaign intel',incomingCampaignWait:'Checking active campaigns…',supremeOrder:'High Command // Supreme Order',orderLoading:'Retrieving current Supreme Order…',orderBriefLoading:'The latest directive from Super Earth will appear here when available.',planetaryFront:'Galactic theatre / Planetary front',loadoutTitle:'Combat intelligence / Loadout suggestion',loadoutSub:'Quick tactical recommendation by enemy faction and combat role'},
    es:{incomingOrder:'Nueva orden suprema',incomingWaiting:'Esperando al Alto Mando…',incomingDispatch:'Nuevo comunicado',incomingDispatchWait:'Supervisando canal de despachos…',incomingNews:'Noticias oficiales',incomingNewsWait:'Supervisando canal oficial…',incomingCampaign:'Inteligencia de campaña',incomingCampaignWait:'Consultando campañas activas…',supremeOrder:'Alto Mando // Orden Suprema',orderLoading:'Recuperando la Orden Suprema actual…',orderBriefLoading:'La última directiva de Super Tierra aparecerá aquí cuando esté disponible.',planetaryFront:'Teatro galáctico / Frente planetario',loadoutTitle:'Inteligencia de combate / Recomendación de equipamiento',loadoutSub:'Recomendación táctica según facción enemiga y rol de combate'}
  };
  let gcLanguage='en';
  function setLanguage(lang){
    gcLanguage=lang==='es'?'es':'en';document.documentElement.lang=gcLanguage;
    document.querySelectorAll('[data-lang]').forEach(b=>b.classList.toggle('active',b.dataset.lang===gcLanguage));
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.dataset.i18n;if(i18n[gcLanguage][k])el.textContent=i18n[gcLanguage][k];});
    renderLoadoutSuggestion();
  }
  document.querySelectorAll('[data-lang]').forEach(b=>b.addEventListener('click',()=>setLanguage(b.dataset.lang)));
  document.querySelectorAll('[data-jump]').forEach(b=>b.addEventListener('click',()=>{const t=document.getElementById(b.dataset.jump);if(t)t.scrollIntoView({behavior:'smooth',block:'center'});}));
  function renderLoadoutSuggestion(){
    const f=document.getElementById('loadoutFaction')?.value||'automatons',r=document.getElementById('loadoutRole')?.value||'balanced',d=document.getElementById('loadoutDifficulty')?.value||'mid';
    const data={
      automatons:{
        balanced:['Automaton field kit','Favor precision, cover and anti-armor capacity.','Autocannon or anti-materiel option · Eagle airstrike · Orbital precision strike · Supply pack or shield'],
        antiarmor:['Automaton armor breaker','Prioritize dependable penetration and precision against heavy units.','Recoilless rifle / Quasar Cannon · Orbital precision strike · Eagle airstrike · EMS support'],
        crowd:['Automaton suppression','Bring area damage for clustered infantry without losing all anti-armor coverage.','Explosive primary · Machine gun / Autocannon · Eagle cluster strike · Orbital gatling barrage'],
        support:['Automaton squad support','Coordinate anti-armor with teammates and provide resupply or defensive cover.','Squad support weapon · Supply pack · Eagle airstrike · Shield relay / EMS'],
        stealth:['Automaton reconnaissance','Use mobility and precision; avoid prolonged fights and preserve an emergency strike.','Precision primary · Scout armor · Orbital precision strike · Eagle airstrike']},
      terminids:{
        balanced:['Terminid field kit','Balance swarm clearing with a reliable answer to armored threats.','Crowd-clear primary · Flamethrower / Quasar Cannon · Eagle airstrike · Orbital precision strike'],
        antiarmor:['Terminid armor breaker','Reserve a dedicated slot for chargers and other heavy targets.','Quasar Cannon / Recoilless rifle · Orbital railcannon strike · Eagle airstrike · Sentry'],
        crowd:['Terminid swarm control','Prioritize sustained area damage and safe space around objectives.','Area-clear primary · Machine gun / Flamethrower · Eagle napalm strike · Gatling sentry'],
        support:['Terminid squad support','Cover teammates, thin swarms and provide resupply or defensive sentries.','Crowd-clear primary · Supply pack · Autocannon sentry · Eagle airstrike'],
        stealth:['Terminid reconnaissance','Move between objectives, avoid extra patrols and save strikes for emergencies.','Mobile primary · Scout armor · Eagle airstrike · Orbital precision strike']},
      illuminate:{
        balanced:['Illuminate field kit','Use flexible damage for mixed targets and keep tools for durable enemies.','Versatile primary · High-damage support weapon · Eagle airstrike · Orbital precision strike'],
        antiarmor:['Illuminate shield breaker','Coordinate concentrated burst damage against shielded or durable targets.','High-damage primary · Anti-armor support weapon · Orbital precision strike · Eagle airstrike'],
        crowd:['Illuminate crowd control','Handle groups of light targets while preserving a slot for priority threats.','Area-clear primary · Machine gun / laser option · Eagle cluster strike · Sentry'],
        support:['Illuminate squad support','Protect teammates, provide resupply or defense, and retain a flexible strike.','Versatile primary · Supply pack / shield option · Sentry · Eagle airstrike'],
        stealth:['Illuminate reconnaissance','Stay mobile, isolate threats and save strikes for objectives or extraction.','Precision primary · Scout armor · Orbital precision strike · Eagle airstrike']}
    };
    const x=data[f]?.[r]||data.automatons.balanced,es=gcLanguage==='es';
    const factions={automatons:es?'AUTÓMATAS':'AUTOMATONS',terminids:es?'TERMÍNIDOS':'TERMINIDS',illuminate:es?'ILUMINADOS':'ILLUMINATE'};
    const title=es?({'Automaton field kit':'Equipo de campo contra autómatas','Automaton armor breaker':'Antiblindaje contra autómatas','Automaton suppression':'Supresión de autómatas','Automaton squad support':'Apoyo de escuadrón contra autómatas','Automaton reconnaissance':'Reconocimiento contra autómatas','Terminid field kit':'Equipo de campo contra termínidos','Terminid armor breaker':'Antiblindaje contra termínidos','Terminid swarm control':'Control de enjambres termínidos','Terminid squad support':'Apoyo contra termínidos','Terminid reconnaissance':'Reconocimiento contra termínidos','Illuminate field kit':'Equipo de campo contra iluminados','Illuminate shield breaker':'Rompeescudos contra iluminados','Illuminate crowd control':'Control de grupos de iluminados','Illuminate squad support':'Apoyo contra iluminados','Illuminate reconnaissance':'Reconocimiento contra iluminados'}[x[0]]||x[0]):x[0];
    const desc=es?'Prioriza esta configuración como punto de partida; adapta las herramientas a objetivos, planetas y composición del escuadrón.':x[1];
    const kit=es?x[2].replaceAll('Supply pack','mochila de suministros').replaceAll('Eagle airstrike','ataque aéreo Eagle').replaceAll('Orbital precision strike','ataque orbital de precisión').replaceAll('Gatling sentry','centinela Gatling').replaceAll('Scout armor','armadura de explorador'):x[2];
    const extra = d === 'high' ? (es ? ' En dificultad alta, coordina antiblindaje y control de masas.' : ' At high difficulty, coordinate anti-armor and crowd control.') : d === 'mid' ? (es ? ' En dificultad media, conserva una herramienta flexible.' : ' At medium difficulty, keep one flexible tool.') : '';
    document.getElementById('loadoutResult').innerHTML=`<strong>${esc(factions[f])} // ${esc(title)}</strong><p>${esc(desc+extra)}</p><p><b>${es?'Equipo orientativo:':'Suggested kit:'}</b> ${esc(kit)}</p><p style="opacity:.75">${esc(es?'Guía heurística, no estadísticas de victorias en tiempo real. Verifica disponibilidad y balance actual en el juego.':'Heuristic field guide, not live win-rate statistics. Verify current balance and unlocks in-game.')}</p>`;
  }
  ['loadoutFaction','loadoutRole','loadoutDifficulty'].forEach(id=>document.getElementById(id)?.addEventListener('change',renderLoadoutSuggestion));

  const fmt = n => { const v = Number(n); return Number.isFinite(v) ? new Intl.NumberFormat('en-US',{notation:v>=1e6?'compact':'standard',maximumFractionDigits:1}).format(v) : '—'; };
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  setLanguage('en');renderLoadoutSuggestion();
  const unbox = x => x && typeof x === 'object' && Object.prototype.hasOwnProperty.call(x,'data') ? x.data : x;

  function initAppViews(){
    const shell=document.querySelector('.shell');
    const nav=document.createElement('nav');nav.className='app-nav';nav.setAttribute('aria-label','Main navigation');
    const viewHost=document.createElement('div');viewHost.className='app-views';viewHost.id='appViews';
    const definitions=[
      {id:'command',number:'01',en:'COMMAND',es:'MANDO',selectors:['.hero','#majorOrderBanner','.stats','.pillars']},
      {id:'galaxy',number:'02',en:'GALACTIC THEATRE',es:'TEATRO GALÁCTICO',selectors:['.grid-main']},
      {id:'combat',number:'03',en:'COMBAT INTEL',es:'INTELIGENCIA DE COMBATE',selectors:['#loadoutSuggestion']},
      {id:'archives',number:'04',en:'DISPATCHES & ARCHIVES',es:'COMUNICADOS Y ARCHIVO',selectors:[]}
    ];
    const views={};
    definitions.forEach(d=>{
      const b=document.createElement('button');b.type='button';b.dataset.view=d.id;b.dataset.number=d.number;b.textContent=gcLanguage==='es'?d.es:d.en;
      b.addEventListener('click',()=>activateAppView(d.id));nav.appendChild(b);
      const v=document.createElement('section');v.className='app-view';v.id='view-'+d.id;v.dataset.view=d.id;viewHost.appendChild(v);views[d.id]=v;
    });
    const header=document.querySelector('.topbar');
    const strip=document.getElementById('incomingStrip');
    header.after(strip,nav,viewHost);
    definitions.forEach(d=>d.selectors.forEach(sel=>{const el=document.querySelector(sel);if(el)views[d.id].appendChild(el);}));
    const twoCols=[...document.querySelectorAll('.shell > .two-col')];
    if(twoCols[0])views.archives.appendChild(twoCols[0]);
    if(twoCols[1])views.archives.appendChild(twoCols[1]);
    const footer=document.querySelector('.shell > .footer');if(footer)views.command.appendChild(footer);
    const quickNav=document.querySelector('.shell > .mobile-nav');if(quickNav)quickNav.remove();
    window.activateAppView=activateAppView;
    function activateAppView(id){
      definitions.forEach(d=>{
        const active=d.id===id;views[d.id].classList.toggle('active',active);
        const b=nav.querySelector(`[data-view="${d.id}"]`);b.classList.toggle('active',active);b.setAttribute('aria-current',active?'page':'false');
      });
      try{history.replaceState(null,'','#'+id);}catch(e){}
    }
    document.querySelectorAll('[data-jump]').forEach(b=>b.addEventListener('click',()=>{
      const target=b.dataset.jump;
      activateAppView(['orders','planets','campaigns'].includes(target)?'galaxy':(['dispatches','steamNews','station'].includes(target)?'archives':'galaxy'));
      setTimeout(()=>document.getElementById(target)?.scrollIntoView({behavior:'smooth',block:'center'}),30);
    }));
    const initial=location.hash.replace('#','');
    activateAppView(definitions.some(d=>d.id===initial)?initial:'command');
    document.querySelectorAll('[data-lang]').forEach(b=>b.addEventListener('click',()=>{
      definitions.forEach(d=>{const button=nav.querySelector(`[data-view="${d.id}"]`);button.textContent=gcLanguage==='es'?d.es:d.en;});
    }));
  }

  initAppViews();
  async function get(path) { const r = await fetch(endpoint(path), {headers:{'Accept':'application/json'}, cache:'no-store'}); const j = await r.json(); if (!r.ok || j?.ok === false) throw new Error(j?.error?.message || `HTTP ${r.status}`); return {data:unbox(j),meta:j?.meta||{}}; }
  function first(o, keys, fallback=null) { for (const k of keys) if (o && o[k] !== undefined && o[k] !== null) return o[k]; return fallback; }
  function arr(v) { if (Array.isArray(v)) return v; if (v && typeof v === 'object') { for (const k of ['items','results','data','planets','campaigns','assignments','dispatches','news']) if (Array.isArray(v[k])) return v[k]; } return []; }
  function nameOf(v) { if (typeof v === 'string') return v; if (v && typeof v === 'object') return first(v,['name','title','value','faction'], 'Unknown'); return v == null ? 'Unknown' : String(v); }
  function dateText(v) { if (!v) return ''; const n = typeof v === 'number' ? (v < 1e12 ? v*1000 : v) : Date.parse(v); if (!Number.isFinite(n)) return String(v); return new Date(n).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}); }
  function getStats(w) { return first(w,['statistics','stats','galaxyStats'],{}) || {}; }
  function setText(id,v) { $(id).textContent = v == null || v === '' ? '—' : String(v); }
  function healthPct(p) { const health=Number(first(p,['health','currentHealth'],NaN)), max=Number(first(p,['maxHealth','maximumHealth'],NaN)); if (Number.isFinite(health)&&Number.isFinite(max)&&max>0) return Math.max(0,Math.min(100,health/max*100)); const prog=Number(first(p,['liberation','liberationPercent','progress'],NaN)); return Number.isFinite(prog)?Math.max(0,Math.min(100,prog)):null; }
  function isAttacked(p) { return Boolean(first(p,['attacking','isAttacked','underAttack'],false)) || (Array.isArray(p?.attacking) && p.attacking.length>0); }
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
    const filtered=state.planets.filter(p=>{ const hay=[p.name,p.sector,nameOf(p.biome),nameOf(p.currentOwner),nameOf(p.initialOwner),...(Array.isArray(p.hazards)?p.hazards.map(nameOf):[])].join(' ').toLowerCase(); if(query&&!hay.includes(query))return false; if(filter==='attacked'&&!isAttacked(p))return false; if(filter==='event'&&!hasEvent(p))return false; if(filter==='liberation'&&healthPct(p)===null)return false; return true; });
    setText('planetCount',`${filtered.length.toLocaleString()} planets matching · ${state.planets.length.toLocaleString()} total`);
    const shown=filtered.slice(0,state.shown);
    $('planetList').innerHTML=shown.length?shown.map(p=>{const pct=healthPct(p),owner=nameOf(p.currentOwner||p.initialOwner),biome=nameOf(p.biome),attacked=isAttacked(p),event=hasEvent(p); const h=pct===null?'Progress unavailable':`${Math.round(pct)}% health remaining`; return `<article class="planet"><div class="planet-top"><div><div class="planet-name">${esc(p.name||`Planet ${p.index??'—'}`)}</div><div class="planet-meta">${esc(p.sector||'Unknown sector')} · ${esc(biome)}</div></div><span class="tag ${attacked||event?'hot':'good'}">${attacked?'UNDER ATTACK':event?'ACTIVE EVENT':esc(owner)}</span></div><div class="meter"><span style="width:${pct===null?0:pct}%"></span></div><div class="planet-foot"><span>${esc(h)}</span><span>${esc(owner)}${p.index!==undefined?' · Index '+esc(p.index):''}</span></div></article>`;}).join(''):'<div class="empty">No planets match those filters. Try another search.</div>';
    $('morePlanets').classList.toggle('hide',filtered.length<=state.shown); $('morePlanets').textContent=`Show more planets (${Math.max(0,filtered.length-state.shown)} remaining)`;
  }
  function renderOrders(data) { const items=arr(data); const primary=items[0]; if(primary){const t=first(primary,['title','name'],'Major Order'),d=first(primary,['briefing','description','message'],'Current operational directive.'),st=first(primary,['progressText','status'],'ACTIVE'); const titleEl=$('supremeOrderTitle'),briefEl=$('supremeOrderBrief'),stateEl=$('supremeOrderState'),inbox=$('incomingOrderText');if(titleEl)titleEl.textContent=t;if(briefEl)briefEl.textContent=d;if(stateEl)stateEl.textContent=String(st).toUpperCase();if(inbox)inbox.textContent=t;} $('orders').innerHTML=items.length?items.slice(0,4).map(o=>{const title=first(o,['title','name'],'Major Order');const desc=first(o,['briefing','description','message'],'Current operational directive.');const progress=Array.isArray(o.progress)?o.progress.join(' / '):first(o,['progressText','status'],'Active');const expiry=first(o,['expiration','expiresAt','expiry']);return `<article class="order"><h4>${esc(title)}</h4><p>${esc(desc)}</p><div class="order-bottom"><span>${esc(String(progress).slice(0,100))}</span><span>${expiry?'Ends '+esc(dateText(expiry)):'ORDER ACTIVE'}</span></div></article>`;}).join(''):'<div class="empty">No order data has been synchronized yet.</div>'; }
  function renderCampaigns(data) { const items=arr(data); setText('campaignCount',`${items.length} reported`); $('campaigns').innerHTML=items.length?items.slice(0,7).map(c=>{const p=c.planet||{};return `<div class="campaign"><strong>${esc(nameOf(p.name? p : first(c,['planet','name'],'Campaign')))}</strong><span>${esc(nameOf(first(c,['faction','type'],'Operation')))}${c.count!==undefined?' · '+esc(c.count):''}</span></div>`;}).join(''):'<div class="empty">No active campaigns in the latest synchronized payload.</div>'; }
  function renderDispatches(data) { const items=arr(data); const preview=$('incomingDispatchText');if(preview&&items[0])preview.textContent=String(first(items[0],['title','message','content'],'New dispatch')); $('dispatches').innerHTML=items.length?items.slice().sort((a,b)=>Number(first(b,['published','publishedAt','id'],0))-Number(first(a,['published','publishedAt','id'],0))).slice(0,5).map(d=>`<article class="feed-item"><div class="meta">DISPATCH ${esc(first(d,['id','Id'],'—'))} ${first(d,['published','Published'])?' · '+esc(dateText(first(d,['published','Published']))):''}</div><h4>${esc(first(d,['title'],'Galactic dispatch'))}</h4><p>${esc(first(d,['message','Message','content'],'No message text available.'))}</p></article>`).join(''):'<div class="empty">No dispatches synchronized yet.</div>'; }
  function renderSteam(data) { const items=arr(data); const preview=$('incomingNewsText');if(preview&&items[0])preview.textContent=String(first(items[0],['title','name','content'],'Official update')); $('steamNews').innerHTML=items.length?items.slice(0,5).map(n=>{const title=first(n,['title'],'Official update');const url=first(n,['url','link']);return `<article class="feed-item"><div class="meta">${esc(first(n,['author'],'HELLDIVERS 2'))}${first(n,['publishedAt','published'])?' · '+esc(dateText(first(n,['publishedAt','published']))):''}</div><h4>${url?`<a href="${esc(url)}" target="_blank" rel="noopener noreferrer">${esc(title)}</a>`:esc(title)}</h4><p>${esc(String(first(n,['content','description','summary'],'')).replace(/<[^>]*>/g,' ').slice(0,240))}</p></article>`;}).join(''):'<div class="empty">No official news synchronized yet.</div>'; }
  function renderStations(data) { const items=arr(data); const s=items[0]||data; if(!s||typeof s!=='object'||!Object.keys(s).length){$('station').innerHTML='<div class="empty">No station data available.</div>';return;} const p=s.planet||{};const actions=Array.isArray(s.tacticalActions)?s.tacticalActions:[];$('station').innerHTML=`<div class="order"><h4>${esc(nameOf(p.name?p:first(s,['name'],'Mobile Space Station')))}</h4><p>Station ID32: ${esc(first(s,['id32'],'—'))}</p><div class="order-bottom"><span>${actions.length} tactical actions</span><span>${s.electionEnd?'Election ends '+esc(dateText(s.electionEnd)):'Strategic asset'}</span></div>${actions.length?`<div style="margin-top:10px">${actions.slice(0,4).map(a=>`<span class="tag" style="margin:0 5px 5px 0">${esc(nameOf(first(a,['name','title','id'],a)))}</span>`).join('')}</div>`:''}</div>`; }
  function renderWar(data, meta) { const w=data&&typeof data==='object'?data:{};const s=getStats(w);const players=first(s,['playerCount','players','activePlayers']);const won=first(s,['missionsWon','missions_won']);const killed=['terminidKills','automatonKills','illuminateKills'].reduce((sum,k)=>sum+(Number(s[k])||0),0);const acc=first(s,['accuracy','missionAccuracy']);setText('players',players===undefined?'—':fmt(players));setText('missionsWon',won===undefined?'—':fmt(won));setText('kills',killed?fmt(killed):'—');setText('accuracy',acc===undefined?'—':`${Number(acc)<=1?Math.round(Number(acc)*100):Math.round(Number(acc))}%`);const now=first(w,['now','time']);const planets=state.planets;const attacks=planets.filter(isAttacked).length;setText('warStatus',planets.length?`${attacks} FRONT${attacks===1?'':'S'} ACTIVE`:'WAR FEED ONLINE');setText('warSub',first(w,['clientVersion'])?`Client version ${w.clientVersion} · Galactic status received`:'Live galactic data received');setText('warMeterLabel',planets.length?`${attacks} planets under attack / ${planets.length} tracked`:'Awaiting planet feed');$('warMeter').style.width=planets.length?`${Math.min(100,attacks/planets.length*100*5)}%`:'0%';setText('warClock',now?dateText(now).toUpperCase():'LIVE FEED');setText('warUpdated',meta?.source_fetched_at?`SYNC ${dateText(meta.source_fetched_at)}`:'SYNCED');setText('warIntelState',planets.length?'Planet data loaded':'Awaiting sync');setText('archiveState','Dispatch feed '+(state.lastUpdate?'loaded':'pending')); }
  async function loadOne(path, success, fail) { try {const result=await get(path);success(result.data,result.meta);return result;}catch(err){if(fail)fail(err);return null;} }
  async function loadAll() { $('connection').textContent='SYNCING GALACTIC NETWORK';$('connection').style.color='';$('refreshBtn').disabled=true; $('refreshBtn').textContent='↻ Syncing'; const results=await Promise.allSettled([
    loadOne('api/v1/war',(d,m)=>{renderWar(d,m);},err=>{setText('warStatus','FEED UNAVAILABLE');setText('warSub',`API error: ${err.message}`);setText('warMeterLabel','Check API endpoint and sync status');}),
    loadOne('api/v1/planets',(d)=>{state.planets=arr(d);renderGalaxyMap();renderPlanets();},err=>{$('planetList').innerHTML=`<div class="error">Planet API unavailable: ${esc(err.message)}</div>`;$('galaxyMap').innerHTML=`<div class="map-empty">Could not retrieve planetary data. Check the API response and sync status.</div>`;}),
    loadOne('api/v1/assignments',renderOrders,()=>loadOne('api/v1/orders',renderOrders,err=>{$('orders').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/campaigns',renderCampaigns,err=>{$('campaigns').innerHTML=`<div class="error">${esc(err.message)}</div>`;}),
    loadOne('api/v1/dispatches',renderDispatches,()=>loadOne('api/v1/news',renderDispatches,err=>{$('dispatches').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/steam',renderSteam,()=>loadOne('api/v1/steam-news',renderSteam,err=>{$('steamNews').innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
    loadOne('api/v1/space-stations',renderStations,err=>{$('station').innerHTML=`<div class="error">${esc(err.message)}</div>`;})
  ]); const ok=results.some(r=>r.status==='fulfilled'&&r.value!==null); state.lastUpdate=new Date(); $('connection').textContent=ok?'GALACTIC NETWORK CONNECTED':'API ONLINE · DATA SYNC REQUIRED';$('connection').style.color=ok?'var(--acid)':'var(--gold)';setText('footerUpdated',`Last checked ${state.lastUpdate.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}`);setText('warUpdated',state.lastUpdate.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}));$('refreshBtn').disabled=false;$('refreshBtn').textContent='↻ Refresh'; }
  $('planetSearch').addEventListener('input',()=>{state.shown=12;renderPlanets();});$('planetFilter').addEventListener('change',()=>{state.shown=12;renderPlanets();});$('morePlanets').addEventListener('click',()=>{state.shown+=12;renderPlanets();});$('refreshBtn').addEventListener('click',loadAll);
  // API routes are queried independently so one unavailable feed does not blank the whole page.
  loadAll(); setInterval(loadAll,120000);
})();
</script></body></html>
HTML;
    exit;
}

if ($path === '/api/v1/health') respond(['service'=>'Galactic Command API','status'=>'up']);

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
