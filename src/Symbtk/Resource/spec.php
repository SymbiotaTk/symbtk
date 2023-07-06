<?php

namespace Symbiotatk\Symbtk\Resource\Spec;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\File\Model\Yaml AS Yaml;

const RC_DIR = '.rc';

/** [testing] Check for enabled constant
 *  @param String $property_name
 *  @return Bool false || true
 */
function Enabled(String $name) {
    $root = Env\Path('root');
    $rc = $root.'/.testrc';
    if (is_dir(dirname($rc))
        && is_file($rc)
    ) {
        $c = Yaml\decode(file_get_contents($rc));

        return (property_exists($c, $name)
            && $c->$name == 'true'
        ) ? true : false;
    }

    return false;
}

/** [testing] Set alternate rc directory
 *  @return false;
 */
function SET_ALTERNATE_RC_DIR() {
    $dir = tempnam(Main\app_path(), RC_DIR);
    unlink($dir);
    mkdir($dir);
    Main\Set(Main\ALTERNATE_RC_DIR, $dir);
    return false;
}

/** [testing] Unset alternate rc directory
 *  @return false;
 */
function UNSET_ALTERNATE_RC_DIR() {
    $dir = Main\rc_directory_alt();
    if ($dir) {
        File\rmdir($dir);
        Main\Clear(Main\ALTERNATE_RC_DIR);
    }
    return false;
}
