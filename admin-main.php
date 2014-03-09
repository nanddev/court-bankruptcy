<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Auth.php");
include($_SERVER['DOCUMENT_ROOT'] . '/ps_pagination.php');

$Auth = new Auth();
$Auth->forceLogin();

$authInfo = $Auth->getAuthInfo();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<script type="text/javascript">
			function validate_searchform1()
			{
				document.form1.Submit_Search.value='Submit_Search1';
				document.form1.submit();
				return true;
			}
		</script>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-35011799-1']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	</head>
	<body>
		<div id="main">
			<div class="head">
				<div style="width:40%; float:left">
					<a href="index.php">
						<img src="" width="165" height="123" border="0" style="vertical-align:middle" />
					</a>
					<span style="height:60px;">&nbsp;&nbsp;&nbsp;<strong>Administrative Login</strong></span>
					<div align="right" style="padding-right:90px">
<?php 
echo <<< NAM
Welcome Home: <strong><em><u>$authInfo->{'admin_name'}</u></em></strong>
NAM;
?>
					</div>
				</div>
				<div align="center" class="view_client">
					<table width="20%" border="0" align="right" cellpadding="0" cellspacing="4">
						<tr>
							<td>
								<a href="admin-logout.php">Logout</a>
							</td>
						</tr>
						<tr>
							<td>
								<a href="admin-main.php">Home</a>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
					</table>
				</div>
			</div>
			<div style="clear:both"></div>
<?php
include($_SERVER['DOCUMENT_ROOT'] . "/inner_page_menu.php");
?>
			<form name="form1" id="form1" method="post" action="">
				<div class="admin-content1">
					<div style="float:left; width:30%; padding:15px">
						CID Search &nbsp;&nbsp;<input name="CID" type="text" id="CID" value="<?php echo $_REQUEST['CID'];?>" />
					</div>
					<div style="float:left; width:50%; padding:15px">
						<table width="80%" border="0" cellspacing="3" cellpadding="3">
							<tr>
								<td>Name</td>
								<td><input name="Name" type="text" id="Name" value="<?php echo $_REQUEST['Name'];?>" /></td>
							</tr>
							<tr>
								<td>Citation Number</td>
								<td><input name="Citation_Number" type="text" id="Citation_Number" value="<?php echo $_REQUEST['Citation_Number'];?>" /></td>
							</tr>
							<tr>
								<td>Case Number</td>
								<td><input name="Case_Number" type="text" id="Case_Number" value="<?php echo $_REQUEST['Case_Number'];?>" /></td>
							</tr>
							<tr>
								<td>Violation Date</td>
								<td><input name="Violation_Date" type="text" id="Violation_Date" value="<?php echo $_REQUEST['Violation_Date'];?>" /></td>
							</tr>
							<tr>
								<td>Court Date</td>
								<td><input name="Court_Date" type="text" id="Court_Date" value="<?php echo $_REQUEST['Court_Date'];?>" /></td>
							</tr>
							<tr>
								<td>Charges</td>
								<td><input name="Charges" type="text" id="Charges" value="<?php echo $_REQUEST['Charges'];?>" /></td>
							</tr>
							<tr>
								<td colspan="2" align="center">
									<img src="/css/images/but_submit.png" width="118" height="33" border="0" onclick="return validate_searchform1();" />
									<input type="hidden" name="Submit_Search" id="Submit_Search" />
									&nbsp;&nbsp;&nbsp;<a href="admin-main-allresult.php"><strong style="text-decoration:underline;">View All</strong></a> 
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
			<div style="clear:both"></div>
			<h2 style="padding-left:30px;"><strong>Search Results:</strong></h2>
			<div class="admin-content1">
				<table width="100%" border="0" cellspacing="2" cellpadding="2" >
					<tr style="color:#FFF;" align="center">
						<td width="10%" bgcolor="#366092">Account CID</td>
						<td width="6%" align="left" bgcolor="#366092">Name</td>
						<td width="26%" bgcolor="#366092">Address</td>
						<td width="17%" bgcolor="#366092">Violations</td>
						<td width="14%"></td>
						<td width="27%" style="color:#000">Export to <strong style="color:red">XLS</strong> File </td>
					</tr>
<?php
if (isset($_REQUEST['Submit_Search']) || ($_REQUEST['Submit_Search']=='Submit_Search1')) {  
	$CID=$_REQUEST['CID'];
	$Name=$_REQUEST['Name'];
	$Citation_Number=$_REQUEST['Citation_Number'];
	$Case_Number=$_REQUEST['Case_Number'];
	$Violation_Date=$_REQUEST['Violation_Date'];
	$Court_Date=$_REQUEST['Court_Date'];
	$Charges=$_REQUEST['Charges'];

	$search_query= "select * from citation where id!='' ";
	if (!empty($Name)) {
		$search_query .= " AND (First_Name ='$Name' or Last_Name='$Name')";
	}
	if (!empty($Citation_Number)) {
		$search_query .= " AND Citation_Number='$Citation_Number' ";
	}
	if (!empty($Case_Number)) {
		$search_query .= " AND Case_Number='$Case_Number' ";
	}
	if (!empty($Violation_Date)) {
		$search_query .= " AND Violation_Date='$Violation_Date' ";
	}
	if (!empty($Charges)) {
		$search_query .= " AND Charges='$Charges' ";
	}
/*
First_Name ='$Name' or Last_Name='$Name' or  CID='$CID' or Citation_Number='$Citation_Number' or Case_Number='$Case_Number' or Violation_Date='$Violation_Date' or Charges='$Charges'
";
*/
				  
	$search_query .=" ORDER BY id  DESC "; 	
	$rowsPerPage=10;

	$result_select1=mysql_query($search_query) or die(mysql_error());
	$result_select1_num1=mysql_num_rows($result_select1);
	$maxPage = ceil($result_select1_num1/$rowsPerPage); 
	   
	$pager = new PS_Pagination($conn, $search_query, $rowsPerPage, 5, "Submit_Search=Submit_Search1&CID=$CID&Name=$Name&Citation_Number=$Citation_Number&Case_Number=$Case_Number&Violation_Date=$Violation_Date&Court_Date=$Court_Date&Charges=$Charges");
	$rs = $pager->paginate();
	if($rs > 0) { 
		$i=0;
		while($search_result_row=mysql_fetch_assoc($rs)) {
			if ($i % 2 == 0) {
?>
<tr bgcolor="#d9d9d9">
	<td><?php echo stripslashes($search_result_row['CID']);?>&nbsp;</td>
	<td><?php echo stripslashes($search_result_row['First_Name']);?>&nbsp;<?php echo stripslashes($search_result_row['Last_Name']);?></td>
	<td><?php echo stripslashes($search_result_row['Address']);?>&nbsp;</td>
	<td><?php echo stripslashes($search_result_row['Violation_Date']);?>&nbsp;</td>
	<td>
		<a href="view_citation_details.php?view_id=<?=$search_result_row['id']?>">
			<strong style="text-decoration:underline;">View Details</strong>
		</a>
	</td>
	<td>&nbsp;</td>
</tr>
<?php 
			}
			else {
?>
<tr bgcolor="#dce6f2">
	<td><?php echo stripslashes($search_result_row['CID']);?>&nbsp;</td>
	<td><?php echo stripslashes($search_result_row['First_Name']);?>&nbsp;<?php echo stripslashes($search_result_row['Last_Name']);?></td>
	<td><?php echo stripslashes($search_result_row['Address']);?>&nbsp;</td>
	<td><?php echo stripslashes($search_result_row['Violation_Date']);?>&nbsp;</td>
	<td>
		<a href="view_citation_details.php?view_id=<?=$search_result_row['id']?>">
			<strong style="text-decoration:underline;">View Details</strong>
		</a>
	</td>
	<td>&nbsp;</td>
</tr>
<?php
			}
			$i++;
		}
	}
}
?>
<?php 
if($rs == '0') {
?>
<tr bgcolor="#dce6f2">
	<td colspan="6"><font color="#FF0000">Citation List Not Available Please Modify Your Search</font></td>
</tr>
<?php
} 
?>
<tr>
	<td colspan="5" align="center" valign="middle">
<?php
if($maxPage > 1) {
?>
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="35%" style="border-right:none;">&nbsp;</td>
			<td width="40%" align="center" valign="middle" style="border-right:none;">
				<?php echo $pager->renderFirst('First');?>&nbsp;
				<?php echo $pager->renderPrev('Previous');?>&nbsp;
				<?php echo $pager->renderNav('<span>', '</span>');?>&nbsp;
				<?php echo $pager->renderNext('Next'); ?>&nbsp;
				<?php echo $pager->renderLast('Last'); ?>
			</td>
			<td width="25%" style="border-right:none;">&nbsp;</td>
		</tr>
	</table>
<?php
}
?>
						</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</div>
			<p>&nbsp;</p>
		</div>
	</body>
</html>
