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

main();

function main() {
    $db = init_db("test.db");

    $errno = 0;
    $submit = sgetv($_POST, "submit");
    $cancel = sgetv($_POST, "cancel");
    $p = sgetv($_GET, "p");

    if (strequals($p, "logout")) {
        logout_user();
        header("Location: /");
        return;
    }
    if (!strequals($submit, "")) {
        if (strequals($submit, "login")) {
            $errno = login_user($db, $_POST["username"], $_POST["password"]);
            if ($errno == 0) {
                header("Location: /");
                return;
            }
        } else if (strequals($submit, "register")) {
            $errno = register_user($db, $_POST["username"], $_POST["password"], $_POST["password2"]);
            if ($errno == 0) {
                header("Location: /");
                return;
            }
        } else if (strequals($submit, "addexp")) {
            header("Location: /");
            return;
        } else {
            // unknown submit
            header("Location: /");
            return;
        }
    }
    if (!strequals($cancel, "")) {
        if (strequals($p, "addexp")) {
            header("Location: /");
        } else {
            // unknown cancel
            header("Location: /");
        }
        return;
    }

    $user = get_session_user($db);

    print_head();
    print_navbar($user);
    print('<div class="grid-2col">');

    if (strequals($p, "login") || (strequals($submit, "login") && $errno != 0))
        print_loginpanel($errno);
    else if (strequals($p, "register") || (strequals($submit, "register") && $errno != 0))
        print_registerpanel($errno);
    else if (strequals($p, "addexp") || (strequals($submit, "addexp") && $errno != 0))
        print_addexp_panel($errno);
    else
        print_textpanel($user);

    print '</div>';
    print_foot();
}
function print_head() {
    print('<!DOCTYPE html>');
    print('<html>');
    print('<head>');
    print('<meta charset="utf-8">');
    print('<meta name="viewport" content="width=device-width, initial-scale=1">');
    print('<title>login</title>');
    print('<link rel="stylesheet" type="text/css" href="style.css">');
    print('</head>');
    print('<body>');
}
function print_foot() {
    print('</body>');
    print('</html>');
}

function print_navbar($user) {
    print('<div class="navbar">');

    print('  <ul class="line-menu">');
    print('    <li><a href="/">Expense Buddy Web</a></li>');
    print('    <li><a href="/">About</a></li>');
    print('  </ul>');

    print('<ul class="line-menu">');
    if (!$user) {
        print('<li><a href="/index.php?p=login">login</a></li>');
    } else {
        printf('<li><a href="/">%s</a></li>', $user["username"]);
        print('<li><a href="/?p=logout">logout</a></li>');
    }
    print('</ul>');

    print('</div>');
}
function print_loginpanel($errno=0) {
    print('<div class="panel login-panel">');
    print('    <p class="titlebar">Login</p>');
    print('    <div>');
    print('        <h2 class="heading">Login</h2>');
    print('        <form class="simpleform" action="/?p=login" method="POST">');

    $username = trim(sgetv($_POST, "username"));
    $password = sgetv($_POST, "password");

    print('<div class="control">');
    print('    <label for="username">Username</label>');
    if ($errno == E_INVALID_USERNAMEPWD || $errno == E_USERNAME_REQUIRED) {
        printf('<input id="username" name="username" type="text" size="20" value="%s" class="invalid">', $username);
    } else {
        printf('<input id="username" name="username" type="text" size="20" value="%s">', $username);
    }
    print('</div>');

    print('<div class="control">');
    print('<label for="password">Password</label>');
    if ($errno == E_INVALID_USERNAMEPWD)
        printf('<input id="password" name="password" type="password" size="20" value="%s" class="invalid">', $password);
    else
        printf('<input id="password" name="password" type="password" size="20" value="%s">', $password);
    print('</div>');

    if ($errno != 0)
        printf('<p class="error">%s</p>', _strerror($errno));

    print('<div class="btnrow">');
    print('    <button class="submit" name="submit" type="submit" value="login">Login</button>');
    print('</div>');
    print('</form>');
    print('<p><a href="/?p=register">Create New Account</a></p>');
    print('</div>');
    print('</div> <!-- panel -->');
}
function print_registerpanel($errno=0) {
    print('<div class="panel register-panel">');
    print('    <p class="titlebar">Create New User</p>');
    print('    <div>');
    print('        <h2 class="heading">Create New User</h2>');
    print('        <form class="simpleform" action="/?p=register" method="POST">');

    $username = trim(sgetv($_POST, "username"));
    $password = sgetv($_POST, "password");
    $password2 = sgetv($_POST, "password2");

    print('<div class="control">');
    print('<label for=\"username\">Username</label>');
    if ($errno == E_INVALID_USERNAMEPWD || $errno == E_USERNAME_REQUIRED || $errno == E_USERNAME_EXISTS)
        printf('<input id="username" name="username" type="text" size="20" value="%s" class="invalid">', $username);
    else
        printf('<input id="username" name="username" type="text" size="20" value="%s">', $username);
    print('</div>');

    if ($errno == E_PASSWORD_NOMATCH || $errno == E_INVALID_USERNAMEPWD) {
        print('<div class="control">');
        print('  <label for="password">Password</label>');
        printf('  <input id="password" name="password" type="password" size="20" value="%s" class="invalid">', $password);
        print('</div>');
        print('<div class="control">');
        print('  <label for="password2">Re-enter Password</label>');
        printf('  <input id="password2" name="password2" type="password" size="20" value="%s" class="invalid">', $password2);
        print('</div>');
    } else {
        print('<div class="control">');
        print('  <label for="password">Password</label>');
        printf('  <input id="password" name="password" type="password" size="20" value="%s">', $password);
        print('</div>');
        print('<div class="control">');
        print('  <label for="password2">Re-enter Password</label>');
        printf('  <input id="password2" name="password2" type="password" size="20" value="%s">', $password2);
        print('</div>');
    }

    if ($errno != 0)
        printf('<p class="error">%s</p>', _strerror($errno));

    print('<div class="btnrow">');
    print('    <button class="submit" name="submit" type="submit" value="register">Register</button>');
    print('</div>');
    print('</form>');
    print('<p><a href="/?p=login">Log in to existing account</a></p>');
    print('</div>');
    print('</div> <!-- panel -->');
}

function print_addexp_panel($errno=0) {
    $desc = trim(sgetv($_POST, "desc"));
    $amt = trim(sgetv($_POST, "amount"));
    $catname = trim(sgetv($_POST, "cat"));
    $date = trim(sgetv($_POST, "date"));
    if (strlen($date) == 0)
        $date = date("Y-m-d");

    print('<div class="panel editexp-panel">');
    print('  <p class="titlebar">New Expense</p>');
    print('  <div>');
    print('      <form class="entryform" action="/?p=addexp" method="POST">');
    print('          <h2 class="heading">Enter Expense Details</h2>');
    print('          <div class="control">');
    print('              <label for="desc">Description</label>');
    printf('              <input id="desc" name="desc" type="text" size="25" value="%s">', $desc);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="amt">Amount</label>');
    printf('              <input id="amt" name="amt" type="number" value="%s">', $amt);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="cat">Category</label>');
    printf('              <input id="cat" name="cat" type="text" list="catlist" size="10" value="%s">', $catname);
    print('              <datalist id="catlist">');
    print('                  <option value="coffee">');
    print('              </datalist>');
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="date">Description</label>');
    printf('              <input id="date" name="date" type="date" value="%s">', $date);
    print('          </div>');

    if ($errno != 0)
        printf('<p class="error">%s</p>', _strerror($errno));

    print('<div class="btnrow">');
    print('    <button class="submit" name="submit" type="submit" value="addexp">OK</button>');
    print('    <button class="submit" name="cancel" value="addexp">Cancel</button>');
    print('</div>');
    print('</form>');
    print('</div>');
    print('</div> <!-- panel -->');
}

function print_textpanel($user) {
    echo <<<TEXT
    <div class="panel expenses">
        <p class="titlebar">Welcome</p>
        <div>
            <div class="menubar">
                <ul class="line-menu">
                    <li class="sel"><a href="/" class="sel">Expenses</a></li>
                    <li><a href="/">Categories</a></li>
                    <li><a href="/">Year-to-date</a></li>
                </ul>
                <ul class="line-menu">
                    <li><a class="action" href="/?p=addexp">Add Expense</a></li>
                </ul>
            </div>
            <h2 class="heading">Welcome to Expense Buddy</h2>
            <p>Use Expense Buddy to keep track of your daily expenses.</p>
TEXT;
    if (!$user)
        echo "<p>To start: <a href=\"/?p=login\">Log In</a> or <a href=\"/?p=register\">Create a New Account</a>.</p>";
    echo <<<TEXT
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
CREATE TABLE IF NOT EXISTS cat (cat_id INTEGER PRIMARY KEY NOT NULL, name TEXT NOT NULL, user_id INTEGER NOT NULL);
CREATE TABLE IF NOT EXISTS exp (exp_id INTEGER PRIMARY KEY NOT NULL, date INTEGER, desc TEXT NOT NULL DEFAULT '', amt REAL NOT NULL DEFAULT 0.0, cat_id INTEGER NOT NULL DEFAULT 0, user_id INTEGER NOT NULL);
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

function add_exp($db, $xp) {
    $sql = "INSERT INTO exp (date, time, desc, amt, catid) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $xp["date"]);
    $stmt->bindParam(2, $xp["time"]);
    $stmt->bindParam(3, $xp["date"]);
    $stmt->bindParam(4, $xp["amt"]);
    $stmt->bindParam(5, $xp["catid"]);
    $stmt->execute();
    $stmt->close();
    $expid = $db->lastInsertRowID();

    return 0;
}

