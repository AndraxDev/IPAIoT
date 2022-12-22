<?php

$ml = $_GET['ml'];
$mt = $_GET['mt'];
$rollets = $_GET['rollets'];
$enabled = $_GET['enabled'];

$db = file_get_contents("/home/tesla/iot/database.json");
$data = json_decode($db);

if ($ml != "") {
    $data->{'ml'} = $ml;
}

if ($mt != "") {
    $data->{'mt'} = $mt;
}

if ($rollets != "") {
    $data->{'rollets'} = $rollets;
}

if ($enabled != "") {
    $data->{'enabled'} = $enabled;
}

$data_new = json_encode($data);
file_put_contents("/home/tesla/iot/database.json", $data_new);

header("Access-Control-Allow-Origin: *");
echo("OK");
?>