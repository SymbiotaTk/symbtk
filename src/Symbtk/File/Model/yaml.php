<?php

namespace Symbiotatk\Symbtk\File\Model\Yaml;

use Symbiotatk\Symbtk\File AS File;

/*
 * YAML handler
 *   {middleware}
 *
 *   File\Read    file_get_contents
 *   File\Write   file_put_contents
 *   File\Append
 *   File\Update
 *
 *   Encode/Decode
 *   Parse
 *     Path
 *     global
 *     index
 *     inheritance
 *   Interpolate
 *
 */

/** Parse YAML
 *  @param String $str
 *  @return Object $obj
 */
function decode(String $str) {
    $obj = false;
    if ($str) {
        try {
            $obj = \spyc_load($str);
        } catch (\Throwable $e) {
            $obj = yaml_parse_error($e, $str);
        }
    }
    return (object) $obj;
}

/** Convert Array to YAML
 *  @param Array $array
 *  @return String $yaml
 */
function encode(Array $arr=NULL) {
    return ($arr) ? \spyc_dump($arr) : '';
}

/** Read, interpolate, expand YAML
 * @param String $name
 * @param Array $arr
 * @param Array $interp
 * @return Array $arr
 */
function load(String $name, Array $arr, Array $interp=NULL) {
    $arr = array_filter($arr, function ($i) {
        return (!is_null($i)) ? true : false;
    });
    $arr = array_map(function ($p) use ($name) {
        return (is_file($p))
            ? read_as_content($p)
            // : load_possible($name, $p);
            : $p;
    }, $arr);

    /*
    if ($interp) {
        $yaml = str_replace(array_keys($interp), array_values($interp), $yaml);
    }
    return ($yaml) ? (object) \spyc_load($yaml) : false;
     */
    return $arr;
}

/** Load file as content attribute
 *  @param String $path
 *  @return Array $array [ 'content' ]
 */
function read_as_content (String $path) {
    return [ 'content' => File\read($path) ];
}

/** Format YAML decode error
 *  @param \Throwable $e
 *  @param String $str
 *  @return Array $array [ 'error', 'message', 'error_message', 'content' ]
 */
function yaml_parse_error (\Throwable $e, String $str) {
    return [
        'error' => true,
        'message' => 'Yaml content contains an error.',
        'error_message' => $e->getMessage(),
        'content' => substr($str, 0, 255)
    ];
}
