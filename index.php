<?php

require_once __DIR__ . '/vendor/autoload.php';


$app = new Silex\Application();



$server_definition_file = file_get_contents("mailchimp.json");
$server_definition = json_decode($server_definition_file);


foreach ($server_definition->routes as $route => $request_response_defns) {



    foreach ($request_response_defns as $method => $request_response_defn) {

        $handler = function () use ($request_response_defn) {
            return 'Hi';
        };
        
        call_user_func_array([$app, $method], [$route, $handler]);

    }
}



$app->run();