<?php

require_once(__DIR__ . '/../oc-config.php');

/*
    Returns information on name run through NCIC.
    TODO: Add a check here to check the admin panel to determine if Randomized names are allowed
*/
if (isset($_POST['ncic_name'])){
    name();
}
if (isset($_POST['ncic_plate'])){
    plate();
}

function name()
{
    $name = $_POST['ncic_name'];


    if(strpos($name, ' ') !== false) {
        $name_arr = explode(" ", $name);
        $first_name = $name_arr[0];
        $last_name = $name_arr[1];

        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (!$link) {
            die('Could not connect: ' .mysql_error());
        }

        $sql = "SELECT id, first_name, last_name, dob, address, sex, race, dl_status, hair_color, build, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age FROM ncic_names WHERE first_name = \"$first_name\" and last_name = \"$last_name\"";

        $result=mysqli_query($link, $sql);

        $encode = array();

        $num_rows = $result->num_rows;
        if($num_rows == 0)
        {
            $encode["noResult"] = "true";
        }
        else
        {

            while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
            {
                $userId = $row[0];
                $encode["userId"] = $row[0];
                $encode["first_name"] = $row[1];
                $encode["last_name"] = $row[2];
                $encode["dob"] = $row[3];
                $encode["address"] = $row[4];
                $encode["sex"] = $row[5];
                $encode["race"] = $row[6];
                $encode["dl_status"] = $row[7];
                $encode["hair_color"] = $row[8];
                $encode["build"] = $row[9];
                $encode["age"] = $row[10];
            }
            mysqli_close($link);

            /* Check for Warrants */
            $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if (!$link) {
                die('Could not connect: ' .mysql_error());
            }

            $sql = "SELECT id, name_id, warrant_name FROM ncic_warrants WHERE name_id = \"$userId\"";

            $result=mysqli_query($link, $sql);

            $num_rows = $result->num_rows;
            if($num_rows == 0)
            {
                $encode["noWarrants"] = "true";
            }
            else
            {
                $warrantIndex = 0;
                while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
                {
                    $encode["warrantId"][$warrantIndex] = $row[0];
                    $encode["warrant_name"][$warrantIndex] = $row[2];

                    $warrantIndex++;
                }
                mysqli_close($link);
            }

            /* Check for Citations */
            $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if (!$link) {
                die('Could not connect: ' .mysql_error());
            }

            $sql = "SELECT id, name_id, citation_name FROM ncic_citations WHERE name_id = \"$userId\"";

            $result=mysqli_query($link, $sql);

            $num_rows = $result->num_rows;
            if($num_rows == 0)
            {
                $encode["noCitations"] = "true";
            }
            else
            {
                $citationIndex = 0;
                while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
                {
                    $encode["citationId"][$citationIndex] = $row[0];
                    $encode["citation_name"][$citationIndex] = $row[2];

                    $citationIndex++;
                }
                mysqli_close($link);
            }

        }

        echo json_encode($encode);


    } else {
        $encode = array();
        $encode["noResult"] = "true";
        echo json_encode($encode);
    }
}

function plate()
{
    $plate = $_POST['ncic_plate'];

    //Remove all spaces from plate
    $plate = str_replace(' ', '', $plate);
    //Set plate to all uppercase
    $plate = strtoupper($plate);
    //Convert all O to 0
    $plate = str_replace('O', '0', $plate);
    //Remove al hyphens
    $plate = str_replace('-', '', $plate);
    //Remove all special characters
    $plate = preg_replace('/[^A-Za-z0-9\-]/', '', $plate);

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $sql = "SELECT ncic_plates.*, ncic_names.first_name, ncic_names.last_name FROM ncic_plates INNER JOIN ncic_names ON ncic_names.id=ncic_plates.name_id WHERE veh_plate = \"$plate\"";

    $result=mysqli_query($link, $sql);

    $encode = array();

    $num_rows = $result->num_rows;
    if($num_rows == 0)
    {
        $encode["noResult"] = "true";
    }
    else
    {
        $result=mysqli_query($link, $sql);

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            $owner = $row[11]." ".$row[12];

            $encode["plate"] = $row[2];
            $encode["veh_make"] = $row[3];
            $encode["veh_model"] = $row[4];
            $encode["veh_color"] = $row[5];
            $encode["veh_ro"] = $owner;
            $encode["veh_insurance"] = $row[6];
            $encode["flags"] = $row[7];
            $encode["veh_reg_state"] = $row[8];
            $encode["notes"] = $row[9];

        }
        mysqli_close($link);
    }

    echo json_encode($encode);
}

function firearm()
{

}

?>
