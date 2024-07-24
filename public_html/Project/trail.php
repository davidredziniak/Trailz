<?php
require_once(__DIR__ . "/../../partials/nav.php");

// Check if user is logged in
is_logged_in(true);

// Check if ID is set and is a valid trail
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_trail($id, true);

    // Retrieve trail data
    $trail = get_trail_by_id($id);

    // Get if user has it favorited
    $favorite = is_favorited($id);

    $user_id = get_user_id();

    if (isset($_POST["favorite"])) {
        $type = se($_POST, "favorite", "", false);
        $hasError = false;

        // Validate option chosen is one of two available
        if ($type !== "add" && $type !== "delete") {
            $hasError = true;
            flash("Unhandled option selection for favoriting a trail.", "danger");
        }

        if (!$hasError) {
            // Call helper function to either add or delete trail from favorites
            if ($type == "add") {
                if (add_favorite($user_id, $id)) {
                    flash("Successfully added this trail to favorites!", "success");
                } else {
                    flash("Error when adding this trail to favorites.", "danger");
                }
            } else {
                if (delete_favorite($user_id, $id)) {
                    flash("Successfully deleted this trail from favorites!", "success");
                } else {
                    flash("Error when deleting this trail from favorites.", "danger");
                }
            }

            // Update
            $favorite = is_favorited($id);
        }
    }

    function get_image_url($thumb){
        if (empty($thumb) || $thumb == null){
            echo './images/placeholder.jpg';
        } else {
            echo $thumb;
        }
    }
}
?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <div class="row">
                <div class="col-md-8">
                    <h1><?php se($trail, "name"); ?></h1>
                    <p><?php se($trail, "city"); ?>, <?php se($trail, "region", "", true); ?>, <?php se($trail, "country", "", true); ?></p>
                    <p><b>Length:</b> <?php se($trail, "length", "", true); ?> miles</p>
                    <p><b>Description:</b> <?php if(se($trail, "description") == "") : echo 'N/A'; ?><?php else: se($trail, "description", "", true) ?><?php endif ?></p>
                    <p><b>Difficulty:</b> <?php se($trail, "difficulty", "", true); ?></p>
                    <p><b>Features:</b> <?php if(se($trail, "features") == "") : echo 'N/A'; ?><?php else: se($trail, "features", "", true) ?><?php endif ?></p>
                </div>
                <div class="col-md-4 text-center">
                    <img src="<?php get_image_url(se($trail, "thumbnail"), "", false) ?>" alt="Hiking Trail Image">
                    <div class="location-pin">
                        <img src="https://img.icons8.com/ios-filled/50/000000/marker.png" alt="Location Pin">
                        <span><?php se($trail, "latitude", "", true) ?>, <?php se($trail, "longitude", "", true) ?></span>
                    </div>
                    <form method="POST">
                        <div class="btn-group mt-5" role="group">
                            <?php if (!$favorite) : ?>
                                <button class="btn btn-favorite" name="favorite" value="add" type="submit">Favorite</button>
                            <?php else : ?>
                                <button class="btn btn-danger" name="favorite" value="delete" type="submit">Unfavorite</button>
                            <?php endif ?>
                            <?php if (has_role("Admin") || is_trail_owner($id)) : ?>
                                <?php echo '<a href="./edit_trail.php?id=' . $id . '" class="btn btn-primary link-in-button">Edit</a>'; ?>
                                <?php echo '<a href="./delete_trail.php?id=' . $id . '" class="link-in-button btn btn-danger" style="margin-left: -10;">Delete</a>'; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>