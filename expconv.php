<?php

define("E_OK", 0);
define("E_INVALID_USERNAMEPWD", 1);
define("E_USERNAME_REQUIRED", 2);
define("E_USERNAME_EXISTS", 3);
define("E_PASSWORD_NOMATCH", 4);

define("E_CAT_NOT_FOUND", 5);
define("E_DESC_REQUIRED", 6);
define("E_AMT_REQUIRED", 7);
define("E_CAT_REQUIRED", 8);


main($argc, $argv);

function main($argc, $argv) {
    if ($argc < 3) {
        printf("Usage: %s <expense file> <dbfile>\n", $argv[0]);
        return 0;
    }

    $expfile = $argv[1];
    $dbfile = $argv[2];
    $userid = 1;

    $db = init_db($dbfile);

    $f = fopen($expfile, "r");
    if (!$f) {
        printf("Error opening %s\n", $dbfile);
        return 1;
    }

    $sql = "BEGIN TRANSACTION;";
    $db->exec($sql);
    while (!feof($f)) {
        $line = rtrim(fgets($f));
        if (strlen($line) == 0)
            continue;
        $ss = explode("; ", $line);

#        printf("line: '%s'\n", $line);
#        printf("count(ss): %d\n", count($ss));
        $date = $ss[0];
        $time = $ss[1];
        $desc = $ss[2];
        $samt = $ss[3];
        $catname = $ss[4];
        add_exp($db, $userid, $desc, $samt, $catname, $date);
    }
    $sql = "END TRANSACTION;";
    $db->exec($sql);

    fclose($f);
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
function dbinsert($db, $sql, ...$params) {
    $stmt = $db->prepare($sql);
    for ($i=0; $i < count($params); $i++)
        $stmt->bindParam($i+1, $params[$i]);
    $res = $stmt->execute();
    $stmt->close();

    $newid = $db->lastInsertRowID();
    return $newid;
}
function add_exp($db, $userid, $desc, $samt, $catname, $date) {
    $desc = trim($desc);
    $samt = trim($samt);
    $catname = trim($catname);
    $date = trim($date);
    if (strlen($desc) == 0)
        return E_DESC_REQUIRED;
    if (strlen($samt) == 0)
        return E_AMT_REQUIRED;
    $catname = trim($catname);
    if (strlen($catname) == 0)
        return E_CAT_REQUIRED;

    $amt = floatval($samt);
    $dt = strtotime($date);
    if (!$dt)
        $dt = time();

    // Get existing category corresponding to $catname or create new category with $catname.
    $sql = "SELECT cat_id FROM cat WHERE name = ? AND user_id = ?";
    $cat = dbquery_one($db, $sql, $catname, $userid);
    $catid = 0;
    if (!$cat) {
        $sql = "INSERT INTO cat (name, user_id) VALUES (?, ?)";
        $catid = dbinsert($db, $sql, $catname, $userid);
    } else {
        $catid = $cat["cat_id"];
    }
    // This condition should never occur.
    if ($catid == 0)
        return E_CAT_NOT_FOUND;

    $sql = "INSERT INTO exp (date, desc, amt, cat_id, user_id) VALUES (?, ?, ?, ?, ?)";
    $expid = dbinsert($db, $sql, $dt, $desc, $amt, $catid, $userid);
    return 0;
}

function isodate_to_cal($isodate, &$year, &$month, &$day) {
    $year = 0;
    $month = 0;
    $day = 0;

    $ss = explode("-", $isodate);
    $year = intval($ss[0]);
    if (count($ss) > 1) {
        $month = intval($ss[1]);
    }
    if (count($ss) > 2) {
        $day = intval($ss[2]);
    }

    # If incomplete isodate (Ex. "2025", "2025-07"), defaults to first month/day.
    if ($year == 0)
        $year = 1970;
    if ($month == 0)
        $month = 1;
    if ($day == 0)
        $day = 1;
}
function date_from_iso($isodate) {
    $year = 0;
    $month = 0;
    $day = 0;
    isodate_to_cal($isodate, $year, $month, $day);
    return date_from_cal($year, $month, $day);
}
