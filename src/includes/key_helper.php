<?php

use TBot\Bot;

function generateKeys(Bot $bot, $channs) : array {
    $keys = [];

    foreach ($channs as $row) {
        $bot->getChat($row["chat_id"]);
        $chat = $bot->result["result"];

        $title_key = [ $chat["title"], "title." . $row["chat_id"] ];
        $active_key = [ $row["active"] ? "Activated" : "Deactivated", $row["chat_id"] . "." . $row["active"] ];
        $delete_key = [ "Delete", "delete." . $row["chat_id"] ];

        $key = [];
        $key[] = $title_key;
        $key[] = $active_key;
        $key[] = $delete_key;

        $keys[] = $key;
    }
    return $keys;
}

