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
        case "other":
            if(!isset($_GET["length"]) || !isset($_GET["difficulty"]) || !isset($_GET["country"])){
                flash("Length, country or difficulty is not set.", "danger");
                $hasError = true;

            }
            break;
        default:
            flash("Search type is invalid.", "danger");
            $hasError = true;
    } 
    
    $limit = 10;

    if (isset($_GET["limit"])){
        // Check if limit is valid
        $user_limit = se($_GET, "limit", null, false);
        if(!empty($user_limit)){
            $user_limit = intval($user_limit);
            if ($user_limit > 100 || $user_limit < 1){
                flash("Limit needs to be in a range 1 to 100.", "danger");
                $hasError = true;
            }
            else{
                $limit = $user_limit;
            }
        }
    }

    //$sortfield = se($_GET, "sort", null, false);
    //$sortby = se($_GET, "sortby", "ASC", false);

    

    // Check if sortby is valid
    //if ($sortby != "ASC" && $sortby != "DESC"){
    //    flash("Sort by is invalid.", "danger");
    //    $hasError = true;
    //}

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
            $stmt = $db->prepare("SELECT name, city, country, length, difficulty, (3959 * acos(cos(radians(:lat)) * cos(radians(ST_X(`coord`))) * cos( radians(ST_Y(`coord`)) - radians(:long)) + sin(radians(:lat)) * sin(radians(ST_X(`coord`))))) AS distance FROM Trails HAVING distance <= :distance ORDER BY distance LIMIT " . intval($limit) . ";");
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
        $search_length = false;
        $search_country = false;
        $search_diff = false;

        if (empty($length) && empty($country) && empty($diff)){
            $hasError = true;
            flash("You must specify at least one field (length, country, or difficulty).");
        }

        // Check if length is provided 
        if(!empty($length)){
            $length = floatval($length);
            if($length <= 0 || $length > 100){
                $hasError = true;
                flash("Length of the trails requested is invalid.", "danger");
            }
            else{
                $search_length = true;
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
                $search_country = true;
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
                $search_diff = true;
                switch($diff){
                    case "beg":
                        $diff = "Beginner";
                        break;
                    case "easy":
                        $diff = "Easiest";
                        break;
                    case "int":
                        $diff = "Intermediate";
                        break;
                    case "hard":
                        $diff = "Hard";
                        break;
                }
            }
        }
        
        // Build query
        if(!$hasError){
            $query = "";

            if($search_country){
                $query .= "country='" . $country . "'";
            }

            if($search_diff){
                if(strlen($query) > 0){
                    $query .= " AND ";
                }
                $query .= "difficulty='" . $diff . "'";
            }

            if($search_length){
                if(strlen($query) > 0){
                    $query .= " AND ";
                }
                $query .= "length <=" . $length . "";
            }

            $db = getDB();
            $stmt = $db->prepare("SELECT name, city, country, length, difficulty FROM Trails WHERE " . $query . " LIMIT " . intval($limit) . ";");
            try {
                $stmt->execute();
                $r = $stmt->fetchAll();
                if($r){
                    $result = $r;
                }
                else{
                    flash("No results available.", "danger");
                }
            } catch (Exception $e) {
                flash(". var_export($e, true) .", "danger");
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
    <div class="mb-3">
        <label for="limit">Limit:</label>
        <input type="number" name="limit" id="limit" />
    </div>
    <button class="btn" name="find" value="location" type="submit">Find</button>
</form>

<form method="GET" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="country">Country:</label>
        <input type="text" name="country" id="country" />
    </div>
    <div class="mb-3">
        <label for="length">Maximum Length:</label>
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
    <div class="mb-3">
        <label for="limit">Limit:</label>
        <input type="number" name="limit" id="limit" />
    </div>
    <button class="btn" name="find" value="other" type="submit">Find</button>
</form>
<?php if (!count($result) == 0) : ?>
    <div class="container-fluid">
        <h4>Trails</h4>
        <div class="container mx-auto">
            <div class="row justify-content-center">
                <?php foreach ($result as $trail) : ?>
                    <div class="col">
                        <?php echo $trail['name']; ?>
                        <?php echo $trail['country']; ?>
                        <?php echo $trail['length']; ?>
                        <?php echo $trail['difficulty']; ?>
                        <?php if (array_key_exists("distance", $trail)) : ?>
                            <?php echo $trail['distance']; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<script>
    function validate(form) {

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>