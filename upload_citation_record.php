<?php
ob_start();
session_start();
include("includes/dbinfo.php");
include("session_check.php");
ini_set(‘max_execution_time’, 3000); //300 seconds = 5 minutes
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
  
	if(document.admin_formreg.item_name.value=="")
	{
	alert("Please upload your clinet xls file");
	document.admin_formreg.item_name.focus();
	return false;
	}
	
	 else
	 {
	   return true;
	  }
}
</script>
<script type="text/javascript">
function validateFileExtension(fld) 
{
	
	if(!/(\.xls|\.xlsx)$/i.test(fld.value))
	 {
		alert("You Uploaded Invalid file format,Plerase Upload\n  .xls format only");
		fld.value="";
		fld.focus();
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
function genRandomString() {
    $length = 6;
    //$characters = '0123456789CDEFGHIJKMNOPQRSTUVWXYZ' ;
	$characters = '0123456789' ;

  $string ='ARK';    
    for ($p = 0; $p < $length; $p++) {
        //$string .= $characters[mt_rand(0, strlen($characters))];
		        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}
if(isset($_REQUEST['Save']))
{
   

    $fname = $_FILES['item_name']['name'];
    
     $chk_ext = explode(".",$fname);
    
     if(strtolower($chk_ext[1]) == "xls")
     {
    
         $filename = $_FILES['item_name']['tmp_name'];
         $handle = fopen($filename, "r");
   
         while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE)
         {
		 
		// echo $data[0];
           // $sql = "INSERT into client(account_cid,municipality,clerk_of_court1) values('$data[1]','$data[2]','$data[3]')";
		   /* ---------------------- ----------------------*/
		   /*
		   $GetCIDrandom=genRandomString();
		   $select_CID_check=mysql_query("select * from citation where CID='$GetCIDrandom'");
		   $select_CID_num=mysql_num_rows($select_CID_check);
		   if($select_CID_num>0)
		   {
		    $GetCIDrandom=genRandomString();
		   }
		   */
		   //addslashes($data[0]);
			  // $select_duplicate_values=mysql_query("select * from citation where CID='".addslashes($data[0])."' and First_Name='".addslashes($data[1])."' and Last_Name='".addslashes($data[2])."' and Address='".addslashes($data[3])."' and City='".addslashes($data[4])."' and State='".addslashes($data[5])."' and Date_of_Birth='".addslashes($data[6])."' and Citation_Number='".addslashes($data[7])."' and Case_Number='".addslashes($data[8])."' and Court_Date='".addslashes($data[9])."' and Charges='".addslashes($data[10])."' and Violation_Date='".addslashes($data[11])."' and Fine_Amount='".addslashes($data[12])."' and Appearance='".addslashes($data[13])."'");
			  $select_duplicate_values=mysql_query("select * from citation where CID='".addslashes($data[0])."' and First_Name='".addslashes($data[1])."' and Last_Name='".addslashes($data[2])."' and Address='".addslashes($data[3])."' and City='".addslashes($data[4])."' and State='".addslashes($data[5])."' and Date_of_Birth='".addslashes($data[6])."' and Citation_Number='".addslashes($data[7])."' and Case_Number='".addslashes($data[8])."' and Court_Date='".addslashes($data[9])."' and Charges='".addslashes($data[10])."' and Violation_Date='".addslashes($data[11])."'  and Appearance='".addslashes($data[13])."'");

			   $select_duplicate_num=mysql_num_rows($select_duplicate_values);
			   $select_duplicate_values_row=mysql_fetch_array($select_duplicate_values);
			   $select_duplicate_values_id=$select_duplicate_values_row['id'];
			   if(!$select_duplicate_num>0)
			   {
					$sql_insert_citation = "insert into citation (CID,First_Name,Last_Name,Address,City,State,Date_of_Birth,Citation_Number,Case_Number,Court_Date,Charges,Violation_Date,Fine_Amount,Appearance) values ('".addslashes($data[0])."','".addslashes($data[1])."','".addslashes($data[2])."','".addslashes($data[3])."','".addslashes($data[4])."','".addslashes($data[5])."','".addslashes($data[6])."','".addslashes($data[7])."','".addslashes($data[8])."','".addslashes($data[9])."','".addslashes($data[10])."','".addslashes($data[11])."','".addslashes($data[12])."','".addslashes($data[13])."')"; // i have two fields only , in oyour case depends on your table structure
					mysql_query($sql_insert_citation) or die(mysql_error());
			  }
			  else
			  {
			        $sql_update_citation ="update citation set Fine_Amount ='".addslashes($data[12])."' where id='$select_duplicate_values_id'";
			       // $sql_insert_citation = "insert into citation (CID,First_Name,Last_Name,Address,City,State,Date_of_Birth,Citation_Number,Case_Number,Court_Date,Charges,Violation_Date,Fine_Amount,Appearance) values ('".addslashes($data[0])."','".addslashes($data[1])."','".addslashes($data[2])."','".addslashes($data[3])."','".addslashes($data[4])."','".addslashes($data[5])."','".addslashes($data[6])."','".addslashes($data[7])."','".addslashes($data[8])."','".addslashes($data[9])."','".addslashes($data[10])."','".addslashes($data[11])."','".addslashes($data[12])."','".addslashes($data[13])."')"; // i have two fields only , in oyour case depends on your table structure
					mysql_query($sql_update_citation) or die(mysql_error());
			  }
			  			
         }
   
         fclose($handle);
         //echo "Successfully Imported";
		header("location:upload_citation_record.php?msg=1");

     }
     else
     {
         //echo "Invalid File";
		   header("location:upload_citation_record.php?msg=2");

     }    
	
	 /*
	 $filename = $_FILES['item_name']['tmp_name'];
 $handle = fopen($filename, "r"); //test.xls excel file name
if ($handle)
{
$array = explode("\n", fread($handle, filesize($filename)));
} 

$total_array = count($array);
$i = 0;

while($i < $total_array)
{
$data = explode(",", $array[$i]);
$sql = "insert into client(account_cid,municipality,clerk_of_court1,clerk_of_court2,clerk_of_court3,client_name,address,city,state,zip,phone,fax,email,webaddress,judge_name,judge_phone,judge_email,police_contact,police_phone,police_email,sheriff_contact,sheriff_phone,sheriff_email,bank,routing_number,account_number,ticket_volume,print_ticket,comments,date) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]','$data[16]','$data[17]','$data[18]','$data[19]','$data[20]','$data[21]','$data[22]','$data[23]','$data[24]','$data[25]','$data[26]','$data[27]','$data[28]','$data[29]')"; // i have two fields only , in oyour case depends on your table structure
$result = mysql_query($sql);

$i++;
}
*/
}
?>
<body>
<div id="main"><div class="head">
<div style="width:40%; float:left" align="" ><a href="index.php"><img src="" width="165" height="123" border="0" style="vertical-align:middle" /></a><span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong >Administration
</strong></span>

 <div align="right" style="padding-right:90px">Welcome Home: <strong><em><u><?php 
if(isset($_SESSION['admin_name']))
 {
 echo $_SESSION['admin_name'];
 }?>
</u></em></strong><em></em></div>
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
<div style="clear:both"></div>

<div class="admin-content1">

<table width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr  style="color:#000;" align="center">
    <td width="10%" ></td>
    <td width="19%" >   </td>
    <td width="50%" >
	<?
	if($_REQUEST['msg']=='1')
	{
	echo "Citation Records Successfully Imported";
	}
	if($_REQUEST['msg']=='2')
	{
	echo "Invalid File";
	}
	?>                                   </td>
    <td width="21%" style="color:#000">Export to <strong style="color:red"><a href="export_citation_record.php">XLS</a></strong> File </td>
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
    <td><form action="" method="post" enctype="multipart/form-data" name="admin_formreg" id="admin_formreg">
      <table width="100%" border="0" cellspacing="3" cellpadding="3">
        <tr>
          <td width="32%"> Upload Date(Xls) </td>
          <td width="68%"><input name="item_name" type="file" id="item_name" onchange="return validateFileExtension(this)"  /></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><input name="Save" type="submit" id="Save" onclick="return admin_register_validate();" value="Upload" /></td>
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
