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
function validate_admin_record()
{
	 if(document.admin_recordform.account_cid.value=="")
	{
	alert("Please enter client Account CID ");
	document.admin_recordform.account_cid.focus();
	return false;
	}
	
    if(document.admin_recordform.municipality.value=="")
	{
	alert("Please enter Court/Municipality Name");
	document.admin_recordform.municipality.focus();
	return false;
	}
	if(document.admin_recordform.clerk_of_court1.value=="")
	{
	alert("Please enter Clerk of Court#1 ");
	document.admin_recordform.clerk_of_court1.focus();
	return false;
	}
	if(document.admin_recordform.clerk_of_court2.value=="")
	{
	alert("Please enter Clerk of Court#2 ");
	document.admin_recordform.clerk_of_court2.focus();
	return false;
	}
	if(document.admin_recordform.clerk_of_court3.value=="")
	{
	alert("Please enter Clerk of Court#3 ");
	document.admin_recordform.clerk_of_court3.focus();
	return false;
	}if(document.admin_recordform.client_name.value=="")
	{
	alert("Please enter Client Name ");
	document.admin_recordform.client_name.focus();
	return false;
	}
	if(document.admin_recordform.address.value=="")
	{
	alert("Please enter client Address ");
	document.admin_recordform.address.focus();
	return false;
	}
	if(document.admin_recordform.city.value=="")
	{
	alert("Please enter client city name ");
	document.admin_recordform.city.focus();
	return false;
	}
	if(document.admin_recordform.state.value=="")
	{
	alert("Please enter client state name ");
	document.admin_recordform.state.focus();
	return false;
	}
	if(document.admin_recordform.phone.value=="")
	{
	alert("Please enter client phone number ");
	document.admin_recordform.phone.focus();
	return false;
	}
	if(document.admin_recordform.email.value=="")
	{
	alert("Please enter client email address ");
	document.admin_recordform.email.focus();
	return false;
	}
	if(document.admin_recordform.email.value.search(/^(\w+(?:\.\w+)*)@((?:\w+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i))
	{
	alert("Please enter your valid email address");
	document.admin_recordform.email.focus();
	return false;
	}	
	if(document.admin_recordform.judge_name.value=="")
	{
	alert("Please enter client judge name ");
	document.admin_recordform.judge_name.focus();
	return false;
	}
	if(document.admin_recordform.judge_phone.value=="")
	{
	alert("Please enter client judge phone ");
	document.admin_recordform.judge_phone.focus();
	return false;
	}
	if(document.admin_recordform.judge_email.value=="")
	{
	alert("Please enter client judge_email address");
	document.admin_recordform.judge_email.focus();
	return false;
	}
	if(document.admin_recordform.judge_email.value.search(/^(\w+(?:\.\w+)*)@((?:\w+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i))
	{
	alert("Please enter your valid judge_email address");
	document.admin_recordform.judge_email.focus();
	return false;
	}	
	
	if(document.admin_recordform.bank.value=="")
	{
	alert("Please enter client bank name");
	document.admin_recordform.bank.focus();
	return false;
	}
	if(document.admin_recordform.routing_number.value=="")
	{
	alert("Please enter client bank routing number ");
	document.admin_recordform.routing_number.focus();
	return false;
	}
	if(document.admin_recordform.account_number.value=="")
	{
	alert("Please enter client bank account number");
	document.admin_recordform.account_number.focus();
	return false;
	}
	if(document.admin_recordform.ticket_volume.value=="")
	{
	alert("Please enter client bank ticket volume");
	document.admin_recordform.ticket_volume.focus();
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
 $view_client_list_select=mysql_query("select * from client where id='$view_id'") or die(mysql_error());
 $view_client_list_count=mysql_num_rows($view_client_list_select);
 $view_client_list_row=mysql_fetch_array($view_client_list_select);
?>

<?php
if(isset($_REQUEST['Admin_Register']))
{
 $account_cid=addslashes($_REQUEST['account_cid']);
 $municipality=addslashes($_REQUEST['municipality']);
 
 $clerk_of_court1=addslashes($_REQUEST['clerk_of_court1']);
 $clerk_of_court2=addslashes($_REQUEST['clerk_of_court2']);
 $clerk_of_court3=addslashes($_REQUEST['clerk_of_court3']);

 $client_name=addslashes($_REQUEST['client_name']);
 $address=addslashes($_REQUEST['address']);
 $city=addslashes($_REQUEST['city']);
 $state=addslashes($_REQUEST['state']);
 $zip=addslashes($_REQUEST['zip']);
 $phone=addslashes($_REQUEST['phone']);
 $fax=addslashes($_REQUEST['fax']);
 $email=addslashes($_REQUEST['email']);
 $webaddress=addslashes($_REQUEST['webaddress']);

 $judge_name=addslashes($_REQUEST['judge_name']);
 $judge_phone=addslashes($_REQUEST['judge_phone']);
 $judge_email=addslashes($_REQUEST['judge_email']);
 
 $police_contact=addslashes($_REQUEST['police_contact']);
 $police_phone=addslashes($_REQUEST['police_phone']);
 $police_email=addslashes($_REQUEST['police_email']);
 
 $sheriff_contact=addslashes($_REQUEST['sheriff_contact']);
 $sheriff_phone=addslashes($_REQUEST['sheriff_phone']);
 $sheriff_email=addslashes($_REQUEST['sheriff_email']);

 $bank=addslashes($_REQUEST['bank']);
 $routing_number=addslashes($_REQUEST['routing_number']);
 $account_number=addslashes($_REQUEST['account_number']);
 $ticket_volume=addslashes($_REQUEST['ticket_volume']);
 
 if(!empty($_REQUEST['print_ticket']))
 {
  $print_ticket=implode(",",$_REQUEST['print_ticket']);
 }

 $comments=addslashes($_REQUEST['comments']);
 $todaydate=date("d-m-Y"); 
   //$insert_client_record=mysql_query("insert into client(account_cid,municipality,clerk_of_court1,clerk_of_court2,clerk_of_court3,client_name,address,city,state,zip,phone,fax,email,webaddress,judge_name,judge_phone,judge_email,police_contact,police_phone,police_email,sheriff_contact,sheriff_phone,sheriff_email,bank,routing_number,account_number,ticket_volume,print_ticket,comments,date) values ('$account_cid','$municipality','$clerk_of_court1','$clerk_of_court2','$clerk_of_court3','$client_name','$address','$city','$state','$zip','$phone','$fax','$email','$webaddress','$judge_name','$judge_phone','$judge_email','$police_contact','$police_phone','$police_email','$sheriff_contact','$sheriff_phone','$sheriff_email','$bank','$routing_number','$account_number','$ticket_volume','$print_ticket','$comments','$todaydate')") or die(mysql_error());
   $update_client_record=mysql_query("update client set account_cid='$account_cid',municipality='$municipality',clerk_of_court1='$clerk_of_court1',clerk_of_court2='$clerk_of_court2',clerk_of_court3='$clerk_of_court3',client_name='$client_name',address='$address',city='$city',state='$state',zip='$zip',phone='$phone',fax='$fax',email='$email',webaddress='$webaddress',judge_name='$judge_name',judge_phone='$judge_phone',judge_email='$judge_email',police_contact='$police_contact',police_phone='$police_phone',police_email='$police_email',sheriff_contact='$sheriff_contact',sheriff_phone='$sheriff_phone',sheriff_email='$sheriff_email',bank='$bank',routing_number='$routing_number',account_number='$account_number',ticket_volume='$ticket_volume',print_ticket='$print_ticket',comments='$comments' where id='$view_id'") or die(mysql_error());
   if($update_client_record)
   {
      header("location:view-clients-details.php?msg=1&view_id=$view_id");

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
<div align="center" class="view_client" ><a href="#"><strong>View Citations</strong></a> | <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a><table width="20%" border="0" align="right" cellpadding="0" cellspacing="4">
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
    <td colspan="2" align="left"><a href="confirmation.php"><img src="/css/images/but_submit.png" width="118" height="33" border="0" /></a></td>
    </tr>
</table></div>
</div>
<div style="clear:both"></div>
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>

<div class="admin-content1">
  <form name="admin_recordform" id="admin_recordform" method="post" action="">
    <table width="100%" border="0" cellspacing="2" cellpadding="2" >
      <tr  style="color:#000;" align="center">
        <td width="10%" ><strong>New Record</strong></td>
        <td width="32%" ></td>
        <td ></td>
        <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_view_clients.php">XLS</a></strong> File </td>
      </tr>
      <tr>
        <td></td>
        <td></td>
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
              <td><input name="account_cid" type="text" id="account_cid" value="<?php echo stripslashes($view_client_list_row['account_cid']); ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
        </table></td>
        <td><table width="100%" border="0" cellspacing="3" cellpadding="3">
            <tr>
              <td>Court/Municipality Name</td>
              <td><input name="municipality" type="text" id="municipality" value="<?php echo stripslashes($view_client_list_row['municipality']); ?>" /></td>
            </tr>
            <tr>
              <td>Clerk of Court#1</td>
              <td><input name="clerk_of_court1" type="text" id="clerk_of_court1" value="<?php echo stripslashes($view_client_list_row['clerk_of_court1']); ?>" /></td>
            </tr>
            <tr>
              <td>Clerk of Court#2</td>
              <td><input name="clerk_of_court2" type="text" id="clerk_of_court2" value="<?php echo stripslashes($view_client_list_row['clerk_of_court2']); ?>" /></td>
            </tr>
            <tr>
              <td>Clerk of Court#3</td>
              <td><input name="clerk_of_court3" type="text" id="clerk_of_court3" value="<?php echo stripslashes($view_client_list_row['clerk_of_court3']); ?>" /></td>
            </tr>
            <tr>
              <td>Client Name</td>
              <td><input name="client_name" type="text" id="client_name" value="<?php echo stripslashes($view_client_list_row['client_name']); ?>" /></td>
            </tr>
            <tr>
              <td>Address</td>
              <td><input name="address" type="text" id="address" value="<?php echo stripslashes($view_client_list_row['address']); ?>" /></td>
            </tr>
            <tr>
              <td>City</td>
              <td><input name="city" type="text" id="city" value="<?php echo stripslashes($view_client_list_row['city']); ?>" /></td>
            </tr>
            <tr>
              <td>State</td>
              <td><input name="state" type="text" id="state" value="<?php echo stripslashes($view_client_list_row['state']); ?>" /></td>
            </tr>
            <tr>
              <td>Zip</td>
              <td><input name="zip" type="text" id="zip" value="<?php echo stripslashes($view_client_list_row['zip']); ?>" /></td>
            </tr>
            <tr>
              <td>Phone</td>
              <td><input name="phone" type="text" id="phone" value="<?php echo stripslashes($view_client_list_row['phone']); ?>" /></td>
            </tr>
            <tr>
              <td>Fax</td>
              <td><input name="fax" type="text" id="fax" value="<?php echo stripslashes($view_client_list_row['fax']); ?>" /></td>
            </tr>
            <tr>
              <td>Email</td>
              <td><input name="email" type="text" id="email" value="<?php echo stripslashes($view_client_list_row['email']); ?>" /></td>
            </tr>
            <tr>
              <td>Web Address</td>
              <td><input name="webaddress" type="text" id="webaddress" value="<?php echo stripslashes($view_client_list_row['webaddress']); ?>" />
              </td>
            </tr>
            <tr>
              <td><strong>Court &amp; Enforcement </strong></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>Judge Name</td>
              <td><input name="judge_name" type="text" id="judge_name" value="<?php echo stripslashes($view_client_list_row['judge_name']); ?>" /></td>
            </tr>
            <tr>
              <td>Judge Phone</td>
              <td><input name="judge_phone" type="text" id="judge_phone" value="<?php echo stripslashes($view_client_list_row['judge_phone']); ?>" /></td>
            </tr>
            <tr>
              <td>Judge Email</td>
              <td><input name="judge_email" type="text" id="judge_email" value="<?php echo stripslashes($view_client_list_row['judge_email']); ?>" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>Police Contact</td>
              <td><input name="police_contact" type="text" id="police_contact" value="<?php echo stripslashes($view_client_list_row['police_contact']); ?>" /></td>
            </tr>
            <tr>
              <td>Police Phone</td>
              <td><input name="police_phone" type="text" id="police_phone" value="<?php echo stripslashes($view_client_list_row['police_phone']); ?>" /></td>
            </tr>
            <tr>
              <td>Police Email</td>
              <td><input name="police_email" type="text" id="police_email" value="<?php echo stripslashes($view_client_list_row['police_email']); ?>" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>Sheriff Contact</td>
              <td><input name="sheriff_contact" type="text" id="sheriff_contact" value="<?php echo stripslashes($view_client_list_row['sheriff_contact']); ?>" /></td>
            </tr>
            <tr>
              <td>Sheriff Phone</td>
              <td><input name="sheriff_phone" type="text" id="sheriff_phone" value="<?php echo stripslashes($view_client_list_row['sheriff_phone']); ?>" /></td>
            </tr>
            <tr>
              <td>Sheriff Email</td>
              <td><input name="sheriff_email" type="text" id="sheriff_email" value="<?php echo stripslashes($view_client_list_row['sheriff_email']); ?>" /></td>
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
              <td><input name="bank" type="text" id="bank" value="<?php echo stripslashes($view_client_list_row['bank']); ?>" /></td>
            </tr>
            <tr>
              <td>Routing Number</td>
              <td><input name="routing_number" type="text" id="routing_number" value="<?php echo stripslashes($view_client_list_row['routing_number']); ?>" /></td>
            </tr>
            <tr>
              <td>Account Number</td>
              <td><input name="account_number" type="text" id="account_number" value="<?php echo stripslashes($view_client_list_row['account_number']); ?>" /></td>
            </tr>
            <tr>
              <td><strong>Ticket Volume<br />
              </strong></td>
              <td><input name="ticket_volume" type="text" id="ticket_volume" value="<?php echo stripslashes($view_client_list_row['ticket_volume']); ?>" /></td>
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
                    <td width="16%"><input type="checkbox" name="print_ticket[]" value="We Do" /></td>
                    <td width="24%">They Do </td>
                    <td width="38%"><input type="checkbox" name="print_ticket[]" value="They Do" /></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td>Comments & Notes</td>
              <td><textarea name="comments" id="comments"><?php echo stripslashes($view_client_list_row['comments']); ?></textarea>
              </td>
            </tr>
            <tr>
              <td colspan="2" align="center">
			   <?php
		  if($admin_user_type=='Super')
		  {?>
			  <input name="Admin_Register" type="submit" value="Update " onclick="return validate_admin_record();" />
			  <?php
			  }
			  ?>
			  </td>
            </tr>
        </table></td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </form>
</div>
<p>&nbsp;</p>
<div align="center" style="padding-top:1060px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
