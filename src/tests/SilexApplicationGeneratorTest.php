<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 21:59
 */

require_once __DIR__ . '/loader.php';


use MockServer\SilexApplicationGenerator;
use MockServer\SilexApplicationHandlerSet;


class SilexApplicationGeneratorTest extends PHPUnit_Framework_TestCase {



    public function test_instance () {
        new SilexApplicationGenerator([]);
    }

    public function test_routes_are_added_to_application () {

        $route1 = "test/route";
        $route_def1 = [
            "get" => [
                [
                    "params" => [],
                    "response" => [
                        "body" => "hello"
                    ]
                ]
            ],
            "post" => [
                [
                    "params" => ["a" => 1],
                    "response" => [
                        "body" => "thanks for a1 post",
                    ]
                ]
            ]
        ];
        $route2 = "another/test";
        $route_def2 = [
            "get" => [

            ]
        ];

        $sets = [];
        $sets[] = new SilexApplicationHandlerSet($route1, $route_def1);
        $sets[] = new SilexApplicationHandlerSet($route2, $route_def2);

        $generator = new SilexApplicationGenerator($sets);


        $app = $this->getMockBuilder('Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app->expects($this->exactly(2))
            ->method('get');
        $app->expects($this->once())
            ->method('post');

        $generator->create($app);


    }
}
 