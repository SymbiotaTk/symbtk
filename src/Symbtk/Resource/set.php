<?php

namespace Symbiotatk\Symbtk\Resource\Set;

/** Defined callback parameter from rc files (ref. \Env).
 *  @param Object $settings
 *  @return Array $callbacks
 */
function Callback (Object $obj) {
    $callback = (property_exists($obj, 'callback'))
        ? (is_string($obj->callback))
            ? [ $obj->callback ]
            : $obj->callback
        : [];
    $local_callback = (property_exists($obj, 'local')
        && property_exists((object) $obj->local, 'callback'))
        ? (is_string($obj->local['callback']))
            ? [ $obj->local['callback'] ]
            : $obj->local['callback']
        : [];

    return array_merge($callback, $local_callback);
}

/** Set resource controller property
 *  @param Object $obj From paths()
 *  @return Object $obj
 */
function Controller (Object $obj) {
    $obj->controller = [
        'possible' => false,
        'enabled' => false
    ];
    return $obj;
}

/** Defined required parameter from rc files (ref. \Env).
 *  @param Object $settings
 *  @return Array $required
 */
function Required (Object $obj) {
    $required = (property_exists($obj, 'required'))
        ? $obj->required
        : [];
    $local_required = (property_exists($obj, 'local')
        && property_exists((object) $obj->local, 'required'))
        ? $obj->local['required']
        : [];
    return array_merge_recursive($required, $local_required);
}
