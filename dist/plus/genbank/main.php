<?php
namespace Symbiotatk\Symbtk\Addon\Genbank;

use Symbiotatk\Symbtk\Env\Model\Http AS Http;
use Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;
use Symbiotatk\Symbtk\Resource AS Resource;
use Symbiotatk\Symbtk\Resource\Addon AS Addon;

/**
 * This program has two options:
 *   1) FORM-SELECT: query_labels_ids
 *       - defined by QUERY
 *   2) TABLE-REPORT: query_by_args
 *       - args: id, filter
 *       - defined by QUERY
 *
 * Output:
 *   Formatted by Javascript
 *     1) FORM-SELECT_W_INPUT: listener
 *     2) TABLE: formatted_report
 *
 * Method:
 *   GET /              query_labels_ids    @return html
 *   GET :json          query_labels_ids    @return json
 *   GET /<id>          query_by_args       @return html
 *   GET /<id>:json     query_by_args       @return json
 *
 *   POST /             query_labels_ids    @return json
 *   POST /{ id: <id>, filter: <filter> } query_by_args        @return json
 *
 */

include_once (__DIR__.'/sql.php');
include_once (__DIR__.'/format.php');

const DELIM_ARGS = ',';
const DEFAULT_FUNC = __NAMESPACE__.'\GetOrPost';
const DEFAULT_JS_FUNC = 'select_collection';

function Args() {
    $id = Http\Request\firstelement();
    $label = Http\Request\element_by_index(1);

    $id_parts = explode(',', $id);
    $num = (isset($id_parts[1]))
        ? $id_parts[1]
        : false;
    $tid = ($num)
        ? $id_parts[0]
        : false;

    return (object) [
        'data' => false,
        'catalog_number' => (($num) && ($tid)) ? $num : false,
        'collid' => (($num) && ($id)) ? $tid : false,
        'occid' => ((! $num) && ($id)) ? $id : false,
        'id' => $id,
        'num' => $num,
        'label' => $label
    ];
}

function Content() {
    return Symbiota\Run(DEFAULT_FUNC);
}

function GetData() {
    $a = Args();

    $a->data = ($a->occid)
        ? query_by_id($a->occid, $a->label)
        : (($a->catalog_number)
           ? query_by_id($a->id, $a->label)
           : query_labels_ids());

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

function data_fetch(Object $obj) {
    return (is_collection_query($obj))
        ? data_fetch_collections($obj)
        : data_fetch_by_args($obj->fetch_object());
}

function data_fetch_collections(Object $obj) {
    $obj->collections = (array) [];
    foreach ($obj as $row) {
        array_push($obj->collections, $row);
    }
    return $obj;
}

function data_fetch_by_args(Object $obj=NULL) {
    if (!$obj) { return (object) [ 'message' => 'No record found.' ]; }
    $obj->collection_date = Format\date($obj->collection_date);
    $obj->host = Format\host($obj->host);
    $obj->identified_by = Format\identified_by_fix($obj->identified_by);
    $obj = Format\country($obj);
    $obj = Format\lat_lon($obj);
    $obj = Format\sort_order($obj);

    return $obj;
}

function is_collection_query(Object $obj) {
    return ($obj && property_exists($obj, 'field_count') && $obj->field_count == 3) ? true : false;
}

function query (String $statement, String $occid, String $seqid) {
    $conn = Symbiota\mysqlInfo();
    $res = $conn->query(query_format($statement, $occid, $seqid));

    return ($res) ? data_fetch($res) : false;
}

function query_format_catalog_number(String $sql, String $occid, String $seqid) {
    $parts = explode(DELIM_ARGS, $occid);
    $code = (isset($parts[0])) ? $parts[0] : false;
    $num = (isset($parts[1])) ? $parts[1] : false;

    $code = (! filter_var($code, FILTER_VALIDATE_INT)) ? -1 : $code;

    if ($code && $num) {
        $sql = str_replace(['%{where}'], [Sql\WHERE_COLLID], str_replace([PHP_EOL], [' '], $sql));
        $sql = str_replace(['%{catalog_number}', '%{collid}', '%{seqid}'], [$num, $code, $seqid], str_replace([PHP_EOL], [' '], $sql));
        return $sql;
    }

    return false;
}

function query_format_occid(String $sql, Int $occid, String $seqid) {
    $sql = str_replace(['%{where}'], [Sql\WHERE_OCCID], str_replace([PHP_EOL], [' '], $sql));
    $sql = str_replace(['%{occid}', '%{seqid}'], [$occid, $seqid], str_replace([PHP_EOL], [' '], $sql));
    return $sql;
}

function query_format(String $sql, String $occid, String $seqid) {
    return (strpos($occid, DELIM_ARGS))
	? query_format_catalog_number($sql, $occid, $seqid)
	: query_format_occid($sql, (integer) $occid, $seqid);
}

function query_by_id(String $occid=NULL, String $seqid=NULL) {
    $endpoint = Resource\Addon\Url().'/<[occid|institution_code,catalog_number>/<sequence_id>';

    $occid = Http\Request\sanitize($occid);
    $seqid = ($seqid) ? Http\Request\sanitize($seqid) : "ERROR: Please provide a sequence id. [$endpoint]";

    $query_statement = Sql\ATTRIBUTES_BY_WHERE;

    if ($occid == 'Collections') {
        $query_statement = Sql\COLLECTIONS;
    }

    $obj = (object) [
	'occid' => ($occid) ? $occid : "Please provide a valid occid, or collection_id,catalog_number. [$endpoint]",
	'seqid' => $seqid,
	'raw' => ($occid && $seqid)
		? query_format($query_statement, $occid, $seqid)
		: 'Error: Unable to generate data query.',
	'result' => ($occid && $seqid)
		? query($query_statement, $occid, $seqid)
		: 'Error: No result.'
    ];

    if ($obj->result && property_exists($obj->result, 'occid')) {
        if ($obj->occid !== $obj->result->occid) {
            $obj->occid = $obj->result->occid;
        }
        unset($obj->result->occid);
    }

    return $obj;
}

function query_labels_ids () {
    return query(Sql\COLLECTIONS, 0, 0);
}
