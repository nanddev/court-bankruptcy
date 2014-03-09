<?php
ob_start();
session_start();
include("includes/dbinfo.php");
include("session_check.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Online Court Payment System Demo</title>
<style type="text/css">
@import url("style.css");
.style1 {color: #FFFFFF}
</style>
<script type="text/javascript">
function validate_form1()
{
if(document.form1.name.value=="")
{
alert("Please enter your name");
document.form1.name.focus();
return true;
}
else
{
document.form1.submit();
return true;
}
}
</script>
</head>

<body>
<div id="main">
<div class="head">
<div style="width:40%; float:left" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a><span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Administration</strong></span>
<div align="right" style="padding-right:90px"><span style="color:#000000;">Welcome Home:</span> <strong><em><u><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?></u></em></strong></div>
 </div>                
<div align="center" class="view_client">
<!--
<a href="admin-welcome.php"><strong>View Client Records</strong></a>
-->
 <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a>
<table width="20%" border="0" align="right" cellpadding="0" cellspacing="4">
  <tr>
    <td><a href="admin-logout.php">Logout</a></td>
  </tr>
  <tr>
    <td><a href="admin-main.php">Home</a></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
</div>
</div>

<div style="clear:both"></div>
<div align="right" style="padding-bottom:20px;padding-right:120px;">Export to <strong style="color:red">XLS</strong> File <br />
</div>
<div style="clear:both"></div>
<h2 style="padding-left:30px;"><strong>View Partners:</strong></h2>
<?php
include("inner_page_menu.php");
?>
<br />

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >

  <tr bgcolor="#366092">
    <td><span class="style1">Partner Company</span></td>
    <td bgcolor="#366092"><span class="style1">Contact</span></td>
    <td><span class="style1">Phone</span></td>
    <td bgcolor="#366092"><span class="style1">Email</span></td>
    <td><span class="style1"> Expand Record | Delete</span></td>
  </tr>
  <tr bgcolor="#d9d9d9">
    <td>MSI</td>
    <td>Jerry Caldwell</td>
    <td>563-230-0091 </td>
    <td><a href="mailto:jerry@msi.com" style="color:#0000FF;">jerry@msi.com </a></td>
    <td><a href="#">Expand Record</a> | <a href="#">Delete</a> </td>
  </tr>
  <tr bgcolor="#dce6f2">
    <td>MSI</td>
    <td>Jerry Caldwell</td>
    <td>563-230-0091 </td>
    <td><a href="mailto:jerry@msi.com" style="color:#0000FF;">jerry@msi.com </a></td>
    <td><a href="#">Expand Record</a> | <a href="#">Delete</a> </td>
  </tr>
  <tr bgcolor="#d9d9d9">
    <td>MSI</td>
    <td>Jerry Caldwell</td>
    <td>563-230-0091 </td>
    <td><a href="mailto:jerry@msi.com" style="color:#0000FF;">jerry@msi.com </a></td>
    <td><a href="#">Expand Record</a> | <a href="#">Delete</a> </td>
  </tr>
  <tr bgcolor="#dce6f2">
    <td>MSI</td>
    <td>Jerry Caldwell</td>
    <td>563-230-0091 </td>
    <td><a href="mailto:jerry@msi.com" style="color:#0000FF;">jerry@msi.com </a></td>
    <td><a href="#">Expand Record</a> | <a href="#">Delete</a> </td>
  </tr>
  <tr>
    <td colspan="2">
</td>
    <td colspan="2">&nbsp;
</td>
    <td>&nbsp;</td>
  </tr>
</table>

</div><p>&nbsp;</p>
<div align="center" style="padding-top:200px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
