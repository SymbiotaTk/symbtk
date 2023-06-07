<?php
namespace Symbiotatk\Symbtk\Model\Yaml\Rc;

use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\File\Model\Yaml AS Yaml;

/** \Model\Yaml\Rc object containing raw, template_vars, and array parameters.
 *  @return Object $yaml_rc
 */
function Def() {
    $obj = new \stdClass();
    $obj->raw = false;
    $obj->template_vars = false;
    $obj->rc = false;

    return $obj;
}

/** Parse Rc file located at file path.
 *  @param String $path
 *  @return Object \Model\Yaml\Rc\\$object
 */
function Parse(String $path) {
    $def = Def();

    $def->raw = File\read($path);
    $def->template_vars = Model\Template\Variables($def->raw);
    $def->rc = Yaml\decode($def->raw);

    return $def;
}
