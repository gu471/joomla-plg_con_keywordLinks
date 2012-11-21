<?php

$analy = htmlentities("!(Hogan&Lilly)|Schulz_No&(!SSS!bing|Burkhalter)");
//$analy = htmlentities(" computer&!(Windows|Mac)  ");
var_dump($analy);

// &amp => &
$pattern[0] = "/&amp;/";
$replace[0] = " &&& ";
// Leerzeichen vor und nach Operatoren setzen
$pattern[1] = "~([!()|])~";
$replace[1] = " $1 ";
// unnötige Leerzeichen entfernen
$pattern[2] = "/\s+/";
$replace[2] = " ";
// ! Key => !Key
$pattern[3] = "~([!])[\s]([\w])~";
$replace[3] = "$1$2";
// Key => SQL
$pattern[4] = "~[\s]([^!^(^)^&^| ]+)~";
$replace[4] = " metakey LIKE '%$1%'";
// !Key => SQL
$pattern[5] = "~[!]([^!^(^)^&^| ]+)~";
$replace[5] = "metakey NOT LIKE '%$1%'";
// ! => NOT
$pattern[6] = "/[!]/";
$replace[6] = "NOT";
// & => SQL
$pattern[7] = "/&&&/";
$replace[7] = "AND";
// | => SQL
$pattern[8] = "/\|/";
$replace[8] = "OR";

$out = preg_replace($pattern, $replace, $analy);

var_dump($out);

?>