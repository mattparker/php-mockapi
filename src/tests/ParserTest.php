<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 22:29
 */
use MockServer\Parser;

require_once __DIR__ . '/loader.php';




class ParserTest extends PHPUnit_Framework_TestCase {



    public function test_instance () {
        new Parser(['routes' => []]);
    }

    public function test_we_get_an_exception_without_routes () {
        $this->setExpectedException('RuntimeException');
        new Parser([]);
    }


    public function test_we_can_parse_a_full_definition () {

        $defn = [
            'routes' => [
                'test/route' => [
                    'get' => [
                        [
                            'params' => ['a' => 1],
                            'response' => ['body' => 'one']
                        ]
                    ]
                ],
                'route/num2' => [
                    'post' => [
                        [
                            'response' => ['body' => 'no', 'httpcode' => 500]
                        ]
                    ]
                ],
                'another/route' => [
                    'get' => [
                        [
                            'params' => ['b' => 3],
                            'response' => ['body' => 'hi', 'httpcode' => 201]
                        ],
                        [
                            'params' => ['d' => 10],
                            'response' => ['body' => 'greetings']
                        ]
                    ],
                    'post' => [
                        [
                            'params' => ['b' => 3],
                            'response' => ['body' => 'this was posted']
                        ]
                    ]
                ]
            ]
        ];

        $parser = new Parser($defn);
        $generator = $parser->parse();

        $this->assertInstanceOf('MockServer\SilexApplicationGenerator', $generator);


        $app = $this->getMockBuilder('Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app->expects($this->exactly(2))
            ->method('get');
        $app->expects($this->exactly(2))
            ->method('post');

        $generator->create($app);

    }
}
 