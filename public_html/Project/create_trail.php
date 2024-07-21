<?php
// UCID: dr475
// Date: 07/17/24
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
    $lat = se($_POST, "lat", null, false);
    $long = se($_POST, "long", null, false);
    $length = se($_POST, "length", null, false);
    $difficulty = se($_POST, "difficulty", null, false);
    $features = se($_POST, "features", null, false);
    //$thumbnail = se($_POST, "thumbnail", null, false);

    $hasError = false;
}
?>

<?php

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
        <label for="region">Region:</label>
        <input type="text" name="region" id="region" />
    </div>
    <div class="mb-3">
        <label for="country">Country:</label>
        <input type="text" name="country" id="country" />
    </div>
    <div class="mb-3">
        <label for="lat">Latitude:</label>
        <input type="number" name="lat" id="lat" />
    </div>
    <div class="mb-3">
        <label for="long">Longitude:</label>
        <input type="number" name="long" id="long" />
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
    <div class="mb-3">
        <label for="thumb">Thumbnail:</label>
        <input type="text" name="thumb" id="thumb" />
    </div>
    <input type="submit" value="Submit Trail" name="create_trail" />
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
            alert("Latitude is invalid.")
            return false;
        }

        // Check if longtitude is valid using regex
        if (!/^[-]?([1-9]?\d(\.\d+)?|1[0-7]\d(\.\d+)?|180(\.0+)?)$/i.test(long)) {
            alert("Longitude is invalid.")
            return false;
        }

        // Check if length is valid (non negative)
        if (parseFloat(length) <= 0) {
            alert("Please enter a length greater than 0 miles.");
            return false;
        }

        // Check if difficulty selection is valid
        if (diff != "easy" || diff != "beg" || diff != "int" || diff != "hard"){
            alert("Invalid difficulty selection, please select a drop down option.");
            return false;
        }

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>