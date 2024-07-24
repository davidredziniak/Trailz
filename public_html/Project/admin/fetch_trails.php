<?php
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../lib/manage_trail_data.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}


$result = [];
if (isset($_GET["lat"]) && isset($_GET["long"]) && isset($_GET["radius"])) {
    $data = ["lat" => $_GET["lat"], "lon" => $_GET["long"], "radius" => $_GET["radius"], "per_page" => "1000"];
    $endpoint = "https://trailapi-trailapi.p.rapidapi.com/trails/explore/";
    $isRapidAPI = true;
    $rapidAPIHost = "trailapi-trailapi.p.rapidapi.com";
    $result = get($endpoint, "API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        process_trails($result);
    } else {
        $result = [];
    }
}
?>
<script>
    function validate(form) {
        let lat = form.lat.value;
        let long = form.long.value;
        let radius = form.radius.value;

        // Check if any of the fields are empty
        if (lat === "" || long === "" || radius === "") {
            flash("All fields must be filled out.", "warning");
            return false;
        }

        // Check if latitude is valid using regex
        if (!/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/.test(lat)) {
            flash("Latitude is invalid. Enter a value from -90.00 to 90.00", "warning");
            return false;
        }

        // Check if longitude is valid using regex
        if (!/^[-]?([1-9]?\d(\.\d+)?|1[0-7]\d(\.\d+)?|180(\.0+)?)$/i.test(long)) {
            flash("Longitude is invalid. Enter a value from -180.00 to 180.00", "warning");
            return false;
        }

        // Check if radius is valid (non negative)
        if (parseFloat(radius) <= 0 || parseFloat(radius) > 100) {
            flash("Please enter a radius in range 1 to 100.", "warning");
            return false;
        }

        return true;
    }
</script>

<body class="bg-dark">
    <div class="container-sm mt-5 p-5 rounded-2" style="background-color: #ffffff;">
        <h1>Fetch Trail Info</h1>
        <p>Add trails to the database from the API by searching the area given a latitude, longitude, and radius.</p>
        <form onsubmit="return validate(this)">
            <div class="input-group mb-3">
                <span class="input-group-text">Latitude</span>
                <input type="text" id="latitude" class="form-control" name="lat" placeholder="-90.00 to 90.00" aria-describedby="basic-addon1">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Longitude</span>
                <input type="text" id="longitude" class="form-control" name="long" placeholder="-180.00 to 180.00" aria-describedby="basic-addon1">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Radius</span>
                <input type="text" id="radius" class="form-control" name="radius" placeholder="100 miles max." aria-describedby="basic-addon1">
            </div>
            <div class="row mt-4">
                <div class="col"></div><!-- This is a filler column -->
                <div class="col-auto"><input type="submit" class="btn btn-primary btn-md" value="Fetch" /></div>
            </div>
        </form>
    </div>
</body>

<?php
require(__DIR__ . "/../../../partials/flash.php");
