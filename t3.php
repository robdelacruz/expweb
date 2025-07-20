<?php

$arr = ["abc" => 123];

if (null == null)
    print("null == null\n");
if (null === null)
    print("null === null\n");
if (!null)
    print("!null\n");
if (is_null(null))
    print("is_null(null)\n");
if (getv($arr, "abc") != null)
    print('getv($arr, "abc") != null' . PHP_EOL);
if (getv($arr, "def") == null)
    print('getv($arr, "def") == null' . PHP_EOL);
if (getv($arr, "abc"))
    print('getv($arr, "abc")' . PHP_EOL);
if (!getv($arr, "def"))
    print('!getv($arr, "def")' . PHP_EOL);
if (null == "")
    print("null == \"\"\n");
var_dump(getv($arr, "abc"));
var_dump(getv($arr, "def"));


function getv($t, $k) {
    return $t[$k] ?? null;
}
