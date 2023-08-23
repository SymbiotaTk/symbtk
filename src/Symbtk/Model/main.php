<?php
namespace Symbiotatk\Symbtk\Model;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Model\Ns AS Ns;
use Symbiotatk\Symbtk\Resource AS Resource;

include_once(dirname(__FILE__).'/rc.php');
include_once(dirname(__FILE__).'/rc_site.php');
include_once(dirname(__FILE__).'/namespace.php');
include_once(dirname(__FILE__).'/string.php');
include_once(dirname(__FILE__).'/symbiota.php');
include_once(dirname(__FILE__).'/template.php');
include_once(dirname(__FILE__).'/Yaml/rc.php');
include_once(dirname(__FILE__).'/Yaml/form.php');

const META_ID = '__meta-data__';

/** Return callback names for passing to JavaScript
 * @return Array $callbacks
 */
function Callbacks() {
    $c = Env\Info();
    return $c->settings->callback;
}

/** Using Env\Param('modifier') or provided param from data response to determine response content-type
 * @param String $modifier
 * @return String $content_type
 */
function ContentType(String $modifier=NULL) {
    $default = 'txt';

    $options = Env\Model\Http\MediaTypes();

    $mod = ($modifier)
        ? $modifier
        : ((Env\Param('modifier'))
           ? Env\Param('modifier')
           : $default);

    return (in_array($mod, array_keys($options)))
        ? $options[$mod]
        : $options[$default];
}

/** Load callback generated data
 * @return Array $callback_data
 */
function Data() {
    $callbacks = Callbacks();

    if (is_null($callbacks) || (! is_array($callbacks))) {
        return [];
    }

    $pos_ns = Resource\Related\possible_paths();

    $rep = (object) [];
    foreach ($callbacks AS $call) {
        $rep->$call = false;
    }
    $rep = (object)
        array_combine(
            array_keys((array)$rep),
            array_map(
                function ($k, $v) use ($pos_ns) {
                    return Resource\is_available_function($k, $v, $pos_ns);
                },
                array_keys((array)$rep),
                array_values((array)$rep)
    ));

    foreach (array_keys((array)$rep) AS $k) {
        if (! $rep->$k) {
            unset($rep->$k);
        }
    }

    $available = (array) $rep;

    $res = array_map(
        function ($c) {
            return $c();
        },
        $available
    );

    $res[META_ID] = (object)[];

    foreach ($res AS $i) {
        if (is_object($i) && property_exists($i, 'title')) {
            $res[META_ID]->title = $i->title;
        }
    }
    return $res;
}

/** Include files for Model (ie. css, javascript)
 * @return Object $includes [ lib, css, js ]
 */
function Includes() {
    $def = Resource\Def();

    return ($def && property_exists($def, 'config'))
        ? $def->config->Include
        : (object) [];
}

/** Return meta-data attribute value from Model\Data()
 *  @param Object $obj  Model\Data()
 *  @param String $attribute
 *  @return Mixed false|$value
 */
function MetaData(Object $obj, String $param) {
    return (property_exists($obj, $param))
        ? $obj[META_ID]->$param
        : false;
}

/** Request method
 * @return String $method [ GET, POST, PUT, DEL ]
 */
function Method() {
    return Env\Param('method');
}

/** Model related paths and relative urls
 * @param String $str Path parameter key
 * @return Mixed false|$value
 */
function Paths(String $str=NULL) {
    $def = (object) [
        'app_directory' => Env\Path('root'),
        'app_url' => Env\Param('offset'),
        'app_parent_url' => dirname(Env\Param('offset')),
        'app_query_delim' => Env\Param('query_delim'),
        'modifier' => Env\Param('modifier'),
        'resource' => Env\Param('resource'),
        'resource_url' => Resource\Addon\Url(),
        'route' => Env\Param('route'),
        'url' => Env\Param('url'),
        'uuid' => Env\uuid(),
        'addon_directory' => Resource\Addon\Path()
    ];

    return ($str && property_exists($def, $str))
        ? $def->$str
        : false;
}

/** Response code; using internal evaluation.
 * @param Int $code
 * @return Object $obj Containing $obj->code, $obj->message
 */
function ResponseCode(Int $code=200) {
    $options = (object) [
        200 => 'OK',           // route exists
        201 => 'Created',      // API create
        202 => 'Accepted',     // API accepted
        204 => 'No Content',   // Response no content
        206 => 'Partial Content',   // Response partial content
        303 => 'See Other',
        304 => 'Not Modified', // API not modified
        305 => 'Use Proxy',    // Use Proxy; login
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        407 => 'Proxy Authentication Required',
        501 => 'Not Implemented'
    ];

    http_response_code($code);
    return (object) [
        'code' => http_response_code(),
        'message' => (property_exists($options, $code))
            ? $options->$code
            : false
    ];
}
