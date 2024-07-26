<?php
require_once(__DIR__ . "/../../partials/nav.php");
// Check if user is logged in
is_logged_in(true);
$result = [];

?>
<?php
if (isset($_GET["find"])) {
    $difficulties = ["unsp", "easy", "beg", "int", "adv"];
    $hasError = false;
    $type = se($_GET, "find", null, false);

    // Check if query params are set
    switch ($type) {
        case "location":
            if (!isset($_GET["lat"]) || !isset($_GET["long"]) || !isset($_GET["radius"])) {
                flash("Latitude, longtitude or radius is not set.", "danger");
                $hasError = true;
            }
            break;
        case "other":
            if (!isset($_GET["length"]) || !isset($_GET["difficulty"]) || !isset($_GET["country"])) {
                flash("Length, country or difficulty is not set.", "danger");
                $hasError = true;
            }
            break;
        default:
            flash("Search type is invalid.", "danger");
            $hasError = true;
    }

    $limit = 10;

    if (isset($_GET["limit"])) {
        // Check if limit is valid
        $user_limit = se($_GET, "limit", null, false);
        if (!empty($user_limit)) {
            $user_limit = intval($user_limit);
            if ($user_limit > 100 || $user_limit < 1) {
                flash("Limit needs to be in a range 1 to 100.", "danger");
                $hasError = true;
            } else {
                $limit = $user_limit;
            }
        }
    }

    if ($type === "location" && !$hasError) {
        $lat = se($_GET, "lat", null, false);
        $long = se($_GET, "long", null, false);
        $radius = se($_GET, "radius", null, false);

        if (empty($lat) || empty($long)) {
            flash("Latitude and longitude must both be set.", "danger");
            $hasError = true;
        }

        if (empty($radius)) {
            flash("Radius must be set.", "danger");
            $hasError = true;
        }

        $radius = floatval($radius);
        $lat = floatval($lat);
        $long = floatval($long);

        // Check radius
        if ($radius <= 0 || $radius > 100) {
            flash("Radius must be greater than 0 and less than 100 miles.", "danger");
            $hasError = true;
        }

        // Check latitude
        if (!is_valid_latitude($lat)) {
            flash("Latitude must be valid. -90.00 to 90.00", "danger");
            $hasError = true;
        }

        // Check longtitude
        if (!is_valid_longtitude($long)) {
            flash("Longitude must be valid. -90.00 to 90.00", "danger");
            $hasError = true;
        }

        if (!$hasError) {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, name, city, country, length, difficulty, (3959 * acos(cos(radians(:lat)) * cos(radians(ST_X(`coord`))) * cos( radians(ST_Y(`coord`)) - radians(:long)) + sin(radians(:lat)) * sin(radians(ST_X(`coord`))))) AS distance FROM `Trails` HAVING distance <= :distance ORDER BY distance LIMIT " . intval($limit) . ";");
            try {
                $stmt->execute([":lat" => $lat, ":long" => $long, ":distance" => $radius]);
                $r = $stmt->fetchAll();
                if ($r) {
                    $result = $r;
                } else {
                    flash("No results available.", "danger");
                }
            } catch (Exception $e) {
                flash("An unexpected error occurred when searching for trails.", "danger");
            }
        }
    }

    if ($type === "other" && !$hasError) {
        $countries = array("Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, The Democratic Republic of The", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-bissau", "Guyana", "Haiti", "Heard Island and Mcdonald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and The South Sandwich Islands", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe");

        $length = se($_GET, "length", "", false);
        $country = se($_GET, "country", "", false);
        $diff = se($_GET, "difficulty", "", false);
        $search_length = false;
        $search_country = false;
        $search_diff = false;

        if (empty($length) && empty($country) && empty($diff)) {
            $hasError = true;
            flash("You must specify at least one field (length, country, or difficulty).", "warning");
        }

        // Check if length is provided 
        if (!empty($length)) {
            $length = floatval($length);
            if ($length <= 0 || $length > 100) {
                $hasError = true;
                flash("Length of the trails requested is invalid.", "danger");
            } else {
                $search_length = true;
            }
        }

        // Check if country is provided
        if (!empty($country)) {
            $country = trim($country);
            if (strlen($country) <= 0 || strlen($country) > 50) {
                $hasError = true;
                flash("Country requested is invalid.", "danger");
            } else {
                $search_country = true;
            }
        }

        // Check if specific difficulty is provided
        if (!empty($diff)) {
            $diff = trim($diff);
            if (!in_array($diff, $difficulties)) {
                $hasError = true;
                flash("Invalid difficulty requested.", "danger");
            } else {
                $search_diff = true;
                switch ($diff) {
                    case "unsp":
                        $diff = "Unspecified";
                        break;
                    case "beg":
                        $diff = "Beginner";
                        break;
                    case "easy":
                        $diff = "Easiest";
                        break;
                    case "int":
                        $diff = "Intermediate";
                        break;
                    case "adv":
                        $diff = "Advanced";
                        break;
                }
            }
        }

        // Check if country is valid
        if(!in_array($country, $countries)){
            $hasError = true;
            flash("Invalid country selection", "warning");
        }

        // Build query
        if (!$hasError) {
            $query = "";

            if ($search_country) {
                $query .= "country='" . $country . "'";
            }

            if ($search_diff) {
                if (strlen($query) > 0) {
                    $query .= " AND ";
                }
                $query .= "difficulty='" . $diff . "'";
            }

            if ($search_length) {
                if (strlen($query) > 0) {
                    $query .= " AND ";
                }
                $query .= "length <=" . $length . "";
            }

            $db = getDB();
            $stmt = $db->prepare("SELECT id, name, city, country, length, difficulty FROM `Trails` WHERE " . $query . " LIMIT " . intval($limit) . ";");
            try {
                $stmt->execute();
                $r = $stmt->fetchAll();
                if ($r) {
                    $result = $r;
                } else {
                    flash("No results available.", "danger");
                }
            } catch (Exception $e) {
                flash(". var_export($e, true) .", "danger");
            }
        }
    }
}
?>

<body class="bg-dark">
    <div class="container mt-5">
        <div class="row gx-3">
            <div class="col-md-6">
                <div class="container-sm p-5 rounded-2" style="background-color: #ffffff;">
                    <h4>By Location</h4>
                    <hr>
                    <form method="GET" onsubmit="return validate(this);">
                        <div class="mb-3">
                            <label for="lat" class="form-label">Latitude:</label>
                            <input type="text" name="lat" id="lat" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="long" class="form-label">Longitude:</label>
                            <input type="text" name="long" id="long" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="lat" class="form-label">Radius:</label>
                            <input type="radius" name="radius" id="radius" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="limit" class="form-label">Limit:</label>
                            <input type="number" name="limit" id="limit" class="form-control" />
                        </div>
                        <button class="btn btn-primary" name="find" value="location" type="submit">Find</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="container-sm p-5 rounded-2" style="background-color: #ffffff;">
                    <h4>By Specification</h4>
                    <hr>
                    <form method="GET" onsubmit="return validate(this);">
                        <div class="mb-3">
                            <label for="country" class="form-label">Country:</label>
                            <select class="form-select" name="country" id="country">
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Aland Islands">Aland Islands</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="American Samoa">American Samoa</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Anguilla">Anguilla</option>
                                <option value="Antarctica">Antarctica</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Aruba">Aruba</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bermuda">Bermuda</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Bouvet Island">Bouvet Island</option>
                                <option value="Brazil">Brazil</option>
                                <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                <option value="Brunei Darussalam">Brunei Darussalam</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Cape Verde">Cape Verde</option>
                                <option value="Cayman Islands">Cayman Islands</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Christmas Island">Christmas Island</option>
                                <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo">Congo</option>
                                <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                <option value="Cook Islands">Cook Islands</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Cote D'ivoire">Cote D'ivoire</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                <option value="Faroe Islands">Faroe Islands</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="French Guiana">French Guiana</option>
                                <option value="French Polynesia">French Polynesia</option>
                                <option value="French Southern Territories">French Southern Territories</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Gibraltar">Gibraltar</option>
                                <option value="Greece">Greece</option>
                                <option value="Greenland">Greenland</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guadeloupe">Guadeloupe</option>
                                <option value="Guam">Guam</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guernsey">Guernsey</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-bissau">Guinea-bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hong Kong">Hong Kong</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Isle of Man">Isle of Man</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jersey">Jersey</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                <option value="Korea, Republic of">Korea, Republic of</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Macao">Macao</option>
                                <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Martinique">Martinique</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mayotte">Mayotte</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                <option value="Moldova, Republic of">Moldova, Republic of</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Netherlands Antilles">Netherlands Antilles</option>
                                <option value="New Caledonia">New Caledonia</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Niue">Niue</option>
                                <option value="Norfolk Island">Norfolk Island</option>
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Pitcairn">Pitcairn</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Puerto Rico">Puerto Rico</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Reunion">Reunion</option>
                                <option value="Romania">Romania</option>
                                <option value="Russian Federation">Russian Federation</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Helena">Saint Helena</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                <option value="Swaziland">Swaziland</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-leste">Timor-leste</option>
                                <option value="Togo">Togo</option>
                                <option value="Tokelau">Tokelau</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Viet Nam">Viet Nam</option>
                                <option value="Virgin Islands, British">Virgin Islands, British</option>
                                <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                                <option value="Wallis and Futuna">Wallis and Futuna</option>
                                <option value="Western Sahara">Western Sahara</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="length" class="form-label">Maximum Length:</label>
                            <input type="number" name="length" id="length" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="difficulty" class="form-label">Difficulty:</label>
                            <select class="form-select" name="difficulty" id="difficulty">
                                <option value="">Please choose</option>
                                <option value="unsp">Unspecified</option>
                                <option value="easy">Easiest</option>
                                <option value="beg">Beginner</option>
                                <option value="int">Intermediate</option>
                                <option value="adv">Advanced</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="limit" class="form-label">Limit:</label>
                            <input type="number" name="limit" id="limit" class="form-control" />
                        </div>
                        <button class="btn btn-primary" name="find" value="other" type="submit">Find</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (!count($result) == 0) : ?>
        <div class="container">
            <div class="col-md-12">
                <div class="container-lg mt-5 p-5 rounded-2" style="background-color: #ffffff;">
                    <h4>Trails</h4>
                    <hr>
                    <?php foreach ($result as $trail) : ?>
                        <div class="row mt-1">
                            <div class="col-md-8">
                                <p>
                                    <b><?php echo $trail['name']; ?></b>,
                                    <?php echo $trail['country']; ?> -
                                    <b>Length</b>: <?php echo $trail['length']; ?> mi -
                                    <b>Difficulty</b>: <?php echo $trail['difficulty']; ?>
                                    <?php if (array_key_exists("distance", $trail)) : ?>
                                        - <b>Distance</b>: <?php echo number_format($trail['distance'], 2); ?> mi
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-4 d-flex justify-content-end">
                                <p>
                                    <a href="./trail.php?id=<?php echo $trail['id'] ?>">View</a>
                                    <?php if (has_role("Admin") || is_trail_owner($trail['id'])) : ?>
                                        <?php echo '<a href="./edit_trail.php?id=' . $trail['id'] . '">Edit</a>'; ?>
                                        <?php echo '<a href="./delete_trail.php?id=' . $trail['id'] . '">Delete</a>'; ?>
                                    <?php endif; ?>
                                </p>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
<script>
    function validate(form) {
        const countries = ["Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, The Democratic Republic of The", "Cook Islands", "Costa Rica", "Cote D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-bissau", "Guyana", "Haiti", "Heard Island and Mcdonald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and The South Sandwich Islands", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe"];

        if (form.find.value == "location") {
            let lat = form.lat.value;
            let long = form.long.value;
            let radius = form.radius.value;
            let limit = form.limit.value;

            // Check if empty values
            if (lat == "" || long == "" || radius == "") {
                flash("You must enter latitude, longitude, and radius.", "warning");
                return false;
            }

            // Check if specified limit is valid
            if (limit !== "") {
                limit = parseInt(limit);
                if (limit <= 0 || limit > 100) {
                    flash("Limit specified must be a number in the range 1-100.", "warning");
                    return false;
                }
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
            return true;
        } else if (form.find.value == "other") {
            let country = form.country.value;
            let length = form.length.value;
            let diff = form.difficulty.value;
            let limit = form.limit.value;

            if (country == "" && length == "" && diff == "") {
                flash("You must specify a field between country, max length or difficulty.", "warning");
                return false;
            }

            // Check if specified limit is valid
            if (limit !== "") {
                limit = parseInt(limit);
                if (limit <= 0 || limit > 100) {
                    flash("Limit specified must be a number in the range 1-100.", "warning");
                    return false;
                }
            }

            // Check country length is valid
            if (country.length > 50) {
                flash("The length of the country should not be greater than 50 chars.", "warning");
                return false;
            }

            // Check if length is valid (non negative)
            if (length !== "" && parseFloat(length) <= 0) {
                flash("Please enter a length greater than 0 miles.", "warning");
                return false;
            }

            // Check if length is valid (non negative)
            if (length !== "" && parseFloat(length) <= 0) {
                flash("Please enter a length greater than 0 miles.", "warning");
                return false;
            }

            // Check if difficulty selection is valid
            if (diff !== "" && diff !== "unsp" && diff != "easy" && diff != "beg" && diff != "int" && diff != "adv") {
                flash("Invalid difficulty selection, please select a drop down option.", "warning");
                return false;
            }

            // Check if country is valid
            if (!countries.includes(country)){
                flash("Invalid country selected.", "warning");
                return false;
            }

            return true;
        } else {
            return false;
        }
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>