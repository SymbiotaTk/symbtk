<?php

namespace Symbiotatk\Symbtk\File\Model\Html;

use Symbiotatk\Symbtk AS Main;
use Symbiotatk\Symbtk\File AS File;
use Symbiotatk\Symbtk\Model AS Model;

/** DOMDocument append a node
 *  @param String $html
 *  @param \DOMElement $node
 *  @param String $parent [ 'head', 'title', 'body', 'id:id_name' ]
 *  @return String $html
 */
function Append(String $html, \DOMElement $node, String $parent) {
     $doc = load_from_string($html);
     $node = $doc->importNode($node, true);
     $docElement = find_doc_element($doc, $parent);
     if ($docElement) {
         $docElement->appendChild($node);
     }

     return doc_to_string($doc);
}

/** DOMDocument create document or node.
 *  @param String $tag
 *  @param Object $attributes
 *  @param Array $children   If nodes are improper, an error will be returned. (ie. &lt;h1&gt; containing another tag)
 *  @return Mixed $html|$node   If no args provided, creates basic document. Returns HTML as string. With args provided, a DOMElement is returned.
 */
function Create(String $tag=NULL, Object $attributes=NULL, Array $children=NULL) {
    if ($tag) {
        return create_tag($tag, $attributes, $children);
    }
    $doc = basic_template();
    $render = doc_to_string($doc);
    return '<!DOCTYPE html>' . "\n" . $render;
}

function Replace(String $html, \DOMElement $node, String $parent) {
     $doc = load_from_string($html);
     $node = $doc->importNode($node, true);
     $docElement = find_doc_element($doc, $parent);
     if ($docElement) {
         $existing = $docElement->childNodes;
         while ($existing->length > 0) {
             $docElement->removeChild($existing->item(0));
         }

         $docElement->appendChild($node);
     }
     return doc_to_string($doc);
}

/** Basic DOMDocument [complete HTML]
 *  @return \DOMDocument $doc
 */
function basic_template() {
    $rcsite = Model\RcSite\Load();

    $doc = new \DOMDocument('1.0','UTF-8');
    $doc->preserveWhiteSpace = false;
    $html = $doc->createElement('html');
    $html->setAttribute('lang', 'en');

    $head = $doc->createElement('head');
    $title = $doc->createElement('title');
    $title->nodeValue = (property_exists($rcsite, 'SiteName'))
        ? $rcsite->SiteName
        : ucfirst(Main\APP_ID);
    $meta = $doc->createElement('meta');
    $meta->setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
    $meta->setAttribute('name', 'viewport');
    $head->appendChild($title);
    $head->appendChild($meta);

    $meta = $doc->createElement('meta');
    $meta->setAttribute('http-equiv', 'Content-Security-Policy');
    $meta->setAttribute('content', "default-src 'self'; img-src https://*; child-src 'none';");
    // $head->appendChild($meta);

    $body = $doc->createElement('body');

    $html->appendChild($head);
    $html->appendChild($body);
    $doc->appendChild($html);

    return $doc;
}

/** Count node children
 *  @param String $html
 *  @param String $parent (html, head, title, body, 'id:id_name')
 *  @return Integer $count
 */
function children_count (String $html, String $parent, Int $index=0) {
    $doc = load_from_string($html);
    $ele = find_doc_element($doc, $parent, $index);

    $no_text_nodes = (in_array($parent, ['html', 'head']))
        ? true
        : false;
    $count = -1;

    if ($ele && property_exists($ele,'childNodes')) {
        $count = 0;
        foreach($ele->childNodes AS $node) {
            if ($no_text_nodes) {
                if (!($node instanceof \DOMText))
                    $count++;
            } else {
                $count++;
            }
        }
    }

    return $count;
}

/** Create document tag node
 *  @param String $tag
 *  @param Object $attributes
 *  @param Array $children
 *  @return \DOMElement $node
 */
function create_tag (String $tag, Object $attributes=NULL, Array $children=NULL) {
    $doc = new \DOMDocument;
    $node = create_tag_attributes($doc->createElement($tag), $attributes);
    $node = create_tag_children($doc, $node, $children);
    return $node;
}

/** Assign attributes to node
 *  @param \DOMElement $node
 *  @param Object $attributes
 *  @return \DOMElement $node
 */
function create_tag_attributes(\DOMElement $node, Object $attributes=NULL) {
    if ($attributes) {
        array_map(function ($k) use ($node, $attributes){
            $node->setAttribute($k, $attributes->$k);
        }, array_keys((array) $attributes));
    }
    return $node;
}

/** Assign children to node
 *  @param \DOMDocument $doc
 *  @param \DOMElement $node
 *  @param Array $children    May contain String and \DOMElement
 *  @return \DOMElement $node
 */
function create_tag_children(\DOMDocument $doc, \DOMElement $node, Array $children=NULL) {
    if ($children) {
        array_map(function ($i) use ($doc, $node, $children) {
            if ($children[$i] instanceof \DOMElement) {
                $child = $doc->importNode($children[$i], true);
                $node->appendChild($child);
            } elseif (is_string($children[$i])) {
                if (Model\String\is_html($children[$i])) {
                    $insert = new \DOMDocument();
                    $insert->loadHTML($children[$i]);
                    $node->appendChild($doc->importNode($insert->documentElement, true));
                } else {
                    $text = $doc->createTextNode($children[$i]);
                    $node->appendChild($text);
                }
            }
        }, array_keys($children));
    }
    return $node;
}

/** \DOMDocument to String
 *  @param \DOMDocument $doc
 *  @return String $html
 */
function doc_to_string(\DOMDocument $doc) {
    $doc->formatOutput = true;
    return $doc->saveHTML();
}

/** Find DOMElement by class name.
 *  @param \DOMDocument $doc
 *  @param String $name  Class name
 *  @param String $tag   [optional] limit by tag name
 *  @return Array $matched
 */
function find_by_class (\DOMDocument $doc, String $name, String $tag=NULL) {
    if($tag){
        $elements = $doc->getElementsByTagName($tag);
    }else {
        $elements = $doc->getElementsByTagName("*");
    }
    $matched = array();
    for($i=0;$i<$elements->length;$i++) {
        if($elements->item($i)->attributes->getNamedItem('class')){
            if($elements->item($i)->attributes->getNamedItem('class')->nodeValue == $name) {
                $matched[]=$elements->item($i);
            }
        }
    }
    return $matched;
}

/** Find DOMElement by id or class name.
 *  @param \DOMDocument $doc
 *  @param String $name  Id or Class name
 *  @param Int $index   [optional] return Array index if multiple elements found
 *  @return \DOMElement $ele
 */
function find_by_class_or_id (\DOMDocument $doc, String $class_or_id, Int $index=0) {
    $parts = explode(':', $class_or_id);
    $id_ns = (isset($parts[0])) ? $parts[0] : false;
    $value = (isset($parts[1])) ? $parts[1] : false;

    $class = ($id_ns && $id_ns == 'class') ? $id_ns : false;
    $id = ($id_ns && $id_ns == 'id') ? $id_ns : false;

    $ele = ($id)
        ? $doc->getElementById($value)
        : false;

    if ((! $ele) && ($class)) {
        $class_elements = find_by_class($doc, $value);
        $ele = (isset($class_elements[$index]))
            ? $class_elements[$index]
            : false;
    }
    return $ele;
}

/** Find DOMElement by tag name.
 *  @param \DOMDocument $doc
 *  @param String $tag  Limited to 'html', 'head', 'title', 'body'
 *  @param Int $index   [optional] return Array index if multiple elements found
 *  @return \DOMElement $ele
 */
function find_by_tag (\DOMDocument $doc, String $tag, Int $index=0) {
    $valid = [ 'html', 'head', 'title', 'body' ];

    if (in_array($tag, $valid)) {
        $ele = $doc->getElementsByTagName($tag)->item($index);
        return $ele;
    }
    return false;
}

/** Find DOMElement by tag, id or class name.
 *  @param \DOMDocument $doc
 *  @param String $id  Tag, Id or Class name
 *  @param Int $index   [optional] return Array index if multiple elements found
 *  @return \DOMElement $ele
 */
function find_doc_element(\DOMDocument $doc, String $id, Int $index=0) {
    $ele = find_by_tag($doc, $id, $index);
    return ($ele) ? $ele : find_by_class_or_id($doc, $id, $index);
}

/** String to \DOMDocument
 *  @param String $html
 *  @return \DOMDocument $doc
 */
function load_from_string(String $str) {
    libxml_use_internal_errors(true);
    $doc = new \DOMDocument;
    $doc->preserveWhiteSpace = false;
    if (!$doc->loadHTML($str)) {
        foreach (libxml_get_errors() as $error) {
            // handle errors here
            echo var_dump($error);
        }

        libxml_clear_errors();
    }
    return $doc;
}

/** Alias for Create
 *  @param String $tag
 *  @param Mixed $attributes  Accepts Array or Object
 *  @param Mixed $children   If nodes are improper, an error will be returned. (ie. &lt;h1&gt; containing another tag). Accepts String or Array.
 *  @return Mixed $html|$node   If no args provided, creates basic document. Returns HTML as string. With args provided, a DOMElement is returned.
 */
function m(String $tag=NULL, $attributes=NULL, $children=NULL) {
    $attributes = (is_array($attributes))
        ? (object) $attributes
        : $attributes;
    $children = (is_array($children)) ? $children : [ $children ];
    return Create($tag, $attributes, $children);
}

/** Get or Set the document title
 *  @param String $html
 *  @param String $title        If not provided, the current value will be returned. If provided the given value will be set, and the updated document will be returned.
 *  @param Bool $append [optional] Default is true. If false, title is overwritten.
 *  @param String $delim  [optional] Title value append delimiter. Default ': '
 *  @return String $html|$title
 */
function title (String $html, String $title=NULL, Bool $append=true, $delim=': ') {
    $doc = load_from_string($html);
    $tag = 'title';
    $current = find_doc_element($doc, $tag)->nodeValue;

    if (! $title) {
        return $current;
    }

    $title = ($append) ? $current . $delim . $title : $title;

    find_doc_element($doc, $tag)->nodeValue = $title;

    return doc_to_string($doc);
}
