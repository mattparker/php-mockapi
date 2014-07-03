<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:05
 */

namespace MockServer;

use Silex\Application;


class SilexApplicationGenerator {


    /**
     * @var SilexApplicationHandlerSet[]
     */
    protected $handlers;


    /**
     * @param array $routes
     */
    public function __construct (array $routes = array()) {
        foreach ($routes as $route) {
            $this->addHandlers($route);
        }

    }


    /**
     * This is where we add our routes and handler functions
     * @param Application $app
     */
    public function create (Application $app) {

        foreach ($this->handlers as $handlerset) {

            $route = $handlerset->getRoute();

            foreach ($handlerset as $handler) {
                /** @var SilexApplicationHandler $handler $method */

                $method = $handler->getMethod();
                $callback = $handler->getHandler();

                call_user_func_array([$app, $method], [$route, $callback]);
            }
        }
    }


    /**
     * @param SilexApplicationHandlerSet $handler
     */
    protected function addHandlers (SilexApplicationHandlerSet $handler) {
        $this->handlers[] = $handler;
    }
} 