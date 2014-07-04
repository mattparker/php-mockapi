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


$default_definition_file = __DIR__ . "/mockserver.json";
$default_datafile = __DIR__ . '/data/requests.txt';


$app = new Silex\Application();




// Detect environment (default: prod) by checking for the existence of $app_env
// (If you know of a safer or smarter way to do this that works with both HTTP
// and CLI, let me know)
if (isset($app_env) && in_array($app_env, array('prod','dev','test'))) {
    $app['env'] = $app_env;
} else {
    $app['env'] = 'prod';
}

// Use a known test file
if (1 || 'test' === $app['env']) {
    $default_definition_file = __DIR__ . '/src/tests/testserver.json';
    $default_datafile = __DIR__ . '/src/tests/testrequests.txt';
}


if (!file_exists($default_datafile)) {
    file_put_contents($default_datafile, '');
}







// This is where we load the stuff and add it to the application:
$server_definition_file = file_get_contents($default_definition_file);
$server_definition = json_decode($server_definition_file, true);

$parser = new MockServer\Parser($server_definition);
$appCreator = $parser->parse();
$appCreator->create($app);





// Now add routes to allow us to get response/request objects

$data_store = new MockServer\DataStore($default_datafile);
$rest_server = new MockServer\RestServer($app, $data_store);
$rest_server->addStorageListener()
    ->addApiRoutes();






if ('test' === $app['env']) {
    return $app;
} else {
    $app->run();
}

