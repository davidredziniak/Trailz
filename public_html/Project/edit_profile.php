<?php

require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
?>
<?php
if (isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);
    $hasError = false;
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
        $db = getDB();
        $stmt = $db->prepare("UPDATE Users SET email = :email, username = :username WHERE id = :id");
        try {
            $stmt->execute($params);
            flash("Profile saved", "success");
        } catch (Exception $e) {
            users_check_duplicate($e->errorInfo);
        }
        //select fresh data from table
        $stmt = $db->prepare("SELECT id, email, username FROM `Users` WHERE id = :id LIMIT 1");
        try {
            $stmt->execute([":id" => get_user_id()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                //$_SESSION["user"] = $user;
                $_SESSION["user"]["email"] = $user["email"];
                $_SESSION["user"]["username"] = $user["username"];
            } else {
                flash("User doesn't exist", "danger");
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred, please try again", "danger");
        }
    }

    //check/update password
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $hasError = false;
        if (!is_valid_password($new_password)) {
            flash("Password too short", "danger");
            $hasError = true;
        }
        if (!$hasError) {
            if ($new_password === $confirm_password) {
                $stmt = $db->prepare("SELECT password FROM `Users` WHERE id = :id");
                try {
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        if (password_verify($current_password, $result["password"])) {
                            $query = "UPDATE `Users` SET password = :password WHERE id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                ":id" => get_user_id(),
                                ":password" => password_hash($new_password, PASSWORD_BCRYPT)
                            ]);

                            flash("Password reset", "success");
                        } else {
                            flash("Current password is invalid", "warning");
                        }
                    }
                } catch (Exception $e) {
                    echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
                }
            } else {
                flash("New passwords don't match", "warning");
            }
        }
    }
}
?>

<?php
$email = get_user_email();
$username = get_username();
?>

<body class="bg-dark">
    <div class="container mt-5 p-5 rounded-2 w-25" style="background-color: #ffffff;">
        <h2>Edit Profile</h2>
        <hr>
        <form method="POST" onsubmit="return validate(this);">
            <div class="container-xlg mt-2 mb-4">
                <div class="mb-3 mt-2">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" value="<?php se($email); ?>" class="form-control" />
                </div>
                <div class="mb-4">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" name="username" id="username" value="<?php se($username); ?>" class="form-control" />
                </div>
            </div>
            <div class="container-xlg">
                <h6>Password Reset</h6>
                <hr>
                <div class="mb-3">
                    <label for="cp" class="form-label">Current Password:</label>
                    <input type="password" name="currentPassword" id="cp" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="np" class="form-label">New Password:</label>
                    <input type="password" name="newPassword" id="np" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="conp" class="form-label">Confirm Password:</label>
                    <input type="password" name="confirmPassword" id="conp" class="form-control" />
                </div>
            </div>
            <div class="row mt-4">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><input type="submit" value="Update" name="save" class="btn btn-primary" /></div>
            </div>
        </form>
    </div>
</body>


<script>
    function validate(form) {
        let email = form.email.value;
        let username = form.username.value;
        let currentPass = form.currentPassword.value;
        let newPass = form.newPassword.value;
        let confirmNewPass = form.confirmPassword.value;

        // Check if email is empty
        if (email === "") {
            alert("[Client]: Email field cannot be empty.");
            return false;
        }

        // Check if username is empty
        if (username === "") {
            alert("[Client]: Username field cannot be empty.");
            return false;
        }

        // If email, check if email is valid using regex
        if (!/^[a-z0-9.]{1,64}@[a-z0-9.]{1,64}$/i.test(email)) {
            alert("[Client]: " + email + " is invalid.")
            return false;
        }

        // Username validation using regex
        if (!/^[a-z0-9_-]{3,30}$/.test(username)) {
            alert("[Client]: Username must be 3-30 characters and contain valid characters (a-z, 0-9, _, or -)");
            return false;
        }

        // Check if user is only changing email/username
        if (currentPass === "" && newPass === "" && confirmNewPass === "") {
            return true;
        }

        // Check if current password is empty (Required to edit profile)
        if (currentPass === "") {
            alert("[Client]: Current password field is required to change the password..");
            return false;
        }



        // Check password length (user changing password)
        if (currentPass.length < 8 || newPass.length < 8 || confirmNewPass.length < 8) {
            alert("[Client]: Password lengths cannot be less than 8 characters.");
            return false;
        }

        //example of using flash via javascript
        //find the flash container, create a new element, appendChild
        if (newPass !== confirmNewPass) {
            //find the container
            let flash = document.getElementById("flash");
            //create a div (or whatever wrapper we want)
            let outerDiv = document.createElement("div");
            outerDiv.className = "row justify-content-center";
            let innerDiv = document.createElement("div");

            //apply the CSS (these are bootstrap classes which we'll learn later)
            innerDiv.className = "alert alert-warning";
            //set the content
            innerDiv.innerText = "Password and Confirm password must match";

            outerDiv.appendChild(innerDiv);
            //add the element to the DOM (if we don't it merely exists in memory)
            flash.appendChild(outerDiv);
            return false;
        }
        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>