<?php

namespace Symbiotatk\Symbtk\Model\String;

/** Check if string contains any html tags.
 *  @param String $str
 *  @return Bool false|true
 */
function is_html(String $string) {
    return (preg_match('/<\s?[^\>]*\/?\s?>/i', $string))
        ? true
        : false;
}

/** Check if string is valid JSON
 *  @param String $str
 *  @return Bool false|true
 */
function is_json(String $string) {
    if(is_numeric($string)) return false;
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/** Check if is_string
 *  @param Mixed $str
 *  @return Bool false|true
 */
function is_text($string) {
    return is_string($string);
}
