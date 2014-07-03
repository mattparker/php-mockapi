<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:03
 */

namespace MockServer;


class Parser {


    protected $defn;


    public function __construct (\stdClass $server_definition) {
        $this->defn = $server_definition;
    }


    /**
     * @return SilexApplicationGenerator
     */
    public function parse () {

        $routers = [];

        foreach ($this->defn->routes as $route => $definitions) {

            $routers[] = new SilexApplicationHandlerSet($route, $definitions);

        }
        $generator = new SilexApplicationGenerator($routers);
        return $generator;


    }
} 