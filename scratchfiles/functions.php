<?php
function init_db($dbfile) {
    $db = new SQLite3($dbfile);
    $sql = <<<TEXT
CREATE TABLE IF NOT EXISTS user (user_id INTEGER PRIMARY KEY NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL);
TEXT;
    $db->exec($sql);
    return $db;
}

function clear_users($db) {
    $db->exec("DELETE FROM user;");
}

function get_user($db, $username) {
    $sql = "SELECT user_id, username FROM user WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $username);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $stmt->close();

    if ($row)
        return $row;
    else
        return null;
}

function register_user($db, $username, $pwd) {
    $sql = "INSERT INTO user (username, password) VALUES (?, ?)";
    $pwdhash = password_hash($pwd, PASSWORD_DEFAULT);
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $pwdhash);
    $stmt->execute();
    $stmt->close();

    $userid = $db->lastInsertRowID();
    return $userid;
}

function gen_token($username, $pwd) {
    return password_hash($username . $pwd, PASSWORD_DEFAULT);
}

?>
