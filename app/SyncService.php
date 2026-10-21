<?php
declare(strict_types=1);

final class SyncService {
    public function __construct(private PDO $pdo, private UpstreamClient $client, private array $sources) {}

    public function sync(string $sourceKey, ?int $id32 = null): array {
        $registry = $this->sources['community_api'];
        if (!isset($registry['routes'][$sourceKey])) throw new InvalidArgumentException("Unknown source: $sourceKey");
        $route = $registry['routes'][$sourceKey];
        if (!empty($route['requires_id32'])) {
            if ($id32 === null || $id32 < 1) throw new InvalidArgumentException('This source requires --id32=<station id32>.');
            $path = str_replace('{id32}', rawurlencode((string)$id32), $route['path_template']);
        } else {
            $path = $route['path'];
        }
        $url = rtrim($registry['base_url'], '/') . $path;
        $run = $this->pdo->prepare("INSERT INTO source_sync_runs(source_key,source_url,started_at,status) VALUES(?,?,UTC_TIMESTAMP(),'running')");
        $run->execute([$sourceKey,$url]); $runId=(int)$this->pdo->lastInsertId();
        try {
            $lock = (int)$this->pdo->query("SELECT GET_LOCK('galactic_command_upstream', 5)")->fetchColumn();
            if ($lock !== 1) throw new RuntimeException('Could not acquire upstream request lock.');
            try { $r=$this->client->getJson($url,$registry['headers']); }
            finally { $this->pdo->query("SELECT RELEASE_LOCK('galactic_command_upstream')"); }

            $data=$r['data'];
            $kind=$route['kind'] ?? 'any';
            if ($kind==='list' && !is_array($data)) throw new UnexpectedValueException('Expected list response.');
            if ($kind==='object' && (!is_array($data) || array_is_list($data))) throw new UnexpectedValueException('Expected object response.');
            if ($sourceKey==='space_station_by_index' && is_array($data) && isset($data['id32']) && (int)$data['id32'] !== $id32) {
                throw new UnexpectedValueException('Station response id32 did not match requested id32.');
            }

            $json=DataNormalizer::json($data); $hash=hash('sha256',$json);
            $this->pdo->beginTransaction();
            $q=$this->pdo->prepare('INSERT INTO source_payloads(source_key,entity_key,fetched_at,http_status,duration_ms,payload_hash,payload_json) VALUES(?,?,UTC_TIMESTAMP(),?,?,?,?)');
            $entityKey=$sourceKey==='space_station_by_index' ? (string)$id32 : null;
            $q->execute([$sourceKey,$entityKey,$r['status'],$r['duration_ms'],$hash,$json]);

            $this->normalize($sourceKey,$data,$id32);
            $done=$this->pdo->prepare("UPDATE source_sync_runs SET finished_at=UTC_TIMESTAMP(),status='success',http_status=?,duration_ms=?,payload_hash=? WHERE id=?");
            $done->execute([$r['status'],$r['duration_ms'],$hash,$runId]);
            $this->pdo->commit();
            return ['run_id'=>$runId,'source_key'=>$sourceKey,'status'=>'success','http_status'=>$r['status'],'duration_ms'=>$r['duration_ms'],'url'=>$url];
        } catch(Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->pdo->prepare("UPDATE source_sync_runs SET finished_at=UTC_TIMESTAMP(),status='error',error_message=? WHERE id=?")->execute([mb_substr($e->getMessage(),0,1000),$runId]);
            $this->pdo->prepare('INSERT INTO source_errors(source_key,occurred_at,message,sync_run_id) VALUES(?,UTC_TIMESTAMP(),?,?)')->execute([$sourceKey,mb_substr($e->getMessage(),0,1000),$runId]);
            throw $e;
        }
    }

    private function normalize(string $source, mixed $data, ?int $id32): void {
        $json=DataNormalizer::json($data);
        if ($source==='war' && is_array($data)) {
            $stats=$data['statistics'] ?? [];
            $this->pdo->prepare('INSERT INTO war_snapshots(captured_at,war_id,summary_json,raw_json) VALUES(UTC_TIMESTAMP(),?,?,?)')
                ->execute([isset($data['warId'])?(string)$data['warId']:null,DataNormalizer::json($stats),$json]);
        }
        if (($source==='planets' || $source==='planet_events') && is_array($data)) {
            $sql='INSERT INTO planets(upstream_index,name,sector_name,biome_json,hazards_json,position_json,max_health,health,current_owner,initial_owner,regen_per_second,disabled,event_json,statistics_json,attacking_json,regions_json,raw_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE name=VALUES(name),sector_name=VALUES(sector_name),biome_json=VALUES(biome_json),hazards_json=VALUES(hazards_json),position_json=VALUES(position_json),max_health=VALUES(max_health),health=VALUES(health),current_owner=VALUES(current_owner),initial_owner=VALUES(initial_owner),regen_per_second=VALUES(regen_per_second),disabled=VALUES(disabled),event_json=VALUES(event_json),statistics_json=VALUES(statistics_json),attacking_json=VALUES(attacking_json),regions_json=VALUES(regions_json),raw_json=VALUES(raw_json),last_seen_at=UTC_TIMESTAMP()';
            $q=$this->pdo->prepare($sql);
            foreach ($data as $p) {
                if (!is_array($p) || !isset($p['index'])) continue;
                $q->execute([
                    (int)$p['index'], (string)($p['name']??'Unknown'), $p['sector']??null,
                    isset($p['biome'])?DataNormalizer::json($p['biome']):null,
                    isset($p['hazards'])?DataNormalizer::json($p['hazards']):null,
                    isset($p['position'])?DataNormalizer::json($p['position']):null,
                    isset($p['maxHealth'])?(int)$p['maxHealth']:null, isset($p['health'])?(int)$p['health']:null,
                    $p['currentOwner']??null,$p['initialOwner']??null,
                    isset($p['regenPerSecond'])?(float)$p['regenPerSecond']:null,
                    !empty($p['disabled'])?1:0,
                    isset($p['event'])?DataNormalizer::json($p['event']):null,
                    isset($p['statistics'])?DataNormalizer::json($p['statistics']):null,
                    isset($p['attacking'])?DataNormalizer::json($p['attacking']):null,
                    isset($p['regions'])?DataNormalizer::json($p['regions']):null,$jsonForPlanet=DataNormalizer::json($p)
                ]);
                $planetId=(int)$this->pdo->lastInsertId();
                if ($planetId===0) {
                    $s=$this->pdo->prepare('SELECT id FROM planets WHERE upstream_index=?'); $s->execute([(int)$p['index']]); $planetId=(int)$s->fetchColumn();
                }
                $snap=$this->pdo->prepare('INSERT INTO planet_snapshots(planet_id,captured_at,health,max_health,player_count,raw_json) VALUES(?,UTC_TIMESTAMP(),?,?,?,?)');
                $stats=$p['statistics']??[];
                $snap->execute([$planetId,$p['health']??null,$p['maxHealth']??null,$stats['playerCount']??null,$jsonForPlanet]);
            }
        }
        if ($source==='assignments' && is_array($data)) {
            $q=$this->pdo->prepare('INSERT INTO assignments(upstream_id,title,briefing,description_text,progress_json,tasks_json,reward_json,rewards_json,expiration_at,flags,raw_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE title=VALUES(title),briefing=VALUES(briefing),description_text=VALUES(description_text),progress_json=VALUES(progress_json),tasks_json=VALUES(tasks_json),reward_json=VALUES(reward_json),rewards_json=VALUES(rewards_json),expiration_at=VALUES(expiration_at),flags=VALUES(flags),raw_json=VALUES(raw_json),last_seen_at=UTC_TIMESTAMP()');
            foreach($data as $a) { if(!is_array($a)||!isset($a['id']))continue; $q->execute([(string)$a['id'],$a['title']??null,$a['briefing']??null,$a['description']??null,isset($a['progress'])?DataNormalizer::json($a['progress']):null,isset($a['tasks'])?DataNormalizer::json($a['tasks']):null,isset($a['reward'])?DataNormalizer::json($a['reward']):null,isset($a['rewards'])?DataNormalizer::json($a['rewards']):null,DataNormalizer::utcDate($a['expiration']??null),$a['flags']??null,DataNormalizer::json($a)]); }
        }
        if ($source==='campaigns' && is_array($data)) {
            $q=$this->pdo->prepare('INSERT INTO campaigns(upstream_id,planet_index,campaign_type,campaign_count,faction,raw_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE planet_index=VALUES(planet_index),campaign_type=VALUES(campaign_type),campaign_count=VALUES(campaign_count),faction=VALUES(faction),raw_json=VALUES(raw_json),last_seen_at=UTC_TIMESTAMP()');
            foreach($data as $c) { if(!is_array($c)||!isset($c['id']))continue; $planet=$c['planet']??[]; $q->execute([(string)$c['id'],isset($planet['index'])?(int)$planet['index']:null,isset($c['type'])?(string)$c['type']:null,isset($c['count'])?(int)$c['count']:null,$c['faction']??null,DataNormalizer::json($c)]); }
        }
        if ($source==='dispatches' && is_array($data)) {
            $q=$this->pdo->prepare('INSERT INTO dispatches(upstream_id,published_at,dispatch_type,message_text,raw_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE published_at=VALUES(published_at),dispatch_type=VALUES(dispatch_type),message_text=VALUES(message_text),raw_json=VALUES(raw_json),last_seen_at=UTC_TIMESTAMP()');
            foreach($data as $d) { if(!is_array($d)||!isset($d['id']))continue; $q->execute([(string)$d['id'],DataNormalizer::utcDate($d['published']??null),isset($d['type'])?(string)$d['type']:null,$d['message']??null,DataNormalizer::json($d)]); }
        }
        if (($source==='space_stations' || $source==='space_station_by_index') && is_array($data)) {
            $items=isset($data['id32'])?[$data]:$data;
            $q=$this->pdo->prepare('INSERT INTO space_stations(upstream_id32,planet_index,election_end_at,flags,tactical_actions_json,raw_json,fetched_at) VALUES(?,?,?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE planet_index=VALUES(planet_index),election_end_at=VALUES(election_end_at),flags=VALUES(flags),tactical_actions_json=VALUES(tactical_actions_json),raw_json=VALUES(raw_json),fetched_at=UTC_TIMESTAMP()');
            foreach($items as $s) { if(!is_array($s)||!isset($s['id32']))continue; $planet=$s['planet']??[]; $q->execute([(string)$s['id32'],isset($planet['index'])?(int)$planet['index']:null,DataNormalizer::utcDate($s['electionEnd']??null),$s['flags']??null,isset($s['tacticalActions'])?DataNormalizer::json($s['tacticalActions']):null,DataNormalizer::json($s)]); }
        }
        if ($source==='steam_news' && is_array($data)) {
            $q=$this->pdo->prepare('INSERT INTO steam_news(upstream_id,title,url,author,content_text,published_at,raw_json,first_seen_at,last_seen_at) VALUES(?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE title=VALUES(title),url=VALUES(url),author=VALUES(author),content_text=VALUES(content_text),published_at=VALUES(published_at),raw_json=VALUES(raw_json),last_seen_at=UTC_TIMESTAMP()');
            foreach($data as $n) { if(!is_array($n)||!isset($n['id']))continue; $q->execute([(string)$n['id'],$n['title']??null,$n['url']??null,$n['author']??null,$n['content']??null,DataNormalizer::utcDate($n['publishedAt']??null),DataNormalizer::json($n)]); }
        }
    }
}
