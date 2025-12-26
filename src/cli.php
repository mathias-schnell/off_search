<?php
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];
$cache_dir = __DIR__ . '/../cache/';

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

    $bc = strtolower(trim($argv[2]));
    if(($data = check_cache('info', $bc)) == false):
        $query = "https://world.openfoodfacts.net/api/v2/product/$bc.json";
        $data = api_request($query);
        if($data == NULL):
            echo "Error! Search request timed out or failed. Please try again.\n";
            exit(1);
        elseif(!isset($data['product']) || empty($data['product'])):
            echo "We're sorry, we couldn't find any product with that barcode.\n";
            exit(1);
        endif;
        check_cache('info', $bc, $data);
    endif;

    echo "Product: " . ($data['product']['product_name'] ?? 'N/A') . "\n";
    echo "Brand: " . ($data['product']['brands'] ?? 'N/A') . "\n\n";
    echo "Ingredients: " . (!empty($data['product']['ingredients']) ? "" : 'N/A') . "\n";

    foreach($data['product']['ingredients'] as $ing):
        if(!empty($ing['text'])):
            echo '- ' . $ing['text'] . "\n";
        endif;
    endforeach;

    echo "Nutrition (per 100g): " . (!empty($data['product']['nutriments']) ? "" : 'N/A') . "\n";
    $fields = [
        'energy-kcal_100g' => 'calories',
        'fat_100g'         => 'fat',
        'carbohydrates_100g' => 'carbohydrates',
        'sugars_100g'      => 'sugar',
        'proteins_100g'    => 'protein'
    ];

    foreach($fields as $key => $label):
        if(isset($data['product']['nutriments'][$key])):
            echo '- ' . $label . ': ' . $data['product']['nutriments'][$key] . "\n";
        endif;
    endforeach;
}

function handle_query($argc, $argv) {
    if($argc < 3):
        echo "Error: Missing query string.\n";
        exit(1);
    endif;
    $query = strtolower(trim($argv[2]));

    if(($data = check_cache('query', $query)) == false):
        $base_url = 'https://world.openfoodfacts.org/cgi/search.pl';
        $params = [
            'search_fields' => 'product_name',
            'search_terms'  => $query,
            'search_simple' => 1,
            'action'        => 'process',
            'json'          => 1,
            'page_size'     => 5,
            'fields'        => 'product_name,brands,code',
            'lc'            => 'en',
            'lang'          => 'en',
        ];
        $url = $base_url . '?' . http_build_query($params);
        echo "Searching Open Food Facts...\n";
        $data = api_request($url);

        if($data == NULL):
            echo "Error! Search request timed out or failed. Please try again.\n";
            exit(1);
        elseif(!isset($data['products']) || empty($data['products'])):
            echo "We're sorry, no matching products could be found.\n";
            exit(1);
        endif;
    endif;

    echo 'Results for "' . $query . "\":\n\n";
    $index = 1;
    foreach($data['products'] as $product):
        $name  = $product['product_name'] ?? 'N/A';
        $brand = ' — ' . ($product['brands'] ?? 'N/A');
        $code  = ' — ' . ($product['code'] ?? 'N/A');
        echo $index . ') ' . $name . $brand . $code . "\n";
        $index++;
    endforeach;
}

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

function check_cache($command, $arg, $data = null) {
    $filename = $cache_dir . $command . base64_encode($arg) . '.json';
    if(file_exists($filename)):
        $contents = file_get_contents($filename);
        return json_decode($contents, true);
    elseif($data !== null):
        file_put_contents($filename, json_encode($data));
        return true;
    else:
        return false;
    endif;
}