<?php
header('HTTP/1.1 200 OK', TRUE);
header("Status: 200");
header("Content-Type: application/json; charset=UTF-8");
//header("Content-Type: text/html; charset=UTF-8");
include_once './common.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$input = json_decode(file_get_contents('php://input'), true);

echo WebService::processCommand($method, $request, $input);
// echo WebService::processCommand("post", array("api", "convertdate", "gregorian", 2017, 3), array());
// echo WebService::processCommand("post", array("api", "getprayertimes", 2017, 2), array("latitude"=>30.0771, "longitude"=>31.2859, "timezone"=>2));
// echo WebService::processCommand("post", array("api", "calcualteqipla"), array("latitude"=>30.0771, "longitude"=>31.2859));

?>
