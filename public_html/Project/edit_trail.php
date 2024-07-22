<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_trail($id, true);
}

// Check if user has permissions to edit the trail
if (!has_role("Admin") || !is_trail_owner($id)) {
    flash("You don't have permission to edit this trail.", "danger");
    die(header("Location: " . get_url("view_trails.php")));
}

$trail = get_trail_by_id($id);

?>
<div class="container-fluid">
    <h2><?php se($trail, "name"); ?></h2>
    <div>
        <h5><?php se($trail, "city"); ?>, <?php se($trail, "region"); ?>, <?php se($trail, "country"); ?></h5>
        <p><?php se($trail, "description"); ?></p>
        <p>Difficulty: <?php se($trail, "difficulty"); ?></p>
        <br>
        <p>Details</p>
        <p>Latitude: <?php echo $trail['latitude'] ?></p>
        <p>Longitude: <?php echo $trail['longitude'] ?></p>
        <p>Length: <?php echo $trail['length'] ?></p>
        <p>Features: <?php echo $trail['features'] ?></p>
    </div>
</div>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>