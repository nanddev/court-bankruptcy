<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
$Last_Name = (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : "");
$Date_of_Birth = (isset($_REQUEST['Date_of_Birth']) ? htmlentities($_REQUEST['Date_of_Birth']) : "");
$Date_of_Birth = date('m/d/y', strtotime($Date_of_Birth));  

if(!isset($_SESSION['sesid'])) {
	$_SESSION['sesid']=session_id();
	$ct_session_id=session_id();
}
else {
	$ct_session_id=$_SESSION['sesid'];
}

$turnOffTimepay = false;
$showMessage = true;
$month = "August";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Online Court Payment System Demo</title>
<link rel="stylesheet" href="/css/style.css" type="text/css"/>
<script type="text/javascript">
	function validate_form2() {
	  	if(document.form1.Last_Name.value=="") {
	    		alert("Please enter your Last Name");
	    		document.form1.Last_Name.focus();
	    		return false;
	  	}
      		if(document.form1.Date_of_Birth.value=="") {
	   		alert("Please enter your Date of Birth");
	   		document.form.Date_of_Birth.focus();
	   		return false;
  		}
   		return true;
	}
	function addpayment_validate(value) {
		document.resultform2.action="results-tp.php?Last_Name=<?php echo $Last_Name; ?>&Date_of_Birth=<?php echo $Date_of_Birth; ?>&msg=5";
		document.resultform2.Addpayment_Submit.value='Addpayment';
		document.resultform2.cart_id.value=value;	
		document.resultform2.submit();
		return true;
	}
</script>
</head>


<?php
if(isset($_REQUEST['Addpayment_Submit']) && $_REQUEST['Addpayment_Submit']=='Addpayment') {
 
	$product_id=$_REQUEST['cart_id'];
 
	$ct_date=date("Y-m-d");
	$select_citation_addcart = $db->query("select * from citation_test where id='$product_id'");
	$select_citation_addcart_num = $select_citation_addcart->rowCount();  
	$select_citation_addcart_row = $select_citation_addcart->fetch(PDO::FETCH_BOTH);
	if($select_citation_addcart_num > 0) {
		if(true) {
			$check_mysql_cart=$db->query("select * from citation_cart_temp where product_id='$product_id' and ct_session_id='$ct_session_id'");
			$check_mysql_cart_num=$check_mysql_cart->rowCount();

			if(!$check_mysql_cart_num>0) {
				$insert_payment_cart=$db->query("insert into citation_cart_temp(product_id,ct_session_id,ct_date) values ('$product_id','$ct_session_id','$ct_date')");
	         		header("location:results-tp.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&msg=3&ct_session_id=$ct_session_id");
		  	} else {
		  		$delete_payment_cart=$db->query("delete from citation_cart_temp where product_id='$product_id' and ct_session_id='$ct_session_id'");
		   		header("location:results-tp.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&msg=2&ct_session_id=$ct_session_id");
		  	}
	 	} else {
			header("location:results-tp.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth&msg=1");
	 	}
	}	  
}

if(!empty($Last_Name) || !empty($Date_of_Birth)) 
{
  
	$search_query = "select *, GROUP_CONCAT(charges separator ', ') as Charges from citation_test where id != '' AND Status = 'A' AND (Min_Payment != '0' OR Fine_Amount != '0') AND Contract_Num != '' AND row(CID, First_Name, Last_Name, date_of_birth) not in (select CID, First_Name, Last_Name, date_of_birth from warrant_test where Status='A' and Fine_Amount!='0') AND (First_Name like '$Last_Name' or Last_Name like '$Last_Name') AND date_of_birth like '$Date_of_Birth' group by cid, vjpid, contract_num";

	try {
		$search_query_result = $db->query($search_query);
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

	$search_query_result_count = $search_query_result->rowCount();
}

if(isset($_REQUEST['Search_form']) && $_REQUEST['Search_form']=='Search') 
{
  
	$delete_citation_cart_temp = $db->query("delete from citation_cart_temp where ct_session_id = '$ct_session_id'");

	$search_query = "select *, GROUP_CONCAT(charges separator ', ') as Charges from citation_test where id != '' AND Status = 'A' AND (Min_Payment != '0' OR Fine_Amount != '0') AND Contract_Num != '' AND row(CID, First_Name, Last_Name, date_of_birth) not in (select CID, First_Name, Last_Name, date_of_birth from warrant_test where Status='A' and Fine_Amount!='0') AND (First_Name like '$Last_Name' or Last_Name like '$Last_Name') AND date_of_birth like '$Date_of_Birth' group by cid, vjpid, contract_num";

	try {
		$search_query_result = $db->query($search_query);
	} catch (PDOException $e) {
		echo $e->getMessage();
	}
	$search_query_result_count = $search_query_result->rowCount();
}

try {
	$check_mysql_cart_status = $db->query("select * from citation_cart_temp where ct_session_id = '$ct_session_id'");
} catch (PDOException $e) {
	echo $e->getMessage();
}

$check_mysql_cart_status_num = $check_mysql_cart_status->rowCount();

?>

<body>
<div id="main"><div class="head">
<div align="right" style="width:70%; float:left" >
<u><a href="index.php">Home</a></u></div>
</div>
<div class="login">

<p align="center">
<?php
if ($turnOffTimepay || $showMessage) {
	echo <<< AAA
<h2 style="color: red; font-weight: bold; font-size: 14px;">If you did not make a payment in $month do not proceed until you contact 877-689-5144. If you do not have permission to pay late your payment will be voided.</h2>
AAA;
}
?>
</p>
<p align="center">Complete at least two data fields below to find your ticket
</p>

<div style="width:30%; float:left"  align="right"><img src="" width="165" height="123" /> </div>                
<?php
if (!$turnOffTimepay) {
?>
<div align="left" style="width:50%; float:left; margin-left:20px;" >
  <form name="form1" id="form1" method="post" action="results-tp.php">
  <table width="90%" border="0" cellspacing="6" cellpadding="6" 
style="border:1px solid #333">
  <tr>
    <td>Last Name</td>
    <td><label for="textfield"></label>
      <input type="text" name="Last_Name" id="textfield" value="<?php echo (isset($_REQUEST['Last_Name']) ? htmlentities($_REQUEST['Last_Name']) : ""); ?>" /></td>
  </tr>
  <tr>
    <td>Date of Birth</td>
    <td> <input type="text" name="Date_of_Birth" id="textfield" value="<?php echo (isset($_REQUEST['Date_of_Birth']) ? htmlentities($_REQUEST['Date_of_Birth']) : ""); ?>" /><br />
	"Please enter date example<br />
	(MONTH/DAY/YEAR) : 4/6/1966"</td>
  </tr>
  <tr>
    <td><strong style="color:#F00">Help</strong></td>
    <td><input type="submit" name="Search_form" id="Search_form" value="Search" onclick="return validate_form2();" /></td>
  </tr>
</table>
  </form>
</div>


</div>
<?php
}
?>
<div style="clear:both"></div>
<div class="search" align="center">
<?php
if($search_query_result_count>0)
  {
   if($check_mysql_cart_status_num>0)
	{
  ?>
  <a href="checkout-tp.php?Last_Name=<?php echo $Last_Name; ?>&Date_of_Birth=<?php echo $Date_of_Birth; ?>&back_page=results-tp.php"><img src="/css/images/but.png" width="129" height="49" border="0" /></a>
   
<?php 
}
if(isset($_REQUEST['msg']) && $_REQUEST['msg']=='1')
{
?>
 <br><div align="center" style="color:#FF0000;font-size:18px;">"This contract cannot be paid online. You must make a court appearance,<br>please refer any additional questions to the Clerk of Court or call +1-877-689-5144"</div>
<?php
}
?>
<form name="resultform2" id="resultform2" method="post" action="">
<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
  <tr bgcolor="#366092" style="color:#FFF;" align="center">
    <td width="17%">Timepay Contract Number</td>
    <td width="15%">Name</td>
    <td width="23%">Jurisdiction</td>
    <td width="14%">Minimum Payment</td>
    <td>Charges</td>
  <td>Select To Pay</td>
  </tr>

  <?php
  
  while($search_query_result_row=$search_query_result->fetch(PDO::FETCH_BOTH))
  {
  
      
      $product_cart_id=$search_query_result_row['id'];
try {
	  $product_cart_added_check=$db->query("select * from citation_cart_temp where product_id='$product_cart_id' and ct_session_id='$ct_session_id'");
} catch (PDOException $e) {
	echo $e->getMessage();
}
      $product_cart_added_num=$product_cart_added_check->rowCount();
	  if($product_cart_added_num>0)
	  {
		    $product_cart_added="checked";
	  }
	  else
	  {
	   		$product_cart_added="";

	  }
  ?>
  <tr valign="top">
    <td><?php echo stripslashes($search_query_result_row['Contract_Num']);?></td>
    <td><?php echo stripslashes($search_query_result_row['Last_Name']); echo ', '; echo stripslashes($search_query_result_row['First_Name']); ?></td>
    <td><?php echo stripslashes($search_query_result_row['Jurisdiction']);?></td>
    <td><?php echo '$' . number_format(stripslashes($search_query_result_row['Min_Payment']), 2); ?></td>
    <td width="20%"><?php echo stripslashes($search_query_result_row['Charges']);?></td>
  <td width="11%">
  <?php switch($search_query_result_row['CID']) { case '9': ?>
	For Benton County, Pea Ridge please call 479-451-1101.</td>
  <?php break; case '11': ?>
	For White County, Beebe please call 501-882-8110</td>
  <?php break; case '13': ?>
	For Ouachita County, Camden please call 870-836-0331</td>
  <?php break; default: ?>
 <input type="checkbox" name="checkbox" value="checkbox" onclick="return addpayment_validate('<?php echo $search_query_result_row['id']; ?>');" <?php echo $product_cart_added; ?> /></td>
 <?php } ?>

  <?php
  }
 
  ?>
</table>
<br />
<table width="80%"  border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td align="right"><a href="#" style="font-size:16px;color:#FF0000;">Add Another Contract</a></td>
  </tr>
</table>

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

echo "Results not found. Please modify your search or try searching under <a href=\"/paymyfine/results-ct.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth\">citation</a> or <a href=\"/warrant/results.php?Last_Name=$Last_Name&Date_of_Birth=$Date_of_Birth\">warrant</a>.";

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
