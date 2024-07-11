<?php

use TBot\Bot;
use TBot\Database;

define("SPAM_TIME", 0.5);

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

$user = findUser($db, $user_id);

if ($user) {
    if (microtime(true) - $user["last_update"] < SPAM_TIME) {
        exit;
    }
}

if ($text == "/start") {
    $bot->sendMessage("Welcome to voidtreebot!\nYou Can Select From The Many Commands...");

    if (!$user) {
        addUser($db, $user_id, $user_name);
    }

} else if ($text == "/add") {
    $bot->sendMessage("Please Send The Channel Name...");

    $query = "UPDATE users SET step = 'add' WHERE user_id = :user_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
} else if ($text == "/list") {
    $query = "SELECT * FROM channels WHERE user_id = :user_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $chat = $bot->getChat($row["chat_id"]);
        $bot->sendMessage($chat->title);
    }
} else if ($user["step"] == "add") {
    if (preg_match("#^@[^\s]*$#", $text)) {
        $bot->getChat($text);
        $chat = $bot->result;
        if ($chat && $chat["type"] == "channel") {
            $query = "INSERT INTO channels (chat_id, user_id) VALUES (:chat_id, :user_id);";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":chat_id", $chat["id"]);
            $stmt->bindParam(":user_id", $user["id"]);
            $stmt->execute();

            $bot->sendMessage("Channel Added!");
        } else {
            $bot->sendMessage("Channel Doesn't Exist!");
        }
    } else {
        $bot->sendMessage("Please Send The Correct Channel Id");
    }
}

$time = microtime(true);
$query = "UPDATE users SET last_update = {$time} WHERE user_id = :user_id;";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();




function addUser(PDO $db, int $user_id, ?string $user_name = null) : bool {
    $time = microtime(true);

    if (isset($user_name)) {
        $query = "INSERT INTO users (user_id, user_name, last_update) VALUES (:user_id, :user_name, {$time});";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":user_name", $user_name);
    } else {
        $query = "INSERT INTO users (user_id, last_update) VALUES (:user_id, {$time});";

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

function f_log(string $text) : void {
    $myfile = fopen("log.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $text);
    fclose($myfile); 
}
