<?php
namespace Symbiotatk\Symbtk\Env\Model\Http\Request;

use \Symbiotatk\Symbtk\Env AS Env;

function firstelement() {
    $ele = Env\Param('elements');
    return (isset($ele[0]))
        ? $ele[0]
        : false;
}

function element_by_index(Int $int) {
    $ele = Env\Param('elements');
    return (isset($ele[$int]))
        ? $ele[$int]
        : false;
}

function sanitize(String $str=NULL) {
    if (! $str) { return $str; }
    $pc = explode(';', $str);
    $ps = explode(' ', $pc[0]);

    return $ps[0];
}
