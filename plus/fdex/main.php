<?php
namespace Symbiotatk\Symbtk\Addon\Fdex;

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

    $a = (object) [
        'data' => false,
        'id' => $id
    ];

    $a->data = ($a->id)
        ? Data\get($a->id)
        : Data\collection_collids();

    return (array) $a;
}

function GetOptions(Object $symb) {
    $def = Def();
    return (object) array_merge(
        $def,
        GetElements($def),
        (array) $symb
    );
}

function GetPost(Object $symb) {
    return (Env\Model\Http\Method() === 'GET')
        ? GetOptions($symb)
        : Post($symb);
}

function Post(Object $symb) {
    $obj = GetOptions($symb);
    $obj->function = default_js_func();
    return $obj;
}

function default_js_func () {
    return 'select_collection';
}
