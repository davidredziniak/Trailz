<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

//handle the toggle first so select pulls fresh data
if (isset($_POST["delete"])) {
    $fav_id = se($_POST, "fav_id", "", false);
    if (!empty($fav_id)) {
        if (delete_favorite_by_id($fav_id)) {
            flash("Successfully deleted favorite.", "success");
        } else {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}

$total_favorites = intval(get_number_of_favorites());

$query = "SELECT 
    uf.id,
    uf.user_id,
    uf.trail_id,
    COUNT(uf.trail_id) OVER (PARTITION BY uf.trail_id) AS trail_count,
    u.username as username
FROM 
    `User_Favorites` uf
JOIN `Users` u on uf.user_id = u.id";


$params = null;
if (isset($_POST["username"]) && !empty($_POST["username"])) {
    $search = se($_POST, "username", "", false);
    $query .= " WHERE u.username LIKE :username";
    $params =  [":username" => "%$search%"];
}

if (isset($_POST["trail"]) && !empty($_POST["trail"])) {
    $search = se($_POST, "trail", "", false);
    $query .= " AND uf.trail_id=" . intval($search);
}

$limit = 10;

if (isset($_POST["limit"])) {
    // Check if limit is valid
    $user_limit = se($_POST, "limit", null, false);
    if (!empty($user_limit)) {
        $user_limit = intval($user_limit);
        if ($user_limit > 100 || $user_limit < 1) {
            flash("Limit needs to be in a range 1 to 100.", "danger");
            $hasError = true;
        } else {
            $limit = $user_limit;
        }
    }
}

$query .= " ORDER BY uf.modified desc LIMIT $limit";
$db = getDB();
$stmt = $db->prepare($query);
$favorites = [];
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $favorites = $results;
    } else {
        flash("No matches found.", "warning");
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}

?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container-sm mt-5 p-5 rounded-2" style="background-color: #ffffff;">
            <h1>User's Favorite Trails (<?php echo $total_favorites ?>)</h1>
            <p>View User's favorite trails and delete them.</p>
            <form method="POST" onsubmit="return validate(this)">
                <div class="input-group mb-3">
                    <span class="input-group-text">Username</span>
                    <input type="text" id="username" class="form-control" name="username" placeholder="ex. Davideee" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Trail ID</span>
                    <input type="number" id="trail" class="form-control" name="trail" placeholder="ex. 610" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Limit</span>
                    <input type="number" name="limit" id="limit" class="form-control" placeholder="Default: 10" aria-describedby="basic-addon1" />
                </div>
                <div class="row mt-4">
                    <div class="col"></div><!-- This is a filler column -->
                    <div class="col-auto"><input type="submit" class="btn btn-primary btn-md" value="Search" /></div>
                </div>
            </form>
        </div>
        <div class="container mt-5 mb-4 p-5 rounded-2" style="background-color: #ffffff;">
            <h1>Favorites (<?php echo count($favorites) ?>)</h1>
            <table class="table">
                <thead>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Trail ID</th>
                    <th>Users Associated to Trail</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php if (empty($favorites)) : ?>
                        <tr>
                            <td colspan="100%">No favorites</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($favorites as $favorite) : ?>
                            <tr>
                                <td><?php se($favorite, "id"); ?></td>
                                <td><?php se($favorite, "username"); ?></td>
                                <td><?php se($favorite, "trail_id"); ?></td>
                                <td><?php se($favorite, "trail_count"); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="fav_id" value="<?php se($favorite, 'id'); ?>" />
                                        <?php if (isset($_POST["username"]) && !empty($_POST["username"])) : ?>
                                            <input type="hidden" name="username" value="<?php se($_POST, "username"); ?>" />
                                        <?php endif; ?>
                                        <?php if (isset($_POST["limit"]) && !empty($_POST["limit"])) : ?>
                                            <input type="hidden" name="limit" value="<?php se($_POST, "limit"); ?>" />
                                        <?php endif; ?>
                                        <?php if (isset($_POST["trail"]) && !empty($_POST["trail"])) : ?>
                                            <input type="hidden" name="trail" value="<?php se($_POST, "trail"); ?>" />
                                        <?php endif; ?>
                                        <input type="submit" name="delete" value="Delete" class="btn btn-primary" />
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script>
    function validate(form) {
        let username = form.username.value;
        let trailId = form.trail.value;
        let limit = form.limit.value;

        // Check if empty values
        if (username === "" && trailId === "") {
            flash("You must enter username or trail ID for searching.", "warning");
            return false;
        }

        // Check if specified limit is valid
        if (limit !== "") {
            limit = parseInt(limit);
            if (limit <= 0 || limit > 100) {
                flash("Limit specified must be a number in the range 1-100.", "warning");
                return false;
            }
        }

        // Check name length is valid
        if (username.length > 30) {
            flash("The length of the username should not be greater than 30 chars.", "warning");
            return false;
        }

        // Check if trail ID is valid
        if (!/^[0-9]+$/.test(trailId)) {
            flash("The trail ID should consist of only numbers.", "warning");
            return false;
        }

        return true;
    }
</script>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>