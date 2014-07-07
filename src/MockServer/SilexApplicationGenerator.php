<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:05
 */

namespace MockServer;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SilexApplicationGenerator
 *
 * Responsible for adding handlers to the Silex Application.
 * This is created by the \MockServer\Parser.
 *
 * @package MockServer
 */
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
     * to the application
     *
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

        $this->addJsonParser($app);
    }


    protected function addJsonParser (Application $app) {
        $app->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });
    }

    /**
     * @param SilexApplicationHandlerSet $handler
     */
    protected function addHandlers (SilexApplicationHandlerSet $handler) {
        $this->handlers[] = $handler;
    }
} 