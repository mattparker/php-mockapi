<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:46
 */

require_once __DIR__ . '/loader.php';
require_once __DIR__ . '/../MockServer/SilexApplicationHandler.php';


use MockServer\SilexApplicationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexApplicationHandlerTest extends \PHPUnit_Framework_TestCase {

    private function getMockRequest () {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        return $request;
    }



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

        $request = $this->getMockRequest();

        $response = $cb($request);
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

    }


    public function test_we_can_set_the_response_code () {

        $defn = [
            ['response' => ['httpcode' => 404]]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = $this->getMockRequest();

        $response = $cb($request);
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());


    }



    public function test_we_can_set_the_response_body_with_a_string () {
        $defn = [
            ['response' => ['body' => 'hello']]
        ];
        $handler = new SilexApplicationHandler('get', $defn);
        $cb = $handler->getHandler();

        $request = $this->getMockRequest();

        $response = $cb($request);
        $this->assertEquals('hello', $response->getContent());

    }

}
 