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
</style>
</head>

<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" align="" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a><span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Administration
</strong></span>

 <div align="right" style="padding-right:90px;">Welcome Home: <strong><em><u><?php if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?></u></em></strong></div>
</div>                
<div align="center" class="view_client" ><a href="admin-record.php"><strong>Create New Record</strong></a> | <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a><table width="20%" border="0" align="right" cellpadding="0" cellspacing="4">
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
<p>&nbsp;</p>
<!--
<div class="admin-content">
<div class="search_blue">
  
  <em><strong>Power Search </strong></em>
    <input name="" type="text" />&nbsp;&nbsp;<input name="" type="button" value="Search" />
</div>
</div>
-->

<div class="admin-content1">
<div style="float:left; width:30%; padding:15px"><strong>CID Search</strong> &nbsp;&nbsp;<input name="" type="text" /></div>
<div style="float:left; width:50%;padding:15px "><table width="80%" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td><strong>Client</strong></td>
    <td><input name="" type="text" /></td>
  </tr>
  <tr>
    <td><strong>City</strong></td>
    <td><input name="" type="text" /></td>
  </tr>
 
  
   
   
   <tr>
    <td colspan="2" align="center"><a href="confirmation.php"><img src="/css/images/but_submit.png" width="118" height="33" border="0" /></a></td>
    </tr>
</table></div>
</div>
<div style="clear:both"></div>
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr  style="color:#FFF;" align="center">
    <td width="10%" bgcolor="#366092">CID         </td>
    <td width="32%" bgcolor="#366092"> Organization   </td>
    <td width="17%" bgcolor="#366092">Records       </td>
    <td width="20%">                            </td>
    <td width="21%" style="color:#000">Export to <strong style="color:red"> XLS</strong> File </td>
  </tr>
  <tr bgcolor="#dce6f2">
    <td>15001               </td>
    <td>Louisville City Municipal Court    </td>
    <td>2181</td>
    <td align="center"><strong style="text-decoration:underline;">View Details</strong>&nbsp;&nbsp;<strong style="text-decoration:underline;">Delete Record</strong>
</td>
    <td></td>
  </tr>
  <tr bgcolor="#d9d9d9">
    <td>15001</td>
    <td>Louisville City Municipal Court </td>
    <td>356</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr bgcolor="#dce6f2">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr bgcolor="#d9d9d9">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr bgcolor="#dce6f2">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
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
