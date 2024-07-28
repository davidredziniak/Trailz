<?php
require_once(__DIR__ . "/../lib/functions.php");
//Note: this is to resolve cookie issues with port numbers
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
$localWorks = true; //some people have issues with localhost for the cookie params
//if you're one of those people make this false

//this is an extra condition added to "resolve" the localhost issue for the session cookie
if (($localWorks && $domain == "localhost") || $domain != "localhost") {
    session_set_cookie_params([
        "lifetime" => 60 * 60,
        "path" => "$BASE_PATH",
        //"domain" => $_SERVER["HTTP_HOST"] || "localhost",
        "domain" => $domain,
        "secure" => true,
        "httponly" => true,
        "samesite" => "lax"
    ]);
}
session_start();


?>
<!-- include css and js files -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>


<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="<?php echo get_url('helpers.js'); ?>"></script>
<header class="header">
    <div class="container">
        <div class="logo">
            <h2>Trailz</h2>
        </div>
        <nav class="navbar" id="main-menu">
            <ul>
                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-item" href="<?php echo get_url('home.php'); ?>">Home</a></li>
                    <li class="dropdown" id="menuList">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" id="droplabel">
                            Profile
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="listbox" id="menuDropdowns">
                            <a href="<?php echo get_url('profile.php'); ?>?id=<?php echo get_user_id() ?>"><li class="dropdown-item">My Profile</li></a>
                            <a href="<?php echo get_url('edit_profile.php'); ?>"><li class="dropdown-item">Edit Profile</li></a>
                        </ul>
                    </li>
                    <li class="dropdown" id="menuList">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" id="droplabel">
                            Trails
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="listbox" id="menuDropdowns">
                            <a href="<?php echo get_url('submit_trail.php'); ?>"><li class="dropdown-item">Submit</li></a>
                            <a href="<?php echo get_url('view_trails.php'); ?>"><li class="dropdown-item">View List</li></a>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-item" href="<?php echo get_url('login.php'); ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-item" href="<?php echo get_url('register.php'); ?>">Register</a></li>
                <?php endif; ?>
                <?php if (has_role("Admin")) : ?>
                    <li class="dropdown" id="menuList">
                        <a href="#" class="dropdown-toggle nav-item" data-bs-toggle="dropdown" id="droplabel">
                            Admin
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="listbox" id="menuDropdowns">
                            <a href="<?php echo get_url('admin/create_role.php'); ?>"><li class="dropdown-item">Create Role</li></a>
                            <a href="<?php echo get_url('admin/list_roles.php'); ?>"><li class="dropdown-item">List Roles</li></a>
                            <a href="<?php echo get_url('admin/assign_roles.php'); ?>"><li class="dropdown-item">Assign Role</li></a>
                            <a href="<?php echo get_url('admin/fetch_trails.php'); ?>"><li class="dropdown-item">Fetch Trails</li></a>
                            <a href="<?php echo get_url('admin/list_favorites.php'); ?>"><li class="dropdown-item">List Favorites</li></a>
                            <a href="<?php echo get_url('admin/list_unfavorited.php'); ?>"><li class="dropdown-item">List Unfavorited</li></a>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-item" href="<?php echo get_url('logout.php'); ?>">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>