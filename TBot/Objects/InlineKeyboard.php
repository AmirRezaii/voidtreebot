<?php

namespace TBot\Objects;

class InlineKeyboard {
    public function __construct(public array $keyboard) {
    }


    public function toJson() : string {
        $keyboard = $this->keyboard;

        for ($i = 0; $i < count($keyboard); $i++) {
            $keyboard[$i] = array_map(function (IKeyboardButton $key) {
                return $key->toJson();
            }, $this->keyboard[$i]);
        }

        $obj = [
            "inline_keyboard" => $keyboard,
        ];

        return json_encode($obj);
    }

    public static function init(array $keyboard_buttons) {
        $keyboard_btns = [];
        for ($i = 0; $i < count($keyboard_buttons); $i++) {
            foreach ($keyboard_buttons[$i] as $btn) {
                $keyboard_btns[$i][] = new IKeyboardButton($btn);
            }
        }

        return $keyboard_btns;
    }
}
