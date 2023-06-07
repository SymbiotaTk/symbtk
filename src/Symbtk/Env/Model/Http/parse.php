<?php
namespace Symbiotatk\Symbtk\Env\Model\Http\Parse;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;

/**
 * Find elements
 * @param String $uri
 * @return Array $elements
 */
function Elements(String $uri) {
    $route = Route($uri);
    $route = (strpos($route, Env\MODIFIER_DELIMITER))
        ? explode(Env\MODIFIER_DELIMITER, $route)[0]
        : $route;

    return (($route === Env\PATH_DELIMITER)
        || (sizeof(elements_from_string($route)) < 2))
        ? []
        : array_filter(
            array_slice(elements_from_string($route),1),
            function( $v ) {
                return !( is_null( $v) or '' === $v ); }
        );
}

/**
 * Find hash
 * @param String $uri
 * @return String $hash
 */
function Hash (String $uri) {
    $parts = parse_url($uri);
    $args = [];
    return (isset($parts['fragment']))
        ? $parts['fragment']
        : '';
}

/**
 * Find modifier
 * @param String $uri
 * @return String $modiifer
 */
function Modifier (String $uri) {
    $route = Route($uri);
    return (strpos($route, Env\MODIFIER_DELIMITER))
        ? explode(Env\MODIFIER_DELIMITER, $route)[1]
        : '';
}

/**
 * Find offset
 * @param String $uri
 * @return String $offset
 */
function Offset(String $uri) {
    $parts = parse_url($uri);
    return rtrim($parts['path'], File\ds());
}

/**
 * Find QUERY_STRING
 * @param String $uri
 * @return String $query_string
 */
function QueryString(String $uri) {
    $parts = parse_url($uri);
    return (isset($parts['query'])) ? $parts['query'] : false;
}

/**
 * Find resource
 * @param String $uri
 * @return String $resource
 */
function Resource(String $uri) {
    $route = Route($uri);
    $route = (strpos($route, Env\MODIFIER_DELIMITER))
        ? explode(Env\MODIFIER_DELIMITER, $route)[0]
        : $route;

    return ($route === Env\PATH_DELIMITER)
        ? $route
        : explode(Env\PATH_DELIMITER,
            ltrim($route, Env\PATH_DELIMITER))[0];
}

/**
 * Find route
 * @param String $uri
 * @return String $route
 */
function Route(String $uri) {
    $parts = parse_url($uri);
    $args = [];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $args);
    }
    $keys = array_keys($args);
    return (sizeof($keys) > 0)
        ? $keys[0]
        : File\ds();
}

/**
 * Elements from string
 * @param $str
 * @return Array $ele
 */
function elements_from_string(String $str) {
    return explode(Env\PATH_DELIMITER, ltrim($str, Env\PATH_DELIMITER));
}
