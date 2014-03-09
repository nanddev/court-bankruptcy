<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/includes/dbinfo-pdo-bankruptcy.php');

// If we don't have a trustee name or case number, redirect back to index.php
if (empty($_SESSION["TrusteeName"]) || empty($_SESSION["CaseNumber"])) {
	header("Location: index.php");
}

$errorMessage = "";

$valid = true;
if (isset($_POST['CustomPayment'])) {

	if (!is_numeric($_POST['CustomPayment'])) {
		$errorMessage = 'Please enter a valid number for your custom payment amount.';
		$valid = false;
	}
	else {
		// This is an ok amount, so update the totals
		$_SESSION['PaymentAmount'] = number_format($_POST['CustomPayment'], 2);
		$_SESSION['ProcessingFee'] = number_format($_POST['ProcessingFee'], 2);
		$_POST['chargetotal'] = $_POST['CustomPayment'] + $_POST['ProcessingFee'];
		$_SESSION['Total'] = number_format($_POST['chargetotal'], 2);
	}
}

// Process the payment upon submission of the form (at the bottom of this file)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid) {

	try {

		include($_SERVER['DOCUMENT_ROOT'] . '/includes/TransFirst.php');

		$debug = false;
		$demo = true;
		$success = false;
		$receiptNumber = strtoupper('CF-' . md5(uniqid(rand(), true)));
		$prodEmails = "jarrett.andrew@gmail.com, jnthomas8@gmail.com";
		$debugEmails = "jarrett.andrew@gmail.com, jnthomas8@gmail.com";
		$headers = "From: Website Support <support@example.com>\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";						
		if ($debug) {
			$headers .= 'Bcc: ' . $debugEmails . "\r\n";
			$_SESSION['ProcessingFee'] = "0.20";
			$_SESSION['PaymentAmount'] = "0.30";
			$_SESSION['Total'] = "0.50";
		}
		else {
			$headers .= 'Bcc: ' . $prodEmails . "\r\n";
		}

		// Send through the processing fee first
		$wsdl = "https://ws.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl";
		$client = new SoapClient($wsdl, array('trace'=>1, 'exceptions'=>1));  

		$expiration = explode('/', $_POST['exp_date']);
		$expirationMonth = $expiration[0];
		$expirationYear = $expiration[1];

		$billingInfo = array();
		$billingInfo['merchantId'] = 'xxxxxxxx'; // "Chapter 13" Merchant ID
		$billingInfo['key'] = 'xxxxxxxx'; // "Chapter 13" Reg key
		$billingInfo['receiptNumber'] = $receiptNumber;
		$billingInfo['ccNum'] = $_POST['card_number'];
		$billingInfo['cvv'] = $_POST["cvv"];
		$billingInfo['expiration'] = $expirationYear . $expirationMonth;
		$billingInfo['total'] = '0' . (str_replace(',', '', $_SESSION['Total']) * 100); // "Total" Leading zero is required. This is in *pennies*
		$billingInfo['name'] = $_POST['name'];
		$billingInfo['phoneNumber'] = $_POST['phone'];
		$billingInfo['email'] = $_POST['email'];
		$billingInfo['address1'] = $_POST['address'];
		$billingInfo['city'] = $_POST['city'];
		$billingInfo['state'] = $_POST['state'];
		$billingInfo['zipcode'] = $_POST['zip'];

		// 5. If settle isn't successful, void the total amount (using tranNr returned by the settle)
		// 6. Send email and reference the reciept number (use the same one for all transactions)
		
		if (!$demo) {
			// Just authorize the total amount to see if they have it available
			$tf = new TransFirst();
			$request = $tf->generateAuthOnlyRequest($billingInfo);
			$response = $client->SendTran($request);
			$success = $tf->isSuccessful($response);
			$partialAuth = $tf->isPartialAuth($response);
			$authTranNumber = $response->tranData->tranNr;
			$billingInfo['tranNumber'] = $authTranNumber;

			// If this was partially authorized or unsuccessful void the transaction and then throw an exception.
			if ($partialAuth || !$success) {
				$request = $tf->generateVoidRequest($billingInfo);
				$response = $client->sendTran($request);
				$success = $tf->isSuccessful($response);

				if ($success) {
					throw new Exception("Please verify that you have enough money on this card to pay the total amount. This transaction has been successfully voided.");
				}
				else {
					throw new Exception("Please verify that you have enough money on this card to pay the total amount. We were unable to successfully void this transcaction. Please call us at 1-877-689-5144 for assistance.");
				}
			}
			else {
				// Settle the processing fee first
				$billingInfo['total'] = '0' . (str_replace(',', '', $_SESSION['ProcessingFee']) * 100); // "Processing Fee" Leading zero is required. This is in *pennies*
				$request = $tf->generateSettleRequest($billingInfo);
				$response = $client->SendTran($request);
				$success = $tf->isSuccessful($response);
				$settleProcessingFeeTranNumber = $response->tranData->tranNr;
				$billingInfo['tranNumber'] = $settleProcessingFeeTranNumber;

				// If this wasn't successful void the transaction and then throw an exception.
				// I'm not sure why this would happen since the amount was successfully authed.
				if (!$success) {
					$request = $tf->generateVoidRequest($billingInfo);
					$response = $client->sendTran($request);
					$success = $tf->isSuccessful($response);

					if ($success) {
						throw new Exception("There was an error when trying to process the processing fee. This transaction has been successfully voided.");
					}
					else {
						throw new Exception("There was an error when trying to process the processing fee. We were unable to successfully void this transcaction. Please call us at 1-877-689-5144 for assistance.");
					}
				}

				// Settle the payment amount next
				$trusteeId = $_SESSION['TrusteeId'];
				
				if (!$debug) {
					$sql = 'select gateway_id, reg_key from trustees where id = :trusteeId';
					$statement = $db->prepare($sql);
					$statement->execute(array(':trusteeId'=>$trusteeId));
					$trustee = $statement->fetch();

					$billingInfo['merchantId'] = $trustee['gateway_id']; // Trustee Office's Merchant ID
					$billingInfo['key'] = $trustee['reg_key']; // Trustee Office's Reg key
				}
				$billingInfo['total'] = '0' . (str_replace(',', '', $_SESSION['PaymentAmount']) * 100); // "Payment Amount" Leading zero is required. This is in *pennies*
				$billingInfo['tranNumber'] = $authTranNumber;

				$request = $tf->generateSettleRequest($billingInfo);
				$response = $client->SendTran($request);
				$success = $tf->isSuccessful($response);
				$settlePaymentTranNumber = $response->tranData->tranNr;
				$billingInfo['tranNumber'] = $settlePaymentTranNumber;

				// If this wasn't successful void the transaction and then throw an exception.
				// I'm not sure why this would happen since the amount was successfully authed.
				if (!$success) {
					$request = $tf->generateVoidRequest($billingInfo);
					$response = $client->sendTran($request);
					$success = $tf->isSuccessful($response);

					if ($success) {
						throw new Exception("There was an error when trying to process your payment. The transaction has been successfully voided.");
					}
					else {
						throw new Exception("There was an error when trying to process your payment. We were unable to successfully void this transcaction. Please call us at 1-877-689-5144 for assistance.");
					}
				}
				else {
					// If authorization and charges succeeded, update the database

					// Record the payment
					$sql = "insert into payments (
							trustee_id,
							case_number,
							full_name,
							codebtor_full_name,
							employer,
							payment_amount,
							processing_fee,
							payment_total
						) values (
							:trustee_id,
							:case_number,
							:full_name,
							:codebtor_full_name,
							:employer,
							:payment_amount,
							:processing_fee,
							:payment_total
						)";

					$statement = $db->prepare($sql);

					$params = array(
						':trustee_id' => $_SESSION['TrusteeId'],
						':case_number' => $_SESSION['CaseNumber'],
						':full_name' => $_SESSION['FullName'],
						':codebtor_full_name' => $_SESSION['CodebtorFullName'],
						':employer' => '',
						':payment_amount' => str_replace(',', '', $_SESSION['PaymentAmount']),
						':processing_fee' => str_replace(',', '', $_SESSION['ProcessingFee']),
						':payment_total' => str_replace(',', '', $_SESSION['Total'])
					);
					$statement->execute($params);

					// Record the billing info
					$sql = "insert into orders (
							trustee_id,
							case_number,
							full_name,
							codebtor_full_name,
							employer,
							billing_name,
							billing_address,
							billing_city,
							billing_state,
							billing_zip,
							billing_email,
							billing_phone,
							payment_amount,
							processing_fee,
							payment_total,
							receipt_number,
							ws_response
						) values (
							:trustee_id,
							:case_number,
							:full_name,
							:codebtor_full_name,
							:employer,
							:billing_name,
							:billing_address,
							:billing_city,
							:billing_state,
							:billing_zip,
							:billing_email,
							:billing_phone,
							:payment_amount,
							:processing_fee,
							:payment_total,
							:receipt_number,
							:ws_response
						)";

					$statement = $db->prepare($sql);

					$params = array(
						':trustee_id' => $_SESSION['TrusteeId'],
						':case_number' => $_SESSION['CaseNumber'],
						':full_name' => $_SESSION['FullName'],
						':codebtor_full_name' => $_SESSION['CodebtorFullName'],
						':employer' => '',
						':billing_name' => $billingInfo['name'],
						':billing_address' => $billingInfo['address1'],
						':billing_city' => $billingInfo['city'],
						':billing_state' => $billingInfo['state'],
						':billing_zip' => $billingInfo['zipcode'],
						':billing_email' => $billingInfo['email'],
						':billing_phone' => $billingInfo['phoneNumber'],
						':payment_amount' => str_replace(',', '', $_SESSION['PaymentAmount']),
						':processing_fee' => str_replace(',', '', $_SESSION['ProcessingFee']),
						':payment_total' => str_replace(',', '', $_SESSION['Total']),
						':receipt_number' => $billingInfo['receiptNumber'],
						':ws_response' => serialize($response)
					);
					$statement->execute($params);
				}
			}
		}

		// Send off the customer's personal receipt and send them to confirmation page
		if ($success || $demo || $debug) {

			$customerName = $billingInfo['name'];
			$customerEmail = $billingInfo['email'];
			$customerSubject = 'Personal Receipt - Chapter 13 Payment Details';
			$message2Customer = '<table width="650">
				  <tr>
				    <td align="left" valign="middle"><table width="100%" >
				      <tr>
					<td>&nbsp;</td>
					<td align="center" valign="middle"><h3>Chapter 13 Payment Details</h3></td>
					<td>&nbsp;</td>
				      </tr>
				      <tr>
					<td>&nbsp;</td>
					<td><table width="100%" cellpadding="3" cellspacing="3">
					  <tr>
					    <td align="right" valign="middle">Receipt Number: </td>
					    <td align="left" valign="middle">'.$billingInfo['receiptNumber'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Payment For: </td>
					    <td align="left" valign="middle">'.$_SESSION['FullName'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Trustee Office: </td>
					    <td align="left" valign="middle">'.$_SESSION['TrusteeName'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Case Number</td>
					    <td align="left" valign="middle">'.$_SESSION['CaseNumber'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Your payment: </td>
					    <td align="left" valign="middle">$'.$_SESSION['PaymentAmount'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Processing Fee: </td>
					    <td align="left" valign="middle">$'.$_SESSION['ProcessingFee'].'</td>
					  </tr>
					  <tr>
					    <td align="right" valign="middle">Total Amount: </td>
					    <td align="left" valign="middle"><strong>$'.$_SESSION['Total'].'</strong></td>
					  </tr>
					</table></td>
					<td>&nbsp;</td>
				      </tr>
				    </table></td>
				  </tr>
				</table>';

			// Send Personal receipt
			$sendmail2Customer = mail($customerEmail, $customerSubject, $message2Customer, $headers);

			// Save receipt info and redirect to the confirmation page
			$_SESSION['ReceiptNumber'] = $billingInfo['receiptNumber'];
			$_SESSION['BillingName'] = $billingInfo['name'];
			$_SESSION['PhoneNumber'] = $billingInfo['phoneNumber'];
			$_SESSION['Email'] = $billingInfo['email'];
			$_SESSION['BillingAddress'] = $billingInfo['address1'];
			$_SESSION['BillingCity'] = $billingInfo['city'];
			$_SESSION['BillingState'] = $billingInfo['state'];
			$_SESSION['BillingZip'] = $billingInfo['zipcode'];
			$_SESSION['LastFourCardDigits'] = substr($billingInfo['ccNum'], -4, strlen($billingInfo['ccNum']));

			header("Location: confirmation.php");
		}
	}
	catch (Exception $e) {
		$errorMessage = $e->getMessage();
	}
}

// Calculate the processing fee
$PaymentAmount = str_replace(',', '', $_SESSION['PaymentAmount']);
if ($PaymentAmount < 100) {
	$ProcessingFee = 4.0 + (ceil((4.0+$PaymentAmount)*003.8) / 100);
}
else {
	$ProcessingFee = 8.0 + (ceil((8.0+$PaymentAmount)*004.0) / 100);
}

$_SESSION['ProcessingFee'] = number_format($ProcessingFee, 2);
$_SESSION['Total'] = number_format($PaymentAmount + $ProcessingFee, 2);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#submit').click(function(e) {

					valid = true;
					errorMessage = "";
					$('#submit').hide();
					$('#processing').show();

					if ($('#customPayment').val() == "") {
						errorMessage += "Please enter your payment amount.\n";
						valid = false;
					}
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
				var payment = parseFloat(document.checkout.CustomPayment.value);
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
				else {
					total.innerHTML = "$" + (0 + parseFloat(updatedProcessingFee.innerHTML)).toFixed(2);
				}
			}
		</script>
	</head>
	<body>
		<form name="checkout" id="checkout" method="post" action="">
			<div id="main">
				<div class="head">
					<div style="width:40%; float:left" >
						<a href="/index.php">
							<img src="" width="165" height="123" border="0" style="vertical-align: middle;" />
 						</a>
					</div>                
					<div align="right" style="width:50%; float:left">
						<a href="/index.php" style="text-decoration: underline;">Home</a>
					</div>
				</div>
				<div class="login">&nbsp;</div>
				<div style="clear:both"></div>
				<div class="search" align="center">
					<table width="100%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
						<tr bgcolor="#366092" style="color:#FFF;" align="center">
							<td>Name</td>
							<td>Trustee</td>
							<td>Case Number</td>
							<td>Outstanding Balance</td>
							<td>Suggested Frequency</td>
							<td>Suggested Payment</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<td>
								<span><?php echo $_SESSION['FullName']; ?></span>
							</td>
							<td>
								<span><?php echo $_SESSION['TrusteeName']; ?></span>
							</td>
							<td>
								<span><?php echo $_SESSION['CaseNumber']; ?></span>
							</td>
							<td>
								<span>$<?php echo $_SESSION['OutstandingBalance']; ?></span>
							</td>
							<td>
								<span><?php echo $_SESSION['SuggestedFrequency']; ?></span>
							</td>
							<td>
								<span>$<?php echo $_SESSION['SuggestedAmount']; ?></span>
							</td>
						</tr>
						<tr>
							<td colspan="4">&nbsp;</td>
							<td align="right" style="background-color: #f2f2f2;">Payment Amount*: </td>
							<td>
								$<input type="text" size="5" name="CustomPayment" id="customPayment" 
									value="<?php echo (isset($_POST['CustomPayment']) ? $_POST['CustomPayment'] : $_SESSION['PaymentAmount']); ?>"
									onkeyup="javascript:updateTotal();"
									onpaste="javascript:updateTotal();"
									oninput="javascript:updateTotal();"
									onchange="javascript:updateTotal();" />
							</td>
						</tr>
						<tr>
							<td colspan="4">&nbsp;</td>
							<td align="right" style="background-color: #f2f2f2;">Processing Fee: </td>
							<td>
								$<span id="processingFee"><?php echo $_SESSION['ProcessingFee']; ?></span>
								<input id="processingFeeField" type="hidden" name="ProcessingFee" 
									value="<?php echo (isset($_POST['ProcessingFee']) ? $_POST['ProcessingFee'] : $_SESSION['ProcessingFee']); ?>" />
							</td>
						</tr>
						<tr>
							<td colspan="4">&nbsp;</td>
							<td align="right" style="background-color: #f2f2f2;">
								<em><strong style="color: #FF0000">Total:</strong></em>
							</td>
							<td>
								<span id="totalPayment">$<?php echo $_SESSION['Total']; ?></span>
								<input type="hidden" name="chargetotal" id="chargetotal" value="<?php echo str_replace(',', '', $_SESSION['Total']); ?>" />
							</td>
						</tr>
					</table>
				</div>

				<!-- Error message goes here -->
				<?php 
					if (!empty($errorMessage)) {
						echo <<< AAA
							<div style="margin: 20px; padding: 10px; border: 1px solid Red; background-color: LightGoldenRodYellow;">
								<span style="color: Red; font-weight: bold; font-size: 14px;">$errorMessage</span>
							</div>
AAA;
					}
				?>

				<div style="margin: 20px 200px; padding: 10px; background-color: #f2f2f2; font-size: 14px;">
					Email &nbsp;&nbsp;<input size="30" name="email" type="text" id="email"  value="<?php if (isset($_POST['email'])) echo $_POST['email'] ?>" />
					&nbsp;&nbsp;<span style="font-weight: bold;">Please fill out your email address for receipt of payment.</span>
				</div>

				<div align="center">
					<table width="80%" border="0" cellspacing="4" cellpadding="4" style="border: 1px solid #999">
						<tr>
							<td width="40%" align="center">
								<strong>Billing Information</strong>
							</td>
							<td width="10%"></td>
							<td align="middle" width="40%">
								<img src="/css/images/Picture2.png" width="132" height="30" />
							</td>
							<td width="10%"></td>
						</tr>
						<tr>
							<td> 
								<table width="100%" border="0" cellspacing="3" cellpadding="3">
									<tr>
										<td>Name*</td>
										<td><input name="name" type="text" id="billing-name" value="<?php if (isset($_POST['name'])) echo $_POST['name']?>"/></td>
									</tr>
									<tr>
										<td>Phone Number*</td>
										<td><input name="phone" type="text" id="phone" value="<?php if (isset($_POST['phone'])) echo $_POST['phone'] ?>"/></td>
									<tr>
										<td>Address*</td>
										<td><input name="address" type="text" id="billing-address"  value="<?php if (isset($_POST['address'])) echo $_POST['address']?>"/></td>
									</tr>
									<tr>
										<td>City*</td>
										<td><input name="city" type="text" id="billing-city"  value="<?php if (isset($_POST['city'])) echo $_POST['city']?>"/></td>
									</tr>
									<tr>
										<td>State*</td>
										<td>
											<select id="billing-state" name="state">
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
										<td>Zip*</td>
										<td><input id="billing-zip" name="zip" type="text" value="<?php if (isset($_POST['zip'])) echo $_POST['zip']?>"/></td>
									</tr>
									<tr>
										<td>Card Number*</td>
										<td><input name="card_number" type="text" id="card_number"  value="<?php if (isset($_POST['card_number'])) echo $_POST['card_number']?>"/></td>
									</tr>
									<tr>
										<td valign="top">Expiration Date*</td>
										<td>
											<input name="exp_date" type="text" id="exp_date"  value="<?php if (isset($_POST['exp_date'])) echo $_POST['exp_date']?>"/><br/>
											(format: mm/yy)
										</td>
									</tr>
									<tr>
										<td valign="top">CVV Code*</td>
										<td>
											<input name="cvv" type="text" id="cvv" />
											<br />
											(3 digit number on back of card)
										</td>
									</tr>
									<tr>
										<td colspan="2" align="center">
										</td>
									</tr>
								</table>
							</td>
							<td></td>
							<td valign="top">
								<p>The information contained in this website is for general information purposes only. The information is provided by &lt;Business Name&gt; and while we endeavour to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.</p>
								<input name="terms_condition" type="checkbox" id="terms_condition" value="1" /> <label for="terms_condition">I Agree*</label>
								<p style="text-align: center;">
									<a id="submit" href="#"><img src="/css/images/pay-now-button.png" width="118" height="33" border="0" /></a>
									<span id="processing" style="display: none;">Processing...</span>
								</p>
							</td>
							<td></td>
						</tr>
						<tr>
							<td colspan="2"></td>
							<td>
							</td>
							<td></td>
						</tr>
					</table>
				</div>
				<div align="center" style=" padding:80px;">All rights reserved 2013. <a href="/disclaimer.php">Disclaimer</a></div>
			</div>
		</form>
	</body>
</html>
