<?php

namespace TBot\Objects;

class InlineKeyboard {
    public function __construct(public array $keyboard) {
    }


    public function use() : string {
        $keyboard = $this->keyboard;

        for ($i = 0; $i < count($keyboard); $i++) {
            $keyboard[$i] = array_map(function (IKeyboardButton $key) {
                return $key->toArray();
            }, $this->keyboard[$i]);
        }

        $obj = [
            "inline_keyboard" => $keyboard,
        ];

        return json_encode($obj);
    }

    public static function init(array $keyboard_buttons, bool $cb_data = false) {
        if ($cb_data) {
            $keyboard_btns = [];
            for ($i = 0; $i < count($keyboard_buttons); $i++) {
                foreach ($keyboard_buttons[$i] as $btn) {
                    $keyboard_btns[$i][] = new IKeyboardButton($btn[0], callback_data: $btn[1]);
                }
            }

            return $keyboard_btns;
        } else {
            $keyboard_btns = [];
            for ($i = 0; $i < count($keyboard_buttons); $i++) {
                foreach ($keyboard_buttons[$i] as $btn) {
                    $keyboard_btns[$i][] = new IKeyboardButton($btn);
                }
            }

            return $keyboard_btns;
        }
    }
}
