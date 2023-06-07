<?php
namespace Symbiotatk\Symbtk\Resource\Addon;

use Symbiotatk\Symbtk\Env AS Env;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;
use Symbiotatk\Symbtk\Resource AS Resource;

/** Return addon path.
 *  @return String $path Env\Path('root')
 */
function Path () {
    $rcsite = Model\RcSite\Load(Resource\paths(), Resource\Related\path_info());
    $dir = $rcsite->AddonDir;
    $res = Env\Param('resource');
    $path = File\mkpath($dir, $res);
    return (is_dir($path)) ? $path : false;
}

/** Return addon url.
 *  @return String $url Env\Param('relative')
 */
function Url () {
    $rcsite = Model\RcSite\Load(Resource\paths(), Resource\Related\path_info());
    $dir = str_replace(Env\Path('root'), '', $rcsite->AddonDir);
    $res = Env\Param('resource');
    return File\mkpath(Env\Param('offset').$dir, $res);
}

/** Return addon id.
 *  @return String $id use addon namespace
 */
function Id () {
    $arr = explode('\\', __NAMESPACE__);
    return array_pop($arr);
}
