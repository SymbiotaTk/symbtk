
// from main.php
function _Compile__REMOVE () {
    $obj = Def();

    // _ Collect resources
    $obj->resources = (object) [];
    $obj->resources->paths = Resource\paths();

    $res = Resource\Def();

    if ($res && property_exists($res, 'array')) {
        $arr = (object) $res->array;

    // X Parse configuration (YAML)
    //     X yaml_find_resource_path()
    //         > yaml_block_contains
    //             X callback reference
    //             X css links (add to head)
    //             X js links (append to body)
    //             X yamltohtml markup with interpolated vars
    //             _ per_resource_config (ie. alt dsn) [<pkg>/.site.php]
    //             _ form markup w/ layout, requirements, fk_relationships
    //
    // X Execute callback if available (path)
    //     _ callback may use data handler
    //     _ callback will return data response
    //         _ may be limited by AUTH || NOAUTH
    //         _ may return object
    //         _ may transfer files
    //         _ may interact with file system
    //     _ callback may include formatting
    //         _ may be HTML
    //         _ may be TEXT
    //         _ may be JSON
    //
    // _ Send to VIEW for format and output

        $obj->config = $res->config;
        $obj->callback = define_callback($arr);
        $obj->required = define_required($arr);

        $pos_inc = $obj->config->Include->lib->required->include;
        $pos_ns = $obj->config->Include->lib->required->namespace;

        foreach ($pos_inc AS $inc) {
            if (is_file($inc)) {
                include_once($inc);
            }
        }

        $rep = (object) [];
        foreach ($obj->callback AS $call) {
            $rep->$call = false;
        }
        $rep = (object)
            array_combine(
                array_keys((array)$rep),
                array_map(
                    function ($k, $v) use ($pos_ns) {
                        for ($i=0; $i<sizeof($pos_ns); $i++){
                            $call = implode('\\', [ $pos_ns[$i], $k]);
                            if (is_callable($call)) {
                                return $call;
                            }
                        }
                        $call = implode('\\', [ Resource\ns_call(), $k]);
                        return (is_callable($call)) ? $call : false;
                    },
                    array_keys((array)$rep),
                    array_values((array)$rep)
        ));

        $available = (array) $rep;

        $data = array_map(
            function ($c) {
                return $c();
            },
            $available
        );

        $obj->data = $data;

        echo var_dump(array_keys((array)$obj));
        echo var_dump($obj->url);
        echo var_dump(Env\Param('url'));

        return $obj;
    }

    return $obj;
}
