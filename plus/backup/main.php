<?php
namespace Symbiotatk\Symbtk\Addon\Backup;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;

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

function GetPost(Object $symb) {
    return (Env\Model\Http\Method() === 'GET')
        ? special_permissions($symb)
        : Post($symb);
}

function Post(Object $symb) {
    return (object) [
        'type' => 'json',
        'content' => json_encode($_SERVER)
    ];
}

function special_permissions (Object $symb) {
    $email = (property_exists($symb, 'symbini')
        && property_exists($symb->symbini, 'ADMIN_EMAIL'))
        ? $symb->symbini->ADMIN_EMAIL
        : '';
    $message = "This requires additional permissions. Please contact the portal administrator. $email";

    return [
        'error' => true,
        'message' => $message
    ];
}
