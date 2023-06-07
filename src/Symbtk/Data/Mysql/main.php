<?php
namespace Symbiotatk\Symbtk\Data\Mysql;

const PROTOCOL = 'mysql:';
const HOST = 'localhost';
const PORT = 3306;
const DBNAME = 'test';
const UNIX_SOCKET = false;   // '/tmp/mysql.sock'
const CHARSET = 'utf8mb4';

/** Close connection.
 *  @param \PDO $conn
 *  @return Bool false
 */
function close (\PDO $conn) {
    $conn = null;
    return false;
}

/** Create tables
 *  @param String $dsn
 *  @param Array $commands
 *  @return Bool false
 */
function createTables(String $dsn, Array $commands = null) {
    $tables = ($commands) ? $commands : getDefaultSchema();
    $conn = open($dsn);
    if (ping($conn)) {
        foreach ($tables as $command) {
            $conn->exec($command);
        }
    }
    $conn = close($conn);
    return false;
}

/** Default PDO options
 *  @return Array $options
 */
function default_options () {
    return array(
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    );
}

/** Validate DSN connection string
 *  @param String $dsn
 *  @return Bool false
 */
function dsn (String $str) {
    $valid = is_dsn_string($str);
    if ($valid) {
        $valid->dsn;
    }
    return false;
}

/** Execute SQL
 *  @param \PDO $conn
 *  @param String $stmt
 *  @return Bool false;
 */
function exec (\PDO $conn, String $stmt) {
    $conn->exec($stmt);
    return false;
}

/** Return name of connected database
 *  @param \PDO $conn
 *  @return String $dbname
 */
function getConnectedDatabase(\PDO $conn) {
    return $conn->query('SELECT DATABASE()')->fetchColumn();
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
    $db = getConnectedDatabase($conn);
    $colname = sprintf("Tables_in_%s", $db);
    $query = $conn->query("SHOW TABLES;");
    $tables = [];
    while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
        $tables[] = $row[$colname];
    }
    return $tables;
}

/** Verify existing table
 *  @param \PDO $conn
 *  @param String $table_name
 *  @return Bool true||false
 */
function hasTable (\PDO $conn, String $name) {
    $db = getConnectedDatabase($conn);
    $stmt = "SELECT COUNT(TABLE_NAME) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '%s' AND TABLE_TYPE LIKE 'BASE TABLE' AND TABLE_NAME = '%s';";

    $res = $conn->query(sprintf($stmt, $db, $name))->fetchColumn();

    return ($res === 1) ? true : false;
}

/** Validate DSN connection string; return meta object
 *  @param String $dsn
 *  @return Mixed false || Object [ 'dsn', 'host', 'port', 'dbname', 'unix_socket', 'charset', 'username', 'password' ]
 */
function is_dsn_string (String $str) {
    $user = false;
    $pass = false;
    $charset = CHARSET;
    $port = PORT;
    $dbname = DBNAME;
    $host = HOST;
    $unix_socket = UNIX_SOCKET;

    if (strtolower(substr($str, 0, strlen(PROTOCOL))) !== PROTOCOL) {
        return false;
    }
    $opt = substr($str, strlen(PROTOCOL));

    foreach (explode(';', $opt) as $pair) {
        $kv = explode('=', $pair);
        if (isset($kv[0]) && isset($kv[1])) {
            switch ($kv[0]) {
                case 'charset':
                    $charset = $kv[1];
                    break;
                case 'port':
                    $port = $kv[1];
                    break;
                case 'dbname':
                    $dbname = $kv[1];
                    break;
                case 'unix_socket':
                    $unix_socket = $kv[1];
                    break;
                case 'host':
                    $val = $kv[1];
                    $up = (strpos($val, '@'))
                        ? explode('@', $val) : false;
                    $user = ($up
                        && isset($up[0])
                        && isset(explode(':', $up[0])[0]))
                        ? explode(':', $up[0])[0] : false;
                    $pass = ($up
                        && isset($up[0])
                        && isset(explode(':', $up[0])[1]))
                        ? explode(':', $up[0])[1] : false;

                    $host = ($up && isset($up[1]))
                        ? $up[1]
                        : $val;
                    break;
                default:
                    break;
            }
        }
    }

    $format = PROTOCOL.'host=%s;%s=%s;dbname=%s;charset=%s';

    $dsn = ($unix_socket)
        ? sprintf($format, $host, 'unix_socket', $unix_socket, $dbname, $charset)
        : sprintf($format, $host, 'port', $port, $dbname, $charset);

    return (object) [
        'dsn' => $dsn,
        'host' => $host,
        'port' => ($port) ? (int) $port : false,
        'dbname' => $dbname,
        'unix_socket' => ($port) ? false : $unix_socket,
        'charset' => $charset,
        'username' => $user,
        'password' => $pass
    ];
}

/** Open connection to database
 *  @param String $dsn
 *  @param Array $options
 *  @return Mixed $errorMessage || \PDO $object
 */
function open (String $dsn, Array $options = null) {
    $opt = ($options) ? $options : default_options();
    $obj = is_dsn_string($dsn);
    if ($obj) {
        try {
        return new \PDO($obj->dsn, $obj->username, $obj->password, $opt);
        } catch (\PDOException $e)
        {
            return $e->getMessage();
        }
    }
    return "Not a valid DSN string: $dsn";
}

/** Verify PDO connection
 *  @param \PDO $conn
 *  @return Bool true || false
 */
function ping (\PDO $conn) {
    return ($conn instanceof \PDO) ? true : false;
}

/** Execute query and return result
 *  @param \PDO $conn
 *  @param String $statement
 *  @return Array $rows
 */
function query (\PDO $conn, String $stmt) {
    $query = $conn->query($stmt);
    $res = [];
    while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
        array_push($res, $row);
    }

    return $res;
}
