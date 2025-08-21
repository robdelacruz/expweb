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
        header("Location: " . siteurl());
        return;
    }
    if (strequals($submit, "")) {
        $errno = 0;
    } else if (strequals($p, "login")) {
        $errno = login_user($db, trim($_POST["username"]), $_POST["password"]);
        if ($errno == 0) {
            header("Location: " . siteurl());
            return;
        }
    } else if (strequals($p, "register")) {
        $errno = register_user($db, $_POST["username"], $_POST["password"], $_POST["password2"]);
        if ($errno == 0) {
            header("Location: " . siteurl());
            return;
        }
    } else if (strequals($p, "addexp")) {
        $errno = add_exp($db, $user["user_id"], $_POST["desc"], $_POST["amt"], $_POST["cat"], $_POST["date"]);
        if ($errno == 0) {
            header("Location: " . siteurl_get("p=viewexp"));
            return;
        }
    } else if (strequals($p, "editexp")) {
        $expid = intval(sgetv($_GET, "expid"));
        $errno = edit_exp($db, $user["user_id"], $expid, $_POST["desc"], $_POST["amt"], $_POST["cat"], $_POST["date"]);
        if ($errno == 0) {
            header("Location: " . siteurl_get("p=viewexp"));
            return;
        }
    } else {
        // unknown submit
        header("Location: " . siteurl_get());
        return;
    }

    if (!strequals($cancel, "")) {
        if (strequals($p, "addexp") || strequals($p, "editexp")) {
            header("Location: " . siteurl_get("p=viewexp"));
            return;
        }
        header("Location: " . siteurl());
        return;
    }

start_page:
    print_head();
    print_navbar($user);
    if (strequals($p, "login")) {
        print('<div class="maingrid">');
        print_login_sidebar();
        print_login_panel($errno);
        print('</div>'); # maingrid
    } else if (strequals($p, "register")) {
        print('<div class="maingrid">');
        print_login_sidebar();
        print_register_panel($errno);
        print('</div>'); # maingrid
    } else if (strequals($p, "welcome") || $user == null) {
        print('<div class="maingrid">');
        print_login_sidebar();
        print_login_panel($errno);
        print('</div>'); # maingrid
    } else if (strequals($p, "addexp")) {
        print('<div class="maingrid">');
        print_view_sidebar($db, $user);
        print_addexp_panel($db, $user, $errno);
        print('</div>'); # maingrid
    } else if (strequals($p, "editexp")) {
        print('<div class="maingrid">');
        print_view_sidebar($db, $user);
        print_editexp_panel($db, $user, $errno);
        print('</div>'); # maingrid
    } else if (strequals($p, "viewexp")) {
        print('<div class="maingrid">');
        print_view_sidebar($db, $user);
        print_view_panel($db, $user);
        print('</div>'); # maingrid
    } else {
        print('<div class="maingrid">');
        print_view_sidebar($db, $user);
        print_view_panel($db, $user);
        print('</div>'); # maingrid
    }
    print_foot();
}

# Given: "p=login&view=mini"
# Returns: ["p" => "login", "view" => "mini"]
function parse_querystring($querystring, $initqs=[]) {
    $qs = $initqs;
    $params = explode("&", $querystring);
    foreach ($params as $param) {
        $kv = explode("=", $param);
        if (count($kv) < 2)
            continue;
        $qs[$kv[0]] = $kv[1];
    }
    return $qs;
}

function siteurl($initqs=[], $querystring="") {
    $view = sgetv($_GET, "view");
    if ($view)
        $initqs["view"] = $view;
    $qs = parse_querystring($querystring, $initqs);

    $url = "/";
    $i=0;
    foreach ($qs as $k => $v) {
        if ($i == 0)
            $url .= "?$k=$v";
        else
            $url .= "&$k=$v";
        $i++;
    }
    return $url;
}
function siteurl_get($querystring="") {
    return siteurl($_GET, $querystring);
}
function print_head() {
    print('<!DOCTYPE html>');

    $view = sgetv($_GET, "view");
    $wp = "";
#    if (!strequals($view, "mini"))
#        $wp = sprintf("wp%d", rand(1,5));

    printf('<html class="%s %s">', $wp, $view);
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
    printf('    <li><a class="logo pill" href="%s">Expense Buddy</a></li>', siteurl());
    printf('    <li><a href="%s">About</a></li>', siteurl());
    print('  </ul>');

    print('<ul class="line-menu">');
    if (!$user) {
        printf('<li><a href="%s">login</a></li>', siteurl_get("p=login"));
    } else {
        printf('<li><a href="%s">%s</a></li>', siteurl_get(), $user["username"]);
        printf('<li><a href="%s">logout</a></li>', siteurl([], "p=logout"));
    }
    print('</ul>');

    print('</div>');
}
function print_welcome_panel() {
    print('<div class="maingrid">');
    print_login_sidebar();
    print_login_panel(0);

/*
    print('<div class="panel">');
    print('    <p class="titlebar">Welcome</p>');
    print('    <div class="panel_body">');
    print('        <h2 class="heading">Welcome to Expense Buddy</h2>');
    print('        <p>Expense Buddy Web lets you keep track of your daily expenses.</p>');
    printf('        <p>To start: <a class="bold" href="%s">Log in</a> or <a class="bold" href="%s">Create a new account</a></p>', siteurl([], "p=login"), siteurl([], "p=register"));
    print('    </div>');
    print('</div>');
*/

    print('</div>'); # maingrid
}
function print_login_panel($errno=0) {
    print('<div class="panel login-panel">');
    print('    <div class="titlebar">Login</div>');
    print('    <div class="panel_body">');
    printf('       <form class="simpleform" action="%s" method="POST">', siteurl_get("p=login"));
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
    print('    <button class="submit" name="submit" type="submit" value="submit">Login</button>');
    print('</div>');
    print('<div class="control">');
    printf('<p><a href="%s">Create New Account</a></p>', siteurl_get("p=register"));
    print('</div>');

    print('</form>');
    print('</div>');
    print('</div>');
}
function print_register_panel($errno=0) {
    print('<div class="panel register-panel">');
    print('    <div class="titlebar">Create New User</div>');
    print('    <div class="panel_body">');
    printf('        <form class="simpleform" action="%s" method="POST">', siteurl_get("p=register"));
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
    print('    <button class="submit" name="submit" type="submit" value="submit">Register</button>');
    print('</div>');

    print('<div class="control">');
    printf('<p><a href="%s">Log in to existing account</a></p>', siteurl_get("p=login"));
    print('</div>');

    print('</form>');
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
    print('  <div class="panel_body">');
    printf('      <form class="entryform" action="%s" method="POST">', siteurl_get("p=addexp"));
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
        printf('<input id="amt" name="amt" type="number" step="0.01" value="%s" class="invalid">', $samt);
    else
        printf('<input id="amt" name="amt" type="number" step="0.01" min="0" value="%s">', $samt);
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
    print('    <button class="submit" name="submit" type="submit" value="submit">OK</button>');
    print('    <button name="cancel" type="submit" value="cancel">Cancel</button>');
    print('</div>');
    print('</form>');
    print('</div>');
    print('</div>');
}
function print_editexp_panel($db, $user, $errno=0) {
    $expid = intval(sgetv($_GET, "expid"));
    if ($expid == 0) {
        print_addexp_panel($db, $user, $errno);
        return;
    }

    $sql = "SELECT exp_id, date, desc, amt, cat.name AS catname FROM exp LEFT OUTER JOIN cat ON exp.cat_id = cat.cat_id AND exp.user_id = cat.user_id WHERE exp.user_id = ? AND exp.exp_id = ?"; 
    $xp = dbquery_one($db, $sql, $user["user_id"], $expid);
    if (!$xp) {
        print_addexp_panel($db, $user, $errno);
        return;
    }
    $isodate = date("Y-m-d", $xp["date"]);

    if ($errno > 0) {
        $xp["desc"] = trim(sgetv($_POST, "desc"));
        $xp["amt"] = floatval(trim(sgetv($_POST, "amt")));
        $xp["catname"] = trim(sgetv($_POST, "cat"));
        $isodate = trim(sgetv($_POST, "date"));
    }

    $cats = dbquery($db, "SELECT cat_id, name FROM cat WHERE user_id = ? ORDER BY name", $user["user_id"]);

    print('<div class="panel editexp-panel">');
    print('  <div class="titlebar">Edit Expense</div>');
    print('  <div class="panel_body">');
    $editexp_params = sprintf("p=editexp&expid=%d", $expid);
    printf('      <form class="entryform" action="%s" method="POST">', siteurl_get($editexp_params));
    print('          <h2 class="heading">Edit Expense Details</h2>');
    print('          <div class="control">');
    print('              <label for="desc">Description</label>');
    if ($errno == E_DESC_REQUIRED)
        printf('<input id="desc" name="desc" type="text" size="25" value="%s" class="invalid">', $xp["desc"]);
    else
        printf('<input id="desc" name="desc" type="text" size="25" value="%s">', $xp["desc"]);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="amt">Amount</label>');
    if ($errno == E_AMT_REQUIRED)
        printf('<input id="amt" name="amt" type="number" step="0.01" value="%.2f" class="invalid">', $xp["amt"]);
    else
        printf('<input id="amt" name="amt" type="number" step="0.01" min="0" value="%.2f">', $xp["amt"]);
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="cat">Category</label>');
    if ($errno == E_CAT_REQUIRED || $errno == E_CAT_NOT_FOUND)
        printf('<input id="cat" name="cat" type="text" list="catlist" size="10" value="%s" class="invalid">', $xp["catname"]);
    else
        printf('<input id="cat" name="cat" type="text" list="catlist" size="10" value="%s">', $xp["catname"]);
    print('              <datalist id="catlist">');
    for ($i=0; $i < count($cats); $i++) {
        $cat = $cats[$i];
        printf('<option value="%s">', $cat["name"]);
    }
    print('              </datalist>');
    print('          </div>');
    print('          <div class="control">');
    print('              <label for="date">Date</label>');
    printf('              <input id="date" name="date" type="date" value="%s">', $isodate);
    print('          </div>');

    if ($errno != 0)
        printf('<p class="error">%s</p>', _strerror($errno));

    print('<div class="btnrow">');
    print('    <button class="submit" name="submit" type="submit" value="submit">OK</button>');
    print('    <button name="cancel" type="submit" value="cancel">Cancel</button>');
    print('</div>');
    print('</form>');
    print('</div>');
    print('</div>');
}

function print_view_panels($db, $user) {
    print('<div class="maingrid">');
    print_view_sidebar($db, $user);
    print_view_panel($db, $user);
    print('</div>'); # maingrid
}

function print_login_sidebar() {
    print('<div class="panel sidebar-panel">');
    print('    <div class="titlebar">Current View</div>');
    print('    <div class="panel_body vbar">');
    print('    <ul class="action-links">');
    print('        <li><a href="/">Login</a></li>');
    print('        <li><a href="/">Create Account</a></li>');
    print('    </ul>');
    print('    </div>'); # panel_body
    print('</div>'); # sidebar-panel
}

function print_view_sidebar($db, $user) {
    $tab = sgetv($_GET, "tab");
    if (strequals($tab, ""))
        $tab = "exp";
    $period = sgetv($_GET, "period");
    if (strequals($period, ""))
        $period = "month";
    $catid = intval(sgetv($_GET, "catid"));
    $month = sgetv($_GET, "month");
    $year = sgetv($_GET, "year");
    $day = sgetv($_GET, "day");
    $startdate = sgetv($_GET, "startdate");
    $enddate = sgetv($_GET, "enddate");

    $today_isodate = date("Y-m-d", time());
    if (strlen($month) == 0)
        $month = substr($today_isodate, 0, 7);
    if (strlen($year) == 0)
        $year = substr($today_isodate, 0, 4);
    if (strlen($day) == 0)
        $day = $today_isodate;
    if (strlen($startdate) == 0) {
        $y = 0;
        $m = 0;
        $d = 0;
        date_to_cal(time(), $y, $m, $d);
        $startdate = date("Y-m-d", date_from_cal($y, $m, 1));
    }
    if (strlen($enddate) == 0) {
        $y = 0;
        $m = 0;
        $d = 0;
        date_to_cal(date_from_iso($startdate), $y, $m, $d);
        $enddate = date("Y-m-d", date_prev_day(date_next_month(date_from_cal($y, $m, 1))));
    }

    print('<div class="panel sidebar-panel">');

    print('<div class="titlebar">Current View</div>');
    print('<div class="panel_body vbar vsep">');

    printf('<form class="simpleform" method="GET" action="%s">', siteurl());
    print_tab_hidden_inputs();
    print('    <div class="control">');
    print('        <label for="tab">View Items</label>');
    print('        <div class="gobar">');
    print('            <select name="tab">');
    if (strequals($tab, "exp"))
        print('<option selected value="exp">Expenses</option>');
    else
        print('<option value="exp">Expenses</option>');

    if (strequals($tab, "cat"))
        print('<option selected  value="cat">Categories</option>');
    else
        print('<option value="cat">Categories</option>');

    if (strequals($tab, "ytd"))
        print('<option selected value="ytd">Year-to-Date</option>');
    else
        print('<option value="ytd">Year-to-Date</option>');
    print('            </select>');
    print('            <input class="go" type="submit" value="Go">');
    print('        </div>');
    print('    </div>');
    print('</form>');

    if (strequals($tab, "exp")) {
        $sql = "SELECT cat_id, name FROM cat WHERE user_id = ? ORDER BY name";
        $cats = dbquery($db, $sql, $user["user_id"]);
        if (count($cats) == 0)
            goto no_cats;
        printf('<form class="simpleform" method="GET" action="%s">', siteurl());
        print_tab_hidden_inputs();
        print('    <div class="control">');
        print('        <label for="catid">Show Categories</label>');
        print('        <div class="gobar">');
        print('            <select name="catid">');
        if ($catid == 0)
            printf('<option selected value="0">All</option>');
        else
            printf('<option value="0">All</option>');
        for ($i=0; $i < count($cats); $i++) {
            $cat = $cats[$i];
            if ($catid == $cat["cat_id"])
                printf('<option selected value="%d">%s</option>', $cat["cat_id"], $cat["name"]);
            else
                printf('<option value="%d">%s</option>', $cat["cat_id"], $cat["name"]);
        }
        print('            </select>');
        print('            <input class="go" type="submit" value="Go">');
        print('        </div>');
        print('    </div>');
        print('</form>');
    }
no_cats:
    print('</div>');

    print('<div class="titlebar">Date Range</div>');
    print('<div class="panel_body vbar vsep">');

    if (!strequals($tab, "ytd")) {
        printf('<form class="simpleform" action="%s" method="GET">', siteurl());
        print_daterange_hidden_inputs();
        print('<input name="period" type="hidden" value="month">');
        print('<div class="control">');
        print('    <label for="month">Show Month</label>');
        print('    <div class="gobar">');
        printf('       <input id="month" name="month" type="month" value="%s">', $month);
        print('        <input class="go" type="submit" value="Go">');
        print('    </div>');
        print('</div>');
        print('</form>');
    }

    printf('<form class="simpleform" action="%s" method="GET">', siteurl());
    print_daterange_hidden_inputs();
    print('<input name="period" type="hidden" value="year">');
    print('<div class="control">');
    print('    <label for="year">Show Year</label>');
    print('    <div class="gobar">');
    printf('       <input id="year" name="year" type="number" step="1" size="4"  value="%s">', $year);
    print('        <input class="go" type="submit" value="Go">');
    print('    </div>');
    print('</div>');
    print('</form>');

    if (!strequals($tab, "ytd")) {
        printf('<form class="simpleform" action="%s" method="GET">', siteurl());
        print_daterange_hidden_inputs();
        print('<input name="period" type="hidden" value="day">');
        print('<div class="control">');
        print('    <label for="day">Show Day</label>');
        print('    <div class="gobar">');
        printf('       <input id="day" name="day" type="date" value="%s">', $day);
        print('        <input class="go" type="submit" value="Go">');
        print('    </div>');
        print('</div>');
        print('</form>');
    }

    if (!strequals($tab, "ytd")) {
        printf('<form class="simpleform" action="%s" method="GET">', siteurl());
        print_daterange_hidden_inputs();
        print('<input name="period" type="hidden" value="range">');
        print('<div class="control">');
        print('    <label for="startdate">Start Date</label>');
        printf('   <input id="startdate" name="startdate" type="date" value="%s">', $startdate);
        print('</div>');
        print('<div class="control">');
        print('    <label for="enddate">End Date</label>');
        printf('   <input id="enddate" name="enddate" type="date" value="%s">', $enddate);
        print('</div>');
        print('<div class="btnrow">');
        print('    <input class="go" type="submit" value="Go">');
        print('</div>');
        print('</form>');
    }

    print('</div>'); # vbar
    print('</div>'); # sidebar-panel
}
function print_daterange_hidden_inputs() {
    printf('<input name="view" type="hidden" value="%s">', sgetv($_GET, "view"));
    printf('<input name="p" type="hidden" value="viewexp">');
    printf('<input name="tab" type="hidden" value="%s">', sgetv($_GET, "tab"));
    printf('<input name="catid" type="hidden" value="%s">', sgetv($_GET, "catid"));
}
function print_tab_hidden_inputs() {
    foreach ($_GET as $k => $v) {
        if (strequals($k, "p") || strequals($k, "tab") || strequals($k, "catid"))
            continue;
        printf('<input name="%s" type="hidden" value="%s">', $k, $v);
    }
}

# GET parameters:
# tab=exp|cat|ytd
#
# period=month
# month=2025-07
# 
# period=year
# year=2025
#
# period=day
# day=2025-07-31
#
# period=range
# startdate=2025-07-01
# enddate=2025-07-31
function print_view_panel($db, $user) {
    # Normalize inputs into startdt and enddt.
    # Ex. For period=month, month=2025-07, set startdt/enddt to 2025-07-01, 2025-08-01
    #     For period=year, year=2025, set startdt/enddt to 2025-01-01, 2026-01-01
    #     For period=day, day=2025-07-31, set startdt/enddt to 2025-07-31, 2025-08-01
    #     For period=range, use startdate,enddate
    $tab = sgetv($_GET, "tab");
    $period = sgetv($_GET, "period");

    $startdt = 0;
    $enddt = 0;
    $range_caption = "";
    if (strequals($period, "month")) {
        $month = sgetv($_GET, "month");
        $startdt = date_from_iso($month);
        $enddt = date_next_month($startdt);
        $range_caption = date("F Y", $startdt);
    } else if (strequals($period, "year")) {
        $year = sgetv($_GET, "year");
        $startdt = date_from_iso($year);
        $enddt = date_next_year($startdt);
        $range_caption = date("Y", $startdt);
    } else if (strequals($period, "day")) {
        $day = sgetv($_GET, "day");
        $startdt = date_from_iso($day);
        $enddt = date_next_day($startdt);
        $range_caption = date("D M j Y", $startdt);
    } else if (strequals($period, "range")) {
        $startdate = sgetv($_GET, "startdate");
        $enddate = sgetv($_GET, "enddate");
        $startdt = date_from_iso($startdate);
        $enddt = date_next_day(date_from_iso($enddate));
        $range_caption = sprintf("%s to %s", date("Y-m-d", $startdt), date("Y-m-d", date_prev_day($enddt)));
    } else {
        # Default to current month
        $year = 0;
        $month = 0;
        $day = 0;
        date_to_cal(time(), $year, $month, $day);
        $startdt = date_from_cal($year, $month, 1);
        $enddt = date_next_month($startdt);
        $range_caption = date("F Y", $startdt);
    }

    $catid = intval(sgetv($_GET, "catid"));
    if ($catid != 0) {
        $sql = "SELECT name FROM cat WHERE user_id = ? AND cat_id = ?";
        $cat = dbquery_one($db, $sql, $user["user_id"], $catid);
        if ($cat)
            $range_caption = sprintf("%s - %s", $range_caption, $cat["name"]); 
    }

    if (strequals($tab, "exp"))
        print_exp_view_panel($db, $user, $startdt, $enddt, $range_caption);
    else if (strequals($tab, "cat"))
        print_cat_view_panel($db, $user, $startdt, $enddt, $range_caption);
    else if (strequals($tab, "ytd")) {
        date_to_cal($startdt, $year, $month, $day);
        print_ytd_view_panel($db, $user, $year);
    } else
        print_exp_view_panel($db, $user, $startdt, $enddt, $range_caption);
}

function print_exp_view_panel($db, $user, $startdt, $enddt, $range_caption) {
    $catid = intval(sgetv($_GET, "catid"));

    if ($catid == 0) {
        $sql = "SELECT COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ?"; 
        $xptotal = dbquery_one($db, $sql, $user["user_id"], $startdt, $enddt);
    } else {
        $sql = "SELECT COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ? AND cat_id = ?"; 
        $xptotal = dbquery_one($db, $sql, $user["user_id"], $startdt, $enddt, $catid);
    }
    $numitems = 0;
    $amttotal = 0.0;
    if ($xptotal) {
        $numitems = $xptotal["numitems"];
        $amttotal = $xptotal["amttotal"];
    }

    print('<div class="panel view-panel">');

    print('<div class="titlebar flex-between">');
    printf('<p>Expenses - %s</p>', $range_caption);
    print('</div>');

    print('<div class="panel_body">');

    print('<div class="hbar infobar flex-between">');
    print('    <form class="gobar">');
    print('        <input name="search" type="text" placeholder="Search">');
    print('        <input class="go" type="submit" value="Go">');
    print('    </form>');
    print('    <div class="hbar">');
    if ($numitems == 1)
        printf('<p>Total: %s (%d item)</p>', number_format($amttotal, 2), $numitems);
    else
        printf('<p>Total: %s (%d items)</p>', number_format($amttotal, 2), $numitems);
    printf('       <a href="%s" class="pill smallpad green box">+</a>', siteurl_get("p=addexp"));
    print('    </div>');
    print('</div>');

    if ($catid == 0) {
        $sql = "SELECT exp_id, date, desc, amt, cat.name AS catname FROM exp LEFT OUTER JOIN cat ON exp.cat_id = cat.cat_id AND exp.user_id = cat.user_id WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ? ORDER BY date DESC"; 
        $xps = dbquery($db, $sql, $user["user_id"], $startdt, $enddt);
    } else {
        $sql = "SELECT exp_id, date, desc, amt, cat.name AS catname FROM exp LEFT OUTER JOIN cat ON exp.cat_id = cat.cat_id AND exp.user_id = cat.user_id WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ? AND exp.cat_id = ? ORDER BY date DESC"; 
        $xps = dbquery($db, $sql, $user["user_id"], $startdt, $enddt, $catid);
    }
    if (count($xps) == 0) {
        print('<p class="infobar italic">(No Expenses)</p>');
        goto view_panel_end;
    }

    print('<table class="expenses">');
    print('<tbody>');
    print('    <tr>');
    print('        <th>Date</th>');
    print('        <th>Description</th>');
    print('        <th>Amount</th>');
    print('        <th>Category</th>');
    print('        <th></th>');
    print('    </tr>');

    for ($i=0; $i < count($xps); $i++) {
        $xp = $xps[$i];
        print('<tr>');
        printf('<td>%s</td>', date("Y-m-d", $xp["date"]));
        printf('<td>%s</td>', $xp["desc"]);
        printf('<td>%s</td>', number_format($xp["amt"], 2));
        printf('<td>%s</td>', $xp["catname"]);
        $editexp_params = sprintf("p=editexp&expid=%d", $xp["exp_id"]);
        printf('<td><a href="%s"><img class="icon" src="hero-chevron-double-right.svg"></a></td>', siteurl_get($editexp_params));
        print('</tr>');
    }

    print('</tbody>');
    print('</table>');

view_panel_end:
    print('</div>'); # panel_body
    print('</div>'); # view-panel

}

function print_cat_view_panel($db, $user, $startdt, $enddt, $range_caption) {
    $sql = "SELECT COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ?"; 
    $xptotal = dbquery_one($db, $sql, $user["user_id"], $startdt, $enddt);
    $numitems = 0;
    $amttotal = 0.0;
    if ($xptotal) {
        $numitems = $xptotal["numitems"];
        $amttotal = $xptotal["amttotal"];
    }

    print('<div class="panel view-panel">');

    print('<div class="titlebar flex-between">');
    printf('<p>Category Totals - %s</p>', $range_caption);
    print('</div>');

    print('<div class="panel_body">');

    print('<div class="hbar infobar flex-between">');
    print('    <form class="gobar">');
    print('        <input name="search" type="text" placeholder="Search">');
    print('        <input class="go" type="submit" value="Go">');
    print('    </form>');
    print('    <div class="hbar">');
    if ($numitems == 1)
        printf('<p>Total: %s (%d item)</p>', number_format($amttotal, 2), $numitems);
    else
        printf('<p>Total: %s (%d items)</p>', number_format($amttotal, 2), $numitems);
    printf('       <a href="%s" class="pill smallpad green box">+</a>', siteurl_get("p=addexp"));
    print('    </div>');
    print('</div>');

    $sql = "SELECT cat.cat_id AS catid, cat.name AS catname, COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp LEFT OUTER JOIN cat ON exp.cat_id = cat.cat_id WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ? GROUP BY cat.cat_id ORDER BY amttotal DESC";
    $cats = dbquery($db, $sql, $user["user_id"], $startdt, $enddt);

    if (count($cats) == 0) {
        print('<p class="infobar italic">(No Expenses)</p>');
        goto view_panel_end;
    }

    print('<table class="categories">');
    print('<tbody>');
    print('    <tr>');
    print('        <th>Category</th>');
    print('        <th>Total</th>');
    print('    </tr>');

    for ($i=0; $i < count($cats); $i++) {
        $cat = $cats[$i];
        print('<tr>');
        $qs = sprintf("tab=exp&catid=%d", $cat["catid"]);
        printf('<td><span class="bold">%s</span> <span class="smalltext"><a href="%s">(%d)</a></span></td>', $cat["catname"], siteurl_get($qs), $cat["numitems"]);
        printf('<td>%s</td>', number_format($cat["amttotal"], 2));
        print('</tr>');
    }

    print('</tbody>');
    print('</table>');

view_panel_end:
    print('</div>'); # panel_body
    print('</div>'); # view-panel

}

function print_ytd_view_panel($db, $user, $year) {
    $startdt = date_from_cal($year, 1, 1);
    $enddt = date_next_year($startdt);
    $sql = "SELECT COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ?";
    $xptotal = dbquery_one($db, $sql, $user["user_id"], $startdt, $enddt);
    $year_numitems = 0;
    $year_amttotal = 0.0;
    if ($xptotal) {
        $year_numitems = $xptotal["numitems"];
        $year_amttotal = $xptotal["amttotal"];
    }

    print('<div class="panel view-panel">');

    print('<div class="titlebar flex-between">');
    printf('<p>%d Year-to-Date</p>', $year);
    print('</div>');

    print('<div class="panel_body">');

    print('<table class="ytd">');
    print('<tbody>');
    print('    <tr>');
    printf('       <th>%s</th>', $year);
    print('        <th>Total</th>');
    print('    </tr>');

    for ($i=1; $i <= 12; $i++) {
        $startdt = date_from_cal($year, $i, 1);
        $enddt = date_next_month($startdt);
        $sql = "SELECT COUNT(*) AS numitems, SUM(amt) AS amttotal FROM exp WHERE exp.user_id = ? AND exp.date >= ? AND exp.date < ?";
        $xptotal = dbquery_one($db, $sql, $user["user_id"], $startdt, $enddt);
        $numitems = 0;
        $amttotal = 0.0;
        if ($xptotal) {
            $numitems = $xptotal["numitems"];
            $amttotal = $xptotal["amttotal"];
        }
        print('<tr>');
        if ($numitems > 0) {
            $qs = sprintf("tab=exp&period=month&month=%d-%02d", $year, $i);
            printf('<td><span class="bold">%s</span> <span class="smalltext"><a href="%s">(%d)</a></span></td>', date("F", $startdt), siteurl_get($qs), $numitems);
        } else
            printf('<td><span class="bold">%s</span></td>', date("F", $startdt));
        printf('<td>%s</td>', number_format($amttotal, 2));
        print('</tr>');
    }
    print('<tr class="total-row">');
    $qs = sprintf("tab=exp&period=year&year=%d", $year);
    printf('<td><span class="bold">Total</span> <span class="smalltext"><a href="%s">(%d)</a></span></td>', siteurl_get($qs), $year_numitems);
    printf('<td>%s</td>', number_format($year_amttotal, 2));
    print('</tr>');

    print('</tbody>');
    print('</table>');

    print('</div>'); # panel_body
    print('</div>'); # view-panel
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
function dbupdate($db, $sql, ...$params) {
    $stmt = $db->prepare($sql);
    for ($i=0; $i < count($params); $i++)
        $stmt->bindParam($i+1, $params[$i]);
    $res = $stmt->execute();
    $stmt->close();
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
function edit_exp($db, $userid, $expid, $desc, $samt, $catname, $date) {
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

    $sql = "UPDATE exp SET date = ?, desc = ?, amt = ?, cat_id = ?, user_id = ? WHERE exp_id = ?";
    $expid = dbupdate($db, $sql, $dt, $desc, $amt, $catid, $userid, $expid);
    return 0;
}

# Date functions
function date_from_cal($year, $month, $day) {
    $dt = mktime(0, 0, 0, $month, $day, $year);
    if (!$dt)
        $dt = time();
    return $dt;
}
function date_to_cal($dt, &$year, &$month, &$day) {
    $tm = localtime($dt, true);
    $year = $tm["tm_year"] + 1900;
    $month = $tm["tm_mon"]+1;
    $day = $tm["tm_mday"];
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
function date_prev_month($dt) {
    $year = 0;
    $month = 0;
    $day = 0;
    date_to_cal($dt, $year, $month, $day);
    if ($month == 1)
        return date_from_cal($year-1, 12, $day);
    else
        return date_from_cal($year, $month-1, $day);
}
function date_next_month($dt) {
    $year = 0;
    $month = 0;
    $day = 0;
    date_to_cal($dt, $year, $month, $day);
    if ($month == 12)
        return date_from_cal($year+1, 1, $day);
    else
        return date_from_cal($year, $month+1, $day);
}
function date_prev_day($dt) {
    return $dt - 24*60*60;
}
function date_next_day($dt) {
    return $dt + 24*60*60;
}
function date_prev_year($dt) {
    $year = 0;
    $month = 0;
    $day = 0;
    date_to_cal($dt, $year, $month, $day);
    return date_from_cal($year-1, $month, $day);
}
function date_next_year($dt) {
    $year = 0;
    $month = 0;
    $day = 0;
    date_to_cal($dt, $year, $month, $day);
    return date_from_cal($year+1, $month, $day);
}

