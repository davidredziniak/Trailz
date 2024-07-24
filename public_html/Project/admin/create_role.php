<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

if (isset($_POST["name"]) && isset($_POST["description"])) {
    $name = se($_POST, "name", "", false);
    $desc = se($_POST, "description", "", false);
    if (empty($name)) {
        flash("Name is required", "warning");
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO `Roles` (name, description, is_active) VALUES(:name, :desc, 1)");
        try {
            $stmt->execute([":name" => $name, ":desc" => $desc]);
            flash("Successfully created role $name!", "success");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                flash("A role with this name already exists, please try another", "warning");
            } else {
                flash(var_export($e->errorInfo, true), "danger");
            }
        }
    }
}
?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2 trail-details" style="background-color: #ffffff;">
            <h1>Create Role</h1>
            <div class="mt-4">
                <form method="POST">
                    <div>
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" class="form-control" required />
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="d" class="form-label">Description</label>
                        <textarea name="description" id="d" class="form-control"></textarea>
                    </div>
                    <input type="submit" value="Create Role" class="btn btn-primary" />
                </form>
            </div>
        </div>
    </div>
</body>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>