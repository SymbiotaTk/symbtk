<?php
namespace Symbiotatk\Symbtk\Addon\Genbank\Data;

use \Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;
use \Symbiotatk\Symbtk\Resource AS Resource;

const SQL_ATTRIBUTES_BY_WHERE =<<<EndOfString
SELECT
'%{seqid}' AS sequence_id,
o.occid AS occid,
CONCAT(o.minimumElevationInMeters, ' m') AS altitude,
o.recordedBy AS collected_by,
o.eventDate as collection_date,
o.country AS country,
o.stateProvince AS stateProvince,
o.municipality AS municipality,
o.locality AS locality,
o.associatedTaxa AS host,
o.substrate AS isolation_source,
o.identifiedBy AS identified_by,
o.decimalLatitude AS latitude,
o.decimalLongitude AS longitude,
o.sciname AS organism,
CONCAT(c.InstitutionCode, ':', o.catalogNumber) AS specimen_voucher
FROM omoccurrences o
JOIN omcollections c ON o.collid = c.CollID
WHERE %{where}

LIMIT 1
;
EndOfString;

const SQL_WHERE_OCCID = 'o.occid = %{occid}';

const SQL_WHERE_CATALOGNUMBER = 'o.catalogNumber LIKE "%%{catalog_number}%" AND c.institutionCode LIKE "%{institution_code}"';

const SQL_WHERE_COLLID = 'o.catalogNumber LIKE "%%{catalog_number}%" AND c.collid = %{collid}';

const SQL_COLLECTIONS = 'SELECT collid, institutionCode, collectionName FROM omcollections';

// JOIN taxa t ON o.tidinterpreted = t.TID

const ATTRIBUTE_SET_ORDER = [
	"occid",
    "sequence_id",
    "organism",
    "altitude",
	"collected_by",
	"collection_date",
	"country",
	"host",
	"isolation_source",
	"identified_by",
	"lat_lon",
	"specimen_voucher"
    ];

function Symbiota () {
    $error = (object) [
        'error' => true,
        'message' => "This is not an instance of a Symbiota portal."
    ];
    $instance = Symbiota\isInstance();
    return ($instance->error) ? $error : $instance;
}

function collection_collids () {
    return query(SQL_COLLECTIONS, 0, 0);
}

function data_fetch(Object $obj) {
    return (is_collection_query($obj))
        ? data_fetch_collections($obj)
        : data_format($obj->fetch_object());
}

function data_fetch_collections(Object $obj) {
    $obj->collections = (array) [];
    foreach ($obj as $row) {
        array_push($obj->collections, $row);
    }
    return $obj;
}

function data_format(Object $obj=NULL) {
    if (!$obj) { return (object) [ 'message' => 'No record found.' ]; }
    $obj->collection_date = date_format($obj->collection_date);
    $obj->host = host_format($obj->host);
    $obj->identified_by = identified_by_fix($obj->identified_by);
    $obj = country_format($obj);
    $obj = lat_lon_format($obj);
    $obj = sort_order_format($obj);

    return $obj;
}

function country_format(Object $obj) {
    $country = ($obj->country) ? $obj->country : 'None';

    $arr = [
	$obj->stateProvince,
	$obj->municipality,
	$obj->locality
    ];
    $arr = array_merge(
        array_map(
	    function ($i) { return trim($i, ' ,'); },
            array_filter($arr, function ($i) { return (trim($i) === '' || is_null($i)) ? false : true; })
	)
    );
    $str = implode(", ", $arr);

    unset($obj->stateProvince);
    unset($obj->municipality);
    unset($obj->locality);

    $obj->country = "$country: $str";

    return $obj;
}

function date_format(String $str=NULL) {
    if (! $str) { return $str; }
    $date = \date_create($str);
    return \date_format($date, "d-M-Y");
}

function is_collection_query(Object $obj) {
    return ($obj && property_exists($obj, 'field_count') && $obj->field_count == 3) ? true : false;
}

function lat_lon_format(Object $obj) {
    $obj->lat_lon = null;
    $lat = ($obj->latitude) ? $obj->latitude : false;
    $lon = ($obj->longitude) ? $obj->longitude : false;

    if ($lat && $lon) {
        $orda = ($lat < 0) ? 'S' : 'N';
        $ordo = ($lon < 0) ? 'W' : 'E';

    $lat = ($lat < 0) ? $lat * -1 : $lat;
    $lon = ($lon < 0) ? $lon * -1 : $lon;

	$obj->lat_lon = "$lat $orda $lon $ordo";
    }

    unset($obj->latitude);
    unset($obj->longitude);

    return $obj;
}

function identified_by_fix(String $str=NULL) {
    $rep = [
	'\u009a' => "á"
    ];

    $str = json_encode('"'.$str.'"');
    $str = str_replace(array_keys($rep), array_values($rep), $str);
    return trim(json_decode($str), '"');
}

function host_format(String $str=NULL) {
    return ($str) ? str_replace(['host: '], [''], $str) : $str;
}

function sort_order_format(Object $obj) {
    $order = ATTRIBUTE_SET_ORDER;
    $narr = [];
    for($i=0; $i<sizeof($order); $i++) {
	$id = $order[$i];
	$narr[$id] = (property_exists($obj, $id)) ? $obj->$id : null;
    }
    return (object) $narr;
}

function query (String $statement, String $occid, String $seqid) {
    $conn = Symbiota\mysqlInfo();
    $res = $conn->query(query_format($statement, $occid, $seqid));

    return ($res) ? data_fetch($res) : false;
}

function delim_catalog_number() {
    return ',';
}

function query_format_catalog_number(String $sql, String $occid, String $seqid) {
    $parts = explode(delim_catalog_number(), $occid);
    $code = (isset($parts[0])) ? $parts[0] : false;
    $num = (isset($parts[1])) ? $parts[1] : false;

    $code = (! filter_var($code, FILTER_VALIDATE_INT)) ? -1 : $code;

    if ($code && $num) {
        $sql = str_replace(['%{where}'], [SQL_WHERE_COLLID], str_replace([PHP_EOL], [' '], $sql));
        $sql = str_replace(['%{catalog_number}', '%{collid}', '%{seqid}'], [$num, $code, $seqid], str_replace([PHP_EOL], [' '], $sql));
        return $sql;
    }

    return false;
}

function query_format_occid(String $sql, Int $occid, String $seqid) {
    $sql = str_replace(['%{where}'], [SQL_WHERE_OCCID], str_replace([PHP_EOL], [' '], $sql));
    $sql = str_replace(['%{occid}', '%{seqid}'], [$occid, $seqid], str_replace([PHP_EOL], [' '], $sql));
    return $sql;
}

function query_format(String $sql, String $occid, String $seqid) {
    return (strpos($occid, delim_catalog_number()))
	? query_format_catalog_number($sql, $occid, $seqid)
	: query_format_occid($sql, (integer) $occid, $seqid);
}

function get(String $occid=NULL, String $seqid=NULL) {
    $endpoint = Resource\Addon\Url().'/<[occid|institution_code,catalog_number>/<sequence_id>';

    $occid = sanitize($occid);
    $seqid = ($seqid) ? sanitize($seqid) : "ERROR: Please provide a sequence id. [$endpoint]";

    $query_statement = SQL_ATTRIBUTES_BY_WHERE;

    if ($occid == 'Collections') {
        $query_statement = SQL_COLLECTIONS;
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

function sanitize(String $str=NULL) {
    if (! $str) { return $str; }
    $pc = explode(';', $str);
    $ps = explode(' ', $pc[0]);

    return $ps[0];
}
