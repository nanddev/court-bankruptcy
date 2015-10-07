<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Auth.php");

$auth = new Auth();

$courtId = (isset($_REQUEST['courtId']) ? htmlentities($_REQUEST['courtId']) : "");
$knowsCitation = (isset($_REQUEST['knowsCitation']) ? htmlentities($_REQUEST['knowsCitation']) : "");
$lastName = (isset($_REQUEST['lastName']) ? htmlentities($_REQUEST['lastName']) : "");
$citation = (isset($_REQUEST['citation']) ? htmlentities($_REQUEST['citation']) : "");

if (isset($_REQUEST['dob']) && strtolower($_REQUEST['dob']) != "null") {
	$dob = date('m/d/y', strtotime(htmlentities($_REQUEST['dob'])));  
}
else {
	$dob = "";
}

if (!isset($_SESSION['sesid'])) {
	$_SESSION['sesid'] = session_id();
}

$courts = array(
	'21' => 'Benton County, Cave Springs',
	'9' => 'Benton County, Pea Ridge',
	'22' => 'Marion County District Court',
	'20' => 'Monroe County, Clarendon',
	'14' => 'Monroe County, Brinkley',
	'13' => 'Ouachita County, Camden',
	'10' => 'Pulaski County, Sherwood',
	'17' => 'Saline County, Bryant',
	'12' => 'Saline County, Haskell',
	'19' => 'Saline County, Shannon Hills',
	'11' => 'White County, Beebe',
	'18' => 'White County, McRae'
);

$_SESSION['IsWarrant'] = false;
$_SESSION['IsTimepay'] = false;
$_SESSION['IsCitation'] = false;
$searchResultsCount = 0;

// This is used to figure out if the person can pay for their record
function canPay($row, $auth) {
	$canPayMessage = '';

	switch($row['CID']) { 
		case '9': 
			$canPayMessage = "For Benton County, Pea Ridge please call 479-451-1101";
			break;
		case '11': 
			$canPayMessage = "For White County, Beebe please call 501-882-8110";
			break;
		case '13':
			$canPayMessage = "For Ouachita County, Camden please call 870-836-0331";
			break;
		case '21': 
			if (!$auth->isAdmin()) {
				$canPayMessage = "For Benton County, Cave Springs please call 1-877-689-5144";
				break;
			}
		default: 
			$isEnabled = (($auth->isAdmin() || $_SESSION['IsWarrant'] != true) ? "" : "disabled");

			$canPayMessage = <<<PAY
				<input class="paying" type="checkbox" name="isPaying[${row['id']}]" value="true" $isEnabled checked />
PAY;
	}

	return $canPayMessage;
}

// This will display a message to the user based on the type of record found.
function getMessage() {

	if ($_SESSION['IsWarrant']) {
		$message = "You must pay off all citations or timepay contracts asociated with this warrant. If you see any citations that you know you don't have to pay, then please call us at 867-5309.";
	}
	else if ($_SESSION['IsTimepay']) {
		$message = "Please select your timepay contract.";
	}
	else if ($_SESSION['IsCitation']) {
		$message = "Please select the citations you wish to pay.";
	}
	else {
		$message = "";
	}

	return "<h3>$message</h3>";
}

// This will generate the table of records to pay based on type of record found.
function getTable($searchResults, $auth, $courts) {
	$table = "";

	// Make sure we have some type of record
	if (count($searchResults) > 0) {

		// Display the records differently based on their type
		if ($_SESSION['IsWarrant']) {
			$table = <<<HED
				<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Citation Number</td>
						<td>Case Number</td>
						<td>Violation Date</td>
						<td>Court Date</td>
						<td>Charges</td>
						<td>Fine Amount</td>
						<td>Select To Pay</td>
					</tr>
HED;

			foreach ($searchResults as $index => $row) {

				$courtDate = ($row['Court_Date'] != '' ? $row['Court_Date'] : 'N/A');

				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Citation_Number']}</td>
						<td>${row['Case_Number']}</td>
						<td>${row['Violation_Date']}</td>
						<td>$courtDate</td>
						<td>${row['Charges']}</td>
						<td>\$${row['Fine_Amount']}</td>
						<td>
ROW;
				$table .= canPay($row, $auth);

				$table .= <<<CCC
						</td>
					</tr>
CCC;
			}
		}
		// Display Timepay format if this only has timepay records
		else if ($_SESSION['IsTimepay']) {
			$table = <<<HED
				<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Contract Number</td>
						<td>Jurisdiction</td>
						<td>Minimum Payment</td>
						<td>Charges</td>
						<td>Select To Pay</td>
					</tr>
HED;

			foreach ($searchResults as $index => $row) {

				$courtDate = ($row['Court_Date'] != '' ? $row['Court_Date'] : 'N/A');

				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Contract_Num']}</td>
						<td>${row['Jurisdiction']}</td>
						<td>\$${row['Min_Payment']}</td>
						<td>${row['ChargesList']}</td>
						<td>
ROW;
				$table .= canPay($row, $auth);

				$table .= <<<CCC
						</td>
					</tr>
CCC;
			}
		}
		// Display Citation format if this is only has citation records
		else if ($_SESSION['IsCitation']) {
			$table = <<<HED
				<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Citation Number</td>
						<td>Case Number</td>
						<td>Violation Date</td>
						<td>Court Date</td>
						<td>Charges</td>
						<td>Fine Amount</td>
						<td>Select To Pay</td>
					</tr>
HED;

			foreach ($searchResults as $index => $row) {

				$courtDate = ($row['Court_Date'] != '' ? $row['Court_Date'] : 'N/A');

				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Citation_Number']}</td>
						<td>${row['Case_Number']}</td>
						<td>${row['Violation_Date']}</td>
						<td>$courtDate</td>
						<td>${row['Charges']}</td>
						<td>\$${row['Fine_Amount']}</td>
						<td>
ROW;
				$table .= canPay($row, $auth);

				$table .= <<<CCC
						</td>
					</tr>
CCC;
			}
		}
		$table .= "</table>";
	}
	else {
		$courtId = (isset($_POST['courtId']) ? $_POST['courtId'] : "");
		$courtName = (isset($courts[$courtId]) ? $courts[$courtId] : "that court");
		$table = <<<NON
			<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
				<tr>
					<td>
						<p>No records were found for that person in $courtName.</p>
					</td>
				</tr>
			</table>
NON;
	}

	return $table;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" || ($lastName != '' && $dob != '')) {

	try {
		$query = 
			"SELECT *
			FROM
				((SELECT *,
					 GROUP_CONCAT(charges separator ', ') AS ChargesList,
					 1 AS IsCitation,
					 0 AS IsTimepay,
					 0 AS IsWarrant
				 FROM citation_test
				 WHERE CID = :cid1
					 AND (last_name = :last_name1
						 OR first_name = :first_name1)
					 AND date_of_birth = :dob1
					 AND status = 'A'
					 AND fine_amount != '0'
					 AND contract_num = ''
				 GROUP BY vjpid,
					 case_number)
				UNION ALL
				(SELECT *,
					 GROUP_CONCAT(charges separator ', ') AS ChargesList,
					 0 AS IsCitation,
					 1 AS IsTimepay,
					 0 AS IsWarrant
				 FROM citation_test
				 WHERE CID = :cid2
					 AND (last_name = :last_name2
						 OR first_name = :first_name2)
					 AND date_of_birth = :dob2
					 AND status = 'A'
					 AND (fine_amount != '0' OR min_payment != '0')
					 AND contract_num != ''
				 GROUP BY vjpid,
					 contract_num)
				 UNION ALL 
				 (SELECT *,
					 Charges,
					 0 AS IsCitation,
					 0 AS IsTimepay,
					 1 AS IsWarrant
				 FROM warrant_test
				 WHERE CID = :cid3
					 AND (last_name = :last_name3
						 OR first_name = :first_name3)
					 AND date_of_birth = :dob3
					 AND status = 'A'
					 AND fine_amount != '0')) as temp
			 ORDER BY IsWarrant DESC,
				 IsTimepay DESC,
				 IsCitation DESC;";

		$sth = $db->prepare($query);
		$sth->execute(
			array(
				':cid1' => $courtId,
				':last_name1' => $lastName,
				':first_name1' => $lastName,
				':dob1' => $dob,
				':cid2' => $courtId,
				':last_name2' => $lastName,
				':first_name2' => $lastName,
				':dob2' => $dob,
				':cid3' => $courtId,
				':last_name3' => $lastName,
				':first_name3' => $lastName,
				':dob3' => $dob
			)
		);
		$searchResults = $sth->fetchAll(PDO::FETCH_ASSOC);
		$searchResultsCount = count($searchResults);

		//echo '<pre>';
		//echo var_dump($query, $searchResultsCount, $searchResults);
		//echo '</pre>';
		//exit;

		// Did we find anything?
		if ($searchResultsCount > 0) {

			// What kind of record is this?
			foreach (array("IsWarrant", "IsTimepay", "IsCitation") as $status) {
				if (isset($searchResults[0][$status]) && $searchResults[0][$status]) {
					$_SESSION[$status] = true;
					$_SESSION['type'] = $status;
				}
			}

			// Insert records into session
			if (isset($_SESSION['Citations'])) {
				unset($_SESSION['Citations']);
			}
			$_SESSION['Citations'] = $searchResults;
		}
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}

	$searchResultsCount = count($searchResults);
}

?>
<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<style type="text/css">
			div.item { text-align: center; padding-top: 0px; }
			div#item_1 { position: relative; left: -45px; }
			#checkout { 
				background-image: url("/css/images/but.png");
				background-position:  0px 0px;
				background-repeat: no-repeat;
				width: 129px;
				height: 49px;
				border: 0px;
				cursor: pointer;
			}
		</style>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/css/jquery.tooltip.css" />
		<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.tooltip.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.validate.min.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.showhide-rules-1.2.js"></script>
		<script type="text/javascript" src="/js/search-validate.js"></script>
		<script type="text/javascript" src="/js/payment-submit.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("div.item").tooltip();
			});
		</script>
	</head>
	<body>
		<div id="main">
			<div class="head">
				<div align="right" style="width:70%; float:left">
					<a href="/index.php">Home</a> |
					<a href="/admin-login.php">Admin Login</a>
				</div>
			</div>
			<div class="login">
				<div style="width: 30%; float: left" align="right">
					<img src="" width="165" height="123" />
				</div>
				<div align="left" style="width: 50%; float: left; margin-left: 20px;">
				  <form name="form1" id="form1" method="post" action="results.php">
					  <table width="90%" border="0" cellspacing="6" cellpadding="6" style="border: 1px solid #333;">
							<tr>
								<td width="40%">
									<label for="courtId">Which court has your record?</label>
								</td>
								<td width="60%">
									<select id="courtId" name="courtId" class="sh-parent-item" required title="Please choose which court your record belongs to.">
										<option value="Select">Select</option>	
										<?php
										foreach ($courts as $id => $name) {
											if (isset($_REQUEST['courtId']) && htmlentities($_REQUEST['courtId']) == $id) {
												echo "<option value=\"$id\" selected>$name</option>";
											}
											else {
												echo "<option value=\"$id\">$name</option>";
											}
										}
										?>
									</select>
								</td>
							</tr>
							<tr id="knowsCitationRow" class="sh-child" data-showhide='{ "dependencies": "NOT_courtId_VAL_Select" }'>
								<td>
									<label for="knowsCitation">Do you know your citation number?</label>
								</td>
								<td>
									<select id="knowsCitation" name="knowsCitation" required title="Please specify if you know your citation number.">
										<option value="">Select</option>
										<?php
										foreach (array("Yes", "No") as $value) {
											if (isset($_REQUEST['knowsCitation']) && htmlentities($_REQUEST['knowsCitation']) == $value) {
												echo "<option selected>$value</option>";
											}
											else if(!isset($_REQUEST['knowscitation']) && $value == 'No') {
												echo "<option selected>$value</option>";
											}
											else {
												echo "<option>$value</option>";
											}
										}
										?>
									</select>
								</td>
							</tr>
							<tr id="lastNameRow" class="sh-child" data-showhide='{ "dependencies": "knowsCitation_VAL_No_AND_NOT_courtId_VAL_Select" }'>
								<td>
									<label for="lastName">What is your last name?</label>
								</td>
								<td>
									<input type="text" name="lastName" id="textfield" value="<?php echo (isset($_REQUEST['lastName']) ? htmlentities($_REQUEST['lastName']) : "");?>" />
								</td>
							</tr>
							<tr id="dobRow" class="sh-child" data-showhide='{ "dependencies": "knowsCitation_VAL_No_AND_NOT_courtId_VAL_Select" }'>
								<td align="left" valign="top">
									<label for="dob">What is your date of birth?</label>
								</td>
								<td align="left" valign="top">
									<input type="date" name="dob" id="dob" title="Please enter your date of birth." value="<?php echo (isset($_REQUEST['dob']) ? htmlentities($_REQUEST['dob']) : ""); ?>" placeholder="mm/dd/yyyy" />
								</td>
							</tr>
							<tr id="citationRow" class="sh-child" data-showhide='{ "dependencies": "knowsCitation_VAL_Yes_AND_NOT_courtId_VAL_Select" }'>
								<td valign="top">
									<label for="citation">What is your citation number?</label>
								</td>
								<td>
									<input type="text" name="citation" id="citation" value="<?php echo (isset($_REQUEST['citation']) ? htmlentities($_REQUEST['citation']) : "");?>" />
								</td>
							</tr>
							<tr>
								<td>
									<div id="item_1" class="item">
										<strong style="color:#F00">Help</strong>
										<div class="tooltip_description" style="display:none" title="" align="center">
											Customer Support <br />
											<b>867-5309<br />Email us: demo@nanddevelopment.com</b>
										</div>
									</div>
								</td>
								<td>
									<input type="submit" id="submit" value="Search" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<div style="clear:both"></div>
			<div class="search" align="center">
				<form method="post" action="checkout.php">
					<?php
						echo '<input type="submit" id="checkout" value="" />';

						// Display a message based on the type of records
						echo getMessage();

						echo getTable($searchResults, $auth, $courts);
					?>
				</form>
			</div>
			<div align="center" style=" padding:80px;">
				All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a>
			</div>
		</div>
	</body>
</html>
