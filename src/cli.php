<?php
require_once __DIR__ . '/api_funcs.php';
require_once __DIR__ . '/cache_funcs.php';
require_once __DIR__ . '/command_funcs.php';
require_once __DIR__ . '/help_funcs.php';

$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];
$cache_dir = __DIR__ . '/../cache/';

if(!is_dir($cache_dir)):
    mkdir($cache_dir, 0755, true);
endif;

if($argc < 2):
    off_search_help();
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