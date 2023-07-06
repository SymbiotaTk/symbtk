<?php
namespace Symbiotatk\Symbtk\Addon\Fdex;

use Symbiotatk\Symbtk\Env\Model\Http AS Http;
use Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;
use Symbiotatk\Symbtk\Resource\Addon AS Addon;

/**
 * This program has two options:
 *   1) FORM-SELECT: query_labels_ids
 *       - defined by QUERY
 *   2) TABLE-REPORT: query_by_id
 *       - defined by QUERY
 *
 * Output:
 *   Formatted by Javascript
 *     1) FORM-SELECT: listener
 *     2) TABLE: formatted_report
 *
 * Method:
 *   GET /              query_labels_ids    @return html
 *   GET :json          query_labels_ids    @return json
 *   GET /<id>          query_by_id         @return html
 *   GET /<id>:json     query_by_id         @return json
 *
 *   POST /             query_labels_ids    @return json
 *   POST /{ id: <id> } query_by_id         @return json
 *
 */

include_once (__DIR__.'/sql.php');

const DEFAULT_FUNC = __NAMESPACE__.'\GetOrPost';
const DEFAULT_JS_FUNC = 'select_collection';

function Args() {
    return (object) [
        'data' => false,
        'id' => Http\Request\firstelement()
    ];
}

/** Main content callback
 *  @return String $html_or_json
 */
function Content() {
    return Symbiota\Run(DEFAULT_FUNC);
}

function GetData() {
    $a = Args();

    $a->data = ($a->id)
        ? query_by_id($a->id)
        : query_labels_ids();

    return (array) $a;
}

function GetOrPost(Object $symb) {
    return (Http\Method() === 'GET')
        ? Addon\ResponseOptions((array) $symb, GetData())
        : Post($symb);
}

function Post(Object $symb) {
    $obj = Addon\ResponseOptions((array) $symb, GetData());
    $obj->function = DEFAULT_JS_FUNC;
    return $obj;
}

function data_fetch(Object $obj, String $id=NULL) {
    return ($id)
        ? data_fetch_by_id($obj)
        : data_fetch_collections($obj);
}

function data_fetch_collections(Object $obj) {
    $label = 'collections';
    $obj->$label = (array) [];
    $obj->{'meta-data'} = (object) [];
    $obj->{'meta-data'}->$label = (object) [
        'field_count'=> null,
        'num_rows'=> 0
    ];
    foreach ($obj as $row) {
        array_push($obj->$label, $row);
        $obj->{'meta-data'}->$label->num_rows++;
        $obj->{'meta-data'}->$label->field_count = sizeof($row);
    }

    return $obj;
}

function data_fetch_by_id(Object $obj=NULL) {
    if (!$obj) { return (object) [ 'message' => 'No record found.' ]; }
    $label = 'result';
    $obj->$label = $obj->fetch_object();

    return $obj;
}

function fdex_db_exists() {
    $stmt = Sql\DB_EXISTS_FDEX;
    $res = query($stmt);

    return (property_exists($res, 'num_rows')
        && $res->num_rows == 1)
        ? true
        : false;
}

function query (String $statement, String $id=NULL) {
    $conn = Symbiota\mysqlInfo();
    $res = $conn->query($statement);
    $res->__stmt__ = $statement;

    return ($res) ? data_fetch($res, $id) : false;
}

function query_by_id(String $id) {
    $id = Http\Request\sanitize($id);
    $stmt = str_replace(['%{collid}'],[$id], Sql\SELECT_COLLECTION);
    $stmt = (fdex_db_exists())
        ? str_replace(['%{fdex}'],[Sql\SELECT_FDEX], $stmt)
        : str_replace(['%{fdex}'],['NULL'], $stmt);

    return query($stmt, $id);
}

function query_format(String $sql, String $collid) {
    return (strpos($occid, delim_catalog_number()))
	? query_format_catalog_number($sql, $occid, $seqid)
	: query_format_occid($sql, (integer) $occid, $seqid);
}

function query_labels_ids() {
    $stmt = Sql\COLLECTIONS;
    return query($stmt);
}
