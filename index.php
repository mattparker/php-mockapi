<?php

require_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($classname) {
    include_once __DIR__ . '/src/' . str_replace('\\', '/', $classname) . '.php';
});

$app = new Silex\Application();



$server_definition_file = file_get_contents("mailchimp.json");
$server_definition = json_decode($server_definition_file);


$parser = new MockServer\Parser($server_definition);
$appCreator = $parser->parse();
$appCreator->create($app);

$app->run();

