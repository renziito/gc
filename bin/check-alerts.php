<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($vendor)) { fwrite(STDERR, "Install dependencies with composer install first.\n"); exit(2); }
require $vendor;
require_once dirname(__DIR__) . '/app/PushNotifier.php';

function getPayload(PDO $pdo, string $key): array {
    $q=$pdo->prepare('SELECT payload_json FROM source_payloads WHERE source_key=? AND entity_key IS NULL ORDER BY id DESC LIMIT 1');
    $q->execute([$key]);$v=$q->fetchColumn();if(!is_string($v))return [];
    $data=json_decode($v,true);return is_array($data)?$data:[];
}
function stateGet(PDO $pdo,string $key): ?array {
    $q=$pdo->prepare('SELECT state_hash,state_json FROM notification_state WHERE state_key=?');$q->execute([$key]);$r=$q->fetch();
    if(!$r)return null;return ['hash'=>$r['state_hash'],'json'=>json_decode((string)$r['state_json'],true)];
}
function stateSet(PDO $pdo,string $key,array $value): void {
    $json=json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $hash=hash('sha256',(string)$json);
    $q=$pdo->prepare('INSERT INTO notification_state(state_key,state_hash,state_json,updated_at) VALUES(?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE state_hash=VALUES(state_hash),state_json=VALUES(state_json),updated_at=UTC_TIMESTAMP()');
    $q->execute([$key,$hash,$json]);
}
function pushIfConfigured(PDO $pdo,array $config,string $title,string $body,string $topic,string $url='./#details'): void {
    if(empty($config['push']['public_key'])||empty($config['push']['private_key']))return;
    if((int)$pdo->query('SELECT COUNT(*) FROM push_subscriptions')->fetchColumn()<1)return;
    $r=(new PushNotifier($pdo,$config))->send($title,$body,$url,$topic);
    echo $title.': '.json_encode($r,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
}
function isTerminalOrder(array $o): ?string {
    $status=strtolower(trim((string)($o['status']??$o['state']??$o['progressText']??'')));
    if(preg_match('/complete|completed|success|succeeded|finished|victory|fulfilled/',$status))return 'completed';
    if(preg_match('/expired|failed|ended|cancelled|canceled/',$status))return 'ended';
    $expiry=$o['expiration']??$o['expiresAt']??$o['expiry']??null;
    if(is_numeric($expiry))$time=(int)$expiry>1000000000000?(int)($expiry/1000):(int)$expiry;
    else $time=is_string($expiry)?strtotime($expiry):false;
    if($time!==false&&$time>0&&$time<=time())return 'ended';
    return null;
}
try {
    $orders=getPayload($pdo,'assignments');
    $current=null;
    foreach($orders as $candidate){if(is_array($candidate)){$current=$candidate;break;}}
    if(!$current){echo "No current order payload; no notifications sent.\n";exit(0);}
    $identity=(string)($current['id']??$current['assignmentId']??$current['title']??$current['name']??'unknown');
    $title=trim(strip_tags((string)($current['title']??$current['name']??'Supreme Order')));
    $brief=trim(strip_tags((string)($current['briefing']??$current['description']??$current['message']??'')));
    $terminal=isTerminalOrder($current);
    $snapshot=['identity'=>$identity,'title'=>$title,'terminal'=>$terminal,'expiration'=>(string)($current['expiration']??$current['expiresAt']??$current['expiry']??'')];
    $prev=stateGet($pdo,'current_supreme_order');
    if($prev===null){
        stateSet($pdo,'current_supreme_order',$snapshot);
        echo "Initial order state stored; no notification sent.\n";
        exit(0);
    }
    $old=is_array($prev['json']??null)?$prev['json']:[];
    if(($old['identity']??null)!==$identity){
        stateSet($pdo,'current_supreme_order',$snapshot);
        $pdo->beginTransaction();
        pushIfConfigured($pdo,$appConfig,'New Supreme Order',$title.($brief!==''?': '.$brief:''),'orders','./#orders');
        $pdo->commit();
        echo "New Supreme Order detected.\n";
        exit(0);
    }
    $wasTerminal=$old['terminal']??null;
    if($wasTerminal===null&&$terminal!==null){
        stateSet($pdo,'current_supreme_order',$snapshot);
        $pdo->beginTransaction();
        $endTitle=$terminal==='completed'?'Supreme Order completed':'Supreme Order ended';
        pushIfConfigured($pdo,$appConfig,$endTitle,$title,'orders','./#details');
        $pdo->commit();
        echo "Supreme Order terminal state detected.\n";
        exit(0);
    }
    // Update snapshots without notifying for routine progress changes.
    stateSet($pdo,'current_supreme_order',$snapshot);
    echo "No qualifying order event; no notification sent.\n";
} catch(Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();
    fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);
}
