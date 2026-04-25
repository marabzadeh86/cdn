<?php

/**
 * Configuration example for CDN geo-redirect system
 * Copy this file to config.php and update with your settings
 */

return [
    // Path to MaxMind GeoLite2-Country database
    'geoip_db_path' => __DIR__ . '/GeoLite2-Country.mmdb',
    
    // Cache directory for GeoIP lookups
    'cache_dir' => sys_get_temp_dir(),
    
    // Server configuration by country code
    'servers' => [
        // North America
        'US' => 'https://us-server.example.com',
        'CA' => 'https://us-server.example.com',
        'MX' => 'https://us-server.example.com',
        
        // Europe
        'GB' => 'https://eu-server.example.com',
        'DE' => 'https://eu-server.example.com',
        'FR' => 'https://eu-server.example.com',
        'NL' => 'https://eu-server.example.com',
        'SE' => 'https://eu-server.example.com',
        
        // Asia
        'JP' => 'https://asia-server.example.com',
        'SG' => 'https://asia-server.example.com',
        'CN' => 'https://asia-server.example.com',
        'IN' => 'https://asia-server.example.com',
        
        // Australia
        'AU' => 'https://au-server.example.com',
        'NZ' => 'https://au-server.example.com',
    ],
    
    // Default server for unknown countries
    'default_server' => 'https://eu-server.example.com',
    
    // Enable logging
    'enable_logging' => true,
    
    // Log file path
    'log_file' => __DIR__ . '/logs/redirects.log',
    
    // Cache TTL in seconds (24 hours)
    'cache_ttl' => 86400,
];
