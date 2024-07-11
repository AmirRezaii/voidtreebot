<?php

use TBot\Bot;
use TBot\Database;

$bot = new Bot($_ENV["telegram_token"]);
$db = new Database("localhost", "voidtree_voidtreebot", $_ENV["db_user"], $_ENV["db_password"]);
$db = $db->getConnection();

$bot->default = [
    "sendMessage" => [
        "chat_id" => $_ENV["admin_id"]
    ]
];

$bot->getUpdate();

$text = $bot->update_data["text"];
$user_id = $bot->update_data["from_id"];
$user_name = $bot->update_data["username"];

if ($text == "/start") {
    $bot->sendMessage("Welcome to voidtreebot!\nYou Can Select From The Many Commands...");
    $time = microtime(true);

    $myfile = fopen("log.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $user_id);
    fclose($myfile); 


    $user = findUser($db, $user_id);
    if ($user) {
        $query = "UPDATE users SET last_update = {$time} WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    } else {
        addUser($db, $user_id, $user_name);
    }

} else if ($text == "/add") {
    $bot->sendMessage("Please Send The Channel Name...");
}




function addUser(PDO $db, int $user_id, ?string $user_name = null) : bool {
    $time = microtime(true);

    if (isset($user_name)) {
        $query = "INSERT INTO users (user_id, user_name, last_update) VALUES (:user_id, :user_name, {$time})";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":user_name", $user_name);
    } else {
        $query = "INSERT INTO users (user_id, last_update) VALUES (:user_id, {$time})";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
    }
    $res = $stmt->execute();
    return $res; 
}

function findUser(PDO $db, int $user_id) : bool|array {
    $query = "SELECT * FROM users WHERE user_id = :user_id;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
