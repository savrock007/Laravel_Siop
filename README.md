# Laravel Security Dashboard Package

This package provides a security dashboard for monitoring and managing web application security events, including XSS attacks, SQL injection attempts, CSRF violations, rate-limit hits, and more. The dashboard provides administrators with real-time insights into security incidents and includes functionality for blocking malicious IPs and visualizing event data through graphs.

## Features

- **XSS Detection Middleware**: Detects and mitigates Cross-Site Scripting (XSS) attacks by scanning user inputs.
- **SQL Injection Detection**: Detects attempts at SQL injection and prevents malicious queries.
- **CSRF Violation Detection**: Detects Cross-Site Request Forgery (CSRF) attacks.
- **Rate Limit Monitoring**: Tracks and logs requests that exceed rate limits.
- **Centralized Dashboard**: A centralized web interface for monitoring, logging, and visualizing security events.
- **Graphical Visualizations**: Provides graphs for security event counts by IP, event frequency over time, and event distribution by category.
- **IP Blocking**: Allows administrators to block and unblock IP addresses from the dashboard to mitigate repeated attacks.
  
## Installation

### 1. Install the Package

Add repository to your composer.json file
```json
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/savrock007/Laravel_Siop"
        }
    ],
```
Require package
```json
 "require": {
        "savrock007/laravel_siop": "dev-main"
}
```

### 2. Publish Configuration Files
```bash
php artisan vendor:publish --provider="Savrock\Siop\SiopServiceProvider"
```
Register Provider in ```config/app.php```
```php
'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\SiopServiceProvider::class
    ])->toArray(),
```

### 3. Run migration
```bash
php artisan migrate
```
