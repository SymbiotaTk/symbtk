<?php
namespace Symbiotatk\Symbtk\Env\Auth;

use Symbiotatk\Symbtk\Env AS Env;

/** Basic authentication object
 *  @return Object $auth
 */
function Def() {
    return (object) [
        'uid' => false,
        'permission' => false
    ];
}

/** Verify authentication
 *  @return Mixed Cli() || Http()
 */
function Required() {
    // _ find authentication
    //     _ per application/site_php()
    //     _ per route
    //
    // _ Prompt for credentials
    //
    // _ Return Def()
    //
    return (Env\is_cli())
        ? Env\Model\Cli\Auth\Verify()
        : Env\Model\Http\Auth\Verify();
}
