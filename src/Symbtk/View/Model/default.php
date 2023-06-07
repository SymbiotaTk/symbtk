<?php
namespace Symbiotatk\Symbtk\View\Model\Def;

use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\View AS ViewModel;

/** Default TEXT render
 *  @return Object $template
 */
function Render () {
    $data = Model\Data();
    $def = ViewModel\Def();
    $res = ViewModel\DefResponse();

    $res->content = print_r($data, true);
    $res->{'content-type'} = $def->{'content-type'};
    $res->{'meta-data'} = $def;
    $res->{'response-code'} = $res->{'response-code'};
    $res->size = strlen($res->content);

    return $res;
}
