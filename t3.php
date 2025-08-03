<?php

#$s1 = "2025-07-21";
#$s2 = "07/21/2025";
#$s3 = "";

#var_dump(strtotime($s1));
#var_dump(strtotime($s2));
#var_dump(strtotime(""));

#var_dump(date("Y-m-d", 0));

#$newqs = parse_querystring("p=login&view=mini&period=month", ["abc"=>123, "def"=>456]);
#var_dump($newqs);
#$newqs = parse_querystring("", ["abc"=>123, "def"=>456]);
#var_dump($newqs);

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

$s1 = "2025-02-333";
$year=0;
$month=0;
$day=0;

isodate_to_cal($s1, $year, $month, $day);
printf("%04d-%02d-%02d\n", $year, $month, $day);

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

    if ($year == 0)
        $year = 1970;
    if ($month == 0)
        $month = 1;
    if ($day == 0)
        $day = 1;
}

