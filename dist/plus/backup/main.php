<?php
namespace Symbiotatk\Symbtk\Addon\Backup;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;

/**
 * This program has two options:
 *   1) FORM-SELECT: query_labels_ids
 *       - defined by QUERY
 *       - select available collection
 *   2) FORM-SUBMIT: query_by_id
 *       - defined by QUERY
 *       - formdata { collid, uid, backup_time, backup_url, backup_passwd }
 *       - read/write formdata
 *       - execute_long_process
 *           - read formdata and execute backup at specified interval
 *       - report status
 *
 * Output:
 *   Formatted by Javascript
 *     1) FORM-SELECT: listener
 *     2) TABLE: form
 *     3) TEXT: status_message
 *
 * Method:
 *   GET /              query_labels_ids    @return html
 *   GET :json          query_labels_ids    @return json
 *   GET /<id>          query_by_id         @return html
 *   GET /<id>:json     query_by_id         @return json  READ_ONLY
 *
 *   POST /             query_labels_ids    @return json
 *   POST /{ id: <id> } query_by_id         @return json  READ_ONLY | REPORT_STATUS
 *   POST /{ id: <id>, formdata: ... } query_by_id         @return json  WRITE
 *
 */


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
