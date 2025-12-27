<?php

/**
 * Check or populate a simple filesystem cache
 *
 * If `$data` is null, attempts to read a cached JSON file for
 * the given `$command` and `$arg`. The cache file is expected to be a JSON
 * object with keys `expires_at` (UNIX timestamp) and `data` (the stored value).
 * If the cache exists and hasn't expired, the function returns the stored
 * `data` value. If the cache is missing or expired, it returns `false`.
 *
 * If `$data` is provided, the function writes a JSON file containing
 * `expires_at` (current time + 3600 seconds) and `data` (the provided value),
 * and returns `true` on success.
 */
function check_cache($command, $arg, $data = null) {
    global $cache_dir;
    $dir = $cache_dir . $command . '/';
    $filename = $dir . hash('sha1', $arg) . '.json';

    if(!is_dir($dir)):
        mkdir($dir, 0755, true);
    endif;
    
    if(file_exists($filename)):
        $cached = json_decode(file_get_contents($filename), true);

        if (is_array($cached) 
            && isset($cached['expires_at']) 
            && isset($cached['data']) 
            && time() <= (int)$cached['expires_at']):
            return $cached['data'];
        endif;
    endif;

    if ($data !== null):
        $payload = [
            'expires_at' => time() + 3600,
            'data' => $data,
        ];
        file_put_contents($filename, json_encode($payload));
        return true;
    endif;

    return false;
}