<?php
/**
 * General file system routines.
 */
namespace Symbiotatk\Symbtk\File;

include_once(dirname(__FILE__)."/Model/html.php");
include_once(dirname(__FILE__)."/Model/yaml.php");

/** DIRECTORY_SEPARATOR constant
 *  @return String DIRECTORY_SEPARATOR
 */
function ds () {
    return DIRECTORY_SEPARATOR;
}

/** Interpolation function
 *  @param String $str
 *  @param Array $keyvalues
 *  @return String $str
 */
function interp(String $yaml, Array $interp) {
    return str_replace(array_keys($interp), array_values($interp), $yaml);
}

/**
 * Directory exists.
 * @param String $path
 * @return Bool
 */
function is_dir(String $path) {
    return \is_dir($path);
}

/**
 * File exists.
 * @param String $path
 * @return Bool
 */
function is_file(String $path) {
    return \is_file($path);
}

/**
 * touch || create empty file
 * @param String $path
 * @return Bool
 */
function touch(String $path) {
    return \touch($path);
}

/**
 * Mkdir
 * @param String $path
 * @param Int $mode Defaults to 0777
 * @return null
 */
function mkdir(String $path, Int $mode=0777, Bool $recursive=true) {
    if (! is_dir($path)
        && strlen($path)>0){
        \mkdir($path, $mode, $recursive);
    }
}

/** Make path from base, name, subdir
 *  @param String $basedir
 *  @param String $name
 *  @param String $offset
 *  @return String $path
 */
function mkpath (String $dir, String $name, String $subdir=NULL) {
    $path = ($subdir)
        ? mkpath_args( rtrim($dir, ds()), $subdir, $name )
        : mkpath_args( rtrim($dir, ds()), $name );
    return $path;
}

/** Make path from args
 *  @param String $args
 *  @return String $path
 */
function mkpath_args () {
    $args = func_get_args();
    return implode(ds(), $args);
}

/**
 * Rmdir
 * @param String $path
 * @return Bool
 */
function rmdir(String $path) {
    if (is_dir($path)) {
        return \rmdir($path);
    }
    return false;
}

/**
 * Scandir
 * @param String $path
 * @return Array $values
 * @todo Add preg_match filter
 * @todo Remove uneccessary code.
 */
function scandir(String $path) {
    /*
    $p = scandir(dirname($path));
    $p = glob(dirname($path) . '/kahlan-test-*', GLOB_BRACE);
    foreach($p as $dir) {
        if (is_file($dir)) {
            unlink($dir);
        } else {
            rmdir($dir);
        }
    }
    echo var_dump($p);
     */
    return array_diff(scandir($path), array('..', '.'));
}

/**
 * Remove file.
 * @param String $path
 * @return Bool
 */
function rm(String $path) {
    if (is_file($path)) {
        return \unlink($path);
    }
    return false;
}

/**
 * System environment values.
 * @return Object $attributes
 * @todo Create os attribute struct.
 */
function os_info() {
    return (object) array(
        "directory_separator" => DIRECTORY_SEPARATOR,
        "php_shlib_suffix" => PHP_SHLIB_SUFFIX,
        "path_separator" => PATH_SEPARATOR,
        "osname" => php_uname('s'),
        "hostname" => php_uname('n'),
        "release" => php_uname('r'),
        "version" => php_uname('v'),
        "machine_type" => php_uname('m')
    );
}

/**
 * Operating system name.
 * @return String $name
 */
function os() {
    return os_info()->osname;
}

/** Read file contents
 * @param String $path
 * @return String $content
 */
function read(String $path) {
    return (is_file($path))
        ? file_get_contents($path)
        : false;
}

/** Write file contents
 * @param String $path
 * @param String $content
 * @return Mixed $res 1 || false
 */
function write(String $path, String $content) {
    return (is_dir(dirname($path)))
        ? file_put_contents($path, $content)
        : false;
}

/** Write force; create directory if not exists
 * @param String $path
 * @param String $content
 * @return Mixed $res write()
 */
function write_force(String $path, String $content) {
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path));
    }

    return write($path, $content);
}
