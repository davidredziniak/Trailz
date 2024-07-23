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
        if (empty($name)) {
            flash("Name of trail must not be empty", "danger");
            $hasError = true;
        }
        if (empty($desc)) {
            flash("Description of trail must not be empty", "danger");
            $hasError = true;
        }
        if (empty($city)) {
            flash("City must not be empty", "danger");
            $hasError = true;
        }
        if (empty($region)) {
            flash("Region/state must not be empty", "danger");
            $hasError = true;
        }
        if (empty($country)) {
            flash("Country must not be empty", "danger");
            $hasError = true;
        }
        if (empty($lat)) {
            flash("Latitude must not be empty", "danger");
            $hasError = true;
        }
        if (empty($long)) {
            flash("Longitude must not be empty", "danger");
            $hasError = true;
        }

        if (empty($length)) {
            flash("Length of trail must not be empty", "danger");
            $hasError = true;
        }
        if (empty($difficulty)) {
            flash("Difficulty must not be empty", "danger");
            $hasError = true;
        }
        if (empty($features)) {
            flash("Features of trail must not be empty", "danger");
            $hasError = true;
        }

        // Check if difficulty is one of four options (Easiest, Beginner, Intermediate, Hard)
        if ($difficulty != "easy" && $difficulty != "beg" && $difficulty != "int" && $difficulty != "hard") {
            flash("Difficulty selection is invalid. Please select an option from the drop down.");
            $hasError = true;
        }

        // Check lengths of input for string fields
        if (strlen($name) > 50) {
            flash("The length of the Name field should not be greater than 50 chars.", "danger");
            $hasError = true;
        }
        if (strlen($desc) > 400) {
            flash("The length of the Description field should not be greater than 400 chars.", "danger");
            $hasError = true;
        }
        if (strlen($city) > 30) {
            flash("The length of the City field should not be greater than 30 chars.", "danger");
            $hasError = true;
        }
        if (strlen($region) > 30) {
            flash("The length of the State/Region field should not be greater than 30 chars.", "danger");
            $hasError = true;
        }
        if (strlen($country) > 30) {
            flash("The length of the Country field should not be greater than 30 chars.", "danger");
            $hasError = true;
        }
        if (strlen($features) > 100) {
            flash("The length of the Features field should not be greater than 100 chars.", "danger");
            $hasError = true;
        }

        $lat = floatval($lat);
        $long = floatval($long);

        // Check if latitude is valid
        if (!is_valid_latitude($lat)) {
            flash("Latitude is invalid. Must be between -90 to 90", "danger");
            $hasError = true;
        }

        // Check if latitude is valid
        if (!is_valid_longtitude($long)) {
            flash("Longitude is invalid. Must be between -180 to 180", "danger");
            $hasError = true;
        }

        $length = floatval($length);

        // Check if length is valid
        if ($length <= 0) {
            flash("Length is invalid. Must be a positive number.", "danger");
            $hasError = true;
        }

        // Convert difficulty
        switch ($difficulty) {
            case "beg":
                $difficulty = "Beginner";
                break;
            case "easy":
                $difficulty = "Easiest";
                break;
            case "int":
                $difficulty = "Intermediate";
                break;
            case "adv":
                $difficulty = "Advanced";
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
} else {
    die(header("Location: " . get_url("view_trails.php")));
}
?>

<body class="bg-dark">
    <div class="container mt-5 mb-4 p-5 rounded-2 w-25" style="background-color: #ffffff;">
        <h2>Edit Trail</h2>
        <hr>
        <form method="POST" onsubmit="return validate(this)">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" name="name" id="name" value="<?php se($trail, "name"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="desc" class="form-label">Description:</label>
                <input type="text" name="desc" id="desc" value="<?php se($trail, "description"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">City:</label>
                <input type="text" name="city" id="city" value="<?php se($trail, "city"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="region" class="form-label">State/Region:</label>
                <input type="text" name="region" id="region" value="<?php se($trail, "region"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="country" class="form-label">Country:</label>
                <input type="text" name="country" id="country" value="<?php se($trail, "country"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="lat" class="form-label">Latitude:</label>
                <input type="text" name="lat" id="lat" value="<?php se($trail, "latitude"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="long" class="form-label">Longitude:</label>
                <input type="text" name="long" id="long" value="<?php se($trail, "longitude"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="length" class="form-label">Length:</label>
                <input type="number" name="length" id="length" value="<?php se($trail, "length"); ?>" class="form-control" />
            </div>
            <div class="mb-3">
                <label for="difficulty" class="form-label">Difficulty:</label>
                <select class="form-select" name="difficulty" id="difficulty" required>
                    <option value="">Please choose</option>
                    <option value="easy" <?php if ($trail["difficulty"]  == "Easiest") echo 'selected="Selected"' ?>>Easiest</option>
                    <option value="beg" <?php if ($trail["difficulty"] == "Beginner") echo 'selected="Selected"' ?>>Beginner</option>
                    <option value="int" <?php if ($trail["difficulty"]  == "Intermediate") echo 'selected="Selected"' ?>>Intermediate</option>
                    <option value="adv" <?php if ($trail["difficulty"] == "Advanced") echo 'selected="Selected"' ?>>Hard</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="feats" class="form-label">Features:</label>
                <input type="text" name="feats" id="feats" value="<?php se($trail, "features"); ?>" class="form-control" />
            </div>
            <div class="row mt-4">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><button class="btn btn-primary" name="save" value="true" type="submit">Save</button></div>
            </div>
        </form>
    </div>
</body>

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
            flash("All fields must be filled out.", "warning");
            return false;
        }

        // Check if name is valid using regex
        if (!/^[a-z0-9.]{1,50}/i.test(name)) {
            flash("Name of trail is invalid.", "warning");
            return false;
        }

        // Check if latitude is valid using regex
        if (!/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/.test(lat)) {
            flash("Latitude is invalid. Enter a value from -90.00 to 90.00", "warning");
            return false;
        }

        // Check if longtitude is valid using regex
        if (!/^[-]?([1-9]?\d(\.\d+)?|1[0-7]\d(\.\d+)?|180(\.0+)?)$/i.test(long)) {
            flash("Longitude is invalid. Enter a value from -180.00 to 180.00", "warning");
            return false;
        }

        // Check if length is valid (non negative)
        if (parseFloat(length) <= 0) {
            flash("Please enter a length greater than 0 miles.", "warning");
            return false;
        }

        // Check if difficulty selection is valid
        if (diff != "easy" && diff != "beg" && diff != "int" && diff != "adv") {
            flash("Invalid difficulty selection, please select a drop down option.", "warning");
            return false;
        }

        // Check lengths of input for string fields
        if (name.length > 50) {
            flash("The length of the Name field should not be greater than 50 chars.", "warning");
            return false;
        }
        if (desc.length > 400) {
            flash("The length of the Description field should not be greater than 400 chars.", "warning");
            return false;
        }
        if (city.length > 30) {
            flash("The length of the City field should not be greater than 30 chars.", "warning");
            return false;
        }
        if (region.length > 30) {
            flash("The length of the State/Region field should not be greater than 30 chars.", "warning");
            return false;
        }
        if (country.length > 30) {
            flash("The length of the Country field should not be greater than 30 chars.", "warning");
            return false;
        }
        if (feats.length > 100) {
            flash("The length of the Features field should not be greater than 100 chars.", "warning");
            return false;
        }

        return true;
    }
</script>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>