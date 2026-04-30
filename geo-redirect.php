<?php

use MaxMind\Db\Reader;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'vendor/autoload.php';
    
    class GeoRedirector {
        private $dbPath;
        private $cacheDir;
        private $defaultServer;
        private $iranServer;
        
        public function __construct($dbPath, $cacheDir = null) {
            $this->dbPath = $dbPath;
            $this->cacheDir = $cacheDir ?? sys_get_temp_dir();
            
            // Verify database file exists
            if (!file_exists($this->dbPath)) {
                throw new Exception("Database file not found: " . $this->dbPath);
            }
            
            // Verify cache directory is writable
            if (!is_writable($this->cacheDir)) {
                throw new Exception("Cache directory not writable: " . $this->cacheDir);
            }
            
            // Server configuration
            $this->defaultServer = 'https://cdn.digiboy.ir';
            $this->iranServer = 'https://fdn.digiboy.ir';
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
                error_log("Database file not found: " . $this->dbPath);
                return null; // Return null instead of throwing error
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
                return null; // Return null instead of throwing error
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
            // If Iran, use Iran server; otherwise use default server
            // If country cannot be determined, use default server
            $baseServer = ($country === 'IR') ? $this->iranServer : $this->defaultServer;
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
    
} catch (Exception $e) {
    error_log("Fatal Error: " . $e->getMessage());
    header('Content-Type: text/plain', true, 500);
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit;
}
