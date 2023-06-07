<?php
namespace Symbiotatk\Symbtk\Data;

use Symbiotatk\Symbtk\Env as Env;

include_once(dirname(__FILE__).'/Sqlite/main.php');
include_once(dirname(__FILE__).'/Sqlite/Model/eav.php'); // ONLY code specific to Sqlite
include_once(dirname(__FILE__).'/Mysql/main.php');
include_once(dirname(__FILE__).'/Mysql/Model/eav.php');  // ONLY code specific to Mysql
include_once(dirname(__FILE__).'/Model/eav.php');        // Model should toggle between specified handlers (ie. sqlite, mysql);

/** Call dynamic namespace function
 *  @param String $handler SQLite|mySQL
 *  @param String $func Function name
 *  @return String $function_path
 */
function call(String $handler, String $func) {
    $npaths = explode("\\", __NAMESPACE__);
    $npath = implode("\\", array_merge($npaths, [ ucfirst($handler) ]));
    return "$npath\\$func";
}

/** Get PDO driver name
 *  @param \PDO $conn
 *  @return String $driver_name
 */
function driver(\PDO $conn) {
    return $conn->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));
}

/** Execute dynamic function call
 *  @param String $handler SQLite|mySQL
 *  @param String $func Function name
 *  @return Mixed $result
 */
function func (String $handler, String $func) {
    $npath = ns_path();
    $call = "$npath\call";
    return $call($handler, $func);
}

/** Namespace path
 * @param Array $extra Optional array of strings for constructing path.
 * @return String $path
 */
function ns_path (Array $extra=NULL) {
    $ext = [];

    if ($extra) { $ext = array_merge($ext, $extra); }
    $npaths = explode(Env\NAMESPACE_DELIMITER, __NAMESPACE__);
    return implode(Env\NAMESPACE_DELIMITER, array_merge($npaths, $ext));
}
