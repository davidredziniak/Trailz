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

<body class="bg-dark">
    <div class="container-sm p-5 rounded-2" style="background-color: #ffffff;">
        <h1>Fetch Trail Info</h1>
        <p>Add trails to the database by submitting a latitude, longitude, and radius.</p>
        <form>
            <div class="input-group mb-3">
                <span class="input-group-text">Latitude</span>
                <input type="text" id="latitude" class="form-control" name="lat" placeholder="-90.00 to 90.00"  aria-describedby="basic-addon1">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Longitude</span>
                <input type="text" id="longitude" class="form-control" name="long" placeholder="-180.00 to 180.00" aria-describedby="basic-addon1">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Radius</span>
                <input type="text" id="radius" class="form-control" name="radius" placeholder="100 miles max." aria-describedby="basic-addon1">
            </div>
            <input type="submit" class="btn btn-primary btn-md" value="Fetch" />
        </form>
    </div>
    <div class="container-sm p-5 rounded-2 mt-5" style="background-color: #ffffff;">
        <h2>Result</h2>
        <div class="row">
            
        </div>
    </div>
    </div>
</body>

<?php
require(__DIR__ . "/../../../partials/flash.php");
