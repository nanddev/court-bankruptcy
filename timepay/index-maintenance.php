<?php
session_start();
if (isset($_SESSION['sesid'])) {
	unset($_SESSION['sesid']);
	session_destroy();
}
include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");

$turnOffTimepay = false;
$showMessage = true;
$month = "August";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Online Court Payment System Demo</title>
<link rel="stylesheet" href="/css/style.css" type="text/css" />
<link rel="stylesheet" href="/css/jquery.tooltip.css" type="text/css" />
<style type="text/css">
	div.item { text-align:center; padding-top:0px; }
	div#item_1 { position: relative; left: -45px; }
</style>
<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="/js/lib/jquery.tooltip.js"></script>
<script type="text/javascript" src="/js/lib/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
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
<div style="width:20%; float:left" ><!--Pay Your Citation--> </div>                
<div align="right" style="width:70%; float:left" ><a href="/admin-login.php">Admin Login</a></div>
</div>

<div class="login">
<!--
<p align="center">Complete the fields below to find your timepay contract.</p>
-->
<div style="width:30%; float:left"  align="right"><a href="/index.php"><img src="" width="165" height="123" border="0" /></a> </div> <!--change this line when implementing-->               
<div align="left" style="width:50%; float:left; margin-left:20px;" >
<p align="center"><h2>Our website is down for maintenance. Please call our toll free number at 1-877-689-5144 for support.</h2></p>
<!--
  <form name="form1" id="form1" method="post" action="results-tp.php"> 
    <table width="90%" border="0" cellspacing="6" cellpadding="6" style="border:1px solid #333">
      <tr>
        <td width="29%">Last Name</td>
        <td width="71%"><label for="textfield"></label>
            <input type="text" name="Last_Name" id="Last_Name" /></td>
      </tr>
      <tr>
        <td align="left" valign="top">Date of Birth</td>
        <td align="left" valign="top">
		<input type="text" name="Date_of_Birth" id="Date_of_Birth"   /><br />
		"Please enter date example<br />
		(MONTH/DAY/YEAR) : 4/6/1966"</td>
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
-->
</div>
</div>
<div style="clear:both"></div>
<div align="center" style=" padding:160px;">All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a></div>
</div>

</body>
</html>
