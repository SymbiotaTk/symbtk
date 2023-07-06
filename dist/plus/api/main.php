<?php
namespace Symbiotatk\Symbtk\Addon\Api;

use Symbiotatk\Symbtk\Env AS Env;

/** Api endpoint data response
 *  @return Object $obj
 */
function data() {
    return (object) [
        'message' => 'Data from '.__NAMESPACE__.'\\'.__FUNCTION__,
        'method' => Env\Model\Http\Method(),
        'headers' => Env\Model\Http\get_headers_all(),
        'request-data' => Env\Model\Http\Data(),
        'request-url' => Env\Param('url'),
        'error' => false
    ];

}
