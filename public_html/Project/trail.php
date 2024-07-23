<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);

// Check if ID is set and is a valid trail
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_trail($id, true);
}

// Retrieve trail data
$trail = get_trail_by_id($id);
?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <div class="row">
                <div class="col-md-8">
                    <h1><?php se($trail, "name"); ?></h1>
                    <p><?php se($trail, "city"); ?>, <?php se($trail, "region"); ?>, <?php se($trail, "country"); ?></p>
                    <p><b>Length:</b> <?php echo $trail['length'] ?> miles</p>
                    <p><b>Description:</b> <?php se($trail, "description"); ?></p>
                    <p><b>Difficulty:</b> <?php se($trail, "difficulty"); ?></p>
                    <p><b>Features:</b> <?php echo $trail['features'] ?></p>
                </div>
                <div class="col-md-4 text-center">
                    <img src="<?php echo $trail['thumbnail'] ?>" alt="Hiking Trail Image">
                    <div class="location-pin">
                        <img src="https://img.icons8.com/ios-filled/50/000000/marker.png" alt="Location Pin">
                        <span><?php echo $trail['latitude'] ?>, <?php echo $trail['longitude'] ?></span>
                    </div>
                    <div class="btn-group mt-5">
                        <?php if (has_role("Admin") || is_trail_owner($id)) : ?>
                            <?php echo '<a href="./edit_trail.php?id=' . $id . '" class="btn btn-primary">Edit</a>'; ?>
                            <?php echo '<a href="./delete_trail.php?id=' . $id  . '" class="btn btn-danger">Delete</a>'; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>