<?php
namespace Symbiotatk\Symbtk\Model\Def;

use Symbiotatk\Symbtk\Resource AS Resource;
use Symbiotatk\Symbtk\Ns AS Ns;
use Symbiotatk\Symbtk\Env AS Env;

/** Default response
 */
function EmptyContent() {
    return Welcome();
}

/** Basic response
 * @return String $str
 */
function Welcome() {
    $name = str_replace(
        [Env\NAMESPACE_DELIMITER],
        [Resource\ds()],
        strtolower(Ns\parent(__NAMESPACE__)));
    return 'Welcome to '.$name;
}
