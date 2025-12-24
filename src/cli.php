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
    $query = "https://world.openfoodfacts.org/api/v0/product/$barcode.json";
    $data = api_request($query);

    if($data == NULL || !isset($data['product']) || empty($data['product'])):
        echo "We're sorry, we couldn't find any product with that barcode.\n";
        exit(0);
    endif;

    $product = $data['product'];

    $name  = $product['product_name'] ?? 'N/A';
    $brand = $product['brands'] ?? 'N/A';

    echo "Product: $name\n";
    echo "Brand: $brand\n\n";

    if(!empty($product['ingredients'])):
        echo "Ingredients:\n";

        foreach($product['ingredients'] as $ingredient):
            if(!empty($ingredient['text'])):
                echo '- ' . $ingredient['text'] . "\n";
            endif;
        endforeach;
    else:
        echo "Ingredients: N/A\n";
    endif;

    echo "\n";

    if(!empty($product['nutriments'])):
        echo "Nutrition (per 100g):\n";

        $nutriments = $product['nutriments'];

        $fields = [
            'energy-kcal_100g' => 'calories',
            'fat_100g'         => 'fat',
            'carbohydrates_100g' => 'carbohydrates',
            'sugars_100g'      => 'sugar',
            'proteins_100g'    => 'protein'
        ];

        foreach($fields as $key => $label):
            if(isset($nutriments[$key])):
                echo '- ' . $label . ': ' . $nutriments[$key] . "\n";
            endif;
        endforeach;
    else:
        echo "Nutrition (per 100g): N/A\n";
    endif;
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
        echo "We're sorry, no products matching your query could be found.\n";
        exit(0);
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