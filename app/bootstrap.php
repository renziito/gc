<?php
declare(strict_types=1);
$configFile=dirname(__DIR__).'/config/config.php';
if(!is_file($configFile)) throw new RuntimeException('Missing config/config.php; copy config.example.php and configure it.');
$appConfig=require $configFile;
$sourceConfig=require dirname(__DIR__).'/config/sources.php';
require_once __DIR__.'/Database.php';
require_once __DIR__.'/UpstreamClient.php';
require_once __DIR__.'/DataNormalizer.php';
require_once __DIR__.'/SyncService.php';
$pdo=Database::connection($appConfig);
