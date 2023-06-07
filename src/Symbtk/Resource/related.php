<?php

namespace Symbiotatk\Symbtk\Resource\Related;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\Resource AS Resource;

/** Set callbacks
 *  @param Object $obj From paths()
 *  @return Object $obj
 */
function Callback (Object $obj) {
    $param = param($obj, 'callback');
    $local_param = local_param($obj, 'callback');

    $global = (is_string($param)) ? [ $param ] : $param;
    $local = (is_string($local_param)) ? [ $local_param ] : $local_param;

    return array_merge(
        $global,
        $local
    );
}

/** Additonal global CSS links
 *  @param Object $obj
 *  @return Array $global_css
 */
function Css (Object $obj) {
    return array_merge(
        param($obj, 'required', 'css'),
        local_param($obj, 'required', 'css')
    );
}

/** Combined Object for lib, css, javascript
 *  @param Object $obj
 *  @return Object $obj [ 'lib', 'css', 'javascript' ]
 */
function Includes(Object $obj) {
    return (object) [
        'lib' => Lib($obj),
        'css' => Css($obj),
        'javascript' => Javascript($obj)
    ];
}

/** Additonal global JavaScript
 *  @param Object $obj
 *  @return Array $global_javascript
 */
function Javascript (Object $obj) {
    $arr = array_merge(
        param($obj, 'required', 'js'),
        local_param($obj, 'required', 'js')
    );
    return $arr;
}

/** Return library paths
 *  @param Object $obj
 *  @return Object $obj Combined required [ 'include', 'namespace', 'url' ] && [ 'root', 'addon' => [ 'id', 'name', 'root', 'route' ], 'namespace', 'app_url', 'query_delim' ]
*/
function Lib (Object $obj) {
    $rcsite = Model\RcSite\Load(Resource\paths(), path_info());

    $paths = (object) [
        'root' => $rcsite->Root,
        'addon' => (object)[
            'id' => Resource\Addon\Id(),
            'name' => Env\Param('resource'),
            'root' => $rcsite->AddonDir,
            'route' => path_info()['Offset'].
                path_info()['QueryDelim'].
                File\ds().
                Env\Param('resource')
        ],
        'namespace' => Main\app_namespace(),
        'app_url' => path_info()['Offset'],
        'query_delim' => path_info()['QueryDelim']
    ];
    $paths->addon->url = $paths->app_url.str_replace([$paths->root], [''], $paths->addon->root);

    $ns = implode('\\', [ $paths->namespace, $paths->addon->id ]);

    $paths->required = (object) [
        'include' => include_paths ($paths->addon->root, Resource\DEFAULT_PHP_FILE),
        'namespace' => Namespaces($ns, Null),
        'url' => Urls($paths->addon->url, Null)
    ];

    return $paths;
}

/** Return namespace paths
 *  @param String $prefix
 *  @param String $postfix
 *  @return Array $priority For each priority_array()
 */
function Namespaces (String $prefix=NULL, String $postfix=NULL) {
    $delim = '\\';
    $arr = priority_array($prefix, $postfix);

    return array_map(
        function ($i) use ($delim) {
            $p = array_map(
                function ($j) {
                    return ucfirst($j);
                },
                explode(File\ds(), $i)
            );

            return rtrim(implode($delim, $p), $delim);
        },
        $arr
    );
}

/** Return url paths
 *  @param String $prefix
 *  @param String $postfix
 *  @return Array $priority priority_array()
 */
function Urls (String $prefix=NULL, String $postfix=NULL) {
    return array_map(
        function ($i) {
            return rtrim($i, File\ds());
        },
        priority_array($prefix, $postfix)
    );
}

/** Return include file paths
 *  @param String $prefix
 *  @param String $postfix
 *  @return Array $priority priority_array()
 */
function include_paths (String $prefix=NULL, String $postfix=NULL) {
    return priority_array($prefix, $postfix);
}

/** Additonal localized parameters
 *  @param Object $obj
 *  @param String $param
 *  @param String $type
 *  @return Array $local_parameter
 */
function local_param(Object $obj, String $param, String $type=NULL) {
    if (! $type) {
        return ($obj
            && property_exists((object) $obj, 'rc')
            && isset($obj->rc['local'])
            && isset($obj->rc['local'][$param])
        )
        ? $obj->rc['local'][$param]
        : [];
    }
    return ($obj
        && property_exists((object) $obj, 'rc')
        && isset($obj->rc['local'])
        && isset($obj->rc['local'][$param])
        && isset($obj->rc['local'][$param][$type])
    )
    ? (is_array($obj->rc['local'][$param][$type]))
        ? $obj->rc['local'][$param][$type]
        : [ $obj->rc['local'][$param][$type] ]
    : [];
}

/** Additonal global parameters
 *  @param Object $obj
 *  @param String $param
 *  @param String $type
 *  @return Array $global_parameter
 */
function param(Object $obj, String $param, String $type=NULL) {
    if (! $type) {
        return ($obj
            && property_exists((object) $obj, 'rc')
            && isset($obj->rc[$param])
        )
        ? (is_array($obj->rc[$param])) ? $obj->rc[$param] : [ $obj->rc[$param] ]
        : [];
    }
    return ($obj
        && property_exists((object) $obj, 'rc')
        && isset($obj->rc[$param])
        && isset($obj->rc[$param][$type])
    )
    ? $obj->rc[$param][$type]
    : [];
}

/** Return path info from Env\Param && Env\Path
 *  @return Array $info [ 'Url', 'Offset', 'QueryDelim', 'Query', 'Root' ]
 */
function path_info () {
    $arr = [];
    $arr['Url'] = Env\Param('relative');
    $arr['Offset'] = Env\Param('offset');
    $arr['QueryDelim'] = Env\Param('query_delim');
    $arr['Query'] = Env\Param('query');
    $arr['Root'] = Env\Path('root');
    return $arr;
}

/** Verify and load possible modules; if exist
 *  @return Array $possible_namespaces
 */
function possible_paths() {
    $pos = Resource\include_path_info();
    $pos_inc = is_array($pos->include)
        ? $pos->include
        : [];
    $pos_ns  = $pos->namespace;

    foreach ($pos_inc AS $inc) {
        if (is_file($inc)) {
            include_once($inc);
        }
    }

    return $pos_ns;
}

/** Sort resources in priority order (paths, namespaces, urls)
 *  @param String $prefix
 *  @param String $postfix
 *  @return Array $priority Merged resource and element related components
 */
function priority_array (String $prefix=NULL, String $postfix=NULL) {
    $resource = Env\Param('resource');
    $elements = Env\Param('elements');

    $prev = File\ds();
    $pos = array_merge([$resource], $elements);
    for ($i=0; $i<sizeof($pos); $i++) {
        $el = $pos[$i];
        $pos[$i] = ($el === $prev) ? $el : $prev.$el.File\ds();
        $prev = $pos[$i];
    }

    return array_map(function ($i) use ($prefix, $postfix) {
        $prefix = (is_null($prefix)) ? '' : $prefix;
        $postfix = (is_null($postfix)) ? '' : $postfix;

        return $prefix.$i.$postfix; }, array_reverse($pos));
}
