<?php
namespace Symbiotatk\Symbtk\Addon\Genbank\Format;

use Symbiotatk\Symbtk\Addon\Genbank\Sql AS Sql;

function country(Object $obj) {
    $country = ($obj->country) ? $obj->country : 'None';

    $arr = [
	$obj->stateProvince,
	$obj->municipality,
	$obj->locality
    ];
    $arr = array_merge(
        array_map(
	    function ($i) { return trim($i, ' ,'); },
            array_filter($arr, function ($i) { 
                return (is_null($i)) 
                    ? false 
                    : ((trim($i) === '')
                        ? false
                        : true); 
            })
	)
    );
    $str = implode(", ", $arr);

    unset($obj->stateProvince);
    unset($obj->municipality);
    unset($obj->locality);

    $obj->country = "$country: $str";

    return $obj;
}

function date(String $str=NULL) {
    if (! $str) { return $str; }
    $date = \date_create($str);
    return \date_format($date, "d-M-Y");
}

function host(String $str=NULL) {
    return ($str) ? str_replace(['host: '], [''], $str) : $str;
}

function identified_by_fix(String $str=NULL) {
    $rep = [
	'\u009a' => "á"
    ];

    $str = json_encode('"'.$str.'"');
    $str = str_replace(array_keys($rep), array_values($rep), $str);
    return trim(json_decode($str), '"');
}

function lat_lon(Object $obj) {
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

function sort_order(Object $obj) {
    $order = Sql\ATTRIBUTE_SET_ORDER;
    $narr = [];
    for($i=0; $i<sizeof($order); $i++) {
	$id = $order[$i];
	$narr[$id] = (property_exists($obj, $id)) ? $obj->$id : null;
    }
    return (object) $narr;
}
