<?php
namespace Symbiotatk\Symbtk\Model\Template;

/** String interpolation
 * @param String $str
 * @param Array $vars
 * @return String $interpolated_str
 */
function Interp (String $str, Array $vars = NULL) {
    if ($vars) {
        return str_replace(
            array_map(function ($el) { return fmvar($el); }, array_keys($vars)),
            array_values($vars),
            $str);
    }
    return $str;
}

/** Extract template variables from raw content.
 *  @param String $content
 *  @param String $pattern
 *  @return Array $variables
 */
function Variables(String $content, String $pattern=NULL) {
    $regex = ($pattern) ? $pattern : '/'.fmvar('([\w_0-9]+)').'/';
    preg_match_all($regex, $content, $matches);
    return (sizeof($matches) > 1)
        ? array_merge(array_unique($matches[1]))
        : []
    ;
}
/** Format of tempate variables to be interpolated (ref. \Model\Yaml\Rc).
 *  @param String $var_name
 *  @return String $formatted_var
 */
function fmvar(String $str) {
    return '%{'.$str.'}';
}
