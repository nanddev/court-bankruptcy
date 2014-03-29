<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/includes/dbinfo-pdo.php');
include($_SERVER['DOCUMENT_ROOT'] . '/includes/TransFirst.php');

$tf = new TransFirst();
$response = $_SESSION['response'];
$success = $tf->isSuccessful($response);
$data = $_SESSION['data'];
$citationInfo = $_SESSION['citationInfo'];
$receiptNumber = $_SESSION['receiptNumber'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css"/>
	</head>
	<body>
		<div id="main">
			<div class="head">
				<div style="width: 40%; float: left">
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
						<td>Citation Number</td>
						<td>Violation Date</td>
						<td>Court Date</td>
						<td>Charges</td>
						<td>Fine Amount</td>
					</tr>

<?php

if (isset($citationInfo['Citations'])) {
	foreach ($citationInfo['Citations'] as $citation) {
		echo '<tr>';
		echo "<td>${citation['CitationNumber']}</td>";
		echo "<td>${citation['ViolationDate']}</td>";
		echo "<td>${citation['CourtDate']}</td>";
		echo "<td>${citation['Charges']}</td>";
		echo "<td>$${citation['FineAmount']}</td>";
		echo '</tr>';
	}
}

?>

					<tr>
						<td colspan="5">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">
<?php

echo <<< AAA
	${citationInfo['FirstName']} ${citationInfo['LastName']}<br/>
	${citationInfo['Address']}<br/>
	${citationInfo['City']}, ${citationInfo['State']} ${citationInfo['Zip']}
AAA;

?>
						</td>
						<td colspan="3">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div style=" padding-left: 140px; padding-top: 10px; padding-bottom: 15px;">
<?php

	if ($success) {
		echo '<h2 style="color: #FF0000">Thank You! Your payment has been successfully processed.<br/>
		Please print this screen for your records. Date of payment: ' . date('m/d/Y') . '</h2>';
	}

?>
			</div>
			<div align="center">
				<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border: 1px solid #999">
					<tr>
						<td width="49%">Fine Amount: $<?php echo $citationInfo['FineTotal'];?><br />
						Processing Fee: $<?php echo $citationInfo['ProcessingTotal'];?><br/>
						<em><strong style="color:#FF0000">Pay This Amount:</strong></em> $<?php echo $citationInfo['Total'] ;?></td>
						<td width="31%" align="left"><strong>Payment Card Billing Address</strong></td>
						<td width="20%"><img src="/css/images/Picture2.png" width="170" height="30" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td rowspan="3"> 
							<table width="100%" border="0" cellspacing="3" cellpadding="3">
								<tr>
									<td>Name</td>
									<td><?php echo $data['name']; ?></td>
								</tr>
								<tr>
									<td>Address</td>
									<td><?php echo $data['address1']; ?></td>
								</tr>
								<tr>
									<td>City</td>
									<td><?php echo $data['city']; ?></td>
								</tr>
								<tr>
									<td>State</td>
									<td><?php echo $data['state']; ?></td>
								</tr>
								<tr>
									<td>Zip</td>
									<td><?php echo $data['zipcode']; ?></td>
								</tr>
								<tr>
									<td>Card Number</td>
									<td><?php echo $data['ccNum']; ?></td>
								</tr>
								<tr>
									<td>Exp Date</td>
									<td><?php echo $data['expirationMonth']; ?>/<?php echo $data['expirationYear']; ?></td>
								</tr>
								<tr>
									<td>CVV Code</td>
									<td>***</td>
								</tr>
								<tr>
									<td colspan="2" align="center"></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="3">
<?php
	if ($success) 
		echo "<h2><strong>Receipt Number: ".$receiptNumber."</strong></h2>";
?>		
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div align="center" style=" padding: 80px;">All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a></div>
		</div>
	</body>
</html>
