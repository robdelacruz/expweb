<?php
require "functions.php";

$db = new SQLite3("test.db");
create_tables($db);

//$sql = "SELECT user_id, username, password FROM user ORDER BY user_id";
//$result = $db->query($sql);

//while ($row = $result->fetchArray()) {
//    extract($row, EXTR_REFS | EXTR_PREFIX_ALL, "rob");
//    echo "user_id: {$rob_user_id}, username: {$rob_username}, password: {$rob_password}\n";
//}
?>


