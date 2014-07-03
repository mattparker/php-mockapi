<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 21:26
 */

require_once __DIR__ . '/loader.php';


use \MockServer\SilexApplicationHandlerSet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class SilexApplicationHandlerSetTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        new SilexApplicationHandlerSet('index', []);
    }


    public function test_simple_construction () {

        $defn = [
            "get" => [
                [
                    "params" => [],
                    "response" => []
                ]
            ]
        ];
        $handler = new SilexApplicationHandlerSet("test/route", $defn);

        $this->assertEquals("test/route", $handler->getRoute());

        $route1 = $handler->current();
        $this->assertInstanceOf('\MockServer\SilexApplicationHandler', $route1);

        $this->assertEquals("get", $route1->getMethod());

        $cb = $route1->getHandler();
        $request = new Request();

        $response = $cb($request);
        /** @var $response Response */
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

    }


    public function test_multiple_handlers () {
        $defn = [
            "get" => [
                [
                    "params" => [],
                    "response" => []
                ],
                [
                    "params" => ["a" => 3],
                    "response" => [
                        "body" => "test a is 3"
                    ]
                ],
                [
                    "params" => ["a" => -2],
                    "response" => [
                        "httpcode" => 400,
                        "body" => "thats 400"
                    ]
                ]
            ],

            "post" => [
                [
                    "params" => ["c" => 41, "d" => "hi"],
                    "response" => [
                        "httpcode" => 301,
                        "body" => "we got a post"
                    ]
                ]
            ]
        ];

        $request1 = new Request();
        $request2 = new Request(["a" => 3]);
        $request3 = new Request(["a" => -2]);
        $request4 = new Request([], ["c" => 41]);
        $request5 = new Request([], ["c" => 41, "d" => "hi"]);


        $handler = new SilexApplicationHandlerSet("test/route", $defn);


        $cb = [];
        foreach ($handler as $hand) {
            /** @var $hand \MockServer\SilexApplicationHandler */
            $cb[] = $hand->getHandler();
        }

        $this->assertEquals(2, count($cb));

        $response01 = $cb[0]($request1);
        /** @var $response01 Response */
        $this->assertEquals("", $response01->getContent());

        $response02 = $cb[0]($request2);
        /** @var $response02 Response */
        $this->assertEquals("test a is 3", $response02->getContent());

        $response03 = $cb[0]($request3);
        /** @var $response03 Response */
        $this->assertEquals("thats 400", $response03->getContent());
        $this->assertEquals(400, $response03->getStatusCode());


        $response04 = $cb[0]($request4);
        /** @var $response04 Response */
        $this->assertEquals("", $response04->getContent());


        $response11 = $cb[1]($request1);
        /** @var $response11 Response */
        $this->assertEquals("", $response11->getContent());

        $response14 = $cb[1]($request4);
        /** @var $response14 Response */
        $this->assertEquals("", $response14->getContent());

        $response15 = $cb[1]($request5);
        /** @var $response15 Response */
        $this->assertEquals("we got a post", $response15->getContent());
        $this->assertEquals(301, $response15->getStatusCode());
    }



}
 