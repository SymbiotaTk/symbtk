<?php
namespace Symbiotatk\Symbtk\Addon\Genbank\Sql;

const ATTRIBUTES_BY_WHERE =<<<EndOfString
SELECT
'%{seqid}' AS sequence_id,
o.occid AS occid,
CONCAT(o.minimumElevationInMeters, ' m') AS altitude,
o.recordedBy AS collected_by,
o.eventDate as collection_date,
o.county AS county,
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

const WHERE_OCCID = 'o.occid = %{occid}';

const WHERE_CATALOGNUMBER = 'o.catalogNumber LIKE "%%{catalog_number}%" AND c.institutionCode LIKE "%{institution_code}"';

const WHERE_COLLID = 'o.catalogNumber LIKE "%%{catalog_number}%" AND c.collid = %{collid}';

const COLLECTIONS = 'SELECT collid, institutionCode, collectionName FROM omcollections';

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
