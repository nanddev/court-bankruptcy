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
<script type="text/javascript">
function validate_Partnersform()
{
   if(document.Partnersform.partners_page.value=="")
	{
	alert("Please Enter Partners Contact Person");
	document.Partnersform.partners_page.focus();
	return false;
	}
	 if(document.Partnersform.partners_name.value=="")
	{
	alert("Please Enter Partners Name");
	document.Partnersform.partners_name.focus();
	return false;
	}
	if(document.Partnersform.address.value=="")
	{
	alert("Please Enter Partner Address");
	document.Partnersform.address.focus();
	return false;
	}
	if(document.Partnersform.city.value=="")
	{
	alert("Please Enter Partner City");
	document.Partnersform.city.focus();
	return false;
	}
	if(document.Partnersform.state.value=="")
	{
	alert("Please Select Partner State");
	document.Partnersform.state.focus();
	return false;
	}
	if(document.Partnersform.zip.value=="")
	{
	alert("Please Enter Partner Zip");
	document.Partnersform.zip.focus();
	return false;
	}
	if(document.Partnersform.phone.value=="")
	{
	alert("Please Enter Partner Phone");
	document.Partnersform.phone.focus();
	return false;
	}
	if(document.Partnersform.email.value=="")
	{
	alert("Please Enter Partner Email Address");
	document.Partnersform.email.focus();
	return false;
	}
	if(document.Partnersform.email.value.search(/^(\w+(?:\.\w+)*)@((?:\w+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i))
	{
	alert("Please Enter Valid Partner Email Address");
	document.Partnersform.email.focus();
	return false;
	}	
	if(document.Partnersform.services_rendered.value=="")
	{
	alert("Please Enter Partner Services Rendered");
	document.Partnersform.services_rendered.focus();
	return false;
	}
	if(document.Partnersform.banking_information.value=="")
	{
	alert("Please Enter Partner Bank Name");
	document.Partnersform.banking_information.focus();
	return false;
	}
	if(document.Partnersform.routing_number.value=="")
	{
	alert("Please Enter Partner Routing Number");
	document.Partnersform.routing_number.focus();
	return false;
	}
	if(document.Partnersform.account_number.value=="")
	{
	alert("Please Enter Partner Account Number");
	document.Partnersform.account_number.focus();
	return false;
	}
	if(document.Partnersform.comments.value=="")
	{
	alert("Please Enter Partner Comments");
	document.Partnersform.comments.focus();
	return false;
	}
	else
	{
	document.Partnersform.submit();
	return true;
	}

}
</script>
</head>
<?php
if(isset($_REQUEST['Partnersform_Submit']))
{
 $partners_page=addslashes($_REQUEST['partners_page']);
 $partners_name=addslashes($_REQUEST['partners_name']);
 $address=addslashes($_REQUEST['address']);
 $city=addslashes($_REQUEST['city']);
 $state=addslashes($_REQUEST['state']);
 $zip=addslashes($_REQUEST['zip']);
 $phone=addslashes($_REQUEST['phone']);
 $fax=addslashes($_REQUEST['fax']);
 $email=addslashes($_REQUEST['email']);
 $webaddress=addslashes($_REQUEST['webaddress']);
 
 
 $services_rendered=addslashes($_REQUEST['services_rendered']);
 $banking_information=addslashes($_REQUEST['banking_information']);
 $routing_number=addslashes($_REQUEST['routing_number']);
 $account_number=addslashes($_REQUEST['account_number']);
 
 $comments=addslashes($_REQUEST['comments']);
$title=addslashes($_REQUEST['title']);
 $todaydate=date("d-m-Y");
  

		
   $insert_admin=mysql_query("insert into partner(partners_page,title,partners_name,address,city,state,zip,phone,fax,email,webaddress,services_rendered,banking_information,routing_number,account_number,comments,date) values ('$partners_page','$title','$partners_name','$address','$city','$state','$zip','$phone','$fax','$email','$webaddress','$services_rendered','$banking_information','$routing_number','$account_number','$comments','$todaydate')");
   
   header("location:view-partner.php?msg=1");

 
}
?>

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
--> <a href="admin-main.php?name=jeff"><strong>Payee Search</strong></a>
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
<h2 style="padding-left:30px;"><strong>Add New Partner</strong></h2>
<?php
include("inner_page_menu.php");
?>
<br />

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >

  <tr>
    <td width="6%">&nbsp;</td>
    <td width="89%"><form name="Partnersform" id="Partnersform" method="post" action="">
      <table width="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td>Contact Person</td>
          <td><input name="partners_page" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Title</td>
          <td><input name="title" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Partners Name<br /></td>
          <td><input name="partners_name" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Address</td>
          <td><input name="address" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>City</td>
          <td><input name="city" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>State </td>
          <td>
		  <!--
		  <input name="state" type="text" size="30" />
		  -->
		  <select name="state">
		  <option value="" selected>Select</option>
		  <option value="AL">Alabama</option> <option value="AK">Alaska</option> <option value="AZ">Arizona</option> <option value="AR">Arkansas</option> <option value="CA">California</option> <option value="CO">Colorado</option> <option value="CT">Connecticut</option> <option value="DE">Delaware</option> <option value="DC">District of Columbia</option> <option value="FL">Florida</option> <option value="GA">Georgia</option> <option value="HI">Hawaii</option> <option value="ID">Idaho</option> <option value="IL">Illinois</option> <option value="IN">Indiana</option> <option value="IA">Iowa</option> <option value="KS">Kansas</option> <option value="KY">Kentucky</option> <option value="LA">Louisiana</option> <option value="ME">Maine</option> <option value="MD">Maryland</option> <option value="MA">Massachusetts</option> <option value="MI">Michigan</option> <option value="MN">Minnesota</option> <option value="MS">Mississippi</option> <option value="MO">Missouri</option> <option value="MT">Montana</option> <option value="NE">Nebraska</option> <option value="NV">Nevada</option> <option value="NH">New Hampshire</option> <option value="NJ">New Jersey</option> <option value="NM">New Mexico</option> <option value="NY">New York</option> <option value="NC">North Carolina</option> <option value="ND">North Dakota</option> <option value="OH">Ohio</option> <option value="OK">Oklahoma</option> <option value="OR">Oregon</option> <option value="PA">Pennsylvania</option> <option value="RI">Rhode Island</option> <option value="SC">South Carolina</option> <option value="SD">South Dakota</option> <option value="TN">Tennessee</option> <option value="TX">Texas</option> <option value="UT">Utah</option> <option value="VT">Vermont</option> <option value="VA">Virginia</option> <option value="WA">Washington</option> <option value="WV">West Virginia</option> <option value="WI">Wisconsin</option> <option value="WY">Wyoming</option> </select>
		  </td>
        </tr>
        <tr>
          <td>Zip</td>
          <td><input name="zip" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Phone</td>
          <td><input name="phone" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Fax</td>
          <td><input name="fax" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Email</td>
          <td><input name="email" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Web Address</td>
          <td><input name="webaddress" type="text" size="30" /></td>
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
          <td><input name="services_rendered" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Bank Name</td>
          <td><input name="banking_information" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Routing Number</td>
          <td><input name="routing_number" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Account Number</td>
          <td><input name="account_number" type="text" size="30" /></td>
        </tr>
        <tr>
          <td>Comments</td>
          <td><textarea name="comments" cols="40" rows="3" id="comments"></textarea></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="8%">&nbsp;</td>
            <td width="52%" align="center">
			<input type="hidden" name="Partnersform_Submit" value="Partnersform_Submit" /> 
			 <a href="#" onclick="return validate_Partnersform();">Save</a> | <a href="#">Clear Record</a> </td>
              <td width="40%">&nbsp;</td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td width="20%">&nbsp;</td>
          <td width="80%">&nbsp;</td>
        </tr>
      </table>
    </form></td>
    <td width="5%">&nbsp;</td>
  </tr>
</table>

</div><p>&nbsp;</p>
<div align="center" style="padding-top:540px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
