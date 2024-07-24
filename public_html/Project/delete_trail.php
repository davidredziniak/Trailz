<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in

is_logged_in(true);
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_trail($id, true);

    // Check if user has permissions to delete the trail
    if (!has_role("Admin") && !is_trail_owner($id)) {
        flash("You don't have permission to delete this trail.", "danger");
        die(header("Location: " . get_url("view_trails.php")));
    } else {
        // Check if the trail is user submitted
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM `User_Trails` WHERE trail_id=:id;");
        try {
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            $r = $stmt->fetch();

            // Delete User_Trails if it exists (Not API generated)
            if ($r) {
                $stmt2 = $db->prepare("DELETE FROM `User_Trails` WHERE trail_id=:id;");
                $stmt2->bindValue(":id", $id);
                try {
                    $stmt2->execute();
                } catch (Exception $e) {
                    flash("An unexpected error occurred when deleting the user_trails record.", "danger");
                }
            }
            
            // Delete User_Favorites associations
            $stmt3 = $db->prepare("DELETE FROM `User_Favorites` WHERE trail_id=:id;");
            $stmt3->bindValue(":id", $id);
            try {
                $stmt3->execute();
            } catch (Exception $e) {
                flash("An unexpected error occurred when deleting the user_favorites records.", "danger");
            }

            // Delete the Trails record
            $stmt4 = $db->prepare("DELETE FROM `Trails` WHERE id=:id;");
            try {
                $stmt4->execute([":id" => $id]);
                flash("Successfully deleted the trail.", "success");
            } catch (Exception $e) {
                flash("An unexpected error occurred when deleting the trail, please try again", "danger");
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred when checking for the user_trails record, please try again.", "danger");
        }
    }
} else {
    die(header("Location: " . get_url("view_trails.php")));
}
?>
<script>
    setTimeout(function() {
        window.history.go(-1);
    }, 3000);
</script>

<body class="bg-dark">
    <div class="container">
    </div>
</body>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>