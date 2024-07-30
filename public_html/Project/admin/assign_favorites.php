<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

//attempt to toggle favorites
if (isset($_POST["users"]) && isset($_POST["trails"])) {
    $user_ids = $_POST["users"];
    $trail_ids = $_POST["trails"];
    if (empty($user_ids) || empty($trail_ids)) {
        flash("Both users and trails need to be selected.", "warning");
    } else {
        foreach ($user_ids as $uid) {
            foreach ($trail_ids as $tid) {
                try {
                    if(toggle_favorite($uid, $tid)){
                        flash("Successfully toggled favorite.", "success");
                    } else {
                        flash("Unable to toggle favorite for user $uid.", "danger");
                    }
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//search for trails by name and users by username
$users = [];
$trails = [];

if (isset($_POST["username"]) && isset($_POST["trail"])) {
    $username = se($_POST, "username", "", false);
    $trail = se($_POST, "trail", "", false);

    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username FROM `Users` WHERE username LIKE :username LIMIT 25;");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            } else {
                flash("Username was not found.", "danger");
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }

    if (!empty($trail)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name FROM `Trails` WHERE name LIKE :name LIMIT 25;");
        try {
            $stmt->execute([":name" => "%$trail%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $trails = $results;
            } else {
                flash("Trail was not found.", "danger");
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Trail name must not be empty", "warning");
    }
}

?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <h1>Assign Favorites</h1>
            <p>Assign favorite trails to a specified user.</p>
            <form method="POST">
                <div class="input-group mb-3 mt-4">
                    <input type="text" class="form-control" name="username" placeholder="Username" aria-label="Recipient's username" aria-describedby="basic-addon2">
                </div>
                <div class="input-group mb-3 mt-4">
                    <input type="text" class="form-control" name="trail" placeholder="Trail Name" aria-label="Trail's name" aria-describedby="basic-addon2">
                </div>
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit" value="Search">Search</button>
                </div>
            </form>
        </div>
        <?php if (count($users) > 0) : ?>
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <form method="POST">
                    <table class="table">
                        <thead>
                            <th></th>
                            <th>Users</th>
                            <th>Trails to Toggle</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <table>
                                        <?php foreach ($users as $user) : ?>
                                            <tr>
                                                <td>
                                                    <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" class="form-check" />
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                                <td>
                                    <table>
                                        <?php foreach ($users as $user) : ?>
                                            <tr>
                                                <td>
                                                    <label for="user_<?php se($user, 'id'); ?>" class="form-check-label"><?php se($user, "username"); ?></label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                                <td>
                                    <?php foreach ($trails as $trail) : ?>
                                        <div>
                                            <label for="trail_<?php se($trail, 'id'); ?>"><?php se($trail, "name"); ?></label>
                                            <input id="trail_<?php se($trail, 'id'); ?>" type="checkbox" name="trails[]" value="<?php se($trail, 'id'); ?>" />
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <input type="submit" value="Toggle Favorites" class="btn btn-primary"/>
            </form>
        </div>
        <?php endif ?>
    </div>
</body>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>