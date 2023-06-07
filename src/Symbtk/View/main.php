<?php
namespace Symbiotatk\Symbtk\View;

use Symbiotatk\Symbtk\View\Model AS ViewModel;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\Resource AS Resource;

include_once (dirname(__FILE__).'/Model/default.php');
include_once (dirname(__FILE__).'/Model/error.php');
include_once (dirname(__FILE__).'/Model/header.php');
include_once (dirname(__FILE__).'/Model/html.php');
include_once (dirname(__FILE__).'/Model/json.php');

/** ViewModel attribute object
 *  @return Object $attributes
 */
function Def() {
    $modparam = Env\Param('modifier');
    $mod = ($modparam != '')
        ? $modparam
        : ((in_array(Model\Method(), ['POST','PUT','DELETE'])) ? 'json' : 'html');

    $content_type = Model\ContentType($mod);
    return (object) [
        'callbacks' => Model\Callbacks(),
        'content-type' => $content_type,
        'elements' => Env\Param('elements'),
        'includes' => Model\Includes(),
        'method' => Model\Method(),
        'modifier' => $mod,
        'paths' => Model\Paths(),
        'template' => false,
        'template-vars' => Resource\Variables()
    ];
}

/** Response definition object
 * @return Object $attributes
 */
function DefResponse () {
    return (object) [
        'content' => false,
        'content-type' => false,
        'error' => false,
        'message' => false,
        'meta-data' => false,
        'response-code' => Model\ResponseCode(),
        'size' => false
    ];
}

/** Render object
 * @return Object $obj ViewModel\DefResponse;
 */
function Render() {
    $def = Def();
    $res = false;

    switch ($def->modifier) {
    case 'json':
        $res = ViewModel\Json\Render();
        break;
    case 'html':
        $res = ViewModel\Html\Render();
        break;
    default:
        $res = ViewModel\Def\Render();
    }

    if ($res) {
        ViewModel\Header\Set($res);
    }

    return $res;
}
