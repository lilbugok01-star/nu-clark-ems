<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Block any hidden dotfiles (e.g., /.htaccess, /.env, /.git) immediately with 404
if (preg_match('#(?:^|/)\.#', $uri) || stripos($uri, '.htaccess') !== false || stripos($uri, '.env') !== false) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    if (!headers_sent()) {
        header_remove('X-Powered-By');
        header_remove('Server');
    }
    echo "404 Not Found\n";
    return;
}

// Emulate web server for static files while injecting required security headers
if ($uri !== '/' && file_exists($publicPath . $uri) && !is_dir($publicPath . $uri)) {
    $ext = strtolower(pathinfo($publicPath . $uri, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'json'  => 'application/json; charset=utf-8',
        'txt'   => 'text/plain; charset=utf-8',
        'xml'   => 'application/xml; charset=utf-8',
        'pdf'   => 'application/pdf',
    ];

    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('X-Content-Type-Options: nosniff');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Cache-Control: public, max-age=31536000, immutable');
        header_remove('X-Powered-By');
        header_remove('Server');
        readfile($publicPath . $uri);
        return;
    }

    return false;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$remoteAddress = ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ':' . ($_SERVER['REMOTE_PORT'] ?? '0');

@file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath . '/index.php';
