<?php
ob_start();
session_start();
include("includes/dbinfo.php");
include("session_check.php");
if(!isset($_SESSION['sesid']))
  {
  	  $_SESSION['sesid']=session_id();
     $ct_session_id=session_id();
  }
  else
  {
   	  
     $ct_session_id=$_SESSION['sesid'];
  }
  
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
 $view_client_list_select=mysql_query("select * from citation where id='$view_id'") or die(mysql_error());
 $view_client_list_count=mysql_num_rows($view_client_list_select);
 $view_client_list_row=mysql_fetch_array($view_client_list_select);
?>
<?php
          
if($_REQUEST['Addpayment_Submit']=='Addpayment')
{
  
 
  $product_id=$_REQUEST['cart_id'];
 
  $ct_date=date("Y-m-d");
  $select_citation_addcart=mysql_query("select * from citation where id='$product_id'");
  $select_citation_addcart_num=mysql_num_rows($select_citation_addcart);  
  $select_citation_addcart_row=mysql_fetch_array($select_citation_addcart);
  if($select_citation_addcart_num>0)
  {
      if($select_citation_addcart_row['Appearance']=='0')
	  {
		  $check_mysql_cart=mysql_query("select * from citation_cart_temp where product_id='$product_id' and ct_session_id='$ct_session_id'");
		  $check_mysql_cart_num=mysql_num_rows($check_mysql_cart);
		  if(!$check_mysql_cart_num>0)
		  {
			$insert_payment_cart=mysql_query("insert into citation_cart_temp(product_id,ct_session_id,ct_date) values ('$product_id','$ct_session_id','$ct_date')");
	         header("location:results.php?Last_Name=$Last_Name&Citation_Number=$Citation_Number&Date_of_Birth=$Date_of_Birth&msg=3&ct_session_id=$ct_session_id");

		  }
		  else
		  {
		    $delete_payment_cart=mysql_query("delete from citation_cart_temp where product_id='$product_id' and ct_session_id='$ct_session_id'");
		   	header("location:results.php?Last_Name=$Last_Name&Citation_Number=$Citation_Number&Date_of_Birth=$Date_of_Birth&msg=2&ct_session_id=$ct_session_id");

		  }
		  //$prod_id=mysql_insert_id();
	 }
	 else
	 {
	 	   header("location:results.php?Last_Name=$Last_Name&Citation_Number=$Citation_Number&Date_of_Birth=$Date_of_Birth&msg=1");

	   ?>
	   <script type="text/javascript">
	   //alert("This citation cannot be paid online. You must make a court appearance,\nplease refer any additional questions to the Clerk of Court or call +1-877-689-5144");
       </script>
	   <?
	 }
 }	  

}
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
<div class="admin-content1">

<!--
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
  -->
</div>
<div style="clear:both"></div>
<h2 style="padding-left:30px;"><strong>View Citation Details:</strong></h2>

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr  style="color:#000;" align="center">
    <td width="10%" ><strong>Current Record</strong></td>
    <td colspan="2" align="left" ><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="7%">&nbsp;</td>
      <td><a href="admin-main.php?name=jeff">View Citation & Data Records </a> <?php
		  if($admin_user_type=='Super')
		  {?>|  <a href="upload_citation_record.php">Upload Data File &nbsp;</a><?php }?></td>
      </tr>
    </table></td>
    <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_citation_record.php">XLS</a></strong> File </td>
  </tr>
  <tr>
    <td>               </td>
    <td width="32%">    </td>
    <td></td>
    <td></td>
  </tr>
 
  
  
  <tr>
    <td colspan="2" align="left" valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><strong>Account CID&nbsp;</strong></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><?php echo stripslashes($view_client_list_row['CID']); ?></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table>
</td>
    <td><form name="form1" id="form1" method="post" action="">
      <table width="100%" border="0" cellspacing="3" cellpadding="3">
        <tr>
          <td width="53%">First Name </td>
          <td width="47%"><?php echo stripslashes($view_client_list_row['First_Name']); ?></td>
        </tr>
        <tr>
          <td>Last Name </td>
          <td><?php echo stripslashes($view_client_list_row['Last_Name']); ?></td>
        </tr>
        <tr>
          <td>Address</td>
          <td><?php echo stripslashes($view_client_list_row['Address']); ?></td>
        </tr>
        <tr>
          <td>City</td>
          <td><?php echo stripslashes($view_client_list_row['City']); ?></td>
        </tr>
        <tr>
          <td>State</td>
          <td><?php echo stripslashes($view_client_list_row['State']); ?></td>
        </tr>
        <tr>
          <td>Date Of Birth </td>
          <td><?php echo stripslashes($view_client_list_row['Date_of_Birth']); ?></td>
        </tr>
        <tr>
          <td>Citation Number</td>
          <td><?php echo stripslashes($view_client_list_row['Citation_Number']); ?></td>
        </tr>
        <tr>
          <td>Case Number</td>
          <td><?php echo stripslashes($view_client_list_row['Case_Number']); ?></td>
        </tr>
        <tr>
          <td>Court Date</td>
          <td><?php echo stripslashes($view_client_list_row['Court_Date']); ?></td>
        </tr>
        <tr>
          <td>Charges</td>
          <td><?php echo stripslashes($view_client_list_row['Charges']); ?></td>
        </tr>
        <tr>
          <td>Violation Date</td>
          <td><?php echo stripslashes($view_client_list_row['Violation_Date']); ?></td>
        </tr>
        <tr>
          <td>Fine Amount</td>
          <td><?php echo stripslashes($view_client_list_row['Fine_Amount']); ?></td>
        </tr>
        <tr>
          <td>Appearance</td>
          <td><?php echo stripslashes($view_client_list_row['Appearance']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" align="center">
		  <a href="results.php?Last_Name=<?php echo stripslashes($view_client_list_row['Last_Name']); ?>&Citation_Number=<?php echo stripslashes($view_client_list_row['Citation_Number']); ?>&Date_of_Birth=<?php echo stripslashes($view_client_list_row['Date_of_Birth']); ?>&Addpayment_Submit=Addpayment&cart_id=<?php echo $view_id;?>">Pay Ticket</a> | 
		  <?php
		  if($admin_user_type=='Super')
		  {?>
		  <a href="delete-view-clients.php?view_id=<?php echo $view_id;?>" onclick="return confirm_admin_delete();">Delete Record</a> | <a href="modify-view-clients.php?view_id=<?php echo $view_id;?>">Update Record</a>
		  <?php
		  }
		  ?> </td>
        </tr>
      </table>
    </form></td>
    <td>&nbsp;</td>
  </tr>
</table>

</div><p>&nbsp;</p>
<div align="center" style="padding-top:420px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
