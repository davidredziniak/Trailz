<?php

function sanitize_email($email = "")
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
function is_valid_email($email = "")
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}
function is_valid_username($username)
{
    return preg_match('/^[a-z0-9_-]{3,16}$/', $username);
}
function is_valid_password($password)
{
    return strlen($password) >= 8;
}
function is_valid_latitude($latitude){
    return preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/', $latitude);
}
function is_valid_longtitude($longtitude){
    return preg_match('/^[-]?([1-9]?\d(\.\d+)?|1[0-7]\d(\.\d+)?|180(\.0+)?)$/', $longtitude);
}