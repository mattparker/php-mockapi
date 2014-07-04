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


$app = new Silex\Application();


// This is where we load the stuff and add it to the application:
$server_definition_file = file_get_contents("mailchimp.json");
$server_definition = json_decode($server_definition_file, true);

$parser = new MockServer\Parser($server_definition);
$appCreator = $parser->parse();
$appCreator->create($app);




// Now add routes to allow us to get response/request objects
$datafile = __DIR__ . '/data/requests.txt';

$data_store = new MockServer\DataStore($datafile);
$rest_server = new MockServer\RestServer($app, $data_store);
$rest_server->addStorageListener()
    ->addApiRoutes();



$app->run();

