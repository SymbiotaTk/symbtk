<?php
/**
 * General interface to Symbiota symbini values, dbconnection information, and SQL queries.
 */
namespace Symbiotatk\Symbtk\Model\Symbiota;

use \Symbiotatk\Symbtk\Env AS Env;
use \Symbiotatk\Symbtk\Env\Model\Http AS Http;

function Run (String $callback) {
    $def = (object) isInstance();
    return ($def->error
        || false
    )
    ? (array) $def
    : $callback($def);
}

/**
 * Is this a valid Symbiota portal.
 * @return Object $attributes userCollectionData|$attribute->error=true
 */
function isInstance () {
    $portal_dir = portal_dir();
    $pos = (object) [
        'error' => false,
        'message' => false,
        'base' => $portal_dir,
        'symbini' => symbini(),
        'parent_url' => dirname(Env\param('offset')),
        'parent_text' => 'Return to portal',
        'conn' => mysqlInfo(),
        'config_dir_exists' => is_dir($portal_dir.'/config'),
        'class_dir_exists' => is_dir($portal_dir.'/classes')
    ];

    if (! $pos->config_dir_exists) {
        $pos->error = true;
        $pos->message = 'symbini.php not found. This requires a Symbiota portal to reference.';
    }

    return ($pos->symbini) ? userCollectionsData($pos) : $pos;
}

/**
 * Collection data available to current user.
 * @param Object $attributes
 * @return Object $attributes|error_login
 */
function userCollectionsData(Object $env) {
    $env->error = false;
    $env->collections = setCollectionList($env);
    $env->user = userObjectFormat(getUserProfile($env));
    if (! $env->user) {
        $env->error = true;
        $env->message = 'Please return to the portal and login.';
    }
    return $env;
}

/**
 * Format current user attributes.
 * @param Mixed $obj
 * @return Object $fobj|false
 */
function userObjectFormat ($obj=NULL) {
    if (! $obj) { return false; }
    $fobj = (object) [];
    $fobj->uid = $obj->getUid();
    $fobj->username = $obj->getUserName();
    $fobj->firstname = $obj->getFirstName();
    $fobj->lastname = $obj->getLastName();
    $fobj->title = $obj->getTitle();
    $fobj->institution = $obj->getInstitution();
    // $fobj->department = $obj->getDepartment();
    $fobj->city = $obj->getCity();
    $fobj->state = $obj->getState();
    $fobj->country = $obj->getCountry();
    $fobj->zip = $obj->getZip();
    // $fobj->phone = $obj->getPhone();
    $fobj->email = $obj->getEmail();
    // $fobj->guid = $obj->getGUID();
    $fobj->lastlogindate = $obj->getLastLoginDate();
    $fobj->usertaxonomy = $obj->getUserTaxonomy();

    return $fobj;
}

/**
 * Get current user attributes.
 * @param $env
 * @return Object $sql_fetch_object()|false
 */
function getUserProfile(Object $env) {
    global $SYMB_UID;
    $SYMB_UID = (property_exists($env->symbini, 'SYMB_UID')) ? $env->symbini->SYMB_UID : null;

    if ($SYMB_UID) {
        symb_classes();
        $pm = new \ProfileManager();
        $pm->setUid($SYMB_UID);
        return $pm->getPerson();
    }
    return false;
    
/*
    $sqlStr =<<<EndOfString
        SELECT
            u.uid, u.firstname, u.lastname, u.title,
            u.institution, u.department, u.address, u.city,
            u.state, u.zip, u.country, u.phone, u.email,
            u.url, u.guid, u.biography, u.ispublic, u.notes,
            ul.username, ul.lastlogindate
        FROM users u
            LEFT JOIN userlogin ul ON u.uid = ul.uid
        WHERE (u.uid = $SYMB_UID);
EndOfString;

    $result = $env->conn->query($sqlStr);

    return ($result) ? $result->fetch_object() : false;
*/
}

/**
 * Get use collection list.
 * @param $env
 * @return Object $colllist|false
 */
function setCollectionList(Object $env) {
    global $ADMIN_EMAIL, $CHARSET, $CLIENT_ROOT, $DEFAULT_TITLE, $IS_ADMIN, $LANG_TAG, $SERVER_ROOT, $SYMB_UID, $TEMP_DIR_ROOT, $USER_RIGHTS; 

    $ADMIN_EMAIL = (property_exists($env->symbini, 'ADMIN_EMAIL')) ? $env->symbini->ADMIN_EMAIL : null;
    $CHARSET = (property_exists($env->symbini, 'CHARSET')) ? $env->symbini->CHARSET : null;
    $CLIENT_ROOT = (property_exists($env->symbini, 'CLIENT_ROOT')) ? $env->symbini->CLIENT_ROOT : null;
    $DEFAULT_TITLE = (property_exists($env->symbini, 'DEFAULT_TITLE')) ? $env->symbini->DEFAULT_TITLE : null;
    $IS_ADMIN = (property_exists($env->symbini, 'IS_ADMIN')) ? $env->symbini->IS_ADMIN : null;
    $LANG_TAG = (property_exists($env->symbini, 'LANG_TAG')) ? $env->symbini->LANG_TAG : null;
    $SERVER_ROOT = (property_exists($env->symbini, 'SERVER_ROOT')) ? $env->symbini->SERVER_ROOT : null;
    $SYMB_UID = (property_exists($env->symbini, 'SYMB_UID')) ? $env->symbini->SYMB_UID : null;
    $TEMP_DIR_ROOT = (property_exists($env->symbini, 'TEMP_DIR_ROOT')) ? $env->symbini->TEMP_DIR_ROOT : null;
    $USER_RIGHTS = (property_exists($env->symbini, 'USER_RIGHTS')) ? $env->symbini->USER_RIGHTS : null;

    symb_classes();
    $smManager = new \SiteMapManager();
    $smManager->setCollectionList();
    return ($collList = $smManager->getCollArr()) ? $collList : false;
}

/**
 * Read symbini.php and collect subset of environment variables.
 * @return Object $env
 */
function symbini () {
    $portal_dir = portal_dir();
    $file = $portal_dir."/config/symbini.php";
    $result = (is_file($file)) ? include_once($file) : false;
    $env = ($result) ? (object) [
        'ADMIN_EMAIL' => $ADMIN_EMAIL,
        'CHARSET' => $CHARSET,
        'CLIENT_ROOT' => $CLIENT_ROOT,
        'DEFAULT_TITLE' => $DEFAULT_TITLE,
        'IS_ADMIN' => $IS_ADMIN,
        'LANG_TAG' => $LANG_TAG,
        'USER_RIGHTS' => $USER_RIGHTS,
        'SERVER_ROOT' => $SERVER_ROOT,
        'SYMB_UID' => $SYMB_UID,
        'TEMP_DIR_ROOT' => $TEMP_DIR_ROOT
    ] : false;
    return $env;
}

/**
 * dbconnection READ ONLY
 * @return SYMB::MySQLiConnectionFactory $conn|false
 */
function mysqlReadOnlyConn() {
    $conn = new \MySQLiConnectionFactory;
    return ($conn) ? $conn->getCon('readonly') : false;
}

/**
 * Read dbconnection.php and return MySQLi object if found.
 * @return SYMB::MySQLiConnectionFactory|false
 */
function mysqlInfo () {
    $portal_dir = portal_dir();
    $file = $portal_dir."/config/dbconnection.php";
    $conn = (is_file($file)) ? include_once($file) : false;
    return ($conn) ? mysqlReadOnlyConn() : false;
}

/**
 * Load require Symbiota classes (DwcArchiverCore,ProfileManager,SiteMapManager)
 * @return Bool
 */
function symb_classes () {
    global $SERVER_ROOT, $LANG_TAG;

    $success = false;
    $libs = [
        'DwcArchiverCore',
        'ProfileManager',
        'SiteMapManager'
    ];
    $portal_dir = portal_dir();
    foreach($libs as $lib) {
        $f = $portal_dir."/classes/$lib.php";
        if (is_file($f)) {
            include_once($f);
            $success = true;
        }
    }
    return $success;
}

/**
 * Current user and permissions
 * @return false
 * @todo Unnecessary
 */
function user() {
    // current user and permissions
    return false;
}

/**
 * Backup handler for creating DwCA
 * @param Int $collid
 * @param String $cSet Default 'utf-8'
 * @return SYMB::DwcArchiverCore $dwca
 */
function symbiotaBackupHandler(Int $collid, String $cSet='utf-8') {
    // silence errors
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

    // run backup for selected collection
    global $tempDirRoot, $serverRoot, $TEMP_DIR_ROOT, $SERVER_ROOT, $CLIENT_ROOT, $DEFAULT_TITLE, $ADMIN_EMAIL;
    $tempDirRoot = $TEMP_DIR_ROOT;
    $serverRoot = $SERVER_ROOT;


    $dwcaHandler = new \DwcArchiverCore();
    $dwcaHandler->setSchemaType('backup');
    $dwcaHandler->setCharSetOut($cSet);
    $dwcaHandler->setVerboseMode(0);
    $dwcaHandler->setIncludeDets(1);
    $dwcaHandler->setIncludeImgs(1);
    $dwcaHandler->setIncludeAttributes(1);
    // if($dwcaHandler->hasMaterialSamples()) $dwcaHandler->setIncludeMaterialSample(1);
    $dwcaHandler->setRedactLocalities(0);
    $dwcaHandler->setCollArr($collid);

    return $dwcaHandler->createDwcArchive();
}

/**
 * File system remove backup archive build files.
 * @param String $target Working directory path
 * @return null
 */
function removeFiles(String $target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK );

        foreach( $files as $file ){
            removeFiles( $file );
        }

        (is_dir($target)) ? rmdir( $target ) : false;
    } elseif(is_file($target)) {
        unlink( $target );
    }
}

/**
 * Add build backup files to encrypted archive.
 * @param String $target Directory path
 * @param String $zip Zip archive path
 * @return String $zip Zip archive path
 */
function addFiles($target, $zip) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK );

        foreach( $files as $file ){
            addFiles( $file, $zip );
        }

    } elseif(is_file($target)) {
        $zip->addFile($target, basename($target));
        $zip->setEncryptionName(basename($target), \ZipArchive::EM_AES_256);
    }
    return $zip;
}

/**
 * Create encrypted archive from existing ZIP file.
 * @param $file ZIP archive path
 * @return $file Encrypted ZIP archive path
 */
function encrypt_backup_archive(String $file) {
    $temp_archive = tempnam(backup_dir(), 'tmp_');
    $temp_working_dir = backup_dir().'/'.basename($temp_archive).'__tmpdir';
    $archiveFileEnc = backup_dir().'/'.basename($file, '.zip').'.enc.zip';

    (is_dir($temp_working_dir)) ? removeFiles($temp_working_dir) : mkdir($temp_working_dir);

    $zip = new \ZipArchive();
    $zip->open($file, \ZipArchive::CREATE);
    $zip->extractTo($temp_working_dir);
    $zip->close();

    $zip = new \ZipArchive();
    $zip->open($temp_archive, \ZipArchive::OVERWRITE|\ZipArchive::CREATE);
    $zip->setPassword('@password');
    $zip = addFiles($temp_working_dir, $zip);
    $zip->close();

    rename($temp_archive, $archiveFileEnc);
    chmod($archiveFileEnc, 0755);

    removeFiles($temp_working_dir);
    unlink($file);

    return $archiveFileEnc;
}

/**
 * Create encyrpted collection backup
 * @param Int $collid
 * @return false
 * @todo Remove echo statement
 */
function backup(Int $collid) {

    $cSet = 'utf-8'; // 'iso-8859-1';
    $archiveFile = symbiotaBackupHandler($collid, $cSet);

    $enc = encrypt_backup_archive($archiveFile);
    echo $enc;

    return false;
}

/**
 * Symbiota temp downloads directory
 * @return $path
 */
function backup_dir() {
    // directory for backup file output
    global $TEMP_DIR_ROOT;

    return $TEMP_DIR_ROOT.'/downloads';
}

/**
 * Symbiota collections
 * @return false
 * @todo Unnecessary
 */
function collections() {
    // current user available collections
    return false;
}

/**
 * Establish base portal path
 */
function portal_dir() {
    $path = dirname(Env\Path('root'));
    if (! is_dir("$path/config")) {
        $attr = Http\Attributes();
        $url = dirname($attr->base);
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            die('Not a valid URL');
        }
        $headers = Http\Headers($url);
        if (isset($headers['portal-root'])) {
            $path = $headers['portal-root'];
        }
    }
    return (is_dir($path))
        ? $path
        : false;
}
