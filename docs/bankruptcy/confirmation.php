<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/includes/dbinfo-pdo-bankruptcy.php');

if (empty($_SESSION['ReceiptNumber'])) {
	header("Location: index.php");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/css/print.css" media="print" />
	</head>
	<body>
		<div id="main">
			<div class="head">
				<div style="width: 40%; float: left" >
					<a href="/index.php"><img src="" width="165" height="123" border="0" style="vertical-align: middle;" /></a>
				</div>                
				<div align="right" style="width: 50%; float: left">
					<u><a href="/index.php">Home</a></u>
				</div>
			</div>
			<div class="login">&nbsp;</div>
			<div style="clear:both"></div>
			<div class="search" align="center">
				<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border: 1px solid #999">
					<tr bgcolor="#366092" style="color: #FFF;" align="center">
						<td>Name</td>
						<td>Case Number</td>
						<td>Trustee Office</td>
						<td>Payment Total</td>
						<td>Receipt Number</td>
					</tr>
					<tr>
						<td><?php echo $_SESSION['FullName']; ?></td>
						<td><?php echo $_SESSION['CaseNumber']; ?></td>
						<td><?php echo $_SESSION['TrusteeName']; ?></td>
						<td>$<?php echo $_SESSION['Total']; ?></td>
						<td><?php echo $_SESSION['ReceiptNumber']; ?></td>
					</tr>
					<tr>
						<td colspan="5">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div style="margin: 20px 120px; padding: 10px; border: 1px solid DarkGreen; background-color: LightGreen;">
				<h2 style="color: DarkGreen">
		
					Thank You! Your payment has been successfully processed. Please <a id="print" href="javascript:window.print()">print this page</a> for your records. If you provided an email address you should receive an email receipt within a few minutes.<br/><br/>
					Date of payment: <?php echo date('m/d/y g:i A'); ?>
				</h2>
			</div>
			<div align="center" style=" padding: 80px;">All rights reserved 2013. <a href="/disclaimer.php">Disclaimer</a></div>
		</div>

		<div id="print-receipt">
			<h1>&lt;Business Name&gt;</h1>

			<h2>Chapter 13 Payment Receipt</h2>

			<hr />

			<div class="bankruptcy-details">
				<h3>Chapter 13 Details</h3>
				<span>Debtor Name: <?php echo $_SESSION['FullName']; ?></span><br/>
				<span>Codebtor Name: <?php echo (!empty($_SESSION['CodebtorFullName']) ? $_SESSION['CodebtorFullName'] : 'N/A'); ?></span><br/>
				<span>Case Number: <?php echo $_SESSION['CaseNumber']; ?></span><br/>
				<span>Trustee Office: <?php echo $_SESSION['TrusteeName']; ?></span><br/>
				<span>Suggested Payment: $<?php echo $_SESSION['SuggestedAmount']; ?></span><br/>
				<span>Suggested Frequency: <?php echo $_SESSION['SuggestedFrequency']; ?></span><br/>
			</div>

			<br /><hr />

			<div class="billing-details">
				<h3>Billing Details</h3>
				<span>Date: <?php echo date('m/d/y g:i A'); ?></span><br/>
				<span>Receipt Number: <?php echo $_SESSION['ReceiptNumber']; ?></span><br/>
				<span>Name: <?php echo $_SESSION['BillingName']; ?></span><br/>
				<span>Phone Number: <?php echo $_SESSION['PhoneNumber']; ?></span><br/>
				<span>Email Address: <?php echo (!empty($_SESSION['Email']) ? $_SESSION['Email'] : 'N/A'); ?></span><br/>
				<span>Address: <?php echo $_SESSION['BillingAddress']; ?></span><br/>
				<span>City: <?php echo $_SESSION['BillingCity']; ?></span><br/>
				<span>State: <?php echo $_SESSION['BillingState']; ?></span><br/>
				<span>Zip: <?php echo $_SESSION['BillingZip']; ?></span><br/>
				<span>Payment Amount: $<?php echo $_SESSION['PaymentAmount']; ?></span><br/>
				<span>Processing Fee: $<?php echo $_SESSION['ProcessingFee']; ?></span><br/>
				<span>Payment Total: $<?php echo $_SESSION['Total']; ?></span><br/>
				<span>Card Number: *<?php echo $_SESSION['LastFourCardDigits']; ?></span><br/>
			</div>

			<br /><hr />
		</div>
	</body>
</html>

<?php

session_unset(); //Clear the variables
session_destroy(); // Actually destroy the session

?>
