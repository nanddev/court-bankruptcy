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
function admin_register_validate()
{
  
	if(document.admin_formreg.name.value=="")
	{
	alert("Please enter your full name");
	document.admin_formreg.name.focus();
	return false;
	}
	if(document.admin_formreg.password.value=="")
	{
	alert("Please enter your password");
	document.admin_formreg.password.focus();
	return false;
	}
	 if(document.admin_formreg.email.value=="")
	{
	alert("Please enter your email address");
	document.admin_formreg.email.focus();
	return false;
	}
	if(document.admin_formreg.email.value.search(/^(\w+(?:\.\w+)*)@((?:\w+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i))
	{
	alert("Please enter your valid email address");
	document.admin_formreg.email.focus();
	return false;
	}	
	 else
	 {
	   return true;
	  }
}
</script>
</head>
<?php
$view_id=$_REQUEST['view_id'];
 $view_admin_select=mysql_query("select * from administrator where id='$view_id'") or die(mysql_error());
 $view_admin_select_count=mysql_num_rows($view_admin_select);
 $view_admin_row=mysql_fetch_array($view_admin_select);

if(isset($_REQUEST['Save']))
{
 $name=addslashes($_REQUEST['name']);
 $password=addslashes($_REQUEST['password']);
 $email=addslashes($_REQUEST['email']);
 $user_type=addslashes($_REQUEST['user_type']);
 $todaydate=date("d-m-Y");
// $random=genRandomString();
  /*
 $admin_select=mysql_query("select * from administrator where email='$email'") or die(mysql_error());
 $admin_select_count=mysql_num_rows($admin_select);
 if($admin_select_count>0)
 {
  header("location:admin-user.php?msg=2&view_id=$view_id");
  }
  else
  {
  &/
    /*
	$Sql_check=mysql_query("select * from tbl_user where random='$random'");
	$Sal_Num=mysql_num_rows($Sql_check);
	if($Sal_Num>0)
	{
  			$random=genRandomString();
	}         
		*/
   $update_admin=mysql_query("update administrator set name='$name',password='$password',email='$email',user_type='$user_type' where id='$view_id'") or die(mysql_error());
    header("location:view-user.php?msg=1");

  //}
}
?>
<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" align="" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a>
<span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >User Setup
</strong></span>

 <div align="right" style="padding-right:90px">Welcome Home: <strong><em><u><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?>
</u></em></strong><em></em></div>
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
<div style="clear:both"></div>

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr  style="color:#000;" align="center">
    <td width="10%" ></td>
    <td width="19%" >   </td>
    <td width="50%" ><?
	if($_REQUEST['msg']=='1')
	{
	echo "Admin User Details update success";
	}
	if($_REQUEST['msg']=='2')
	{
	echo "This email address already Registred";
	}
	?>                                   </td>
    <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_data.php">XLS</a></strong> File </td>
  </tr>
  <tr>
    <td>               </td>
    <td>    </td>
    <td></td>
    <td></td>
  </tr>
 
  
  
  <tr>
    <td colspan="2">
</td>
    <td><form name="admin_formreg" id="admin_formreg" method="post" action="">
      <table width="100%" border="0" cellspacing="3" cellpadding="3">
        <tr>
          <td> Name</td>
          <td><input name="name" type="text" id="name" value="<?php echo stripslashes($view_admin_row['name']);?>" /></td>
        </tr>
        <tr>
          <td> Password</td>
          <td><input name="password" type="text" id="password" value="<?php echo stripslashes($view_admin_row['email']);?>" /></td>
        </tr>
        <tr>
          <td>Email</td>
          <td><input name="email" type="text" id="email" value="<?php echo stripslashes($view_admin_row['email']);?>" /></td>
        </tr>
        <tr>
          <td>User Type</td>
          <td>Super
              <input name="user_type" type="radio" value="Super" <?php if($view_admin_row['user_type']=='Super'){echo "checked";}?> />
&nbsp;&nbsp; View Only
      <input name="user_type" type="radio" value="View Only" <?php if($view_admin_row['user_type']=='View Only'){echo "checked";}?> />
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
		  <?php
		  if($admin_user_type=='Super')
		  {?>
		  <input name="Save" type="submit" id="Save" onclick="return admin_register_validate();" value="Update" />
		  <?php
		  }
		  ?>
		  </td>
        </tr>
      </table>
    </form></td>
    <td>&nbsp;</td>
  </tr>
</table>

</div><p>&nbsp;</p>
<div align="center" style="padding-top:210px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
