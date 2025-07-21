<?php

if ($argc < 2) {
    printf("Usage: %s <dbfile>\n", $argv[0]);
    return 0;
}

$db = init_db($argv[1]);
$userid = 1;
$user = get_user($db, $userid);

//add_cat($db, $userid, "coffee");
//add_cat($db, $userid, "commute");
//add_cat($db, $userid, "dine_out");

//add_exp($db, $userid, ["date" => time(), "desc" => "coffee with moley", "amt" => 1000.50, "catid" => 1]);
//add_exp($db, $userid, ["date" => time(), "desc" => "surfing usa", "amt" => 12.0, "catid" => 2]);
//add_exp($db, $userid, ["date" => time(), "desc" => "texas roadhouse", "amt" => 3500.99, "catid" => 3]);

print_users($db);
print_cats($db);
print_exps($db);

function print_users($db) {
    $users = query_users($db);
    printf("Users:\n");
    for ($i=0; $i < count($users); $i++) {
        $user = $users[$i];
        printf("%d: #%d %s\n", $i, $user["user_id"], $user["username"]);
    }
}
function print_cats($db) {
    global $userid;

    $cats = query_cats($db, $userid);
    printf("Categories:\n");
    for ($i=0; $i < count($cats); $i++) {
        $cat = $cats[$i];
        printf("%d: %s\n", $cat["cat_id"], $cat["name"]);
    }
}
function print_exps($db) {
    global $userid;

    $xps = query_exps($db, $userid);
    printf("Expenses:\n");
    for ($i=0; $i < count($xps); $i++) {
        $xp = $xps[$i];
        $cat = get_cat($db, $xp["cat_id"]);
        $catname = "(cat not found)";
        if ($cat)
            $catname = $cat["name"];
        printf("%d: [%s]; '%s'; %.2f; %s\n", $xp["exp_id"], $xp["date"], $xp["desc"], $xp["amt"], $catname);
    }
}

function init_db($dbfile) {
    $db = new SQLite3($dbfile);
    $sql = <<<TEXT
CREATE TABLE IF NOT EXISTS user (user_id INTEGER PRIMARY KEY NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS cat (cat_id INTEGER PRIMARY KEY NOT NULL, name TEXT NOT NULL, user_id INTEGER NOT NULL);
CREATE TABLE IF NOT EXISTS exp (exp_id INTEGER PRIMARY KEY NOT NULL, date INTEGER, desc TEXT NOT NULL DEFAULT '', amt REAL NOT NULL DEFAULT 0.0, cat_id INTEGER NOT NULL DEFAULT 0, user_id INTEGER NOT NULL);
TEXT;
    $db->exec($sql);
    return $db;
}

function add_cat($db, $userid, $catname) {
    $sql = "INSERT INTO cat (name, user_id) VALUES (?, ?)";
    return dbinsert($db, $sql, $catname, $userid);
}
function add_exp($db, $userid, $xp) {
    $sql = "INSERT INTO exp (date, desc, amt, cat_id, user_id) VALUES (?, ?, ?, ?, ?)";
    return dbinsert($db, $sql, $xp["date"], $xp["desc"], $xp["amt"], $xp["catid"], $userid);
}

function get_user($db, $userid) {
    $sql = "SELECT user_id, username, password FROM user WHERE user_id = ?";
    return dbquery_one($db, $sql, $userid);
}
function get_cat($db, $catid) {
    $sql = "SELECT cat_id, name FROM cat WHERE cat_id = ?";
    return dbquery_one($db, $sql, $catid);
}
function get_exp($db, $expid) {
    $sql = "SELECT exp_id, date, desc, amt, cat_id FROM exp WHERE exp_id = ?";
    return dbquery_one($db, $sql, $expid);
}
function query_users($db) {
    $sql = "SELECT user_id, username, password FROM user ORDER BY user_id";
    return dbquery($db, $sql);
}
function query_cats($db, $userid) {
    $sql = "SELECT cat_id, name FROM cat WHERE user_id = ? ORDER BY name";
    return dbquery($db, $sql, $userid);
}
function query_exps($db, $userid) {
    $sql = "SELECT exp_id, date, desc, amt, cat_id FROM exp WHERE user_id = ? ORDER BY date";
    return dbquery($db, $sql, $userid);
}

function dbinsert($db, $sql, ...$params) {
    $stmt = $db->prepare($sql);
    for ($i=0; $i < count($params); $i++)
        $stmt->bindParam($i+1, $params[$i]);
    $res = $stmt->execute();
    $stmt->close();

    $newid = $db->lastInsertRowID();
    return $newid;
}
function dbquery($db, $sql, ...$params) {
    $stmt = $db->prepare($sql);
    for ($i=0; $i < count($params); $i++)
        $stmt->bindParam($i+1, $params[$i]);
    $res = $stmt->execute();

    $rows = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC))
        array_push($rows, $row);
    $stmt->close();
    return $rows;
}
function dbquery_one($db, $sql, ...$params) {
    $rows = dbquery($db, $sql, ...$params);
    if (count($rows) == 0)
        return null;
    return $rows[0];
}
