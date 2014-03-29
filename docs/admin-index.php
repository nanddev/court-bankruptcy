<?php
ob_start();
include("includes/dbinfo.php");

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
<div style="width:20%; float:left" >Pay Your Citation </div>                
<div align="right" style="width:70%; float:left" ><strong><a href="admin-login.php">Admin Login</a></strong></div>


</div>
<div class="login"><p align="center">Complete at least two data fields below to find your ticket
</p>

<div style="width:30%; float:left"  align="right"><a href="index.php"><img src="" width="165" height="123" border="0" /></a> </div>                
<div align="left" style="width:50%; float:left; margin-left:20px;" ><table width="70%" border="0" cellspacing="6" cellpadding="6" style="border:1px solid #333">
  <tr>
    <td>Last Name</td>
    <td><label for="textfield"></label>
      <input type="text" name="textfield" id="textfield" /></td>
  </tr>
  <tr>
    <td>Date of Birth</td>
    <td> <input type="text" name="textfield" id="textfield" /></td>
  </tr>
  <tr>
    <td>Citation Number
</td>
    <td> <input type="text" name="textfield" id="textfield" /></td>
  </tr>
  <tr>
    <td><strong style="color:#F00">Help</strong></td>
    <td><input type="submit" name="button" id="button" value="Pay Now" onclick="javascript:window.location='results.php';" /></td>
  </tr>
</table>
</div>


</div>
<div style="clear:both"></div>
<div align="center" style=" padding:160px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
