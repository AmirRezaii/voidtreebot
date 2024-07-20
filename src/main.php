<?php

use TBot\Bot;
use TBot\Database;
use TBot\Objects\InlineKeyboard;
use TBot\Objects\ReplyKeyboard;

require ROOT_PATH . "/src/language.php";

require ROOT_PATH . "/src/includes/db_helper.php";
require ROOT_PATH . "/src/includes/core.php";
require ROOT_PATH . "/src/includes/key_helper.php";

define("SPAM_TIME", 0.5);

$bot = new Bot($_ENV["telegram_token"]);
$db = new Database("localhost", "voidtree_voidtreebot", $_ENV["db_user"], $_ENV["db_password"]);
$db = $db->getConnection();

$bot->default = [
    "sendMessage" => [
        "chat_id" => $_ENV["admin_id"],
    ]
];

$bot->getUpdate();

f_log(var_export($bot->update,true), "update");
if (!isset($bot->update_data)) {
    f_log("No update data", "update");
    exit;
}

$text = $bot->update_data["text"];
$user_id = $bot->update_data["from_id"];
$user_name = $bot->update_data["username"];
$step = "start";

$user = findUser($db, $user_id);

if ($user) {
    define("USER_LANG", $user["lang"]);

    if (microtime(true) - $user["last_update"] < SPAM_TIME) {
        exit;
    }
} else {
    define("USER_LANG", "fa");
}

$COMMANDS = [ "/start", "/list", "/add", "post" ];

if (str_starts_with($text, "/")) {
    if (!in_array($text, $COMMANDS)) {
        $bot->senMessage("Not a Valid Command.");
    }

    if ($text == "/start") {
        $bot->sendMessage(str_replace("*", $bot->update_data["firstname"], $lang[USER_LANG]["start"]));

        if (!$user) {
            addUser($db, $user_id, $user_name);
        }

    } else if ($text == "/add") {
        $bot->sendMessage($lang[USER_LANG]["add"]);

        $step = "add";
    } else if ($text == "/list") {
        $channs = getChannsUser($db, $user["id"]);

        $keys = generateKeys($bot, $channs);

        $ikeyboard = new InlineKeyboard(InlineKeyboard::init($keys,true));

        $bot->sendMessage($lang[USER_LANG]["list"], [
            "reply_markup" => $ikeyboard->use()
        ]);
    } else if ($text == "/post") {
        $keys = [
            [ "Video", "Photo" ],
            [ "Voice", "Audio" ],
            [ "Text" ]
        ];

        $reply = new ReplyKeyboard(ReplyKeyboard::init($keys));

        $bot->sendMessage("Please Select...", [
            "reply_markup" => $reply->use()
        ]);

        $step = "post";
    }

} else if ($user["step"] == "add") {
    if (preg_match("#^@[^\s]*$#", $text)) {
        $bot->getChat($text);
        $chat = $bot->result["result"];
        $res = true;

        if (empty($chat)) $res = false;
        if ($chat["type"] != "channel") $res = false;
        if (getChann($db, $chat["id"])) $res = false;

        if ($res) {
            $query = "INSERT INTO channels (chat_id, user_id) VALUES (:chat_id, :user_id);";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":chat_id", $chat["id"]);
            $stmt->bindParam(":user_id", $user["id"]);
            $stmt->execute();

            $bot->sendMessage($lang[USER_LANG]["add_success"]);
            $step = "start";
        } else {
            $bot->sendMessage($lang[USER_LANG]["add_fail"]);
        }
    } else {
        $bot->sendMessage($lang[USER_LANG]["id_error"]);
    }
} else if ($user["step"] == "post") {
    $bot->default = [
        "sendMessage" => [
            "reply_markup" => ReplyKeyboard::remove()
        ]
    ];

    if ($text == "Video") {
        $step = "Video";
        $bot->sendMessage($lang[USER_LANG]["send_video"]);
    } else if ($text == "Photo") {
        $step = "Photo";
        $bot->sendMessage($lang[USER_LANG]["send_photo"]);
    } else if ($text == "Text") {
        $step = "Text";
        $bot->sendMessage($lang[USER_LANG]["send_text"]);
    } else if ($text == "Audio") {
        $step = "Audio";
        $bot->sendMessage($lang[USER_LANG]["send_audio"]);
    } else if ($text == "Voice") {
        $step = "Voice";
        $bot->sendMessage($lang[USER_LANG]["send_voice"]);
    } else {
        $bot->sendMessage("Not a Valid Type!");
        $step = "start";
    }

}  else if (in_array($user["step"], ["Voice", "Video", "Audio", "Text", "Photo"])) {
    $caption = $bot->update_data["caption"];

    $query = "SELECT * FROM channels WHERE user_id = :user_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user["id"]);
    $stmt->execute();

    $channs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $video = $bot->update_data["video_id"];
    $photo = $bot->update_data["photo"];
    $audio = $bot->update_data["audio_id"];
    $voice = $bot->update_data["voice_id"];

    $res = true;

    if ($user["step"] == "Video") {
        if (isset($video)) {
            foreach ($channs as $ch) {
                if ($ch["active"] == 0) continue;
                $bot->sendVideo($video, [
                    "chat_id" => $ch["chat_id"],
                    "caption" => $caption
                ]);
            }
        } else {
            $bot->sendMessage("Not a Valid Video!");
            $res = false;
        }
    } else if ($user["step"] == "Photo") {
        if (isset($photo)) {
            foreach ($channs as $ch) {
                if ($ch["active"] == 0) continue;
                $bot->sendPhoto(end($photo)->file_id, [
                    "chat_id" => $ch["chat_id"],
                    "caption" => $caption
                ]);
            }
        } else {
            $bot->sendMessage("Not a Valid Photo!");
            $res = false;
        }
    } else if ($user["step"] == "Audio") {
        if (isset($audio)) {
            foreach ($channs as $ch) {
                if ($ch["active"] == 0) continue;
                $bot->sendAudio($audio, [
                    "chat_id" => $ch["chat_id"],
                    "caption" => $caption
                ]);
            }
        } else {
            $bot->sendMessage("Not a Valid Audio!");
            $res = false;
        }
    } else if ($user["step"] == "Voice") {
        if (isset($voice)) {
            foreach ($channs as $ch) {
                if ($ch["active"] == 0) continue;
                $bot->sendVoice($voice, [
                    "chat_id" => $ch["chat_id"],
                    "caption" => $caption
                ]);
            }
        } else {
            $bot->sendMessage("Not a Valid Voice!");
            $res = false;
        }
    } else if ($user["step"] == "Text") {
        if (isset($text)) {
            foreach ($channs as $ch) {
                if ($ch["active"] == 0) continue;
                $bot->sendMessage($text, [
                    "chat_id" => $ch["chat_id"]
                ]);
            }
        } else {
            $bot->sendMessage("Not a Valid Message!");
            $res = false;
        }
    }
    
    if ($res) {
        $bot->sendMessage($lang[USER_LANG]["post_success"]);
        $step = "start";
    }
} else if (!isset($text) && isset($bot->update_data["callback_id"])) {
    $da = explode(".", $bot->update_data["data"]);
    if ($da[0] == "title") {
        $bot->getChat($da[1]);
        $chann = $bot->result["result"];

        $bot->answerCallbackQuery($chann["username"]);
    } else if ($da[0] == "delete") {
        $query = "DELETE FROM channels WHERE chat_id = {$da[1]};";
        $res = $db->exec($query);
        if ($res) {
            $bot->answerCallbackQuery("Channel Deleted");
        }
    } else {
        $status = $da[1] ? 0 : 1;
        $query = "UPDATE channels SET active = {$status} WHERE chat_id = {$da[0]};";
        $res = $db->exec($query);

        if ($res) {
            $bot->answerCallbackQuery("Channel " . $status ? "Activated" : "Deactivated");
        }
    }


    $channs = getChannsUser($db, $user["id"]);

    $keys = generateKeys($bot, $channs);


    $ikeyboard = new InlineKeyboard(InlineKeyboard::init($keys,true));

    $bot->editMessageText($lang[USER_LANG]["list"], [
        "reply_markup" => $ikeyboard->use()
    ]);
} else {
    $bot->sendMessage("Bruh");
}


if ($user) {
    $time = microtime(true);
    $query = "UPDATE users SET last_update = {$time}, step = '{$step}' WHERE user_id = :user_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
}

f_log(var_export($bot->result, true), "result");


