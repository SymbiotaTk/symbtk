<?php
namespace Symbiotatk\Symbtk\Model\Ns;

use Symbiotatk\Symbtk\Env AS Env;

/** Format namespace function call
 *  @param String $handler SQLite || mySQL
 *  @param String $func
 *  @return String $func_path
 */
function call(String $handler, String $func) {
    $npaths = explode("\\", __NAMESPACE__);
    $npath = implode("\\", array_merge($npaths, [ ucfirst($handler) ]));
    return "$npath\\$func";
}

/** Return key=>value list of defined constants related to namespace
 *  @param String $namespace
 *  @return Array $array
 */
function constant_array (String $ns) {
    $arr = [];
    foreach (constant_names($ns) as $const) {
        $name = substr($const, strlen($ns) + 1);
        $arr[$name] = constant("$ns\\$name");
    };

    return $arr;
}

/** Return list of defined constants related to namespace
 *  @param String $namespace
 *  @return Array $array
 */
function constant_names (String $ns) {
    return array_filter(array_keys(get_defined_constants(TRUE)['user']), function ($name) use ($ns)
    {
        return 0 === strpos($name, $ns);
    });
}

/** Localize namespace function call
 *  @param String $handler SQLite || mySQL
 *  @param String $func
 *  @return String $func_path
 */
function func (String $handler, String $func) {
    $npath = path();
    $call = "$npath\call";
    return $call($handler, $func);
}

/** Return parent path for given namespace
 *  @param String $namespace
 *  @return String $parent_namespace
 */
function parent (String $ns) {
    $npaths = explode(Env\NAMESPACE_DELIMITER, $ns);
    array_pop($npaths);
    $npath = implode(Env\NAMESPACE_DELIMITER, $npaths);
    return $npath;
}

/** Construct namespace path
 *  @param Array $additional_elements
 *  @return String $namespace
 */
function path (Array $extra=NULL) {
    $ext = [];

    if ($extra) { $ext = array_merge($ext, $extra); }
    $npaths = explode(Env\NAMESPACE_DELIMITER, __NAMESPACE__);
    return implode(Env\NAMESPACE_DELIMITER, array_merge($npaths, $ext));
}
