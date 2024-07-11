<?php

use TBot\Bot;
use TBot\Database;

$bot = new Bot($_ENV["telegram_token"]);
$db = new Database("localhost", "voidtree_voidtreebot", "voidtree_Amir", "1383111813amrg");
$db = $db->getConnection();

$bot->default = [
    "sendMessage" => [
        "chat_id" => $_ENV["admin_id"]
    ]
];

$bot->getUpdate();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    echo "hello why are you here?";
    die();
}

$text = $bot->update_data["text"];
if ($text == "/start") {
    $bot->sendMessage("Welcome to voidtreebot!\nYou Can Select From The Many Commands...");

    $bot->sendMessage(var_export($bot->update_data["from_id"]));
    $bot->sendMessage($bot->result);
    
    $user = findUser($db, $bot->update_data["from_id"]);
    $bot->sendMessage(json_encode($user, JSON_PRETTY_PRINT));
    if ($user) {
        $time = time();
        $query = "UPDATE users SET last_update = {$time} WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $bot->update_data["from_id"]);
        $stmt->execute();
    } else {
        addUser($db, $bot->update_data["from_id"], $bot->update_data["username"]);
    }
} else if ($text == "/add") {
    $bot->sendMessage("Please Send The Channel Name...");
}




function addUser(PDO $db, int $user_id, ?string $user_name = null) : bool {
    if (isset($user_name)) {
        $query = "INSERT INTO users (user_id, user_name) VALUES (:user_id, :user_name)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":user_name", $user_name);
    } else {
        $query = "INSERT INTO users (user_id) VALUES (:user_id)";

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
