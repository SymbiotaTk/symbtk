<?php
namespace Symbiotatk\Symbtk\Env\Set\Cli;

use Symbiotatk\Symbtk\Env AS Env;

/**
 * Set CLI request
 * @return Bool false
 */
function Request(String $uri, String $method='GET', String $data=null) {
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['QUERY_STRING'] = Env\Model\Http\Parse\QueryString($uri);
    parse_str(Env\Model\Http\Parse\QueryString($uri), $_GET);
}

/**
 * Set CLI cookie
 * @return Bool false
 */
function Cookie(String $name, String $value) {
    $_COOKIE[$name] = $value;
}

/**
 * Set CLI post data
 * @return Bool false
 */
function Post(String $json) {
    $_POST = json_decode($json);
}
