<?php

function getChann(PDO $db, int $channel_id) : bool|array {
    $query = "SELECT * FROM channels WHERE chat_id = :chat_id;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":chat_id", $channel_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getChannsUser(PDO $db, int $user_id) : bool|array {
    $query = "SELECT * FROM channels WHERE user_id = :user_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetchAll();
}

function addUser(PDO $db, int $user_id, ?string $user_name = null) : bool {
    $time = microtime(true);

    if (isset($user_name)) {
        $query = "INSERT INTO users (user_id, user_name, last_update) VALUES (:user_id, :user_name, {$time});";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":user_name", $user_name);
    } else {
        $query = "INSERT INTO users (user_id, last_update) VALUES (:user_id, {$time});";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
    }
    $res = $stmt->execute();
    return $res; 
}

function findUser(PDO $db, int $user_id) : bool|array {
    $query = "SELECT * FROM users WHERE user_id = :user_id;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
