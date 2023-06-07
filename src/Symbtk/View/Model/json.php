<?php
namespace Symbiotatk\Symbtk\View\Model\Json;

use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\View AS ViewModel;

/** Render JSON response
 *  @param Array $data  Model\Data()
 *  @return Object $json_template
 */
function Render () {
    $data = Model\Data();
    $def = ViewModel\Def();
    $res = ViewModel\DefResponse();

    // Also applies to GraphQL
    // Use Model\Data()
    // Interpolate template-vars from (paths, includes, etc.)
    // json_encode()
    //
    $res->content = json_encode($data);
    $res->{'content-type'} = $def->{'content-type'};
    $res->{'meta-data'} = $def;
    $res->{'response-code'} = $res->{'response-code'};
    $res->size = strlen($res->content);

    return $res;
}
