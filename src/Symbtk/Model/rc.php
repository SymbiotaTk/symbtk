<?php
namespace Symbiotatk\Symbtk\Model\Rc;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;

const RC_FILENAME = '.symbtkrc';
const RC_D_NAME = '.symbtkrc.d';
const RC_D_INDEX = 'index';
const RC_D_GLOBAL = 'global';
const RC_DATA_DIR = 'data';

/** Return filtered resource.d tree
 * @return Array $tree
 */
function DirectoryTree (Int $check=1) {
    // _ !! if site_php::plugin_dir available, include full path to plugin::Model
    //
    $arr = [ rc_d_index() ];
    $resource = (Env\Param('resource')
        && Env\Param('resource') != File\ds()
    )
        ? rc_d_index(Env\Param('resource'))
        : null;
    if ($resource) { array_push($arr, $resource); }
    $elements = Elements();
    if ($elements) { foreach($elements as $ele) { array_push($arr, $ele); }; }
    $modifier = Modifier($arr);
    if ($modifier) { foreach($modifier as $ele) { array_push($arr, $ele); }; }

    if (! $check || $check != 1)  {
        return $arr;
    }

    $filtered = array_merge($arr);
    $filtered = array_filter($filtered, function ($el) {
        return (is_file($el)) ? $el : null;
    });

    return $filtered;
}

/** Return all possible resource elements.
 * @return Array $elements
 */
function Elements () {
    $resource = (Env\Param('resource'))
        ? Env\Param('resource')
        : null;
    $arr = false;
    $elements = Env\Param('elements');
    if ($resource && $elements && is_array($elements) && sizeof($elements) > 0) {
        $arr = [];
        $path = $resource;
        foreach ($elements as $ele) {
            $path = implode(File\ds(), [ $path, $ele ]);
            array_push($arr, rc_d_index($path));
        }
    }
    return $arr;
}

/** Defines resource file (.rc)
 * @return string $path
 */
function File(Int $check=1) {
    $rc = Model\Rc\name();
    $dir = (Main\rc_directory_alt())
        ? Main\rc_directory_alt()
        : Main\cwd();

    $file = $dir.File\ds().$rc;
    return ($check && $check == 1
        && (! is_file($file)))
        ? false
        : $file;
}

/** Return resource modifier configuration.
 * @return Array $mods
 */
function Modifier (Array $arr) {
    $val = Env\Param('modifier');
    $mods = [];
    if ($arr && $val) {
        foreach($arr AS $ele) {
            $dir = dirname($ele);
            array_push($mods, implode(File\ds(), [ $dir, $val ]));
        }
    }
    sort($mods);
    return $mods;
}

/** Resource data directory name.
 *  @return String $directory_name
 */
function data_directory() {
    return RC_DATA_DIR;
}

/** Resource directory index file name.
 *  @return String $name
 */
function directory_index() {
    return RC_D_INDEX;
}

/** Resource directory global file name.
 *  @return String $name
 */
function directory_global() {
    return RC_D_GLOBAL;
}

/** Resource directory name.
 *  @return String $name
 */
function dirname() {
    return RC_D_NAME;
}

/** Defines resource file directory (.rc.d)
 * @return string $path
 */
function dirpath() {
    $dir = (Main\rc_directory_alt())
        ? Main\rc_directory_alt()
        : Main\cwd();
    return File\mkpath($dir, dirname());
}

/** Resource file name.
 *  @return String $name
 */
function name() {
    return RC_FILENAME;
}

/** Defines resource global file (.rc.d/?/global)
 * @return String $path
 */
function rc_d_global(String $path=NULL) {
    return ($path)
        ? File\mkpath_args(dirpath(), $path, Model\Rc\directory_global())
        : File\mkpath_args(dirpath(), Model\directory_global());
}

/** Defines resource index file (.rc.d/?/index)
 * @return String $path
 */
function rc_d_index(String $path=NULL) {
    return ($path)
        ? File\mkpath_args(dirpath(), $path, Model\Rc\directory_index())
        : File\mkpath_args(dirpath(), Model\Rc\directory_index());
}
