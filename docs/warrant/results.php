<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Auth.php");

$Auth = new Auth();

$Last_Name = (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : "");

if (isset($_REQUEST['Date_of_Birth']) && strtolower($_REQUEST['Date_of_Birth']) != "null") {
	$Date_of_Birth = date('m/d/y', strtotime(htmlentities($_REQUEST['Date_of_Birth'])));  
}
else {
	$Date_of_Birth = "";
}

if (!isset($_SESSION['sesid'])) {
	$_SESSION['sesid'] = session_id();
	$ct_session_id = session_id();
}
else {
	$ct_session_id = $_SESSION['sesid'];
}

if ($_SERVER['REQUEST_METHOD'] == "POST" || ($Last_Name != '' && $Date_of_Birth != '')) {

	try {
		$query = "select * from (select *, CASE WHEN contract_num = '' THEN 1 ELSE 0 END as IsCitation, CASE WHEN contract_num = '' THEN 0 ELSE 1 END as IsTimepay, 0 as IsWarrant from citation_test where (last_name = :last_name1 or first_name = :first_name1) and date_of_birth = :dob1 and status = 'A' and fine_amount != '0' union all select *, 0 as IsCitation, 0 as IsTimepay, 1 as IsWarrant from warrant_test where (last_name = :last_name2 or first_name = :first_name2) and date_of_birth = :dob2 and status = 'A' and fine_amount != '0') t order by IsWarrant DESC, IsTimepay DESC, IsCitation DESC;";

		$sth = $db->prepare($query);
		$sth->execute(
			array(
				':last_name1' => $Last_Name,
				':first_name1' => $Last_Name,
				':dob1' => $Date_of_Birth,
				':last_name2' => $Last_Name,
				':first_name2' => $Last_Name,
				':dob2' => $Date_of_Birth
			)
		);
		$searchResults = $sth->fetchAll(PDO::FETCH_ASSOC);
		$searchResultsCount = count($searchResults);

		// Is this a warrant?
		if ($searchResultsCount > 0 && isset($searchResults[0]['IsWarrant']) && $searchResults[0]['IsWarrant']) {

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
<script type="text/javascript" src="/js/search-validate.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("div.item").tooltip();
	});
	function validate_form2() {
		if(document.form1.Last_Name.value=="") {
			alert("Please enter your Last Name");
			document.form1.Last_Name.focus();
			return false;
		}
		if(document.form1.Date_of_Birth.value=="") {
			alert("Please enter your Date of Birth");
			document.form1.Date_of_Birth.focus();
			return false;
		} 

		return true;
	}
</script>
</head>
<body>
<div id="main"><div class="head">
<div align="right" style="width:70%; float:left" >
<u><a href="/index.php">Home</a></u></div>


</div>
<div class="login">

<div style="width:30%; float:left"  align="right"><img src="" width="165" height="123" /></div>                
<div align="left" style="width:50%; float:left; margin-left:20px;" >
  <form name="form1" id="form1" method="post" action="results.php">
  <table width="90%" border="0" cellspacing="6" cellpadding="6" 
style="border:1px solid #333">
  <tr>
    <td>Last Name</td>
    <td><label for="textfield"></label>
      <input type="text" name="Last_Name" id="textfield" value="<?php echo (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : "");?>" /></td>
  </tr>
  <tr>
    <td align="left" valign="top">Date of Birth</td>
    <td align="left" valign="top"> <input type="text" name="Date_of_Birth" id="textfield" value="<?php echo (isset($_REQUEST['Date_of_Birth']) ? htmlentities($_REQUEST['Date_of_Birth']) : ""); ?>" /><br />
		
"Please enter date example<br />
(MONTH/DAY/YEAR) : 4/8/1972"    </td>
  </tr>
  <tr>
    <td> <div id="item_1" class="item">
         <strong style="color:#F00">Help</strong>
        <div class="tooltip_description" style="display:none" title="" align="center">
          
       Customer Support <br /><b>867-5309 <br />email us: demoemail@nanddevelopment.com</b>
</div>
      </div>
		</td>
    <td><input type="submit" name="Search_form" id="Search_form" value="Search" onclick="return validate_form2();" /></td>
  </tr>
</table>
  </form>
</div>


</div>
<div style="clear:both"></div>
<div class="search" align="center">
<form method="post" action="checkout.php">

<?php
if ($searchResultsCount > 0) {
	echo <<< BUT
<input type="submit" id="checkout" value="" />
BUT;

	echo <<< MSG
<h3>You must pay off all citations or timepay contracts asociated with this warrant. If you see any citations that you know you don't have to pay, then please call us at 1-877-689-5144.</h3>
MSG;
?>

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
  <?php
  
foreach ($searchResults as $index => $row) {
      
	$courtDate = ($row['Court_Date'] != '' ? $row['Court_Date'] : 'N/A');

	echo <<< AAA
<tr>
	<td>${row['Last_Name']}, ${row['First_Name']}</td>
	<td>${row['Citation_Number']}</td>
	<td>${row['Case_Number']}</td>
	<td>${row['Violation_Date']}</td>
	<td>$courtDate</td>
	<td>${row['Charges']}</td>
	<td>\$${row['Fine_Amount']}</td>
	<td>
AAA;

	switch($row['CID']) { 
		case '9': 
			echo "For Benton County, Pea Ridge please call 479-451-1101";
			break;
		 case '11': 
			echo "For White County, Beebe please call 501-882-8110";
			break;
		 case '13':
			echo "For Ouachita County, Camden please call 870-836-0331";
			break;
		 case '21': 
			if (!$Auth->isAdmin()) {
				echo "For Benton County, Cave Springs please call 1-877-689-5144";
				break;
			}
		default: 
			$isEnabled = ($Auth->isAdmin() ? "" : "disabled");
			echo <<< BBB
<input type="checkbox" name="isPaying[$index]" value="true" $isEnabled checked />
BBB;
	}

	echo <<< CCC
	</td>
</tr>
CCC;
  
}
?>
	</table>
	<br />
<?php
}
else {
?>
	<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
		<tr>
			<td>No warrants were found.</td>
		</tr>
	</table>
<?php
}
?>
</form>
</div>
<div align="center" style=" padding:80px;">All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a></div>
</div>
</body>
</html>
