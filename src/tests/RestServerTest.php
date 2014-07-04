<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 04/07/14
 * Time: 13:55
 */

use MockServer\RestServer;

require_once __DIR__ . '/loader.php';

class RestServerTest extends PHPUnit_Framework_TestCase {


    private $mockApp;
    private $mockStore;

    /**
     * @var RestServer
     */
    private $restServer;

    public function setUp () {
        $this->mockApp = $this->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockStore = $this->getMockBuilder('\MockServer\DataStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->restServer = new RestServer($this->mockApp, $this->mockStore);
    }

    public function test_instance () {

    }


    public function test_we_add_the_finish_listener () {

        $this->mockApp->expects($this->once())
            ->method('finish');

        $this->restServer->addStorageListener();
    }

    public function test_we_add_the_mockserver_routes () {

        $this->mockApp->expects($this->exactly(2))
            ->method('get');

        $this->restServer->addApiRoutes();
    }

}
 