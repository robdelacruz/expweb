<?php

#$s1 = "2025-07-21";
#$s2 = "07/21/2025";
#$s3 = "";

#var_dump(strtotime($s1));
#var_dump(strtotime($s2));
#var_dump(strtotime(""));

#var_dump(date("Y-m-d", 0));

$newqs = parse_querystring("p=login&view=mini&period=month", ["abc"=>123, "def"=>456]);
var_dump($newqs);
$newqs = parse_querystring("", ["abc"=>123, "def"=>456]);
var_dump($newqs);


# Given: "p=login&view=mini"
# Returns: ["p" => "login", "view" => "mini"]
function parse_querystring($querystring, $initqs=[]) {
    $qs = $initqs;
    $params = explode("&", $querystring);
    foreach ($params as $param) {
        $kv = explode("=", trim($param));
        if (count($kv) < 2)
            continue;
        $qs[$kv[0]] = $kv[1];
    }
    return $qs;
}

