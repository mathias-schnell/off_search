<?php
require_once __DIR__ . '/api_funcs.php';
require_once __DIR__ . '/cache_funcs.php';
require_once __DIR__ . '/command_funcs.php';
require_once __DIR__ . '/help_funcs.php';

$options = getopt("v", ["version"]);
$valid_commands = ['query', 'info'];
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];
$cache_dir = __DIR__ . '/../cache/';

if(isset($options['v']) || isset($options['version'])) :
    echo "off_search v1.0\n";
    exit(0);
endif;

if(!is_dir($cache_dir)):
    mkdir($cache_dir, 0755, true);
endif;

if($argc < 2):
    off_search_help();
    exit(1);
endif;

$command = strtolower(trim($argv[1]));

switch($command):
    case "help":
        $subcom = strtolower(trim(!in_array($argv[2] ?? '', $valid_commands) ? 'main' : $argv[2]));
        $help_func = 'off_search_' . $subcom . '_help';
        $help_func();
        break;
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