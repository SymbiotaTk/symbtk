<?php
namespace Symbiotatk\Symbtk\Data\Mysql\Model\Eav;

const UUID_TABLE_NAME = "uuid";

const APP_TABLE_PREFIX = "symbtk_";

const CREATE_UUID_TABLE =<<<EndOfString
CREATE TABLE IF NOT EXISTS :uuid_table (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    status INTEGER DEFAULT 0,
    reference_id INTEGER DEFAULT 0,
    related_id INTEGER DEFAULT 0,
    timestamp TEXT NOT NULL
);
EndOfString;

const DROP_UUID_TABLE = "DROP TABLE If Exists :uuid_table ;";

const DROP_TABLE = "DROP TABLE If Exists :table ;";

const CREATE_UUID_INDEX = "CREATE INDEX idx_uuid_reference_status ON :uuid_table (id, reference_id, status);";

const CREATE_EAV_INDEX = "CREATE INDEX idx_:table_eav ON :table (entity, attribute);";

const INSERT_UUID_NEW = "INSERT INTO :uuid_table (timestamp) VALUES (NOW())";

const UPDATE_UUID_RELATED_ID_AND_REFERENCE_ID_BY_ID = "UPDATE :uuid_table SET related_id = :related_id, reference_id = :reference_id WHERE id = :id";

const UPDATE_UUID_REFERENCE_ID_BY_ID = "UPDATE :uuid_table SET reference_id = :reference_id WHERE id = :id";

const SELECT_UUID_BY_REFERENCE_ID_AND_STATUS = "SELECT id from :uuid_table WHERE reference_id = :reference_id AND status = :status";

const SELECT_UUID_REFERENCE_ID_BY_ID = "SELECT reference_id from :uuid_table WHERE id = :id";

const SELECT_UUID_STATUS_BY_ID = "SELECT status from :uuid_table WHERE id = :id";

const SELECT_UUID_COUNT_BY_ID = "SELECT count(*) FROM :uuid_table WHERE id = :id";

const SELECT_UUID_ATTRIBUTE_BY_ID = "SELECT :ele FROM :uuid_table WHERE id = :id";

const SELECT_UUID_DISTINCT_ID_JOIN_EAV_WHERE = "SELECT distinct(u.id) AS id FROM :uuid_table u JOIN :table t ON u.id = t.entity :where";

const CREATE_EAV_TABLE =<<<EndOfString
CREATE TABLE IF NOT EXISTS :table (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    entity INTEGER,
    attribute VARCHAR (255),
    value TEXT
);
EndOfString;

const INSERT_EAV = "INSERT INTO :table (entity, attribute, value) VALUES (:entity, :attribute, :value)";

const SELECT_COUNT_ALL_EAV_CURRENT = "SELECT count(distinct(f.entity)) FROM :table f JOIN :uuid_table u ON u.id = f.entity WHERE u.status = 0 :and_where";

const SELECT_EAV_BY_ENTITY_ID_AND_ATTRIBUTE_VALUES = "SELECT u.timestamp, u.status, u.reference_id as entity, u.related_id, f.id, f.entity as `real`, f.attribute, f.value FROM :table f JOIN :uuid_table u ON u.id = f.entity WHERE f.entity IN ( :sub_select_entities ) :fieldvalue";

const SELECT_EAV_BY_ENTITY_ID = "SELECT u.timestamp, u.status, u.reference_id as entity, u.related_id, f.id, f.entity as `real`, f.attribute, f.value FROM :table f JOIN :uuid_table u ON u.id = f.entity WHERE f.entity = :id";

const SELECT_DISTINCT_ATTRIBUTE_VALUES_BY_ATTRIBUTE_NAME_CURRENT = "SELECT distinct(value) as :alias_attribute FROM :table t JOIN :uuid_table u ON u.id = t.entity WHERE u.status = 0 AND t.attribute = :value_attribute ORDER BY value ASC";

const SELECT_EAV_BY_ATTRIBUTE_NAME_AND_ATTRIBUTE_MATCH_CURRENT = "SELECT u.timestamp, u.status, u.reference_id, u.related_id, f.id, f.entity, f.attribute, f.value FROM :table f JOIN :uuid_table u ON u.id = f.entity WHERE u.status = 0 AND f.entity IN ( SELECT entity FROM :table WHERE attribute = :attribute AND value :eval :keyword )";

const UPDATE_UUID_SET_ENTITY_ARCHIVED_BY_ID = "UPDATE :uuid_table SET status = :status WHERE id = :id";

const STR_BY_LIKE_VALUE_MATCH = " AND t.value LIKE '%ee%'";

const STR_BY_AND_VALUE = " AND :fieldvalue";

const STR_BY_LIMIT = " LIMIT :limit";

const STR_BY_LIMIT_OFFSET = " LIMIT :limit OFFSET :offset";

const STR_BY_ATTRIBUTE_IN_ARRAY = ":ele IN ( :array )";

const STR_BY_ORDER_BY_REFERENCE_ID_ASC = "ORDER BY u.reference_id ASC";

const STR_EQUAL = '=';

const STR_LIKE = 'LIKE';

const STR_ASTERISK = '*';

const STR_WILDCARD = '%';
