<?php

require_once 'vendor/autoload.php';
use MaxMind\Db\Reader;

class GeoRedirector {
    private $dbPath;
    private $cacheDir;
    private $serverConfig;
    private $defaultServer;
    
    public function __construct($dbPath, $cacheDir = null) {
        $this->dbPath = $dbPath;
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir();
        
        // Server configuration
        $this->serverConfig = [
            'US' => 'https://us-server.example.com',
            'CA' => 'https://us-server.example.com',
            'GB' => 'https://eu-server.example.com',
            'DE' => 'https://eu-server.example.com',
            'FR' => 'https://eu-server.example.com',
        ];
        
        $this->defaultServer = 'https://eu-server.example.com';
    }
    
    public function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return trim($ip);
    }
    
    public function getCountryFromIP($ip) {
        // Check cache first
        $cacheKey = 'geoip_' . md5($ip);
        $cacheFile = $this->cacheDir . '/' . $cacheKey;
        
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 86400) { // 24h cache
            return file_get_contents($cacheFile);
        }
        
        if (!file_exists($this->dbPath)) {
            return null;
        }
        
        try {
            $reader = new Reader($this->dbPath);
            $result = $reader->get($ip);
            $country = $result['country']['iso_code'] ?? null;
            
            // Cache the result
            if ($country) {
                file_put_contents($cacheFile, $country);
            }
            
            return $country;
        } catch (Exception $e) {
            error_log("GeoIP Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function redirect() {
        $clientIP = $this->getClientIP();
        $country = $this->getCountryFromIP($clientIP);
        $filename = $_GET['file'] ?? $_SERVER['REQUEST_URI'];
        
        // Remove query string
        $filename = explode('?', $filename)[0];
        $filename = ltrim($filename, '/');
        
        if (empty($filename)) {
            $filename = 'index.html';
        }
        
        // Get server based on country
        $baseServer = $this->serverConfig[$country] ?? $this->defaultServer;
        $redirectUrl = $baseServer . '/' . ltrim($filename, '/');
        
        // Preserve query string
        if (!empty($_SERVER['QUERY_STRING'])) {
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        
        // Log redirect
        error_log("Redirect: IP={$clientIP}, Country={$country}, File={$filename}, Server={$baseServer}");
        
        // Perform redirect
        header('Location: ' . $redirectUrl, true, 302);
        exit;
    }
}

// Usage
$redirector = new GeoRedirector(__DIR__ . '/GeoLite2-Country.mmdb');
$redirector->redirect();
