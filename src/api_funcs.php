<?php

/**
 * Perform an HTTP GET request and decode JSON.
 *
 * Uses cURL to fetch the given URL with a timeout, verifies
 * the HTTP response code and returns the decoded JSON as an
 * associative array or null on error.
 */
function api_request($url, $timeout = 30) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'off_search/1.0 (https://github.com/mathias-schnell/off_search)');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    $response = curl_exec($ch);

    if($response === false):
        curl_close($ch);
        return null;
    endif;

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($http_code !== 200):
        return null;
    endif;

    $decoded = json_decode($response, true);

    if(!is_array($decoded)):
        return null;
    endif;

    return $decoded;
}