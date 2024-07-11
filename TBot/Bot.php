<?php

namespace TBot;

use TBot\ArrayManager;

class Bot {
    private string $token;
    public object $update;
    public array $default;
    public array $update_data;
    public ?array $result;

    public function __construct(string $token) {
        $this->token = $token; }

    public function __call(string $method, $args) : void {
        $this->telegramMethod($method, $args[0], $args[1] ?? []);
    }

    public function telegramMethod(string $method, $main = "", array $query) : void {
        if (gettype($main) == "array") {
            $query = $main;
        } else {
            $main = $main ?? $this->default[$method][$this->getDefault($method)];
            $query[$this->getDefault($method)] = $main;
        }
        if (isset($this->update_data)) {
            $this->default[$method] = isset($this->default[$method]) ? ArrayManager::combineArrays($this->default[$method], $this->getClassDefault($method)) : $this->getClassDefault($method);
        }
        $query = isset($this->default[$method]) ? ArrayManager::combineArrays($this->default[$method], $query) : $query;
        $this->request($method, $query);
    }


    public function setWebhook(string $url) : void {
        $query = [ 
            "url" => $url,
        ];

        $this->request("setWebhook", $query);
    }
    public function getUpdate() : bool|Bot {
        $update = json_decode(file_get_contents("php://input")) ?? $return = true;
        if ($return) {
            return false;
        }
        $this->update = $update;
        if (isset($update->message)) {
            $this->update_data["message_id"] = $update->message->message_id;
            $this->update_data["text"] = $update->message->text;
            $this->update_data["date"] = $update->message->date;
            if (isset($update->message->from)) {
                $this->update_data["is_bot"] = $update->message->from->is_bot;
                $this->update_data["firstname"] = $update->message->from->first_name;
                $this->update_data["lastname"] = $update->message->from->last_name;
                $this->update_data["username"] = $update->message->from->username;
                $this->update_data["language_code"] = $update->message->from->language_code;
                $this->update_data["from_id"] = $update->message->from->id;
            }
            if (isset($update->message->chat)) {
                $this->update_data["chat_id"] = $update->message->chat->id;
                $this->update_data["type"] = $update->message->chat->type;
            }
            if (isset($update->message->reply_to_message)) {
                $this->update_data["reply_to_message"]['message_id'] = $update->message->reply_to_message->message_id;
                if (isset($update->message->reply_to_message->from)) {
                    $this->update_data["reply_to_message"]['from_id'] = $update->message->reply_to_message->from->id;
                    $this->update_data["reply_to_message"]['is_bot'] = $update->message->reply_to_message->from->is_bot;
                    $this->update_data["reply_to_message"]['first_name'] = $update->message->reply_to_message->from->first_name;
                    $this->update_data["reply_to_message"]['username'] = $update->message->reply_to_message->from->username;
                }
                if (isset($update->message->reply_to_message->chat)) {
                    $this->update_data["reply_to_message"]['chat_id'] = $update->message->reply_to_message->chat->id;
                    $this->update_data["reply_to_message"]['chat_first'] = $update->message->reply_to_message->chat->first_name;
                    $this->update_data["reply_to_message"]['chat_last'] = $update->message->reply_to_message->chat->last_name;
                    $this->update_data["reply_to_message"]['chat_username'] = $update->message->reply_to_message->chat->username;
                }
                $this->update_data["reply_to_message"]['text'] = $update->message->reply_to_message->text;
                $this->update_data["reply_to_message"]['date'] = $update->message->reply_to_message->date;
            }
            if (isset($update->message->reply_to_message->contact)) {
                $this->update_data["phone_number"] = $update->message->reply_to_message->contact->phone_number;
                $this->update_data["contact_name"] = $update->message->reply_to_message->contact->first_name;
                $this->update_data["contact_last"] = $update->message->reply_to_message->contact->last_name;
                $this->update_data["contact_id"] = $update->message->reply_to_message->contact;
            }
        }
        if (isset($update->callback_query)) {
            $this->update_data["data"] = $update->callback_query->data;
            $this->update_data["message_id"] = $update->callback_query->message->message_id;
            $this->update_data["callback_id"] = $update->callback_query->id;
            if(isset($update->callback_query->message->chat)){
                $this->update_data["type"] = $update->callback_query->message->chat->type;
                $this->update_data["chat_id"] = $update->callback_query->message->chat->id;
            }
            if (isset($update->callback_query->from)) {
                $this->update_data["from_id"] = $update->callback_query->from->id;
                $this->update_data["is_bot"] = $update->callback_query->from->is_bot;
                $this->update_data["first_name"] = $update->callback_query->from->first_name;
                $this->update_data["last_name"] = $this->update_data["is_bot"] = $update->callback_query->from->last_name;
                $this->update_data["username"] = $update->callback_query->from->username;
                $this->update_data["language_code"] = $update->callback_query->from->language_code;
            }
        }
        else if(isset($update->inline_query)){
            $this->update_data["query"] = $update->inline_query->query;
            if(isset($update->inline_query->from)){
                $this->update_data["from_id"] = $update->inline_query->from->id;
                $this->update_data["chat_id"] = $update->inline_query->from->id;
                $this->update_data["last_name"] = $update->inline_query->from->last_name;
                $this->update_data["is_bot"] = $update->inline_query->from->is_bot;
                $this->update_data["language_code"] = $update->inline_query->from->language_code;
            }
            $this->update_data["callback_id"] = $update->inline_query->id;

        }
        return $this;
    }

    protected function request(string $method, array $query) : void {
        $url = "https://api.telegram.org/bot{$this->token}/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $method);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->result = json_decode(curl_exec($ch), true);
        curl_close($ch);
    }

    public function getDefault(string $name) : string {
        return [
            'setWebhook' => 'url',
            'sendMessage' => 'text',
            'editMessageText' => 'text',
            'copyMessage' => 'chat_id',
            'sendPhoto' => 'photo',
            'forwardMessage' => 'chat_id',
            'sendAudio' => 'audio',
            'sendDocument' => 'document',
            'sendVideo' => 'video',
            'sendAnimation' => 'animation',
            'sendVoice' => 'voice',
            'sendVideoNote' => 'video_note',
            'sendMediaGroup' => 'media',
            'sendContact' => 'phone_number',
            'sendPoll' => 'question',
            'sendDice' => 'emoji',
            'sendChatAction' => 'action',
            'getFile' => 'file_id',
            'kickChatMember' => 'user_id',
            'unbanChatMember' => 'user_id',
            'restrictChatMember' => 'user_id',
            'promoteChatMember' => 'user_id',
            'setChatAdministratorCustomTitle' => 'user_id',
            'setChatPermissions' => 'permissions',
            'exportChatInviteLink' => 'chat_id',
            'createChatInviteLink' => 'chat_id',
            'editChatInviteLink' => 'chat_id',
            'revokeChatInviteLink' => 'chat_id',
            'setChatPhoto' => 'chat_id',
            'deleteChatPhoto' => 'chat_id',
            'setChatTitle' => 'chat_id',
            'setChatDescription' => 'chat_id',
            'pinChatMessage' => 'chat_id',
            'unpinChatMessage' => 'chat_id',
            'unpinAllChatMessages' => 'chat_id',
            'leaveChat' => 'chat_id',
            'getChat' => 'chat_id',
            'getChatAdministrators' => 'chat_id',
            'getChatMembersCount' => 'chat_id',
            'getChatMember' => 'user_id',
            'setChatStickerSet' => 'chat_id',
            'deleteChatStickerSet' => 'chat_id',
            'answerCallbackQuery' => 'text',
            'answerInlineQuery' => 'inline_query_id',
            'setMyCommands' => 'commands',
            'editMessageCaption' => 'caption',
            'editMessageMedia' => 'media',
            'sendDocument' => 'document',
            'getFile' => 'file_id',
            'File' => 'file_id',
        ][$name];
    }
    public function getClassDefault(string $name) : mixed {
        return [
            '*' => [

            ],
            'sendMessage' => [
                'chat_id' => $this->update_data["chat_id"],
            ],
            'editMessageText' => [
                'chat_id' => $this->update_data["chat_id"],
                'message_id' => $this->update_data["message_id"],
            ],
            'answerCallbackQuery' => [
                'callback_query_id' => $this->update_data["callback_id"],
            ],
            'answerInlineQuery' => [
                'inline_query_id' => $this->update_data["callback_id"],
            ],
            'deleteMessage' => [
                'message_id' => $this->update_data["message_id"],
                'chat_id' => $this->update_data["chat_id"],
            ],
            'copyMessage' => [
                'from_chat_id' => $this->update_data["chat_id"],
                'message_id' => $this->update_data["message_id"],

            ],
            'forwardMessage' => [
                'from_chat_id' => $this->update_data["chat_id"],    
                'message_id' => $this->update_data["message_id"],
            ],
            'sendDocument' => [
                'chat_id' => $this->update_data["chat_id"],
            ],
        ][$name];
    }

}
