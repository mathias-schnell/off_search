<?php
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];

if($argc < 2):
    echo "Usage:\n";
    echo "\toff_search query \"<query string>\"\n";
    echo "\toff_search info <barcode number>\n";
    exit(1);
endif;

$command = strtolower(trim($argv[1]));

switch($command):
    case "query":
        handle_query($argc, $argv);
        break;
    case "info":
        handle_info($argc, $argv);
        break;
    default:
        echo "Error: Invalid command.\n";
        exit(1);
endswitch;

function handle_info($argc, $argv) {
    if($argc < 3):
        echo "Error: Missing barcode number.\n";
        exit(1);
    endif;
    $barcode = strtolower(trim($argv[2]));
    echo "[STUB] Barcode - $barcode\n";
}

function handle_query($argc, $argv) {
    if($argc < 3):
        echo "Error: Missing query string.\n";
        exit(1);
    endif;
    $query = strtolower(trim($argv[2]));

    $base_url = 'https://world.openfoodfacts.org/cgi/search.pl';
    $params = [
        'search_terms'  => $query,
        'search_simple' => 1,
        'action'        => 'process',
        'json'          => 1,
        'page_size'     => 5,
        'fields'        => 'product_name,brands,code'
    ];
    $url = $base_url . '?' . http_build_query($params);
    $data = api_request($url);

    if($data == NULL || !isset($data['products']) || empty($data['products'])):
        echo "We're sorry, no products with that name could be found.\n";
        exit(0);
    endif;
    
    echo 'Results for "' . $query . "\":\n\n";
    $index = 1;
    foreach($data['products'] as $product):
        $name  = $product['product_name'] ?? 'N/A';
        $brand = $product['brands'] ?? 'N/A';
        $code  = $product['code'] ?? 'N/A';
        echo $index . ') ' . $name;
        if($brand !== 'N/A'):
            echo ' — ' . $brand;
        endif;
        echo ' — ' . $code . "\n";
        $index++;
    endforeach;
}

function api_request($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'off_search/1.0 (https://github.com/mathias-schnell/off_search)');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

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