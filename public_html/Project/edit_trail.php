<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);
if (isset($_GET["id"])) {
    $id = intval(se($_GET, "id", 0, false));
    is_valid_trail($id, true);

    // Check if user has permissions to edit the trail
    if (!has_role("Admin") && !is_trail_owner($id)) {
        flash("You don't have permission to edit this trail.", "danger");
        die(header("Location: " . get_url("view_trails.php")));
    } 

    $trail = get_trail_by_id($id);

    if (isset($_POST["save"])) {
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
            $stmt = $db->prepare("UPDATE Trails SET name=:name, description=:desc, city=:city, region=:region, country=:country, coord=POINT(:lat, :long), length=:length, difficulty=:difficulty, features=:features WHERE id=:id;");
            try {
                $stmt->execute([":name" => $name, ":desc" => $desc, ":city" => $city, ":region" => $region, ":country" => $country, ":lat" => $lat, ":long" => $long, ":length" => $length, ":difficulty" => $difficulty, ":features" => $features, ":id" => $id]);
                flash("Successfully edited the trail!", "success");
            } catch (Exception $e) {
                flash("An unexpected error occurred initially when saving the trail, please try again" . var_export($e, true), "danger");
            }

            // Fetch fresh trail data
            $trail = get_trail_by_id($id);
        }
    }    
}
else{
    die(header("Location: " . get_url("view_trails.php")));
}
?>

<form method="POST">
    <div class="mb-3">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php se($trail, "name"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="desc">Description:</label>
        <input type="text" name="desc" id="desc" value="<?php se($trail, "description"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="city">City:</label>
        <input type="text" name="city" id="city" value="<?php se($trail, "city"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="region">State/Region:</label>
        <input type="text" name="region" id="region" value="<?php se($trail, "region"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="country">Country:</label>
        <input type="text" name="country" id="country" value="<?php se($trail, "country"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="lat">Latitude:</label>
        <input type="text" name="lat" id="lat" value="<?php se($trail, "latitude"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="long">Longitude:</label>
        <input type="text" name="long" id="long" value="<?php se($trail, "longitude"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="length">Length:</label>
        <input type="number" name="length" id="length" value="<?php se($trail, "length"); ?>"/>
    </div>
    <div class="mb-3">
        <label for="difficulty">Difficulty:</label>
        <select name="difficulty" id="difficulty" required>
            <option value="">Please choose</option>

            <option value="easy" <?php if($trail["difficulty"]  == "Easiest") echo 'selected="Selected"'?>>Easiest</option>
            <option value="beg" <?php if($trail["difficulty"] == "Beginner") echo 'selected="Selected"'?>>Beginner</option>
            <option value="int" <?php if($trail["difficulty"]  == "Intermediate") echo 'selected="Selected"'?>>Intermediate</option>
            <option value="hard" <?php if($trail["difficulty"] == "Hard") echo 'selected="Selected"'?>>Hard</option>
        </select>
        <br>
    </div>
    <div class="mb-3">
        <label for="feats">Features:</label>
        <input type="text" name="feats" id="feats" value="<?php se($trail, "features"); ?>" />
    </div>
    <button class="btn" name="save" value="true" type="submit">Save</button>
</form>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>