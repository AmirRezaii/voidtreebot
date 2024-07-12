<?php

namespace TBot\Objects;

class ReplyKeyboard {
    public function __construct(public array $keyboard, public bool $is_persistent = false, public bool $resize_keyboard = false, public bool $one_time_keyboard = false, public string $input_field_placeholder = "", public bool $selective = false) {
    }


    public function use() : string {
        $keyboard = $this->keyboard;

        for ($i = 0; $i < count($keyboard); $i++) {
            $keyboard[$i] = array_map(function (KeyboardButton $key) {
                return $key->toArray();
            }, $this->keyboard[$i]);
        }

        $obj = [
            "keyboard" => $keyboard,
            "is_persistent" => $this->is_persistent,
            "resize_keyboard" => $this->resize_keyboard,
            "one_time_keyboard" => $this->one_time_keyboard,
            "input_field_placeholder" => $this->input_field_placeholder,
            "selective" => $this->selective
        ];

        return json_encode($obj);
    }
    public static function remove() : string {
        $obj = [
            "remove_keyboard" => true,
        ];

        return json_encode($obj);
    }

    public static function init(array $keyboard_buttons) {
        $keyboard_btns = [];
        for ($i = 0; $i < count($keyboard_buttons); $i++) {
            foreach ($keyboard_buttons[$i] as $btn) {
                $keyboard_btns[$i][] = new KeyboardButton($btn);
            }
        }

        return $keyboard_btns;
    }
}
