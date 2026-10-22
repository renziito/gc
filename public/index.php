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

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) $path = substr($path, strlen($scriptDir));
$path = '/' . trim($path, '/');
if ($path === '//') $path = '/';
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$pushWriteRoutes = ['/api/v1/push/subscribe','/api/v1/push/unsubscribe'];
if ($method === 'POST' && in_array($path,$pushWriteRoutes,true)) {
    $payload=json_decode((string)file_get_contents('php://input'),true);
    if(!is_array($payload)) respondError(400,'invalid_json','A JSON request body is required.');
    if($path==='/api/v1/push/subscribe'){
        $subscription=$payload['subscription']??null;
        $endpoint=is_array($subscription)?(string)($subscription['endpoint']??''):'';
        $keys=is_array($subscription)?($subscription['keys']??[]):[];
        if(!str_starts_with($endpoint,'https://')||!is_array($keys)||empty($keys['p256dh'])||empty($keys['auth'])) respondError(422,'invalid_subscription','A valid browser push subscription is required.');
        $hash=hash('sha256',$endpoint);
        $prefs=['orders'=>true];
        $q=$pdo->prepare('INSERT INTO push_subscriptions(endpoint_hash,subscription_json,preferences_json,created_at,updated_at) VALUES(?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE subscription_json=VALUES(subscription_json),preferences_json=VALUES(preferences_json),updated_at=UTC_TIMESTAMP()');
        $q->execute([$hash,json_encode($subscription,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),json_encode($prefs)]);
        respond(['subscribed'=>true,'topic'=>'orders']);
    }
    $endpoint=(string)($payload['endpoint']??'');
    if($endpoint==='') respondError(422,'missing_endpoint','The subscription endpoint is required.');
    $q=$pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint_hash=?');$q->execute([hash('sha256',$endpoint)]);
    respond(['unsubscribed'=>true]);
}
if ($method !== 'GET') { header('Allow: GET, POST'); respondError(405,'method_not_allowed','This method is not supported for this endpoint.'); }

// The browser dashboard lives at the root. All API routes continue to return JSON.
if ($path === '/' || $path === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<!-- DiverSignal build: LOADOUT-STRATAGEMS-WIKI-FILES-v6 -->
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#10150f"><meta name="description" content="DiverSignal — Helldivers 2 orders, loadout advice and combat intelligence.">
<title>DiverSignal — Helldivers 2 Companion</title>
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


/* DiverSignal focused companion layout */
:root{--bg:#08090a;--panel:#111416;--panel2:#191d20;--line:#41474b;--text:#f2f1e8;--muted:#a7adb0;--acid:#f2d400}
.shell{display:flex;flex-direction:column;min-height:100vh}
.language-picker select{max-width:150px;padding:8px 9px;background:#151819;color:var(--text);border:1px solid #555b5e;border-radius:0;font-size:11px}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.app-views{height:calc(100vh - 155px);min-height:570px;flex:1}
.app-view{padding:14px 0 28px}.app-view .footer{display:flex}
.app-nav button{text-align:center;text-transform:uppercase;letter-spacing:.04em}
.app-nav button:before{display:none}
.major-order-banner{margin:14px 0 12px}
#supremeOrderTitle{font-size:clamp(22px,3vw,36px);line-height:1.15;margin:7px 0}
#supremeOrderBrief{max-width:900px}
#ordersPanel{margin-bottom:12px}
.mission-line{width:100%;display:grid;grid-template-columns:34px minmax(0,1fr) minmax(90px,150px) auto 18px;gap:10px;align-items:center;text-align:left;padding:12px 10px;background:#151819;border:1px solid #41474b;color:var(--text);border-radius:0}
.mission-line:hover,.mission-line:focus-visible{border-color:var(--acid);outline:none}
.mission-index{font-size:11px;color:var(--acid);font-weight:900}
.mission-line-title{font-size:13px;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mission-line-progress{font-size:11px;color:var(--muted);white-space:nowrap}
.mission-chevron{color:var(--acid);font-size:20px}
.loadout-controls{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.loadout-controls label{display:grid;gap:6px;color:var(--muted);font-size:11px;font-weight:800}
.order-detail-heading{display:flex;justify-content:space-between;gap:12px;align-items:center}
.order-detail-heading h4{margin:0;font-size:18px}
.order-detail-content>p{color:var(--muted);line-height:1.65}
.detail-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:14px 0}
.detail-metrics>div,.selected-mission{border:1px solid var(--line);background:#ffffff04;padding:12px}
.detail-metrics small,.selected-mission small{display:block;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}
.detail-metrics strong,.selected-mission strong{display:block;margin-top:5px;font-size:15px}
.detail-section-title{font-size:12px;text-transform:uppercase;letter-spacing:.1em;margin:18px 0 8px}
.detail-objectives{display:grid;gap:8px}
.detail-objective{display:flex;justify-content:space-between;gap:12px;padding:10px;border-bottom:1px solid #ffffff15;font-size:12px}
.detail-objective span{color:var(--muted);text-align:right}
.intel-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.intel-card{padding:14px;border:1px solid var(--line);background:#ffffff03}
.intel-card h4{margin:0 0 8px;font-size:13px;text-transform:uppercase}
.intel-card p{margin:0;color:var(--muted);font-size:12px;line-height:1.65}
.intel-tags{display:flex;flex-wrap:wrap;gap:5px;margin-top:12px}
.intel-tags span{font-size:9px;text-transform:uppercase;border:1px solid #50575a;padding:4px 6px;color:var(--acid)}
#aboutPanel p{color:var(--muted);font-size:13px;line-height:1.7}
#donationLink{color:var(--acid)}
.ad-slot{min-height:82px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;margin:12px 0;border:1px dashed #555b5e;background:#ffffff03;color:#b8bec0;text-align:center;font-size:11px;text-transform:uppercase;letter-spacing:.08em}
.ad-slot small{font-size:10px;color:#7f898d;text-transform:none;letter-spacing:0}
.rewarded-ad-slot{min-height:110px}
#view-more .two-col{margin-bottom:12px}
@media(max-width:850px){.loadout-controls{grid-template-columns:repeat(2,minmax(0,1fr))}.app-views{height:calc(100vh - 150px);min-height:620px}}
@media(max-width:600px){.topbar{flex-wrap:wrap}.top-actions{gap:5px}.language-picker select{max-width:120px}.app-nav button{font-size:8px;padding:10px 3px}.app-views{height:calc(100vh - 165px);min-height:650px}.mission-line{grid-template-columns:24px minmax(0,1fr) auto 12px;gap:6px;padding:10px 7px}.mission-line-progress{display:none}.mission-line-title{font-size:11px}.loadout-controls,.intel-grid{grid-template-columns:1fr}.detail-objective{align-items:flex-start;flex-direction:column}.detail-objective span{text-align:left}}

/* Visual equipment cards and stratagem artwork */
.loadout-stratagem-card{display:grid;grid-template-columns:96px minmax(0,1fr);gap:12px;align-items:center;padding:12px;margin:8px 0;background:#101315;border:1px solid #3b4144}
.loadout-stratagem-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:12px}
.loadout-stratagem-grid .loadout-stratagem-card{margin:0;min-height:110px}
.loadout-art img{display:block;position:absolute;width:100%;height:100%;object-fit:contain;padding:7px;opacity:1;background:#0b0d0e}
.loadout-art img[hidden]{display:none!important}
.loadout-art .art-fallback{position:relative;z-index:1;font-size:30px;text-shadow:0 2px 5px #000}
.loadout-art.has-art .art-fallback{display:none}
@media(max-width:700px){.loadout-stratagem-grid{grid-template-columns:1fr}}
.loadout-art{position:relative;display:flex;align-items:center;justify-content:center;min-height:82px;overflow:hidden;background:linear-gradient(145deg,#252a2b,#0a0c0d);border:1px solid #50585a}
.loadout-art img{position:absolute;width:100%;height:100%;object-fit:contain;opacity:.9}
.loadout-art img[hidden]{display:none!important}
.loadout-art span{position:relative;z-index:1;font-size:29px;text-shadow:0 2px 5px #000}
.loadout-art.no-art{background:linear-gradient(145deg,#282d2e,#101213)}
.loadout-stratagem-copy{min-width:0}.loadout-stratagem-copy h4{margin:3px 0 5px}.stratagem-code{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:4px 0 9px;color:var(--muted);font-size:11px}.stratagem-code strong{color:var(--acid);font-size:18px;letter-spacing:2px;text-transform:none;white-space:normal}.loadout-stratagem-copy p{margin:0 0 8px}.loadout-stratagem-copy .module-actions{display:flex;gap:5px;flex-wrap:wrap}.wiki-source{color:#e3c93b;text-decoration:none}
@media(max-width:520px){.loadout-stratagem-card{grid-template-columns:64px minmax(0,1fr);gap:8px;padding:8px}.loadout-art{min-height:66px}.loadout-art .art-fallback{font-size:23px}}
</style>
</head>
<body><div class="shell">
<header class="topbar"><div class="brand"><div class="logo">DS</div><div class="brandname">DIVERSIGNAL<small>HELLDIVERS 2 COMPANION</small></div></div><div class="status"><span class="dot"></span><span id="connection">CONNECTING TO GALACTIC NETWORK</span></div><div class="top-actions"><button class="btn" id="refreshBtn" type="button">↻ Refresh</button><label class="language-picker"><span class="sr-only">Language</span><select id="languageSelect" aria-label="Language"><option value="en">English</option><option value="zh">简体中文</option><option value="ru">Русский</option><option value="es">Español</option><option value="pt">Português</option><option value="de">Deutsch</option></select></label></div></header>
<section class="major-order-banner" id="majorOrderBanner" aria-live="polite">
<div><div class="eyebrow" data-i18n="supremeOrder">HIGH COMMAND // SUPREME ORDER</div><h2 id="supremeOrderTitle" data-i18n="orderLoading">Retrieving current Supreme Order…</h2><p id="supremeOrderBrief" data-i18n="orderBriefLoading">The latest directive will appear here when available.</p></div>
<div class="order-state" id="supremeOrderState">ORDER STATUS</div>
</section>
<section class="panel" id="ordersPanel"><div class="panel-title"><div><h3 data-i18n="missionListTitle">Supreme Order missions</h3><div class="small-note" data-i18n="missionListSub">One concise line per mission. Select a mission to view details.</div></div><span class="tag hot" data-i18n="priority">PRIORITY</span></div><div class="order-list" id="missionRows"><div class="loading">Retrieving current missions…</div></div></section>
<section class="panel" id="loadoutSuggestion"><div class="panel-title"><div><h3 data-i18n="loadoutTitle">Loadout Advisor</h3><div class="small-note" data-i18n="loadoutSub">Choose an enemy, mission type and combat style.</div></div><span class="tag hot">FIELD GUIDE</span></div>
<div class="loadout-controls">
<label><span data-i18n="enemyFaction">Enemy faction</span><select class="search" id="loadoutFaction" aria-label="Enemy faction"><option value="terminids">Terminids</option><option value="automatons">Automatons</option><option value="illuminate">Illuminate</option></select></label>
<label><span data-i18n="missionType">Mission type</span><select class="search" id="loadoutMission" aria-label="Mission type"><option value="eradicate">Eradication / high kills</option><option value="defense">Defense / hold position</option><option value="objectives" selected>Objectives / operations</option><option value="recon">Recon / stealth</option><option value="extermination">Heavy targets</option><option value="escort">Escort / civilians</option><option value="blitz">Blitz / speed</option></select></label>
<label><span data-i18n="combatStyle">Combat style</span><select class="search" id="loadoutRole" aria-label="Combat style"><option value="balanced">Balanced</option><option value="antiarmor">Anti-armor</option><option value="crowd">Crowd control</option><option value="support">Support / team utility</option><option value="stealth">Stealth / mobility</option></select></label>
<label><span data-i18n="difficulty">Difficulty</span><select class="search" id="loadoutDifficulty" aria-label="Difficulty"><option value="low">Difficulty 1–4</option><option value="mid" selected>Difficulty 5–7</option><option value="high">Difficulty 8–10</option></select></label>
</div><div class="loadout-result" id="loadoutResult" aria-live="polite"><strong>Balanced deployment</strong><p>Choose your mission parameters to get a tactical starting point.</p></div></section>
<section class="panel" id="orderDetailsPanel"><div class="panel-title"><div><h3 data-i18n="orderDetailsTitle">Supreme Order details</h3><div class="small-note" data-i18n="orderDetailsSub">Full objectives, progress and deadline.</div></div><span class="tag" id="detailOrderState">—</span></div><div id="orderDetailsContent" class="order-detail-content"><div class="empty" data-i18n="orderDetailsEmpty">Waiting for current order data.</div></div></section>
<section class="panel" id="combatIntelPanel"><div class="panel-title"><div><h3 data-i18n="combatIntelTitle">Combat Intel</h3><div class="small-note" data-i18n="combatIntelSub">General tactical guidance for any mission.</div></div><span class="tag">FIELD GUIDE</span></div>
<div class="intel-grid">
<article class="intel-card"><h4 data-i18n="terminidIntelTitle">Against Terminids</h4><p data-i18n="terminidIntel">Prioritize swarm control, area damage, safe space around objectives and at least one reliable answer to heavily armored targets.</p><div class="intel-tags"><span>Area damage</span><span>Anti-armor</span><span>Area denial</span></div></article>
<article class="intel-card"><h4 data-i18n="automatonIntelTitle">Against Automatons</h4><p data-i18n="automatonIntel">Use cover and precision, bring dependable armor penetration, and coordinate heavy-target tools rather than relying only on crowd control.</p><div class="intel-tags"><span>Precision</span><span>Cover</span><span>Penetration</span></div></article>
<article class="intel-card"><h4 data-i18n="illuminateIntelTitle">Against Illuminate</h4><p data-i18n="illuminateIntel">Use flexible damage for mixed groups, coordinate burst damage against durable or shielded enemies, and keep one slot for team utility.</p><div class="intel-tags"><span>Flexible damage</span><span>Team utility</span></div></article>
<article class="intel-card"><h4 data-i18n="missionIntelTitle">By mission type</h4><p data-i18n="missionIntel">Defense benefits from area denial and sentries; blitz missions favor mobility and fast objective completion; heavy-target missions need coordinated anti-armor.</p><div class="intel-tags"><span>Defense</span><span>Blitz</span><span>Heavy targets</span></div></article>
</div><p class="module-disclaimer" data-i18n="intelDisclaimer">Tactical starting points, not official win-rate statistics. Verify current balance and unlocks in-game.</p></section>
<section class="panel" id="aboutPanel"><div class="panel-title"><h3 data-i18n="aboutTitle">About DiverSignal</h3><span class="tag">INDEPENDENT PROJECT</span></div><p data-i18n="aboutText">DiverSignal is an independent companion for Helldivers 2, built to help players understand current orders and prepare tactical loadouts while they play. It is not affiliated with Arrowhead Game Studios or Sony.</p><p><a href="#" id="donationLink" data-i18n="donationLink">Support the project (link coming soon)</a></p></section>
<section class="panel" id="notificationPanel"><div class="panel-title"><div><h3 data-i18n="notificationsTitle">Order notifications</h3><div class="small-note" data-i18n="notificationsSub">Only a new Supreme Order or the end of the current order can trigger a push.</div></div><span class="tag">PUSH</span></div><button class="btn primary" type="button" id="enableOrderPush" data-i18n="enableNotifications">Enable order notifications</button><p id="pushStatus" class="small-note" data-i18n="pushNotEnabled">Notifications are not enabled on this device.</p></section>
<section class="ad-slot banner-ad-slot" aria-label="Advertisement banner"><span data-i18n="bannerAdSlot">Advertisement banner space</span><small data-i18n="bannerAdNote">Reserved for future ad provider integration.</small></section>
<section class="ad-slot rewarded-ad-slot" aria-label="Optional rewarded advertisement"><span data-i18n="rewardAdSlot">Optional rewarded ad space</span><small data-i18n="rewardAdNote">No ad or reward is active until the provider and rules are configured.</small></section>
<div class="two-col"><section class="panel"><div class="panel-title"><h3 data-i18n="dispatchesTitle">Galactic dispatches</h3><span class="tag">FIELD REPORTS</span></div><div class="feed-list" id="dispatches"><div class="loading">Loading dispatches…</div></div></section><section class="panel"><div class="panel-title"><h3 data-i18n="officialNewsTitle">Official updates</h3><span class="tag">STEAM NEWS</span></div><div class="feed-list" id="steamNews"><div class="loading">Loading official news…</div></div></section></div>
<div class="two-col"><section class="panel"><div class="panel-title"><h3 data-i18n="spaceStationTitle">Space station</h3><span class="tag good">STRATEGIC ASSETS</span></div><div id="station"><div class="loading">Loading station data…</div></div></section><section class="panel"><div class="panel-title"><h3 data-i18n="communityTitle">Community intel</h3><span class="tag">FIELD NOTES</span></div><p data-i18n="communityText">A reference area for tactical notes and community observations. Recommendations are guidance, not official statistics.</p><div class="campaign-list"><div class="campaign"><strong data-i18n="warIntelligence">War intelligence</strong><span id="warIntelState">Connecting…</span></div><div class="campaign"><strong data-i18n="combatRecommendations">Combat recommendations</strong><span data-i18n="rulesBased">Rules-based guide</span></div><div class="campaign"><strong data-i18n="archivesLabel">Archives</strong><span id="archiveState">Connecting…</span></div></div></section></div>
<footer class="footer"><span>DIVERSIGNAL // TACTICAL COMPANION</span><span>Data provided by configured upstream feeds · <span id="footerUpdated">Waiting for sync</span></span></footer>
</div>
<script>
(() => {
  const $ = id => document.getElementById(id);
  const basePath = (() => { const p = window.location.pathname; if (p.endsWith('/')) return p; return p.replace(/[^/]*$/, ''); })();
  const endpoint = path => `${basePath}${path.replace(/^\//, '')}`;
  const state = { lastUpdate: null, order: null };
  const i18n = {
    en:{terminids:'Terminids',automatons:'Automatons',illuminate:'Illuminate',notificationsTitle:'Order notifications',notificationsSub:'Only a new Supreme Order or the end of the current order can trigger a push.',enableNotifications:'Enable order notifications',pushNotEnabled:'Notifications are not enabled on this device.',pushEnabled:'Order notifications are enabled.',pushUnsupported:'Push notifications are not supported by this browser.',pushDenied:'Notification permission was denied.',pushError:'Could not enable notifications. Check the site configuration.',supremeOrder:'HIGH COMMAND // SUPREME ORDER',orderLoading:'Retrieving current Supreme Order…',orderBriefLoading:'The latest directive will appear here when available.',missionListTitle:'Supreme Order missions',missionListSub:'One concise line per mission. Select a mission to view details.',priority:'PRIORITY',loadoutTitle:'Loadout Advisor',loadoutSub:'Choose an enemy, mission type and combat style.',enemyFaction:'Enemy faction',missionType:'Mission type',combatStyle:'Combat style',difficulty:'Difficulty',orderDetailsTitle:'Supreme Order details',orderDetailsSub:'Full objectives, progress and deadline.',orderDetailsEmpty:'Waiting for current order data.',combatIntelTitle:'Combat Intel',combatIntelSub:'General tactical guidance for any mission.',terminidIntelTitle:'Against Terminids',terminidIntel:'Prioritize swarm control, area damage, safe space around objectives and at least one reliable answer to heavily armored targets.',automatonIntelTitle:'Against Automatons',automatonIntel:'Use cover and precision, bring dependable armor penetration, and coordinate heavy-target tools rather than relying only on crowd control.',illuminateIntelTitle:'Against Illuminate',illuminateIntel:'Use flexible damage for mixed groups, coordinate burst damage against durable or shielded enemies, and keep one slot for team utility.',missionIntelTitle:'By mission type',missionIntel:'Defense benefits from area denial and sentries; blitz missions favor mobility and fast objective completion; heavy-target missions need coordinated anti-armor.',intelDisclaimer:'Tactical starting points, not official win-rate statistics. Verify current balance and unlocks in-game.',aboutTitle:'About DiverSignal',aboutText:'DiverSignal is an independent companion for Helldivers 2, built to help players understand current orders and prepare tactical loadouts while they play. It is not affiliated with Arrowhead Game Studios or Sony.',donationLink:'Support the project (link coming soon)',bannerAdSlot:'Advertisement banner space',bannerAdNote:'Reserved for future ad provider integration.',rewardAdSlot:'Optional rewarded ad space',rewardAdNote:'No ad or reward is active until the provider and rules are configured.',dispatchesTitle:'Galactic dispatches',officialNewsTitle:'Official updates',spaceStationTitle:'Space station',communityTitle:'Community intel',communityText:'A reference area for tactical notes and community observations. Recommendations are guidance, not official statistics.',warIntelligence:'War intelligence',combatRecommendations:'Combat recommendations',rulesBased:'Rules-based guide',archivesLabel:'Archives',navOrders:'ORDERS',navLoadout:'LOADOUT',navIntel:'COMBAT INTEL',navDetails:'ORDER DETAILS',navMore:'MORE',refresh:'Refresh',active:'Active',completed:'Completed',expired:'Expired',progress:'Progress',deadline:'Deadline',objective:'Objective',noMissionData:'No mission-level objectives were supplied by the data source.',mission:'Mission',selectMission:'Select a mission to see the full order details.',balanced:'Balanced',antiarmor:'Anti-armor',crowd:'Crowd control',support:'Support / team utility',stealth:'Stealth / mobility',eradicate:'Eradication / high kills',defense:'Defense / hold position',objectives:'Objectives / operations',recon:'Recon / stealth',extermination:'Heavy targets',escort:'Escort / civilians',blitz:'Blitz / speed',lowDifficulty:'Difficulty 1–4',midDifficulty:'Difficulty 5–7',highDifficulty:'Difficulty 8–10',recommendation:'Suggested kit:',disclaimer:'Heuristic field guide, not live win-rate statistics. Verify current balance and unlocks in-game.'},
    es:{terminids:'Termínidos',automatons:'Autómatas',illuminate:'Iluminados',notificationsTitle:'Notificaciones de órdenes',notificationsSub:'Solo una nueva Orden Suprema o el fin de la orden actual puede generar una notificación.',enableNotifications:'Activar notificaciones de órdenes',pushNotEnabled:'Las notificaciones no están activadas en este dispositivo.',pushEnabled:'Las notificaciones de órdenes están activadas.',pushUnsupported:'Este navegador no admite notificaciones push.',pushDenied:'Se denegó el permiso de notificaciones.',pushError:'No se pudieron activar las notificaciones. Revisa la configuración del sitio.',supremeOrder:'ALTO MANDO // ORDEN SUPREMA',orderLoading:'Recuperando la Orden Suprema actual…',orderBriefLoading:'La directiva más reciente aparecerá aquí cuando esté disponible.',missionListTitle:'Misiones de la Orden Suprema',missionListSub:'Una línea resumida por misión. Selecciona una para ver el detalle.',priority:'PRIORIDAD',loadoutTitle:'Asesor de equipamiento',loadoutSub:'Elige enemigo, tipo de misión y estilo de combate.',enemyFaction:'Facción enemiga',missionType:'Tipo de misión',combatStyle:'Estilo de combate',difficulty:'Dificultad',orderDetailsTitle:'Detalle de la Orden Suprema',orderDetailsSub:'Objetivos, progreso y fecha límite.',orderDetailsEmpty:'Esperando los datos de la orden actual.',combatIntelTitle:'Inteligencia de combate',combatIntelSub:'Consejos tácticos generales para cualquier misión.',terminidIntelTitle:'Contra los termínidos',terminidIntel:'Prioriza el control de enjambres, daño de área, espacio seguro alrededor de los objetivos y al menos una respuesta fiable contra blindados pesados.',automatonIntelTitle:'Contra los autómatas',automatonIntel:'Usa cobertura y precisión, lleva penetración de blindaje fiable y coordina herramientas contra objetivos pesados.',illuminateIntelTitle:'Contra los iluminados',illuminateIntel:'Usa daño flexible contra grupos mixtos, coordina daño concentrado contra enemigos resistentes o con escudo y reserva un espacio para apoyo al equipo.',missionIntelTitle:'Según el tipo de misión',missionIntel:'La defensa se beneficia de negación de área y centinelas; las misiones Blitz favorecen movilidad y rapidez; los objetivos pesados requieren antiblindaje coordinado.',intelDisclaimer:'Son puntos de partida tácticos, no estadísticas oficiales de victorias. Verifica el balance y desbloqueos en el juego.',aboutTitle:'Sobre DiverSignal',aboutText:'DiverSignal es un complemento independiente para Helldivers 2 que ayuda a entender las órdenes actuales y preparar equipamientos tácticos mientras juegas. No está afiliado a Arrowhead Game Studios ni a Sony.',donationLink:'Apoya el proyecto (enlace próximamente)',bannerAdSlot:'Espacio para banner publicitario',bannerAdNote:'Reservado para integrar un proveedor de anuncios en el futuro.',rewardAdSlot:'Espacio para anuncio recompensado opcional',rewardAdNote:'No habrá anuncios ni recompensas hasta configurar proveedor y reglas.',dispatchesTitle:'Comunicados galácticos',officialNewsTitle:'Actualizaciones oficiales',spaceStationTitle:'Estación espacial',communityTitle:'Inteligencia comunitaria',communityText:'Espacio de referencia para notas tácticas y observaciones comunitarias. Las recomendaciones son orientativas, no estadísticas oficiales.',warIntelligence:'Inteligencia de guerra',combatRecommendations:'Recomendaciones de combate',rulesBased:'Guía basada en reglas',archivesLabel:'Archivos',navOrders:'ÓRDENES',navLoadout:'EQUIPAMIENTO',navIntel:'COMBATE',navDetails:'DETALLE DE ORDEN',navMore:'MÁS',refresh:'Actualizar',active:'Activa',completed:'Completada',expired:'Expirada',progress:'Progreso',deadline:'Fecha límite',objective:'Objetivo',noMissionData:'La fuente no proporcionó objetivos desglosados por misión.',mission:'Misión',selectMission:'Selecciona una misión para ver el detalle completo.',balanced:'Equilibrado',antiarmor:'Antiblindaje',crowd:'Control de masas',support:'Apoyo al equipo',stealth:'Sigilo / movilidad',eradicate:'Erradicación / muchas bajas',defense:'Defensa / mantener posición',objectives:'Objetivos / operaciones',recon:'Reconocimiento / sigilo',extermination:'Objetivos pesados',escort:'Escolta / civiles',blitz:'Blitz / rapidez',lowDifficulty:'Dificultad 1–4',midDifficulty:'Dificultad 5–7',highDifficulty:'Dificultad 8–10',recommendation:'Equipo sugerido:',disclaimer:'Guía heurística, no estadísticas de victorias en vivo. Verifica el balance y desbloqueos actuales en el juego.'},
    zh:{terminids:'虫族',automatons:'自动机',illuminate:'光能族',notificationsTitle:'命令通知',notificationsSub:'仅新最高命令或当前命令结束时发送推送通知。',enableNotifications:'启用命令通知',pushNotEnabled:'此设备尚未启用通知。',pushEnabled:'命令通知已启用。',pushUnsupported:'此浏览器不支持推送通知。',pushDenied:'通知权限被拒绝。',pushError:'无法启用通知，请检查网站配置。',supremeOrder:'最高指挥部 // 最高命令',orderLoading:'正在获取当前最高命令…',orderBriefLoading:'最新指令将在此显示。',missionListTitle:'最高命令任务',missionListSub:'每个任务一行摘要。选择任务查看详情。',priority:'优先',loadoutTitle:'装备顾问',loadoutSub:'选择敌人阵营、任务类型和战斗风格。',enemyFaction:'敌人阵营',missionType:'任务类型',combatStyle:'战斗风格',difficulty:'难度',orderDetailsTitle:'最高命令详情',orderDetailsSub:'目标、进度和截止时间。',orderDetailsEmpty:'正在等待当前命令数据。',combatIntelTitle:'战斗情报',combatIntelSub:'适用于各种任务的战术建议。',terminidIntelTitle:'对抗虫族',terminidIntel:'优先控制虫群、使用范围伤害、保持目标区域安全，并携带可靠的反重甲手段。',automatonIntelTitle:'对抗自动机',automatonIntel:'利用掩体和精准射击，携带可靠的穿甲武器，并协调处理重型目标。',illuminateIntelTitle:'对抗光能族',illuminateIntel:'针对混合敌群使用灵活伤害，集中火力攻击耐久或有护盾的敌人，并保留团队支援位置。',missionIntelTitle:'按任务类型',missionIntel:'防守任务适合区域封锁和哨戒炮；闪击任务需要机动和快速完成目标；重型目标任务需要协同反装甲。',intelDisclaimer:'这些是战术参考，并非官方胜率统计。请在游戏中确认当前平衡和解锁情况。',aboutTitle:'关于 DiverSignal',aboutText:'DiverSignal 是 Helldivers 2 的独立辅助工具，帮助玩家了解当前命令并在游戏过程中准备战术装备。与 Arrowhead Game Studios 或 Sony 无关联。',donationLink:'支持项目（链接即将推出）',bannerAdSlot:'广告横幅预留位',bannerAdNote:'预留给未来广告服务商接入。',rewardAdSlot:'自愿观看奖励广告预留位',rewardAdNote:'配置广告服务商和规则前不会启用广告或奖励。',dispatchesTitle:'银河通讯',officialNewsTitle:'官方更新',spaceStationTitle:'太空站',communityTitle:'社区情报',communityText:'用于展示战术笔记和社区观察。建议仅供参考，不是官方统计。',warIntelligence:'战争情报',combatRecommendations:'战斗建议',rulesBased:'规则指南',archivesLabel:'档案',navOrders:'命令',navLoadout:'装备',navIntel:'战斗情报',navDetails:'命令详情',navMore:'更多',refresh:'刷新',active:'进行中',completed:'已完成',expired:'已过期',progress:'进度',deadline:'截止时间',objective:'目标',noMissionData:'数据源未提供细分任务目标。',mission:'任务',selectMission:'选择任务查看完整命令详情。',balanced:'均衡',antiarmor:'反装甲',crowd:'群体控制',support:'团队支援',stealth:'潜行 / 机动',eradicate:'歼灭 / 高击杀',defense:'防守 / 固守阵地',objectives:'目标 / 行动',recon:'侦察 / 潜行',extermination:'重型目标',escort:'护送 / 平民',blitz:'闪击 / 速度',lowDifficulty:'难度 1–4',midDifficulty:'难度 5–7',highDifficulty:'难度 8–10',recommendation:'推荐装备：',disclaimer:'战术参考指南，并非实时胜率统计。请在游戏中确认当前平衡和解锁情况。'},
    ru:{terminids:'Терминиды',automatons:'Автоматоны',illuminate:'Иллюминаты',notificationsTitle:'Уведомления о приказах',notificationsSub:'Push отправляется только при новом главном приказе или завершении текущего.',enableNotifications:'Включить уведомления о приказах',pushNotEnabled:'Уведомления на этом устройстве не включены.',pushEnabled:'Уведомления о приказах включены.',pushUnsupported:'Браузер не поддерживает push-уведомления.',pushDenied:'Разрешение на уведомления отклонено.',pushError:'Не удалось включить уведомления. Проверьте настройки сайта.',supremeOrder:'ВЫСШЕЕ КОМАНДОВАНИЕ // ГЛАВНЫЙ ПРИКАЗ',orderLoading:'Загрузка текущего главного приказа…',orderBriefLoading:'Последняя директива появится здесь.',missionListTitle:'Задачи главного приказа',missionListSub:'Краткая строка для каждой задачи. Выберите задачу для подробностей.',priority:'ПРИОРИТЕТ',loadoutTitle:'Советник по снаряжению',loadoutSub:'Выберите фракцию врага, тип миссии и стиль боя.',enemyFaction:'Фракция врага',missionType:'Тип миссии',combatStyle:'Стиль боя',difficulty:'Сложность',orderDetailsTitle:'Подробности главного приказа',orderDetailsSub:'Цели, прогресс и срок выполнения.',orderDetailsEmpty:'Ожидание данных текущего приказа.',combatIntelTitle:'Боевая разведка',combatIntelSub:'Общие тактические рекомендации для любых миссий.',terminidIntelTitle:'Против терминидов',terminidIntel:'Контролируйте рои, используйте урон по площади, защищайте цели и берите надёжное средство против тяжёлой брони.',automatonIntelTitle:'Против автоматонов',automatonIntel:'Используйте укрытия и точность, берите надёжное бронепробитие и координируйте борьбу с тяжёлыми целями.',illuminateIntelTitle:'Против иллюминатов',illuminateIntel:'Используйте универсальный урон против смешанных групп и концентрируйте огонь по прочным или защищённым врагам.',missionIntelTitle:'По типу миссии',missionIntel:'Для обороны полезны зоны запрета и турели; для Blitz — мобильность; против тяжёлых целей требуется скоординированное противотанковое оружие.',intelDisclaimer:'Это тактические ориентиры, а не официальная статистика побед. Проверяйте баланс и доступность в игре.',aboutTitle:'О DiverSignal',aboutText:'DiverSignal — независимый помощник для Helldivers 2, который помогает следить за приказами и готовить снаряжение во время игры. Не связан с Arrowhead Game Studios или Sony.',donationLink:'Поддержать проект (ссылка скоро появится)',bannerAdSlot:'Место для рекламного баннера',bannerAdNote:'Зарезервировано для будущего рекламного сервиса.',rewardAdSlot:'Место для добровольной рекламы с наградой',rewardAdNote:'Реклама и награды не активны до настройки сервиса и правил.',dispatchesTitle:'Галактические сводки',officialNewsTitle:'Официальные обновления',spaceStationTitle:'Космическая станция',communityTitle:'Данные сообщества',communityText:'Тактические заметки и наблюдения сообщества. Рекомендации не являются официальной статистикой.',warIntelligence:'Военная разведка',combatRecommendations:'Боевые рекомендации',rulesBased:'Справочник по правилам',archivesLabel:'Архивы',navOrders:'ПРИКАЗЫ',navLoadout:'СНАРЯЖЕНИЕ',navIntel:'БОЕВАЯ РАЗВЕДКА',navDetails:'ДЕТАЛИ ПРИКАЗА',navMore:'ЕЩЁ',refresh:'Обновить',active:'Активен',completed:'Завершён',expired:'Истёк',progress:'Прогресс',deadline:'Срок',objective:'Цель',noMissionData:'Источник данных не предоставил цели по отдельным миссиям.',mission:'Миссия',selectMission:'Выберите миссию для подробностей.',balanced:'Сбалансированный',antiarmor:'Против брони',crowd:'Контроль толпы',support:'Поддержка команды',stealth:'Скрытность / мобильность',eradicate:'Уничтожение / много целей',defense:'Оборона / удержание позиции',objectives:'Цели / операции',recon:'Разведка / скрытность',extermination:'Тяжёлые цели',escort:'Эскорт / гражданские',blitz:'Blitz / скорость',lowDifficulty:'Сложность 1–4',midDifficulty:'Сложность 5–7',highDifficulty:'Сложность 8–10',recommendation:'Рекомендуемое снаряжение:',disclaimer:'Тактический справочник, не статистика побед в реальном времени. Проверяйте текущий баланс в игре.'},
    pt:{terminids:'Terminids',automatons:'Automatons',illuminate:'Illuminate',notificationsTitle:'Notificações de ordens',notificationsSub:'Só uma nova Ordem Suprema ou o fim da ordem atual pode gerar uma notificação.',enableNotifications:'Ativar notificações de ordens',pushNotEnabled:'As notificações não estão ativadas neste dispositivo.',pushEnabled:'As notificações de ordens estão ativadas.',pushUnsupported:'Este navegador não oferece suporte a notificações push.',pushDenied:'A permissão para notificações foi negada.',pushError:'Não foi possível ativar as notificações. Verifique a configuração do site.',supremeOrder:'ALTO COMANDO // ORDEM SUPREMA',orderLoading:'Obtendo a Ordem Suprema atual…',orderBriefLoading:'A diretiva mais recente aparecerá aqui.',missionListTitle:'Missões da Ordem Suprema',missionListSub:'Uma linha resumida por missão. Selecione uma para ver os detalhes.',priority:'PRIORIDADE',loadoutTitle:'Consultor de equipamento',loadoutSub:'Escolha a facção inimiga, o tipo de missão e o estilo de combate.',enemyFaction:'Facção inimiga',missionType:'Tipo de missão',combatStyle:'Estilo de combate',difficulty:'Dificuldade',orderDetailsTitle:'Detalhes da Ordem Suprema',orderDetailsSub:'Objetivos, progresso e prazo.',orderDetailsEmpty:'Aguardando dados da ordem atual.',combatIntelTitle:'Inteligência de combate',combatIntelSub:'Orientações táticas gerais para qualquer missão.',terminidIntelTitle:'Contra os Terminids',terminidIntel:'Priorize controle de enxames, dano em área, espaço seguro perto dos objetivos e uma resposta confiável contra alvos blindados.',automatonIntelTitle:'Contra os Automatons',automatonIntel:'Use cobertura e precisão, leve penetração de blindagem confiável e coordene ferramentas contra alvos pesados.',illuminateIntelTitle:'Contra os Illuminate',illuminateIntel:'Use dano flexível contra grupos mistos e concentre o fogo em inimigos resistentes ou protegidos.',missionIntelTitle:'Por tipo de missão',missionIntel:'Defesa se beneficia de controle de área e sentinelas; Blitz favorece mobilidade; alvos pesados exigem antiblindagem coordenada.',intelDisclaimer:'São orientações táticas, não estatísticas oficiais de vitória. Confira o balanceamento no jogo.',aboutTitle:'Sobre o DiverSignal',aboutText:'DiverSignal é um complemento independente para Helldivers 2 que ajuda jogadores a acompanhar ordens e preparar equipamentos enquanto jogam. Não é afiliado à Arrowhead Game Studios nem à Sony.',donationLink:'Apoie o projeto (link em breve)',bannerAdSlot:'Espaço para banner publicitário',bannerAdNote:'Reservado para integração futura de anúncios.',rewardAdSlot:'Espaço para anúncio opcional com recompensa',rewardAdNote:'Nenhum anúncio ou recompensa será ativado antes da configuração do provedor.',dispatchesTitle:'Comunicados galácticos',officialNewsTitle:'Atualizações oficiais',spaceStationTitle:'Estação espacial',communityTitle:'Inteligência da comunidade',communityText:'Área de referência para notas táticas e observações da comunidade. Recomendações não são estatísticas oficiais.',warIntelligence:'Inteligência de guerra',combatRecommendations:'Recomendações de combate',rulesBased:'Guia baseado em regras',archivesLabel:'Arquivos',navOrders:'ORDENS',navLoadout:'EQUIPAMENTO',navIntel:'COMBATE',navDetails:'DETALHES DA ORDEM',navMore:'MAIS',refresh:'Atualizar',active:'Ativa',completed:'Concluída',expired:'Expirada',progress:'Progresso',deadline:'Prazo',objective:'Objetivo',noMissionData:'A fonte não forneceu objetivos detalhados por missão.',mission:'Missão',selectMission:'Selecione uma missão para ver os detalhes.',balanced:'Equilibrado',antiarmor:'Antiblindagem',crowd:'Controle de multidões',support:'Apoio à equipe',stealth:'Furtividade / mobilidade',eradicate:'Erradicação / muitas eliminações',defense:'Defesa / manter posição',objectives:'Objetivos / operações',recon:'Reconhecimento / furtividade',extermination:'Alvos pesados',escort:'Escolta / civis',blitz:'Blitz / rapidez',lowDifficulty:'Dificuldade 1–4',midDifficulty:'Dificuldade 5–7',highDifficulty:'Dificuldade 8–10',recommendation:'Equipamento sugerido:',disclaimer:'Guia heurístico, não estatísticas de vitórias ao vivo. Confira o balanceamento no jogo.'},
    de:{terminids:'Terminiden',automatons:'Automaten',illuminate:'Illuminate',notificationsTitle:'Befehlsbenachrichtigungen',notificationsSub:'Push-Nachrichten gibt es nur bei einem neuen Oberbefehl oder dem Ende des aktuellen Befehls.',enableNotifications:'Befehlsbenachrichtigungen aktivieren',pushNotEnabled:'Benachrichtigungen sind auf diesem Gerät nicht aktiviert.',pushEnabled:'Befehlsbenachrichtigungen sind aktiviert.',pushUnsupported:'Dieser Browser unterstützt keine Push-Benachrichtigungen.',pushDenied:'Die Benachrichtigungsberechtigung wurde verweigert.',pushError:'Benachrichtigungen konnten nicht aktiviert werden. Prüfe die Website-Konfiguration.',supremeOrder:'OBERKOMMANDO // OBERBEFEHL',orderLoading:'Aktueller Oberbefehl wird geladen…',orderBriefLoading:'Die neueste Anweisung wird hier angezeigt.',missionListTitle:'Missionen des Oberbefehls',missionListSub:'Eine kurze Zeile pro Mission. Wähle eine Mission für Details.',priority:'PRIORITÄT',loadoutTitle:'Ausrüstungsberater',loadoutSub:'Wähle Gegnerfraktion, Missionstyp und Kampfstil.',enemyFaction:'Gegnerfraktion',missionType:'Missionstyp',combatStyle:'Kampfstil',difficulty:'Schwierigkeit',orderDetailsTitle:'Details zum Oberbefehl',orderDetailsSub:'Ziele, Fortschritt und Frist.',orderDetailsEmpty:'Warte auf Daten zum aktuellen Befehl.',combatIntelTitle:'Kampf-Intel',combatIntelSub:'Allgemeine taktische Hinweise für jede Mission.',terminidIntelTitle:'Gegen Terminiden',terminidIntel:'Priorisiere Schwarmkontrolle, Flächenschaden, sichere Bereiche um Ziele und eine zuverlässige Antwort auf schwer gepanzerte Gegner.',automatonIntelTitle:'Gegen Automaten',automatonIntel:'Nutze Deckung und Präzision, bringe zuverlässige Panzerungsdurchdringung mit und koordiniere Werkzeuge gegen schwere Ziele.',illuminateIntelTitle:'Gegen Illuminate',illuminateIntel:'Nutze flexiblen Schaden gegen gemischte Gruppen und konzentriere Feuer auf robuste oder abgeschirmte Gegner.',missionIntelTitle:'Nach Missionstyp',missionIntel:'Verteidigung profitiert von Flächenkontrolle und Geschützen; Blitzmissionen brauchen Mobilität; schwere Ziele erfordern abgestimmte Panzerabwehr.',intelDisclaimer:'Taktische Ausgangspunkte, keine offiziellen Siegesstatistiken. Prüfe Balance und Freischaltungen im Spiel.',aboutTitle:'Über DiverSignal',aboutText:'DiverSignal ist ein unabhängiger Begleiter für Helldivers 2, der aktuelle Befehle verständlich macht und taktische Ausrüstung während des Spiels vorbereitet. Keine Verbindung zu Arrowhead Game Studios oder Sony.',donationLink:'Projekt unterstützen (Link folgt)',bannerAdSlot:'Platz für Werbebanner',bannerAdNote:'Für zukünftige Werbeanbieter reserviert.',rewardAdSlot:'Platz für freiwillige Reward-Werbung',rewardAdNote:'Werbung und Belohnungen bleiben bis zur Einrichtung deaktiviert.',dispatchesTitle:'Galaktische Meldungen',officialNewsTitle:'Offizielle Updates',spaceStationTitle:'Raumstation',communityTitle:'Community-Intel',communityText:'Referenzbereich für taktische Notizen und Community-Beobachtungen. Empfehlungen sind keine offiziellen Statistiken.',warIntelligence:'Kriegsinformationen',combatRecommendations:'Kampfempfehlungen',rulesBased:'Regelbasierter Leitfaden',archivesLabel:'Archiv',navOrders:'BEFEHLE',navLoadout:'AUSRÜSTUNG',navIntel:'KAMPF-INTEL',navDetails:'BEFEHLSDETAILS',navMore:'MEHR',refresh:'Aktualisieren',active:'Aktiv',completed:'Abgeschlossen',expired:'Abgelaufen',progress:'Fortschritt',deadline:'Frist',objective:'Ziel',noMissionData:'Die Datenquelle enthält keine aufgeschlüsselten Missionsziele.',mission:'Mission',selectMission:'Wähle eine Mission für vollständige Details.',balanced:'Ausgewogen',antiarmor:'Panzerabwehr',crowd:'Massenkontrolle',support:'Team-Unterstützung',stealth:'Tarnung / Mobilität',eradicate:'Vernichtung / viele Abschüsse',defense:'Verteidigung / Position halten',objectives:'Ziele / Operationen',recon:'Aufklärung / Tarnung',extermination:'Schwere Ziele',escort:'Eskorte / Zivilisten',blitz:'Blitz / Geschwindigkeit',lowDifficulty:'Schwierigkeit 1–4',midDifficulty:'Schwierigkeit 5–7',highDifficulty:'Schwierigkeit 8–10',recommendation:'Empfohlene Ausrüstung:',disclaimer:'Heuristischer Leitfaden, keine Live-Siegstatistik. Prüfe die aktuelle Balance im Spiel.'}
  };
  const langNames={en:'English',zh:'简体中文',ru:'Русский',es:'Español',pt:'Português',de:'Deutsch'};
  let gcLanguage='en';
  function cleanText(v){return String(v??'').replace(/<\s*\/?\s*[a-z][^>]*>/gi,' ').replace(/\s+/g,' ').trim();}
  const esc = v => cleanText(v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  // Safe Steam/Arrowhead BBCode renderer. Escape source text first, then allow only known formatting tags.
  function renderBBCode(value){
    let s=String(value??'');
    // Normalize HTML-like game markup and already-escaped malformed tags before processing BBCode.
    s=s.replace(/<\s*\/?\s*i\s*=\s*\d+\s*>/gi,' ')
       .replace(/&lt;\s*\/?\s*i\s*=\s*\d+\s*&gt;/gi,' ')
       .replace(/<\s*\/?\s*[a-z][^>]*>/gi,' ');
    // Preserve paragraph breaks while removing unsupported list wrappers into readable list lines.
    s=s.replace(/\[\/?(?:list|ul|ol)\]/gi,'\n')
       .replace(/\[\*\]/gi,'\n• ')
       .replace(/\[p\]/gi,'\n').replace(/\[\/p\]/gi,'\n')
       .replace(/\[br\s*\/??\]/gi,'\n');
    // URLs are converted only after escaping the complete input, and hrefs are restricted to HTTP(S).
    s=esc(s);
    s=s.replace(/\[url=(https?:\/\/[^\]\s]+)\]([\s\S]*?)\[\/url\]/gi,(_,url,label)=>`<a href="${url.replace(/&amp;/g,'&')}" target="_blank" rel="noopener noreferrer">${label}</a>`)
       .replace(/\[url\](https?:\/\/[^\[]+)\[\/url\]/gi,(_,url)=>{const u=url.trim();return `<a href="${u.replace(/&amp;/g,'&')}" target="_blank" rel="noopener noreferrer">${u}</a>`;})
       .replace(/\[b\]([\s\S]*?)\[\/b\]/gi,'<strong>$1</strong>')
       .replace(/\[i\]([\s\S]*?)\[\/i\]/gi,'<em>$1</em>')
       .replace(/\[u\]([\s\S]*?)\[\/u\]/gi,'<u>$1</u>')
       .replace(/\[(?:s|strike)\]([\s\S]*?)\[\/(?:s|strike)\]/gi,'<s>$1</s>')
       .replace(/\[spoiler\]([\s\S]*?)\[\/spoiler\]/gi,'<span class="spoiler">$1</span>')
       .replace(/\[h([1-6])\]([\s\S]*?)\[\/h\1\]/gi,(_,n,body)=>`<h${n}>${body}</h${n}>`)
       .replace(/\[\/?(?:list|ul|ol|\*|p|br)\]/gi,'')
       .replace(/\[\/?[a-z][^\]]*\]/gi,'');
    return s.split(/\n+/).map(line=>line.trim()).filter(Boolean).join('<br>');
  }
  function setLanguage(lang){
    gcLanguage=i18n[lang]?lang:'en';document.documentElement.lang=gcLanguage;
    document.cookie=`gc_language=${encodeURIComponent(gcLanguage)}; Max-Age=31536000; Path=/; SameSite=Lax`;
    try{localStorage.setItem('gc_language',gcLanguage);}catch(e){}
    const picker=document.getElementById('languageSelect');if(picker)picker.value=gcLanguage;const refreshButton=document.getElementById('refreshBtn');if(refreshButton&&!refreshButton.disabled)refreshButton.textContent='↻ '+i18n[gcLanguage].refresh;
    document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.dataset.i18n;if(i18n[gcLanguage][k])el.textContent=i18n[gcLanguage][k];});
    document.querySelectorAll('.app-nav [data-view]').forEach(b=>{const key={orders:'navOrders',loadout:'navLoadout',intel:'navIntel',details:'navDetails',more:'navMore'}[b.dataset.view];if(key)b.textContent=i18n[gcLanguage][key];});
    const optKeys={loadoutFaction:{terminids:'terminids',automatons:'automatons',illuminate:'illuminate'},loadoutRole:{balanced:'balanced',antiarmor:'antiarmor',crowd:'crowd',support:'support',stealth:'stealth'},loadoutMission:{eradicate:'eradicate',defense:'defense',objectives:'objectives',recon:'recon',extermination:'extermination',escort:'escort',blitz:'blitz'},loadoutDifficulty:{low:'lowDifficulty',mid:'midDifficulty',high:'highDifficulty'}};
    Object.entries(optKeys).forEach(([id,map])=>{const el=document.getElementById(id);if(el)Array.from(el.options).forEach(o=>{if(map[o.value]&&i18n[gcLanguage][map[o.value]])o.textContent=i18n[gcLanguage][map[o.value]];});});
    renderLoadoutSuggestion();
  }
  document.getElementById('languageSelect')?.addEventListener('change',e=>setLanguage(e.target.value));
  function renderLoadoutSuggestion(){
    const f=document.getElementById('loadoutFaction')?.value||'terminids',r=document.getElementById('loadoutRole')?.value||'balanced',d=document.getElementById('loadoutDifficulty')?.value||'mid',m=document.getElementById('loadoutMission')?.value||'objectives';
    const data={
      automatons:{balanced:['Automaton field kit','Favor precision, cover and anti-armor capacity.','Eagle Airstrike · Orbital Precision Strike · Orbital Gatling Barrage · Autocannon Sentry'],antiarmor:['Automaton armor breaker','Prioritize dependable penetration and precision against heavy units.','Orbital Railcannon Strike · Orbital Precision Strike · Eagle Airstrike · EMS Mortar Sentry'],crowd:['Automaton suppression','Bring area damage for clustered infantry without losing all anti-armor coverage.','Eagle Cluster Bomb · Orbital Gatling Barrage · Eagle Airstrike · Autocannon Sentry'],support:['Automaton squad support','Coordinate anti-armor with teammates and provide resupply or defensive cover.','Supply Pack · Eagle Airstrike · Shield Generator Relay · EMS Mortar Sentry'],stealth:['Automaton reconnaissance','Use mobility and precision; avoid prolonged fights and preserve an emergency strike.','Orbital Precision Strike · Eagle Airstrike · Jump Pack · Smoke Strike']},
      terminids:{balanced:['Terminid field kit','Balance swarm clearing with a reliable answer to armored threats.','Eagle Napalm Airstrike · Orbital Gatling Barrage · Orbital Precision Strike · Guard Dog Rover'],antiarmor:['Terminid armor breaker','Reserve a dedicated slot for chargers and other heavy targets.','Orbital Railcannon Strike · Eagle Airstrike · Orbital Precision Strike · Autocannon Sentry'],crowd:['Terminid swarm control','Prioritize sustained area damage and safe space around objectives.','Eagle Napalm Airstrike · Orbital Gatling Barrage · Gatling Sentry · Incendiary Mines'],support:['Terminid squad support','Cover teammates, thin swarms and provide resupply or defensive sentries.','Supply Pack · Autocannon Sentry · Eagle Airstrike · Gatling Sentry'],stealth:['Terminid reconnaissance','Move between objectives, avoid extra patrols and save strikes for emergencies.','Eagle Airstrike · Orbital Precision Strike · Jump Pack · Smoke Strike']},
      illuminate:{balanced:['Illuminate field kit','Use flexible damage for mixed targets and keep tools for durable enemies.','Eagle Airstrike · Orbital Precision Strike · Orbital Railcannon Strike · Autocannon Sentry'],antiarmor:['Illuminate shield breaker','Coordinate concentrated burst damage against shielded or durable targets.','Orbital Railcannon Strike · Orbital Precision Strike · Eagle Airstrike · Autocannon Sentry'],crowd:['Illuminate crowd control','Handle groups of light targets while preserving a slot for priority threats.','Eagle Cluster Bomb · Orbital Gatling Barrage · Eagle Airstrike · Autocannon Sentry'],support:['Illuminate squad support','Protect teammates, provide resupply or defense, and retain a flexible strike.','Supply Pack · Shield Generator Relay · Autocannon Sentry · Eagle Airstrike'],stealth:['Illuminate reconnaissance','Stay mobile, isolate threats and save strikes for objectives or extraction.','Orbital Precision Strike · Eagle Airstrike · Jump Pack · Smoke Strike']}
    };
    const x=data[f]?.[r]||data.terminids.balanced, t=i18n[gcLanguage], faction={automatons:'Automatons',terminids:'Terminids',illuminate:'Illuminate'}[f];
    const missionNames={eradicate:t.eradicate,defense:t.defense,objectives:t.objectives,recon:t.recon,extermination:t.extermination,escort:t.escort,blitz:t.blitz};
    const missionAdvice={eradicate:'Prioritize sustained area damage and fast swarm clearing.',defense:'Favor sentries, area denial and tools that hold a fixed position.',objectives:'Bring a flexible kit that can clear patrols and handle priority targets.',recon:'Favor mobility, precision and tools that let you avoid unnecessary fights.',extermination:'Coordinate anti-armor and concentrated damage against heavy targets.',escort:'Protect routes and civilians with crowd control and reliable defensive coverage.',blitz:'Favor mobility, fast objective completion and cooldown-efficient strikes.'};
    const extra=(d==='high'?' At high difficulty, coordinate anti-armor and crowd control.':d==='mid'?' At medium difficulty, keep one flexible tool.':'');
    const title=gcLanguage==='es'?({'Automaton field kit':'Equipo de campo contra autómatas','Automaton armor breaker':'Antiblindaje contra autómatas','Automaton suppression':'Supresión de autómatas','Automaton squad support':'Apoyo de escuadrón contra autómatas','Automaton reconnaissance':'Reconocimiento contra autómatas','Terminid field kit':'Equipo de campo contra termínidos','Terminid armor breaker':'Antiblindaje contra termínidos','Terminid swarm control':'Control de enjambres termínidos','Terminid squad support':'Apoyo contra termínidos','Terminid reconnaissance':'Reconocimiento contra termínidos','Illuminate field kit':'Equipo de campo contra iluminados','Illuminate shield breaker':'Rompeescudos contra iluminados','Illuminate crowd control':'Control de grupos de iluminados','Illuminate squad support':'Apoyo contra iluminados','Illuminate reconnaissance':'Reconocimiento contra iluminados'}[x[0]]||x[0]):x[0];
    const desc=gcLanguage==='es'?'Prioriza esta configuración como punto de partida; adapta las herramientas a los objetivos y la composición del escuadrón.':x[1];
    const kit=gcLanguage==='es'?x[2].replaceAll('Supply pack','mochila de suministros').replaceAll('Eagle airstrike','ataque aéreo Eagle').replaceAll('Orbital precision strike','ataque orbital de precisión').replaceAll('Gatling sentry','centinela Gatling'):x[2];
    const box=document.getElementById('loadoutResult');if(!box)return;
    const items=x[2].split('·').map(v=>v.trim()).filter(Boolean).slice(0,4);
    const stratagemInfo={
      'Eagle Airstrike':{arrows:'↑ → ↓ →',wiki:'Eagle_Airstrike',imageFile:'Eagle Airstrike Stratagem Icon.svg',icon:'✈'},
      'Orbital Precision Strike':{arrows:'→ → ↑',wiki:'Orbital_Precision_Strike',imageFile:'Orbital Precision Strike Stratagem Icon.svg',icon:'☄'},
      'Orbital Gatling Barrage':{arrows:'→ ↓ ← ↑ ↑',wiki:'Orbital_Gatling_Barrage',imageFile:'Orbital Gatling Barrage Stratagem Icon.svg',icon:'☄'},
      'Autocannon Sentry':{arrows:'↓ ↑ → ↑ →',wiki:'Autocannon_Sentry',imageFile:'Autocannon Sentry Stratagem Icon.svg',icon:'▤'},
      'Orbital Railcannon Strike':{arrows:'→ ↑ ↓ ↑',wiki:'Orbital_Railcannon_Strike',imageFile:'Orbital Railcannon Strike Stratagem Icon.svg',icon:'☄'},
      'EMS Mortar Sentry':{arrows:'↓ ↑ → ↓ →',wiki:'EMS_Mortar_Sentry',imageFile:'EMS Mortar Sentry Stratagem Icon.svg',icon:'▤'},
      'Eagle Cluster Bomb':{arrows:'↑ → ↓ ↓ →',wiki:'Eagle_Cluster_Bomb',imageFile:'Eagle Cluster Bomb Stratagem Icon.svg',icon:'✈'},
      'Supply Pack':{arrows:'↓ ← ↓ ↑ ↑ ↓',wiki:'Supply_Pack',imageFile:'B-1 Supply Pack Stratagem Icon.svg',icon:'▣'},
      'Shield Generator Relay':{arrows:'↓ ↓ ← → ← →',wiki:'Shield_Generator_Relay',imageFile:'Shield Generator Relay Stratagem Icon.svg',icon:'⬡'},
      'Orbital Railcannon Strike':{arrows:'→ ↑ ↓ ↑',wiki:'Orbital_Railcannon_Strike',imageFile:'Orbital Railcannon Strike Stratagem Icon.svg',icon:'☄'},
      'Eagle Napalm Airstrike':{arrows:'↑ → ↓ ↑',wiki:'Eagle_Napalm_Airstrike',imageFile:'Eagle Napalm Airstrike Stratagem Icon.svg',icon:'✈'},
      'Guard Dog Rover':{arrows:'↓ ↑ ← ↑ → ↓',wiki:'Guard_Dog_Rover',imageFile:'AX LAS-5 Rover Stratagem Icon.svg',imageFiles:['AX LAS-5 Rover Stratagem Icon.svg','AX-LAS-5 Rover Stratagem Icon.svg','Guard Dog Rover Stratagem Icon.svg'],icon:'◈'},
      'Gatling Sentry':{arrows:'↓ ↑ → ←',wiki:'Gatling_Sentry',imageFile:'Gatling Sentry Stratagem Icon.svg',icon:'▤'},
      'Incendiary Mines':{arrows:'↓ ← ↑ →',wiki:'Incendiary_Mines',imageFile:'MD-I4 Incendiary Mines Stratagem Icon.svg',imageFiles:['MD-I4 Incendiary Mines Stratagem Icon.svg','Incendiary Mines Stratagem Icon.svg'],icon:'✳'},
      'Jump Pack':{arrows:'↓ ↑ ↑ ↓ ↑',wiki:'Jump_Pack',imageFile:'LIFT-850 Jump Pack Stratagem Icon.svg',imageFiles:['LIFT-850 Jump Pack Stratagem Icon.svg','Jump Pack Stratagem Icon.svg'],icon:'▣'},
      'Smoke Strike':{arrows:'↑ → ↓ ↓',wiki:'Eagle_Smoke_Strike',imageFile:'Eagle Smoke Strike Stratagem Icon.svg',icon:'☁'}
    };
    const cardMarkup=items.map((item,i)=>{const info=stratagemInfo[item]||{arrows:'Código en juego',wiki:item.replaceAll(' ','_'),imageFile:`${item} Stratagem Icon.svg`,icon:'✦'};const imageFiles=[...(info.imageFiles||[]),info.imageFile||`${item} Stratagem Icon.svg`,`${item} Icon.svg`];return `<article class="loadout-stratagem-card"><div class="loadout-art"><span class="art-fallback">${info.icon}</span><img alt="Icono de ${esc(item)}" data-image-files="${esc(JSON.stringify([...new Set(imageFiles)]))}" loading="lazy" hidden></div><div class="loadout-stratagem-copy"><h4>${esc(item)}</h4><div class="stratagem-code"><span>${gcLanguage==='es'?'Código de activación':'Call-in code'}</span><strong>${esc(info.arrows)}</strong></div><div class="module-actions"><a class="tag wiki-source" href="https://helldivers.wiki.gg/wiki/${encodeURIComponent(info.wiki)}" target="_blank" rel="noopener noreferrer">Wiki ↗</a></div></div></article>`;}).join('');
    box.innerHTML=`<strong>${esc(faction)} // ${esc(title)}</strong><p><b>${esc(t.missionType)}:</b> ${esc(missionNames[m]||m)}. ${esc(missionAdvice[m]||'')}</p><p>${esc(desc+extra)}</p><div class="loadout-stratagem-grid">${cardMarkup}</div><p><b>${esc(t.recommendation)}</b> ${esc(kit)}</p><p style="opacity:.75">${esc(t.disclaimer)}</p>`;
    loadWikiArtwork(box);
  }
  const wikiImageCache=new Map();
  function loadWikiArtwork(root){
    const images=Array.from(root.querySelectorAll('img[data-image-files]'));
    images.forEach(img=>{
      let candidates=[];
      try{candidates=JSON.parse(img.dataset.imageFiles||'[]')}catch(e){}
      const key=candidates.join('|');
      const cached=wikiImageCache.get(key);
      if(cached){
        img.onload=()=>{img.hidden=img.naturalWidth<=0;};
        img.onerror=()=>{img.hidden=true;img.parentElement.classList.remove('has-art');};
        img.hidden=false;
        img.src=cached;
        return;
      }

      let index=0;
      const tryNext=()=>{
        if(index>=candidates.length){
          img.hidden=true;
          img.parentElement.classList.remove('has-art');
          img.removeAttribute('src');
          return;
        }

        const file=candidates[index++];
        // Use the direct public image path. Special:FilePath is returning HTTP 403
        // in the embedded request, while the direct /images/<filename> URL works.
        const basename=String(file).split('/').pop().replace(/\\/g,'/');
        const directUrl='https://helldivers.wiki.gg/images/'+basename.replace(/ /g,'_');

        // Preload separately so hidden/lazy rendering cannot prevent the request.
        const probe=new Image();
        probe.decoding='async';
        probe.onload=()=>{
          if(probe.naturalWidth>0){
            const resolved=probe.currentSrc||probe.src;
            img.onload=null;
            img.onerror=()=>{img.hidden=true;img.parentElement.classList.remove('has-art');};
            img.src=resolved;
            img.hidden=false;
            img.parentElement.classList.add('has-art');
            img.parentElement.classList.remove('no-art');
            wikiImageCache.set(key,resolved);
          }else{
            tryNext();
          }
        };
        probe.onerror=tryNext;
        probe.src=directUrl;
      };
      img.hidden=true;
      img.loading='eager';
      tryNext();
    });
  }
  ['loadoutFaction','loadoutMission','loadoutRole','loadoutDifficulty'].forEach(id=>document.getElementById(id)?.addEventListener('change',renderLoadoutSuggestion));
  const storedLang=(()=>{try{return localStorage.getItem('gc_language')}catch(e){return null}})();
  const cookieLang=(document.cookie.match(/(?:^|; )gc_language=([^;]+)/)||[])[1];
  setLanguage(storedLang||decodeURIComponent(cookieLang||'en'));
  const unbox = x => x && typeof x === 'object' && Object.prototype.hasOwnProperty.call(x,'data') ? x.data : x;

  const fmt = n => { const v = Number(n); return Number.isFinite(v) ? new Intl.NumberFormat('en-US',{notation:v>=1e6?'compact':'standard',maximumFractionDigits:1}).format(v) : '—'; };

  function initAppViews(){
    const nav=document.createElement('nav');nav.className='app-nav';nav.setAttribute('aria-label','Main navigation');
    const viewHost=document.createElement('div');viewHost.className='app-views';viewHost.id='appViews';
    const definitions=[
      {id:'orders',key:'navOrders',selectors:['#majorOrderBanner','#ordersPanel']},
      {id:'loadout',key:'navLoadout',selectors:['#loadoutSuggestion']},
      {id:'intel',key:'navIntel',selectors:['#combatIntelPanel']},
      {id:'details',key:'navDetails',selectors:['#orderDetailsPanel']},
      {id:'more',key:'navMore',selectors:['#aboutPanel','#notificationPanel','.ad-slot','.two-col']}
    ];
    const views={};
    definitions.forEach(d=>{
      const b=document.createElement('button');b.type='button';b.dataset.view=d.id;b.textContent=i18n[gcLanguage][d.key];b.addEventListener('click',()=>activateAppView(d.id));nav.appendChild(b);
      const v=document.createElement('section');v.className='app-view';v.id='view-'+d.id;v.dataset.view=d.id;viewHost.appendChild(v);views[d.id]=v;
    });
    const header=document.querySelector('.topbar');header.after(nav,viewHost);
    definitions.forEach(d=>d.selectors.forEach(sel=>document.querySelectorAll(sel).forEach(el=>views[d.id].appendChild(el))));
    const footer=document.querySelector('.shell > .footer');if(footer)views.more.appendChild(footer);
    window.activateAppView=activateAppView;
    function activateAppView(id){
      definitions.forEach(d=>{
        const active=d.id===id;views[d.id].classList.toggle('active',active);
        const b=nav.querySelector(`[data-view="${d.id}"]`);b.classList.toggle('active',active);b.setAttribute('aria-current',active?'page':'false');
      });
      try{history.replaceState(null,'','#'+id);}catch(e){}
    }
    const initial=location.hash.replace('#','');
    activateAppView(definitions.some(d=>d.id===initial)?initial:'orders');
  }
  initAppViews();
  async function enableOrderPush(){
    const status=$('pushStatus'),button=$('enableOrderPush'),t=i18n[gcLanguage];
    if(!('serviceWorker' in navigator)||!('PushManager' in window)||!('Notification' in window)){if(status)status.textContent=t.pushUnsupported;return;}
    try{
      const permission=await Notification.requestPermission();
      if(permission!=='granted'){if(status)status.textContent=t.pushDenied;return;}
      const keyResponse=await get('api/v1/push/key');
      const publicKey=keyResponse.data.public_key;
      const encodedKey=publicKey.replace(/-/g,'+').replace(/_/g,'/');const paddedKey=encodedKey+'='.repeat((4-encodedKey.length%4)%4);const applicationServerKey=Uint8Array.from(atob(paddedKey),c=>c.charCodeAt(0));
      const registration=await navigator.serviceWorker.register(endpoint('sw.js'));
      let subscription=await registration.pushManager.getSubscription();
      if(!subscription)subscription=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey});
      const response=await fetch(endpoint('api/v1/push/subscribe'),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({subscription:subscription.toJSON(),preferences:{orders:true}})});
      const result=await response.json();if(!response.ok||result.ok===false)throw new Error(result.error?.message||'Subscription failed');
      if(status)status.textContent=t.pushEnabled;if(button)button.textContent=t.pushEnabled;
    }catch(err){if(status)status.textContent=t.pushError;console.error('Push setup failed',err);}
  }
  $('enableOrderPush')?.addEventListener('click',enableOrderPush);
  $('donationLink')?.addEventListener('click',e=>e.preventDefault());

  async function get(path) { const r = await fetch(endpoint(path), {headers:{'Accept':'application/json'}, cache:'no-store'}); const j = await r.json(); if (!r.ok || j?.ok === false) throw new Error(j?.error?.message || `HTTP ${r.status}`); return {data:unbox(j),meta:j?.meta||{}}; }
  function first(o, keys, fallback=null) { for (const k of keys) if (o && o[k] !== undefined && o[k] !== null) return o[k]; return fallback; }
  function arr(v) { if (Array.isArray(v)) return v; if (v && typeof v === 'object') { for (const k of ['items','results','data','planets','campaigns','assignments','dispatches','news']) if (Array.isArray(v[k])) return v[k]; } return []; }
  function nameOf(v) { if (typeof v === 'string') return v; if (v && typeof v === 'object') return first(v,['name','title','value','faction'], 'Unknown'); return v == null ? 'Unknown' : String(v); }
  function dateText(v) { if (!v) return ''; const n = typeof v === 'number' ? (v < 1e12 ? v*1000 : v) : Date.parse(v); if (!Number.isFinite(n)) return String(v); return new Date(n).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}); }
  function getStats(w) { return first(w,['statistics','stats','galaxyStats'],{}) || {}; }
  function setText(id,v) { const el=$(id); if(el) el.textContent = v == null || v === '' ? '—' : cleanText(v); }
  function renderOrders(data) {
    const items=arr(data).filter(v=>v&&typeof v==='object');
    const primary=items[0]||null;
    const setting=primary?.setting&&typeof primary.setting==='object'?primary.setting:primary;
    const taskSource=setting?first(setting,['tasks','objectives','missions'],[]):[];
    const taskList=Array.isArray(taskSource)?taskSource.filter(x=>x&&typeof x==='object'):[];
    const progressValues=Array.isArray(primary?.progress)?primary.progress:[];
    const doneCount=progressValues.filter(v=>Number(v)>0).length;
    const hasTaskProgress=taskList.length>0&&progressValues.length>0;
    const orderProgress=hasTaskProgress
      ? `${doneCount}/${taskList.length} (${Math.round(doneCount/taskList.length*100)}%)`
      : (typeof primary?.progress==='number'
          ? (primary.progress<=1?Math.round(primary.progress*100):Math.round(primary.progress))+'%'
          : '—');
    const title=primary?first(setting,['overrideTitle','title','name'],first(primary,['title','name'],'Major Order')):'No active Supreme Order';
    const desc=primary?first(setting,['overrideBrief','briefing','taskDescription','description'],first(primary,['briefing','description','message'],'Current operational directive.')):'The latest order will appear when synchronized data is available.';
    const status=primary?first(primary,['progressText','status'],hasTaskProgress?(doneCount===taskList.length?'COMPLETED':'ACTIVE'):'ACTIVE'):'DATA UNAVAILABLE';
    const titleEl=$('supremeOrderTitle'),briefEl=$('supremeOrderBrief'),stateEl=$('supremeOrderState');
    if(titleEl)titleEl.textContent=cleanText(title);
    if(briefEl)briefEl.textContent=cleanText(desc);
    if(stateEl)stateEl.textContent=cleanText(status).toUpperCase();

    const rows=$('missionRows');
    const missionRows=taskList.length?taskList:[primary].filter(Boolean);
    const taskName=(task,i)=>{
      const type=Number(task?.type);
      const known={2:'Collect samples / complete assigned task',3:'Eliminate enemies',7:'Complete designated missions',9:'Complete designated objective',11:'Liberate designated planet',12:'Defend planets',13:'Hold designated planet',15:'Defeat the designated faction'};
      return known[type]||`${gcLanguage==='es'?'Objetivo':'Objective'} ${i+1}`;
    };
    const taskState=(i)=>{
      if(!Array.isArray(primary?.progress)||primary.progress[i]===undefined)return gcLanguage==='es'?'Progreso no disponible':'Progress unavailable';
      return Number(primary.progress[i])>0?(gcLanguage==='es'?'COMPLETADO':'COMPLETED'):(gcLanguage==='es'?'PENDIENTE':'PENDING');
    };
    if(rows){
      rows.innerHTML=missionRows.length?missionRows.map((m,i)=>{
        const rawMissionTitle=first(m,['description','title','name','objective','task'],null);
        const missionTitle=rawMissionTitle!==null&&!/^\d+$/.test(String(rawMissionTitle).trim())?cleanText(rawMissionTitle):taskName(m,i);
        const progressText=taskList.length?taskState(i):orderProgress;
        const missionStatus=taskList.length?taskState(i):cleanText(first(m,['status','state'],'ACTIVE')).toUpperCase();
        return `<button class="mission-line" type="button" data-mission-index="${i}"><span class="mission-index">${String(i+1).padStart(2,'0')}</span><span class="mission-line-title">${esc(missionTitle)}</span><span class="mission-line-progress">${esc(progressText)}</span><span class="tag">${esc(missionStatus)}</span><span class="mission-chevron">›</span></button>`;
      }).join(''):'<div class="empty">No active mission data has been synchronized yet.</div>';
      rows.querySelectorAll('[data-mission-index]').forEach(btn=>btn.addEventListener('click',()=>{
        const n=Number(btn.dataset.missionIndex);
        renderOrderDetails(primary,missionRows[n]||primary,n);
        if(window.activateAppView)window.activateAppView('details');
      }));
    }
    const stateTag=$('detailOrderState');if(stateTag)stateTag.textContent=cleanText(status).toUpperCase();
    renderOrderDetails(primary,null,0);
  }
  function renderOrderDetails(order,selectedMission=null,index=0){
    const box=$('orderDetailsContent');if(!box)return;
    if(!order){box.innerHTML=`<div class="empty">${esc(i18n[gcLanguage].orderDetailsEmpty)}</div>`;return;}
    const setting=order.setting&&typeof order.setting==='object'?order.setting:order;
    const title=cleanText(first(setting,['overrideTitle','title','name'],first(order,['title','name'],'Major Order')));
    const desc=cleanText(first(setting,['overrideBrief','briefing','taskDescription','description'],first(order,['briefing','description','message'],'No further description supplied.')));
    const taskSource=first(setting,['tasks','objectives','missions'],first(order,['tasks','objectives','missions'],[]));
    const tasks=Array.isArray(taskSource)?taskSource.filter(x=>x&&typeof x==='object'):[];
    const progressValues=Array.isArray(order.progress)?order.progress:[];
    const doneCount=progressValues.filter(v=>Number(v)>0).length;
    const hasTaskProgress=tasks.length>0&&progressValues.length>0;
    const status=cleanText(first(order,['progressText','status'],hasTaskProgress?(doneCount===tasks.length?'COMPLETED':'ACTIVE'):'ACTIVE'));
    let expiry=first(order,['expiration','expiresAt','expiry'],null);
    if(expiry==null&&Number.isFinite(Number(order.expiresIn)))expiry=Date.now()+Number(order.expiresIn)*1000;
    const mission=selectedMission||order;
    const missionTitle=cleanText(first(mission,['description','title','name','objective','task'],''));
    const progressText=hasTaskProgress
      ? `${doneCount}/${tasks.length} (${Math.round(doneCount/tasks.length*100)}%)`
      : (typeof order.progress==='number'?(order.progress<=1?Math.round(order.progress*100):Math.round(order.progress))+'%':'—');
    const t=i18n[gcLanguage];
    const taskName=(m,i)=>{
      const raw=first(m,['description','title','name','objective','task'],null);
      if(raw!==null&&!/^\d+$/.test(String(raw).trim()))return cleanText(raw);
      const type=Number(m.type);
      const es=gcLanguage==='es';
      const names={
        2:es?'Completar objetivo asignado / recoger muestras':'Complete assigned objective / collect samples',
        3:es?'Eliminar enemigos':'Eliminate enemies',
        7:es?'Completar misiones designadas':'Complete designated missions',
        9:es?'Completar objetivo designado':'Complete designated objective',
        11:es?'Liberar planeta designado':'Liberate designated planet',
        12:es?'Defender planetas':'Defend planets',
        13:es?'Mantener el control del planeta':'Hold designated planet',
        15:es?'Derrotar a la facción designada':'Defeat designated faction'
      };
      return names[type]||`${t.objective} ${i+1}`;
    };
    const detailRows=tasks.length?tasks.map((m,i)=>{
      const done=progressValues[i]!==undefined&&Number(progressValues[i])>0;
      const state=progressValues[i]===undefined?(gcLanguage==='es'?'Progreso no disponible':'Progress unavailable'):(done?(gcLanguage==='es'?'COMPLETADO':'COMPLETED'):(gcLanguage==='es'?'PENDIENTE':'PENDING'));
      const values=Array.isArray(m.values)?m.values:[];
      const target=values.find(v=>Number(v)>0);
      const targetText=target!==undefined?` · ${gcLanguage==='es'?'Objetivo':'Target'}: ${fmt(target)}`:'';
      return `<div class="detail-objective"><strong>${esc(taskName(m,i))}</strong><span>${esc(state+targetText)}</span></div>`;
    }).join(''):`<div class="empty">${esc(t.noMissionData)}</div>`;
    box.innerHTML=`<div class="order-detail-heading"><h4>${esc(title)}</h4><span class="tag hot">${esc(status.toUpperCase())}</span></div><p>${esc(desc)}</p><div class="detail-metrics"><div><small>${esc(t.progress)}</small><strong>${esc(progressText)}</strong></div><div><small>${esc(t.deadline)}</small><strong>${esc(expiry?dateText(expiry):'—')}</strong></div></div>${(missionTitle&&selectedMission)?`<div class="selected-mission"><small>${esc(t.mission)} ${index+1}</small><strong>${esc(missionTitle)}</strong></div>`:''}<h4 class="detail-section-title">${esc(t.objective)}s</h4><div class="detail-objectives">${detailRows}</div>`;
  }
  function renderDispatches(data) { const items=arr(data); const preview=$('incomingDispatchText');if(preview&&items[0])preview.textContent=String(first(items[0],['title','message','content'],'New dispatch')); $('dispatches').innerHTML=items.length?items.slice().sort((a,b)=>Number(first(b,['published','publishedAt','id'],0))-Number(first(a,['published','publishedAt','id'],0))).slice(0,5).map(d=>`<article class="feed-item"><div class="meta">DISPATCH ${esc(first(d,['id','Id'],'—'))} ${first(d,['published','Published'])?' · '+esc(dateText(first(d,['published','Published']))):''}</div><h4>${esc(first(d,['title'],'Galactic dispatch'))}</h4><p>${renderBBCode(first(d,['message','Message','content'],'No message text available.'))}</p></article>`).join(''):'<div class="empty">No dispatches synchronized yet.</div>'; }
  function renderSteam(data) { const items=arr(data); const preview=$('incomingNewsText');if(preview&&items[0])preview.textContent=String(first(items[0],['title','name','content'],'Official update')); $('steamNews').innerHTML=items.length?items.slice(0,5).map(n=>{const title=first(n,['title'],'Official update');const url=first(n,['url','link']);return `<article class="feed-item"><div class="meta">${esc(first(n,['author'],'HELLDIVERS 2'))}${first(n,['publishedAt','published'])?' · '+esc(dateText(first(n,['publishedAt','published']))):''}</div><h4>${url?`<a href="${esc(url)}" target="_blank" rel="noopener noreferrer">${esc(title)}</a>`:esc(title)}</h4><p>${renderBBCode(String(first(n,['content','description','summary'],'')).slice(0,1200))}</p></article>`;}).join(''):'<div class="empty">No official news synchronized yet.</div>'; }
  function renderStations(data) { const items=arr(data); const s=items[0]||data; if(!s||typeof s!=='object'||!Object.keys(s).length){$('station').innerHTML='<div class="empty">No station data available.</div>';return;} const p=s.planet||{};const actions=Array.isArray(s.tacticalActions)?s.tacticalActions:[];$('station').innerHTML=`<div class="order"><h4>${esc(nameOf(p.name?p:first(s,['name'],'Mobile Space Station')))}</h4><p>Station ID32: ${esc(first(s,['id32'],'—'))}</p><div class="order-bottom"><span>${actions.length} tactical actions</span><span>${s.electionEnd?'Election ends '+esc(dateText(s.electionEnd)):'Strategic asset'}</span></div>${actions.length?`<div style="margin-top:10px">${actions.slice(0,4).map(a=>`<span class="tag" style="margin:0 5px 5px 0">${esc(nameOf(first(a,['name','title','id'],a)))}</span>`).join('')}</div>`:''}</div>`; }
  async function loadOne(path, success, fail) { try {const result=await get(path);success(result.data,result.meta);return result;}catch(err){if(fail)fail(err);return null;} }
  async function loadAll() {
    const connection=$('connection');if(connection)connection.textContent='SYNCING ORDER AND INTEL FEEDS';
    const refresh=$('refreshBtn');if(refresh){refresh.disabled=true;refresh.textContent='↻ '+i18n[gcLanguage].refresh;}
    const results=await Promise.allSettled([
      loadOne('api/v1/assignments',renderOrders,()=>loadOne('api/v1/orders',renderOrders,err=>{const rows=$('missionRows');if(rows)rows.innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
      loadOne('api/v1/dispatches',renderDispatches,()=>loadOne('api/v1/news',renderDispatches,err=>{const el=$('dispatches');if(el)el.innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
      loadOne('api/v1/steam',renderSteam,()=>loadOne('api/v1/steam-news',renderSteam,err=>{const el=$('steamNews');if(el)el.innerHTML=`<div class="error">${esc(err.message)}</div>`;})),
      loadOne('api/v1/space-stations',renderStations,err=>{const el=$('station');if(el)el.innerHTML=`<div class="error">${esc(err.message)}</div>`;})
    ]);
    const ok=results.some(r=>r.status==='fulfilled'&&r.value!==null);
    state.lastUpdate=new Date();
    if(connection){connection.textContent=ok?'ORDER & INTEL FEEDS CONNECTED':'API ONLINE · DATA SYNC REQUIRED';connection.style.color=ok?'var(--acid)':'var(--gold)';}
    setText('footerUpdated',`Last checked ${state.lastUpdate.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}`);
    const warIntel=$('warIntelState');if(warIntel)warIntel.textContent=ok?'Data loaded':'Awaiting sync';
    const archiveState=$('archiveState');if(archiveState)archiveState.textContent=ok?'Feed loaded':'Awaiting sync';
    if(refresh){refresh.disabled=false;refresh.textContent='↻ '+i18n[gcLanguage].refresh;}
  }
  $('refreshBtn').addEventListener('click',loadAll);
  // API routes are queried independently so one unavailable feed does not blank the whole page.
  loadAll(); setInterval(loadAll,120000);
})();
</script></body></html>
HTML;
    exit;
}

if ($path === '/api/v1/health') respond(['service'=>'Galactic Command API','status'=>'up']);
if ($path === '/api/v1/push/key') respond(['public_key'=>(string)($appConfig['push']['public_key'] ?? '')]);

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
