<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);
?>
<?php
if (isset($_POST["create_trail"])) {
    $name = se($_POST, "name", null, false);
    $desc = se($_POST, "desc", null, false);
    $city = se($_POST, "city", null, false);
    $region = se($_POST, "region", null, false);
    $country = se($_POST, "country", null, false);   
    $difficulty = se($_POST, "difficulty", null, false);
    $features = se($_POST, "feats", null, false);

    $lat = se($_POST, "lat", null, false);
    $long = se($_POST, "long", null, false);
    $length = se($_POST, "length", null, false);
    //$thumbnail = se($_POST, "thumbnail", null, false);

    $hasError = false;

    // Validate
    if (empty($name)){
        flash("Name of trail must not be empty", "danger");
        $hasError = true;
    }
    if (empty($desc)){
        flash("Description of trail must not be empty", "danger");
        $hasError = true;
    }
    if (empty($city)){
        flash("City must not be empty", "danger");
        $hasError = true;
    }
    if (empty($region)){
        flash("Region/state must not be empty", "danger");
        $hasError = true;
    }
    if (empty($country)){
        flash("Country must not be empty", "danger");
        $hasError = true;
    }
    if (empty($lat)){
        flash("Latitude must not be empty", "danger");
        $hasError = true;
    }
    if (empty($long)){
        flash("Longitude must not be empty", "danger");
        $hasError = true;
    }
    if (empty($length)){
        flash("Length of trail must not be empty", "danger");
        $hasError = true;
    }
    if (empty($difficulty)){
        flash("Difficulty must not be empty", "danger");
        $hasError = true;
    }
    if (empty($features)){
        flash("Features of trail must not be empty", "danger");
        $hasError = true;
    }

    // Check if difficulty is one of four options (Easiest, Beginner, Intermediate, Hard)
    if ($difficulty != "easy" && $difficulty != "beg" && $difficulty != "int" && $difficulty != "hard"){
        flash("Difficulty selection is invalid. Please select an option from the drop down.");
        $hasError = true;
    }

    $lat = floatval($lat);
    $long = floatval($long);

    // Check if latitude is valid
    if (!is_valid_latitude($lat)){
        flash("Latitude is invalid. Must be between -90 to 90", "danger");
        $hasError = true;
    }

    // Check if latitude is valid
    if (!is_valid_longtitude($long)){
        flash("Longitude is invalid. Must be between -180 to 180", "danger");
        $hasError = true;
    }

    $length = floatval($length);

    // Check if length is valid
    if ($length <= 0){
        flash("Length is invalid. Must be a positive number.", "danger");
        $hasError = true;
    }

    $user_id = get_user_id();

    // Convert difficulty
    switch($difficulty){
        case "beg":
            $difficulty = "Beginner";
            break;
        case "easy":
            $difficulty = "Easiest";
            break;
        case "int":
            $difficulty = "Intermediate";
            break;
        case "hard":
            $difficulty = "Hard";
            break;
    }

    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Trails (name, description, city, region, country, coord, length, difficulty, features) VALUES(:name, :desc, :city, :region, :country, POINT(:lat, :long), :length, :difficulty, :features)");
        try {
            $stmt->execute([":name" => $name, ":desc" => $desc, ":city" => $city, ":region" => $region, ":country" => $country, ":lat" => $lat, ":long" => $long, ":length" => $length, ":difficulty" => $difficulty, ":features" => $features]);
            $trail_id = $db->lastInsertId();
            // Insert into User_Trails to keep track of user_submitted trails
            $stmt2 = $db->prepare("INSERT INTO User_Trails (user_id, trail_id) VALUES(:user_id, :trail_id)");
            try{
                $stmt2->execute([":user_id" => $user_id, ":trail_id" => $trail_id]);
                flash("Successfully submitted a new trail!", "success");
            } catch (Exception $e) {
                flash("An unexpected error occurred submitting user trail information, please try again", "danger");
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred initially when submitting a new trail, please try again", "danger");
        }
    }
}
?>

<form method="POST" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" />
    </div>
    <div class="mb-3">
        <label for="desc">Description:</label>
        <input type="text" name="desc" id="desc" />
    </div>
    <div class="mb-3">
        <label for="city">City:</label>
        <input type="text" name="city" id="city" />
    </div>
    <div class="mb-3">
        <label for="region">State/Region:</label>
        <input type="text" name="region" id="region" />
    </div>
    <div class="mb-3">
        <label for="country">Country:</label>
        <input type="text" name="country" id="country" />
    </div>
    <div class="mb-3">
        <label for="lat">Latitude:</label>
        <input type="text" name="lat" id="lat" />
    </div>
    <div class="mb-3">
        <label for="long">Longitude:</label>
        <input type="text" name="long" id="long" />
    </div>
    <div class="mb-3">
        <label for="length">Length:</label>
        <input type="number" name="length" id="length" />
    </div>
    <div class="mb-3">
        <label for="difficulty">Difficulty:</label>
        <select name="difficulty" id="difficulty" required>
            <option value="">Please choose</option>
            <option value="easy">Easiest</option>
            <option value="beg">Beginner</option>
            <option value="int">Intermediate</option>
            <option value="hard">Hard</option>
        </select>
        <br>
    </div>
    <div class="mb-3">
        <label for="feats">Features:</label>
        <input type="text" name="feats" id="feats" />
    </div>
    <input type="submit" value="Create Trail" name="create_trail" />
</form>

<script>
    function validate(form) {
        let name = form.name.value;
        let desc = form.desc.value;
        let city = form.city.value;
        let region = form.region.value;
        let country = form.country.value;
        let lat = form.lat.value;
        let long = form.long.value;
        let length = form.length.value;
        let diff = form.difficulty.value;
        let feats = form.feats.value;
        //let thumbnail = form.thumb.value;

        // Check if any of the fields are empty
        if (name === "" || desc === "" || city === "" || region === "" || country === "" || lat === "" || long === "" || length === "" || diff === "" || feats === "") {
            alert("All fields must be filled out.");
            return false;
        }

        // Check if name is valid using regex
        if (!/^[a-z0-9.]{1,50}/i.test(name)) {
            alert("Name of trail is invalid.")
            return false;
        }

        // Check if latitude is valid using regex
        if (!/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/.test(lat)) {
            alert("Latitude is invalid. Enter a value from -90.00 to 90.00")
            return false;
        }

        // Check if longtitude is valid using regex
        if (!/^[-]?([1-9]?\d(\.\d+)?|1[0-7]\d(\.\d+)?|180(\.0+)?)$/i.test(long)) {
            alert("Longitude is invalid. Enter a value from -180.00 to 180.00")
            return false;
        }

        // Check if length is valid (non negative)
        if (parseFloat(length) <= 0) {
            alert("Please enter a length greater than 0 miles.");
            return false;
        }

        // Check if difficulty selection is valid
        if (diff != "easy" && diff != "beg" && diff != "int" && diff != "hard"){
            alert("Invalid difficulty selection, please select a drop down option.");
            return false;
        }

        // Check lengths of input for string fields
        if (name.length > 30){
            alert("The length of the Name field should not be greater than 30 chars.");
            return false;
        }
        if (desc.length > 300){
            alert("The length of the Description field should not be greater than 300 chars.");
            return false;
        }
        if (city.length > 30){
            alert("The length of the City field should not be greater than 30 chars.");
            return false;
        }
        if (region.length > 30){
            alert("The length of the State/Region field should not be greater than 30 chars.");
            return false;
        }
        if (country.length > 30){
            alert("The length of the Country field should not be greater than 30 chars.");
            return false;
        }
        if (feats.length > 30){
            alert("The length of the Features field should not be greater than 30 chars.");
            return false;
        }

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>