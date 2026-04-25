# CDN - Intelligent Path Selector for File Downloads

An intelligent geographic routing system that redirects file download requests to different servers based on the client's location (country).

## Features

- 🌍 **Geographic-based routing** - Redirects users to the optimal server based on their country
- ⚡ **High performance** - Includes caching mechanism (24-hour TTL)
- 🔒 **IP detection** - Detects client IP from various sources (direct connection, proxy, X-Forwarded-For)
- 📝 **Logging** - Logs all redirects for monitoring and debugging
- 🛠️ **Easy configuration** - Simple server configuration mapping
- 🔌 **Composer support** - Uses MaxMind GeoIP2 database

## Requirements

- PHP 7.4 or higher
- Apache with mod_rewrite enabled
- MaxMind GeoLite2-Country database

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/marabzadeh86/cdn.git
   cd cdn
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Download MaxMind GeoLite2 database**
   - Download from: https://www.maxmind.com/en/geoip2-databases
   - Place the `GeoLite2-Country.mmdb` file in the project root directory

4. **Configure your servers**
   Edit `geo-redirect.php` and update the `$serverConfig` array:
   ```php
   $this->serverConfig = [
       'US' => 'https://us-server.example.com',
       'CA' => 'https://us-server.example.com',
       'GB' => 'https://eu-server.example.com',
       'DE' => 'https://eu-server.example.com',
       'FR' => 'https://eu-server.example.com',
   ];
   ```

5. **Set default server**
   Update the default server fallback:
   ```php
   $this->defaultServer = 'https://eu-server.example.com';
   ```

## Usage

Once installed, all requests to your CDN will be automatically routed based on the client's country:

```
Request: https://cdn.example.com/videos/tutorial.mp4
User Location: United States
Redirect to: https://us-server.example.com/videos/tutorial.mp4
```

### Query String Preservation

Query parameters are automatically preserved during redirection:

```
Request: https://cdn.example.com/file.zip?token=abc123
Redirect to: https://server.example.com/file.zip?token=abc123
```

## How It Works

1. Client requests a file through the CDN
2. Apache rewrites the request to `geo-redirect.php`
3. System detects client IP address
4. MaxMind database looks up the country code
5. User is redirected to the appropriate server based on country
6. Results are cached for 24 hours to improve performance

## IP Detection Methods

The system detects client IP in the following order:
1. `HTTP_CLIENT_IP` - Direct connection or shared proxy
2. `HTTP_X_FORWARDED_FOR` - X-Forwarded-For header (from proxies)
3. `REMOTE_ADDR` - Server's remote address

## Configuration

### Server Mapping

Edit the `$serverConfig` array in `geo-redirect.php`:

```php
$this->serverConfig = [
    'US' => 'https://us-server.example.com',
    'CA' => 'https://us-server.example.com',
    'GB' => 'https://eu-server.example.com',
    'DE' => 'https://eu-server.example.com',
    'FR' => 'https://eu-server.example.com',
];
```

### Cache Directory

By default, caching uses the system temp directory. To customize:

```php
$cacheDir = '/var/cache/cdn';
$redirector = new GeoRedirector(__DIR__ . '/GeoLite2-Country.mmdb', $cacheDir);
```

## Logging

All redirects are logged with:
- Client IP address
- Detected country
- Requested filename
- Selected server

Check your PHP error log to view redirects:
```bash
tail -f /var/log/apache2/error.log | grep "Redirect:"
```

## Country Codes

The system uses ISO 3166-1 alpha-2 country codes:
- `US` - United States
- `CA` - Canada
- `GB` - United Kingdom
- `DE` - Germany
- `FR` - France
- [See full list](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

## License

MIT License - See LICENSE file for details

## Author

[marabzadeh86](https://github.com/marabzadeh86)

## Support

For issues, questions, or suggestions, please create an issue on GitHub.
