<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 04/07/14
 * Time: 09:30
 */

namespace MockServer;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Silex\Provider\TwigServiceProvider;


/**
 * Class RestServer
 *
 * Adds API routes to retrieve data and clear it out
 *
 * @package MockServer
 */
class RestServer {

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var DataStore
     */
    protected $store;

    /**
     * @param Application $app
     * @param DataStore $store
     */
    public function __construct (Application $app, DataStore $store) {
        $this->app = $app;
        $this->store = $store;
    }


    /**
     * Adds a `finish` listener to the app that saves request and response
     * details
     *
     * @return $this
     */
    public function addStorageListener () {

        // Saves requests to the data file
        $data_store = $this->store;

        $storage_function = function (Request $request, Response $response) use ($data_store) {

            $path = $request->getPathInfo();

            // Don't want to store results of API calls
            if (substr($path, 0, 14) === '/__mockserver/') {
                return;
            }

            $params = $request->query;
            if (strtoupper($request->getMethod()) === 'POST') {
                $params = $request->request;
            }

            $ret = [
                'request' => [
                    'path' => $path,
                    'params' => $params->all(),
                    'method' => $request->getMethod(),
                    'date' => date('Y-m-d H:i:s')
                ],
                'response' => [
                    'httpcode' => $response->getStatusCode(),
                    'content' => $response->getContent()
                ]
            ];
            $data_store->append($ret);

        };

        $this->app->finish($storage_function);

        return $this;

    }


    /**
     * Add the API routes so we can clear and retrieve response/request objects
     *
     * @return $this
     */
    public function addApiRoutes () {
        $this->addClearRoute();
        $this->addShowRoute();
        //$this->addAddRoute();
        return $this;
    }


    /**
     * Adds a __mockserver/clear route that clears out the data store
     */
    protected function addClearRoute () {
        // Clear all saved request/responses
        $data_store = $this->store;

        $this->app->get('__mockserver/clear', function () use ($data_store) {

            $data_store->clear();
            return 'ok';

        });
    }


    /**
     * Adds a __mockserver/show/{id} route so we can retrieve
     * - 'all' results
     * - 'last' result
     * - i the i-th result
     */
    protected function addShowRoute () {

        $data_store = $this->store;

        $show_function = function (Application $app, Request $request, $id) use ($data_store) {

            $resp = $data_store->fetch($id);
            $response_code = 200;

            if (!in_array($id, ['all', 'last']) && count($resp) === 0) {
                $resp = ["error" => true, "msg" => "Item {$id} not found"];
                $response_code = 404;
            }


            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                return new JsonResponse($resp, $response_code);
            }

            $template = 'one';
            if ($id === 'all') {
                $template = 'all';
                $resp = ['data' => $resp];
            }
            return $this->app['twig']->render('mockserver/' . $template . '.twig', $resp);

        };

        $this->app->get('__mockserver/show/{id}', $show_function);
        $this->app->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/views',
        ));
    }


    /**
     * Adds a route to add a route!
     *
     * Means we can POST details (as per the .json structure) to add routes
     * or append
     */
    protected function addAddRoute () {

        return;
        $add_function = function (Application $app, Request $request) {

            $route_info = $request->request->all();
            $app['session']->set('added_route', $route_info);

/*            $parser = new Parser($route_info);
            $appCreator = $parser->parse();
            $appCreator->create($app);
*/
            foreach ($route_info as $route_name => $details) {

                $handler_set = new SilexApplicationHandlerSet($route_name, $details);
                $app_generator = new SilexApplicationGenerator([$handler_set]);
                $app_generator->create($app);
            }

            return 'ok';
        };

        $this->app->post('__mockserver/add', $add_function);
    }

} 