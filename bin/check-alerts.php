<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($vendor)) { fwrite(STDERR, "Install dependencies with composer install first.\n"); exit(2); }
require $vendor;
require_once dirname(__DIR__) . '/app/PushNotifier.php';

function getPayload(PDO $pdo, string $key): array {
    $q = $pdo->prepare('SELECT payload_json FROM source_payloads WHERE source_key=? AND entity_key IS NULL ORDER BY id DESC LIMIT 1');
    $q->execute([$key]); $v=$q->fetchColumn();
    if (!is_string($v)) return [];
    $data=json_decode($v,true);
    return is_array($data)?$data:[];
}
function stateGet(PDO $pdo,string $key): ?array {
    $q=$pdo->prepare('SELECT state_hash,state_json FROM notification_state WHERE state_key=?');$q->execute([$key]);$r=$q->fetch();
    if(!$r)return null;
    return ['hash'=>$r['state_hash'],'json'=>json_decode((string)$r['state_json'],true)];
}
function stateSet(PDO $pdo,string $key,array $value): void {
    $json=json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $hash=hash('sha256',(string)$json);
    $q=$pdo->prepare('INSERT INTO notification_state(state_key,state_hash,state_json,updated_at) VALUES(?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE state_hash=VALUES(state_hash),state_json=VALUES(state_json),updated_at=UTC_TIMESTAMP()');
    $q->execute([$key,$hash,$json]);
}
function pushIfConfigured(PDO $pdo,array $config,string $title,string $body,string $topic): void {
    if(empty($config['push']['public_key'])||empty($config['push']['private_key'])) return;
    $count=(int)$pdo->query('SELECT COUNT(*) FROM push_subscriptions')->fetchColumn();
    if($count<1)return;
    $r=(new PushNotifier($pdo,$config))->send($title,$body,'/',$topic);
    echo $title . ': ' . json_encode($r) . PHP_EOL;
}
try {
    $pdo->beginTransaction();
    // Track only order identity/title/expiry so progress polling does not spam notifications.
    $orders=getPayload($pdo,'assignments');
    $orderSummary=array_values(array_map(static fn($o)=>[
        'id'=>(string)($o['id']??''),'title'=>(string)($o['title']??''),'expiration'=>(string)($o['expiration']??'')
    ],array_filter($orders,'is_array')));
    $orderSummary=array_slice($orderSummary,0,10);
    $prev=stateGet($pdo,'orders');
    if($prev===null){stateSet($pdo,'orders',$orderSummary);}
    elseif($prev['hash']!==hash('sha256',(string)json_encode($orderSummary,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))){
        stateSet($pdo,'orders',$orderSummary);
        $first=$orderSummary[0]['title']??'A major order changed.';
        $pdo->commit();
        pushIfConfigured($pdo,$appConfig,'Major order updated',$first,'orders');
        $pdo->beginTransaction();
    }
    // Dispatch notifications use newest upstream ID to deduplicate across cron runs.
    $dispatches=getPayload($pdo,'dispatches');
    usort($dispatches, static function($a,$b): int {
        $date = static function($v): int {
            $raw=$v['published']??$v['publishedAt']??$v['id']??0;
            if(is_numeric($raw))return (int)$raw;
            $time=strtotime((string)$raw);
            return $time===false?0:$time;
        };
        return $date($b)<=>$date($a);
    });
    $latest=$dispatches[0]??null;
    if(is_array($latest)&&isset($latest['id'])){
        $key=(string)$latest['id'];$prev=stateGet($pdo,'latest_dispatch');
        if($prev===null) stateSet($pdo,'latest_dispatch',['id'=>$key]);
        elseif(($prev['json']['id']??null)!==$key){
            stateSet($pdo,'latest_dispatch',['id'=>$key]);
            $title=(string)($latest['title']??'New galactic dispatch');
            $body=trim(strip_tags((string)($latest['message']??'')));
            $pdo->commit();pushIfConfigured($pdo,$appConfig,$title,$body,'dispatches');$pdo->beginTransaction();
        }
    }
    // Current normalized planet records are compared with the previous attack set.
    $rows=$pdo->query('SELECT upstream_index,name,raw_json FROM planets')->fetchAll();
    $now=[];
    foreach($rows as $row){
        $p=json_decode((string)$row['raw_json'],true);if(!is_array($p))continue;
        $a=$p['attacking']??false;
        $is=is_array($a)?count($a)>0:(is_bool($a)?$a:((int)$a===1));
        if($is)$now[(string)$row['upstream_index']]=(string)$row['name'];
    }
    $prev=stateGet($pdo,'attacked_planets');
    if($prev===null)stateSet($pdo,'attacked_planets',$now);
    else {
        $old=is_array($prev['json'])?$prev['json']:[];
        $new=array_diff_key($now,$old);
        stateSet($pdo,'attacked_planets',$now);
        if($new){
            $name=(string)reset($new);$count=count($new);
            $pdo->commit();pushIfConfigured($pdo,$appConfig,'Planet under attack',$count>1?"{$name} and ".($count-1)." more planet(s) are now attacked.":"{$name} is now under attack.",'attacks');$pdo->beginTransaction();
        }
    }
    $pdo->commit();
    echo "Notification state checked.\n";
} catch(Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();
    fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);
}
