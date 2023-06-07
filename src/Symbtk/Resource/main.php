<?php

namespace Symbiotatk\Symbtk\Resource;

use \Symbiotatk\Symbtk\Env AS Env;
use \Symbiotatk\Symbtk\File AS File;
use \Symbiotatk\Symbtk\File\Model\Yaml AS Yaml;
use \Symbiotatk\Symbtk\Model AS Model;
use \Symbiotatk\Symbtk AS Main;

include_once(dirname(__FILE__).'/related.php');
include_once(dirname(__FILE__).'/route.php');
include_once(dirname(__FILE__).'/spec.php');
include_once(dirname(__FILE__).'/set.php');
include_once(dirname(__FILE__).'/Addon/main.php');
include_once(dirname(__FILE__).'/Call/main.php');

const DEFAULT_PHP_FILE = 'main.php';

/** Updates Def() object with Model\RcSite\Load(), Related\Includes() [Include], Related\Callback() [Callback]
 *  @return Mixed $def|false
 */
function Def() {
    $res = Route\Parse(read_rc_file());
    if ($res) {
        $config = (array) Model\RcSite\Load(paths(), Related\path_info());
        $config['Include'] = Related\Includes($res);
        $config['Callback'] = Related\Callback($res);

        ksort($config);
        $res->config = (object) $config;
    }
    return ($res) ? $res : false;
}

/** Resource identifier
 *  @return String $id
 */
function Id() {
    return Def()->resource;
}

/** Return resource config parameter value by key
 *  @param String $name
 *  @return Mixed $value || false
 */
function Param(String $name = NULL) {
    $val = Def();
    return ($name
        && property_exists($val, 'config')
        && property_exists($val->config, $name))
        ? $val->config->$name
        : false;
}

/** Return given resource path parameter value.
 *  @param String $name
 *  @return Mixed $value || false
 */
function Path (String $name = NULL) {
    $pos = paths();

    return ($name && property_exists($pos, $name))
        ? $pos->$name
        : false;
}

/** Resource route
 *  @return String $route
 */
function Route() {
    return Def()->route;
}

/** Return Site Config (RcSite)
 *  @return Object $rcsite
 */
function SiteConfig () {
    return Model\RcSite\Load(paths(), Related\path_info());
}

/** Return Template array
 *  @return Array $template
 */
function Template () {
    $def = Def();

    return ($def
        && property_exists($def, 'rc')
        && property_exists($def, 'route')
        && ($def->route)
    )
        ? $def->rc
        : [
            'error' => true,
            'message' => 'Route does not exist. ('.Env\Param('route').')',
            'rc' => $def->rc
        ];
}

/** Resource template variables
 *  @return Array $vars
 */
function Variables() {
    $def = Def();

    return ($def && property_exists($def, 'template_vars'))
        ? $def->template_vars
        : [];
}

/** Return required include path and namespace.
 *  @return Object $obj [ 'include', 'namespace' ]
 */
function include_path_info() {
    $def = Def();

    $inc = ($def && property_exists($def, 'config')
        && property_exists($def->config, 'Include'))
        ? $def->config->Include->lib->required->include
        : false;

    $ns = ($def && property_exists($def, 'config')
        && property_exists($def->config, 'Include'))
        ? $def->config->Include->lib->required->namespace
        : false;

    return (object) [
        'include' => $inc,
        'namespace' => $ns
    ];
}

/**
 * Verify and execute callable function with appropriate namespace
 * @param String $k Function name
 * @param String $v Existing default value
 * @param Array $pos_ns List of possible namespace paths
 * @return Mixed false|$call_path Callable function path
 */
function is_available_function(String $k, String $v, Array $pos_ns) {
    for ($i=0; $i<sizeof($pos_ns); $i++){
        $call = implode(Env\NAMESPACE_DELIMITER,
            [ $pos_ns[$i], $k]);
        if (is_callable($call)) {
            return $call;
        }
    }
    $call = implode(Env\NAMESPACE_DELIMITER,
        [ Call\ns(), $k]);
    $is_call = is_callable($call);
    return ($is_call) ? $call : false;
}

/** Resource paths object (rc, rc_d, rc_possible, rcsite)
 *  @return Object $obj
 */
function paths () {
    $obj = new \stdClass();
    $obj->rc = Model\Rc\File();
    $obj->rc_d = Model\Rc\DirectoryTree();
    $obj->rc_possible = [
        'rc' => Model\Rc\File(0),
        'rc_d' => Model\Rc\DirectoryTree(0)
    ];
    $obj->rcsite = Model\RcSite\Path();
    $obj->rcsite_local = false;

    $obj = Set\Controller($obj);

    return $obj;
}

/** Read Rc file or cached copy (YAML)
 *  @return Object $rc
 */
function read_rc_file () {
    if (Main\Get(Main\CACHE_RC_FILE)) {
        return Main\Get(Main\CACHE_RC_FILE);
    }

    $rc = Model\Yaml\Rc\Parse(Model\Rc\File());
    Main\Set(Main\CACHE_RC_FILE, $rc);
    return $rc;
}
