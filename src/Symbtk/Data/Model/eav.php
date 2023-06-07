<?php
namespace Symbiotatk\Symbtk\Data\Model\Eav;

use Symbiotatk\Symbtk\Data AS Data;
use Symbiotatk\Symbtk\Model\Ns AS Ns;
use Symbiotatk\Symbtk\Data\Model\Filterparser AS Filterparser;

include_once (dirname(__FILE__).'/filter_parser.php');

/** [internal] Static attribute list for EAV.
 *  @return Array $attributes
 */
function app_attributes () {
    return ['entity', 'reference_id', 'timestamp', 'status', 'related_id'];
}

/** [internal] Static attribute element prefix
 *  @return String $prefix.
 */
function app_data_prefix () {
    return '_';
}

/** Prefix for table names (Defined in .site.php)
 *  @param String $handler SQLite|mySQL
 *  @return String $prefix
 */
function app_table_name_prefix (String $handler) {
    return eav_const($handler, 'APP_TABLE_PREFIX');
}

/** [internal] Array function
 *  @param Array $array
 *  @param Array $array_to_append
 *  @return Array $array
 */
function appendAttributeValue (Array $newarr, Array $arr) {
    $newarr[$arr['attribute']] = $arr['value'];
    return $newarr;
}

/** [internal] Array function to reset key (app_attributes()) values
 *  @param Array $array
 *  @return Array $array
 */
function clearAppAttributes(Array $data) {
    foreach (app_attributes() as $attr) {
        $key = app_data_prefix().$attr;
        if (isset($data[$key])) {
            unset($data[$key]);
        }
    }
    return $data;
}

/** [internal] Create default tables.
 *  @param \PDO $conn
 *  @return Bool false
 */
function createDefaultTables(\PDO $conn) {
    $handler = Data\driver($conn);
    $hasTable = Data\func($handler, 'hasTable');
    $pre = app_table_name_prefix($handler);
    $tn = uuid_table_name($handler);

    $table_exists = ($hasTable($conn, $pre.$tn))
        ? true
        : false;

    query($conn, 'CREATE_UUID_TABLE');
    if (! $table_exists) {
        query($conn, 'CREATE_UUID_INDEX');
    }
    return false;
}

/** [internal] Create EAV tables
 *  @param \PDO $conn
 *  @param String $table_name Data store identifier (ie. Form)
 *  @return Bool false
 */
function createEavTables (\PDO $conn, String $name) {
    $handler = Data\driver($conn);
    $hasTable = Data\func($handler, 'hasTable');

    createDefaultTables($conn);

    $table_exists = ($hasTable($conn, getFormTableName($handler, $name)))
        ? true
        : false;

    $table_meta_exists = ($hasTable($conn, getFormMetaTableName($handler, $name)))
        ? true
        : false;

    query($conn, 'CREATE_EAV_TABLE', [ ':table' => getFormTableName($handler, $name) ]);
    query($conn, 'CREATE_EAV_TABLE', [ ':table' => getFormMetaTableName($handler, $name) ]);

    if (! $table_exists) {
        query($conn, 'CREATE_EAV_INDEX', [ ':table' => getFormTableName($handler, $name) ]);
    }
    if (! $table_meta_exists) {
        query($conn, 'CREATE_EAV_INDEX', [ ':table' => getFormMetaTableName($handler, $name) ]);
    }

    return false;
}

/** Delete entity by id.
 *  @param \PDO $conn
 *  @param Int $id
 *  @return Bool $status true|false
 */
function deleteEntityById(\PDO $conn, Int $id) {
    $did = newEntityId($conn, $id);
    setEntityArchived($conn, $id);
    setEntityArchived($conn, $did, true);

    if (isEntityDeleted($conn, $did)) {
        return true;
    }
    return false;
}

/** Namespace constants array (ref. Data\ns_path, Model\Ns\constant_array)
 *  @param String $handler SQLite|mySQL
 *  @return Array $constants
 */
function eav_ns_const_array(String $handler) {
    $ns_model = Data\ns_path([ucfirst($handler), "Model", "Eav" ]);
    return Ns\constant_array($ns_model);
}

/** Constants array
 *  @param String $handler SQLite|mySQL
 *  @param String $id Namespace path element
 *  @return Mixed $path
 */
function eav_const(String $handler, String $id=null) {
    $lookup = eav_ns_const_array($handler);
    if (! $id) { return $lookup; }
    return (isset($lookup[$id]))
        ? $lookup[$id]
        : false;
}

/** [SQL] Fetch entity element
 *  @param \PDO $conn
 *  @param Int $id Entity id.
 *  @param String $element Entity attribute name.
 *  @return \PDOStatement $result PDOStatment::fetchColumn
 */
function entityElement (\PDO $conn, Int $id, String $ele) {
    $stmt = query($conn, 'SELECT_UUID_ATTRIBUTE_BY_ID', [ ':ele' => $ele ], [ ':id' => $id ]);
    return $stmt->fetchColumn();
}

/** [SQL] Confirm entity exists by id.
 *  @param \PDO $conn
 *  @param Int $entity_id
 *  @return Int $count
 */
function entityExists (\PDO $conn, Int $id) {
    $stmt = query($conn, 'SELECT_UUID_COUNT_BY_ID', [], [ ':id' => $id ]);
    $count = $stmt->fetchColumn();
    return $count;
}

/** [SQL] Get related entity id.
 *  @param \PDO $conn
 *  @param Int $entity_id
 *  @return \PDOStatement $related_entity_id \PDOStatement::fetchColumn
 */
function entityRelatedId (\PDO $conn, Int $id) {
    return entityElement($conn, $id, 'related_id');
}

/** [SQL] Get entity status.
 *  @param \PDO $conn
 *  @param Int $entity_id
 *  @return \PDOStatement $status \PDOStatement::fetchColumn
 */
function entityStatus (\PDO $conn, Int $id) {
    return entityElement($conn, $id, 'status');
}

/** [SQL] Get entity timestamp.
 *  @param \PDO $conn
 *  @param Int $entity_id
 *  @return \PDOStatement $timestamp \PDOStatement::fetchColumn
 */
function entityTimestamp (\PDO $conn, Int $id) {
    return entityElement($conn, $id, 'timestamp');
}

/** [internal] Array format function
 *  @param Array $array
 *  @param String $attribute_name
 *  @return Array $array Array of specified attribute values
 */
function formatAsAttributeValueList (Array $data, String $attribute) {
    $arr = [];
    foreach ($data as $key => $row) {
        if (isset($row[$attribute])) {
            array_push($arr, $row[$attribute]);
        }
    }
    return $arr;
}

/** [internal] Array format append function
 *  @param Array $array
 *  @param Array $attributes Defaults to app_attributes()
 *  @return Array $array
 */
function formatEavAddInfo (Array $data, Array $fields = null) {
    $arr = [];
    if (!$fields) {
        $fields = app_attributes();
    }
    foreach ($fields as $label) {
        if (isset($data[$label])) {
            $arr[app_data_prefix().$label] = $data[$label];
        }
    }
    return $arr;
}

/** [internal] Array format EAV as row function
 *  @param Array $array
 *  @param Int $meta
 *  @return Array $array
 */
function formatEavAsRow (Array $arr, Int $meta) {
    $newarr = [];
    if (count($arr) > 0) {
        $data = $arr[0];
        $meta = ($meta === 0) ? ['entity', 'timestamp'] : null;

        $newarr = formatEavAddInfo($data, $meta);
        foreach ($arr as $attribute) {
            $newarr = appendAttributeValue($newarr, $attribute);
        }
    }
    return $newarr;
}

/** [internal] Array format EAV as rows function
 *  @param Array $array
 *  @param Array $meta
 *  @return Array $array
 */
function formatEavAsRows (Array $arr, Array $meta = null) {
    $newarr = [];
    foreach ($arr as $attr) {
        $id = $attr['entity'];
        if (!isset($newarr[$id])) {
            $newarr[$id] = formatEavAddInfo($attr, ['entity', 'timestamp']);
        }
        $newarr[$id][$attr['attribute']] = $attr['value'];
    }
    if ($meta) {
        $meta['return-count'] = count(array_values($newarr));
        return [
            "data" => array_values($newarr),
            "meta-data" => $meta
        ];
    }
    return array_values($newarr);
}

/** [SQL] Generate statement filter (ie. WHERE)
 *  @param \PDO $conn
 *  @param String $statment
 *  @param String $statement_filter
 *  @return String $statement
 */
function formatSqlFilter (\PDO $conn, String $sql, String $filter) {
    $handler = Data\driver($conn);
    $sql_filter = ($filter)
        ? $filter
        : eav_const($handler, 'STR_BY_LIKE_VALUE_MATCH');

    // !!! if no ':' search all fields
    //
    // if filter; parse string; append to $sql
    // return $sql . " AND t.attribute = 'color' AND t.value LIKE 'bl%'";
    return $sql . $sql_filter;
    // return $sql;
}

/** [SQL] Generate statement limit
 *  @param \PDO $conn
 *  @param String $statment
 *  @param String $statement_limit
 *  @return String $statement
 */
function formatSqlLimit (\PDO $conn, String $sql, String $limit) {
    $handler = Data\driver($conn);
    $sql_limit = eav_const($handler, 'STR_BY_LIMIT');
    $sql_limit_offset = eav_const($handler, 'STR_BY_LIMIT_OFFSET');

    if (ctype_digit($limit)) {
        $limit = str_replace([':limit'],[$limit],$sql_limit);
        return $sql . $limit;
    }
    if (strpos($limit, ',')) {
        $parts = explode(',', $limit);
        if (count($parts) > 1) {
            if ((ctype_digit($parts[0])) && (ctype_digit($parts[1]))) {
                $limit = str_replace([':limit', ':offset'],[$parts[1], $parts[0]],$sql_limit_offset);
                return $sql . $limit;
            }
        }
    }

    return $sql;
}


/** [SQL] Format statement ORDER BY
 *  @param String $statment
 *  @param String $statement_orderby
 *  @return String $statement
 */
function formatSqlOrder(String $sql, String $orderby)
{
    // if orderby; order by value; parse from string; append to $sql
    if ($orderby) {
        return "$sql $orderby";
    }

    return $sql;
}

/** Format meta table name
 *  @param String $handler SQLite|mySQL
 *  @param String $name
 *  @return String $table_name
 */
function getFormMetaTableName(String $handler, String $name) {
    $pre = app_table_name_prefix($handler);
    return $pre.'form_meta_' . $name;
}

/** Format table name
 *  @param String $handler SQLite|mySQL
 *  @param String $name
 *  @return String $table_name
 */
function getFormTableName(String $handler, String $name) {
    $pre = app_table_name_prefix($handler);
    return $pre.'form_' . $name;
}

/** [SQL] Verify entity status as active
 *  @param \PDO $conn
 *  @param Int $id
 *  @return Bool $status true||false
 */
function isEntityActive (\PDO $conn, Int $id) {
    $status  = selectEntityStatus($conn, $id);
    return ($status === 0) ? true : false;
}

/** [SQL] Verify entity status as archived
 *  @param \PDO $conn
 *  @param Int $id
 *  @return Bool $status true||false
 */
function isEntityArchived (\PDO $conn, Int $id) {
    $status  = selectEntityStatus($conn, $id);
    if ($status == 1) {
        return true;
    }
    return false;
}

/** [SQL] Verify entity status as deleted
 *  @param \PDO $conn
 *  @param Int $id
 *  @return Bool $status true||false
 */
function isEntityDeleted (\PDO $conn, Int $id) {
    $status  = selectEntityStatus($conn, $id);
    if ($status == -1) {
        return true;
    }
    return false;
}

/** [SQL] Create a new entity-attribute-value record
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param Int $id
 *  @param String $attribute
 *  @param String $value
 *  @return Bool false
 */
function newEav (\PDO $conn, String $table, Int $id, String $attribute, String $value) {
    query($conn, 'INSERT_EAV',
        [ ':table' => $table ],
        [ ':entity' => $id, ':attribute' => $attribute, ':value' => $value ]);

    return false;
}

/** [SQL] Create a new entity id record
 *  @param \PDO $conn
 *  @param Int $related_id Optional existing id (ie. UPDATE)
 *  @return Bool $entity_id
 */
function newEntityId (\PDO $conn, Int $related_id = null) {
    $handler = query($conn, 'INSERT_UUID_NEW');

    $getLastInsertId = Data\func($handler, "getLastInsertId");
    $id = (integer) $getLastInsertId($conn);

    if ($related_id) {
        $reference_id = selectEntityReferenceId($conn, $related_id);

        query($conn, 'UPDATE_UUID_RELATED_ID_AND_REFERENCE_ID_BY_ID',
            [],
            [':id' => $id, ':related_id' => $related_id, ':reference_id' => $reference_id]);
    } else {
        query($conn, 'UPDATE_UUID_REFERENCE_ID_BY_ID',
            [],
            [':id' => $id, ':reference_id' => $id]);
    }

    return $id;
}

/** [SQL] Execute prepared query statement
 *  @param \PDO $conn
 *  @param String $internal_constant_sql_reference
 *  @param Array $keyvalues
 *  @param Array $prepared_additional
 *  @return String $data_driver
 */
function query (\PDO $conn, String $const, Array $replace = NULL, Array $prepare = NULL) {
    $handler = Data\driver($conn);
    $stmt = query_prepare($conn, $const);

    if ($replace) {
        $stmt = str_replace(array_keys($replace), array_values($replace), $stmt);
    }

    if (! $prepare) {
        $exec = Data\func($handler, 'exec');
        $exec($conn, $stmt);
    } else {
        $query = $conn->prepare($stmt);
        $query->execute($prepare);
        return $query;
    }

    return $handler;
}

/** [SQL] [internal] Insert correct table names, and main :uuid_table values
 *  @param \PDO $conn
 *  @param String $internal_constant_sql_reference
 *  @return \PDOStatement $stmt
 */
function query_prepare (\PDO $conn, String $const) {
    $handler = Data\driver($conn);
    $pre = app_table_name_prefix($handler);
    $tn = uuid_table_name($handler);
    $stmt = eav_const($handler, $const);
    if (strpos($stmt, ':uuid_table')) {
        $stmt = str_replace([':uuid_table'], [$pre.$tn], $stmt);
    }
    return $stmt;
}

/** [SQL] Fetch all rows
 *  @param \PDOStatement $stmt
 *  @return Array $array
 */
function queryFetchAllRows (\PDOStatement $stmt) {
    $rows = [];

    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        array_push($rows, $row);
    }
    return $rows;
}

/** [SQL] SELECT all records
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param String $filter
 *  @param String $orderby
 *  @param String $limit
 *  @param Bool $meta If TRUE, return row COUNT
 *  @return Array $rows
 */
function selectAll (\PDO $conn, String $table, String $filter = null, String $orderby = null, String $limit = null, bool $meta = null) {
    $handler = Data\driver($conn);

    $log = function (String $msg) {
        error_log(__LINE__.": ".basename(__FILE__)." :: ".$msg."\n", 3, '/tmp/php-Database.log');
    };

    if (!$orderby) {
        $orderby = eav_const($handler, 'STR_BY_ORDER_BY_REFERENCE_ID_ASC');
    }

    if ($meta) {
        $total = selectAllCount($table);
    }

    $filter_sql = Filterparser\SQL([
        'prefix' => 't',
        'filter' => $filter
    ]);
    $where = $filter_sql->where;
    $select_fields = ($filter_sql->select_fields) ? " AND ".$filter_sql->select_fields : '';

    $sql_sub = query_prepare($conn, 'SELECT_UUID_DISTINCT_ID_JOIN_EAV_WHERE');
    $sql_sub = str_replace([':table'], [$table], $sql_sub);
    $sql_sub = str_replace([':where'], [$where], $sql_sub);


    /*
    $sql_sub = "SELECT distinct(u.id) AS id FROM app_uuid u JOIN $table t ON u.id = t.entity WHERE u.status = 0";

    if ($filter) {
        $sql_sub = $this->formatSqlFilter($sql_sub, $filter);
    }
     */

    if ($limit) {
        $sql_sub = formatSqlLimit($sql_sub, $limit);
    }

    $sql = query_prepare($conn, 'SELECT_EAV_BY_ENTITY_ID_AND_ATTRIBUTE_VALUES');
    $sql = str_replace([':table'], [$table], $sql);
    $sql = str_replace([':sub_select_entities',':fieldvalue'], [$sql_sub, $select_fields], $sql);

    if ($orderby) {
        $sql = formatSqlOrder($sql, $orderby);
    }
    $log($sql);

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($meta) {
        $meta = [
        "total" => $total,
        "filter" => $filter,
        "orderby" => $orderby,
        "limited" => $limit
        ];
    }

    $result = formatEavAsRows(queryFetchAllRows($stmt), $meta);
    return $result;
}

/** [SQL] Return record count
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param String $filter
 *  @return \PDOStatement $count \PDOStatement::fetchColumn
 */
function selectAllCount (\PDO $conn, String $table, String $filter = null) {
    // if $filter; 'and_where' total filtered returns
    $stmt = query($conn, 'SELECT_COUNT_ALL_EAV_CURRENT',
        [ ':table' => $table, ':and_where' => '' ],
        []);
    return $stmt->fetchColumn();
}

/** [SQL] Return records filtered by attribute keyword
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param String $attribute
 *  @param String $keyword
 *  @return Array $rows
 */
function selectByAttributeKeyword (\PDO $conn, String $table, String $attribute, String $keyword) {
    $sql = query_prepare($conn, 'SELECT_EAV_BY_ATTRIBUTE_NAME_AND_ATTRIBUTE_MATCH_CURRENT');
    $sql = str_replace([':table'], [$table], $sql);
    $sql_like = query_prepare($conn, 'STR_LIKE');

    $eval = '=';
    if (strpos($keyword, '*') !== false) {
        $eval = $sql_like;
        $keyword = str_replace('*', '%', $keyword);
    }
    $sql = str_replace([':eval'], [$eval], $sql);

    $stmt = $conn->prepare($sql);
    $stmt->execute([':attribute' => $attribute, ':keyword' => $keyword]);

    return formatEavAsRows(queryFetchAllRows($stmt));
}

/** [SQL] Return list of distinct values by attribute
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param String $attribute
 *  @return Array $values
 */
function selectDistinctAttributeValues (\PDO $conn, String $table, String $attribute) {
    $stmt = query($conn, 'SELECT_DISTINCT_ATTRIBUTE_VALUES_BY_ATTRIBUTE_NAME_CURRENT',
        [ ':table' => $table, ':alias_attribute' => $attribute ],
        [ ':value_attribute' => $attribute ]
    );

    return formatAsAttributeValueList(queryFetchAllRows($stmt), $attribute);
}

/** [SQL] Return entity_id by reference_id
 *  @param \PDO $conn
 *  @param Int $reference_id
 *  @param Int $status
 *  @return \PDOStatement $reference_id
 */
function selectEntityByReferenceId (\PDO $conn, Int $id, Int $status=0) {
    $stmt = query($conn, 'SELECT_UUID_BY_REFERENCE_ID_AND_STATUS',
        [ ],
        [ ':reference_id' => $id, ':status' => $status ]
    );

    return $stmt->fetchColumn();
}

/** [SQL] Return reference_id by entity_id
 *  @param \PDO $conn
 *  @param Int $id
 *  @return \PDOStatement $entity_id
 */
function selectEntityReferenceId (\PDO $conn, Int $id) {
    $stmt = query($conn, 'SELECT_UUID_REFERENCE_ID_BY_ID',
        [ ],
        [ ':id' => $id ]
    );

    return $stmt->fetchColumn();
}

/** [SQL] Return status by entity_id
 *  @param \PDO $conn
 *  @param Int $id
 *  @return \PDOStatement $entity_id
 */
function selectEntityStatus (\PDO $conn, Int $id) {
    $stmt = query($conn, 'SELECT_UUID_STATUS_BY_ID',
        [ ],
        [ ':id' => $id ]
    );

    return $stmt->fetchColumn();
}

/** [SQL] Return record by entity_id
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param Int $entity_id
 *  @param Int $meta
 *  @return Array $row
 */
function selectRowById (\PDO $conn, String $table, Int $id, Int $meta=0) {
    $stmt = query($conn, 'SELECT_EAV_BY_ENTITY_ID',
        [ ':table' => $table ],
        [ ':id' => $id ]
    );

    return formatEavAsRow(queryFetchAllRows($stmt), $meta);
}

/** [SQL] Archive entity by entity_id.
 *  @param \PDO $conn
 *  @param Int $entity_id
 *  @param Int $deleted
 *  @return Bool true
 */
function setEntityArchived (\PDO $conn, Int $id, Int $deleted = null) {
    $sql = query_prepare($conn, 'UPDATE_UUID_SET_ENTITY_ARCHIVED_BY_ID');

    $status = 1;
    if ($deleted) {
        $status = -1;
    }
    $sql = str_replace([':status'], [$status], $sql);

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    return true;
}

/** [SQL] Update entity by entity_id.
 *  @param \PDO $conn
 *  @param String $table_name
 *  @param Array $data Must contain $data['_entity']
 *  @return Mixed $false_or_reference_id
 */
function updateRow (\PDO $conn, String $table, Array $data) {
    if (isset($data['_entity'])) {
        $id = selectEntityByReferenceId($conn, $data['_entity']);
        setEntityArchived($conn, $id);
        $entity = newEntityId($conn, $id);
        $data = clearAppAttributes($data);
        foreach ($data as $key => $value) {
            newEav($conn, $table, $entity, $key, $value);
        }
        return selectEntityReferenceId($conn, $entity);
    }
    return false;
}

/** [internal] Static uuid table name
 *  @param String $handler SQLite|mySQL
 *  @return String $app_constant_uuid_table_name
 */
function uuid_table_name (String $handler) {
    return eav_const($handler, 'UUID_TABLE_NAME');
}
