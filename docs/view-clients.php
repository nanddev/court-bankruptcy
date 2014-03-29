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
<script type="text/javascript">
function confirm_admin_delete()
{
  var delete_admin = confirm("Do you want to delete client list?");
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
</head>

<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" align="" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a>
<span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Client View
</strong></span>

 <div align="right" style="padding-right:90px;">Welcome Home: <strong><em><u><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?></u></em></strong><em></em></div>
</div>                
<div align="center" class="view_client" ><!--<a href="#"><strong>View Citations</strong></a> | <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a>-->
	
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
<?php
include("inner_page_menu.php");
?>
<!--
<div class="admin-content">

<div class="search_blue">
  
  <em><strong>Power Search </strong></em>
    <input name="" type="text" />&nbsp;&nbsp;<input name="" type="button" value="Search" />
</div>

</div>
-->
<div class="admin-content1">
<script type="text/javascript">
function validate_searchform1()
{

/*
if(document.form1.CID.value=="")
{
alert("Please enter your CID");
document.form1.CID.focus();
return true;
}
if(document.form1.Name.value=="")
{
alert("Please enter your search name");
document.form1.Name.focus();
return true;
}
if(document.form1.Citation_Number.value=="")
{
alert("Please enter search Citation Number");
document.form1.Citation_Number.focus();
return true;
}

else
{
document.form1.submit();
return true;
}
*/
document.form1.submit();
return true;
}
</script>
  <form name="form1" id="form1" method="post" action="admin-main.php?Submit_Search=Submit_Search">
  <div style="float:left; width:30%; padding:15px"><strong>CID Search</strong> &nbsp;&nbsp;<input name="CID" type="text" /></div>
<div style="float:left; width:50%;padding:15px "><table width="80%" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td><strong>Client</strong></td>
    <td><input name="Name" type="text" /></td>
  </tr>
  <tr>
    <td><strong>City</strong></td>
    <td><input name="City" type="text" /></td>
  </tr>
 
  
   
   
   <tr>
    <td colspan="2" align="left"><img src="/css/images/but_submit.png" width="118" height="33" border="0" onclick="return validate_searchform1();"  />
	<input type="hidden" name="Submit_Search" value="Submit_Search"  /></td>
    </tr>
</table></div>

  </form>
</div>
<div style="clear:both"></div>
<p>&nbsp;</p>
<!--
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>
-->
<?php
$select_admin=mysql_query("select * from client  order by id desc" )or die(mysql_error());
 $select_admin_count=mysql_num_rows($select_admin);
?>
<div class="admin-content1">
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>
<div align="center" style="color:#FF0000">
    <?
	if($_REQUEST['msg']=='1')
	{
	echo "Client List added success!";
	}
	if($_REQUEST['msg']=='2')
	{
	echo "Client List Deleted Success!";
	}
	?>      </div>        
<table width="80%" border="0" cellspacing="4" cellpadding="4" style="margin-left:30px;" >
  <tr  style="color:#FFF;" align="center">
    <td width="16%" bgcolor="#366092">Account CID </td>
    <td width="24%" bgcolor="#366092">Court/Municipality Name</td>
    <td width="22%" bgcolor="#366092">Email</td>
    <td width="19%" bgcolor="#366092">Phone</td>
    <td width="19%" bgcolor="#366092">Select</td>
  </tr>
  <?php
  if($select_admin_count>0){
  while($select_admin_row=mysql_fetch_array($select_admin))
  {?>
  <tr bgcolor="#dce6f2">
    <td><?php 	echo stripslashes($select_admin_row['account_cid']);?>               </td>
    <td><a href="view-clients-details.php?view_id=<?php echo $select_admin_row['id']?>" style="color:#000000;"><?php 	echo stripslashes($select_admin_row['municipality']);?></a></td>
    <td><?php	echo stripslashes($select_admin_row['email']);?></td>
    <td align="center"><?php echo stripslashes($select_admin_row['phone']);?>
</td>

    <td align="center"><strong style="text-decoration:underline; color:#FF0000"><a href="view-clients-details.php?view_id=<?php echo $select_admin_row['id']?>">Modify</a> 
	      <?php
		  if($admin_user_type=='Super')
		  {?>
	| <a href="delete-view-clients.php?view_id=<?php echo $select_admin_row['id']?>" onclick="return confirm_admin_delete();">Delete</a></strong>
	<?php
	}
	?>
	</td>
  </tr>
  <?php
  }
  }
  ?>
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
</div>
<div style="clear:both"></div>
<p>&nbsp;</p>
<div align="center" style="padding-top:1060px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
