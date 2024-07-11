<?php

use TBot\Bot;

$bot = new Bot($_ENV["telegram_token"]);

$bot->getUpdate();
$bot->setWebhook("https://voidtree.ir/voidtreebot");
$bot->sendMessage($bot->result);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    echo "hello why are you here?";
    die();
}

$text = $bot->update_data["text"];
if ($text == "/start") {
    $bot->sendMessage("Hello to The VoidTreeBot!\nYou Can Select From The Many Commands...");
} else if ($text == "/add") {
    $bot->sendMessage("Please Send The Channel Name...");
}
