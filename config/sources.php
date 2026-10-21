<?php
declare(strict_types=1);

return [
    'community_api' => [
        'base_url' => 'https://api.helldivers2.dev',

        'headers' => [
            'X-Super-Client: GalacticCommand',
            'X-Super-Contact: sepia.aki@gmail.com',
            'Accept: application/json',
        ],

        'routes' => [
            'war' => [
                'path' => '/api/v1/war',
                'interval_seconds' => 180,
                'kind' => 'object',
            ],
            'planets' => [
                'path' => '/api/v1/planets',
                'interval_seconds' => 180,
                'kind' => 'list',
            ],
            'planet_events' => [
                'path' => '/api/v1/planet-events',
                'interval_seconds' => 300,
                'kind' => 'list',
            ],
            'assignments' => [
                'path' => '/api/v1/assignments',
                'interval_seconds' => 300,
                'kind' => 'list',
            ],
            'campaigns' => [
                'path' => '/api/v1/campaigns',
                'interval_seconds' => 300,
                'kind' => 'list',
            ],
            'dispatches' => [
                'path' => '/api/v1/dispatches',
                'interval_seconds' => 900,
                'kind' => 'list',
            ],
            'space_stations' => [
                'path' => '/api/v1/space-stations',
                'interval_seconds' => 300,
                'kind' => 'list',
            ],
            'steam_news' => [
                'path' => '/api/v1/steam',
                'interval_seconds' => 1800,
                'kind' => 'list',
            ],
            'space_station_by_index' => [
                'path_template' => '/api/v1/space-stations/{id32}',
                'interval_seconds' => 300,
                'kind' => 'object',
                'requires_id32' => true,
            ],
            'raw_war_id' => [
                'path' => '/raw/api/WarSeason/current/WarID',
                'interval_seconds' => 3600,
                'kind' => 'any',
            ],
            'raw_war_info' => [
                'path' => '/raw/api/WarSeason/801/WarInfo',
                'interval_seconds' => 3600,
                'kind' => 'object',
            ],
            'raw_war_status' => [
                'path' => '/raw/api/WarSeason/801/Status',
                'interval_seconds' => 180,
                'kind' => 'object',
            ],
            'raw_war_summary' => [
                'path' => '/raw/api/Stats/war/801/summary',
                'interval_seconds' => 900,
                'kind' => 'object',
            ],
            'raw_newsfeed' => [
                'path' => '/raw/api/NewsFeed/801',
                'interval_seconds' => 900,
                'kind' => 'list',
            ],
        ],
    ],
];