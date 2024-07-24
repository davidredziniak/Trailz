<?php

/**
 * Passing $redirect as true will auto redirect when the trail ID passed does not exist.
 * The destination defaults to view_trails.php
 */
function is_valid_trail($id, $redirect = false, $destination = "view_trails.php")
{
    $valid = false;
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM `Trails` WHERE id=:id LIMIT 1;");
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
    $query = "SELECT name, description, city, region, country, ST_X(coord) as latitude, ST_Y(coord) as longitude, length, difficulty, features, thumbnail FROM `Trails` WHERE id = :id";
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

function get_latest_trails(){
    $db = getDB();
    $query = "SELECT id, name, country, length, difficulty, thumbnail FROM `Trails` ORDER BY created DESC LIMIT 12";
    $stmt = $db->prepare($query);
    try {
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching latest trails from db: " . var_export($e, true));
    }
}

function is_trail_owner($id)
{
    $user_id = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM `User_Trails` WHERE trail_id=:id AND user_id=:user_id LIMIT 1;");
    try {
        $stmt->execute([":id" => intval($id), ":user_id" => intval($user_id)]);
        $r = $stmt->fetchAll();
        if($r){
            return true;
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }

    return false;
}

function is_favorited($id){
    $user_id = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM `User_Favorites` WHERE trail_id=:id AND user_id=:user_id LIMIT 1;");
    try {
        $stmt->execute([":id" => intval($id), ":user_id" => intval($user_id)]);
        $r = $stmt->fetchAll();
        if($r){
            return true;
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }

    return false;
}

function delete_favorite($user_id, $trail_id){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM `User_Favorites` WHERE user_id=:user_id AND trail_id=:trail_id");
    try {
        $stmt->execute([":user_id" => intval($user_id), ":trail_id" => intval($trail_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}

function add_favorite($user_id, $trail_id){
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO `User_Favorites` (user_id, trail_id) VALUES (:user_id, :trail_id)");
    try {
        $stmt->execute([":user_id" => intval($user_id), ":trail_id" => intval($trail_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}

function toggle_favorite($id){
    $user_id = get_user_id();
    $db = getDB();

    // Check if user already has the trail favorited
    $stmt = $db->prepare("SELECT 1 FROM `User_Favorites` WHERE trail_id=:id AND user_id=:user_id LIMIT 1;");
    try {
        $stmt->execute([':id' => intval($id), ":user_id" => intval($user_id)]);
        $r = $stmt->fetchAll();

        if($r){
            delete_favorite($user_id, $id);
        } else {
            add_favorite($user_id, $id);
        }
    } catch (Exception $e){
        flash("An error has occured when toggling the User Favorites record.", "danger");
        return false;
    }
}