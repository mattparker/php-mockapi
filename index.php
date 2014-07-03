<?php
/**
 *
 * A Mock server to receive API requests and return reasonable looking responses.
 * That means you can write integration tests against an external API, receive
 * them here, and then make requests to get them back and check them.
 *
 * Routes, parameter matching and responses are defined in a json file.
 *
 *
 */


require_once __DIR__ . '/vendor/autoload.php';
spl_autoload_register(function ($classname) {
    include_once __DIR__ . '/src/' . str_replace('\\', '/', $classname) . '.php';
});


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



$app = new Silex\Application();


// This is where we load the stuff and add it to the application:
$server_definition_file = file_get_contents("mailchimp.json");
$server_definition = json_decode($server_definition_file, true);

$parser = new MockServer\Parser($server_definition);
$appCreator = $parser->parse();
$appCreator->create($app);




// Now add routes to allow us to get response/request objects
$datafile = __DIR__ . '/data/requests.txt';

// Saves requests to the data file
$app->finish(function (Request $request, Response $response) use ($datafile) {

    $path = $request->getPathInfo();

    if (substr($path, 0, 14) !== '/__mockserver/') {

        $params = $request->query;
        if ($request->getMethod() === 'post') {
            $params = $request->request;
        }

        $ret = [
            'request' => [
                'path' => $path,
                'params' => $params->all()
            ],
            'response' => [
                'httpcode' => $response->getStatusCode(),
                'content' => $response->getContent()
            ]
        ];
        $current = unserialize(file_get_contents($datafile));
        $current[] = $ret;
        file_put_contents($datafile, serialize($current));

    }
});


// Clear all saved request/responses
$app->get('__mockserver/clear', function () use ($datafile) {
    file_put_contents($datafile, serialize([]));
    return 'ok';
});
// Provide access to saved responses
$app->get('__mockserver/show/{id}', function (\Silex\Application $app, $id) use ($datafile) {

    $data = unserialize(file_get_contents($datafile));
    $resp = null;

    if ($id === 'last') {
        $resp = $data[count($data) -1];
    } else if ($id === 'all') {
        $resp = $data;
    } else if ($id !== '' && $id >= 0 && array_key_exists($id, $data)) {
        $resp = $data[$id];
    }

    return new JsonResponse($resp);

});



$app->run();

