<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:46
 */

require_once __DIR__ . '/loader.php';


use MockServer\SilexApplicationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexApplicationHandlerTest extends \PHPUnit_Framework_TestCase {




    public function test_instance () {
        new SilexApplicationHandler('get', []);
    }


    public function test_method_returned () {
        $handler = new SilexApplicationHandler('get', []);
        $this->assertEquals('get', $handler->getMethod());
    }


    public function test_first_handler_empty_200 () {
        $handler = new SilexApplicationHandler('get', []);
        $cb = $handler->getHandler();

        $request = new Request();

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

    }


    public function test_we_can_set_the_response_code () {

        $defn = [
            ['response' => ['httpcode' => 404]]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request();

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());


    }



    public function test_we_can_set_the_response_body_with_a_string () {
        $defn = [
            ['response' => ['body' => 'hello']]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request();

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('hello', $response->getContent());

    }



    public function test_we_can_test_for_request_params_on_get_request () {
        $defn = [
            [
                'params' => ['a' => 1],
                'response' => ['body' => 'hello']]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request(['a' => 1]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('hello', $response->getContent());
    }



    public function test_that_params_that_dont_match_return_defaults () {
        $defn = [
            [
                'params' => ['a' => 1],
                'response' => ['body' => 'hello']
            ]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request(['a' => 2]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
    }


    public function test_that_params_that_partially_match_return_defaults () {
        $defn = [
            [
                'params' => ['a' => 1, 'b' => 2],
                'response' => ['body' => 'hello']
            ]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request(['a' => 1, 'b' => 10]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
    }

    public function test_that_params_that_multiple_params_match_return_body () {
        $defn = [
            [
                'params' => ['a' => 1, 'b' => 2],
                'response' => ['body' => 'hello']
            ]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = new Request(['a' => 1, 'b' => 2]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('hello', $response->getContent());
    }


    public function test_that_post_params_that_match_return_body () {
        $defn = [
            [
                'params' => ['a' => 1],
                'response' => ['body' => 'hello']
            ]
        ];
        $handler = new SilexApplicationHandler('post', $defn);
        $cb = $handler->getHandler();

        $request = new Request([], ['a' => 1]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('hello', $response->getContent());
    }



    public function test_that_post_params_that_match_return_default () {
        $defn = [
            [
                'params' => ['a' => 1],
                'response' => ['body' => 'hello']
            ]
        ];
        $handler = new SilexApplicationHandler('post', $defn);
        $cb = $handler->getHandler();

        $request = new Request([], ['a' => 2]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
    }


    public function test_that_two_sets_of_params_match_correctly () {
        $defn = [
            [
                'params' => ['a' => 1],
                'response' => ['body' => 'hello']
            ],
            [
                'params' => ['a' => 2],
                'response' => ['body' => 'goodbye']
            ]
        ];
        $handler = new SilexApplicationHandler('post', $defn);
        $cb = $handler->getHandler();

        $request = new Request([], ['a' => 2]);

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('goodbye', $response->getContent());

        $request2 = new Request([], ['a' => 1]);
        $response2 = $cb($request2);
        /** @var $response2 Response */
        $this->assertEquals('hello', $response2->getContent());
    }


}
 