<?php
$light = $_GET['light'];
$motion = $_GET['motion'];
$temp = $_GET['temp'];

// Security
$hardware_id = $_GET['hwid'];
$db = file_get_contents("/home/tesla/iot/database.json");
$sensors = file_get_contents("/home/tesla/iot/sensors.json");

if ($light != "" && $motion != "" && $temp != "" && $hardware_id != "") {
    if ($hardware_id == "2aaba7ef58908c80f82a92a84f79bf98ce4db41cf3b994241bdf8bb0cbdeb5cd") {
        $data = json_decode($db);
        $sensors_data = json_decode($sensors);
        $sensors_data->{'light'} = $light;
        $sensors_data->{'temperature'} = $temp;
        $sensors_new = json_encode($sensors_data);
        file_put_contents("/home/tesla/iot/sensors.json", $sensors_new);

        if (intval($data->{'ml'}) == 1 && intval($data->{'mt'}) == 0) {
            $modifier = (floatval($sensors_data->{'light'}));

            if ($modifier > 255) $modifier = 255;
            if ($modifier < 0) $modifier = 0;
            strval($data->{'red'} = intval(255 - $modifier));
            strval($data->{'green'} = intval(255 - $modifier));
            strval($data->{'blue'} = intval(255 - $modifier));
        } else if (intval($data->{'ml'}) == 0 && intval($data->{'mt'}) == 1) {
            $modifier = 40 / floatval($sensors_data->{'temperature'}) * 40;

            if ($modifier > 40) $modifier = 40;
            if ($modifier < 0) $modifier = 0;

            if ($modifier >= 0 && $modifier < 20) {
                strval($data->{'red'} = 255);
                strval($data->{'green'} = 255);
                strval($data->{'blue'} = intval(255 - $modifier * 2));
            } else {
                strval($data->{'red'} = intval(255 - $modifier * 2));
                strval($data->{'green'} = 255);
                strval($data->{'blue'} = 255);
            }
        } else if (intval($data->{'ml'}) == 1 && intval($data->{'mt'}) == 1) {
            $modifier = floatval($sensors_data->{'temperature'});

            if ($modifier > 40) $modifier = 40;
            if ($modifier < 0) $modifier = 0;

            if ($modifier >= 0 && $modifier < 20) {
                $data->{'red'} = 255;
                $data->{'green'} = 255;
                $data->{'blue'} = intval(255 - $modifier * 8);
            } else {
                $data->{'red'} = intval(255 - $modifier * 8);
                $data->{'green'} = 255;
                $data->{'blue'} = 255;
            }

            $modifier2 = (floatval($sensors_data->{'light'}));

            if ($modifier2 > 255) $modifier2 = 255;
            if ($modifier2 < 0) $modifier2 = 0;
            strval($data->{'red'} = intval($data->{'red'} - $modifier2));
            strval($data->{'green'} = intval($data->{'green'} - $modifier2));
            strval($data->{'blue'} = intval($data->{'blue'} - $modifier2));

        }

        if ($motion == '1') $data->{'enabled'} = "1";
        $data_new = json_encode($data);
        file_put_contents("/home/tesla/iot/database.json", $data_new);
        header("Access-Control-Allow-Origin: *");
        echo($sensors_new);
    } else {
        header("Access-Control-Allow-Origin: *");
        die('{"code": 403, "message": "Access denied"}');
    }
} else {
    header("Access-Control-Allow-Origin: *");
    die('{"code": 400, "message": "Bad request"}');
}

?>