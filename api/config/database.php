<?php
class DatabaseConfig {
    public static function getConfig() {
        if (getenv('DATABASE_URL')) {
            $url = parse_url(getenv('DATABASE_URL'));
            return [
                'host' => $url['host'],
                'dbname' => ltrim($url['path'], '/'),
                'username' => $url['user'],
                'password' => $url['pass'],
                'port' => $url['port'] ?? '5432'
            ];
        }
        
        return [
            'host' => '127.0.0.1',
            'dbname' => 'sifer',
            'username' => 'root',
            'password' => '',
            'port' => '3306'
        ];
    }
}
?>