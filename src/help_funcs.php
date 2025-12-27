<?php

function off_search_help() {
    echo <<<EOF
off_search — Open Food Facts CLI search tool

USAGE:
  off_search query "<search terms>"
  off_search info <barcode>

OPTIONS:
  -h, --help        Show this help message
  --no-cache        Ignore cached results
  --limit <n>       Number of results to return (default: 5)
  --color           Force colored output
  --no-color        Disable colored output

COMMANDS:
  query             Search for products by name
  info              Fetch detailed nutrition info by barcode

EXAMPLES:
  off_search query "dark chocolate"
  off_search query "oat milk" --limit 10
  off_search info 737628064502
  off_search query "cereal" --no-cache

NOTES:
  • Results are cached locally to reduce API calls
  • Requires PHP with curl enabled
  • Data provided by Open Food Facts

EOF;
    exit(0);
}

function off_search_info_help() {
    echo <<<EOF
off_search — Open Food Facts CLI search tool

USAGE:
  off_search query "<search terms>"
  off_search info <barcode>

OPTIONS:
  -h, --help        Show this help message
  --no-cache        Ignore cached results
  --limit <n>       Number of results to return (default: 5)
  --color           Force colored output
  --no-color        Disable colored output

COMMANDS:
  query             Search for products by name
  info              Fetch detailed nutrition info by barcode

EXAMPLES:
  off_search query "dark chocolate"
  off_search query "oat milk" --limit 10
  off_search info 737628064502
  off_search query "cereal" --no-cache

NOTES:
  • Results are cached locally to reduce API calls
  • Requires PHP with curl enabled
  • Data provided by Open Food Facts

EOF;
    exit(0);
}

function off_search_query_help() {
    echo <<<EOF
off_search — Open Food Facts CLI search tool

USAGE:
  off_search query "<search terms>"
  off_search info <barcode>

OPTIONS:
  -h, --help        Show this help message
  --no-cache        Ignore cached results
  --limit <n>       Number of results to return (default: 5)
  --color           Force colored output
  --no-color        Disable colored output

COMMANDS:
  query             Search for products by name
  info              Fetch detailed nutrition info by barcode

EXAMPLES:
  off_search query "dark chocolate"
  off_search query "oat milk" --limit 10
  off_search info 737628064502
  off_search query "cereal" --no-cache

NOTES:
  • Results are cached locally to reduce API calls
  • Requires PHP with curl enabled
  • Data provided by Open Food Facts

EOF;
    exit(0);
}