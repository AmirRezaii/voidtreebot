<?php

namespace TBot\Objects;

class KeyboardButton {

    public function __construct(public string $text, public bool $request_contact = false, public bool $request_location = false) {
    }

    public function toArray() : array {
        return [
            "text" => $this->text,
            "request_contact" => $this->request_contact,
            "request_location" => $this->request_location
        ];
    }
}
