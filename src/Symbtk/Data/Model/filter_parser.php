<?php

namespace Symbiotatk\Symbtk\Data\Model\Filterparser;

/** Generate SQL filter components (ie. WHERE, <SELECT FIELDS>, LIMIT)
 *  @param Array $attributes
 *  @return Object $filter { WHERE, SELECT_FIELDS }
 */
function SQL (Array $attr) {
    $env = (object) [
        'delim' => ':',
        'sep' => ',',
        'div' => ';',
        'label' => 'filter',
        'term' => 'WHERE',
        'attribute' => 't.attribute',
        'value' => 't.value',
        'fattribute' => 'f.attribute',
        'status' => 'u.status'
    ];

    $str = '';
    $filters = [];
    $fields = [];

    $hasStatus = function (Array $attr) use ($env) {
        $value = 0;
        $label = 'status';

        if (isset($attr[$label]) && (! is_null($attr[$label]))) {
            $value = $attr[$label];
        };

        return "$env->status = ${value}";
    };

    $hasMultiple = function (String $str) {
        $delim = ',';
        if(strpos($str, $delim) === false) { return false; }

        return explode($delim, $str);
    };

    $hasCompare = function (String $pattern, String $compare) {
        $operators = [
            'match' => '=',
            'contain' => 'LIKE',
            'null' => 'IS NULL',
            'notnull' => 'IS NOT NULL',
            'start' => 'LIKE',
            'end' => 'LIKE',
            'equal' => '=',
            'greater' => '>',
            'less' => '>'
        ];
        $numbers = ['equal', 'greater', 'less'];
        if ($compare === 'contain') {
            $pattern = '%'.$pattern.'%';
        }
        elseif ($compare === 'start') {
            $pattern = $pattern.'%';
        }
        elseif ($compare === 'end') {
            $pattern = '%'.$pattern;
        }

        return (object) [
            'pattern' => (in_array($compare, $numbers)) ? $pattern : "'$pattern'",
            'compare' => (isset($operators[$compare])) ? $operators[$compare] : $operators['equal']
        ];
    };

    // ?andor
    $hasAndOr = function (String $andor, String $compare) {
    };

    $hasPattern = function (String $str) use ($env, $hasCompare) {
        $parts = explode($env->div, $str);
        $attribute = (isset($parts[0])) ? $parts[0] : false;
        $pattern = (isset($parts[1])) ? $parts[1] : false;
        $compare = (isset($parts[2])) ? $parts[2] : 'match';
        $andor = (isset($parts[3])) ? ' '.$parts[3] : ' and';
        $associated = (isset($parts[4])) ? $parts[4] : false;

        $pattern = $hasCompare($pattern, $compare)->pattern;
        $compare = $hasCompare($pattern, $compare)->compare;

        return (($attribute) && ($pattern)) ? "( $env->attribute = '$attribute' AND $env->value $compare $pattern ) $andor" : false;
    };

    $hasPatterns = function (String $str) use ($hasMultiple, $hasPattern) {
        $patterns = ($hasMultiple($str)) ? $hasMultiple($str) : [$str];
        $patterns = array_map($hasPattern, $patterns);
        $patterns = preg_replace(['/ and$/','/ or$/'], ['',''], implode(" ", $patterns));
        return $patterns;
    };

    $hasComponents = function (String $str) use ($env, $hasMultiple, $hasPatterns) {
        if(strpos($str, $env->delim) === false) { return false; }
        $parts = explode($env->delim, $str);

        $attributes = (isset($parts[1])) ? $parts[1] : false;
        $attributes = ($attributes !== '*') ? $attributes : false;

        $patterns = (isset($parts[2])) ? $parts[2] : false;

        return (object) [
            'attributes' => ($hasMultiple($attributes)) ? $hasMultiple($attributes) : [ $attributes ],
            'patterns' => $hasPatterns($patterns)
            ];
    };

    $isEmpty = function (String $str) {
        return (trim($str) === '') ? true : false;
    };

    $status = $hasStatus($attr);
    if ($status) {
        array_push($filters, $status);
    }

    if (isset($attr[$env->label]) && (! is_null($attr[$env->label]))) {
        $t1 = $hasComponents($attr[$env->label]);
        if ($t1) {
            $filters = array_merge($filters, [$t1->patterns]);
            $fields = array_merge($fields, $t1->attributes);
        }
    };

    if (sizeof($filters) > 0) {
        $sp = ((sizeof($filters) > 1) && (trim($filters[1]) !== '')) ? ' and ' : ' ';

        $filter_str = implode($sp, $filters);
        $str = $env->term." $filter_str";
    };

    return (object) [
        'where' => $str,
        'select_fields' => ((sizeof($fields) > 0) && (isset($fields[0])) && (trim($fields[0]) !== '')) ? "$env->fattribute IN ('".implode("', '",$fields)."')" : null
    ];
}
