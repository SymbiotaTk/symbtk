<?php
namespace Symbiotatk\Symbtk\Env;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Resource AS Resource;
use Symbiotatk\Symbtk AS Main;

include_once(dirname(__FILE__).'/auth.php');
include_once(dirname(__FILE__).'/Model/Cli/main.php');
include_once(dirname(__FILE__).'/Model/Http/main.php');
include_once(dirname(__FILE__).'/Set/cli.php');

const MODIFIER_DELIMITER = ':';
const PATH_DELIMITER = DIRECTORY_SEPARATOR;
const NAMESPACE_DELIMITER = '\\';

/**
 * Basic environment variables object
 * @return Object $def Object [ 'method', 'path', 'request', 'settings' ]
 */
function Def() {
    return (object)[
        'method' => Env\Model\Http\Method(),
        'path' => paths(),
        'request' => Env\Model\Http\Attributes(),
        'settings' => DefSettings()
    ];
}

/**
 * Path environment variables object
 * @return Object $def Object [ 'resources', 'root', 'root_url' ]
 */
function DefPaths() {
    return (object) [
        'resources' => Resource\paths(),
        'root' => false,
        'root_url' => false
    ];
}

/**
 * Settings environment variables object
 * @return Object $def Object [ 'auth', 'config', 'required', 'route', 'template', 'template_vars' ]
 */
function DefSettings() {
    $req = Resource\Def();

    return (object) [
        'auth' => Auth\Required(),
        'config' => ($req && property_exists($req, 'config'))
            ? $req->config : false,
        'callback' => ($req && property_exists($req, 'rc'))
            ? Resource\Set\Callback((object) $req->rc) : false,
        'required' => ($req && property_exists($req, 'rc'))
            ? Resource\Set\Required((object) $req->rc) : false,
        'route' => ($req && property_exists($req, 'route'))
            ? $req->route : false,
        'template' => ($req && property_exists($req, 'rc'))
            ? $req->rc : false,
        'template_vars' => ($req && property_exists($req, 'template_vars'))
            ? $req->template_vars : false
    ];
}

/** Return array value from GET object
 *  @param String $attribute_name
 *  @return Mixed $value || Array $get
 */
function Get(String $name = NULL) {
    return ($name)
        ? Env\Model\Http\Param($name)
        : Env\Model\Http\Data()->get;
}

/** Environment variable object [Def()]
 *  @return Object $def
 */
function Info() {
    return Def();
}

/** Return environment parameter value by key
 *  @param String $name
 *  @return Mixed $value || false
 */
function Param(String $name = NULL) {
    $val = Env\Model\Http\Attributes();
    return ($name && property_exists($val, $name))
        ? $val->$name
        : false;
}

/** Return environment path value by key
 *  @param String $name
 *  @return Mixed $value || false
 */
function Path(String $name = NULL) {
    $val = paths();
    return ($name && property_exists($val, $name))
        ? $val->$name
        : false;
}

/** Command line identifier constant
 *  @return String $str
 */
function cli_domain() {
    return '__CLI__';
}

/** Command line protocol constant
 *  @return String $str
 */
function cli_protocol() {
    return 'file://';
}

/** Verify Command line
 *  @return Bool false || true
 */
function is_cli() {
    $host = Env\Model\Http\Host();
    return ($host == cli_domain()) ? true : false;
}

/** Utility to provide a log file.
 *  @param String $message
 *  @return Bool false
 */
function log(String $msg) {
    date_default_timezone_set("America/Chicago");
    $log = (Main\Get(Main\CUSTOM_LOG))
        ? Main\Get(Main\CUSTOM_LOG)
        : Main\app_log();

    $msg = date("Y.m.d h:i:sa").": $msg\n";
    return error_log($msg, 3, $log);
}

/** Return updated DefPaths object
 *  @return Object $paths
 */
function paths() {
    $obj = DefPaths();
    $obj->root = Main\app_path();
    $obj->root_url = (Param('offset') === '')
        ? PATH_DELIMITER
        : Param('offset').PATH_DELIMITER;

    return $obj;
}

/** Utility to generate a uuid (alias to uuidv4)
 *  @param String $data Seed data for generating (Must be 15 characters or more in length.)
 *  @return String $uuid
 */
function uuid(String $data=NULL) {
    return uuidv4($data);
}

/** Utility to generate a uuid (v4)
 *  @param String $data Seed data for generating (Must be 15 characters or more in length.)
 *  @return String $uuid
 */
function uuidv4(String $data=NULL) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
