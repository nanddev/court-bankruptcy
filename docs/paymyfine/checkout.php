<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/includes/dbinfo-pdo.php');
include($_SERVER['DOCUMENT_ROOT'] . '/includes/Auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/includes/TransFirst.php');

$auth = new Auth();

// Remove any citations that aren't being paid (but only if this user is an admin)
//if ($auth->isAdmin()) {
//	foreach ($_SESSION['Citations'] as $index => $citation) {
//		// This citation isn't checked to be paid so remove it from the session
//		if (!isset($_POST['isPaying'][$index])) {
//			unset($_SESSION['Citations'][$index]);
//		}
//	}
//}

// Session id is used to lookup citations the user is going to pay for 
if (isset($_SESSION['sesid']) && empty($_SESSION['sesid'])) {
	$_SESSION['sesid'] = session_id();
	$sessionId = $_SESSION['sesid'];
}
else {
	$sessionId = $_SESSION['sesid'];
}

$valid = true;
if (isset($_POST['customFineAmount'])) {

	$citationInfo = $_SESSION['citationInfo'];

	// Amount isn't numeric so display an error
	if (!is_numeric($_POST['customFineAmount'])) {
		$customPaymentErrorMessage = 'Please enter a valid number for the custom fine total.';
		$valid = false;
	}
	else {
		// This is an ok amount, so update the totals
		$citationInfo['ProcessingTotal'] = number_format($_POST['ProcessingFee'], 2);
		$_POST['chargetotal'] = $_POST['customFineAmount'] + $_POST['ProcessingFee'];
		$citationInfo['Total'] = number_format($_POST['chargetotal'], 2);
		$_SESSION['citationInfo'] = $citationInfo;
	}
}

// Process the payment upon submission of the form (at the bottom of this file)
if (isset($_POST['process']) && $_POST['process'] == 'payment' && $valid) {

	try {
		// Get out the citation info that was saved before form was submitted
		$citationInfo = $_SESSION['citationInfo'];

		$demo = true;
		$debug = false;
		$success = false;
		$receiptNumber = strtoupper('CF-' . md5(uniqid(rand(), true)));
		$emails = "andrew@nanddevelopment.com, james@nanddevelopment.com";
		$debugEmails = "andrew@nanddevelopment.com, james@nanddevelopment.com";
		$headers = "From: Demo Email <demo@nanddevelopment.com>\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";						
		if (!$demo) {
			if (!$debug) {
				$headers .= 'Bcc: ' . $emails . "\r\n";
			}
			else {
				$headers .= 'Bcc: ' . $debugEmails . "\r\n";
			}
		}

		// Send through the processing fee first
		$wsdl = "https://ws.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl";
		$client = new SoapClient($wsdl, array('trace'=>1, 'exceptions'=>1));  

		$data = array();
		$expiration = explode('/', htmlentities($_POST['exp_date']));
		$expirationMonth = $expiration[0];
		$expirationYear = $expiration[1];
		$data['merchantId'] = 'xxxxxxxx'; // Business Merchant ID
		$data['key'] = 'xxxxxxxx'; // Business Reg Key

		if ($debug) {
			// Debug information
			$data['ccNum'] = '4111111111111111'; // Visa Test card number
			//$data['ccNum'] = '5499740000000057'; // MasterCard Test card number
			$data['cvv'] = '123'; // Any cvv code
			$data['expiration'] = '1806'; // Any future expiration date
			$data['expirationMonth'] = '06'; // Displayed on confirmation page
			$data['expirationYear'] = '18'; // Displayed on confirmation page
			$data['total'] = '050'; // "Request Amount" Leading zero is required. This is in *pennies*
		}
		else {
			$data['ccNum'] = htmlentities($_POST['card_number']);
			$data['cvv'] = htmlentities($_POST["cvv"]);
			$data['expiration'] = $expirationYear . $expirationMonth;
			$data['expirationMonth'] = $expirationMonth; // Displayed on confirmation page
			$data['expirationYear'] = $expirationYear; // Displayed on confirmation page
			$data['total'] = '0' . (str_replace(',', '', $citationInfo['ProcessingTotal']) * 100); // "Request Amount" Leading zero is required. This is in *pennies*
		}
		$data['name'] = htmlentities($_POST['name']);
		$data['phoneNumber'] = htmlentities($_POST['phone']);
		$data['email'] = htmlentities($_POST['email']);
		$data['address1'] = htmlentities($_POST['address']);
		$data['city'] = htmlentities($_POST['city']);
		$data['state'] = htmlentities($_POST['state']);
		$data['zipcode'] = htmlentities($_POST['zip']);
		$data['receiptNumber'] = $receiptNumber;

		if (!$demo) {
			$tf = new TransFirst();
			$request = $tf->generateTransactionRequest($data);
			$response = $client->SendTran($request);
			$success = $tf->isSuccessful($response);

			// If processing fee was successful, let's charge the fine amount
			if ($success || $debug) {

				// Record the convenience fee
				$sql = "insert into orders " .
					"(name, email, phone, address, city, state, zip, citation_number, case_number, violation_date, court_date, charges, processing_fee, chargetotal, ws_response, receipt_number)" .
					" values " .
					"(:name, :email, :phone, :address1, :city, :state, :zipcode, :citation_num, :case_num, :violation_date, :court_date, :charges, :processing_fee, :total, :ws_response, :receipt_number)";
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
							':citation_num' => '',
							':case_num' => '',
							':violation_date' => '',
							':court_date' => '',
							':charges' => '',
							':processing_fee' => $citationInfo['ProcessingTotal'],
							':total' => number_format(($data['total'] / 100), 2),
							':ws_response' => serialize($response),
							':receipt_number' => $receiptNumber
						));
					}

				} catch (PDOException $e) {
					echo $e->getMessage();
					exit;
				}

				// Process each citation being paid for
				foreach ($citationInfo['Citations'] as $citation) {

					// Code to lookup Jurisdiction's merchant id
					$courtId = $citation['CID'];
					$jurisdiction = $citation['Jurisdiction'];

					$sql = 'select merchant_id, reg_key, email from jurisdictions where cid = :courtId and jurisdiction = :jurisdiction';
					$statement = $db->prepare($sql);
					$statement->execute(array(':courtId'=>$courtId, ':jurisdiction'=>$jurisdiction));
					$merchant = $statement->fetch();

					if (!$debug) {
						$data['merchantId'] = $merchant['merchant_id'];// Jurisdiction's Merchant ID
						$data['key'] = $merchant['reg_key']; // Jurisdiction's Reg key
						if (isset($_POST['customFineAmount']) && $_POST['customFineAmount'] != str_replace(',', '', $citationInfo['FineTotal'])) {
							$data['total'] = '0' . ($_POST['customFineAmount'] * 100); // "Request Amount" Leading zero is required. This is in *pennies*
						}
						else {
							$data['total'] = '0' . (str_replace(',', '', $citation['FineAmount']) * 100); // "Request Amount" Leading zero is required. This is in *pennies*
						}
					}

					$request = $tf->generateTransactionRequest($data);
					$response = $client->SendTran($request);
					$success = $tf->isSuccessful($response);

					// If fine amount was successful, then send out the email receipt
					if ($success || $debug) {

						// Record the fine payment
						$sql = "insert into orders " .
							"(name, email, phone, address, city, state, zip, citation_number, case_number, violation_date, court_date, charges, fine_amount, chargetotal, ws_response, receipt_number)" .
							" values " .
							"(:name, :email, :phone, :address1, :city, :state, :zipcode, :citation_num, :case_num, :violation_date, :court_date, :charges, :fine_amount, :total, :ws_response, :receipt_number)";
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
									':citation_num' => $citation['CitationNumber'],
									':case_num' => $citation['CaseNumber'],
									':violation_date' => $citation['ViolationDate'],
									':court_date' => $citation['CourtDate'],
									':charges' => $citation['Charges'],
									':fine_amount' => str_replace(',', '', $citation['FineAmount']),
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

						$courtSubject = 'Confirmation of Warrant Payment';
						$message2court='
								<table width="650" >
				  <tr>
					<td align="left" valign="middle"><table width="100%" >
					  <tr>
					<td>&nbsp;</td>
					<td align="center" valign="middle"><h3>Warrant Payment Details</h3> </td>
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
						<td align="right" valign="middle">VJPID : </td>
						<td align="left" valign="middle">&nbsp;'.$citationInfo["VJPID"].'</td>
					  </tr>
					  <tr>
						<td align="right" valign="middle">Offender\'s Name: </td>
						<td align="left" valign="middle">&nbsp;'.$citationInfo["FirstName"] . ' ' . $citationInfo["LastName"].'</td>
					  </tr>
					  <tr>
						<td align="right" valign="top">Offender\'s Address: </td>
						<td align="left" valign="top">&nbsp;'.
						$citationInfo["Address"]. '<br/>&nbsp;' .
						$citationInfo["City"] . ', ' . $citationInfo["State"] . ' ' . $citationInfo["Zip"].
						'</td>
					  </tr>
					  <tr>
						<td align="right" valign="middle">Citation Number: </td>
						<td align="left" valign="middle">&nbsp;'.$citation["CitationNumber"].'</td>
					  </tr>
					  <tr>
						<td align="right" valign="middle">Charges: </td>
						<td align="left" valign="middle">&nbsp;'.$citation["Charges"].'</td>
					  </tr>
					  <tr>
						<td align="right" valign="middle">Violation Date: </td>
						<td align="left" valign="middle">&nbsp;'.$citation["ViolationDate"].'</td>
					  </tr>
					  <tr>
						<td align="right" valign="middle">Court Date: </td>
						<td align="left" valign="middle">&nbsp;'.($citation["CourtDate"] != '' ? $citation["CourtDate"] : "N/A").'</td>
					  </tr>';
						if (isset($_POST['customFineAmount']) && $_POST['customFineAmount'] != str_replace(',', '', $citationInfo['FineTotal'])) { $message2court .=  
					  '<tr>
						<td align="right" valign="middle">Fine Amount: </td>
						<td align="left" valign="middle">&nbsp;$'.number_format($_POST['customFineAmount'], 2).'</td>
					  </tr>'; }
						else { $message2court .=  				  
					  '<tr>
						<td align="right" valign="middle">Fine Amount: </td>
						<td align="left" valign="middle">&nbsp;$'.$citation["FineAmount"].'</td>
					  </tr>'; }
						$message2court .= 
					  '<tr>
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
					}
				}
			}

			if ($success || $debug) {
				// Everythin processed correctly, so send off the customer's personal receipt
				$customerName = stripslashes($data['name']);
				$customerEmail = stripslashes($data['email']);

				$customerSubject = 'Personal Receipt - Warrant Payment Details';

				$citationList = '';
				foreach ($citationInfo['Citations'] as $citation) {
					$citationList .= '<tr>';
					$citationList .= "<td>${citation['CitationNumber']}</td>";
					$citationList .= "<td><em>${citation['Charges']}</em></td>";
					$citationList .= "<td align=\"right\">$${citation['FineAmount']}</td>";
					$citationList .= '</tr>';
				}

				$message2Customer = '<table width="650" >
			  <tr>
				<td align="left" valign="middle"><table width="100%" >
				  <tr>
				<td>&nbsp;</td>
				<td align="center" valign="middle"><h3>Warrant Payment Details</h3>
				  </td>
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
					<td align="left" valign="middle">'.stripslashes($data["name"]).'</td>
				  </tr>
				  <tr>
					<td align="right" valign="middle">Email: </td>
					<td align="left" valign="middle">'.stripslashes($data["email"]).'</td>
				  </tr>
				  <tr>
					<td align="right" valign="middle">Phone: </td>
					<td align="left" valign="middle">'.stripslashes($data["phoneNumber"]).'</td>
				  </tr>		   
				  <tr>
					<td align="right" valign="top">Address: </td>
					<td align="left" valign="top">'
					.stripslashes($data["address1"]).'<br/>'
					.stripslashes($data["city"] . ', ' . $data["state"] . ' ' . $data["zipcode"])
				  .'</td>
				  </tr>
				  <tr>
					<td colspan="2">
					  <table>
					<tr>
					  <td align="middle"><strong>Citation Number</strong></td>
					  <td align="middle"><strong>Charges</strong></td>
					  <td align="middle"><strong>Fine Amount</strong></td>
					</tr>' . $citationList;
						if (isset($_POST['customFineAmount']) && $_POST['customFineAmount'] != str_replace(',', '', $citationInfo['FineTotal'])) { 
							$message2Customer .=  
					  '<tr>
						<td align="right" valign="middle" colspan="2">Fine Total: </td>
						<td align="right" valign="middle">&nbsp;$'.number_format($_POST['customFineAmount'], 2).'</td>
					  </tr>'; }
						else { $message2Customer .=  				  
					  '<tr>
						<td align="right" valign="middle" colspan="2">Fine Total: </td>
						<td align="right" valign="middle">&nbsp;$'.$citationInfo["FineTotal"].'</td>
					  </tr>'; }
						$message2Customer .= 
					'<tr>
					  <td colspan="2" align="right">Processing Fee: </td>
					  <td align="right">$'. stripslashes($citationInfo["ProcessingTotal"]).'</td>
					</tr>
					<tr>
					  <td colspan="2" align="right">Total Amount: </td>
					  <td align="right"><strong>$'. stripslashes($citationInfo["Total"]).'</strong></td>
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
				$_SESSION['receiptNumber'] = $receiptNumber;

				// We're done processing the order. Show the confirmation page.
				header("Location: confirmation.php");
			}
		}
		else {
			// Save relevant info for the confirmation page
			$data['ccNum'] = "************".substr($data['ccNum'], strlen($data['ccNum'])-4, 4);
			$_SESSION['data'] = $data;
			$_SESSION['response'] = (object) array('rspCode'=>'00'); // Pretend like it was successful
			$_SESSION['receiptNumber'] = $receiptNumber;

			// Send email
			$sendmail2Us = mail("demo@nanddevelopment.com", "New Demo email submitted", "Demo email submitted for court.nanddevelopment.com by " . stripslashes($data['name']) . " (" . stripslashes($data['email']) . ").", $headers);

			header("Location: confirmation.php");
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
                                        e.preventDefault();
                                        return false;
                                });

			});

			function updateTotal() {
				var processingFee = parseFloat(document.getElementById('processingFee').innerHTML);
				var payment = parseFloat(document.checkout.customFineAmount.value);
				var total = document.getElementById('totalPayment');
				var updatedProcessingFee = document.getElementById('processingFee');
				var updatedFineAmount = document.getElementById('fineAmount');
					
				if (!isNaN(payment)) {
					if (payment < 100) { 
						updatedProcessingFee.innerHTML = (4 + (Math.ceil((4 + payment) * 3.8) / 100)).toFixed(2);
					} else {
						updatedProcessingFee.innerHTML = (8 + (Math.ceil((8 + payment) * 4.0) / 100)).toFixed(2);
					}
					updatedFineAmount.innerHTML = payment;

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
						<a href="/index.php">
							<img src="/img/logo.gif" width="300" height="60" border="0" style="vertical-align: middle;" />
						</a>
					</div>                
					<div align="right" style="width:50%; float:left">
						<u><a href="/index.php">Home</a></u>
					</div>
				</div>
				<div class="login">&nbsp;</div>
				<div style="clear:both"></div>
				<div class="search" align="center">
				<table width="90%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="18%" align="center" valign="middle">&nbsp;</td>
						<td width="82%">
<?php
// We filter out the citations we didn't check to pay
filterCitations();

$citationInfo = array();
$citationsToPay = $_SESSION['Citations'];
$citationInfo['Citations'] = array();
$citationInfo['FineTotal'] = 0; // Set up an initial value for the fine total
$citationCount = count($citationsToPay);

// Setup all the citations that the user wants to pay
foreach ($citationsToPay as $citationRow) {

	// Calculate the processing fee
	$FineAmount = str_replace(',', '', $citationRow['Fine_Amount']);

	// Setup info related to this individual citation
	$citation = array(
		"CID" => $citationRow['CID'],
		"Jurisdiction" => $citationRow['Jurisdiction'],
		"ViolationDate" => $citationRow['Violation_Date'],
		"CitationNumber" => $citationRow['Citation_Number'],
		"CaseNumber" => $citationRow['Case_Number'],
		"Charges" => $citationRow['Charges'],
		"CourtDate" => ($citationRow['Court_Date'] != '' ? $citationRow['Court_Date'] : 'N/A'),
		"FineAmount" => number_format($FineAmount, 2),
		"ProcessingFee" => number_format(0, 2)
	);
	array_push($citationInfo['Citations'], $citation);

	// Setup info related to all citations. I'm assuming that the person's
	// VJPID, name, and address will always be the same.
	$citationInfo['VJPID'] = $citationRow['VJPID'];
	$citationInfo['FirstName'] = $citationRow['First_Name'];
	$citationInfo['LastName'] = $citationRow['Last_Name'];
	$citationInfo['Address'] = $citationRow['Address'];
	$citationInfo['City'] = $citationRow['City'];
	$citationInfo['State'] = $citationRow['State'];
	$citationInfo['Zip'] = $citationRow['Zip'];
	$citationInfo['FineTotal'] = $citationInfo['FineTotal'] + $FineAmount;
	$citationInfo['Total'] = $citationInfo['FineTotal'];
}

echo getTable($auth);

if ($citationInfo['FineTotal'] < 100) {
	$citationInfo['ProcessingTotal'] = 4.0 + (ceil((4.0+$citationInfo['FineTotal'])*003.8) / 100);
} else {
	$citationInfo['ProcessingTotal'] = 8.0 + (ceil((8.0+$citationInfo['FineTotal'])*004.0) / 100);
}
for ($i = 0; $i < $citationCount; $i++) {
	$citationInfo['Citations'][$i]['ProcessingFee'] = number_format($citationInfo['ProcessingTotal'] / $citationCount, 2); #look here
}
$citationInfo['Total'] = $citationInfo['Total'] + $citationInfo['ProcessingTotal'];
$citationInfo['ProcessingTotal'] = number_format($citationInfo['ProcessingTotal'], 2);
$citationInfo['FineTotal'] = number_format($citationInfo['FineTotal'], 2);
$citationInfo['Total'] = number_format($citationInfo['Total'], 2);

// Save the $citationInfo variable to the session so we have this info when the payment is processed
$_SESSION['citationInfo'] = $citationInfo;
?>
			</td>
		</tr>
	</table>
</div>

<div style="padding-left: 140px; padding-top: 10px; padding-bottom: 15px;">
	<span class="style3" style="padding-left: 100px;">Please fill out your email address for receipt of payment.</span>
<p style="width:80%; background-color:#f2f2f2">Email &nbsp;&nbsp;

<input name="email" type="text" id="email"  value="<?php echo (isset($_POST['email']) ? htmlentities($_POST['email']) : ""); ?>"/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Phone Number<span class="style1">*</span>&nbsp;&nbsp;
<input name="phone" type="text" id="phone"  value="<?php echo (isset($_POST['phone']) ? htmlentities($_POST['phone']) : ""); ?>"/></p>
</div>

<?php 
if ($auth->isAdmin()) {
	if (!isset($_POST['customFineAmount'])) $_POST['customFineAmount'] = str_replace(',', '', $citationInfo['FineTotal']);
	echo <<<ADM
	<div style="padding-left: 140px; padding-top: 10px; padding-bottom: 15px; background-color: #f2f2f2;">
		<h3>Admin Only</h3>
		<p style="width:80%;">
			<label for="customFineAmount">Custom Fine Amount</label>
			<input name="customFineAmount" type="text" id="customFineAmount"  value="${_POST['customFineAmount']}" onkeydown="javascript:updateTotal();" onpaste="javascript:updateTotal();" oninput="javascript:updateTotal();" onchange="javascript:updateTotal();" />
		</p>
	</div>
	<p>&nbsp;</p>
ADM;
}
?>


<div align="center"><table width="80%" border="0" cellspacing="4" cellpadding="4" style="border: 1px solid #999">
<tr>
	<td width="30%" colspan="2">
		Fine Amount: $<span id="fineAmount"><?php echo $citationInfo['FineTotal']; ?></span><br />
		Processing Fee: $<span id="processingFee"><?php echo number_format($citationInfo['ProcessingTotal'], 2); ?></span>
		<input id="processingFeeField" type="hidden" name="ProcessingFee" value="<?php echo (isset($_POST['ProcessingFee']) ? htmlentities($_POST['ProcessingFee']) : number_format($citationInfo['ProcessingTotal'], 2)); ?>" />
		<br/>
		<em><strong style="color: #FF0000">Pay This Amount:</strong></em> <span id="totalPayment">$<?php echo $citationInfo['Total']; ?></span>
		<input type="hidden" name="chargetotal" id="chargetotal" value="<?php echo str_replace(',', '', $citationInfo['Total']); ?>" />
	</td>
	<td width="20%"><strong style="color: red;"><?php if (isset($customPaymentErrorMessage)) echo $customPaymentErrorMessage; ?></strong></td>
	<td width="30%" align="center"><strong>Payment Card Billing Address</strong></td>
	<td width="20%">
		<img src="/css/images/Picture2.png" width="132" height="30" />
	</td>
</tr>
<tr>
<td colspan="3"><?php echo (isset($paymentErrorMessage) ? $paymentErrorMessage : ""); ?></td>
<td rowspan="3" colspan="2"> 

<table width="100%" border="0" cellspacing="3" cellpadding="3">
<tr>
<td>Name</td>
<td><input name="name" type="text" id="billing-name" value="<?php echo (isset($_POST['name']) ? htmlentities($_POST['name']) : ""); ?>"/></td>
</tr>
<tr>
<td>Address</td>
<td><input name="address" type="text" id="billing-address"  value="<?php echo (isset($_POST['address']) ? htmlentities($_POST['address']) : ""); ?>"/></td>
</tr>
<tr>
<td>City</td>
<td><input name="city" type="text" id="billing-city"  value="<?php echo (isset($_POST['city']) ? htmlentities($_POST['city']) : ""); ?>"/></td>
</tr>
<tr>
<td>State</td>
<td>
<select id="billing-state" name="state" value="<?php echo (isset($_POST['state']) ? htmlentities($_POST['state']) : ""); ?>" >
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
<td><input id="billing-zip" name="zip" type="text" value="<?php echo (isset($_POST['zip']) ? htmlentities($_POST['zip']) : ""); ?>"/></td>
</tr>
<tr>
<td>Card Number</td>
<td><input name="card_number" type="text" id="card_number"  value="<?php echo (isset($_POST['card_number']) ? htmlentities($_POST['card_number']) : ""); ?>"/></td>
</tr>
<tr>
<td>Exp Date (Format : mm/yy)</td>
<td><input name="exp_date" type="text" id="exp_date"  value="<?php echo (isset($_POST['exp_date']) ? htmlentities($_POST['exp_date']) : ""); ?>"/></td>
</tr>
<tr>
<td>CVV Code</td>
<td><input name="cvv" type="text" id="cvv" />
<input type="hidden" name="process" value="payment"/>
	<br />
(3 digit pin on rear of card)
	</td>
	</tr>
	<tr>
	<td colspan="2" align="center">
	<p style="text-align: center;">
		<a id="submit" href="#"><img src="/css/images/pay-now-button.png" width="118" height="33" border="0" /></a>
		<span id="processing" style="display: none;">Processing...</span>
	</p>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan="3">The information contained in this website is for general information purposes only. The information is provided by Business Name and while we endeavour to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.
	<p><span class="style1">*</span><input name="terms_condition" type="checkbox" id="terms_condition" value="1" /> <label for="terms_condition">I Agree</label></p>
	</td>
	<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
	<td colspan="5">&nbsp;</td>
	</tr>
	</table>
	</div>
				<div align="center" style="padding: 80px;">&copy; <?php echo date('Y'); ?> NAND Development, LLC. All rights reserved. <a href="/disclaimer.php">Disclaimer</a>.</div>
			</div>
		</form>
	</body>
</html>

<?php

// Filters out the citations that were not checked to pay
function filterCitations() {

	// Check if we have already filtered
	if (!isset($_POST['isPaying'])) {
		$_SESSION['Citations'] = $_SESSION['citationInfo']['Citations'];
	}
	else {
		$count = count($_SESSION['Citations']);
		foreach ($_SESSION['Citations'] as $index => $array) {
			$id = $_SESSION['Citations'][$index]['id'];
			if (!isset($_POST['isPaying'][$id])) {
				unset($_SESSION['Citations'][$index]);
			}
		}
	}
}

// This will generate the table of records to pay based on type of record found.
function getTable($auth) {
	$table = "";

	$citations = $_SESSION['Citations'];

	// Make sure we have some type of record
	if (count($citations) > 0) {

		// Figure out how many columns are in this table
		$leftCols = 0;
		$rightCols = 0;

		// Display the records differently based on their type
		if ($_SESSION['IsWarrant']) {
			$leftCols = 3;
			$rightCols = 2;

			$table = <<<HED
				<table width="90%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Citation Number</td>
						<td>Case Number</td>
						<td>Charges</td>
						<td>Fine Amount</td>
					</tr>
HED;

			foreach ($citations as $index => $row) {
				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Citation_Number']}</td>
						<td>${row['Case_Number']}</td>
						<td>${row['Charges']}</td>
						<td>\$${row['Fine_Amount']}</td>
					</tr>
ROW;
			}
		}
		// Display Timepay format if this only has timepay records
		else if ($_SESSION['IsTimepay']) {
			$leftCols = 2;
			$rightCols = 2;

			$table = <<<HED
				<table width="90%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Contract Number</td>
						<td>Charges</td>
						<td width="10%">Minimum Payment</td>
					</tr>
HED;

			foreach ($citations as $index => $row) {
				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Contract_Num']}</td>
						<td>${row['ChargesList']}</td>
						<td>\$${row['Min_Payment']}</td>
					</tr>
ROW;
			}
		}
		// Display Citation format if this is only has citation records
		else if ($_SESSION['IsCitation']) {
			$leftCols = 3;
			$rightCols = 2;

			$table = <<<HED
				<table width="90%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
					<tr bgcolor="#366092" style="color:#FFF;" align="center">
						<td>Name</td>
						<td>Citation Number</td>
						<td>Case Number</td>
						<td>Charges</td>
						<td>Fine Amount</td>
					</tr>
HED;

			foreach ($citations as $index => $row) {
				$table .= <<<ROW
					<tr>
						<td>${row['Last_Name']}, ${row['First_Name']}</td>
						<td>${row['Citation_Number']}</td>
						<td>${row['Case_Number']}</td>
						<td>${row['Charges']}</td>
						<td>\$${row['Fine_Amount']}</td>
					</tr>
ROW;
			}
		}
		$columns = $leftCols + $rightCols;
		$citationInfo = $_SESSION['citationInfo'];
		$table .= <<<ADR
			<tr>
				<td colspan="$columns">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="$leftCols">
					<span id="last">${citationInfo['LastName']}</span>, 
					<span id="first">${citationInfo['FirstName']}</span><br />
					Address: <span id="address">${citationInfo['Address']}</span><br/>
					<span id="city">${citationInfo['City']}</span>,
					<span id="state">${citationInfo['State']}</span> 
					<span id="zip">${citationInfo['Zip']}</span>
				</td>
				<td colspan="$rightCols">
					<input name="sameAsBilling" type="checkbox" value="false" id="sameAsBilling" />
					 <label for="sameAsBilling">Is this your current mailing address?</label>
				 </td>
			</tr>
		</table>
ADR;
	}
	else {
		$table = <<<NON
			<table width="90%" border="0" cellspacing="4" cellpadding="4" style="border:1px solid #999">
				<tr>
					<td>
						<p>Please go back and select a record to pay.</p>
					</td>
				</tr>
			</table>
NON;
	}

	return $table;
}

?>
