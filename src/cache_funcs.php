<?php

/**
 * Check or populate a simple filesystem cache.
 *
 * If `$data` is null, attempts to read a cached JSON file for
 * the given `$command` and `$arg` and returns the decoded data
 * or `false` if not present. If `$data` is provided, writes it
 * to the cache and returns `true`.
 */
function check_cache($command, $arg, $data = null) {
    global $cache_dir;
    $dir = $cache_dir . $command . '/';
    $filename = $dir . hash('sha1', $arg) . '.json';

    if(!is_dir($dir)):
        mkdir($dir, 0755, true);
    endif;
    
    if(file_exists($filename)):
        $contents = file_get_contents($filename);
        $data = json_decode($contents, true);
        return is_array($data) ? $data : false;
    elseif($data !== null):
        file_put_contents($filename, json_encode($data));
        return true;
    else:
        return false;
    endif;
}