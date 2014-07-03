<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:09
 */

namespace MockServer;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SilexApplicationHandler {


    /**
     * @var string
     */
    protected $method;
    /**
     * @var array
     */
    protected $definitions;


    /**
     * @param $method
     * @param $definitions
     */
    public function __construct ($method, $definitions) {
        $this->method = $method;
        $this->definitions = $definitions;
    }


    /**
     * @return string
     */
    public function getMethod () {
        return $this->method;
    }


    /**
     * Makes the function that acts as a Silex Controller
     *
     * @return callable
     */
    public function getHandler () {

        $definitions = $this->definitions;
        $parent = $this;

        $handler = function (Request $request) use ($definitions, $parent) {

            $http_response_code = 200;
            $http_response_body = '';


            foreach ($definitions as $defn) {

                $http_response_body = $parent->extractFromArray($defn, 'response', 'body', '');
                $http_response_code = $parent->extractFromArray($defn, 'response', 'httpcode', 200);


            }
            return new Response($http_response_body, $http_response_code);
        };

        return $handler;
    }





    /**
     * Finds a value from an arbitrarily nested array where keys may
     * or may not exist.
     *
     * For example, given an array $arr like this:
     * $arr = ['jack' => [
     *     'be' => [
     *         'nimble' => ['or' => 'quick']
     *         'shortversion' => true
     *     ]
     * ]]
     *
     * Then $this->extractFromArray($arr, 'jack', 'be', 'nimble', 'or', 'defaultvalue');
     * will return 'quick'.
     * While $this->extractFromArray($arr, 'jack', 'be', 'nimble', 'notpresent', 'defaultvalue');
     * will return 'defaultvalue'
     *
     *
     * @param array $arr
     * @param string | int array keys to follow down the array
     * @param mixed  Default value to return if not found
     * @return mixed
     */
    protected function extractFromArray (array $arr) {

        $args = func_get_args();

        // This is the default value, the last argument to the method
        if (!array_key_exists($args[1], $arr)) {
            return $args[count($args) - 1];
        }

        // We know this exists: and we're at the end
        // 3 args is 1: input array, 2: array key, 3: (unused) default value
        if (count($args) === 3) {

            return $arr[$args[1]];

        }

        // Now go down one level of the array
        $args[0] = $arr[$args[1]];
        // And remove the key we've found and moved down
        array_splice($args, 1, 1);

        return call_user_func_array(array($this, 'extractFromArray'), $args);

    }
/*
 *
 *
            foreach ($definitions as $method => $definition) {

                $handler = function (Request $request) use ($definition) {

                    foreach ($definition as $request_response_defn) {


                        $return_code = $request_response_defn->return->httpcode;// property_exists($request_response_defn, 'httpcode') ? $request_response_defn->httpcode : 200;
                        $return_body = $request_response_defn->return->body;// property_exists($request_response_defn, 'body') ? $request_response_defn->body : "";

                    }

                    return new Response($return_body, $return_code);
                };

                call_user_func_array([$app, $method], [$route, $handler]);

            }
        }
 */
}