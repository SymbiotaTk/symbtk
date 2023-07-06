<?php
namespace Symbiotatk\Symbtk\Addon\Fdex\Sql;

const SELECT_COLLECTION =<<<EndOfString
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

const COLLECTIONS = 'SELECT collid, institutionCode, collectionName FROM omcollections';

const DB_EXISTS_FDEX = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = "fdex"';

const SELECT_FDEX = 'SELECT taxon FROM fdex.fdex WHERE taxon = TRIM(sciname)';
