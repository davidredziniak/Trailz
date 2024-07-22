<?php

/**
 * Passing $redirect as true will auto redirect when the trail ID passed does not exist.
 * The destination defaults to view_trails.php
 */
function is_valid_trail($id, $redirect = false, $destination = "view_trails.php")
{
    $valid = false;
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM Trails WHERE id=:id LIMIT 1;");
    try {
        $stmt->execute([":id" => intval($id)]);
        $r = $stmt->fetchAll();
        if($r){
            $valid = true;
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }

    if ($redirect && !$valid) {
        //if this triggers, the calling script won't receive a reply since die()/exit() terminates it
        flash("The trail you requested does not exist.", "danger");
        die(header("Location: $destination"));
    }
    return $valid;
}

function get_trail_by_id($id)
{
    $db = getDB();
    $query = "SELECT name, description, city, region, country, ST_X(coord) as latitude, ST_Y(coord) as longitude, length, difficulty, features FROM Trails WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $id);
    try {
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching trail from db: " . var_export($e, true));
    }
    return [];
}