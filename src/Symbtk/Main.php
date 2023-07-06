<?php

namespace Symbiotatk\Symbtk;

use \Symbiotatk\Symbtk\Env AS Env;
use \Symbiotatk\Symbtk\File AS File;
use \Symbiotatk\Symbtk\Model AS Model;
use \Symbiotatk\Symbtk\View AS View;

include_once(dirname(__FILE__).'/Env/main.php');
include_once(dirname(__FILE__).'/Resource/main.php');
include_once(dirname(__FILE__).'/File/main.php');
include_once(dirname(__FILE__).'/Model/main.php');
include_once(dirname(__FILE__).'/Data/main.php');
include_once(dirname(__FILE__).'/View/main.php');

const APP_ID = 'SYMBTK';
const APP_LOG = 'symbtk.log';
const APP_SRC_PATH = __DIR__;
const APP_PATH_ID = APP_ID.'_PATH';
const ALTERNATE_RC_DIR = APP_ID.'_ALTERNATE_RC_DIR';
const CACHE_RC_FILE = APP_ID.'_CACHE_RC_FILE';
const CUSTOM_LOG = APP_ID.'_CUSTOM_LOG';
const APP_ERROR_MESSAGE = APP_ID.'_ERROR_MESSAGE';
const APP_DEFAULT_ROUTE = '/';
const APP_HTML_CONTAINER_ID = 'symbtk';
const APP_HTML_CONTENT_CONTAINER_ID = 'content';

/** Clear application global variable
 * @param String $name
 * @return Bool false
 */
function Clear(String $name) {
    if (isset($GLOBALS[APP_ID])
        && isset($GLOBALS[APP_ID][$name]))
    {
        unset($GLOBALS[APP_ID][$name]);
    }
    return false;
}

/** Get application global variable
 * @param String $name
 * @return Mixed false|$value
 */
function Get(String $name) {
    return (isset($GLOBALS[APP_ID])
        && isset($GLOBALS[APP_ID][$name]))
        ? $GLOBALS[APP_ID][$name]
        : false;
}

/** Main run process
 *  @param String $path
 *  @return String $content  ViewModel object content attribute
 */
function Run (String $path=NULL) {
    if ($path) { Set(APP_PATH_ID, $path); }

    $obj = View\Render();
    return $obj->content;
}

/** Set application global variable
 * @param String $name
 * @param Mixed $value
 * @return Bool false
 */
function Set(String $name, $value) {
    if (! isset($GLOBALS[APP_ID])) { $GLOBALS[APP_ID] = []; }
    $GLOBALS[APP_ID][$name] = $value;
    return false;
}

/** Application log
 * @return String $path
 */
function app_log() {
    return File\mkpath(sys_get_temp_dir(),APP_LOG);
}

/** Application namespace
 * @return String $namespace
 */
function app_namespace() {
    return __NAMESPACE__;
}

/** Application directory path
 * @return String $path
 */
function app_path() {
    return Get(APP_PATH_ID);
}

/** Application source library directory path
 * @return String $path
 */
function app_lib_path() {
    return dirname(__FILE__);
}

/** Alternate rc directory; Mainly for testing
 *  @return Mixed false|$path
 */
function rc_directory_alt() {
    return (Get(ALTERNATE_RC_DIR))
        ? Get(ALTERNATE_RC_DIR)
        : false;
}

/** Request object (ref. \Env\Info->request)
 * @return Object $req
 */
function request() {
    return Env\Info()->request;
}

if (!function_exists('str_starts_with')) {
    /**
     * Standard string utility.
     * If not supplied by current compiler.
     * @param String $haystack String to search.
     * @param String $needle Substring to find.
     * @return Bool
     */
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Standard string utility.
     * If not supplied by current compiler.
     * @param String $haystack String to search.
     * @param String $needle Substring to find.
     * @return Bool
     */
    function str_ends_with($haystack, $needle) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
    }
}

if (!function_exists('str_contains')) {
    /**
     * Standard string utility.
     * If not supplied by current compiler.
     * @param String $haystack String to search.
     * @param String $needle Substring to find.
     * @return Bool
     */
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
