<?php

init_db("test2.db");

function init_db($dbfile) {
    $db = new SQLite3($dbfile);
    $sql = <<<TEXT
CREATE TABLE IF NOT EXISTS user (user_id INTEGER PRIMARY KEY NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS cat (cat_id INTEGER PRIMARY KEY NOT NULL, name TEXT);
CREATE TABLE IF NOT EXISTS exp (exp_id INTEGER PRIMARY KEY NOT NULL, date INTEGER, desc TEXT NOT NULL DEFAULT '', amt REAL NOT NULL DEFAULT 0.0, cat_id INTEGER NOT NULL DEFAULT 1);;
TEXT;
    $db->exec($sql);
    return $db;
}
