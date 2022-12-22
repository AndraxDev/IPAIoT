<?php

$red = $_GET['r'];
$green = $_GET['g'];
$blue = $_GET['b'];
$api_key = $_GET['key'];

class Color {
    public $red;
    public $green;
    public $blue;
    public $rollets;
    public $enabled;
    public $ml;
    public $mt;

    public function __construct($red, $green, $blue, $rollets, $enabled, $ml, $mt) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->rollets = $rollets;
        $this->enabled = $enabled;
        $this->ml = $ml;
        $this->mt = $mt;
    }
}

if ($red != "" && $green != "" && $blue != "" && $api_key != "") {
    if ($api_key == "12345") {
        $db = file_get_contents("/home/tesla/iot/database.json");
        $dbe = json_decode($db);
        $obj = new Color($red, $green, $blue, $dbe->{'rollets'}, $dbe->{'enabled'}, $dbe->{'ml'}, $dbe->{'mt'});
        $json = json_encode($obj);
        file_put_contents("/home/tesla/iot/database.json", $json);
        header("Access-Control-Allow-Origin: *");
        echo('{"code": 200, "message": "OK"}');
    } else {
        header("Access-Control-Allow-Origin: *");
        die('{"code": 403, "message": "Access denied!"}');
    }
} else {
    header("Access-Control-Allow-Origin: *");
    die('{"code": 400, "message": "Bad request"}');
}

?>