<?php
declare(strict_types=1);
/**
 * Service Api Clients configuration
 */
return [
    'legacyService' => [
        'apiKeys'           => env('LEGACY_SERVICE_API_KEYS', []),
        'apiKeyPrefixes'    => env('LEGACY_SERVICE_API_KEYS_PREFIXES', []),
        'accessToken'       => env('LEGACY_SERVICE_ACCESS_TOKEN', ''),
        'username'          => env('LEGACY_SERVICE_USERNAME', ''),
        'password'          => env('LEGACY_SERVICE_PASSWORD', ''),
        'host'              => env('LEGACY_SERVICE_HOST', 'http://localhost'),
        'userAgent'         => env('LEGACY_SERVICE_USER_AGENT', 'OpenAPI-Generator/1.0.0/PHP'),
        'debug'             => env('LEGACY_SERVICE_DEBUG', false),
        'debugFile'         => env('LEGACY_SERVICE_DEBUG_FILE', 'php://output'),
        'tempFolderPath'    => env('LEGACY_SERVICE_TEMP_FOLDER_PATH', ''),
        'connectionTimeout' => (int) env('LEGACY_SERVICE_CONNECTION_TIMEOUT', 60),
        'timeout'           => (int) env('LEGACY_SERVICE_TIMEOUT', 60)
    ],
    'configService' => [
        'host'    => env('CONFIG_SERVICE_HOST', 'host.docker.internal:5000'),
        'aadAuth' => [
            'clientId'     => env('CONFIG_SERVICE_AAD_CLIENT_ID', ''),
            'tenant'       => env('CONFIG_SERVICE_AAD_TENANT', ''),
            'resource'     => env('CONFIG_SERVICE_AAD_CLIENT_RESOURCE', ''),
            'clientSecret' => env('CONFIG_SERVICE_AAD_CLIENT_SECRET', ''),
        ]
    ]
];
