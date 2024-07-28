<?php

/**
 * Passing $redirect as true will auto redirect a logged out user to the $destination.
 * The destination defaults to login.php
 */
function is_logged_in($redirect = false, $destination = "login.php")
{
    $isLoggedIn = isset($_SESSION["user"]);
    if ($redirect && !$isLoggedIn) {
        //if this triggers, the calling script won't receive a reply since die()/exit() terminates it
        flash("You must be logged in to view this page", "warning");
        die(header("Location: $destination"));
    }
    return $isLoggedIn;
}
function has_role($role)
{
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] === $role) {
                return true;
            }
        }
    }
    return false;
}
function get_username()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "username", "", false);
    }
    return "";
}
function get_user_email()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "email", "", false);
    }
    return "";
}
function get_user_id()
{
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "id", false, false);
    }
    return false;
}

function is_valid_user($id, $redirect = false, $destination = "home.php")
{
    $valid = false;
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM `Users` WHERE id=:id LIMIT 1;");
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
        flash("The user you requested to view does not exist.", "danger");
        die(header("Location: $destination"));
    }
    return $valid;
}

function get_user_by_id($id)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT username, unix_timestamp(created) AS created FROM `Users` WHERE id=:id LIMIT 1;");
    try {
        $stmt->execute([":id" => intval($id)]);
        $r = $stmt->fetchAll();
        if($r){
            return $r[0];
        } else {
            flash("Unable to locate specified user's username.", "danger");
            return "";
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }
}

function get_trails_by_user_id($id){
    $db = getDB();
    $stmt = $db->prepare("SELECT t.id, t.name, t.country, t.length, t.difficulty, unix_timestamp(t.created) AS created FROM `User_Trails` AS u JOIN Trails AS t ON u.trail_id = t.id WHERE u.user_id=:id;");
    try {
        $stmt->execute([":id" => intval($id)]);
        $r = $stmt->fetchAll();
        if($r){
            return $r;
        } else {
            return [];
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }
}

function get_favorites_by_user_id($id){
    $db = getDB();
    $stmt = $db->prepare("SELECT t.id, t.name, t.country, t.length, t.difficulty, unix_timestamp(u.created) AS created, u.id AS f_id FROM `User_Favorites` AS u JOIN Trails AS t ON u.trail_id = t.id WHERE u.user_id=:id;");
    try {
        $stmt->execute([":id" => intval($id)]);
        $r = $stmt->fetchAll();
        if($r){
            return $r;
        } else {
            return [];
        }
    } catch (Exception $e) {
        flash(". var_export($e, true) .", "danger");
    }
}