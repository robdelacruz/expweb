<?php
$submitval = $_POST["submit"] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>login</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <form class="simpleform" style="width: 200px;" action="/t2.php" method="POST">
        <h2 class="heading">Test Form</h2>
        <p>submitval: <span style="font-weight: 700">
<?php
    print("$submitval");
?>
        </span></p>
        <div class="control">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" size="20" value="">
        </div>
        <div class="control">
            <label for="password">Password</label>
            <input class="invalid" id="password" name="password" type="password" size="20" value="">
        </div>
        <div class="btnrow">
            <button class="submit" name="submit" type="submit" value="login">Login</button>
        </div>
    </form>

    <div>
        <h2 class="heading">_POST contains:</h2>
        <pre>
<?php
var_dump($_POST);
?>
        </pre>
    </div>
</body>
</html>

