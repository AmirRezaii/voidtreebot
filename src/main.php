<?php

use TBot\Bot;

$bot = new Bot($_ENV["telegram_token"]);

$bot->getUpdate();
$bot->setWebhook("https://c86f-185-53-211-24.ngrok-free.app/");

$text = $bot->update_data["text"];
if ($text == "/start") {
    $bot->sendMessage("Hello to The VoidTreeBot!\nYou Can Select From The Many Commands...");
} else if ($text == "/add") {
    $bot->sendMessage("Please Send The Channel Name...");
}
