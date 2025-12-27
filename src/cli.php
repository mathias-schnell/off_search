<?php
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];
$cache_dir = __DIR__ . '/../cache/';

if(!is_dir($cache_dir)):
    mkdir($cache_dir, 0755, true);
endif;

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

/**
 * Handle the `info` command.
 *
 * Validates arguments, fetches product data by barcode from the
 * Open Food Facts API (or cache), and prints product details,
 * ingredients and nutrition information to stdout.
 */
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

    echo "\nNutrition (per 100g): " . (!empty($data['product']['nutriments']) ? "" : 'N/A') . "\n";
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

/**
 * Handle the `query` command.
 *
 * Validates arguments, constructs a search request to the
 * Open Food Facts search API (or cache), displays a short list
 * of matching products and prompts the user to select one.
 */
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
        check_cache('query', $query, $data);
    endif;

    echo "Results for $query:\n\n";
    foreach($data['products'] as $index => $product):
        $name  = $product['product_name'] ?? 'N/A';
        $brand = ' — ' . ($product['brands'] ?? 'N/A');
        $code  = ' — ' . ($product['code'] ?? 'N/A');
        echo "[" . ($index + 1) . "] $name $brand $code\n";
    endforeach;
    echo "\nSelect an item [1-" . count($data['products']) . "] or press Enter to cancel: ";

    if (($input = trim(fgets(STDIN))) === ''):
        return;
    endif;

    if (!isset($data['products'][(int)$input - 1])):
        echo "Invalid selection.\n";
        return;
    else:
        echo "\n";
        handle_info(3, ['cli.php', 'info', $data['products'][$input - 1]['code']]);
    endif;
}

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