<?php
// UCID: dr475
// Date: 07/17/24
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);

$result = [];

?>
<?php
$user_id = get_user_id();

if (isset($_GET["find"])) {
    $difficulties = ["easy", "beg", "int", "hard"];
    $hasError = false;
    $type = se($_GET, "find", null, false);

    // Check if query params are set
    switch($type){
        case "location":
            if(!isset($_GET["lat"]) || !isset($_GET["long"]) || !isset($_GET["radius"])){
                flash("Latitude, longtitude or radius is not set.", "danger");
                $hasError = true;
            }
            break;
        case "location":
            if(!isset($_GET["length"]) || !isset($_GET["diff"]) || !isset($_GET["country"])){
                flash("Length, country or difficulty is not set.", "danger");
                $hasError = true;

            }
            break;
        default:
            flash("Search type is invalid.", "danger");
            $hasError = true;
    } 
    
    $sortby = se($_GET, "sortby", "ASC", false);
    $limit = se($_GET, "limit", 10, false);

    // Check if limit is valid
    if ($limit > 100 || $limit < 1){
        flash("Limit is invalid.", "danger");
        $hasError = true;
    }

    // Check if sortby is valid
    if ($sortby != "ASC" && $sortby != "DESC"){
        flash("Sort by is invalid.", "danger");
        $hasError = true;
    }

    if($type === "location" && !$hasError){
        $lat = se($_GET, "lat", null, false);
        $long = se($_GET, "long", null, false);
        $radius = se($_GET, "radius", null, false);

        if(empty($lat) || empty($long)){
            flash("Latitude and longitude must both be set.", "danger");
            $hasError = true;
        }

        if(empty($radius)){
            flash("Radius must be set.", "danger");
            $hasError = true;
        }

        $radius = floatval($radius);
        $lat = floatval($lat);
        $long = floatval($long);

        // Check radius
        if ($radius <= 0 || $radius > 100){
            flash("Radius must be greater than 0 and less than 100 miles.", "danger");
            $hasError = true;
        }

        // Check latitude
        if (!is_valid_latitude($lat)){
            flash("Latitude must be valid. -90.00 to 90.00", "danger");
            $hasError = true;
        }

        // Check longtitude
        if (!is_valid_longtitude($long)){
            flash("Longitude must be valid. -90.00 to 90.00", "danger");
            $hasError = true;
        }

        if(!$hasError){
            $db = getDB();
            $stmt = $db->prepare("SELECT name, city, country, length, difficulty, (3959 * acos(cos(radians(:lat)) * cos(radians(ST_X(`coord`))) * cos( radians(ST_Y(`coord`)) - radians(:long)) + sin(radians(:lat)) * sin(radians(ST_X(`coord`))))) AS distance FROM Trails HAVING distance <= :distance ORDER BY distance;");
            try {
                $stmt->execute([":lat" => $lat, ":long" => $long, ":distance" => $radius]);
                $r = $stmt->fetchAll();
                if($r){
                    $result = $r;
                }
                else{
                    flash("No results available.", "danger");
                }
            } catch (Exception $e) {
                flash("An unexpected error occurred when searching for trails.", "danger");
            }
        }

    }
    
    if($type === "other" && !$hasError){
        $length = se($_GET, "length", "", false);
        $country = se($_GET, "country", "", false);
        $diff = se($_GET, "difficulty", "", false);
            // Check if length is provided 
            if(!empty($length)){
                $length = floatval($length);
                if($length <= 0 || $length > 100){
                    $hasError = true;
                    flash("Length of the trails requested is invalid.", "danger");
                }
                else{
                    $max_length = $length;
                }
            }

            // Check if country is provided
            if (!empty($country)){
                $country = trim($country);
                if(strlen($country) <= 0 || strlen($country) > 30){
                    $hasError = true;
                    flash("Country requested is invalid.", "danger");
                }
                else{
                    $fcountry = $country;
                }
            }

            // Check if specific difficulty is provided
            if (!empty($diff)){
                $diff = trim($diff);
                if (!in_array($diff, $difficulties)){
                    $hasError = true;
                    flash("Invalid difficulty requested.", "danger");
                }
                else{
                    $difficulty = $diff;
                }
            }
    }
}
?>
<form method="GET" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="lat">Latitude:</label>
        <input type="text" name="lat" id="lat" />
    </div>
    <div class="mb-3">
        <label for="long">Longitude:</label>
        <input type="text" name="long" id="long" />
    </div>
    <div class="mb-3">
        <label for="lat">Radius:</label>
        <input type="radius" name="radius" id="radius" />
    </div>
    <button class="btn" name="find" value="location" type="submit">Find</button>
</form>
<form method="GET" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="country">Country:</label>
        <input type="text" name="country" id="country" />
    </div>
    <div class="mb-3">
        <label for="length">Length:</label>
        <input type="number" name="length" id="length" />
    </div>
    <div class="mb-3">
        <label for="difficulty">Difficulty:</label>
        <select name="difficulty" id="difficulty">
            <option value="">Please choose</option>
            <option value="easy">Easiest</option>
            <option value="beg">Beginner</option>
            <option value="int">Intermediate</option>
            <option value="hard">Hard</option>
        </select>
        <br>
    </div>
    <button class="btn" name="find" value="other" type="submit">Find</button>
</form>
<div class="container-fluid">
    <h4>Trails</h4>
    <div class="container mx-auto">
        <div class="row justify-content-center">
            <?php foreach ($result as $trail) : ?>
                <div class="col">
                    <?php echo $trail['name'] ?>
                    <?php echo $trail['distance'] ?>
                </div>
            <?php endforeach; ?>
            <?php if (count($result) === 0) : ?>
                <div class="col-12">
                    No trails found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    function validate(form) {

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>