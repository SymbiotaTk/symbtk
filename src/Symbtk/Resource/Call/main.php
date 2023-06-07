<?php
namespace Symbiotatk\Symbtk\Resource\Call;

/** Return this namespace
 *  @return String __NAMESPACE__
 */
function ns() {
    return __NAMESPACE__;
}

/** [testing] Example function
 *  @return String $test
 */
function general_run () {
    return 'Data from '.__NAMESPACE__.'\\'.__FUNCTION__;
}

/** [testing] Example function
 *  @return String $test
 */
function local_test_ads () {
    return false;
}
