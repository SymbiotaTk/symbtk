<?php
namespace Symbiotatk\Symbtk\Env\Model\Http;

use Symbiotatk\Symbtk\Env AS Env;

include_once(dirname(__FILE__).'/auth.php');
include_once(dirname(__FILE__).'/parse.php');

/**
 * HTTP attributes
 * @return Object $attributes
 */
function Attributes()
{
    $host = Host();
    $protocol = ($host == Env\cli_domain())
        ? Env\cli_protocol() : Protocol();

    $uri = Uri();
    $hash = Parse\Hash($uri);
    $query = QueryString();
    $offset = Parse\Offset($uri);
    $query_delim = (strpos($uri, '?')) ? '/?' : '';
    $route = Parse\Route($uri);
    $route = (strpos($route, Env\MODIFIER_DELIMITER))
        ? explode(Env\MODIFIER_DELIMITER, $route)[0]
        : $route;

    $obj = Def();

    $obj->base = $protocol . $host . $offset;
    $obj->data = Data();
    $obj->elements = Parse\Elements($uri);
    $obj->full = $protocol
        . $host
        . $offset
        . $query_delim
        . $query
        . $hash;
    $obj->hash = $hash;
    $obj->host = $host;
    $obj->modifier = Parse\Modifier($uri);
    $obj->offset = $offset;
    $obj->protocol = $protocol;
    $obj->protocol_host = $protocol . $host;
    $obj->query = $query;
    $obj->query_delim = $query_delim;
    $obj->route = $route;
    $obj->relative = $uri;
    $obj->resource = Parse\Resource($uri);
    $obj->uri = $obj->relative;
    $obj->url = $obj->full;

    return $obj;
}

/**
 * Request data object
 * @return Object $req
 */
function Data () {
    return (object) [
        'cookie' => $_COOKIE,
        'get' => $_GET,
        'post' => Post()
    ];
}

/**
 * HTTP request environment variables object
 * @return Object $def Object [ 'base', 'data', 'elements', 'full', 'hash', 'host', 'method', 'modifier', 'offset', 'protocol', 'query', 'query_delim', 'relative', 'resource', 'route', 'uri', 'url' ]
 */
function Def() {
    return (object) [
        'base' => false,
        'data' => false,
        'elements' => false,
        'full' => false,
        'hash' => false,
        'host' => false,
        'method' => Method(),
        'modifier' => false,
        'offset' => false,
        'protocol' => false,
        'protocol_host' => false,
        'query' => false,
        'query_delim' => false,
        'relative' => false,
        'resource' => false,
        'route' => false,
        'uri' => false,
        'url' => false
    ];
}

/** Get headers by URL
 *  @param String $url
 *  @return Array $headers
 */
function Headers(String $url, String $method='GET', Array $arr=[]) {
    $arr = [];

    $context = stream_context_create();
    if ($method !== 'GET') {
        $data = http_build_query($arr);
        $context = stream_context_create(
            [
                'http' => [
                    'method' => $method,
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                ]
            ]
        );
    }

    $headers = get_headers($url, false, $context);
    foreach ($headers AS $header) {
        $p = explode(': ', $header);
        $key = (isset($p[1]))
            ? $p[0]
            : 'status';
        $value = (isset($p[1]))
            ? $p[1]
            : $p[0];

        if ($key === 'status') {
            $p = explode(' ', $value);
            $protocol = $p[0];
            array_shift($p);
            $code = $p[0];
            array_shift($p);
            $message = implode(' ', $p);
            $arr['status-protocol'] = $protocol;
            $arr['status-code'] = (int) $code;
            $arr['status-message'] = $message;
        }
        $arr[$key] = $value;
    }

    return $arr;
}

/**
 * Request host
 * @return String $host
 */
function Host() {
    return (isset($_SERVER['HTTP_HOST']))
        ? $_SERVER['HTTP_HOST']
        : Env\cli_domain();
}

/**
 * Media types (also known as MIME types)
 * @param String $name
 * @return Mixed false|String $value|Array $media
 */
function MediaTypes (String $name=NULL) {
    $media = [
       'binary' => 'application/octet-stream',
       'css' => 'text/css',
       'csv' => 'text/csv',
       'form' => 'multipart/form-data',
       'html' => 'text/html',
       'javascript' => 'text/javascript',
       'json' => 'application/json',
       'text' => 'text/plain',
       'txt' => 'text/plain',
       'xml' => 'text/xml'
    ];

    return (! $name)
        ? $media
        : ((isset($media[$name]))
            ? $media[$name]
            : false);
}

/**
 * Request method
 * @return String $method
 */
function Method() {
        return (isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : 'GET';
}

/** Return request parameter value by name
 *  @param String $name
 *  @return Mixed $value || false
 */
function Param (String $name) {
    $data = Data();

    return (is_object($data)
        && property_exists($data, 'get')
        && in_array($name, array_keys($data->get))
    ) ? $data->get[$name] : false;
}

/**
 * Request port
 * @return Int $port
 */
function Port() {
    return (isset($_SERVER['SERVER_PORT']))
        ? $_SERVER['SERVER_PORT']
        : 80;
}

/**
 * POST data from $_POST, or php://input
 * @return Array $arr
 */
function Post() {
    $post = (sizeof($_POST) > 0) ? $_POST : file_get_contents("php://input");
    $req = [
        'raw' => $post,
        'arr' => (is_string($post)) ? json_decode($post) : $post
    ];
    return (array) $req['arr'];
}

/**
 * Request protocol
 * @return String $protocol
 */
function Protocol() {
    return
        ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            || Port() == 443)
            ? "https://"
            : ((Env\is_cli()) ? Env\cli_protocol() : "http://");
}

/**
 * Request query string
 * @return String $query_string
 */
function QueryString() {
    return (isset($_SERVER['QUERY_STRING']))
        ? $_SERVER['QUERY_STRING']
        : false;
}

/**
 * Request uri
 * @return String $uri
 */
function Uri() {
    return (isset($_SERVER['REQUEST_URI']))
        ? $_SERVER['REQUEST_URI']
        : '';
}

function get_headers_all() {
    if (function_exists('getallheaders')) {
        return getallheaders();
    }
    $pos = [
        'PHP_SELF',
        'SCRIPT_NAME',
        'SCRIPT_FILENAME',
        'PATH_TRANSLATED',
        'DOCUMENT_ROOT',
        'REQUEST_TIME_FLOAT',
        'REQUEST_TIME',
        'REQUEST_METHOD',
        'REQUEST_URI',
        'QUERY_STRING'
    ];
    $out = [];
    foreach($_SERVER as $key=>$value) {
        if (substr($key,0,5)=="HTTP_") {
            $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
            $out[$key]=$value;
        } else {
            if (in_array($key, $pos)) {
                $out[$key]=$value;
            }
        }
    }
    return $out;
}
