<?php

define("E_OK", 0);
define("E_INVALID_USERNAMEPWD", 1);
define("E_USERNAME_REQUIRED", 2);
define("E_USERNAME_EXISTS", 3);
define("E_PASSWORD_NOMATCH", 4);

function _strerror($errno) {
    if ($errno == E_OK)
        return "";
    else if ($errno == E_INVALID_USERNAMEPWD)
        return "Invalid username or password";
    else if ($errno == E_USERNAME_REQUIRED)
        return "Enter a username";
    else if ($errno == E_USERNAME_EXISTS)
        return "Username already exists";
    else if ($errno == E_PASSWORD_NOMATCH)
        return "Re-entered password doesn't match";
    else
        return "An error occured";
}

$db = init_db("test.db");

$errno = 0;

$action = getv($_GET, "action");
if (strequals($action, "login"))
    $errno = login_user($db, $_POST["username"], $_POST["password"]);
else if (strequals($action, "register"))
    $errno = register_user($db, $_POST["username"], $_POST["password"], $_POST["password2"]);
else if (strequals($action, "logout"))
    logout_user();

print_page($errno);

function print_page($errno) {
    global $db;
    $user = get_session_user($db);
    $p = getv($_GET, "p");
    $action = getv($_GET, "action");

    print_head();
    print_navbar($user);
    print "<div class=\"grid\">";

    if (strequals($p, "login") || (strequals($action, "login") && $errno != 0))
        print_loginpanel($errno);
    else if (strequals($p, "register") || (strequals($action, "register") && $errno != 0))
        print_registerpanel($errno);
    else if (!$user)
        print_loginpanel();
    else
        print_textpanel();

    print "</div>";
    print_foot();
}
function print_head() {
    echo <<<TEXT
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>login</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
TEXT;
}
function print_foot() {
    echo <<<TEXT
</body>
</html>
TEXT;
}

function print_navbar($user) {
    echo <<<TEXT
<div class="navbar">
    <div>
        <ul class="line-menu">
            <li><a href="/">Expense Buddy Web</a></li>
            <li><a href="/">About</a></li>
        </ul>
    </div>
    <div>
        <ul class="line-menu">
TEXT;

    if (!$user) {
        echo "<li><a href=\"/\">login</a></li>\n";
    } else {
        $username = $user["username"];
        echo "<li><a href=\"/\">$username</a></li>\n";
        echo "<li><a href=\"/index.php?action=logout\">logout</a></li>\n";
    }

    echo <<<TEXT
        </ul>
    </div>
</div>
TEXT;
}
function print_loginpanel($errno=0) {
    echo <<<TEXT
<div class="panel login">
    <p class="titlebar">Login</p>
    <div>
        <h2 class="heading">Login</h2>
        <form action="/index.php?action=login" method="POST" class="simpleform" >
TEXT;

    $username = trim(sgetv($_POST, "username"));
    $password = sgetv($_POST, "password");

    echo "<div class=\"control\">\n";
    echo "<label for=\"username\">Username</label>\n";
    if ($errno == E_INVALID_USERNAMEPWD || $errno == E_USERNAME_REQUIRED) {
        echo "<input id=\"username\" name=\"username\" type=\"text\" size=\"20\" value=\"$username\" class=\"invalid\">\n";
    } else {
        echo "<input id=\"username\" name=\"username\" type=\"text\" size=\"20\" value=\"$username\">\n";
    }
    echo "</div>\n";

    echo "<div class=\"control\">\n";
    echo "<label for=\"password\">Password</label>\n";
    if ($errno == E_INVALID_USERNAMEPWD)
        echo "<input id=\"password\" name=\"password\" type=\"password\" size=\"20\" value=\"$password\" class=\"invalid\">\n";
    else
        echo "<input id=\"password\" name=\"password\" type=\"password\" size=\"20\" value=\"$password\">\n";
    echo "</div>\n";

    if ($errno != 0) {
        $errmsg = _strerror($errno);
        echo "<p class=\"error\">{$errmsg}</p>\n";
    }

    echo <<<TEXT
            <div class="control">
                <button class="submit" type="submit">Login</button>
            </div>
        </form>
        <p>
            <a href="/?p=register">Create New Account</a>
        </p>
    </div>
</div> <!-- panel -->
TEXT;
}
function print_registerpanel($errno=0) {
    echo <<<TEXT
    <div class="panel">
        <p class="titlebar">Create New User</p>
        <div>
            <h2 class="heading">Create New User</h2>
            <form action="/index.php?action=register" method="POST" class="simpleform" >
TEXT;

    $username = trim(sgetv($_POST, "username"));
    $password = sgetv($_POST, "password");
    $password2 = sgetv($_POST, "password2");

    echo "<div class=\"control\">\n";
    echo "<label for=\"username\">Username</label>\n";
    if ($errno == E_INVALID_USERNAMEPWD || $errno == E_USERNAME_REQUIRED || $errno == E_USERNAME_EXISTS)
        echo "<input id=\"username\" name=\"username\" type=\"text\" size=\"20\" value=\"$username\" class=\"invalid\">\n";
    else
        echo "<input id=\"username\" name=\"username\" type=\"text\" size=\"20\" value=\"$username\">\n";
    echo "</div>\n";

    if ($errno == E_PASSWORD_NOMATCH || $errno == E_INVALID_USERNAMEPWD) {
        echo "<div class=\"control\">\n";
        echo "  <label for=\"password\">Password</label>\n";
        echo "  <input id=\"password\" name=\"password\" type=\"password\" size=\"20\" value=\"$password\" class=\"invalid\">\n";
        echo "</div>\n";
        echo "<div class=\"control\">\n";
        echo "  <label for=\"password2\">Re-enter Password</label>\n";
        echo "  <input id=\"password2\" name=\"password2\" type=\"password\" size=\"20\" value=\"$password2\" class=\"invalid\">\n";
        echo "</div>\n";
    } else {
        echo "<div class=\"control\">\n";
        echo "  <label for=\"password\">Password</label>\n";
        echo "  <input id=\"password\" name=\"password\" type=\"password\" size=\"20\" value=\"$password\">\n";
        echo "</div>\n";
        echo "<div class=\"control\">\n";
        echo "  <label for=\"password2\">Re-enter Password</label>\n";
        echo "  <input id=\"password2\" name=\"password2\" type=\"password\" size=\"20\" value=\"$password2\">\n";
        echo "</div>\n";
    }

    if ($errno != 0) {
        $errmsg = _strerror($errno);
        echo "<p class=\"error\">$errmsg</p>\n";
    }

    echo <<<TEXT
                <div class="control">
                    <button class="submit" type="submit">Register</button>
                </div>
            </form>
            <p>
                <a href="/">Log in to Existing Account</a>
            </p>
        </div>
    </div> <!-- panel -->
TEXT;
}

function print_textpanel() {
    echo <<<TEXT
    <div class="panel moretext">
        <p class="titlebar">Some more text</p>
        <div>
            <h2 class="heading">Short text</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        </div>
    </div> <!-- panel -->
TEXT;
}

function getv($t, $k) {
    return $t[$k] ?? null;
}
function sgetv($t, $k) {
    return $t[$k] ?? "";
}
# Set cookie so it can be immediately accessed.
function _setcookie($k, $v) {
    setcookie($k, $v);
    $_COOKIE[$k] = $v;
}
function _getcookie($k) {
    return $_COOKIE[$k] && null;
}
function strequals($s1, $s2) {
    return !strcmp($s1, $s2);
}

function init_db($dbfile) {
    $db = new SQLite3($dbfile);
    $sql = <<<TEXT
CREATE TABLE IF NOT EXISTS user (user_id INTEGER PRIMARY KEY NOT NULL, username TEXT NOT NULL, password TEXT NOT NULL);
TEXT;
    $db->exec($sql);
    return $db;
}
function register_user($db, $username, $pwd, $pwd2) {
    $username = trim($username);
    if (strlen($username) == 0)
        return E_USERNAME_REQUIRED;
    if (!strequals($pwd, $pwd2))
        return E_PASSWORD_NOMATCH;

    $sql = "SELECT username FROM user WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $username);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $stmt->close();
    if ($row)
        return E_USERNAME_EXISTS;

    $sql = "INSERT INTO user (username, password) VALUES (?, ?)";
    $pwdhash = password_hash($pwd, PASSWORD_DEFAULT);
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $pwdhash);
    $stmt->execute();
    $stmt->close();
    $userid = $db->lastInsertRowID();

    $tok = password_hash($username . $pwdhash, PASSWORD_DEFAULT);
    _setcookie("userid", $userid);
    _setcookie("tok", $tok);
    return 0;
}
function login_user($db, $username, $pwd) {
    $username = trim($username);
    if (strlen($username) == 0)
        return E_USERNAME_REQUIRED;

    $sql = "SELECT user_id, username, password FROM user WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $username);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $stmt->close();

    if (!$row || !password_verify($pwd, $row["password"]))
        return E_INVALID_USERNAMEPWD;

    $tok = password_hash($row["username"] . $row["password"], PASSWORD_DEFAULT);
    _setcookie("userid", $row["user_id"]);
    _setcookie("tok", $tok);
    return 0;
}
function logout_user() {
    _setcookie("userid", "");
    _setcookie("tok", "");
    return 0;
}
# Return currently logged in user, or null if invalid token or not logged in.
function get_session_user($db) {
    if (!isset($_COOKIE["userid"]) || !isset($_COOKIE["tok"]))
        return null;
    if ($_COOKIE["userid"] == "" || $_COOKIE["tok"] == "")
        return null;

    $sql = "SELECT user_id, username, password FROM user WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $_COOKIE["userid"]);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    $stmt->close();

    if (!$row)
        return null;
    if (!password_verify($row["username"] . $row["password"], $_COOKIE["tok"]))
        return null;
    return $row;
}
