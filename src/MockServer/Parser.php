<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:03
 */

namespace MockServer;

/**
 * Class Parser
 *
 * Main API: takes an array of route/response definitions and
 * creates an application generator that will add the routes
 * and callbacks to the Silex Application.
 *
 * @package MockServer
 */
class Parser {


    /**
     * @var array
     */
    protected $defn;


    /**
     * @param array $server_definition Must contain a key 'routes' with the routing/response
     * data in it
     * @throws \RuntimeException
     */
    public function __construct (array $server_definition) {

        if (!array_key_exists('routes', $server_definition)) {
            throw new \RuntimeException("You need an array with key 'routes' that "
                . "defines routes and responses");
        }

        $this->defn = $server_definition;

    }


    /**
     * @return SilexApplicationGenerator
     */
    public function parse () {

        $routers = [];

        foreach ($this->defn['routes'] as $route => $definitions) {

            $routers[] = new SilexApplicationHandlerSet($route, $definitions);

        }
        $generator = new SilexApplicationGenerator($routers);
        return $generator;


    }
} 