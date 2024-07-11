<?php

namespace TBot\Objects;

class IKeyboardButton {

    public function __construct(public string $text, public string $url = "", public string $callback_data = "") {
    }

    public function toJson() : string {
        $obj = [
            "text" => $this->text,
        ];

        if (!$this->url) {
            $obj["url"] = $this->url;
        } else if (!$this->callback_data) {
            $obj["callback_data"] = $this->callback_data;
        } else {
            $obj["callback_data"] = $this->text;
        }

        return json_encode($obj);
    }
}
