<?php
require(__DIR__ . "/../../partials/nav.php");
?>

<body class="bg-dark">
    <div class="container mt-5 p-5 rounded-2 w-25" style="background-color: #ffffff;">
        <form onsubmit="return validate(this)" method="POST">
            <div class="mt-3">
                <label for="email" class="form-label">Email/username:</label>
                <input type="text" name="email" required class="form-control mt-1" />
            </div>
            <div class="mt-3">
                <label for="pw" class="form-label">Password:</label>
                <input type="password" id="pw" name="password" required minlength="8" class="form-control mt-1" />
            </div>
            <div class="row mt-3">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><input type="submit" class="btn btn-primary" value="Login"></div>
            </div>
        </form>
    </div>
</body>

<script>
    function validate(form) {
        var emailOrUser = document.querySelector('[name="email"]').value;

        // Check if email or username is empty
        if (emailOrUser === "") {
            flash("Email/Username field cannot be empty.", "warning");
            return false;
        }

        // If email, check if email is valid using regex
        if (emailOrUser.includes("@")) {
            if (!/^[a-z0-9.]{1,64}@[a-z0-9.]{1,64}$/i.test(emailOrUser)) {
                flash("Username/Email: " + emailOrUser + " is invalid.", "warning")
                return false;
            }
        } else {
            // Username validation
            if (!/^[a-z0-9_-]{3,30}$/.test(emailOrUser)) {
                flash("Username must be 3-30 characters and contain valid characters (a-z, 0-9, _, or -)", "warning");
                return false;
            }
        }

        var pass = document.querySelector('[name="password"]').value;

        // Check if password is empty
        if (pass === "") {
            flash("Password field cannot be empty.", "warning");
            return false;
        }

        // Check password length
        if (pass.length < 8) {
            flash("Password length cannot be less than 8 characters.", "warning");
            return false;
        }

        return true;
    }
</script>
<?php
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty.", "warning");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (str_contains($email, "@")) {
        //sanitize
        $email = sanitize_email($email);
        if (!is_valid_email($email)) {
            flash("Invalid email address.", "warning");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username.", "warning");
            $hasError = true;
        }
    }
    if (empty($password)) {
        flash("Password must not be empty.", "warning");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password is too short.", "warning");
        $hasError = true;
    }
    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from `Users` where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        $_SESSION["user"] = $user;
                        try {
                            //lookup potential roles
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $user["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
                        //save roles or empty array
                        if (isset($roles)) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        die(header("Location: home.php"));
                    } else {
                        flash("Password is incorrect.", "danger");
                    }
                } else {
                    flash("Username/Email not found.", "danger");
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}

require(__DIR__ . "/../../partials/flash.php");

?>