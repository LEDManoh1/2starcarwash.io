<?php
// api/config.php
// Centralized API configuration (keep secrets out of webroot in production)

// Admin key and DB settings. Prefer environment variables in production.
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'carwash';
$DB_USER = getenv('DB_USER') ?: 'staruser';
$DB_PASS = getenv('DB_PASS') ?: 'StrongPassword123';

$ADMIN_KEY = getenv('ADMIN_KEY') ?: 'k9Xf7sR3qLz2P1vTtB8m';

// Admin UI credentials for session-based login (set in secure config or env)
$ADMIN_USER = getenv('ADMIN_USER') ?: 'admin';
$ADMIN_PASS = getenv('ADMIN_PASS') ?: 'changeme';

// If you moved a secure config outside webroot (recommended), that file
// can set any of the variables above. Example secure path: /var/www/secure/config.php
