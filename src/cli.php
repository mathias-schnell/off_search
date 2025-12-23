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
    echo "[STUB] Query - $query\n";
}