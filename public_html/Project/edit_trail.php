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
        $countries = array("Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, The Democratic Republic of The", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-bissau", "Guyana", "Haiti", "Heard Island and Mcdonald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and The South Sandwich Islands", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe");

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

        // Check if difficulty is one of five options (Unspecified, Easiest, Beginner, Intermediate, Hard)
        if ($difficulty != "unsp" && $difficulty != "easy" && $difficulty != "beg" && $difficulty != "int" && $difficulty != "hard") {
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
        if (strlen($city) > 50) {
            flash("The length of the City field should not be greater than 50 chars.", "danger");
            $hasError = true;
        }
        if (strlen($region) > 50) {
            flash("The length of the State/Region field should not be greater than 50 chars.", "danger");
            $hasError = true;
        }
        if (strlen($country) > 50) {
            flash("The length of the Country field should not be greater than 50 chars.", "danger");
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

        // Check if country is valid
        if(!in_array($country, $countries)){
            $hasError = true;
            flash("Invalid country selection", "warning");
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
                <select class="form-select" name="country" id="country">
                    <option value="Afghanistan" <?php if ($trail["country"]  == "Afghanistan") echo 'selected="Selected"' ?>>Afghanistan</option>
                    <option value="Aland Islands" <?php if ($trail["country"]  == "Aland Islands") echo 'selected="Selected"' ?>>Aland Islands</option>
                    <option value="Albania" <?php if ($trail["country"]  == "Albania") echo 'selected="Selected"' ?>>Albania</option>
                    <option value="Algeria" <?php if ($trail["country"]  == "Algeria") echo 'selected="Selected"' ?>>Algeria</option>
                    <option value="American Samoa" <?php if ($trail["country"]  == "American Samoa") echo 'selected="Selected"' ?>>American Samoa</option>
                    <option value="Andorra" <?php if ($trail["country"]  == "Andorra") echo 'selected="Selected"' ?>>Andorra</option>
                    <option value="Angola" <?php if ($trail["country"]  == "Angola") echo 'selected="Selected"' ?>>Angola</option>
                    <option value="Anguilla" <?php if ($trail["country"]  == "Anguilla") echo 'selected="Selected"' ?>>Anguilla</option>
                    <option value="Antarctica" <?php if ($trail["country"]  == "Antarctica") echo 'selected="Selected"' ?>>Antarctica</option>
                    <option value="Antigua and Barbuda" <?php if ($trail["country"]  == "Antigua and Barbuda") echo 'selected="Selected"' ?>>Antigua and Barbuda</option>
                    <option value="Argentina" <?php if ($trail["country"]  == "Argentina") echo 'selected="Selected"' ?>>Argentina</option>
                    <option value="Armenia" <?php if ($trail["country"]  == "Armenia") echo 'selected="Selected"' ?>>Armenia</option>
                    <option value="Aruba" <?php if ($trail["country"]  == "Aruba") echo 'selected="Selected"' ?>>Aruba</option>
                    <option value="Australia" <?php if ($trail["country"]  == "Australia") echo 'selected="Selected"' ?>>Australia</option>
                    <option value="Austria" <?php if ($trail["country"]  == "Austria") echo 'selected="Selected"' ?>>Austria</option>
                    <option value="Azerbaijan" <?php if ($trail["country"]  == "Azerbaijan") echo 'selected="Selected"' ?>>Azerbaijan</option>
                    <option value="Bahamas" <?php if ($trail["country"]  == "Bahamas") echo 'selected="Selected"' ?>>Bahamas</option>
                    <option value="Bahrain" <?php if ($trail["country"]  == "Bahrain") echo 'selected="Selected"' ?>>Bahrain</option>
                    <option value="Bangladesh" <?php if ($trail["country"]  == "Bangladesh") echo 'selected="Selected"' ?>>Bangladesh</option>
                    <option value="Barbados" <?php if ($trail["country"]  == "Barbados") echo 'selected="Selected"' ?>>Barbados</option>
                    <option value="Belarus" <?php if ($trail["country"]  == "Belarus") echo 'selected="Selected"' ?>>Belarus</option>
                    <option value="Belgium" <?php if ($trail["country"]  == "Belgium") echo 'selected="Selected"' ?>>Belgium</option>
                    <option value="Belize" <?php if ($trail["country"]  == "Belize") echo 'selected="Selected"' ?>>Belize</option>
                    <option value="Benin" <?php if ($trail["country"]  == "Benin") echo 'selected="Selected"' ?>>Benin</option>
                    <option value="Bermuda" <?php if ($trail["country"]  == "Bermuda") echo 'selected="Selected"' ?>>Bermuda</option>
                    <option value="Bhutan" <?php if ($trail["country"]  == "Bhutan") echo 'selected="Selected"' ?>>Bhutan</option>
                    <option value="Bolivia" <?php if ($trail["country"]  == "Bolivia") echo 'selected="Selected"' ?>>Bolivia</option>
                    <option value="Bosnia and Herzegovina" <?php if ($trail["country"]  == "Bosnia and Herzegovina") echo 'selected="Selected"' ?>>Bosnia and Herzegovina</option>
                    <option value="Botswana" <?php if ($trail["country"]  == "Botswana") echo 'selected="Selected"' ?>>Botswana</option>
                    <option value="Bouvet Island" <?php if ($trail["country"]  == "Bouvet Island") echo 'selected="Selected"' ?>>Bouvet Island</option>
                    <option value="Brazil" <?php if ($trail["country"]  == "Brazil") echo 'selected="Selected"' ?>>Brazil</option>
                    <option value="British Indian Ocean Territory" <?php if ($trail["country"]  == "British Indian Ocean Territory") echo 'selected="Selected"' ?>>British Indian Ocean Territory</option>
                    <option value="Brunei Darussalam" <?php if ($trail["country"]  == "Brunei Darussalam") echo 'selected="Selected"' ?>>Brunei Darussalam</option>
                    <option value="Bulgaria" <?php if ($trail["country"]  == "Bulgaria") echo 'selected="Selected"' ?>>Bulgaria</option>
                    <option value="Burkina Faso" <?php if ($trail["country"]  == "Burkina Faso") echo 'selected="Selected"' ?>>Burkina Faso</option>
                    <option value="Burundi" <?php if ($trail["country"]  == "Burundi") echo 'selected="Selected"' ?>>Burundi</option>
                    <option value="Cambodia" <?php if ($trail["country"]  == "Cambodia") echo 'selected="Selected"' ?>>Cambodia</option>
                    <option value="Cameroon" <?php if ($trail["country"]  == "Cameroon") echo 'selected="Selected"' ?>>Cameroon</option>
                    <option value="Canada" <?php if ($trail["country"]  == "Canada") echo 'selected="Selected"' ?>>Canada</option>
                    <option value="Cape Verde" <?php if ($trail["country"]  == "Cape Verde") echo 'selected="Selected"' ?>>Cape Verde</option>
                    <option value="Cayman Islands" <?php if ($trail["country"]  == "Cayman Islands") echo 'selected="Selected"' ?>>Cayman Islands</option>
                    <option value="Central African Republic" <?php if ($trail["country"]  == "Central African Republic") echo 'selected="Selected"' ?>>Central African Republic</option>
                    <option value="Chad" <?php if ($trail["country"]  == "Chad") echo 'selected="Selected"' ?>>Chad</option>
                    <option value="Chile" <?php if ($trail["country"]  == "Chile") echo 'selected="Selected"' ?>>Chile</option>
                    <option value="China" <?php if ($trail["country"]  == "China") echo 'selected="Selected"' ?>>China</option>
                    <option value="Christmas Island" <?php if ($trail["country"]  == "Christmas Island") echo 'selected="Selected"' ?>>Christmas Island</option>
                    <option value="Cocos (Keeling) Islands" <?php if ($trail["country"]  == "Cocos (Keeling) Islands") echo 'selected="Selected"' ?>>Cocos (Keeling) Islands</option>
                    <option value="Colombia" <?php if ($trail["country"]  == "Colombia") echo 'selected="Selected"' ?>>Colombia</option>
                    <option value="Comoros" <?php if ($trail["country"]  == "Comoros") echo 'selected="Selected"' ?>>Comoros</option>
                    <option value="Congo" <?php if ($trail["country"]  == "Congo") echo 'selected="Selected"' ?>>Congo</option>
                    <option value="Congo, The Democratic Republic of The" <?php if ($trail["country"]  == "Congo, The Democratic Republic of The") echo 'selected="Selected"' ?>>Congo, The Democratic Republic of The</option>
                    <option value="Cook Islands" <?php if ($trail["country"]  == "Cook Islands") echo 'selected="Selected"' ?>>Cook Islands</option>
                    <option value="Costa Rica" <?php if ($trail["country"]  == "Costa Rica") echo 'selected="Selected"' ?>>Costa Rica</option>
                    <option value="Cote D'ivoire" <?php if ($trail["country"]  == "Cote D'ivoire") echo 'selected="Selected"' ?>>Cote D'ivoire</option>
                    <option value="Croatia" <?php if ($trail["country"]  == "Croatia") echo 'selected="Selected"' ?>>Croatia</option>
                    <option value="Cuba" <?php if ($trail["country"]  == "Cuba") echo 'selected="Selected"' ?>>Cuba</option>
                    <option value="Cyprus" <?php if ($trail["country"]  == "Cyprus") echo 'selected="Selected"' ?>>Cyprus</option>
                    <option value="Czech Republic" <?php if ($trail["country"]  == "Czech Republic") echo 'selected="Selected"' ?>>Czech Republic</option>
                    <option value="Denmark" <?php if ($trail["country"]  == "Denmark") echo 'selected="Selected"' ?>>Denmark</option>
                    <option value="Djibouti" <?php if ($trail["country"]  == "Djibouti") echo 'selected="Selected"' ?>>Djibouti</option>
                    <option value="Dominica" <?php if ($trail["country"]  == "Dominica") echo 'selected="Selected"' ?>>Dominica</option>
                    <option value="Dominican Republic" <?php if ($trail["country"]  == "Dominican Republic") echo 'selected="Selected"' ?>>Dominican Republic</option>
                    <option value="Ecuador" <?php if ($trail["country"]  == "Ecuador") echo 'selected="Selected"' ?>>Ecuador</option>
                    <option value="Egypt" <?php if ($trail["country"]  == "Egypt") echo 'selected="Selected"' ?>>Egypt</option>
                    <option value="El Salvador" <?php if ($trail["country"]  == "El Salvador") echo 'selected="Selected"' ?>>El Salvador</option>
                    <option value="Equatorial Guinea" <?php if ($trail["country"]  == "Equatorial Guinea") echo 'selected="Selected"' ?>>Equatorial Guinea</option>
                    <option value="Eritrea" <?php if ($trail["country"]  == "Eritrea") echo 'selected="Selected"' ?>>Eritrea</option>
                    <option value="Estonia" <?php if ($trail["country"]  == "Estonia") echo 'selected="Selected"' ?>>Estonia</option>
                    <option value="Ethiopia" <?php if ($trail["country"]  == "Ethiopia") echo 'selected="Selected"' ?>>Ethiopia</option>
                    <option value="Falkland Islands (Malvinas)" <?php if ($trail["country"]  == "Falkland Islands (Malvinas)") echo 'selected="Selected"' ?>>Falkland Islands (Malvinas)</option>
                    <option value="Faroe Islands" <?php if ($trail["country"]  == "Faroe Islands") echo 'selected="Selected"' ?>>Faroe Islands</option>
                    <option value="Fiji" <?php if ($trail["country"]  == "Fiji") echo 'selected="Selected"' ?>>Fiji</option>
                    <option value="Finland" <?php if ($trail["country"]  == "Finland") echo 'selected="Selected"' ?>>Finland</option>
                    <option value="France" <?php if ($trail["country"]  == "France") echo 'selected="Selected"' ?>>France</option>
                    <option value="French Guiana" <?php if ($trail["country"]  == "French Guiana") echo 'selected="Selected"' ?>>French Guiana</option>
                    <option value="French Polynesia" <?php if ($trail["country"]  == "French Polynesia") echo 'selected="Selected"' ?>>French Polynesia</option>
                    <option value="French Southern Territories" <?php if ($trail["country"]  == "French Southern Territories") echo 'selected="Selected"' ?>>French Southern Territories</option>
                    <option value="Gabon" <?php if ($trail["country"]  == "Gabon") echo 'selected="Selected"' ?>>Gabon</option>
                    <option value="Gambia" <?php if ($trail["country"]  == "Gambia") echo 'selected="Selected"' ?>>Gambia</option>
                    <option value="Georgia" <?php if ($trail["country"]  == "Georgia") echo 'selected="Selected"' ?>>Georgia</option>
                    <option value="Germany" <?php if ($trail["country"]  == "Germany") echo 'selected="Selected"' ?>>Germany</option>
                    <option value="Ghana" <?php if ($trail["country"]  == "Ghana") echo 'selected="Selected"' ?>>Ghana</option>
                    <option value="Gibraltar" <?php if ($trail["country"]  == "Gibraltar") echo 'selected="Selected"' ?>>Gibraltar</option>
                    <option value="Greece" <?php if ($trail["country"]  == "Greece") echo 'selected="Selected"' ?>>Greece</option>
                    <option value="Greenland" <?php if ($trail["country"]  == "Greenland") echo 'selected="Selected"' ?>>Greenland</option>
                    <option value="Grenada" <?php if ($trail["country"]  == "Grenada") echo 'selected="Selected"' ?>>Grenada</option>
                    <option value="Guadeloupe" <?php if ($trail["country"]  == "Guadeloupe") echo 'selected="Selected"' ?>>Guadeloupe</option>
                    <option value="Guam" <?php if ($trail["country"]  == "Guam") echo 'selected="Selected"' ?>>Guam</option>
                    <option value="Guatemala" <?php if ($trail["country"]  == "Guatemala") echo 'selected="Selected"' ?>>Guatemala</option>
                    <option value="Guernsey" <?php if ($trail["country"]  == "Guernsey") echo 'selected="Selected"' ?>>Guernsey</option>
                    <option value="Guinea" <?php if ($trail["country"]  == "Guinea") echo 'selected="Selected"' ?>>Guinea</option>
                    <option value="Guinea-bissau" <?php if ($trail["country"]  == "Guinea-bissau") echo 'selected="Selected"' ?>>Guinea-bissau</option>
                    <option value="Guyana" <?php if ($trail["country"]  == "Guyana") echo 'selected="Selected"' ?>>Guyana</option>
                    <option value="Haiti" <?php if ($trail["country"]  == "Haiti") echo 'selected="Selected"' ?>>Haiti</option>
                    <option value="Heard Island and Mcdonald Islands" <?php if ($trail["country"]  == "Heard Island and Mcdonald Islands") echo 'selected="Selected"' ?>>Heard Island and Mcdonald Islands</option>
                    <option value="Holy See (Vatican City State)" <?php if ($trail["country"]  == "Holy See (Vatican City State)") echo 'selected="Selected"' ?>>Holy See (Vatican City State)</option>
                    <option value="Honduras" <?php if ($trail["country"]  == "Honduras") echo 'selected="Selected"' ?>>Honduras</option>
                    <option value="Hong Kong" <?php if ($trail["country"]  == "Hong Kong") echo 'selected="Selected"' ?>>Hong Kong</option>
                    <option value="Hungary" <?php if ($trail["country"]  == "Hungary") echo 'selected="Selected"' ?>>Hungary</option>
                    <option value="Iceland" <?php if ($trail["country"]  == "Iceland") echo 'selected="Selected"' ?>>Iceland</option>
                    <option value="India" <?php if ($trail["country"]  == "India") echo 'selected="Selected"' ?>>India</option>
                    <option value="Indonesia" <?php if ($trail["country"]  == "Indonesia") echo 'selected="Selected"' ?>>Indonesia</option>
                    <option value="Iran, Islamic Republic of" <?php if ($trail["country"]  == "Iran, Islamic Republic of") echo 'selected="Selected"' ?>>Iran, Islamic Republic of</option>
                    <option value="Iraq" <?php if ($trail["country"]  == "Iraq") echo 'selected="Selected"' ?>>Iraq</option>
                    <option value="Ireland" <?php if ($trail["country"]  == "Ireland") echo 'selected="Selected"' ?>>Ireland</option>
                    <option value="Isle of Man" <?php if ($trail["country"]  == "Isle of Man") echo 'selected="Selected"' ?>>Isle of Man</option>
                    <option value="Israel" <?php if ($trail["country"]  == "Israel") echo 'selected="Selected"' ?>>Israel</option>
                    <option value="Italy" <?php if ($trail["country"]  == "Italy") echo 'selected="Selected"' ?>>Italy</option>
                    <option value="Jamaica" <?php if ($trail["country"]  == "Jamaica") echo 'selected="Selected"' ?>>Jamaica</option>
                    <option value="Japan" <?php if ($trail["country"]  == "Japan") echo 'selected="Selected"' ?>>Japan</option>
                    <option value="Jersey" <?php if ($trail["country"]  == "Jersey") echo 'selected="Selected"' ?>>Jersey</option>
                    <option value="Jordan" <?php if ($trail["country"]  == "Jordan") echo 'selected="Selected"' ?>>Jordan</option>
                    <option value="Kazakhstan" <?php if ($trail["country"]  == "Kazakhstan") echo 'selected="Selected"' ?>>Kazakhstan</option>
                    <option value="Kenya" <?php if ($trail["country"]  == "Kenya") echo 'selected="Selected"' ?>>Kenya</option>
                    <option value="Kiribati" <?php if ($trail["country"]  == "Kiribati") echo 'selected="Selected"' ?>>Kiribati</option>
                    <option value="Korea, Democratic People's Republic of" <?php if ($trail["country"]  == "Korea, Democratic People's Republic of") echo 'selected="Selected"' ?>>Korea, Democratic People's Republic of</option>
                    <option value="Korea, Republic of" <?php if ($trail["country"]  == "Korea, Republic of") echo 'selected="Selected"' ?>>Korea, Republic of</option>
                    <option value="Kuwait" <?php if ($trail["country"]  == "Kuwait") echo 'selected="Selected"' ?>>Kuwait</option>
                    <option value="Kyrgyzstan" <?php if ($trail["country"]  == "Kyrgyzstan") echo 'selected="Selected"' ?>>Kyrgyzstan</option>
                    <option value="Lao People's Democratic Republic" <?php if ($trail["country"]  == "Lao People's Democratic Republic") echo 'selected="Selected"' ?>>Lao People's Democratic Republic</option>
                    <option value="Latvia" <?php if ($trail["country"]  == "Latvia") echo 'selected="Selected"' ?>>Latvia</option>
                    <option value="Lebanon" <?php if ($trail["country"]  == "Lebanon") echo 'selected="Selected"' ?>>Lebanon</option>
                    <option value="Lesotho" <?php if ($trail["country"]  == "Lesotho") echo 'selected="Selected"' ?>>Lesotho</option>
                    <option value="Liberia" <?php if ($trail["country"]  == "Liberia") echo 'selected="Selected"' ?>>Liberia</option>
                    <option value="Libyan Arab Jamahiriya" <?php if ($trail["country"]  == "Libyan Arab Jamahiriya") echo 'selected="Selected"' ?>>Libyan Arab Jamahiriya</option>
                    <option value="Liechtenstein" <?php if ($trail["country"]  == "Liechtenstein") echo 'selected="Selected"' ?>>Liechtenstein</option>
                    <option value="Lithuania" <?php if ($trail["country"]  == "Lithuania") echo 'selected="Selected"' ?>>Lithuania</option>
                    <option value="Luxembourg" <?php if ($trail["country"]  == "Luxembourg") echo 'selected="Selected"' ?>>Luxembourg</option>
                    <option value="Macao" <?php if ($trail["country"]  == "Macao") echo 'selected="Selected"' ?>>Macao</option>
                    <option value="Macedonia, The Former Yugoslav Republic of" <?php if ($trail["country"]  == "Macedonia, The Former Yugoslav Republic of") echo 'selected="Selected"' ?>>Macedonia, The Former Yugoslav Republic of</option>
                    <option value="Madagascar" <?php if ($trail["country"]  == "Madagascar") echo 'selected="Selected"' ?>>Madagascar</option>
                    <option value="Malawi" <?php if ($trail["country"]  == "Malawi") echo 'selected="Selected"' ?>>Malawi</option>
                    <option value="Malaysia" <?php if ($trail["country"]  == "Malaysia") echo 'selected="Selected"' ?>>Malaysia</option>
                    <option value="Maldives" <?php if ($trail["country"]  == "Maldives") echo 'selected="Selected"' ?>>Maldives</option>
                    <option value="Mali" <?php if ($trail["country"]  == "Mali") echo 'selected="Selected"' ?>>Mali</option>
                    <option value="Malta" <?php if ($trail["country"]  == "Malta") echo 'selected="Selected"' ?>>Malta</option>
                    <option value="Marshall Islands" <?php if ($trail["country"]  == "Marshall Islands") echo 'selected="Selected"' ?>>Marshall Islands</option>
                    <option value="Martinique" <?php if ($trail["country"]  == "Martinique") echo 'selected="Selected"' ?>>Martinique</option>
                    <option value="Mauritania" <?php if ($trail["country"]  == "Mauritania") echo 'selected="Selected"' ?>>Mauritania</option>
                    <option value="Mauritius" <?php if ($trail["country"]  == "Mauritius") echo 'selected="Selected"' ?>>Mauritius</option>
                    <option value="Mayotte" <?php if ($trail["country"]  == "Mayotte") echo 'selected="Selected"' ?>>Mayotte</option>
                    <option value="Mexico" <?php if ($trail["country"]  == "Mexico") echo 'selected="Selected"' ?>>Mexico</option>
                    <option value="Micronesia, Federated States of" <?php if ($trail["country"]  == "Micronesia, Federated States of") echo 'selected="Selected"' ?>>Micronesia, Federated States of</option>
                    <option value="Moldova, Republic of" <?php if ($trail["country"]  == "Moldova, Republic of") echo 'selected="Selected"' ?>>Moldova, Republic of</option>
                    <option value="Monaco" <?php if ($trail["country"]  == "Monaco") echo 'selected="Selected"' ?>>Monaco</option>
                    <option value="Mongolia" <?php if ($trail["country"]  == "Mongolia") echo 'selected="Selected"' ?>>Mongolia</option>
                    <option value="Montenegro" <?php if ($trail["country"]  == "Montenegro") echo 'selected="Selected"' ?>>Montenegro</option>
                    <option value="Montserrat" <?php if ($trail["country"]  == "Montserrat") echo 'selected="Selected"' ?>>Montserrat</option>
                    <option value="Morocco" <?php if ($trail["country"]  == "Morocco") echo 'selected="Selected"' ?>>Morocco</option>
                    <option value="Mozambique" <?php if ($trail["country"]  == "Mozambique") echo 'selected="Selected"' ?>>Mozambique</option>
                    <option value="Myanmar" <?php if ($trail["country"]  == "Myanmar") echo 'selected="Selected"' ?>>Myanmar</option>
                    <option value="Namibia" <?php if ($trail["country"]  == "Namibia") echo 'selected="Selected"' ?>>Namibia</option>
                    <option value="Nauru" <?php if ($trail["country"]  == "Nauru") echo 'selected="Selected"' ?>>Nauru</option>
                    <option value="Nepal" <?php if ($trail["country"]  == "Nepal") echo 'selected="Selected"' ?>>Nepal</option>
                    <option value="Netherlands" <?php if ($trail["country"]  == "Netherlands") echo 'selected="Selected"' ?>>Netherlands</option>
                    <option value="Netherlands Antilles" <?php if ($trail["country"]  == "Netherlands Antilles") echo 'selected="Selected"' ?>>Netherlands Antilles</option>
                    <option value="New Caledonia" <?php if ($trail["country"]  == "New Caledonia") echo 'selected="Selected"' ?>>New Caledonia</option>
                    <option value="New Zealand" <?php if ($trail["country"]  == "New Zealand") echo 'selected="Selected"' ?>>New Zealand</option>
                    <option value="Nicaragua" <?php if ($trail["country"]  == "Nicaragua") echo 'selected="Selected"' ?>>Nicaragua</option>
                    <option value="Niger" <?php if ($trail["country"]  == "Niger") echo 'selected="Selected"' ?>>Niger</option>
                    <option value="Nigeria" <?php if ($trail["country"]  == "Nigeria") echo 'selected="Selected"' ?>>Nigeria</option>
                    <option value="Niue" <?php if ($trail["country"]  == "Niue") echo 'selected="Selected"' ?>>Niue</option>
                    <option value="Norfolk Island" <?php if ($trail["country"]  == "Norfolk Island") echo 'selected="Selected"' ?>>Norfolk Island</option>
                    <option value="Northern Mariana Islands" <?php if ($trail["country"]  == "Northern Mariana Islands") echo 'selected="Selected"' ?>>Northern Mariana Islands</option>
                    <option value="Norway" <?php if ($trail["country"]  == "Norway") echo 'selected="Selected"' ?>>Norway</option>
                    <option value="Oman" <?php if ($trail["country"]  == "Oman") echo 'selected="Selected"' ?>>Oman</option>
                    <option value="Pakistan" <?php if ($trail["country"]  == "Pakistan") echo 'selected="Selected"' ?>>Pakistan</option>
                    <option value="Palau" <?php if ($trail["country"]  == "Palau") echo 'selected="Selected"' ?>>Palau</option>
                    <option value="Palestinian Territory, Occupied" <?php if ($trail["country"]  == "Palestinian Territory, Occupied") echo 'selected="Selected"' ?>>Palestinian Territory, Occupied</option>
                    <option value="Panama" <?php if ($trail["country"]  == "Panama") echo 'selected="Selected"' ?>>Panama</option>
                    <option value="Papua New Guinea" <?php if ($trail["country"]  == "Papua New Guinea") echo 'selected="Selected"' ?>>Papua New Guinea</option>
                    <option value="Paraguay" <?php if ($trail["country"]  == "Paraguay") echo 'selected="Selected"' ?>>Paraguay</option>
                    <option value="Peru" <?php if ($trail["country"]  == "Peru") echo 'selected="Selected"' ?>>Peru</option>
                    <option value="Philippines" <?php if ($trail["country"]  == "Philippines") echo 'selected="Selected"' ?>>Philippines</option>
                    <option value="Pitcairn" <?php if ($trail["country"]  == "Pitcairn") echo 'selected="Selected"' ?>>Pitcairn</option>
                    <option value="Poland" <?php if ($trail["country"]  == "Poland") echo 'selected="Selected"' ?>>Poland</option>
                    <option value="Portugal" <?php if ($trail["country"]  == "Portugal") echo 'selected="Selected"' ?>>Portugal</option>
                    <option value="Puerto Rico" <?php if ($trail["country"]  == "Puerto Rico") echo 'selected="Selected"' ?>>Puerto Rico</option>
                    <option value="Qatar" <?php if ($trail["country"]  == "Qatar") echo 'selected="Selected"' ?>>Qatar</option>
                    <option value="Reunion" <?php if ($trail["country"]  == "Reunion") echo 'selected="Selected"' ?>>Reunion</option>
                    <option value="Romania" <?php if ($trail["country"]  == "Romania") echo 'selected="Selected"' ?>>Romania</option>
                    <option value="Russian Federation" <?php if ($trail["country"]  == "Russian Federation") echo 'selected="Selected"' ?>>Russian Federation</option>
                    <option value="Rwanda" <?php if ($trail["country"]  == "Rwanda") echo 'selected="Selected"' ?>>Rwanda</option>
                    <option value="Saint Helena" <?php if ($trail["country"]  == "Saint Helena") echo 'selected="Selected"' ?>>Saint Helena</option>
                    <option value="Saint Kitts and Nevis" <?php if ($trail["country"]  == "Saint Kitts and Nevis") echo 'selected="Selected"' ?>>Saint Kitts and Nevis</option>
                    <option value="Saint Lucia" <?php if ($trail["country"]  == "Saint Lucia") echo 'selected="Selected"' ?>>Saint Lucia</option>
                    <option value="Saint Pierre and Miquelon" <?php if ($trail["country"]  == "Saint Pierre and Miquelon") echo 'selected="Selected"' ?>>Saint Pierre and Miquelon</option>
                    <option value="Saint Vincent and The Grenadines" <?php if ($trail["country"]  == "Saint Vincent and The Grenadines") echo 'selected="Selected"' ?>>Saint Vincent and The Grenadines</option>
                    <option value="Samoa" <?php if ($trail["country"]  == "Samoa") echo 'selected="Selected"' ?>>Samoa</option>
                    <option value="San Marino" <?php if ($trail["country"]  == "San Marino") echo 'selected="Selected"' ?>>San Marino</option>
                    <option value="Sao Tome and Principe" <?php if ($trail["country"]  == "Sao Tome and Principe") echo 'selected="Selected"' ?>>Sao Tome and Principe</option>
                    <option value="Saudi Arabia" <?php if ($trail["country"]  == "Saudi Arabia") echo 'selected="Selected"' ?>>Saudi Arabia</option>
                    <option value="Senegal" <?php if ($trail["country"]  == "Senegal") echo 'selected="Selected"' ?>>Senegal</option>
                    <option value="Serbia" <?php if ($trail["country"]  == "Serbia") echo 'selected="Selected"' ?>>Serbia</option>
                    <option value="Seychelles" <?php if ($trail["country"]  == "Seychelles") echo 'selected="Selected"' ?>>Seychelles</option>
                    <option value="Sierra Leone" <?php if ($trail["country"]  == "Sierra Leone") echo 'selected="Selected"' ?>>Sierra Leone</option>
                    <option value="Singapore" <?php if ($trail["country"]  == "Singapore") echo 'selected="Selected"' ?>>Singapore</option>
                    <option value="Slovakia" <?php if ($trail["country"]  == "Slovakia") echo 'selected="Selected"' ?>>Slovakia</option>
                    <option value="Slovenia" <?php if ($trail["country"]  == "Slovenia") echo 'selected="Selected"' ?>>Slovenia</option>
                    <option value="Solomon Islands" <?php if ($trail["country"]  == "Solomon Islands") echo 'selected="Selected"' ?>>Solomon Islands</option>
                    <option value="Somalia" <?php if ($trail["country"]  == "Somalia") echo 'selected="Selected"' ?>>Somalia</option>
                    <option value="South Africa" <?php if ($trail["country"]  == "South Africa") echo 'selected="Selected"' ?>>South Africa</option>
                    <option value="South Georgia and The South Sandwich Islands" <?php if ($trail["country"]  == "South Georgia and The South Sandwich Islands") echo 'selected="Selected"' ?>>South Georgia and The South Sandwich Islands</option>
                    <option value="Spain" <?php if ($trail["country"]  == "Spain") echo 'selected="Selected"' ?>>Spain</option>
                    <option value="Sri Lanka" <?php if ($trail["country"]  == "Sri Lanka") echo 'selected="Selected"' ?>>Sri Lanka</option>
                    <option value="Sudan" <?php if ($trail["country"]  == "Sudan") echo 'selected="Selected"' ?>>Sudan</option>
                    <option value="Suriname" <?php if ($trail["country"]  == "Suriname") echo 'selected="Selected"' ?>>Suriname</option>
                    <option value="Svalbard and Jan Mayen" <?php if ($trail["country"]  == "Svalbard and Jan Mayen") echo 'selected="Selected"' ?>>Svalbard and Jan Mayen</option>
                    <option value="Swaziland" <?php if ($trail["country"]  == "Swaziland") echo 'selected="Selected"' ?>>Swaziland</option>
                    <option value="Sweden" <?php if ($trail["country"]  == "Sweden") echo 'selected="Selected"' ?>>Sweden</option>
                    <option value="Switzerland" <?php if ($trail["country"]  == "Switzerland") echo 'selected="Selected"' ?>>Switzerland</option>
                    <option value="Syrian Arab Republic" <?php if ($trail["country"]  == "Syrian Arab Republic") echo 'selected="Selected"' ?>>Syrian Arab Republic</option>
                    <option value="Taiwan" <?php if ($trail["country"]  == "Taiwan") echo 'selected="Selected"' ?>>Taiwan</option>
                    <option value="Tajikistan" <?php if ($trail["country"]  == "Tajikistan") echo 'selected="Selected"' ?>>Tajikistan</option>
                    <option value="Tanzania, United Republic of" <?php if ($trail["country"]  == "Tanzania, United Republic of") echo 'selected="Selected"' ?>>Tanzania, United Republic of</option>
                    <option value="Thailand" <?php if ($trail["country"]  == "Thailand") echo 'selected="Selected"' ?>>Thailand</option>
                    <option value="Timor-leste" <?php if ($trail["country"]  == "Timor-leste") echo 'selected="Selected"' ?>>Timor-leste</option>
                    <option value="Togo" <?php if ($trail["country"]  == "Togo") echo 'selected="Selected"' ?>>Togo</option>
                    <option value="Tokelau" <?php if ($trail["country"]  == "Tokelau") echo 'selected="Selected"' ?>>Tokelau</option>
                    <option value="Tonga" <?php if ($trail["country"]  == "Tonga") echo 'selected="Selected"' ?>>Tonga</option>
                    <option value="Trinidad and Tobago" <?php if ($trail["country"]  == "Trinidad and Tobago") echo 'selected="Selected"' ?>>Trinidad and Tobago</option>
                    <option value="Tunisia" <?php if ($trail["country"]  == "Tunisia") echo 'selected="Selected"' ?>>Tunisia</option>
                    <option value="Turkey" <?php if ($trail["country"]  == "Turkey") echo 'selected="Selected"' ?>>Turkey</option>
                    <option value="Turkmenistan" <?php if ($trail["country"]  == "Turkmenistan") echo 'selected="Selected"' ?>>Turkmenistan</option>
                    <option value="Turks and Caicos Islands" <?php if ($trail["country"]  == "Turks and Caicos Islands") echo 'selected="Selected"' ?>>Turks and Caicos Islands</option>
                    <option value="Tuvalu" <?php if ($trail["country"]  == "Tuvalu") echo 'selected="Selected"' ?>>Tuvalu</option>
                    <option value="Uganda" <?php if ($trail["country"]  == "Uganda") echo 'selected="Selected"' ?>>Uganda</option>
                    <option value="Ukraine" <?php if ($trail["country"]  == "Ukraine") echo 'selected="Selected"' ?>>Ukraine</option>
                    <option value="United Arab Emirates" <?php if ($trail["country"]  == "United Arab Emirates") echo 'selected="Selected"' ?>>United Arab Emirates</option>
                    <option value="United Kingdom" <?php if ($trail["country"]  == "United Kingdom") echo 'selected="Selected"' ?>>United Kingdom</option>
                    <option value="United States" <?php if ($trail["country"]  == "United States") echo 'selected="Selected"' ?>>United States</option>
                    <option value="United States Minor Outlying Islands" <?php if ($trail["country"]  == "United States Minor Outlying Islands") echo 'selected="Selected"' ?>>United States Minor Outlying Islands</option>
                    <option value="Uruguay" <?php if ($trail["country"]  == "Uruguay") echo 'selected="Selected"' ?>>Uruguay</option>
                    <option value="Uzbekistan" <?php if ($trail["country"]  == "Uzbekistan") echo 'selected="Selected"' ?>>Uzbekistan</option>
                    <option value="Vanuatu" <?php if ($trail["country"]  == "Vanuatu") echo 'selected="Selected"' ?>>Vanuatu</option>
                    <option value="Venezuela" <?php if ($trail["country"]  == "Venezuela") echo 'selected="Selected"' ?>>Venezuela</option>
                    <option value="Viet Nam" <?php if ($trail["country"]  == "Viet Nam") echo 'selected="Selected"' ?>>Viet Nam</option>
                    <option value="Virgin Islands, British" <?php if ($trail["country"]  == "Virgin Islands, British") echo 'selected="Selected"' ?>>Virgin Islands, British</option>
                    <option value="Virgin Islands, U.S." <?php if ($trail["country"]  == "Virgin Islands, U.S.") echo 'selected="Selected"' ?>>Virgin Islands, U.S.</option>
                    <option value="Wallis and Futuna" <?php if ($trail["country"]  == "Wallis and Futuna") echo 'selected="Selected"' ?>>Wallis and Futuna</option>
                    <option value="Western Sahara" <?php if ($trail["country"]  == "Western Sahara") echo 'selected="Selected"' ?>>Western Sahara</option>
                    <option value="Yemen" <?php if ($trail["country"]  == "Yemen") echo 'selected="Selected"' ?>>Yemen</option>
                    <option value="Zambia" <?php if ($trail["country"]  == "Zambia") echo 'selected="Selected"' ?>>Zambia</option>
                    <option value="Zimbabwe" <?php if ($trail["country"]  == "Zimbabwe") echo 'selected="Selected"' ?>>Zimbabwe</option>
                </select>
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
                    <option value="unsp" <?php if ($trail["difficulty"]  == "Unspecified") echo 'selected="Selected"' ?>>Unspecified</option>
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
        const countries = ["Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, The Democratic Republic of The", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-bissau", "Guyana", "Haiti", "Heard Island and Mcdonald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and The South Sandwich Islands", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe"];

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
        if (diff != "unsp" && diff != "easy" && diff != "beg" && diff != "int" && diff != "adv") {
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
        if (city.length > 50) {
            flash("The length of the City field should not be greater than 50 chars.", "warning");
            return false;
        }
        if (region.length > 50) {
            flash("The length of the State/Region field should not be greater than 50 chars.", "warning");
            return false;
        }
        if (country.length > 50) {
            flash("The length of the Country field should not be greater than 50 chars.", "warning");
            return false;
        }
        if (feats.length > 100) {
            flash("The length of the Features field should not be greater than 100 chars.", "warning");
            return false;
        }

        // Check if country is valid
        if (!countries.includes(country)) {
            flash("Invalid country selected.", "warning");
            return false;
        }

        return true;
    }
</script>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>