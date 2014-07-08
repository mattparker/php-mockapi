<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:09
 */

namespace MockServer;


use Symfony\Component\HttpFoundation\JsonResponse;
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


        /**
         * @param Request $request
         * @return Response
         */
        $handler = function (Request $request) use ($definitions, $parent) {

            $http_response_code = 200;
            $http_response_body = '';

            foreach ($definitions as $defn) {

                $paramsMatched = $parent->doParamsMatch($request, $parent->extractFromArray($defn, 'params', []));

                if ($paramsMatched) {
                    $http_response_body = $parent->extractFromArray($defn, 'response', 'body', '');
                    $http_response_code = $parent->extractFromArray($defn, 'response', 'httpcode', 200);

                    if (is_array($http_response_body)) {
                        return new JsonResponse($http_response_body, $http_response_code);
                    }
                    return new Response($http_response_body, $http_response_code);
                }

            }

            return new Response($http_response_body, $http_response_code);

        };

        return $handler;
    }


    /**
     * Checks if the GET or POST params in the request match (exactly) those in
     * $params
     *
     * @param Request $request
     * @param array $params
     * @return bool
     */
    protected function doParamsMatch (Request $request, array $params) {

        switch ($this->getMethod()) {

            case 'post':
                $request_param_bag = $request->request;
                break;

            case 'get':
            default:
                $request_param_bag = $request->query;
                break;
        }

        if (!$request_param_bag) {
            return false;
        }

        $request_params = $request_param_bag->all();


        $ret = array_same_recursive($request_params, $params);

        return $ret;


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

}


if (!function_exists('array_same_recursive')) {

    /**
     * @param array $arr1
     * @param array $arr2
     * @return bool
     * @throws \Exception
     */
    function array_same_recursive (array $arr1 = array(), array $arr2 = array()) {

        //error_log(var_export($arr1, true));error_log(var_export($arr2, true));
        if (count($arr1) !== count($arr2)) {
            return false;
        }

        foreach ($arr1 as $k1 => $v1) {
            if (!array_key_exists($k1, $arr2)) {
                return false;
            }
            $v2 = $arr2[$k1];
            if (is_object($v1) || is_object($v2)) {
                throw new \Exception("Cannot compare objects right now");
            }

            if (!is_array($v1) && $v1 != $v2) {
                return false;

            } else if (is_array($v1) && is_array($v2)) {
                if (array_same_recursive($v1, $v2) === false) {
                    return false;
                }

            }

        }

        return true;
    };
}