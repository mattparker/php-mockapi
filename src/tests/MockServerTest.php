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
        $app_env = 'test';

        $ret = require __DIR__ . '/../../index.php';

        // clear out requests
        file_put_contents(__DIR__ . '/testrequests.txt', '');
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
        $client = $this->createClient();
        $client->request('GET', '/index');
        $client->request('GET', "/testing/params?a=2");
        $client->request('GET', "/json/response");
        $client->request('GET', "/testing/params?a=2&b=3");

        // now get them back
        $client->request('GET', '/__mockserver/show/all');
        $all_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals(4, count($all_arr));

        $this->assertEquals("/index", $all_arr[0]->request->path);
        $this->assertEquals([], $all_arr[0]->request->params);
        $this->assertEquals("", $all_arr[0]->response->content);
        $this->assertEquals(200, $all_arr[0]->response->httpcode);

        $this->assertEquals("/testing/params", $all_arr[3]->request->path);
        $this->assertEquals((object)["a" => 2, "b" => 3], $all_arr[3]->request->params);
        $this->assertEquals("a was two and b was three", $all_arr[3]->response->content);
        $this->assertEquals(200, $all_arr[3]->response->httpcode);

        // now get a single one
        $client->request('GET', '/__mockserver/show/1');
        $one_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals("/testing/params", $one_arr->request->path);
        $this->assertEquals((object)["a" => 2], $one_arr->request->params);
        $this->assertEquals("a was two", $one_arr->response->content);
        $this->assertEquals(200, $one_arr->response->httpcode);

        // now get the last one
        $client->request('GET', '/__mockserver/show/last');
        $one_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals("/testing/params", $one_arr->request->path);
        $this->assertEquals((object)["a" => 2, "b" => 3], $one_arr->request->params);
        $this->assertEquals("a was two and b was three", $one_arr->response->content);
        $this->assertEquals(200, $one_arr->response->httpcode);


        // now clear them
        $client->request('GET', '/__mockserver/clear');
        $this->assertTrue($client->getResponse()->isOk());

        $client->request('GET', '/__mockserver/show/all');
        $all_arr = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0, count($all_arr));
    }

} 