<?php
namespace Symbiotatk\Symbtk\View\Model\Header;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\View AS ViewModel;

/** Set appropriate header TEXT || HTML || JSON || BINARY
 *  @return false
 */
function Set (Object $res) {
    if (! Env\is_cli()) {
        $content_type = $res->{'content-type'};
        $size = $res->size;
        $method = Env\Model\Http\Method();

        header("Content-type: $content_type; charset=utf-8");
        header("Content-length: $size");
        header("Request-method: $method");
        header("Request-extra: Extra");

        // header('Content-Disposition: attachment; filename="downloaded.pdf"');
        // readfile($file);
    }

    return false;
}
