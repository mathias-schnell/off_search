<?php

function off_search_main_help() {
    echo <<<EOF
off_search — Open Food Facts CLI search tool

USAGE:
  off_search help <commmand>
  off_search info <barcode> [OPTIONS]
  off_search query "<search terms>" [OPTIONS]

OPTIONS:
  --limit <n>       Number of results to return (default: 5)
  --no-cache        Ignore cached results and query the API directly

COMMANDS:
  help              Show this help message or the help message for a specific command
  info              Fetch detailed nutrition info by barcode
  query             Search for products by name

EXAMPLES:
  off_search help
  off_search help info
  off_search info 737628064502
  off_search query "dark chocolate"
  off_search query "oat milk" --limit 10

NOTES:
  • Results are cached locally to reduce API calls
  • Requires PHP with curl enabled
  • Data provided by Open Food Facts

EOF;
    exit(0);
}

function off_search_info_help() {
    echo <<<EOF
off_search info — Fetch detailed nutrition information for a product

USAGE:
  off_search info <barcode> [OPTIONS]

OPTIONS:
  --no-cache        Ignore cached results and query the API directly

DESCRIPTION:
  Use this command to get detailed product information for a specific barcode.
  Includes ingredients list and nutrition facts (per 100g).

EXAMPLES:
  off_search info 737628064502
  off_search info 762221057846 --no-cache

NOTES:
  • Results are cached locally to reduce API calls.
  • Requires PHP with curl enabled.
  • Data provided by Open Food Facts.

EOF;
    exit(0);
}

function off_search_query_help() {
    echo <<<EOF
off_search query — Search for products by name using Open Food Facts

USAGE:
  off_search query "<search terms>" [OPTIONS]

OPTIONS:
  --limit <n>       Number of search results to return (default: 5)
  --no-cache        Ignore cached results and query the API directly

DESCRIPTION:
  Use this command to search for products by name. 
  Returns a list of products with their brand and barcode.

EXAMPLES:
  off_search query "dark chocolate"
  off_search query "oat milk" --limit 10
  off_search query "cereal" --no-cache

NOTES:
  • Search results are cached locally to reduce API calls.
  • Requires PHP with curl enabled.
  • Data provided by Open Food Facts.

EOF;
    exit(0);
}
