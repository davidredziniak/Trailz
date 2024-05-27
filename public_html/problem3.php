<?php
$a1 = [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10];
$a2 = [-1, 1, -2, 2, 3, -3, -4, 5];
$a3 = [-0.01, -0.0001, -.15];
$a4 = ["-1", "2", "-3", "4", "-5", "5", "-6", "6", "-7", "7"];

function bePositive($arr) {
    echo "<br>Processing Array:<br><pre>" . var_export($arr, true) . "</pre>";
    echo "<br>Positive output:<br>";
    $output = [];
    //start edits
    // DR475 - 05/27/24
    for($i = 0; $i < count($arr); $i++){
        $positive = abs($arr[$i]);
        $type = gettype($arr[$i]);
        switch($type){
            case "string":
                $output[$i] = strval($positive);
                break;
            case "double":
            case "integer":
                $output[$i] = $positive;
                break;
            default:
                //unexpected type
        }
    }
    //end edits
    
    //displays the output along with their types
    $mappedOutput = array_map(function($o) {
        $type = strtoupper(substr(gettype($o), 0, 1));
        return "$o ($type)";
    }, $output);
    echo implode(', <br>', $mappedOutput);
}
echo "Problem 3: Be Positive<br>";
?>
<table>
    <thread>
        <th>A1</th>
        <th>A2</th>
        <th>A3</th>
        <th>A4</th>
    </thread>
    <tbody>
        <tr>
            <td>
                <?php bePositive($a1); ?>
            </td>
            <td>
                <?php bePositive($a2); ?>
            </td>
            <td>
                <?php bePositive($a3); ?>
            </td>
            <td>
                <?php bePositive($a4); ?>
            </td>
        </tr>
</table>
<style>
    table {
        border-spacing: 2em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
    }
</style>