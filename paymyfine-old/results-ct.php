<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");

$Last_Name = (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : "");
$Citation_Number = (isset($_REQUEST['Citation_Number']) ? htmlentities($_REQUEST['Citation_Number']) : "");
$Date_of_Birth = (isset($_REQUEST['Date_of_Birth']) ? htmlentities($_REQUEST['Date_of_Birth']) : "");

if(!empty($_REQUEST['Date_of_Birth'])) {
	$Date_of_Birth = date('m/d/y', strtotime(htmlentities($Date_of_Birth)));  
}
else {
	$Date_of_Birth="";  
}

if(!isset($_SESSION['sesid'])) {
	$_SESSION['sesid']=session_id();
	$ct_session_id=session_id();
}
else {
	$ct_session_id=$_SESSION['sesid'];
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
</style>
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<link rel="stylesheet" href="/css/jquery.tooltip.css" type="text/css" />
<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="/js/lib/jquery.tooltip.js"></script>
<script type="text/javascript" src="/js/lib/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("div.item").tooltip();
	});
	function validate_form2() {
		if ((document.form1.Last_Name.value == "") && (document.form1.Date_of_Birth.value == "" || document.form1.Citation_Number.value == "")) {
			if (document.form1.Last_Name.value == "") {
				alert("Please enter your Last Name");
				document.form1.Last_Name.focus();
				return false;
			}
			if (document.form1.Date_of_Birth.value == "") {
				alert("Please enter your Date of Birth or Citation Number");
				document.form1.Date_of_Birth.focus();
				document.form1.Citation_Number.focus();
				return false;
			}
		}
		else {
			return true;
		}
	}
	function addpayment_validate(value) {
		document.resultform2.action="results-ct.php?Last_Name=<?php echo $Last_Name; ?>&Date_of_Birth=<?php echo $Date_of_Birth; ?>&Citation_Number=<?php echo $Citation_Number; ?>&msg=5";
		document.resultform2.Addpayment_Submit.value='Addpayment';
		document.resultform2.cart_id.value=value;	
		document.resultform2.submit();
		return true;
	}
</script>
</head>
<?php
          
// When someone checks the box to pay this citation, do this 
if(isset($_REQUEST['Addpayment_Submit']) && $_REQUEST['Addpayment_Submit'] == 'Addpayment') {

	$product_id = $_REQUEST['cart_id'];
	$ct_date = date("Y-m-d");

	$params = array(':product_id' => $product_id);
	$sql = "select * from citation_test where id = :product_id";
	
	$sth = $db->prepare($sql);
	$result = $sth->execute($params);
	$select_citation_addcart = $sth->fetchAll(PDO::FETCH_ASSOC);

	$select_citation_addcart_num = count($select_citation_addcart);  

	if ($select_citation_addcart_num > 0) {
		$select_citation_addcart_row = $select_citation_addcart[0];

		if ($select_citation_addcart_row['Appearance']=='0' || (($select_citation_addcart_row['Appearance']=='1') && (strtotime($select_citation_addcart_row['Court_date']) < time()))) {
			$sth = $db->prepare("select * from citation_cart_temp where product_id = :product_id and ct_session_id = :ct_session_id");
			$result = $sth->execute(array(':product_id' => $product_id, ':ct_session_id' => $ct_session_id));
			$check_mysql_cart = $sth->fetchAll(PDO::FETCH_ASSOC);
			$check_mysql_cart_num = count($check_mysql_cart);

			if(!$check_mysql_cart_num > 0) {
				$sth = $db->prepare("insert into citation_cart_temp (product_id, ct_session_id, ct_date) values (:product_id, :ct_session_id, :ct_date)");
				$sth->execute(array(':product_id' => $product_id, ':ct_session_id' => $ct_session_id, ':ct_date' => $ct_date));
				header("location:results-ct.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&Citation_Number=$Citation_Number&msg=3&ct_session_id=$ct_session_id");
			}
			else {
				$sth = $db->prepare("delete from citation_cart_temp where product_id = :product_id and ct_session_id = :ct_session_id");
				$sth->execute(array(':product_id' => $product_id, ':ct_session_id' => $ct_session_id));
				header("location:results-ct.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&Citation_Number=$Citation_Number&msg=2&ct_session_id=$ct_session_id");
			}
		 }
		 else {
			header("location:results-ct.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&Citation_Number=$Citation_Number&msg=1");
		 }
	}
}

// NOTE: This code is necessary right now but I think this could be rewritten so it is unneeded...
if (!empty($Last_Name) && (!empty($Date_of_Birth) || !empty($Citation_Number))) {

	$params = array(':first_name' => $Last_Name, ':last_name' => $Last_Name);
	$sql = "select * from citation_test where id!='' AND Contract_Num = '' AND Fine_Amount != '0' AND Status = 'A' AND (First_Name = :first_name or Last_Name = :last_name) AND row(CID, First_Name, Last_Name, date_of_birth) not in (select CID, First_Name, Last_Name, date_of_birth from warrant_test where Status='A' and Fine_Amount!='0') AND";
	if (strtolower($_REQUEST['Date_of_Birth']) == "null" || $_REQUEST['Date_of_Birth'] != "") {
		$sql .= "date_of_birth = :date_of_birth";
		$params[':date_of_birth'] = $Date_of_Birth;
	}
	else if (strtolower($_REQUEST['Citation_Number'] != "")) {
		$sql .= "citation_number = :citation_number";
		$params[':citation_number'] = $Citation_Number;
	}
	
	$sth = $db->prepare($sql);

	try {
		$result = $sth->execute($params);
		$searchResults = $sth->fetchAll(PDO::FETCH_BOTH);
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	$search_query_result_count = count($searchResults);
}

// This is the initial search lookup when someone enter last name/dob!
if(isset($_REQUEST['Search_form']) && $_REQUEST['Search_form'] =='Search') {
  
	$sth = $db->prepare("delete from citation_cart_temp where ct_session_id = :ct_session_id");
	$sth->execute(array(':ct_session_id' => $ct_session_id));

	$params = array(':first_name' => $Last_Name, ':last_name' => $Last_Name);
	$sql = "select * from citation_test where id!='' AND Contract_Num = '' AND Fine_Amount != '0' AND Status = 'A' AND (First_Name = :first_name or Last_Name = :last_name) AND row(CID, First_Name, Last_Name, date_of_birth) not in (select CID, First_Name, Last_Name, date_of_birth from warrant_test where status='A' and Fine_Amount!='0') AND";
	if (strtolower($_REQUEST['Date_of_Birth']) == "null" || $_REQUEST['Date_of_Birth'] != "") {
		$sql .= "date_of_birth = :date_of_birth";
		$params[':date_of_birth'] = $Date_of_Birth;
	}
	else if (strtolower($_REQUEST['Citation_Number'] != "")) {
		$sql .= "citation_number = :citation_number";
		$params[':citation_number'] = $Citation_Number;
	}

	$sth = $db->prepare($sql);

	try {
		$result = $sth->execute($params);
		$searchResults = $sth->fetchAll(PDO::FETCH_BOTH);
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	$search_query_result_count = count($searchResults);
}

try {
	$sth = $db->prepare("select * from citation_cart_temp where ct_session_id = :ct_session_id");
	$result = $sth->execute(array(':ct_session_id' => $ct_session_id));
	$check_mysql_cart_status = $sth->fetchAll(PDO::FETCH_BOTH);
	$check_mysql_cart_status_num = count($check_mysql_cart_status);
}
catch (PDOException $e) {
	echo $e->getMessage();
}

?>
<body>
<div id="main"><div class="head">
<div align="right" style="width:70%; float:left" >
<u><a href="/index.php">Home</a></u></div>


</div>
<div class="login">

<div style="width:30%; float:left"  align="right"><img src="" width="165" height="123" /> </div>                
<div align="left" style="width:50%; float:left; margin-left:20px;" >
  <form name="form1" id="form1" method="post" action="results-ct.php">
  <table width="90%" border="0" cellspacing="6" cellpadding="6" 
style="border:1px solid #333">
  <tr>
    <td width="29%">Last Name</td>
    <td width="71%"><label for="textfield"></label>
      <input type="text" name="Last_Name" id="textfield" value="<?php echo (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : "");?>" /></td>
  </tr>
  <tr>
    <td align="left" valign="top">Date of Birth</td>
    <td align="left" valign="top"> <input type="text" name="Date_of_Birth" id="textfield" value="<?php echo (isset($_REQUEST['Date_of_Birth']) ? htmlentities($_REQUEST['Date_of_Birth']) : "");?>" /><br />
		
"Please enter date example<br />
(MONTH/DAY/YEAR) : 4/8/1972"    </td>
  </tr>
  <tr>
    <td>Citation Number</td>
    <td> <input type="text" name="Citation_Number" id="textfield" value="<?php echo (isset($_REQUEST['Citation_Number']) ? htmlentities($_REQUEST['Citation_Number']) : "");?>" /></td>
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
<?php
if ($search_query_result_count > 0) {
	if ($check_mysql_cart_status_num > 0) {
		echo <<< BUT
<a href="checkout-ct.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&back_page=results-ct.php">
	<img src="/css/images/but.png" width="129" height="49" border="0" />
</a>
BUT;
	}
	if (isset($_REQUEST['msg']) && $_REQUEST['msg']  == '1') {
		echo <<< MSG
<br><div align="center" style="color:#FF0000;font-size:18px;">"This citation cannot be paid online. You must make a court appearance,<br>please refer any additional questions to the Clerk of Court or call +1-877-689-5144"</div>
MSG;
	}
?>
<form name="resultform2" id="resultform2" method="post" action="">
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

foreach ($searchResults as $row) {
  
	$product_cart_id = $row['id'];

	try {
		$sth = $db->prepare("select * from citation_cart_temp where product_id = :product_cart_id and ct_session_id = :ct_session_id");
		$result = $sth->execute(array(':product_cart_id' => $product_cart_id, ':ct_session_id' => $ct_session_id));
		$product_cart_added_check = $sth->fetchAll();
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	$product_cart_added_num = count($product_cart_added_check);
	$product_cart_added = ($product_cart_added_num > 0 ? "checked" : "");
  ?>
  <tr>
    <td><?php echo "${row['Last_Name']}, ${row['First_Name']}"; ?></td>
    <td><?php echo $row['Citation_Number']; ?></td>
    <td><?php echo $row['Case_Number']; ?></td>
    <td><?php echo $row['Violation_Date']; ?></td>
    <td><?php echo ($row['Court_Date'] != '' ? $row['Court_Date'] : 'N/A'); ?></td>
    <td><?php echo $row['Charges']; ?></td>
    <td><?php echo '$' . number_format($row['Fine_Amount'], 2); ?></td>
    <td>
	<?php switch($row['CID']) { case '9': ?>
	For Benton County, Pea Ridge please call 479-451-1101</td>
	<?php break; case '11': ?>
	For White County, Beebe please call 501-882-8110</td>
	<?php break; case '13': ?>
	For Ouachita County, Camden please call 870-836-0331</td>
	<?php break; default: ?>
<input type="checkbox" name="checkbox" value="checkbox" onclick="return addpayment_validate('<?php echo $row['id']; ?>');" <?php echo $product_cart_added; ?> /></td>
  <?php } ?>
	</tr>
  
  <?php
  }
 
  ?>
</table>
<br />

 <input type="hidden" name="cart_id" id="cart_id" />
 <input type="hidden" name="Addpayment_Submit" id="Addpayment_Submit" />

</form>
<?php
}
else
{
?>
<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
  
  <tr>
    <td>
<?php

echo "Results not found. Please modify your search or try searching under <a href=\"/timepay/results-tp.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth\">timepay</a> or <a href=\"/warrant/results.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth\">warrant</a>.";

?>
    </td>
  </tr>
  
</table>
<?php
}
?>
</div>
<div align="center" style=" padding:80px;">All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a></div>
</div>

</body>
</html>
