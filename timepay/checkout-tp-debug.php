<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/includes/dbinfo-pdo.php');
include($_SERVER['DOCUMENT_ROOT'] . '/includes/TransFirst.php');

// Session id is used to lookup citations the user is going to pay for 
if (empty($_SESSION['sesid'])) {
	$_SESSION['sesid'] = session_id();
	$sessionId = $_SESSION['sesid'];
}
else {
	$sessionId = $_SESSION['sesid'];
}

$valid = true;
if (isset($_POST['minimumOrCustom']) && $_POST['minimumOrCustom'] == 'custom') {

	$timepayInfo = $_SESSION['timepayInfo'];

	if (!is_numeric($_POST['customPayment'])) {
		$customPaymentErrorMessage = 'Please enter a valid number for your custom payment.';
		$valid = false;
	}
	else if ($_POST['customPayment'] < $timepayInfo['FineTotal']) {
		$customPaymentErrorMessage = 'To pay less than your minimum payment, please call us at 1-877-689-5144.';
		$valid = false;

		// Reset to pay minimum
		$_POST['minimumOrCustom'] = "minimum";
		$_POST['customPayment'] = '';
		$_POST['ProcessingFee'] = '';
	}
	else {
		// This is an ok amount, so update the totals
		$timepayInfo['FineTotal'] = number_format($_POST['customPayment'], 2);
		$timepayInfo['ProcessingTotal'] = number_format($_POST['ProcessingFee'], 2);
		$_POST['chargetotal'] = $_POST['customPayment'] + $_POST['ProcessingFee'];
		$timepayInfo['Total'] = number_format($_POST['chargetotal'], 2);
		$_SESSION['timepayInfo'] = $timepayInfo;
	}
}

// Process the payment upon submission of the form (at the bottom of this file)
if (isset($_POST['process']) && $_POST['process'] == 'payment' && $valid) {

	try {
		// Get out the citation info that was saved before form was submitted
		$timepayInfo = $_SESSION['timepayInfo'];

		$debug = true;
		$success = false;
		$receiptNumber = strtoupper('CF-' . md5(uniqid(rand(), true)));
		$emails = "jarrett.andrew@gmail.com, jnthomas8@gmail.com";
		$debugEmails = "jarrett.andrew@gmail.com, jnthomas8@gmail.com";
		$headers = "From: Demo Email <demoemail@nanddevelopment.com>\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";						
		if (!$debug) {
			$headers .= 'Bcc: ' . $emails . "\r\n";
		}
		else {
			$headers .= 'Bcc: ' . $debugEmails . "\r\n";
		}

		// Send through the processing fee first
		$wsdl = "https://ws.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl";
		$client = new SoapClient($wsdl, array('trace'=>1, 'exceptions'=>1));  

		$data = array();
		$expiration = explode('/', $_POST['exp_date']);
		$expirationMonth = $expiration[0];
		$expirationYear = $expiration[1];
		$data['merchantId'] = 'xxxxxxxx'; // Business Merchant ID
		$data['key'] = 'xxxxxxxx'; // Business Reg key
		if ($debug) {
			// Debug information
			//$data['merchantId'] = '7777777914'; // Test Merchant ID
			//$data['key'] = 'DD8ZARBGKWSWZ3XK'; // Test Reg Key
			$data['ccNum'] = '4111111111111111'; // Visa Test card number
			//$data['ccNum'] = '5499740000000057'; // MasterCard Test card number
			$data['cvv'] = '123'; // Any cvv code
			$data['expiration'] = '1306'; // Any future expiration date
			$data['expirationMonth'] = '06'; // Displayed on confirmation page
			$data['expirationYear'] = '13'; // Displayed on confirmation page
			$data['total'] = '050'; // "Request Amount" Leading zero is required. This is in *pennies*
		}
		else {
			$data['ccNum'] = $_POST['card_number'];
			$data['cvv'] = $_POST["cvv"];
			$data['expiration'] = $expirationYear . $expirationMonth;
			$data['expirationMonth'] = $expirationMonth; // Displayed on confirmation page
			$data['expirationYear'] = $expirationYear; // Displayed on confirmation page
			$data['total'] = '0' . (str_replace(',', '', $timepayInfo['ProcessingTotal']) * 100); // "Request Amount" Leading zero is required. This is in *pennies*
		}
		$data['name'] = $_POST['name'];
		$data['phoneNumber'] = $_POST['phone'];
		$data['email'] = $_POST['email'];
		$data['address1'] = $_POST['address'];
		$data['city'] = $_POST['city'];
		$data['state'] = $_POST['state'];
		$data['zipcode'] = $_POST['zip'];
		$data['receiptNumber'] = $receiptNumber;

		$tf = new TransFirst();
		$request = $tf->generateTransactionRequest($data);
		$response = $client->SendTran($request);
		$success = $tf->isSuccessful($response);

		//echo '<pre>';
		//echo var_dump($response, $success);
		//echo '</pre>';
		//exit;

		// If processing fee was successful, let's charge the fine amount
		if ($success || $debug) {

			// Record the timepay convenience fee
			$sql = "insert into orders " .
				"(name, email, phone, address, city, state, zip, contract_number, case_number, violation_date, court_date, charges, processing_fee, chargetotal, ws_response, receipt_number)" .
				" values " .
				"(:name, :email, :phone, :address1, :city, :state, :zipcode, :contract_number, :case_num, :violation_date, :court_date, :charges, :processing_fee, :total, :ws_response, :receipt_number)";
			$statement = $db->prepare($sql);

			try {

				if (!$debug) {
					$statement->execute(array(
						':name' => $data['name'],
						':email' => $data['email'],
						':phone' => $data['phoneNumber'],
						':address1' => $data['address1'],
						':city' => $data['city'],
						':state' => $data['state'],
						':zipcode' => $data['zipcode'],
						':contract_number' => $timepay['Contract_Num'],
						':case_num' => $timepay['CaseNumber'],
						':violation_date' => $timepay['ViolationDate'],
						':court_date' => $timepay['CourtDate'],
						':charges' => $timepay['Charges'],
						':processing_fee' => $timepayInfo['ProcessingTotal'],
						':total' => number_format(($data['total'] / 100), 2),
						':ws_response' => serialize($response),
						':receipt_number' => $receiptNumber
					));
				}

			} catch (PDOException $e) {
				echo $e->getMessage();
				exit;
			}

			// Process each timepay being paid for
			foreach ($timepayInfo['timepays'] as $timepay) {

				// Code to lookup Jurisdiction's merchant id
				$courtId = $timepay['CID'];
				$jurisdiction = $timepay['Jurisdiction'];

				$sql = 'select merchant_id, reg_key, email from jurisdictions where cid = :courtId and jurisdiction = :jurisdiction';
				$statement = $db->prepare($sql);
				$statement->execute(array(':courtId'=>$courtId, ':jurisdiction'=>$jurisdiction));
				$merchant = $statement->fetch();

				if (!$debug) {
					$data['merchantId'] = $merchant['merchant_id'];// Jurisdiction's Merchant ID
					$data['key'] = $merchant['reg_key']; // Jurisdiction's Reg key
					$data['total'] = '0' . (str_replace(',', '', $timepayInfo['FineTotal']) * 100); // "Request Amount" Leading zero is required. This is in *pennies*
				}

				$request = $tf->generateTransactionRequest($data);
				$response = $client->SendTran($request);
				$success = $tf->isSuccessful($response);

				// If fine amount was successful, then send out the email receipt
				if ($success || $debug) {

					// Record the timepay payment
					$sql = "insert into orders " .
						"(name, email, phone, address, city, state, zip, contract_number, case_number, violation_date, court_date, charges, fine_amount, chargetotal, ws_response, receipt_number)" .
						" values " .
						"(:name, :email, :phone, :address1, :city, :state, :zipcode, :contract_number, :case_num, :violation_date, :court_date, :charges, :fine_amount, :total, :ws_response, :receipt_number)";
					$statement = $db->prepare($sql);

					try {

						if (!$debug) {
							$statement->execute(array(
								':name' => $data['name'],
								':email' => $data['email'],
								':phone' => $data['phoneNumber'],
								':address1' => $data['address1'],
								':city' => $data['city'],
								':state' => $data['state'],
								':zipcode' => $data['zipcode'],
								':contract_number' => $timepay['Contract_Num'],
								':case_num' => $timepay['CaseNumber'],
								':violation_date' => $timepay['ViolationDate'],
								':court_date' => $timepay['CourtDate'],
								':charges' => $timepay['Charges'],
								':fine_amount' => $timepayInfo['FineTotal'],
								':total' => number_format(($data['total'] / 100), 2),
								':ws_response' => serialize($response),
								':receipt_number' => $receiptNumber
							));
						}

					} catch (PDOException $e) {
						echo $e->getMessage();
						exit;
					}

					// Lookup the right email address for the court
					$courtEmail = $merchant['email'];

					if ($debug) {
						$courtEmail = $debugEmails;
					}

					$courtSubject = 'Confirmation of Timepay Payment';
					$message2court='<table width="650">
					  <tr>
					    <td align="left" valign="middle"><table width="100%" >
					      <tr>
						<td>&nbsp;</td>
						<td align="center" valign="middle"><h3>Timepay Contract Payment Details</h3> </td>
						<td>&nbsp;</td>
					      </tr>
					      <tr>
						<td>&nbsp;</td>
						<td><table width="100%" cellpadding="3" cellspacing="3" >
						  <tr>
						    <td width="28%" align="right" valign="middle">&nbsp;</td>
						    <td width="72%" align="left" valign="middle">&nbsp;</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">Offender\'s Name: </td>
						    <td align="left" valign="middle">&nbsp;'.$timepayInfo['FirstName'] . ' ' . $timepayInfo['LastName'].'</td>
						  </tr>
						  <tr>
						    <td align="right" valign="top">Offender\'s Address: </td>
						    <td align="left" valign="top">&nbsp;'.
							$timepayInfo['Address']. '<br/>&nbsp;' .
							$timepayInfo['City'] . ', ' . $timepayInfo['State'] . ' ' . $timepayInfo['Zip'].
						    '</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">Contract Number: </td>
						    <td align="left" valign="middle">&nbsp;'.$timepay['Contract_Num'].'</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">Charges: </td>
						    <td align="left" valign="middle">&nbsp;'.$timepay['Charges'].'</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">Payment Amount: </td>
						    <td align="left" valign="middle">&nbsp;$'.$timepayInfo['FineTotal'].'</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">VJPID : </td>
						    <td align="left" valign="middle">&nbsp;'.$timepayInfo['VJPID'].'</td>
						  </tr>
						  <tr>
						    <td align="right" valign="middle">Date paid : </td>
						    <td align="left" valign="middle">&nbsp;'.date('m/d/Y').'</td>
						  </tr>
						</table></td>
						<td>&nbsp;</td>
					      </tr>
					    </table>
					</td>
					</tr>
					</table>';


					// Send Personal and Court receipts for this one citation
					$sendmail2Court = mail($courtEmail, $courtSubject, $message2court, $headers);
					//echo '<pre>';
					//echo var_dump($courtEmail, $courtSubject, $message2court, $headers, $sendmail2Court);
					//echo '</pre>';
					//exit;
				}
			}
		}

		if ($success || $debug) {
			// Everythin processed correctly, so send off the customer's personal receipt
			$customerName = stripslashes($data['name']);
			$customerEmail = stripslashes($data['email']);

			$customerSubject = 'Personal Receipt - Timepay Contract Payment Details';

			$timepayList = '';
			foreach ($timepayInfo['timepays'] as $timepay) {
				$timepayList .= '<tr>';
				$timepayList .= "<td>${timepay['Contract_Num']}</td>";
				$timepayList .= "<td><em>${timepay['Charges']}</em></td>";
				$timepayList .= "<td align=\"right\">$${timepay['FineAmount']}</td>";
				$timepayList .= '</tr>';
			}

			$message2Customer = '<table width="650" >
		  <tr>
		    <td align="left" valign="middle"><table width="100%" >
		      <tr>
			<td>&nbsp;</td>
			<td align="center" valign="middle"><h3>Timepay Payment Details</h3></td>
			<td>&nbsp;</td>
		      </tr>
		      <tr>
			<td>&nbsp;</td>
			<td><table width="100%" cellpadding="3" cellspacing="3">
			  <tr>
			    <td align="right" valign="middle">Receipt Number: </td>
			    <td align="left" valign="middle">'.stripslashes($receiptNumber).'</td>
			  </tr>
			  <tr>
			    <td align="right" valign="middle">Name: </td>
			    <td align="left" valign="middle">'.stripslashes($data['name']).'</td>
			  </tr>
			  <tr>
			    <td align="right" valign="middle">Email: </td>
			    <td align="left" valign="middle">'.stripslashes($data['email']).'</td>
			  </tr>
			  <tr>
			    <td align="right" valign="middle">Phone: </td>
			    <td align="left" valign="middle">'.stripslashes($data['phoneNumber']).'</td>
			  </tr>		   
			  <tr>
			    <td align="right" valign="top">Address: </td>
			    <td align="left" valign="top">'
				.stripslashes($data['address1']).'<br/>'
				.stripslashes($data['city'] . ', ' . $data['state'] . ' ' . $data['zipcode'])
			  .'</td>
			  </tr>
			  <tr>
			    <td colspan="2">
			      <table>
				<tr>
				  <td align="middle"><strong>Timepay Contract Number</strong></td>
				  <td align="middle"><strong>Charges</strong></td>
				  <td align="middle"><strong>Minimum Payment</strong></td>
				</tr>' . $timepayList . '<tr>
				  <td colspan="2" align="right">Your payment: </td>
				  <td align="right">$'.stripslashes($timepayInfo['FineTotal']).'</td>
				</tr>
				<tr>
				  <td colspan="2" align="right">Processing Fee: </td>
				  <td align="right">$'.stripslashes($timepayInfo['ProcessingTotal']).'</td>
				</tr>
				<tr>
				  <td colspan="2" align="right">Total Amount: </td>
				  <td align="right"><strong>$'.number_format(stripslashes($timepayInfo['Total']), 2).'</strong></td>
				</tr>
			      </table>
			    </td>
			  </tr>
			</table>
		       </td>
			<td>&nbsp;</td>
		      </tr>
		    </table></td>
		  </tr>
		</table>';

			// Send Personal receipt
			$sendmail2Customer = mail($customerEmail, $customerSubject, $message2Customer, $headers);
			//echo '<pre>';
			//echo var_dump($customerEmail, $customerSubject, $message2Customer, $headers, $sendmail2Customer);
			//echo '</pre>';
			//exit;
		}

		// There was an error
		if (!$success && !$debug) {
			$paymentErrorMessage = "<h2 style='color: red;'>There was an error processing your payment. Please try again or call our toll free support at 1-877-689-5144</h2>";
		}
		else {
			// Save relevant info for the confirmation page
			$data['ccNum'] = "************".substr($data['ccNum'], strlen($data['ccNum'])-4, 4);
			$_SESSION['data'] = $data;
			$_SESSION['response'] = $response;
			$_SESSION['timepayInfo2'] = $timepayInfo;
			$_SESSION['receiptNumber'] = $receiptNumber;

			// We're done processing the order. Show the confirmation page.
			header("Location: confirmation-tp.php");
		}
	}
	catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<style type="text/css">
			.style1 {color: #FF0000}
			.style3 {color: #C00000; font-size: 14px; }
		</style>
		<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#sameAsBilling').click(function() {
					if ($('#sameAsBilling').val() == 'false') {
						$('#billing-name').val($('#first').text() + ' ' + $('#last').text());
						$('#billing-address').val($('#address').text());
						$('#billing-city').val($('#city').text());
						$('#billing-state option').each(function() {
							state = $(this).text();
							abbrev = $(this).val();
							if (state == $('#state').text()) {
								$('#billing-state').val(abbrev);
								$('#billing-state').attr({'value': abbrev});
								$(this).prop('selected', true);
							}
						});
						$('#billing-zip').val($('#zip').text());
						$('#sameAsBilling').val('true');
					}
					else {
						$('#billing-name').val('');
						$('#billing-address').val('');
						$('#billing-city').val('');
						$('#billing-state').val('');
						$('#billing-state').attr({'value': ''});
						$('#billing-state option:selected').prop('selected', false);
						$('#billing-zip').val('');
						$('#sameAsBilling').val('false');
					}
				});

				$('#billing-state').change(function() {
					$('#billing-state').attr({'value': $('#billing-state option:selected').val()});
				});

				$('#submit').click(function(e) {

                                        valid = true;
                                        errorMessage = "";
                                        $('#submit').hide();
                                        $('#processing').show();

                                        if ($('#billing-name').val() == "") {
                                                errorMessage += "Please enter your full name.\n";
                                                valid = false;
                                        }
                                        if ($('#phone').val() == "") {
                                                errorMessage += "Please enter your phone number.\n";
                                                valid = false;
                                        }
                                        if ($('#billing-address').val() == "") {
                                                errorMessage += "Please enter your address.\n";
                                                valid = false;
                                        }
                                        if ($('#billing-city').val() == "") {
                                                errorMessage += "Please enter your city name.\n";
                                                valid = false;
                                        }
                                        if ($('#billing-state').val() == "") {
                                                errorMessage += "Please enter your state name.\n";
                                                valid = false;
                                        }
                                        if ($('#billing-zip').val() == "") {
                                                errorMessage += "Please enter your zip code.\n";
                                                valid = false;
                                        }
                                        if ($('#card_number').val() == "") {
                                                errorMessage += "Please enter your card number.\n";
                                                valid = false;
                                        }
                                        if ($('#exp_date').val() == "") {
                                                errorMessage += "Please enter your expiration date.\n";
                                                valid = false;
                                        }
                                        if ($('#cvv').val() == "") {
                                                errorMessage += "Please enter your cvv code.\n";
                                                valid = false;
                                        }
                                        if (!$('#terms_condition').is(':checked')) {
                                                errorMessage += "Please agree to the terms and conditions.\n";
                                                valid = false;
                                        }

                                        if (valid) {
                                                $('#checkout').submit();
                                        }
                                        else {
                                                $('#submit').show();
                                                $('#processing').hide();
                                                alert(errorMessage);
                                        }
                                        e.preventDefault;
                                        return false;
                                });
			});

			function updateTotal() {
				var processingFee = parseFloat(document.getElementById('processingFee').innerHTML);
				var payment = parseFloat(document.checkout.customPayment.value);
				var total = document.getElementById('totalPayment');
				var updatedProcessingFee = document.getElementById('processingFee');
					
				if (!isNaN(payment)) {
					if (payment < 100) { 
						updatedProcessingFee.innerHTML = (4 + (Math.ceil((4 + payment) * 3.8) / 100)).toFixed(2);
					} else {
						updatedProcessingFee.innerHTML = (8 + (Math.ceil((8 + payment) * 4.0) / 100)).toFixed(2);
					}

					var processingFeeField = document.getElementById('processingFeeField');
					processingFeeField.value = updatedProcessingFee.innerHTML;

					total.innerHTML = "$" + (payment + parseFloat(updatedProcessingFee.innerHTML)).toFixed(2);
					
				}
			}
		</script>
	</head>
	<body>
		<form name="checkout" id="checkout" method="post" action="">
			<div id="main">
				<div class="head">
					<div style="width:40%; float:left" >
						<a href="../index.php">
							<img src="" width="165" height="123" border="0" style="vertical-align: middle;" />
 						</a>
					</div>                
					<div align="right" style="width:50%; float:left">
						<u><a href="../index.php">Home</a></u>
					</div>
				</div>
				<div class="login">&nbsp;</div>
				<div style="clear:both"></div>
				<div class="search" align="center">
				<table width="90%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="18%" align="center" valign="middle">&nbsp;</td>
						<td width="82%">
							<table width="90%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
								<tr bgcolor="#366092" style="color:#FFF;" align="center">
									<td>Contract Number</td>
									<td>Violation Date</td>
									<td>Jurisdiction</td>
									<td>Charges</td>
									<td>Minimum Payment</td>
								</tr>
<?php
$timepayInfo = array();
$timepayInfo['timepays'] = array();
try {
	$timepayToPay = $db->query("select * from citation_cart_temp where ct_session_id='$sessionId'");

	// Setup all the citations that the user wants to pay
	while ($cartRow = $timepayToPay->fetch(PDO::FETCH_BOTH)) {
		// Select the citation information
		$timepayId = $cartRow['product_id'];
		
		try {
			$timepayResult = $db->query("select * from citation_test where id='$timepayId'");
		} catch (PDOException $e) {
			echo $e->getMessage();
		}

		$timepayRow = $timepayResult->fetch(PDO::FETCH_BOTH);

		// Calculate the processing fee
		$FineAmount = str_replace(',', '', $timepayRow['Min_Payment']);
		if($FineAmount < 100) {
			$ProcessingFee = 4.0 + (ceil((4.0+$FineAmount)*003.8) / 100);
		} else {
			$ProcessingFee = 8.0 + (ceil((8.0+$FineAmount)*004.0) / 100);
		}

		// Setup info related to this individual citation
		$timepay = array(
			"CID" => $timepayRow['CID'],
			"Jurisdiction" => $timepayRow['Jurisdiction'],
			"ViolationDate" => $timepayRow['Violation_Date'],
			"Contract_Num" => $timepayRow['Contract_Num'],
			"CaseNumber" => $timepayRow['Case_Number'],
			"Charges" => $timepayRow['Charges'],
			"FineAmount" => number_format($FineAmount, 2),
			"ProcessingFee" => number_format($ProcessingFee, 2)
		);
		array_push($timepayInfo['timepays'], $timepay);

		// Setup info related to all citations. I'm assuming that the person's
		// VJPID, name, and address will always be the same.
		$timepayInfo['VJPID'] = $timepayRow['VJPID'];
		$timepayInfo['FirstName'] = $timepayRow['First_Name'];
		$timepayInfo['LastName'] = $timepayRow['Last_Name'];
		$timepayInfo['Address'] = $timepayRow['Address'];
		$timepayInfo['City'] = $timepayRow['City'];
		$timepayInfo['State'] = $timepayRow['State'];
		$timepayInfo['Zip'] = $timepayRow['Zip'];
		$timepayInfo['ProcessingTotal'] = $timepayInfo['ProcessingTotal'] + $ProcessingFee;
		$timepayInfo['FineTotal'] = $timepayInfo['FineTotal'] + $FineAmount;
		$timepayInfo['Total'] = $timepayInfo['FineTotal'] + $timepayInfo['ProcessingTotal'];

		$displayInfo = array(
			stripslashes($timepay['Contract_Num']),
			stripslashes($timepay['ViolationDate']),
			stripslashes($timepay['Jurisdiction']),
			stripslashes($timepay['Charges']),
			'$' . stripslashes($timepay['FineAmount'])
		);
		echo '<tr>';
		foreach ($displayInfo as $info) {
			echo "<td>$info</td>";
		}
		echo '</tr>';
	}

	$timepayInfo['ProcessingTotal'] = number_format($timepayInfo['ProcessingTotal'], 2);
	$timepayInfo['FineTotal'] = number_format($timepayInfo['FineTotal'], 2);
	$timepayInfo['Total'] = number_format($timepayInfo['Total'], 2);

	// Save the $timepayInfo variable to the session so we have this info when the payment is processed
	$_SESSION['timepayInfo'] = $timepayInfo;

} catch (PDOException $e) {
	echo $e->getMessage();
}
?>
								<tr>
									<td colspan="5">&nbsp;</td>
								</tr>
								<tr>
						<td colspan="2">
							<span id="last"><?php echo stripslashes($timepayInfo['LastName']); ?></span>, <span id="first"><?php echo stripslashes($timepayInfo['FirstName']); ?></span><br />
							<span id="address"><?php echo stripslashes($timepayInfo['Address']); ?></span><br/>
							<span id="city"><?php echo stripslashes($timepayInfo['City']); ?></span>, 
							<span id="state"><?php echo stripslashes($timepayInfo['State']); ?></span>
							<span id="zip"><?php echo stripslashes($timepayInfo['Zip']); ?></span>
						</td>
						<td colspan="3">
							<input id="sameAsBilling" type="checkbox" name="sameAsBilling" value="false" /> <label for="sameAsBilling">Is this the same as your billing information?</label>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<div style="padding-left: 140px; padding-top: 10px; padding-bottom: 15px;">
	<span class="style3" style="padding-left: 100px;">Please fill out your email address for receipt of payment.</span>
<p style="width:80%; background-color:#f2f2f2">Email &nbsp;&nbsp;

<input name="email" type="text" id="email"  value="<?php echo (isset($_POST['email']) ? $_POST['email'] : ""); ?>"/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Phone Number<span class="style1">*</span>&nbsp;&nbsp;
<input name="phone" type="text" id="phone"  value="<?php echo (isset($_POST['phone']) ? $_POST['phone'] : ""); ?>"/></p>
</div>

<div align="center"><table width="80%" border="0" cellspacing="4" cellpadding="4" style="border: 1px solid #999">
<tr>
	<td width="29%" colspan="2">
		<input checked="checked" type="radio" name="minimumOrCustom" id="payMin" value="minimum" <?php echo (isset($_POST['minimumOrCustom']) && $_POST['minimumOrCustom'] != 'custom') ? 'checked="yes"' : ''; ?> />
		<label for="payMin">Pay minimum: $<?php echo $timepayInfo['FineTotal']; ?></label><br />
		
		<input type="radio" name="minimumOrCustom" id="payCustom" value="custom" <?php echo (isset($_POST['minimumOrCustom']) && $_POST['minimumOrCustom'] == 'custom') ? 'checked="yes"' : ''; ?> />
		<label for="payCustom">Custom Payment: </label>$<input type="text" size="5" name="customPayment" id="customPayment" 
			value="<?php echo (isset($_POST['customPayment']) ? $_POST['customPayment'] : ""); ?>" onkeyup="javascript:updateTotal();" onpaste="javascript:updateTotal();" oninput="javascript:updateTotal();" onchange="javascript:updateTotal();" /><br/>

		Processing Fee: $<span id="processingFee"><?php echo number_format($timepayInfo['ProcessingTotal'], 2); ?></span><input id="processingFeeField" type="hidden" name="ProcessingFee" value="<?php echo (isset($_POST['ProcessingFee']) && $_POST['ProcessingFee'] != '') ? $_POST['ProcessingFee'] : number_format($timepayInfo['ProcessingTotal'], 2); ?>" /><br/>
		
		<em><strong style="color: #FF0000">Total:</strong></em> <span id="totalPayment">$<?php echo number_format($timepayInfo['Total'], 2); ?></span>
		
		<input type="hidden" name="chargetotal" id="chargetotal" value="<?php echo str_replace(',', '', $timepayInfo['Total']); ?>" />
	</td>
	<td width="19%"><strong style="color: red;"><?php echo (isset($customPaymentErrorMessage) ? $customPaymentErrorMessage : ""); ?></strong></td>
	<td width="31%" align="center"><strong>Payment Card Billing Address</strong></td>
	<td width="20%">
		<img src="/css/images/Picture2.png" width="132" height="30" />
	</td>
</tr>
<tr>
<td></td>
<td colspan="2"><?php echo (isset($paymentErrorMessage) ? $paymentErrorMessage : ""); ?></td>
<td rowspan="3"> 

<table width="100%" border="0" cellspacing="3" cellpadding="3">
<tr>
<td>Name</td>
<td><input name="name" type="text" id="billing-name" value="<?php echo (isset($_POST['name']) ? $_POST['name'] : ""); ?>"/></td>
</tr>
<tr>
<td>Address</td>
<td><input name="address" type="text" id="billing-address"  value="<?php echo (isset($_POST['address']) ? $_POST['address'] : ""); ?>"/></td>
</tr>
<tr>
<td>City</td>
<td><input name="city" type="text" id="billing-city"  value="<?php echo (isset($_POST['city']) ? $_POST['city'] : ""); ?>"/></td>
</tr>
<tr>
<td>State</td>
<td>
<select id="billing-state" name="state" value="<?php echo (isset($_POST['state']) ? $_POST['state'] : ""); ?>" >
	<option value="">Select ...</option>
	<option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="DC">District of Columbia</option>
	<option value="FL">Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option>
</select>
</td>
</tr>
<tr>
<td>Zip</td>
<td><input id="billing-zip" name="zip" type="text" value="<?php echo (isset($_POST['zip']) ? $_POST['zip'] : ""); ?>"/></td>
</tr>
<tr>
<td>Card Number</td>
<td><input name="card_number" type="text" id="card_number"  value="<?php echo (isset($_POST['card_number']) ? $_POST['card_number'] : ""); ?>"/></td>
</tr>
<tr>
<td>Exp Date (Format : mm/yy)</td>
<td><input name="exp_date" type="text" id="exp_date"  value="<?php echo (isset($_POST['exp_date']) ? $_POST['exp_date'] : ""); ?>"/></td>
</tr>
<tr>
<td>CVV Code</td>
<td><input name="cvv" type="text" id="cvv" />
<input type="hidden" name="process" value="payment"/>
	<br />
(3 digit pin on rear of card)
	</td>
	<tr>
	<td colspan="2" align="center">
	<p style="text-align: center;">
		<a id="submit" href="#"><img src="/css/images/pay-now-button.png" width="118" height="33" border="0" /></a>
		<span id="processing" style="display: none;">Processing...</span>
	</p>
	</tr>
	</table>
	</td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td colspan="3">The information contained in this website is for general information purposes only. The information is provided by Business Name and while we endeavour to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.
	<p><span class="style1">*</span><input name="terms_condition" type="checkbox" id="terms_condition" value="1" /> <label for="terms_condition">I Agree</label></p>
	</td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	</table>
	</div>
				<div align="center" style=" padding:80px;">All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a></div>
			</div>
		</form>
	</body>
</html>
