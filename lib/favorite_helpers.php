<?php

function get_number_of_favorites()
{
    $db = getDB();
    $stmt = $db->prepare("SELECT count(id) AS count FROM `User_Favorites`;");
    try {
        $stmt->execute();
        $r = $stmt->fetch();
        if($r){
            return $r["count"];
        }
        else{
            return 0;
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }
}

function is_user_favorite($user_id, $favorite_id)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM `User_Favorites` WHERE id=:favorite_id AND user_id=:user_id LIMIT 1;");
    try {
        $stmt->execute([":favorite_id" => intval($favorite_id), ":user_id" => intval($user_id)]);
        $r = $stmt->fetch();
        if($r){
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }
    return false;
}

function add_favorite_by_trail_id($user_id, $trail_id){
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO `User_Favorites` (user_id, trail_id) VALUES (:user_id, :trail_id)");
    try {
        $stmt->execute([":user_id" => intval($user_id), ":trail_id" => intval($trail_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}

function delete_favorite_by_trail_id($user_id, $trail_id){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM `User_Favorites` WHERE user_id=:user_id AND trail_id=:trail_id");
    try {
        $stmt->execute([":user_id" => intval($user_id), ":trail_id" => intval($trail_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}

function toggle_favorite($user_id, $id){
    $db = getDB();

    // Check if user already has the trail favorited
    $stmt = $db->prepare("SELECT 1 FROM `User_Favorites` WHERE trail_id=:id AND user_id=:user_id LIMIT 1;");
    try {
        $stmt->execute([':id' => intval($id), ":user_id" => intval($user_id)]);
        $r = $stmt->fetchAll();

        if($r){
            return delete_favorite_by_trail_id($user_id, $id);
        } else {
            return add_favorite_by_trail_id($user_id, $id);
        }
    } catch (Exception $e){
        flash("An error has occured when toggling the User Favorites record.", "danger");
        return false;
    }
}

function delete_favorite_by_id($favorite_id){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM `User_Favorites` WHERE id=:favorite_id;");
    try {
        $stmt->execute([":favorite_id" => intval($favorite_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}

function delete_all_favorites($user_id){
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM `User_Favorites` WHERE user_id=:user_id;");
    try {
        $stmt->execute([":user_id" => intval($user_id)]);
        return true;
    } catch (Exception $e){
        return false;
    }
}