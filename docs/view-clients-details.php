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
 $view_client_list_select=mysql_query("select * from client where id='$view_id'") or die(mysql_error());
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
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>

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
    <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_view_clients.php">XLS</a></strong> File </td>
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
        <td><?php echo stripslashes($view_client_list_row['account_cid']); ?></td>
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
          <td>Court/Municipality Name</td>
          <td><?php echo stripslashes($view_client_list_row['municipality']); ?></td>
        </tr>
        <tr>
          <td>Clerk of Court#1</td>
          <td><?php echo stripslashes($view_client_list_row['clerk_of_court1']); ?></td>
        </tr>
        <tr>
          <td>Clerk of Court#2</td>
          <td><?php echo stripslashes($view_client_list_row['clerk_of_court2']); ?></td>
        </tr>
        <tr>
          <td>Clerk of Court#3</td>
          <td><?php echo stripslashes($view_client_list_row['clerk_of_court3']); ?></td>
        </tr>
        <tr>
          <td>Client Name</td>
          <td><?php echo stripslashes($view_client_list_row['client_name']); ?></td>
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
          <td>State</td>
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
          <td><strong>Court &amp; Enforcement </strong></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Judge Name</td>
          <td><?php echo stripslashes($view_client_list_row['judge_name']); ?></td>
        </tr>
        <tr>
          <td>Judge Phone</td>
          <td><?php echo stripslashes($view_client_list_row['judge_phone']); ?></td>
        </tr>
        <tr>
          <td>Judge Email</td>
          <td>
			<table border="0" cellpadding="0" cellspacing="0" width="197" style="border-collapse: collapse; width: 148pt">
				<colgroup>
					<col width="197" style="width: 148pt">
				</colgroup>
				<tr height="20" style="height: 15.0pt">
					<td height="20" width="197" style="height: 15.0pt; width: 148pt; color: blue; text-decoration: underline; text-underline-style: single; font-size: 11.0pt; font-weight: 400; font-style: normal; font-family: Calibri, sans-serif; text-align: general; vertical-align: bottom; white-space: nowrap; border: medium none; padding-left: 1px; padding-right: 1px; padding-top: 1px">
					<a href="mailto:<?php echo stripslashes($view_client_list_row['judge_email']); ?>">
					<?php echo stripslashes($view_client_list_row['judge_email']); ?></a></td>
				</tr>
			</table>
</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Police Contact</td>
          <td><?php echo stripslashes($view_client_list_row['police_contact']); ?></td>
        </tr>
        <tr>
          <td>Police Phone</td>
          <td><?php echo stripslashes($view_client_list_row['police_phone']); ?></td>
        </tr>
        <tr>
          <td>Police Email</td>
          <td><?php echo stripslashes($view_client_list_row['police_email']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Sheriff Contact</td>
          <td><?php echo stripslashes($view_client_list_row['sheriff_contact']); ?></td>
        </tr>
        <tr>
          <td>Sheriff Phone</td>
          <td><?php echo stripslashes($view_client_list_row['sheriff_phone']); ?></td>
        </tr>
        <tr>
          <td>Sheriff Email</td>
          <td><?php echo stripslashes($view_client_list_row['sheriff_email']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><strong>Banking Information</strong></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Bank</td>
          <td><?php echo stripslashes($view_client_list_row['bank']); ?></td>
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
          <td><strong>Ticket Volume<br />
          </strong></td>
          <td><?php echo stripslashes($view_client_list_row['ticket_volume']); ?></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><strong>Who Pays for Printed Tickets?<br />
          </strong></td>
          <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="22%">We Do</td>
                <td width="16%">X</td>
                <td width="24%">They Do </td>
                <td width="38%"><input type="checkbox" name="checkbox" value="checkbox" /></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td>Comments & Notes</td>
          <td><?php echo stripslashes($view_client_list_row['comments']); ?></td>
        </tr>
        <tr>
          <td colspan="2" align="center">
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
<div align="center" style="padding-top:1060px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
