<?php
namespace Symbiotatk\Symbtk\Addon\Genbank;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;

include_once (__DIR__.'/Data.php');

function AuthEnabled(Object $symb) {
    return false;
}

function Content() {
    $symb = (object) Symbiota\isInstance();

    return ($symb->error
            || AuthEnabled($symb)
        )
        ? (array) $symb
        : GetPost($symb);
}

function Def() {
    $env = Env\Model\Http\Attributes();
    return [
        'http' => $env,
        'elements' => Env\Param('elements'),
        'method' => Env\Model\Http\Method(),
        'modifier' => Env\Param('modifier'),
        'resource' => Env\Param('resource'),
        'resource_url' => implode(File\ds(), [
            $env->offset.$env->query_delim,
            $env->resource
        ]),
        'url' => $env->relative
    ];
}

function GetElements(Array $def) {
    $def = (object) $def;
    $id = (isset($def->elements[0]))
        ? $def->elements[0]
        : false;

    $label = (isset($def->elements[1]))
        ? $def->elements[1]
        : false;

    $id_parts = explode(',', $id);
    $num = (isset($id_parts[1]))
        ? $id_parts[1]
        : false;
    $tid = ($num)
        ? $id_parts[0]
        : false;

    $a = (object) [
        'data' => false,
        'catalog_number' => (($num) && ($tid)) ? $num : false,
        'collid' => (($num) && ($id)) ? $tid : false,
        'occid' => ((! $num) && ($id)) ? $id : false,
        'id' => $id,
        'num' => $num,
        'label' => $label
    ];

    $a->data = ($a->occid)
        ? Data\get($a->occid, $a->label)
        : (($a->catalog_number)
           ? Data\get($a->id, $a->label)
           : Data\collection_collids());

    return (array) $a;
}

function GetPost(Object $symb) {
    return (Env\Model\Http\Method() === 'GET')
        ? GetOptions($symb)
        : Post($symb);
}

function GetOptions(Object $symb) {
    $def = Def();

    return (object) array_merge(
        $def,
        GetElements($def),
        (array) $symb
    );
}

function Post(Object $symb) {
    $obj = GetOptions($symb);
    $obj->function = 'select_collection';
    return $obj;
}

function collections_list(Object $symb) {
    return Data\collection_collids();
}
