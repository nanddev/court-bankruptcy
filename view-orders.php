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
<script type="text/javascript">
function confirm_admin_delete()
{
  var delete_admin = confirm("Do you want to delete admin user?");
  if(delete_admin==true)
  {
  return true;
  }
  else
  {
  return false;
  }
}
</script>
<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a><span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Administrative Login</strong></span>
<div align="right" style="padding-right:90px">Welcome Home: <strong><em><u><a href="admin-user.php"><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?></a></u></em></strong></div>
 </div>                
<div align="center" class="view_client" ><a href="admin-welcome.php"><strong>View Client Records</strong></a> | <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a><table width="20%" border="0" align="right" cellpadding="0" cellspacing="4">
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
<?php
include("inner_page_menu.php");
?>
<div class="admin-content">

<div class="search_blue">
  
  <em><strong>View Orders </strong></em>
   
</div>
</div>
<?php
$select_admin=mysql_query("select * from orders  order by oid desc" )or die(mysql_error());
$select_admin_count=mysql_num_rows($select_admin);
?>
<div class="admin-content1">
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>
<table width="80%" border="0" cellspacing="4" cellpadding="4" style="margin-left:30px;" >
  <tr  style="color:#FFF;" align="center">
  <td  width="5%" bgcolor="#366092">Order ID         </td>
    <td width="5%" bgcolor="#366092">Names         </td>
    <td width="5%" bgcolor="#366092">Username/Email</td>
    <td width="5%" bgcolor="#366092">Phone       </td>
    <td width="15%"  bgcolor="#366092"> Address Info                         </td>
	<td width="15%" bgcolor="#366092">Paymnet made on </td>
	<td width="20%" bgcolor="#366092">Citation Info        </td>
	<td width="20%" bgcolor="#366092">Charge total       </td>
	<td width="20%" bgcolor="#366092">First gateway payment info        </td>
	
  </tr>
  <?php
  if($select_admin_count>0){
  while($r=mysql_fetch_array($select_admin))
  {?>
  <tr bgcolor="#dce6f2">
  <td><?php 	echo stripslashes($r['oid']);?>               </td>
    <td><?php 	echo stripslashes($r['name']);?>               </td>
    <td><?php 	echo stripslashes($r['email']);?></td>
    <td><?php	echo stripslashes($r['phone']);?></td>
	
	<td><?php	echo stripslashes($r['address']);?><br/><?php	echo stripslashes($r['city']);?><br/><?php	echo stripslashes($r['state']);?><br/><?php	echo stripslashes($r['zip']);?></td>
	<td><?php	echo stripslashes($r['paidon']);?></td>
	<td>Citation Number  : <?php	echo stripslashes($r['citation_number']);?><br/>Case # : <?php	echo stripslashes($r['case_number']);?><br/>Violate date : <?php	echo stripslashes($r['violation_date']);?><br/>Court date : <?php	echo stripslashes($r['court_date']);?><br/>Processing : <?php	echo stripslashes($r['processing_fee']);?><br/>Fine : <?php	echo stripslashes($r['fine_amount']);?></td>
	<td><?php	echo stripslashes($r['chargetotal']);?></td>
	<td><?php 
	  $ws = unserialize($r['ws_response']);
	  //print_r($ws);
	  if ($ws != '' && count($ws))
		  foreach ($ws as $k => $v) {
			echo $k." : ".$v."<br/>";
		  }
	?>
	</td>
   
  </tr>
  <?php
  }
  }
  ?>
  
  <tr>
    <td colspan="2">
</td>
    <td colspan="2">&nbsp;
</td>
    <td>&nbsp;</td>
  </tr>
</table>
</div>
<div style="clear:both"></div>


<p>&nbsp;</p>
<div align="center" style="padding-top:200px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
