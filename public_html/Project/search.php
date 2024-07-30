<?php
require(__DIR__ . "/../../partials/nav.php");

is_logged_in(true);

//search for users by username
$users = [];

if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    $limit = 10;

    if (isset($_GET["limit"])) {
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

    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username FROM `Users` WHERE username LIKE :username LIMIT $limit;");
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
}

?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <h1>Find users</h1>
            <p>Search by username to find a user's profile.</p>
            <form method="POST" onsubmit="return validate(this);">
                <div class="input-group mb-3 mt-4">
                    <span class="input-group-text">Username:</span>
                    <input type="text" class="form-control" name="username" placeholder="Username" aria-label="Recipient's username" aria-describedby="basic-addon2">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Limit:</span>
                    <input type="text" id="limit" class="form-control" name="limit" placeholder="Maximum amount of search results.." aria-describedby="basic-addon1">
                </div>
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit" value="Search">Search</button>
                </div>
            </form>
        </div>
        <?php if (count($users) > 0) : ?>
            <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
                <table class="table">
                    <thead>
                        <th>Username</th>
                        <th>View</th>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?php se($user, "username", "", true); ?></td>
                                <td><a href="./profile.php?id=<?php se($user, "id"); ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</body>
<script>
    function validate(form) {
        let username = form.username.value;
        let limit = form.limit.value;
        
        // Check if username is empty
        if (username === ""){
            flash("Username must be specified.", "warning");
            return false;
        }

        // Check username length
        if(username.length > 50){
            flash("Username length must be 50 characters or less.", "warning");
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
        
        return true;
    }
</script>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../partials/flash.php");
?>