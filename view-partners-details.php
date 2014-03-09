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
<?php
 $view_id=$_REQUEST['view_id'];
 $view_client_list_select=mysql_query("select * from partner where id='$view_id'") or die(mysql_error());
 $view_client_list_count=mysql_num_rows($view_client_list_select);
 $view_client_list_row=mysql_fetch_array($view_client_list_select);
?>
<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" align="" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a><span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Administration
</strong></span>

 <div align="right" style="padding-right:90px;">Welcome Home: <strong><em><u><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?></u></em></strong><em></em></div>
</div>                
<div align="center" class="view_client" > <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a>
	
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
<!--
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
    <td colspan="2" align="left"><img src="/css/images/but_submit.png" width="118" height="33" border="0" /></td>
    </tr>
</table></div>
</div>
-->
<div style="clear:both"></div>
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr  style="color:#000;" align="center">
    <td width="17%" >&nbsp;</td>
    <td width="62%" >
	<?
	if($_REQUEST['msg']=='1')
	{
	echo "Partner List update success!";
	}
	
	?>                                   </td>
    <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_view_partners.php">XLS</a></strong> File </td>
  </tr>
  <tr>
    <td>               </td>
    <td></td>
    <td></td>
  </tr>
 
  
  
  <tr>
    <td align="left" valign="top">&nbsp;</td>
    <td><form name="Partnersform" id="Partnersform" method="post" action="">
      <table width="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td>Contact Person</td>
          <td><?php echo stripslashes($view_client_list_row['partners_page']); ?></td>
        </tr>
        <tr>
          <td>Title</td>
          <td><?php echo stripslashes($view_client_list_row['title']); ?></td>
        </tr>
        <tr>
          <td>Partners Name<br /></td>
          <td><?php echo stripslashes($view_client_list_row['partners_name']); ?></td>
        </tr>
        <tr>
          <td>Address</td>
          <td><?php echo stripslashes($view_client_list_row['address']); ?></td>
        </tr>
        <tr>
          <td>City</td>
          <td><?php echo stripslashes($view_client_list_row['city']); ?></td>
        </tr>
        <tr>
          <td>State </td>
          <td><?php echo stripslashes($view_client_list_row['state']); ?></td>
        </tr>
        <tr>
          <td>Zip</td>
          <td><?php echo stripslashes($view_client_list_row['zip']); ?></td>
        </tr>
        <tr>
          <td>Phone</td>
          <td><?php echo stripslashes($view_client_list_row['phone']); ?></td>
        </tr>
        <tr>
          <td>Fax</td>
          <td><?php echo stripslashes($view_client_list_row['fax']); ?></td>
        </tr>
        <tr>
          <td>Email</td>
          <td><?php echo stripslashes($view_client_list_row['email']); ?></td>
        </tr>
        <tr>
          <td>Web Address</td>
          <td><?php echo stripslashes($view_client_list_row['webaddress']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><strong>Payment Terms:</strong></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Services Rendered</td>
          <td><?php echo stripslashes($view_client_list_row['services_rendered']); ?></td>
        </tr>
        <tr>
          <td>Bank Name</td>
          <td><?php echo stripslashes($view_client_list_row['banking_information']); ?></td>
        </tr>
        <tr>
          <td>Routing Number</td>
          <td><?php echo stripslashes($view_client_list_row['routing_number']); ?></td>
        </tr>
        <tr>
          <td>Account Number</td>
          <td><?php echo stripslashes($view_client_list_row['account_number']); ?></td>
        </tr>
        <tr>
          <td>Comments</td>
          <td><?php echo stripslashes($view_client_list_row['comments']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>
		  <?php
		  if($admin_user_type=='Super')
		  {?>
		  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="8%">&nbsp;</td>
                <td width="52%" align="center"><a href="delete-view-partners.php?view_id=<?php echo $view_id;?>" onclick="return confirm_admin_delete();">Delete Record</a> | <a href="modify-view-partners.php?view_id=<?php echo $view_id;?>">Update Record</a></td>
                <td width="40%">&nbsp;</td>
              </tr>
          </table>
		  <?php
		  }
		  ?>
		  </td>
        </tr>
        <tr>
          <td width="20%">&nbsp;</td>
          <td width="80%">&nbsp;</td>
        </tr>
      </table>
    </form></td>
    <td>&nbsp;</td>
  </tr>
</table>

</div><p>&nbsp;</p>
<div align="center" style="padding-top:470px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
