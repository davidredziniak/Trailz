<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in

is_logged_in(true);
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    $user_id = get_user_id();

    // Check if user has permissions to delete this association
    if (!has_role("Admin") && !is_user_favorite($user_id, $id)) {
        flash("You don't have permission to delete this favorite.", "danger");
        die(header("Location: " . get_url("home.php")));
    } else {
        // Delete the user_favorite record
        if(delete_favorite_by_id($id)){
            flash("Successfully removed this trail from favorites.", "success");
        } else {
            flash("Error occured while removing this trail from favorites.", "danger");
        }
    }
} else {
    die(header("Location: " . get_url("home.php")));
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