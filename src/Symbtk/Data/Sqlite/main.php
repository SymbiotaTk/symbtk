<?php
namespace Symbiotatk\Symbtk\Data\Sqlite;

const IS_INT = 1;
const IS_STR = 2;
const ASSOC  = 1;
const NUM    = 2;
const BOTH   = 3;


/** Close connection.
 *  @param \PDO $conn
 *  @return Bool false
 */
function close (\PDO $conn) {
    return false;
}

/** Create tables
 *  @param String $path Path to sqlite file
 *  @param Array $commands
 *  @return Bool false
 */
function createTables(String $path, Array $commands = null) {
    $tables = ($commands) ? $commands : getDefaultSchema();
    $conn = open($path);
    foreach ($tables as $command) {
        $conn->exec($command);
    }
    close($conn);
}

/** Default PDO options
 *  @return Array $options
 */
function default_options () {
    return array(
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
    );
}

/** Format DSN connection string
 *  @param String $dsn
 *  @return String $dsn
 */
function dsn(String $str) {
    return "sqlite:" . $str;
}

/** Execute SQL
 *  @param \PDO $conn
 *  @param String $stmt
 *  @return Bool false;
 */
function exec(\PDO $conn, String $stmt) {
    $conn->exec($stmt);
    return false;
}

/** Return default schema
 *  @return Array $array Empty
 */
function getDefaultSchema () {
    return array();
}

/** Return last inserted id
 *  @param \PDO $conn
 *  @return Int $id
 */
function getLastInsertId(\PDO $conn) {
    return $conn->lastInsertId();
}

/** Return list of tables
 *  @param \PDO $conn
 *  @return Array $table_list
 */
function getTableList(\PDO $conn) {
    $stmt = $conn->query("SELECT name
                               FROM sqlite_master
                               WHERE type = 'table'
                               ORDER BY name");
    $tables = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $tables[] = $row['name'];
    }

    return $tables;
}

/** Verify existing table
 *  @param \PDO $conn
 *  @param String $table_name
 *  @return Bool true||false
 */
function hasTable(\PDO $conn, String $name) {
    return in_array($name, getTableList($conn));
}

/** Verify DSN as in memory db
 *  @param String $dsn
 *  @return Bool false || true
 */
function in_memory_db (String $str) {
    if ($str == ":memory:") {
        return true;
    }
    return false;
}

/** Verify DSN as in memory db or file
 *  @param String $dsn
 *  @return Mixed $dsn_or_error_message
 */
function is_path_or_memory (String $str) {
    if (in_memory_db($str)) {
        return $str;
    }
    if (! is_file($str)) {
        die ("DB error: File does not exist. [$str].");
    }
    return $str;
}

/** Open connection to database
 *  @param String $dsn
 *  @param Array $options
 *  @return Mixed $errorMessage || \PDO $object
 */
function open (String $path_or_memory, Array $options = null) {
    $options = ($options) ? $options : default_options();
    $dsn = dsn(is_path_or_memory($path_or_memory));
    try {
        $pdo = new \PDO($dsn, '', '', $options);
    } catch (\PDOException $e) {
        die ('DB error');
    }
    return $pdo;
}

/** Verify PDO connection
 *  @param \PDO $conn
 *  @return Bool true || false
 */
function ping ($conn) {
    return (!$conn) ? false : true;
}

/** Execute query and return result
 *  @param \PDO $conn
 *  @param String $statement
 *  @return Array $rows
 */
function query(\PDO $conn, String $stmt) {
    $query = $conn->query($stmt);
    $res = [];
    while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
        array_push($res, $row);
    }

    return $res;
}
