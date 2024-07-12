<?php

namespace TBot\Objects;

class IKeyboardButton {

    public function __construct(public string $text, public string $url = "", public string $callback_data = "") {
    }

    public function toArray() : array {
        $obj = [
            "text" => $this->text
        ];

        if ($this->callback_data != "") {
            $obj["callback_data"] = $this->callback_data;
        }
        if ($this->url != "") {
            $obj["url"] = $this->url;
        }

        return $obj;
    }
}
