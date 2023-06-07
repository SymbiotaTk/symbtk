<?php
namespace Symbiotatk\Symbtk\View\Model\Html;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\Resource AS Resource;
use Symbiotatk\Symbtk\View AS View;
use Symbiotatk\Symbtk\File\Model\Html AS Html;

/** [in progress] Render HTML response object
 *  @return Object $html_template
 */
function Render () {
    // Compare to curl -X 'GET' "https://www.mycoportal.org/portal/tk/?/"
    // Use Model\Data()
    // Interpolate template-vars from (paths, includes, etc.)
    // Build DOMDocument skeleton using (includes)
    //     - css, javascript
    //     - Model\Data()
    //
    $def = View\Def();
    $def->data = Model\Data();
    $res = View\DefResponse();

    // If Model\Data() error; update $def->{response-code}
    // If Model\Data(Auth()); redirect to login if necessary
    // Convert YAML to HTML
    //
    // Does callback imply Data->Table, Data->Grid, etc?
    $html = Html\Create();

    // Get title from $def->Model\Data()
    $html = (Model\MetaData($def, 'title'))
        ? Html\title($html, Model\MetaData($def, 'title'))
        : $html;

    $html = append_css($def, $html);

    $node = append_content_wrapper($def);

    $html = append_javascript($def, Html\Append($html, $node, 'body'));

    return set_header(callback_content($html, $def, $res), $def);
}

/** Attach CSS references from View\Def()->includes
 *  @param Object $def View\Def()
 *  @param String $html
 *  @return String $html
 */
function append_css(Object $def, String $html) {
    $id = 'css';
    if (! property_exists($def->includes, $id)) {
        return $html;
    }
    $additional = [
        'rel' => 'stylesheet',
        'media' => 'screen',
        'type' => Env\Model\Http\MediaTypes($id)
    ];
    foreach ($def->includes->css AS $l) {
        $html = Html\Append($html,
            Html\m('link', array_merge($additional, [
                'href' => Model\Template\Interp($l, template_vars())
            ])), 'head');
    }
    $html = Html\Append($html,
        Html\m('style', [
            'type' => Env\Model\Http\MediaTypes($id) ],
            '.main-wrapper { display: none; }'),
        'head');
    return $html;
}

/**
 *  Wrapper for HTML; for handling page loading.
 *  @param Object $def
 *  @return \DOMElement $node
 */
function append_content_wrapper(Object $def) {
    $node = Html\m('div',
        [ 'xml:id' => 'fouc', 'id' => 'fouc', 'class' => 'main-wrapper' ],
        [ Html\m('div',
        [ 'id' => 'post-content' ]),
        Html\m('div', [ 'id' => Main\APP_HTML_CONTAINER_ID ],
        format_content($def))] );
    return $node;
}

/** Attach JavaScript references from View\Def()->includes
 *  @param Object $def View\Def()
 *  @param String $html
 *  @return String $html
 */
function append_javascript(Object $def, String $html) {
    $id = 'javascript';
    if (! property_exists($def->includes, $id)) {
        return $html;
    }
    $additional = [
        'language' => 'JavaScript',
        'type' => Env\Model\Http\MediaTypes($id)
    ];
    foreach ($def->includes->javascript AS $l) {
        $html = Html\Append($html,
            Html\m('script',
            array_merge(
                $additional,
                [ 'src' => Model\Template\Interp($l, template_vars())
            ])), 'body');
    }
    return $html;
}

function callback_content(String $html, Object $def, Object $res) {
    $res->{'content-type'} = $def->{'content-type'};
    $res->{'meta-data'} = $def;

    $json = callback_json($def, $res);
    return ($json)
        ? $json
        : callback_set_content($html, $def, $res);
}

function callback_error(String $html, Object $def) {
    $app_error = false;
    $callback_error = false;

    $app_error = Main\Get(Main\APP_ERROR_MESSAGE);

    if ($app_error) {
        $html = Html\Replace($html, Main\Get(Main\APP_ERROR_MESSAGE), 'id:'.Main\APP_HTML_CONTENT_CONTAINER_ID);
    } else {
        // $lipsum = new \joshtronic\LoremIpsum();
        // $text = $lipsum->paragraphs(17, [ 'article', 'p' ]);
        //
        $content_id = 'content-resource';
        $wrapper = Html\m('div', [ 'id' => $content_id ]);
        $html = Html\Replace($html, $wrapper, 'id:'.Main\APP_HTML_CONTENT_CONTAINER_ID);
        foreach ($def->callbacks AS $callback) {
            if (isset($def->data[$callback])) {
                $content = $def->data[$callback];

                // ERROR MESSAGE
                if (is_array($content)
                    && isset($content['error'])
                    && $content['error']
                    && isset($content['message'])
                ) {
                    $callback_error = format_error($content['message']);
                }
                $content_div = ($callback_error)
                    ? $callback_error
                    : Html\m('div', [ 'id' => $callback ], $content);

                $html = Html\Append($html, $content_div, 'id:'.$content_id);
                $callback_error = false;
            }
        }
        $text = json_encode($def);
    }

    return $html;
}

function callback_json(Object $def, Object $res) {
    // JSON RESPONSE (SINGLE)
    $mode = 'json';
    foreach ($def->callbacks AS $callback) {
        if (isset($def->data[$callback])) {
            $content = $def->data[$callback];
            if (is_object($content)
                && property_exists($content, 'type')
                && $content->type == $mode
            ) {
                $res->content = $content->content;
                $res->{'content-type'} = Env\Model\Http\MediaTypes($mode);
                return set_header($res);
            }
        }
    }
    return false;
}

function callback_set_content(String $html, Object $def, Object $res) {
    $res->content = callback_error($html, $def);
    return $res;
}

function check_for_error(Object $def, Array $template) {
    $error = false;
    View\Model\Error\has($def);

    if (isset($template['error'])
    && ($template['error'])) {
        $error = format_error($template['message']);
        $template = (isset($template['rc'])
            && $template['rc'])
            ? $template['rc']
            : View\Model\Error\has(
                (object) (
                    array_merge($template,
                    [ 'namespace' => __NAMESPACE__, 'line' => __LINE__, 'file' => __FILE__ ])));
        Main\Set(Main\APP_ERROR_MESSAGE, $error);
    }
    return $template;
}

function format_error(String $message) {
    return Html\m('div',
        [ 'class' => 'error' ],
        $message);
}

/** Format content
 *  @param Object $def
 *  @return Mixed $string_or_array Array may contain String and Html\m()
 */
function format_content(Object $def) {
    return object_to_html(check_for_error($def, Resource\Template()));
}

/**
 * Build HTML from Object
 * @param Array $arr
 * @return Array $nodes
 */
function object_to_html(Array $arr) {
    $nodes = [];
    foreach(array_keys($arr) as $ele) {
        $nodes = (tag($ele))
            ? object_to_node($nodes, $ele, $arr[$ele])
            : $nodes;
    }
    return $nodes;
}

/**
 * Object to array of DOMElements
 * @param Array $arr  To append.
 * @param String $tag
 * @param Mixed $ele Array|String
 * @param \DOMDocument $doc  Maintain common document for recursive calls
 */
function object_to_node(Array $arr, String $tag, $ele, \DOMDocument $doc=NULL) {
    $doc = ($doc) ? $doc : new \DOMDocument();
    $children = (is_array($ele)) ? array_filter(
        $ele,
        function ($k) {
            return (tag($k));
        },
        ARRAY_FILTER_USE_KEY
    ) : [];
    $attributes = (is_array($ele)) ? array_filter(
        $ele,
        function ($k) {
            return ((! tag($k)) && ($k !== 'text'));
        },
        ARRAY_FILTER_USE_KEY
    ) : [];
    $text = (is_string($ele))
        ? [ $ele ]
        : ((is_array($ele)) ? array_values(
            array_filter(
            $ele,
            function ($k) {
                return ($k == 'text');
            },
            ARRAY_FILTER_USE_KEY
        )) : []);

    $keys_or_text = (is_array($ele)) ? array_keys($ele) : $ele;

    $node = $doc->createElement(tag($tag));
    foreach (array_keys($attributes) AS $attr) {
        $node->setAttribute($attr, Model\Template\Interp($attributes[$attr], template_vars()));
    }
    foreach ($text AS $txt) {
        $t = $doc->createTextNode(Model\Template\Interp($txt, template_vars()));
        $node->appendChild($t);
    }
    $node_children = [];
    foreach (array_keys($children) as $child) {
        $node_children = object_to_node(
            $node_children,
            $child,
            $children[$child],
            $doc
        );
        foreach ($node_children as $nchild) {
            $node->appendChild($nchild);
        }
    }
    array_push($arr, $node);
    return $arr;
}

function set_header(Object $res) {
    $res->{'response-code'} = $res->{'response-code'};
    $res->size = strlen($res->content);

    return $res;
}

/**
 * A tag begins and ends with '<' '>'
 * @param String $str
 * @return String $tag_name|false
 */
function tag(String $str) {
    if (substr($str, 0, 1) === '<') {
        $str = str_replace(['<','>'], ['',''], $str);
        $pos = strpos($str, '|');
        $cln = ($pos) ? substr($str, 0, $pos) : $str;
        return ($cln !== "") ? $cln : false;
    }
    return false;
}

/**
 * Template key => value pairs
 * @return Array $pairs
 */
function template_vars() {
    $def = View\Def();

    // template key=>values
    $arr = [];
    foreach ($def->{'template-vars'} AS $k) {
        if (Model\Paths($k)) {
            $arr[$k] = Model\Paths($k);
        }
    }
    return $arr;
}
