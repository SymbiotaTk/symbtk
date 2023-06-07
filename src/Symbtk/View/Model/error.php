<?php
namespace Symbiotatk\Symbtk\View\Model\Error;

use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\File\Model\Html AS Html;

/** Error Def Object
 *  @return Object $obj
 */
function Def () {
    return (object) [
        'error' => false,
        'message' => false,
        Model\META_ID => (object) []
    ];
}

/** Reviews request Def for error
 *  @param Object $def
 *  @return Object $error (object) [ 'error' => false,  'message' => false, '__meta-data__' => []
 */
function has (Object $def) {
    // _ Route does not exist => return to base
    //     _ RouteExists()
    // _ Data error
    //     _ Required [element] args
    //     _ Not a well-formed request
    // _ Not authenticated
    //     _ Authenticated()
    // _ Method not allowed
    //     _ MethodAllowed()
    //
    // printf ("\n%s: %s\t%s\n\n", __FILE__, __LINE__, json_encode($def));

    // 'message' => 'Route not created in '.Model\Rc\RC_FILENAME.'. Please review dist/'.Model\Rc\RC_FILENAME.'.',

    $ns = (property_exists($def, 'namespace'))
        ? $def->namespace
        : false;
    $file = (property_exists($def, 'file'))
        ? $def->file
        : false;
    $line = (property_exists($def, 'line'))
        ? $def->line
        : false;
    $msg = (property_exists($def, 'message'))
        ? $def->message
        : 'Error';

    $error = sprintf("Error: %s; line: %s; %s; %s", $msg, $line, $ns, $file);
    if ($line) {
        echo $error;
    }

    return false;
}
