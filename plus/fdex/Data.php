<?php
namespace Symbiotatk\Symbtk\Addon\Fdex\Data;

use \Symbiotatk\Symbtk\Model\Symbiota AS Symbiota;
use \Symbiotatk\Symbtk\Resource AS Resource;

const SQL_SELECT_COLLECTION =<<<EndOfString
select CollID, CollectionName, `InstitutionCode`, CollectionCode,
       fulldescription, contactJson, CollType, ManagementType,
       dwcaUrl, initialTimestamp,
       (SELECT max(distinct(dateLastModified)) FROM omoccurrences
          WHERE collid = %{collid}) AS dateLastModified,
       (SELECT count(occid) FROM omoccurrences
          WHERE collid = %{collid}) AS totalRecords,
       (SELECT count(occid) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
       ) AS totalRecordsTaxonIdentified,
       (SELECT count(distinct(tidinterpreted)) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
       ) AS totalUniqueTaxonIdentified,
       (SELECT count(occid) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
       ) AS totalRecordsTaxonNotIdentified,
       (SELECT count(distinct(SciName)) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
       ) AS totalUniqueTaxonNotIdentified,
       (SELECT count(distinct(SciName)) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
          AND sciname IN (SELECT SciName FROM taxa
              WHERE SciName = TRIM(sciname))
       ) AS totalUniqueTaxonNotIdentifiedExistsInThesaurus,
       (SELECT count(distinct(SciName)) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
          AND sciname IN (%{fdex})
       ) AS totalUniqueTaxonNotIdentifiedExistsInFdex,
       (SELECT count(distinct(SciName)) FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
          AND sciname NOT IN (%{fdex})
          AND sciname NOT IN (SELECT SciName FROM taxa
              WHERE SciName = TRIM(sciname))
          AND (TRIM(sciname) != ''
                  OR sciname != 'NULL'
                  OR sciname IS NOT NULL)
       ) AS totalUniqueTaxonNotIdentifiedUnrecognizedNotNULL,
       (SELECT count(occid) FROM omoccurrences
          WHERE collid = %{collid}
          AND (TRIM(sciname) = ''
                  OR sciname = 'NULL'
                  OR sciname IS NULL)
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
       ) AS totalRecordsTaxonNULL,
       (SELECT CONCAT( '["', result, '"]')
       FROM (
       SELECT GROUP_CONCAT(distinct(SciName) SEPARATOR '","')
        as result
        FROM omoccurrences
          WHERE collid = %{collid}
          AND tidinterpreted NOT IN (SELECT TID FROM taxa
              WHERE TID = tidinterpreted)
          AND sciname IN (%{fdex})
          ORDER BY sciname ASC
          ) t2
       ) AS NewTaxaFromFdex
from omcollections WHERE collid = %{collid};
EndOfString;

const SQL_COLLECTIONS = 'SELECT collid, institutionCode, collectionName FROM omcollections';

const SQL_DB_EXISTS_FDEX = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = "fdex"';

const SQL_SELECT_FDEX = 'SELECT taxon FROM fdex.fdex WHERE taxon = TRIM(sciname)';

function collection_collids () {
    $stmt = SQL_COLLECTIONS;
    return query($stmt);
}

function data_fetch(Object $obj, String $id=NULL) {
    return ($id)
        ? data_format($obj)
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

function data_format(Object $obj=NULL) {
    if (!$obj) { return (object) [ 'message' => 'No record found.' ]; }
    $label = 'result';
    $obj->$label = $obj->fetch_object();

    return $obj;
}

function fdex_exists() {
    $stmt = SQL_DB_EXISTS_FDEX;
    $res = query($stmt);

    return (property_exists($res, 'num_rows')
        && $res->num_rows == 1)
        ? true
        : false;
}

function get (String $id) {
    $collid = sanitize($id);
    $stmt = str_replace(['%{collid}'],[$collid], SQL_SELECT_COLLECTION);
    $stmt = (fdex_exists())
        ? str_replace(['%{fdex}'],[SQL_SELECT_FDEX], $stmt)
        : str_replace(['%{fdex}'],['NULL'], $stmt);

    return query($stmt, $id);
}

function query (String $statement, String $id=NULL) {
    $conn = Symbiota\mysqlInfo();
    $res = $conn->query($statement);
    $res->__stmt__ = $statement;

    return ($res) ? data_fetch($res, $id) : false;
}

function query_format(String $sql, String $collid) {
    return (strpos($occid, delim_catalog_number()))
	? query_format_catalog_number($sql, $occid, $seqid)
	: query_format_occid($sql, (integer) $occid, $seqid);
}

function sanitize(String $str=NULL) {
    if (! $str) { return $str; }
    $pc = explode(';', $str);
    $ps = explode(' ', $pc[0]);

    return $ps[0];
}
