<?php
//TODO 1: require db.php
require_once(__DIR__ . "/db.php");
//This is going to be a helper for redirecting to our base project path since it's nested in another folder
//This MUST match the folder name exactly
$BASE_PATH = '/Project';

//require safer_echo.php
require(__DIR__ . "/safer_echo.php");
//TODO 2: filter helpers
require(__DIR__ . "/sanitizers.php");

//TODO 3: User helpers
require(__DIR__ . "/user_helpers.php");
//TODO 4: Flash Message Helpers
require(__DIR__ . "/flash_messages.php");
// Trail Helpers
require(__DIR__ . "/trail_helpers.php");
// Favorite Helpers
require(__DIR__ . "/favorite_helpers.php");

//duplicate email/username
require(__DIR__ . "/duplicate_user_details.php");
//reset session
require(__DIR__ . "/reset_session.php");

require(__DIR__ . "/get_url.php");

// API helper
require(__DIR__ . "/api_helper.php");
?>