<?php
declare(strict_types=1);

define("ROOT_PATH", __DIR__);

spl_autoload_register(function (string $class_name) {
    $class_name = str_replace("\\", "/", $class_name);
    if (explode("/", $class_name)[0] == "TBot") {
        require(ROOT_PATH . "/" . $class_name . ".php");
    } else {
        require(ROOT_PATH . "/src/" . $class_name . ".php");
    }
});

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    echo "hello why are you here?";
    die();
}


use TBot\Dotenv;

$dotenv = new Dotenv;
$dotenv->load(ROOT_PATH . "/.env");

require("./src/main.php");
