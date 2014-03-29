<?php

session_start();

// Delete any existing sessions...
session_unset();
session_destroy();

// Start a clean session
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo-bankruptcy.php");

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	try {
		$trusteeInfo = explode(':', $_POST['trusteeInfo']);
		$trusteeId = $trusteeInfo[0];
		$trusteeName = $trusteeInfo[1];
		$caseNumber = $_POST['caseNumber'];

		// Look for the case number in the debtors table
		$statement = $db->prepare("select * from debtors where trustee_id = :trusteeId and case_number = :caseNumber;");
		$statement->execute(array(':trusteeId' => $trusteeId, ':caseNumber' => $caseNumber));
		$cases = $statement->fetchAll();
		
		$rowCount = count($cases);
		if ($rowCount > 1) {
			$errorMessage = "Your case number returned multiple results. Please call us at 1-877-689-5144 for assistance in making your payment.";
		}
		else if ($rowCount == 0) {
			$errorMessage = "Your case number wasn't found in our database. Please check your case number and trustee office or call us at 1-877-689-5144 for assistance in making your payment.";
		}
		else if ($rowCount == 1) {
			// We found exactly one record so save info to session and redirect the user to the checkout page.
			$_SESSION['FullName'] = $cases['0']['full_name'];
			$_SESSION['CodebtorFullName'] = $cases['0']['codebtor_full_name'];
			$_SESSION['TrusteeId'] = $trusteeId;
			$_SESSION['TrusteeName'] = $trusteeName;
			$_SESSION['CaseNumber'] = $caseNumber;
			$_SESSION['SuggestedAmount'] = number_format($cases['0']['payment_amount'], 2);
			$_SESSION['PaymentAmount'] = number_format($cases['0']['payment_amount'], 2);
			$_SESSION['SuggestedFrequency'] = $cases['0']['payment_frequency'];
			$_SESSION['OutstandingBalance'] = number_format($cases['0']['total_debt_left'], 2);
			//echo '<pre>'; echo var_dump($_SESSION); echo '</pre>'; exit;
			header("Location: checkout.php");
		}
		else {
			throw new Exception('An error occured when looking up your case number. Please call us at 1-877-689-5144 for assistance in making your payment.');
		}
	}
	catch (Exception $e) {
		$errorMessage = $e->getMessage();
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <style type="text/css"></style>
                <title>Online Court Payment System Demo</title>
                <link rel="stylesheet" type="text/css" href="/css/style.css">
                <link rel="stylesheet" href="/css/jquery.tooltip.css" type="text/css">
                <script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
                <script type="text/javascript" src="/js/lib/jquery.tooltip.js"></script>
                <script type="text/javascript">
                        $(document).ready(function(){
                                $("div.item").tooltip();

                                $('#findcase').submit(function(e) {
                                        if ($('#trusteeOffice #default').is(':selected')) {
                                                alert("Please select a trustee office.");
                                                e.preventDefault;
                                                return false;
                                        }
                                        if (!$.trim($('#caseNumber').val())) {
                                                alert("Pleae enter your case number.");
                                                e.preventDefault;
                                                return false;
                                        }
                                });
                        });
                </script>
        </head>
        <body>
                <div id="main">
                        <div class="head"></div>
                        <div class="login">
                                <p align="center">Complete the fields below to find your bankruptcy case number.</p>
                                <div style="width:30%; float:left" align="right">
                                        <a href="/index.php">
                                                <img src="" width="165" height="123" border="0">
                                        </a>
                                </div>
                                <div align="left" style="width:50%; float:left; margin-left:20px;">
                                        <form id="findcase" method="post" action="index.php">
                                                <table width="90%" border="0" cellspacing="6" cellpadding="6" style="border:1px solid #333">
																												<tbody>
                                                                <tr>
                                                                        <td>Select Trustee Office: </td>
                                                                        <td>
                                                                                <select id="trusteeOffice" name="trusteeInfo">
                                                                                        <option id="default" value="-1">Select ...</option>
                                                                                        <option value="1:Barry C. Zimmerman" selected>Barry C. Zimmerman</option>
                                                                                </select>
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                        <td>Case Number: </td>
                                                                        <td>
																																					<input type="text" id="caseNumber" name="caseNumber" value="<?php if (isset($_POST['caseNumber'])) echo $_POST['caseNumber'] ?>" />
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                        <td align="center">
                                                                                <div class="item">
                                                                                        <strong style="color:#F00">Help</strong>
                                                                                        <div class="tooltip_description" style="display:none" align="center">
                                                                                                Customer Support<br>
                                                                                                <b>1-800-123-4567<br>
                                                                                                email us: support@example.com</b>
                                                                                        </div>
                                                                                </div>
                                                                        </td>
                                                                        <td>
                                                                                <input type="submit" value="Find Case" />
                                                                        </td>
                                                                </tr>
                                                        </tbody>
                                                </table>
                                        </form>
                                </div>
                                <div style="clear:both"></div>
				<?php 
					if (!empty($errorMessage)) {
						echo <<< AAA
						<div style="margin: 20px; padding: 10px; border: 1px solid Red; background-color: LightGoldenRodYellow;">
							<span style="color: Red; font-weight: bold; font-size: 14px;">$errorMessage</span>
						</div>
AAA;
					}
				?>
                                <div align="center" style=" padding:160px;">All rights reserved 2013. <a href="/disclaimer.php">Disclaimer</a></div>
                        </div>
                </div>
        </body>
</html>
