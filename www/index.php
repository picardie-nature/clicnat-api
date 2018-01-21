<?php
namespace Picnat\Clicnat;
require_once("/etc/baseobs/config.php");
require_once(__DIR__."/../vendor/autoload.php");
require_once("/etc/baseobs/db.php");

\Picnat\Clicnat\get_db($db);
$app = new \Picnat\Clicnat\Api\App();
$app->setupRoutes();
$app->run();
