<?php
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|html)$/', $path)) {
    $file_path = __DIR__ . '/public' . $path;
    if (file_exists($file_path)) {
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'html' => 'text/html'
        ];
        
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
        }
        
        readfile($file_path);
        exit;
    }
}

if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

$frontend_file = __DIR__ . '/public/index.html';
if (file_exists($frontend_file)) {
    header('Content-Type: text/html');
    readfile($frontend_file);
    exit;
}

http_response_code(404);
echo "Página no encontrada";
?>