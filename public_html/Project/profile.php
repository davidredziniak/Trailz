<?php
require_once(__DIR__ . "/../../partials/nav.php");

// Check if user is logged in
is_logged_in(true);

// Check if ID is set and is a valid user
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_user($id, true);

    // Get user's details
    $user = get_user_by_id($id);

    // Get user's submitted trails
    $trails = get_trails_by_user_id($id);

    // Get user's favorite trails
    $favorites = get_favorites_by_user_id($id);

    // Check if user is viewing their own profile
    $is_own_profile = (get_user_id() == $id ? true : false);

    $is_admin = has_role("Admin");
}
else{
    flash("There was no specific user ID set in the URL.", "warning");
    die(header("Location: " . get_url("home.php")));
}
?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 p-5 rounded-2" style="background-color: #ffffff;">
            <div class="col-md-8">
                <h1><?php echo $user["username"] ?></h1>
                <p><b>Signed up:</b> <?php echo date('m/d/Y', $user["created"]); ?></p>
            </div>
            <div class="col-md-4 text-center">
            </div>
        </div>
        <div class="container mt-5 mb-4 p-5 rounded-2" style="background-color: #ffffff;">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8 justify-content-start">
                        <h2>Submitted Trails (<?php echo count($trails) ?>)</h2>
                    </div>
                    <div class="col-md-4 justify-content-end d-sm-flex">
                        <?php if (count($trails) !== 0) : ?><h6><a href="./submissions.php?id=<?php echo $id ?>">View all</a></h6><?php endif ?>
                    </div>
                </div>
                <div class="row mt-4">
                    <?php if (count($trails) !== 0) : ?>
                        <table class="table">
                            <thead>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Difficulty</th>
                                <th>Submitted</th>
                                <th>Link</th>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < 10; $i++) : ?>
                                    <?php if (array_key_exists($i, $trails)) : ?>
                                        <tr>
                                            <td><?php se($trails[$i], "name", "", true); ?></td>
                                            <td><?php se($trails[$i], "country", "", true); ?></td>
                                            <td><?php se($trails[$i], "difficulty", "", true); ?></td>
                                            <td><?php echo date('m/d/Y', $trails[$i]["created"]); ?></td>
                                            <td><a href="./trail.php?id=<?php se($trails[$i], "id"); ?>">View</a></td>
                                        </tr>
                                    <?php endif ?>
                                <?php endfor ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No results found.</p>
                    <?php endif ?>
                    <?php if (count($trails) > 10) : echo '<h6>Only 10 listed, to see more click View all in the top right.</h6>' ?><?php endif ?>
                </div>
            </div>
        </div>
        <div class="container mt-5 mb-4 p-5 rounded-2" style="background-color: #ffffff;">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8 justify-content-start">
                        <h2>Favorites (<?php echo count($favorites) ?>)</h2>
                    </div>
                    <div class="col-md-4 justify-content-end d-sm-flex">
                        <?php if (count($favorites) !== 0) : ?><h6><a href="./favorites.php?id=<?php echo $id ?>">View all</a></h6><?php endif ?>
                    </div>
                </div>
                <div class="row mt-4">
                    <?php if (count($favorites) !== 0) : ?>
                        <table class="table">
                            <thead>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Difficulty</th>
                                <th>Added</th>
                                <th>Link</th>
                                <?php if ($is_own_profile || $is_admin) : ?><th>Actions</th><?php endif ?>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < 10; $i++) : ?>
                                    <?php if (array_key_exists($i, $favorites)) : ?>
                                        <tr>
                                            <td><?php se($favorites[$i], "name", "", true); ?></td>
                                            <td><?php se($favorites[$i], "country", "", true); ?></td>
                                            <td><?php se($favorites[$i], "difficulty", "", true); ?></td>
                                            <td><?php echo date('m/d/Y', $favorites[$i]["created"]); ?></td>
                                            <td><a href="./trail.php?id=<?php se($favorites[$i], "id"); ?>">View</a></td>
                                            <?php if ($is_own_profile || $is_admin) : ?><td><a href="./delete_favorite.php?id=<?php se($favorites[$i], "f_id"); ?>">Remove</a></td><?php endif ?>
                                        </tr>
                                    <?php endif ?>
                                <?php endfor ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No results found.</p>
                    <?php endif ?>
                    <?php if (count($favorites) > 10) : echo '<h6>Only 10 listed, to see more click View all in the top right.</h6>' ?><?php endif ?>
                </div>
            </div>
        </div>
    </div>
</body>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>