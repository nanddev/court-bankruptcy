<?php

class TransFirst {

	/* This function is used to generate the array for the *Auth Only* TransFirst SOAP call
	 * which will make sure that they have enough money to pay
	 */
	public function generateAuthOnlyRequest($data) {
		$request = array();

		// Build merchant object
		$merchant = array();
		$merchant['id'] = $data['merchantId'];
		$merchant['regKey'] = $data['key'];
		$merchant['inType'] = '1';// "Input Type". 1 = "Merchant Web Service"
		$request['merc'] = $merchant;

		// Set the transaction to "Auth Only" mode
		$request['tranCode'] = '0';

		// Setup the card info
		$card = array();
		$card['pan'] = $data['ccNum'];
		$card['sec'] = $data['cvv'];
		$card['xprDt'] = $data['expiration']; // "YYMM" format
		$request['card'] = $card;

		// Setup the contact info
		$contact = array();
		$contact['fullName'] = $data['name'];

		if (!empty($data['phoneNumber'])) {
			$phone = array();
			$phone['type'] = '0'; // "Home phone". Required if providing a phone number
			$phone['nr'] = $data['phoneNumber'];
			$contact['phone'] = $phone;
		}

		if (!empty($data['email'])) {
			$contact['email'] = $data['email'];
		}

		$contact['addrLn1'] = $data['address1'];
		$contact['city'] = $data['city'];
		$contact['state'] = $data['state'];
		$contact['zipcode'] = $data['zipcode'];
		$request['contact'] = $contact;

		// Set the transaction amount
		$request['reqAmt'] = $data['total'];

		// Set the industry code to "eCommerce"
		$request['indCode'] = '2';

		// Check for duplicate transaction 
		// Documentation says it is in seconds, but all the examples use "6000"...
		$transactionFlags = array();
		$transactionFlags['dupChkTmPrd'] = '6000';
		$request['tranFlags'] = $transactionFlags;

		// Add the receipt number
		$orderNumber = array();
		$orderNumber['ordNr'] = $data['receiptNumber'];
		$request['authReq'] = $orderNumber;

		return $request;
	}

	/* This function is used to generate the array for the *Void* TransFirst SOAP call 
	 * which is called if the Auth Only request fails (they don't have enough money to
	 * pay
	 */
	public function generateVoidRequest($data) {
		$request = array();

		// Build merchant object
		$merchant = array();
		$merchant['id'] = $data['merchantId'];
		$merchant['regKey'] = $data['key'];
		$merchant['inType'] = '1';// "Input Type". 1 = "Merchant Web Service"
		$request['merc'] = $merchant;

		// Set the transaction to "Auth Reversal (Void)" mode
		$request['tranCode'] = '2';

		// Add the receipt number
		$originalTransactionData = array();
		$originalTransactionData['tranNr'] = $data['tranNumber'];
		$request['origTranData'] = $originalTransactionData;

		return $request;
	}

	/* This function is used to generate the array for the *Settle* TransFirst SOAP call 
	 * which is called if the Auth Only request is successful (they have enough money to 
	 * pay 
	 */
	public function generateSettleRequest($data) {
		$request = array();

		// Build merchant object
		$merchant = array();
		$merchant['id'] = $data['merchantId'];
		$merchant['regKey'] = $data['key'];
		$merchant['inType'] = '1';// "Input Type". 1 = "Merchant Web Service"
		$request['merc'] = $merchant;

		// Set the transaction to "Settle" mode
		$request['tranCode'] = '3';

		// Setup the contact info
		$contact = array();
		$contact['fullName'] = $data['name'];

		if (!empty($data['phoneNumber'])) {
			$phone = array();
			$phone['type'] = '0'; // "Home phone". Required if providing a phone number
			$phone['nr'] = $data['phoneNumber'];
			$contact['phone'] = $phone;
		}

		if (!empty($data['email'])) {
			$contact['email'] = $data['email'];
		}

		$contact['addrLn1'] = $data['address1'];
		$contact['city'] = $data['city'];
		$contact['state'] = $data['state'];
		$contact['zipcode'] = $data['zipcode'];
		$request['contact'] = $contact;

		// Set the transaction amount
		$request['reqAmt'] = $data['total'];

		// Set the industry code to "eCommerce"
		$request['indCode'] = '2';

		// Add the receipt number
		$orderNumber = array();
		$orderNumber['ordNr'] = $data['receiptNumber'];
		$request['authReq'] = $orderNumber;

		// Add the associated transaction number
		$originalTransactionData = array();
		$originalTransactionData['tranNr'] = $data['tranNumber'];
		$request['origTranData'] = $originalTransactionData;

		return $request;
	}

	/* This function is used to generate the array for the TransFirst SOAP call */
	public function generateTransactionRequest($data) {
		$request = array();

		// Build merchant object
		$merchant = array();
		$merchant['id'] = $data['merchantId'];
		$merchant['regKey'] = $data['key'];
		$merchant['inType'] = '1';// "Input Type". 1 = "Merchant Web Service"
		$request['merc'] = $merchant;

		// Set the transaction to "Authorize and Settle" mode
		$request['tranCode'] = '1';

		// Setup the card info
		$card = array();
		$card['pan'] = $data['ccNum'];
		$card['sec'] = $data['cvv'];
		$card['xprDt'] = $data['expiration']; // "YYMM" format
		$request['card'] = $card;

		// Setup the contact info
		$contact = array();
		$contact['fullName'] = $data['name'];

		if (!empty($data['phoneNumber'])) {
			$phone = array();
			$phone['type'] = '0'; // "Home phone". Required if providing a phone number
			$phone['nr'] = $data['phoneNumber'];
			$contact['phone'] = $phone;
		}

		if (!empty($data['email'])) {
			$contact['email'] = $data['email'];
		}

		$contact['addrLn1'] = $data['address1'];
		$contact['city'] = $data['city'];
		$contact['state'] = $data['state'];
		$contact['zipcode'] = $data['zipcode'];
		$request['contact'] = $contact;

		// Set the transaction amount
		$request['reqAmt'] = $data['total'];

		// Set the industry code to "eCommerce"
		$request['indCode'] = '2';

		// Check for duplicate transaction for next five seconds
		$transactionFlags = array();
		$transactionFlags['dupChkTmPrd'] = '5';
		$request['tranFlags'] = $transactionFlags;

		// Add the receipt number
		$orderNumber = array();
		$orderNumber['ordNr'] = $data['receiptNumber'];
		$request['authReq'] = $orderNumber;

		return $request;
	}
	
	/* This will return true if rspCode is "00" */
	public function isSuccessful($response) {
		$success = false;

		if (isset($response->rspCode) && $response->rspCode == '00')
			$success = true;

		return $success;
	}

	/* This will return true if rspCode is "00" */
	public function isPartialAuth($response) {
		$partialAuth = false;

		if (isset($response->rspCode) && $response->rspCode == '10')
			$partialAuth = true;

		return $partialAuth;
	}
}

?>
