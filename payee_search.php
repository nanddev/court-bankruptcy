<?php
ob_start();
session_start();
include("includes/dbinfo.php");
include("session_check.php");
include('ps_pagination.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Online Court Payment System Demo</title>
	<script src="calender/jquery-ui.min.js"></script>
	<script src="calender/jquery-1.11.0.min.js"></script>
	<link rel="stylesheet" href="calender/jquery-ui.css">
	<link href="calender/jquery-ui.css" rel="stylesheet" type="text/css"/>
                
 <script src="calender/jquery-1.11.0.min.js"></script>
  <script src="calender/jquery-ui.min.js"></script>
  <script>

	$(function() {
			$( "#datepicker" ).datepicker();

	});
	
	$(function() {
			$( "#datepicker1" ).datepicker();

	});
	
	</script>	
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
<script type="text/javascript">
function validate_search_reports()
{

if(document.Reports_form1.date_from.value=="")
{
alert("Please select your Period From Date");
document.Reports_form1.date_from.focus();
return false;
}
if(document.Reports_form1.date_to.value=="")
{
alert("Please select your Period To Date");
document.Reports_form1.date_to.focus();
return false;
}
/*
if(document.Reports_form1.CID.value=="")
{
alert("Please enter your CID Numner");
document.Reports_form1.CID.focus();
return true;
}
*/
else
{
document.Reports_form1.Submit_Repots.value='Search';
document.Reports_form1.submit();
return true;
}

}
</script>

</head>

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


  <form name="Reports_form1" id="Reports_form1" method="post" action="">

  <div style="float:left; width:30%; padding:15px"><!--<strong>CID Search</strong> &nbsp;&nbsp;<input name="CID" type="text" />--></div>
 
<div style="float:left; width:70%;padding:15px ">
<table width="80%" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td width="28%"><strong>Reports</strong></td>
    <td width="72%"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="21%">Period  From  &nbsp;</td>
      <td width="29%"><input name="date_from" type="text" id="datepicker" size="10" readonly="" /></td>
        <td width="18%">Period To &nbsp;</td>
        <td width="32%"><input name="date_to" type="text" id="datepicker1" size="10" readonly="" /></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><strong>CID</strong></td>
    <td><input name="CID" type="text" id="CID" /></td>
  </tr>  
   <tr>
     <td colspan="2" align="left">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2" align="left"><img src="/css/images/but_submit.png" width="118" height="33" border="0" onclick="return validate_search_reports();"  />
	<input type="hidden" name="Submit_Repots" id="Submit_Repots" /></td>
    </tr>
</table>
</div>

  </form>
</div>
<div style="clear:both"></div>
<p>&nbsp;</p>
<!--
<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>
-->
<?php
/*
echo $db_date = "2012-06-29 00:44:53";
echo "<br>";
$new_format_date = date("d-m-Y h:i:s", strtotime($db_date));
echo $new_format_date;
*/
 $date_from = $_REQUEST['date_from'];
 $date_to = $_REQUEST['date_to']; 
 $CID=  $_REQUEST['CID']; 
                                     $sql_search_query1="SELECT * FROM orders where paidon>='$date_from' and paidon<='$date_to'";									  
									 $sql_search_query1.="ORDER BY oid  DESC "; 	
									 $rowsPerPage=10;
								
										 $result_select1=mysql_query($sql_search_query1) or die(mysql_error());
										 $result_select1_num1=mysql_num_rows($result_select1);
									    $maxPage = ceil($result_select1_num1/$rowsPerPage); 
									   
							    $pager = new PS_Pagination($conn, $sql_search_query1, $rowsPerPage, 5, "date_from=$date_from&date_to=$date_to&CID=$CID");
								//$pager->setDebug(true);
								$rs = $pager->paginate();
								//if(!$rs) die(mysql_error());
if(isset($_REQUEST['Submit_Repots']))
{
/*
 //echo $_REQUEST['Submit_Repots'];
 $db_date = "2012-06-29 00:44:53";
$new_format_date = date("d-m-Y h:i:s", strtotime($db_date));
echo $new_format_date;
*/
 $date_from = $_REQUEST['date_from'];
 $date_to = $_REQUEST['date_to']; 
 $CID=  $_REQUEST['CID']; 

								    /*
								     $sql_search_query1="SELECT * FROM orders where paidon>='$date_from' and paidon<='$date_to'";									  
									 $sql_search_query1.="ORDER BY oid  DESC "; 	
									 $rowsPerPage=1;
								
										 $result_select1=mysql_query($sql_search_query1) or die(mysql_error());
										 $result_select1_num1=mysql_num_rows($result_select1);
									    $maxPage = ceil($result_select1_num1/$rowsPerPage); 
									   
							    $pager = new PS_Pagination($conn, $sql_search_query1, $rowsPerPage, 5, "date_from=$date_from&date_to=$date_to&CID=$CID");
								//$pager->setDebug(true);
								$rs = $pager->paginate();
								*/
								//if(!$rs) die(mysql_error());
								
}								
							  ?>
							  
  
<div class="admin-content1">
<h2 style="padding-left:30px;"><strong>Payee Search Results:</strong></h2>
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
	<?php
	 if($rs>0)
	 {
	 ?>
<table width="80%" border="0" cellspacing="4" cellpadding="4" style="margin-left:30px;" >
  <tr  style="color:#FFF;" align="center">
    <td width="16%" bgcolor="#366092">Name</td>
    <td width="24%" bgcolor="#366092">Citation Number</td>
    <td width="22%" bgcolor="#366092">Email</td>
    <td width="19%" bgcolor="#366092">Phone</td>
    <td width="19%" bgcolor="#366092">Select</td>
  </tr>
      <?php
 
	while($product_row = mysql_fetch_assoc($rs))
	{
									   //echo $product_row ['name'];
									
                               ?>
  <tr bgcolor="#dce6f2">
    <td><?php 	echo stripslashes($product_row['name']);?>               </td>
    <td><a href="view-clients-details.php?view_id=<?php echo $product_row['oid']?>" style="color:#000000;"><?php echo stripslashes($product_row['citation_number']);?></a></td>
    <td><?php	echo stripslashes($product_row['email']);?></td>
    <td align="center"><?php echo stripslashes($product_row['phone']);?>
</td>

    <td align="center"><strong style="text-decoration:underline; color:#FF0000"><!--<a href="view-orders-details.php?view_id=<?php echo $product_row['oid']?>">View</a>--> 
	      <?php
		  if($admin_user_type=='Super')
		  {?>
	 <a href="delete-orders-list.php?view_id=<?php echo $product_row['oid']?>" onclick="return confirm_admin_delete();">Delete</a></strong>
	<?php
	}
	?>
	</td>
  </tr>
  <?php
   $Fine_Amount_Total=$Fine_Amount_Total+$product_row['chargetotal'];
   $Processing_fee_Total=$Processing_fee_Total+$product_row['fine_amount'];

  }
  
  ?>
  <tr  style="color:#FFF;" align="center">
    <td width="16%" bgcolor="#366092">&nbsp;</td>
    <td colspan="2" bgcolor="#366092">Gross Dollar Amounts : &nbsp; $<?php echo $Fine_Amount_Total;?></td>
    <td colspan="2" bgcolor="#366092">Gross Fees Collected : &nbsp;$<?php echo $Processing_fee_Total;?></td>
   </tr>
 
  <tr align="center" valign="middle">
    <td colspan="5">
	<?php
   if($maxPage>1)
   {
   ?>
  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="35%" style="border-right:none;">&nbsp;</td>
      <td width="40%" align="center" valign="middle" style="border-right:none;"><?php echo $pager->renderPrev('Previous');?>&nbsp;<?php echo $pager->renderNav('<span>', '</span>');?>&nbsp;<?php echo $pager->renderNext('Next'); ?></td>
      <td width="25%" style="border-right:none;">&nbsp;</td>
    </tr>
  </table>
  <?php
  }
  ?>
	
</td>
    </tr>
</table>
<?php
}
else
{
?>
<p align="center">Payee search results not available this duration </p>
<?php
}
?>
</div>
<div style="clear:both"></div>
<p>&nbsp;</p>
<div align="center" style="padding-top:0px; padding-bottom:30px;">All rights reserved. 2012 <a href="disclaimer.php">Disclaimer </a></div>
</div>

</body>
</html>
