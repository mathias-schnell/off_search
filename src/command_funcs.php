<?php
require_once __DIR__ . '/cache_funcs.php';

/**
 * Handle the `info` command
 *
 * Validates arguments, fetches product data by barcode from the
 * Open Food Facts API (or cache), and prints product details,
 * ingredients and nutrition information
 */
function handle_info($argc, $argv) {
    if($argc < 3):
        echo "Error: Missing barcode number.\n";
        exit(1);
    endif;

    $options = parse_options($argv);
    $no_cache = $options['flags']['no-cache'] ?? false;
    $bc = strtolower(trim($argv[2]));
    if(($data = check_cache('info', $bc)) == false || $no_cache):
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
 * Handle the `query` command
 *
 * Validates arguments, constructs a search request to the
 * Open Food Facts search API (or cache), displays a short list
 * of matching products and prompts the user to select one for more
 * details by calling `handle_info()`
 */
function handle_query($argc, $argv) {
    if($argc < 3):
        echo "Error: Missing query string.\n";
        exit(1);
    endif;
    $query = strtolower(trim($argv[2]));
    $options = parse_options($argv);
    $no_cache = $options['flags']['no-cache'] ?? false;
    $limit = (int)($options['options']['limit'] ?? 5);

    if(($data = check_cache('query', $query)) == false || $no_cache):
        $base_url = 'https://world.openfoodfacts.org/cgi/search.pl';
        $params = [
            'search_fields' => 'product_name',
            'search_terms'  => $query,
            'search_simple' => 1,
            'action'        => 'process',
            'json'          => 1,
            'page_size'     => 50,
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
    $data['products'] = array_slice($data['products'], 0, $limit);
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
 * Parse trailing CLI options and flags from an argv array
 *
 * Scans `$argv` starting at `$start_index` and returns an associative
 * array with three keys:
 *  - `flags`: boolean flags (e.g. `--no-cache` -> `['no-cache' => true]`)
 *  - `options`: options with values (e.g. `--limit=10` or `--limit 10`)
 *
 * It supports both `--name=value` and `--name value` forms. Items that do
 * not begin with `--` are collected into `args`
 */
function parse_options(array $argv, int $start_index = 3): array {
    $result = ['flags' => [], 'options' => []];
    $len = count($argv);

    for ($i = $start_index; $i < $len; $i++):
        $arg = $argv[$i];
        if (strlen($arg) >= 2 && substr($arg, 0, 2) === '--'):
            $token = substr($arg, 2);
            if (strpos($token, '=') !== false):
                list($name, $value) = explode('=', $token, 2);
                $result['options'][$name] = $value;
            else:
                if (($i + 1) < $len && strlen($argv[$i + 1]) > 0 && $argv[$i + 1][0] !== '-'):
                    $result['options'][$token] = $argv[$i + 1];
                    $i++;
                else:
                    $result['flags'][$token] = true;
                endif;
            endif;
        endif;
    endfor;

    return $result;
}
