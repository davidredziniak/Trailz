<?php

function insert_trails_into_db($db, $trails)
{
    foreach ($trails as $trail) {
        $stmt = $db->prepare("INSERT INTO `Trails` (name, description, city, region, country, coord, length, difficulty, features, api_id, thumbnail)
    SELECT name, description, city, region, country, coord, length, difficulty, features, api_id, thumbnail
    FROM (SELECT :name as name, :description as description, :city as city, :region as region, :country as country, POINT(:lat, :long) as coord, :length as length, :difficulty as difficulty, :features as features, :api_id as api_id, :thumbnail as thumbnail) n
    WHERE NOT EXISTS (SELECT 1 FROM `Trails` t WHERE t.api_id = n.api_id);");
        try {
            $stmt->execute([":name" => $trail["name"], ":description" => $trail["description"], ":city" => $trail["city"], ":region" => $trail["region"], ":country" => $trail["country"], ":lat" => $trail["lat"], ":long" => $trail["long"], ":length" => $trail["length"], ":difficulty" => $trail["difficulty"], ":features" => $trail["features"], ":api_id" => $trail["api_id"], ":thumbnail" => $trail["thumbnail"]]);
        } catch (Exception $e) {
            error_log(var_export($e, true));
            flash("Error adding trails", "danger");
            break;
        }
    }
    flash("Successfully added trails", "success");
}

function process_single_trail($trail)
{
    // Process trail data
    $api_id = se($trail, "id", "", false);
    $name = se($trail, "name", "", false);
    $desc = se($trail, "description", "", false);
    $city = se($trail, "city", "", false);
    $region = se($trail, "region", "", false);
    $country = se($trail, "country", "", false);
    $lat = se($trail, "lat", "", false);
    $long = se($trail, "lon", "", false);
    $length = se($trail, "length", "", false);
    $diff = se($trail, "difficulty", "", false);
    $feats = se($trail, "features", "", false);
    $thumb = se($trail, "thumbnail", "", false);

    // Fill in empty fields (Will allow admin to fix individually)
    if (is_null($lat) || $lat == "") {
        $lat = "0.00000";
    }
    if (is_null($long) || $long == "") {
        $long = "0.00000";
    }
    if (is_null($diff) || $diff == "") {
        $diff = "Unspecified";
    }
    if (is_null($length) || $length == "") {
        $length = "0";
    }
    if (strlen($name) > 50){
        $name = substr($name, 0, 48);
        $name = $name . "..";
    }
    if (strlen($desc) > 400){
        $desc = substr($desc, 0, 398);
        $desc = $desc . "..";
    }
    if (strlen($feats) > 50){
        $feats = substr($feats, 0, 48);
        $feats = $feats . "..";
    }

    // Prepare record
    $record = [];
    $record["api_id"] = intval($api_id);
    $record["name"] = $name;
    $record["description"] = $desc;
    $record["city"] = $city;
    $record["region"] = $region;
    $record["country"] = $country;
    $record["lat"] = floatval($lat);
    $record["long"] = floatval($long);
    $record["length"] = floatval($length);
    $record["difficulty"] = $diff;
    $record["features"] = $feats;
    $record["thumbnail"] = $thumb;

    //error_log("Record: " . var_export($record, true));
    return $record;
}

function process_trails($result)
{
    $status = se($result, "status", 400, false);
    if ($status != 200) {
        return;
    }

    // Extract data from result
    $data_string = html_entity_decode(se($result, "response", "{}", false));
    $wrapper = "{\"data\":$data_string}";
    $data = json_decode($wrapper, true);
    if (!isset($data["data"])) {
        return;
    }

    $data = $data["data"]["data"];

    error_log("data: " . var_export($data, true));

    // Process each trail
    $trails = [];
    foreach ($data as $trail) {
        $record = process_single_trail($trail);
        array_push($trails, $record);
    }

    $db = getDB();
    // Insert trails into database
    insert_trails_into_db($db, $trails);
}
