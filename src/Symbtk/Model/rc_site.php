<?php
namespace Symbiotatk\Symbtk\Model\RcSite;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;

const SITE_CONFIG_FILE = '.site.php';

/** Basic resource configuration site file (.site.php)
 *  @return Object $obj [ 'SiteName', 'AdminEmail', 'DbHandler', 'Dsn', 'DataDir', 'AddonDir' ]
 */
function Def () {
    $obj = new \stdClass();
    $obj->Root = Main\cwd();
    $obj->SiteName = false;
    $obj->AdminEmail = false;
    $obj->DbHandler = "sqlite";
    $obj->Dsn = ":memory";
    $obj->DataDir = false;
    $obj->AddonDir = false;
    return $obj;
}

/** Check if RcSite configuration file is valid, parse, and load.
 *  @param Object $resource_paths
 *  @param Array $related_path_info
 *  @return Object $obj Def && Related\path_info()
 */
function Load (Object $paths=NULL, Array $related=NULL) {
    $paths = ($paths)
        ? $paths
        : (object) [
            'rcsite' => Path()
        ];
    $related = ($related)
        ? $related
        : [];
    $obj = Def();
    $custom_obj = false;
    ob_start();
    if (is_file($paths->rcsite)) {
        try {
            $custom_obj = File\Model\YAML\decode(Model\Template\Interp(File\read($paths->rcsite), [ 'app_root' => Main\cwd() ] ));
        } catch (\Exception $e) {
            $obj->error = $e->getMessage();
        }
    }
    ob_end_clean();

    return ($custom_obj)
        ? (object) array_merge(
            (array) $obj,
            $related,
            (array) $custom_obj)
        : $obj;
}

/** Resource configuration site file path
 *  @return String $path
 */
function Path (String $path=NULL) {
    $path = ($path) ? $path : SITE_CONFIG_FILE;
    $dir = (Main\rc_directory_alt())
        ? Main\rc_directory_alt()
        : Main\cwd();

    return File\mkpath($dir, $path);
}
