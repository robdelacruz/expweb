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
    else if ($errno == E_CAT_NOT_FOUND)
        return "Category not found";
    else if ($errno == E_DESC_REQUIRED)
        return "Enter a description";
    else if ($errno == E_AMT_REQUIRED)
        return "Enter an amount";
    else if ($errno == E_CAT_REQUIRED)
        return "Enter a category";
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
    $user = get_session_user($db);

    if (strequals($p, "logout")) {
        logout_user();
        header(location_header());
        return;
    }
#    printf("\$submit: '%s'\n", $submit);
    if (strequals($submit, "")) {
        $errno = 0;
    } else if (strequals($submit, "login")) {
        $errno = login_user($db, trim($_POST["username"]), $_POST["password"]);
        if ($errno == 0) {
            header(location_header());
            return;
        }
    } else if (strequals($submit, "register")) {
        $errno = register_user($db, $_POST["username"], $_POST["password"], $_POST["password2"]);
        if ($errno == 0) {
            header(location_header());
            return;
        }
    } else if (strequals($submit, "addexp")) {
        $errno = add_exp($db, $user["user_id"], $_POST["desc"], $_POST["amt"], $_POST["cat"], $_POST["date"]);
        if ($errno == 0) {
            header(location_header());
            return;
        }
    } else {
        // unknown submit
        header(location_header());
        return;
    }

    if (strequals($cancel, "")) {
    } else if (strequals($p, "addexp")) {
        header(location_header());
        return;
    } else {
        // unknown cancel
        header(location_header());
        return;
    }

start_page:
    print_head();
    print_navbar($user);
    if (strequals($p, "login"))
        print_login_panel($errno);
    else if (strequals($p, "register"))
        print_register_panel($errno);
    else if (strequals($p, "welcome") || $user == null)
        print_welcome_panel();
    else if (strequals($p, "addexp"))
        print_addexp_panel($db, $user, $errno);
    else
        print_exp_panel($db, $user);
    print_foot();
}

# Return site url.
# siteurl("p=index&seq=1")
#   returns "/?p=index&seq=1", "/?view=mini&p=index&seq=1"
# siteurl() returns "/"
function siteurl($urlparams="") {
    $view = sgetv($_GET, "view");
    if (!$view) {
        if (!$urlparams)
            return "/";
        else
            return sprintf("/?%s", $urlparams);
    }

    if (!$urlparams)
        return sprintf("/?view=%s", $view);
    else
        return sprintf("/?view=%s&%s", $view, $urlparams);
}
function location_header($urlparams="") {
    return "Location: " . siteurl($urlparams);
}
function print_head() {
    print('<!DOCTYPE html>');

    $view = sgetv($_GET, "view");
    printf('<html class="%s">', $view);
    print('<head>');
    print('<meta charset="utf-8">');
    print('<meta name="viewport" content="width=device-width, initial-scale=1">');
    print('<title>Expense Buddy</title>');
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
    printf('    <li><a class="pill" href="%s">Expense Buddy</a></li>', siteurl());
    printf('    <li><a href="%s">About</a></li>', siteurl());
    print('  </ul>');

    print('<ul class="line-menu">');
    if (!$user) {
        printf('<li><a href="%s">login</a></li>', siteurl("p=login"));
    } else {
        printf('<li><a href="%s">%s</a></li>', siteurl(),  $user["username"]);
        printf('<li><a href="%s">logout</a></li>', siteurl("p=logout"));
    }
    print('</ul>');

    print('</div>');
}
function print_welcome_panel() {
    print('<div class="panel">');
    print('    <p class="titlebar">Welcome</p>');
    print('    <div>');
    print('        <h2 class="heading">Welcome to Expense Buddy</h2>');
    print('        <p>Expense Buddy Web lets you keep track of your daily expenses.</p>');
    printf('        <p>To start: <a href="%s">Log in</a> or <a href="%s">Create a new account</a></p>', siteurl("p=login"), siteurl("p=register"));
    print('    </div>');
    print('</div>');
}
function print_login_panel($errno=0) {
    print('<div class="panel login-panel">');
    print('    <div class="titlebar">Login</div>');
    print('    <div>');
    printf('       <form class="simpleform" action="%s" method="POST">', siteurl("p=login"));
    print('        <h2 class="heading">Login</h2>');

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
    printf('<p><a href="%s">Create New Account</a></p>', siteurl("p=register"));
    print('</div>');
    print('</div>');
}
function print_register_panel($errno=0) {
    print('<div class="panel register-panel">');
    print('    <div class="titlebar">Create New User</div>');
    print('    <div>');
    printf('        <form class="simpleform" action="%s" method="POST">', siteurl("p=register"));
    print('        <h2 class="heading">Create New User</h2>');

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
    printf('<p><a href="%s">Log in to existing account</a></p>', siteurl("p=login"));
    print('</div>');
    print('</div>');
}

function print_addexp_panel($db, $user, $errno=0) {
    $desc = trim(sgetv($_POST, "desc"));
    $samt = trim(sgetv($_POST, "amt"));
    $catname = trim(sgetv($_POST, "cat"));
    $date = trim(sgetv($_POST, "date"));
    if ($errno == 0)
        $date = date("Y-m-d");

    $cats = dbquery($db, "SELECT cat_id, name FROM cat WHERE user_id = ? ORDER BY name", $user["user_id"]);

    print('<div class="panel editexp-panel">');
    print('  <div class="titlebar">New Expense</div>');
    print('  <div>');
    printf('      <form class="entryform" action="%s" method="POST">', siteurl("p=addexp"));
    print('          <h2 class="heading">Enter Expense Details</h2>');
    print('          <div class="control">');
    print('              <label for="desc">Description</label>');
    if ($errno == E_DESC_REQUIRED)
        printf('<input id="desc" name="desc" type="text" size="25" value="%s" class="invalid">', $desc);
    else
        printf('<input id="desc" name="desc" type="text" size="25" value="%s">', $desc);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="amt">Amount</label>');
    if ($errno == E_AMT_REQUIRED)
        printf('<input id="amt" name="amt" type="number" pattern="^\d*(\.\d{0,2})?$" value="%s" class="invalid">', $samt);
    else
        printf('<input id="amt" name="amt" type="number" pattern="^\d*(\.\d{0,2})?$" value="%s">', $samt);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="cat">Category</label>');
    if ($errno == E_CAT_REQUIRED || $errno == E_CAT_NOT_FOUND)
        printf('<input id="cat" name="cat" type="text" list="catlist" size="10" value="%s" class="invalid">', $catname);
    else
        printf('<input id="cat" name="cat" type="text" list="catlist" size="10" value="%s">', $catname);
    print('              <datalist id="catlist">');
    for ($i=0; $i < count($cats); $i++) {
        $cat = $cats[$i];
        printf('<option value="%s">', $cat["name"]);
    }
    print('              </datalist>');
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="date">Date</label>');
    printf('              <input id="date" name="date" type="date" value="%s">', $date);
    print('          </div>');

    if ($errno != 0)
        printf('<p class="error">%s</p>', _strerror($errno));

    print('<div class="btnrow">');
    print('    <button class="submit" name="submit" type="submit" value="addexp">OK</button>');
    print('    <button name="cancel" value="addexp">Cancel</button>');
    print('</div>');
    print('</form>');
    print('</div>');
    print('</div>');
}

function print_exp_panel($db, $user) {
    $sql = "SELECT exp_id, date, desc, amt, cat.name AS catname FROM exp LEFT OUTER JOIN cat ON exp.cat_id = cat.cat_id AND exp.user_id = cat.user_id WHERE exp.user_id = ? ORDER BY date"; 
    $xps = dbquery($db, $sql, $user["user_id"]);

    print('<div class="panel explist-panel">');
    print('<div class="titlebar flex-between">');
    printf('    <form class="menuform" action="%s" method="GET">', siteurl());
    printf('        <input type="hidden" name="view" value="%s">', sgetv($_GET, "view"));
    print('        <select name="tab">');

    $tab = sgetv($_GET, "tab");
    if (strequals($tab, "") || strequals($tab, "exp"))
        print('<option value="exp" selected>Expenses</option>');
    else
        print('<option value="exp">Expenses</option>');
    if (strequals($tab, "cat"))
        print('<option value="cat" selected>Categories</option>');
    else
        print('<option value="cat">Categories</option>');
    if (strequals($tab, "ytd"))
        print('<option value="ytd" selected>Year-to-Date</option>');
    else
        print('<option value="ytd">Year-to-Date</option>');

    print('        </select>');
    print('        <input type="submit" value="Go">');
    print('    </form>');
    printf('    <a href="%s">Add Expense</a>', siteurl("p=addexp"));
    print('</div>');

    print('<div>');
    print('    <table class="expenses">');
    print('        <tbody>');
    print('        <tr>');
    print('            <th>Date</th>');
    print('            <th>Description</th>');
    print('            <th>Amount</th>');
    print('            <th>Category</th>');
    print('            <th>Record#</th>');
    print('        </tr>');

    for ($i=0; $i < count($xps); $i++) {
        $xp = $xps[$i];
        print('<tr>');
        printf('<td>%s</td>', date("Y-m-d", $xp["date"]));
        printf('<td>%s</td>', $xp["desc"]);
        printf('<td>%9.2f</td>', $xp["amt"]);
        printf('<td>%s</td>', $xp["catname"]);
        printf('<td><a href="%s">#%d</a></td>', siteurl(), $xp["exp_id"]);
        print('</tr>');
    }

    print('</tbody>');
    print('</table>');
    print('</div>');

    print('</div>');
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
    return $_COOKIE[$k] ?? null;
}
function strequals($s1, $s2) {
    return !strcmp($s1, $s2);
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

