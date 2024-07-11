<?php

namespace TBot\Objects;

class BotCommand {
    public string $command;
    public string $desc;

    public function __construct(string $command, string $desc) {
        $this->command = $command;
        $this->desc = $desc;
    }

    public function toArray() : array {
        $obj = [
            "command" => $this->command,
            "description" => $this->desc
        ];
        return $obj;
    }

    public static function use(...$cmds) {
        $li = [];
        foreach ($cmds as $cmd) {
            $li[] = $cmd->toArray(); 
        }

        return json_encode($li);
    }
}
