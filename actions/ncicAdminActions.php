<?php
require_once(__DIR__ . '/../oc-config.php');

/* Handle POST requests */
if (isset($_POST['create_citation'])){
    create_citation();
}
if (isset($_POST['delete_citation'])){
    delete_citation();
}
if (isset($_POST['delete_name'])){
    delete_name();
}
if (isset($_POST['delete_plate'])){
    delete_plate();
}
if (isset($_POST['delete_warrant'])){
    delete_warrant();
}
if (isset($_POST['create_warrant'])){
    create_warrant();
}
if (isset($_POST['create_name'])){
    create_name();
}
if (isset($_POST['create_plate'])){
    create_plate();
}
if (isset($_POST['reject_identity_request'])){
    rejectRequest();
}

function rejectRequest()
{
    $req_id = $_POST['id'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "DELETE FROM identity_requests WHERE req_id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $req_id);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['identityRequestMessage'] = '<div class="alert alert-success"><span>Successfully rejected request</span></div>';
    header("Location: ../oc-admin/ncicAdmin.php");
}

function getIdentityRequests()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "SELECT req_id, submittedByName, submitted_on FROM identity_requests";

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>There are no identity requests</span></div>";
    }
    else
    {
        echo '
            <table id="identityRequests" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>Request ID</th>
                <th>Submitted By</th>
                <th>Submitted On</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            echo '
            <tr>
                <td>'.$row[0].'</td>
                <td>'.$row[1].'</td>
                <td>'.$row[2].'</td>
                <td>
                    <form action="../actions/ncicAdminActions.php" method="post">
                    <button name="viewRequestDetails" data-toggle="modal" data-target="#requestDetails" class="btn btn-xs btn-link" type="button">Details</button>
                    <input name="reject_identity_request" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Quick Reject"/>
                    <input name="accept_identity_request" type="submit" class="btn btn-xs btn-link" style="color: green;" value="Quick Accept"/>
                    <input name="id" type="hidden" value='.$row[0].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}

function ncicGetNames()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "SELECT * FROM ncic_names";

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>There are currently no names in the NCIC Database</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_names" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Address</th>
                <th>Sex</th>
                <th>Race</th>
                <th>DL Status</th>
                <th>Hair Color</th>
                <th>Build</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            echo '
            <tr>
                <td>'.$row[1].'</td>
                <td>'.$row[2].'</td>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[5].'</td>
                <td>'.$row[6].'</td>
                <td>'.$row[7].'</td>
                <td>'.$row[8].'</td>
                <td>'.$row[9].'</td>
                <td>
                    <button name="edit_name" data-toggle="modal" data-target="#editNameModal" class="btn btn-xs btn-link" disabled>Edit</button>
                    <form action="../actions/ncicAdminActions.php" method="post">
                    <input name="delete_name" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Delete"/>
                    <input name="uid" type="hidden" value='.$row[0].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}

function ncicGetPlates()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "SELECT ncic_plates.*, ncic_names.first_name, ncic_names.last_name FROM ncic_plates INNER JOIN ncic_names ON ncic_names.id=ncic_plates.name_id";

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>There are currently no vehicles in the NCIC Database</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_plates" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>Owner</th>
                <th>Plate</th>
                <th>Reg. State</th>
                <th>Make</th>
                <th>Model</th>
                <th>Color</th>
                <th>Ins. Status</th>
                <th>Flags</th>
                <th>Notes</th>
                <th>Admin Notes</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            $owner = $row[11]." ".$row[12];

            echo '
            <tr>
                <td>'.$owner.'</td>
                <td>'.$row[2].'</td>
                <td>'.$row[8].'</td>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[5].'</td>
                <td>'.$row[6].'</td>
                <td>'.$row[7].'</td>
                <td>'.$row[9].'</td>
                <td>'.$row[10].'</td>
                <td>
                    <form action="../actions/ncicAdminActions.php" method="post">
                    <input name="approveUser" type="submit" class="btn btn-xs btn-link" value="Edit" disabled />
                    <input name="delete_plate" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Delete" enabled/>
                    <input name="vehid" type="hidden" value='.$row[0].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}

function ncic_warrants()
{
   $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "SELECT ncic_names.first_name, ncic_names.last_name, ncic_warrants.id, ncic_warrants.issued_date, ncic_warrants.expiration_date, ncic_warrants.warrant_name, ncic_warrants.issuing_agency, ncic_warrants.status FROM ncic_warrants INNER JOIN ncic_names ON ncic_warrants.name_id=ncic_names.id";

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>There are currently no warrants in the NCIC Database</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_warrants" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>Status</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Warrant Name</th>
                <th>Issued On</th>
                <th>Expires On</th>
                <th>Issuing Agency</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            echo '
            <tr>
                <td>'.$row[7].'</td>
                <td>'.$row[0].'</td>
                <td>'.$row[1].'</td>
                <td>'.$row[5].'</td>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[6].'</td>
                <td>
                    <form action="../actions/ncicAdminActions.php" method="post">
                    <input name="approveUser" type="submit" class="btn btn-xs btn-link" value="Edit" disabled />
                    ';
                        if ($row[7] == "Active")
                        {
                            echo '<input name="serveWarrant" type="submit" class="btn btn-xs btn-link" value="Serve" disabled/>';
                        }
                        else
                        {
                            //Do Nothing
                        }
                    echo '
                    <input name="delete_warrant" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Expunge" />
                    <input name="wid" type="hidden" value='.$row[2].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}

function ncic_citations()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $query = "SELECT ncic_names.first_name, ncic_names.last_name, ncic_citations.id, ncic_citations.citation_name, ncic_citations.issued_date, ncic_citations.issued_by FROM ncic_citations INNER JOIN ncic_names ON ncic_citations.name_id=ncic_names.id WHERE ncic_citations.status = '1'";

    $result=mysqli_query($link, $query);

    $num_rows = $result->num_rows;

    if($num_rows == 0)
    {
        echo "<div class=\"alert alert-info\"><span>There are currently no citations in the NCIC Database</span></div>";
    }
    else
    {
        echo '
            <table id="ncic_citations" class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Citation Name</th>
                <th>Issued On</th>
                <th>Issued By</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        ';

        while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
        {
            echo '
            <tr>
                <td>'.$row[0].'</td>
                <td>'.$row[1].'</td>
                <td>'.$row[3].'</td>
                <td>'.$row[4].'</td>
                <td>'.$row[5].'</td>
                <td>
                    <form action="../actions/ncicAdminActions.php" method="post">
                    <input name="edit_citation" type="submit" class="btn btn-xs btn-link" value="Edit" disabled />
                    <input name="delete_citation" type="submit" class="btn btn-xs btn-link" style="color: red;" value="Expunge"/>
                    <input name="cid" type="hidden" value='.$row[2].' />
                    </form>
                </td>
            </tr>
            ';
        }

        echo '
            </tbody>
            </table>
        ';
    }
}
function getCivilianNamesOption()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $sql = "SELECT id, first_name, last_name FROM ncic_names";

    $result=mysqli_query($link, $sql);

    while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
    {
        echo "<option value=".$row[0].">".$row[1]." ".$row[2]."</option>";
    }
}

function getCivilianNames()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

	$sql = "SELECT ncic_names.id, ncic_names.first_name, ncic_names.last_name FROM ncic_names";

	$result=mysqli_query($link, $sql);

	while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
	{
		echo "<option value=\"$row[0]\">$row[1] $row[2]</option>";
	}
	mysqli_close($link);
}

function getAgencies()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

	$sql = 'SELECT * FROM departments
            WHERE department_name <>"Administrators"
            AND department_name <>"EMS"
            AND department_name <>"Fire"
            AND department_name <>"Civilian"
            AND department_name <>"Communications (Dispatch)"
            AND department_name <>"Head Administrators"';

	$result=mysqli_query($link, $sql);

	while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
	{
		echo "<option value=\"$row[1]\">$row[1]</option>";
	}
	mysqli_close($link);
}

function create_citation()
{
    $userId = $_POST['civilian_names'];
    $citation_name = $_POST['citation_name'];
    session_start();
    $issued_by = $_SESSION['name'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $sql = "INSERT INTO ncic_citations (name_id, citation_name, issued_by, status) VALUES (?, ?, ?, '1')";


	try {
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iss", $userId, $citation_name, $issued_by);
		$result = mysqli_stmt_execute($stmt);

		if ($result == FALSE) {
			die(mysqli_error($link));
		}
	}
	catch (Exception $e)
	{
		die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made
	}
	mysqli_close($link);

    session_start();
    $_SESSION['citationMessage'] = '<div class="alert alert-success"><span>Successfully created citation</span></div>';

    header("Location:../oc-admin/ncicAdmin.php#citation_panel");
}

function create_warrant()
{
    $userId = $_POST['civilian_names'];
    $warrant_name = $_POST['warrant_name_sel'];
    $issuing_agency = $_POST['issuing_agency'];

    $expiry = substr($_POST['warrant_name_sel'], -1);

    $warrant_name = substr($_POST['warrant_name_sel'], 0, -1);

    switch ($expiry)
    {
        case "1":
            $interval = 60;
            break;
        case "2":
            $interval = 30;
            break;
    }

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $sql = "INSERT INTO ncic_warrants (name_id, expiration_date, warrant_name, issuing_agency) SELECT ?, DATE_ADD(NOW(), INTERVAL ? day), ?, ?";


	try {
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "iiss", $userId, $interval, $warrant_name, $issuing_agency);
		$result = mysqli_stmt_execute($stmt);

		if ($result == FALSE) {
			die(mysqli_error($link));
		}
	}
	catch (Exception $e)
	{
		die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made
	}
	mysqli_close($link);

    session_start();
    $_SESSION['warrantMessage'] = '<div class="alert alert-success"><span>Successfully created warrant</span></div>';

    header("Location:../oc-admin/ncicAdmin.php#warrant_panel");
}

function delete_name()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $uid = $_POST['uid'];
    echo $uid;

    $query = "DELETE FROM ncic_names WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $uid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['nameMessage'] = '<div class="alert alert-success"><span>Successfully removed civilian name</span></div>';
    header("Location: ../oc-admin/ncicAdmin.php#name_panel");
}

function delete_plate()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $vehid = $_POST['vehid'];
    echo $vehid;

    $query = "DELETE FROM ncic_plates WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $vehid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['plateMessage'] = '<div class="alert alert-success"><span>Successfully removed civilian plate</span></div>';
    header("Location: ../oc-admin/ncicAdmin.php#plate_panel");
}

function delete_citation()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $cid = $_POST['cid'];

    $query = "DELETE FROM ncic_citations WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $cid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['citationMessage'] = '<div class="alert alert-success"><span>Successfully removed citation</span></div>';
    header("Location: ../oc-admin/ncicAdmin.php#citation_panel");
}

function delete_warrant()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $wid = $_POST['wid'];
    echo $wid;

    $query = "DELETE FROM ncic_warrants WHERE id = ?";

    try {
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $wid);
        $result = mysqli_stmt_execute($stmt);

        if ($result == FALSE) {
            die(mysqli_error($link));
        }
    }
    catch (Exception $e)
    {
        die("Failed to run query: " . $e->getMessage());
    }

    session_start();
    $_SESSION['warrantMessage'] = '<div class="alert alert-success"><span>Successfully removed warrant</span></div>';
    header("Location: ../oc-admin/ncicAdmin.php#warrant_panel");
}

function create_name()
{
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $sex = $_POST['sex'];
    $race = $_POST['race'];
    $dl_status = $_POST['dl_status'];
    $hair_color = $_POST['hair_color'];
    $build = $_POST['build'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $sql = "INSERT INTO ncic_names (first_name, last_name, dob, address, sex, race, dl_status, hair_color, build) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";


	try {
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "sssssssss", $first_name, $last_name, $dob, $address, $sex, $race, $dl_status, $hair_color, $build);
		$result = mysqli_stmt_execute($stmt);

		if ($result == FALSE) {
			die(mysqli_error($link));
		}
	}
	catch (Exception $e)
	{
		die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made
	}
	mysqli_close($link);

    session_start();
    $_SESSION['nameMessage'] = '<div class="alert alert-success"><span>Successfully added name to the database</span></div>';

    header("Location:../oc-admin/ncicAdmin.php#name_panel");
}

function create_plate()
{
    $userId = $_POST['civilian_names'];
    $veh_plate = $_POST['veh_plate'];
    $veh_make = $_POST['veh_make'];
    $veh_model = $_POST['veh_model'];
    $veh_color = $_POST['veh_color'];
    $veh_insurance = $_POST['veh_insurance'];
    $flags = $_POST['flags'];
    $veh_reg_state = $_POST['veh_reg_state'];
    $notes = $_POST['notes'];
    $hidden_notes = $_POST['hidden_notes'];

    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (!$link) {
		die('Could not connect: ' .mysql_error());
	}

    $sql = "INSERT INTO ncic_plates (name_id, veh_plate, veh_make, veh_model, veh_color, veh_insurance, flags, veh_reg_state, notes, hidden_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


	try {
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "isssssssss", $userId, $veh_plate, $veh_make, $veh_model, $veh_color, $veh_insurance, $flags, $veh_reg_state, $notes, $hidden_notes);
		$result = mysqli_stmt_execute($stmt);

		if ($result == FALSE) {
			die(mysqli_error($link));
		}
	}
	catch (Exception $e)
	{
		die("Failed to run query: " . $e->getMessage()); //TODO: A function to send me an email when this occurs should be made
	}
	mysqli_close($link);

    session_start();
    $_SESSION['plateMessage'] = '<div class="alert alert-success"><span>Successfully added plate to the database</span></div>';

    header("Location:../oc-admin/ncicAdmin.php#plate_panel");
}

function getCitations()
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$link) {
        die('Could not connect: ' .mysql_error());
    }

    $sql = "SELECT citation_name FROM citations";

    $result=mysqli_query($link, $sql);

    while($row = mysqli_fetch_array($result, MYSQLI_BOTH))
    {
        echo "<option value=".$row[0].">".$row[0]."</option>";
    }
}
?>
