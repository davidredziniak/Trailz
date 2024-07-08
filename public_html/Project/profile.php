<?php
// UCID: dr475
// Date: 07/08/24
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
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");
        try {
            $stmt->execute($params);
            flash("Profile saved", "success");
        } catch (Exception $e) {
            users_check_duplicate($e->errorInfo);
        }
        //select fresh data from table
        $stmt = $db->prepare("SELECT id, email, username from Users where id = :id LIMIT 1");
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
            //echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
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
                //TODO validate current
                $stmt = $db->prepare("SELECT password from Users where id = :id");
                try {
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        if (password_verify($current_password, $result["password"])) {
                            $query = "UPDATE Users set password = :password where id = :id";
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
<form method="POST" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php se($email); ?>" />
    </div>
    <div class="mb-3">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php se($username); ?>" />
    </div>
    <!-- DO NOT PRELOAD PASSWORD -->
    <div><h4>Password Reset</h4></div>
    <div class="mb-3">
        <label for="cp">Current Password:</label>
        <input type="password" name="currentPassword" id="cp" />
    </div>
    <div class="mb-3">
        <label for="np">New Password:</label>
        <input type="password" name="newPassword" id="np" />
    </div>
    <div class="mb-3">
        <label for="conp">Confirm Password:</label>
        <input type="password" name="confirmPassword" id="conp" />
    </div>
    <input type="submit" value="Update Profile" name="save" />
</form>

<script>
    function validate(form) {
        let email = form.email.value;
        let username = form.username.value;
        let currentPass = form.currentPassword.value;
        let newPass = form.newPassword.value;
        let confirmNewPass = form.confirmPassword.value;

        // Check if email is empty
        if (email === ""){
            alert("[Client]: Email field cannot be empty.");
            return false;
        }

        // Check if username is empty
        if (username === ""){
            alert("[Client]: Username field cannot be empty.");
            return false;
        }

        // If email, check if email is valid using regex
        if (!/^[a-z0-9.]{1,64}@[a-z0-9.]{1,64}$/i.test(email)){
            alert("[Client]: " + email + " is invalid.")
            return false;
        }

        // Username validation using regex
        if (!/^[a-z0-9_-]{3,30}$/.test(username)){
            alert("[Client]: Username must be 3-30 characters and contain valid characters (a-z, 0-9, _, or -)");
            return false;
        }

        // Check if current password is empty (Required to edit profile)
        if (currentPass === ""){
            alert("[Client]: Current password field is required to change the password..");
            return false;
        }
        
        // Check if user is only changing email/username
        if (currentPass === "" && newPass === "" && confirmNewPass === ""){
            return true;
        }

        // Check password length (user changing password)
        if (currentPass.length < 8 || newPass.length < 8 || confirmNewPass.length < 8){
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