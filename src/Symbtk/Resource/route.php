<?php
namespace Symbiotatk\Symbtk\Resource\Route;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;

/** [not implemented]
 *  @return false
 */
function FunctionalMappings() {
    return false;
}

/** Verify configuration route reference, and assemble route meta-data object
 *  @param Object $config
 *  @return Mixed $obj false || [ 'array', 'resource', 'route', 'template_vars' ]
 */
function Parse(Object $obj) {
    $req = find_route($obj);

    return ($req)
        ? (object) [
            'rc' => ((array) $obj->rc)[$req],
            'resource' => Env\Param('resource'),
            'route' => $req,
            'template_vars' => $obj->template_vars
        ]
        : (object) [
            'rc' => (isset(((array) $obj->rc)[Main\APP_DEFAULT_ROUTE]))
            ? ((array) $obj->rc)[Main\APP_DEFAULT_ROUTE]
            : false,
            'resource' => false,
            'route' => false,
            'template_vars' => $obj->template_vars
        ];
}

/** Find all configuration route references, and filter by Env\Param('route')
 *  @param Object $config
 *  @return Mixed $obj false || $array
 */
function find_route(Object $obj) {
    $keys = array_keys((array) $obj->rc);
    $keys = array_filter($keys, function ($el) {
        return (substr($el, 0, 1) === Env\PATH_DELIMITER)
            ? $el
            : false;
    });

    usort($keys, function($a, $b) {
        return strlen($b) - strlen($a);
    });

    $route = Env\Param('route');
    for ($i=0; $i < sizeof($keys); $i++) {
        if (
            $keys[$i] === $route ||
            $keys[$i].Env\PATH_DELIMITER === substr($route, 0, strlen($keys[$i])+1)) {
            return $keys[$i];
        }
    }
    return false;
}
