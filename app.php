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
 * Don't run this file directly: use index.php if you're running the
 * php built in server.
 *
 */


require_once __DIR__ . '/vendor/autoload.php';
spl_autoload_register(function ($classname) {
    include_once __DIR__ . '/src/' . str_replace('\\', '/', $classname) . '.php';
});


// This is  a json definition of server routes, requests and responses:
if (!isset($default_definition_file)) {

    $default_definition_file = getenv('MOCKSERVER_DEFINITION');
    if (!$default_definition_file) {
        $default_definition_file = __DIR__ . "/mockserver.json";
    }
}

// And this is a text file where we save the requests made (so they can be queried)
if (!isset($default_datafile)) {
    $default_datafile = getenv('MOCKSERVER_DATAFILE');
    if (!$default_datafile) {
        $default_datafile = __DIR__ . '/data/requests.txt';
    }
}
if (!file_exists($default_datafile)) {
    file_put_contents($default_datafile, '');
}




$app = new Silex\Application();
$app['debug'] = true;




// Load the server definition and add the routes to the Application:
$server_definition_file = file_get_contents($default_definition_file);
$server_definition = json_decode($server_definition_file, true);

$parser = new MockServer\Parser($server_definition);
$appCreator = $parser->parse();
$appCreator->create($app);





// Now add routes to allow us to get response/request objects
// via the /__mockserver/ API
$data_store = new MockServer\DataStore($default_datafile);
$rest_server = new MockServer\RestServer($app, $data_store);
$rest_server->addStorageListener()
    ->addApiRoutes();


// This makes testing reasonable: we return $app to index.php where it is run(),
// and to the tests where they do their thing (but need an $app, not a running $app).
return $app;



