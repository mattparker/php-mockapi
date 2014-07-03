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


    protected $method;
    protected $definitions;


    public function __construct ($method, $definitions) {
        $this->method = $method;
        $this->definitions = $definitions;
    }



    public function getMethod () {
        return $this->method;
    }




    public function getHandler () {

        $definitions = $this->definitions;
        $handler = function (Request $request) use ($definitions) {

            return new Response('hi there', 200);
        };

        return $handler;
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