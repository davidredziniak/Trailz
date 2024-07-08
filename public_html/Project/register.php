<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo se($_POST, "email", "", false); ?>" required />
    </div>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" value="<?php echo se($_POST, "username", "", false); ?>" required maxlength="30" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script>
    function validate(form) {
        var email = document.querySelector('[name="email"]').value;

        // Check if email is empty
        if (email === ""){
            alert("[Client]: Email field cannot be empty.");
            return false;
        }

        // Check if email is valid (contains @ and valid characters)
        if (!/^[a-z0-9.]{1,64}@[a-z0-9.]{1,64}$/i.test(email)){
            alert("[Client]: " + email + " is invalid.")
            return false;
        }

        var username = document.querySelector('[name="username"]').value;
        
        // Check if username is empty
        if (username === ""){
            alert("[Client]: Username field cannot be empty.");
            return false;
        }
        if(!/^[a-z0-9_-]{3,30}$/.test(username)){
            alert("[Client]: Username must be 3-30 characters and contain valid characters (a-z, 0-9, _, or -)");
            return false;
        }

        var pass = document.querySelector('[name="password"]').value;
        var confirmPass = document.querySelector('[name="confirm"]').value;

        // Check if passwords are empty
        if (pass === ""){
            alert("[Client]: Password field cannot be empty.");
            return false;
        }
        if (confirmPass === ""){
            alert("[Client]: Confirm Password field cannot be empty.");
            return false;
        }

        // Check password lengths
        if (pass.length < 8 || confirmPass.length < 8){
            alert("[Client]: Password length cannot be less than 8 characters.");
            return false;
        }

        // Check if passwords match
        if (pass !== confirmPass){
            alert("[Client]: Password and Confirm Password do not match.");
            return false;
        }
        return true;
    }
</script>
<?php
//TODO 2: add PHP Code
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
    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!preg_match('/^[a-z0-9_-]{3,30}$/', $username)) {
        flash("Username must only contain 3-30 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (strlen($password) < 8) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (
        strlen($password) > 0 && $password !== $confirm
    ) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
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