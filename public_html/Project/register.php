<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>

<body class="bg-dark">
    <div class="container mt-5 p-5 rounded-2 w-25" style="background-color: #c5c5c5;">
        <form onsubmit="return validate(this)" method="POST">
            <div class="mt-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" value="<?php echo se($_POST, "email", "", false); ?>" required class="form-control" />
            </div>
            <div class="mt-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" value="<?php echo se($_POST, "username", "", false); ?>" required maxlength="30" class="form-control" />
            </div>
            <div class="mt-3">
                <label for="pw" class="form-label">Password</label>
                <input type="password" id="pw" name="password" required minlength="8" class="form-control" />
            </div>
            <div class="mt-3">
                <label for="confirm" class="form-label">Confirm</label>
                <input type="password" name="confirm" required minlength="8" class="form-control" />
            </div>
            <div class="row mt-3">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><input type="submit" value="Register" class="btn btn-primary" /></div>
            </div>
        </form>
    </div>
</body>

<script>
    function validate(form) {
        var email = document.querySelector('[name="email"]').value;

        // Check if email is empty
        if (email === "") {
            flash("Email field cannot be empty.", "warning");
            return false;
        }

        // Check if email is valid (contains @ and valid characters)
        if (!/^[a-z0-9.]{1,64}@[a-z0-9.]{1,64}$/i.test(email)) {
            flash("Email: " + email + " is invalid.", "warning");
            return false;
        }

        var username = document.querySelector('[name="username"]').value;

        // Check if username is empty
        if (username === "") {
            flash("Username field cannot be empty.", "warning");
            return false;
        }
        if (!/^[a-z0-9_-]{3,30}$/.test(username)) {
            flash("Username must be 3-30 characters and contain valid characters (a-z, 0-9, _, or -).", "warning");
            return false;
        }

        var pass = document.querySelector('[name="password"]').value;
        var confirmPass = document.querySelector('[name="confirm"]').value;

        // Check if passwords are empty
        if (pass === "") {
            flash("Password field cannot be empty.", "warning");
            return false;
        }
        if (confirmPass === "") {
            flash("Confirm Password field cannot be empty.", "warning");
            return false;
        }

        // Check password lengths
        if (pass.length < 8 || confirmPass.length < 8) {
            flash("Password length cannot be less than 8 characters.", "warning");
            return false;
        }

        // Check if passwords match
        if (pass !== confirmPass) {
            flash("Password and Confirm Password do not match.", "warning");
            return false;
        }
        return true;
    }
</script>
<?php
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se(
        $_POST,
        "confirm",
        "",
        false
    );
    $username = se($_POST, "username", "", false);
    $hasError = false;
    if (empty($email)) {
        flash("Email cannot be empty.", "warning");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address.", "warning");
        $hasError = true;
    }
    if (!preg_match('/^[a-z0-9_-]{3,30}$/', $username)) {
        flash("Username must only contain 3-30 characters a-z, 0-9, _, or -", "warning");
        $hasError = true;
    }
    if (empty($password)) {
        flash("Password cannot be empty", "warning");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password cannot be empty", "warning");
        $hasError = true;
    }
    if (strlen($password) < 8) {
        flash("Password is too short", "warning");
        $hasError = true;
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Password and Confirm password does not match.", "warning");
        $hasError = true;
    }
    if (!$hasError) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (Exception $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>