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
    if ($difficulty != "easy" && $difficulty != "beg" && $difficulty != "int" && $difficulty != "adv") {
        flash("Difficulty selection is invalid. Please select an option from the drop down.", "danger");
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

    $user_id = get_user_id();

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
        $stmt = $db->prepare("INSERT INTO `Trails` (name, description, city, region, country, coord, length, difficulty, features, thumbnail) VALUES(:name, :desc, :city, :region, :country, POINT(:lat, :long), :length, :difficulty, :features, '')");
        try {
            $stmt->execute([":name" => $name, ":desc" => $desc, ":city" => $city, ":region" => $region, ":country" => $country, ":lat" => $lat, ":long" => $long, ":length" => $length, ":difficulty" => $difficulty, ":features" => $features]);
            $trail_id = $db->lastInsertId();
            // Insert into User_Trails to keep track of user_submitted trails
            $stmt2 = $db->prepare("INSERT INTO User_Trails (user_id, trail_id) VALUES(:user_id, :trail_id)");
            try {
                $stmt2->execute([":user_id" => $user_id, ":trail_id" => $trail_id]);
                flash("Successfully submitted a new trail!", "success");
            } catch (Exception $e) {
                flash("An unexpected error occurred submitting user trail information, please try again", "danger");
            }
        } catch (Exception $e) {
            echo '<pre>' . var_dump($e) . '</pre>';
            flash("An unexpected error occurred initially when submitting a new trail, please try again", "danger");
        }
    }
}
?>

<body class="bg-dark">
    <div class="container mt-5 mb-4 p-5 rounded-2 w-25" style="background-color: #ffffff;">
        <h2>Submit Trail</h2>
        <hr>
        <form method="POST" onsubmit="return validate(this);">
            <div class="container-xlg mt-2 mb-4">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" name="name" id="name" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="desc" class="form-label">Description:</label>
                    <input type="text" name="desc" id="desc" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City:</label>
                    <input type="text" name="city" id="city" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="region" class="form-label">State/Region:</label>
                    <input type="text" name="region" id="region" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="country" class="form-label">Country:</label>
                    <input type="text" name="country" id="country" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="lat" class="form-label">Latitude:</label>
                    <input type="text" name="lat" id="lat" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="long" class="form-label">Longitude:</label>
                    <input type="text" name="long" id="long" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="length" class="form-label">Length:</label>
                    <input type="number" name="length" id="length" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="difficulty" class="form-label">Difficulty:</label>
                    <select class="form-select" name="difficulty" id="difficulty" required>
                        <option value="">Please choose</option>
                        <option value="easy">Easiest</option>
                        <option value="beg">Beginner</option>
                        <option value="int">Intermediate</option>
                        <option value="adv">Advanced</option>
                    </select>
                </div>
                <div class="mb-3" >
                    <label for="feats" class="form-label">Features:</label>
                    <input type="text" name="feats" id="feats" class="form-control" />
                </div>
            </div>
            <div class="row mt-4">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><input type="submit" value="Submit" name="create_trail" class="btn btn-primary" /></div>
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
        if (name.length > 30) {
            flash("The length of the Name field should not be greater than 30 chars.", "warning");
            return false;
        }
        if (desc.length > 300) {
            flash("The length of the Description field should not be greater than 300 chars.", "warning");
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
        if (feats.length > 30) {
            flash("The length of the Features field should not be greater than 30 chars.", "warning");
            return false;
        }

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>