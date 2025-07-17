<?php
require "functions.php";

$token = gen_token("robtwister", "bigdaddy");
print "token: '$token'\n";

$f = password_verify("robtwister" . "bigdaddy", $token);
print "password_verify(): $f\n";

?>
