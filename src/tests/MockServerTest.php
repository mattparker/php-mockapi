<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 04/07/14
 * Time: 14:07
 */

use Silex\WebTestCase;
//require_once __DIR__ . '/loader.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class MockServerTest extends WebTestCase {


    public function createApplication () {

        // clear out requests
        file_put_contents(__DIR__ . '/testrequests.txt', '');

        // These are the test files server definition files:
        $default_definition_file = __DIR__ . '/testserver.json';
        $default_datafile = __DIR__ . '/testrequests.txt';
        $ret = require __DIR__ . '/../../app.php';


        return $ret;
    }


    public function test_simple_blank_request () {

        $client = $this->createClient();
        $client->request('GET', '/index');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $this->assertEmpty($response->getContent());

    }

    public function test_naughty_post_to_home_page () {
        $client = $this->createClient();
        $client->request('POST', '/index');
        $response = $client->getResponse();

        $this->assertEquals($response->getStatusCode(), 400);
        $this->assertEquals('POSTing to the index page is just not on', $response->getContent());
    }

    public function test_matching_one_params () {

        $client = $this->createClient();
        $client->request('GET', "/testing/params?a=2");
        $response = $client->getResponse();

        $this->assertEquals("a was two", $response->getContent());
    }

    public function test_matching_two_params () {
        $client = $this->createClient();
        $client->request('GET', "/testing/params?a=2&b=3");
        $response = $client->getResponse();

        $this->assertEquals("a was two and b was three", $response->getContent());
    }

    public function test_matching_another_single_param () {
        $client = $this->createClient();
        $client->request('GET', "/testing/params?b=3");
        $response = $client->getResponse();

        $this->assertEquals("b was three", $response->getContent());

    }

    public function test_a_route_expecting_params_without_giving_any () {

        $client = $this->createClient();
        $client->request('GET', "/testing/params");
        $response = $client->getResponse();

        $this->assertEquals("", $response->getContent());
        $this->assertTrue($response->isOk());
    }

    public function test_an_expected_json_response () {

        $client = $this->createClient();
        $client->request('GET', "/json/response");
        $response = $client->getResponse();

        $expected = json_encode(["d" => 12345, "e" => "hello there"]);
        $this->assertEquals($expected, $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('content-type'));


    }


    public function test_we_can_retrieve_requests () {

        // clear out .log of all requests
        file_put_contents(__DIR__ . '/testrequests.txt.log', '');

        $client = $this->createClient();
        $client->request('GET', '/index');
        $client->request('GET', "/testing/params?a=2");
        $client->request('GET', "/json/response");
        $client->request('GET', "/testing/params?a=2&b=3");

        // now get them back
        $client->request('GET', '/__mockserver/show/all', [], [], ['CONTENT_TYPE' => 'application/json']);
        $all_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals(4, count($all_arr));

        $this->assertEquals("/index", $all_arr[0]->request->path);
        $this->assertEquals([], $all_arr[0]->request->params);
        $this->assertEquals('GET', $all_arr[0]->request->method);
        $this->assertEquals("", $all_arr[0]->response->content);
        $this->assertEquals(200, $all_arr[0]->response->httpcode);

        $this->assertEquals("/testing/params", $all_arr[3]->request->path);
        $this->assertEquals((object)["a" => 2, "b" => 3], $all_arr[3]->request->params);
        $this->assertEquals("a was two and b was three", $all_arr[3]->response->content);
        $this->assertEquals(200, $all_arr[3]->response->httpcode);

        // now get a single one
        $client->request('GET', '/__mockserver/show/1', [], [], ['CONTENT_TYPE' => 'application/json']);
        $one_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals("/testing/params", $one_arr->request->path);
        $this->assertEquals((object)["a" => 2], $one_arr->request->params);
        $this->assertEquals("a was two", $one_arr->response->content);
        $this->assertEquals(200, $one_arr->response->httpcode);

        // now get the last one
        $client->request('GET', '/__mockserver/show/last', [], [], ['CONTENT_TYPE' => 'application/json']);
        $one_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals("/testing/params", $one_arr->request->path);
        $this->assertEquals((object)["a" => 2, "b" => 3], $one_arr->request->params);
        $this->assertEquals("a was two and b was three", $one_arr->response->content);
        $this->assertEquals(200, $one_arr->response->httpcode);


        // now clear them
        $client->request('GET', '/__mockserver/clear');
        $this->assertTrue($client->getResponse()->isOk());

        // and check there's none left
        $client->request('GET', '/__mockserver/show/all', [], [], ['CONTENT_TYPE' => 'application/json']);
        $all_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0, count($all_arr));

        // but theck that there's still some in the ?log=1
        $client->request('GET', '/__mockserver/show/all?log=1', [], [], ['CONTENT_TYPE' => 'application/json']);
        $all_items = json_decode($client->getResponse()->getContent());
        $this->assertEquals(4, count($all_items));
    }


    public function test_with_escaped_paths_in_serverfile () {
        $client = $this->createClient();
        $client->request('GET', '/path/escaped');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $this->assertEquals("hi", $response->getContent());
    }


    public function test_posted_params_are_retrieved () {
        $client = $this->createClient();
        $client->request('POST', '/testing/params', ["a" => 13]);
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());
        $this->assertEquals("Unlucky", $response->getContent());
    }


    public function test_posted_params_as_json_string () {
        $client = $this->createClient();
        $client->request('POST', '/testing/params', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(["a" => 13]));
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());
        $this->assertEquals("Unlucky", $response->getContent());
    }

    /*
        public function test_we_can_add_a_route () {

            $newRoute = [
                'new/route' => [
                    'get' => [
                        [
                            'response' => [
                                'body' => 'Hello there new route'
                            ]
                        ]
                    ]
                ]
            ];
            $contentType = ['CONTENT_TYPE' => 'application/json'];


            $client = $this->createClient();

            $client->request('POST', '__mockserver/add', [], [], $contentType, json_encode($newRoute));
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("ok", $response->getContent());

            // Now test if we can use the new route
            $client = $this->createClient();
            $client->request('GET', '/new/route');
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("Hello there new route", $response->getContent());
        }



        public function test_we_can_add_a_route_with_params () {

            $newRoute = [
                'new/route' => [
                    'get' => [
                        [

                            'params' => [
                                'g' => 5
                            ],
                            'response' => [
                                'body' => 'Hello there new route g5'
                            ]
                        ]
                    ]
                ]
            ];
            $contentType = ['CONTENT_TYPE' => 'application/json'];


            $client = $this->createClient();

            $client->request('POST', '__mockserver/add', [], [], $contentType, json_encode($newRoute));
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("ok", $response->getContent());

            // Now test if we can use the new route
            $client = $this->createClient();
            $client->request('GET', '/new/route?g=5');
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("Hello there new route g5", $response->getContent());
        }


        public function test_that_a_second_route_replaces_the_first () {
            $newRoute = [
                'new/route' => [
                    'get' => [
                        [

                            'params' => [
                                'g' => 5
                            ],
                            'response' => [
                                'body' => 'Hello there new route g5'
                            ]
                        ]
                    ]
                ]
            ];
            $contentType = ['CONTENT_TYPE' => 'application/json'];

            $client = $this->createClient();

            $client->request('POST', '__mockserver/add', [], [], $contentType, json_encode($newRoute));
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("ok", $response->getContent());

            $newRoute2 = [
                'new/route' => [
                    'get' => [
                        [

                            'params' => [
                                'g' => 55
                            ],
                            'response' => [
                                'body' => 'That is 55'
                            ]
                        ]
                    ]
                ]
            ];
            $client->request('POST', '/__mockserver/add', [], [], $contentType, json_encode($newRoute2));
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("ok", $response->getContent());

            // Now test if we can use the new route
            $client = $this->createClient();
            $client->request('GET', '/new/route?g=5');
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("", $response->getContent());

            $client->request('GET', '/new/route?g=55');
            $response = $client->getResponse();

            $this->assertTrue($response->isOk());
            $this->assertEquals("That is 55", $response->getContent());
        }
    */

}