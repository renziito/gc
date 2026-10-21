CREATE TABLE IF NOT EXISTS source_sync_runs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, source_key VARCHAR(100) NOT NULL, source_url VARCHAR(1000) NOT NULL,
 started_at DATETIME NOT NULL, finished_at DATETIME NULL, status ENUM('running','success','error','skipped') NOT NULL DEFAULT 'running',
 http_status SMALLINT UNSIGNED NULL, duration_ms INT UNSIGNED NULL, payload_hash CHAR(64) NULL, error_message VARCHAR(1000) NULL,
 KEY idx_runs_source_started(source_key,started_at), KEY idx_runs_status(status,started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS source_payloads (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, source_key VARCHAR(100) NOT NULL, entity_key VARCHAR(191) NULL,
 fetched_at DATETIME NOT NULL, http_status SMALLINT UNSIGNED NOT NULL, duration_ms INT UNSIGNED NOT NULL,
 payload_hash CHAR(64) NOT NULL, payload_json LONGTEXT NOT NULL,
 KEY idx_payload_source_entity(source_key,entity_key,fetched_at), KEY idx_payload_hash(payload_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS source_errors (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, source_key VARCHAR(100) NOT NULL, occurred_at DATETIME NOT NULL,
 message VARCHAR(1000) NOT NULL, sync_run_id BIGINT UNSIGNED NULL, KEY idx_errors_source_time(source_key,occurred_at),
 CONSTRAINT fk_source_errors_run FOREIGN KEY(sync_run_id) REFERENCES source_sync_runs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS planets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_index INT NOT NULL, name VARCHAR(160) NOT NULL, sector_name VARCHAR(160) NULL,
 biome_json JSON NULL, hazards_json JSON NULL, position_json JSON NULL, max_health BIGINT NULL, health BIGINT NULL,
 current_owner VARCHAR(100) NULL, initial_owner VARCHAR(100) NULL, regen_per_second DECIMAL(18,8) NULL, disabled TINYINT(1) NOT NULL DEFAULT 0,
 event_json JSON NULL, statistics_json JSON NULL, attacking_json JSON NULL, regions_json JSON NULL, raw_json JSON NOT NULL,
 first_seen_at DATETIME NOT NULL, last_seen_at DATETIME NOT NULL, UNIQUE KEY uq_planet_index(upstream_index), KEY idx_planets_name(name), KEY idx_planets_owner(current_owner)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS planet_snapshots (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, planet_id BIGINT UNSIGNED NOT NULL, captured_at DATETIME NOT NULL,
 health BIGINT NULL, max_health BIGINT NULL, player_count INT NULL, raw_json JSON NOT NULL,
 KEY idx_planet_snapshot_time(planet_id,captured_at),
 CONSTRAINT fk_snap_planet FOREIGN KEY(planet_id) REFERENCES planets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS war_snapshots (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, captured_at DATETIME NOT NULL, war_id VARCHAR(100) NULL,
 summary_json JSON NOT NULL, raw_json JSON NOT NULL, KEY idx_war_time(captured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS assignments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_id VARCHAR(191) NOT NULL, title VARCHAR(255) NULL, briefing MEDIUMTEXT NULL,
 description_text MEDIUMTEXT NULL, progress_json JSON NULL, tasks_json JSON NULL, reward_json JSON NULL, rewards_json JSON NULL,
 expiration_at DATETIME NULL, flags BIGINT NULL, raw_json JSON NOT NULL, first_seen_at DATETIME NOT NULL, last_seen_at DATETIME NOT NULL,
 UNIQUE KEY uq_assignment_upstream(upstream_id), KEY idx_assignment_expiration(expiration_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS campaigns (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_id VARCHAR(191) NOT NULL, planet_index INT NULL, campaign_type VARCHAR(100) NULL,
 campaign_count BIGINT NULL, faction VARCHAR(100) NULL, raw_json JSON NOT NULL, first_seen_at DATETIME NOT NULL, last_seen_at DATETIME NOT NULL,
 UNIQUE KEY uq_campaign_upstream(upstream_id), KEY idx_campaign_planet(planet_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS dispatches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_id VARCHAR(191) NOT NULL, published_at DATETIME NULL,
 dispatch_type VARCHAR(100) NULL, message_text MEDIUMTEXT NULL, raw_json JSON NOT NULL, first_seen_at DATETIME NOT NULL, last_seen_at DATETIME NOT NULL,
 UNIQUE KEY uq_dispatch_upstream(upstream_id), KEY idx_dispatch_published(published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS space_stations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_id32 VARCHAR(191) NOT NULL, planet_index INT NULL, election_end_at DATETIME NULL,
 flags BIGINT NULL, tactical_actions_json JSON NULL, raw_json JSON NOT NULL, fetched_at DATETIME NOT NULL,
 UNIQUE KEY uq_station_id32(upstream_id32)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS steam_news (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, upstream_id VARCHAR(191) NOT NULL, title VARCHAR(500) NULL, url VARCHAR(1500) NULL,
 author VARCHAR(255) NULL, content_text MEDIUMTEXT NULL, published_at DATETIME NULL, raw_json JSON NOT NULL,
 first_seen_at DATETIME NOT NULL, last_seen_at DATETIME NOT NULL, UNIQUE KEY uq_steam_news_upstream(upstream_id), KEY idx_steam_news_published(published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS push_subscriptions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 endpoint_hash CHAR(64) NOT NULL,
 endpoint_url VARCHAR(2048) NOT NULL,
 subscription_json JSON NOT NULL,
 preferences_json JSON NULL,
 created_at DATETIME NOT NULL,
 last_seen_at DATETIME NOT NULL,
 KEY idx_push_seen(last_seen_at),
 UNIQUE KEY uq_push_endpoint_hash(endpoint_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_state (
 state_key VARCHAR(191) PRIMARY KEY,
 state_hash CHAR(64) NOT NULL,
 state_json JSON NULL,
 updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
