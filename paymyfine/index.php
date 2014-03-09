<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Auth.php");

$Auth = new Auth();

?>

<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<style type="text/css">
			div.item { text-align: center; padding-top: 0px; }
			div#item_1 { position: relative; left: -45px; }
		</style>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/css/jquery.tooltip.css" />
		<script type="text/javascript" src="/js/lib/jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.tooltip.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.validate.min.js"></script>
		<script type="text/javascript" src="/js/lib/jquery.formShowHide.js"></script>
		<script type="text/javascript" src="/js/validate.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("div.item").tooltip();
			});
		</script>
	</head>
	<body>
		<div id="main">
			<div class="head">
				<div align="right" style="width: 70%; float: left;">
					<a href="/index.php">Home</a> |
					<a href="/admin-login.php">Admin Login</a>
				</div>
			</div>
			<div class="login">
				<div style="width: 30%; float: left;" align="right">
					<a href="/index.php">
						<img src="" width="165" height="123" border="0" />
					</a>
				</div>                
				<div align="center" style="width:50%; float:left; margin-left:20px;">
					<form name="form" id="form" method="post" action="results.php">
						<table width="90%" border="0" cellspacing="6" cellpadding="6" style="border:1px solid #333">
							<tr>
								<td width="40%" valign="top">
									<label for="courtId">Which court has your record?*</label>
								</td>
								<td width="60%">
									<select id="courtId" name="courtId" class="show-hide">
										<option value="" data-hide-id="knowsCitation lastName dob citation">Select</option>	
										<option value="21" data-show-id="knowsCitation">Benton County, Cave Springs</option>
										<option value="9" data-show-id="knowsCitation">Benton County, Pea Ridge</option>
										<option value="22" data-show-id="knowsCitation">Marion County District Court</option>
										<option value="20" data-show-id="knowsCitation">Monroe County, Clarendon</option>
										<option value="14" data-show-id="knowsCitation">Monroe County, Brinkley</option>
										<option value="13" data-show-id="knowsCitation">Ouachita County, Camden</option>
										<option value="10" data-show-id="knowsCitation">Pulaski County, Sherwood</option>
										<option value="17" data-show-id="knowsCitation">Saline County, Bryant</option>
										<option value="12" data-show-id="knowsCitation">Saline County, Haskell</option>
										<option value="19" data-show-id="knowsCitation">Saline County, Shannon Hills</option>
										<option value="11" data-show-id="knowsCitation">White County, Beebe</option>
										<option value="18" data-show-id="knowsCitation">White County, McRae</option>
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top">
									<label for="knowsCitation">Do you know your citation number?*</label>
								</td>
								<td>
									<select id="knowsCitation" name="knowsCitation" class="show-hide">
										<option value="">Select</option>	
										<option data-show-id="citation">Yes</option>
										<option data-show-id="lastName dob">No</option>
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top">
									<label for="lastName">What is your last name?</label>
								</td>
								<td>
									<input type="text" name="lastName" id="lastName" />
								</td>
							</tr>
							<tr>
								<td align="left" valign="top">
									<label for="dob">What is your date of birth?</label>
								</td>
								<td align="left" valign="top">
									<input type="text" name="dob" id="dob" />
									<br />
									"Please enter date example<br />
									(MONTH/DAY/YEAR): 4/6/1966"
								</td>
							</tr>
							<tr>
								<td valign="top">
									<label for="citation">What is your citation number?</label>
								</td>
								<td>
									<input type="text" name="citation" id="citation" />
								</td>
							</tr>
							<tr>
								<td>
									<div id="item_1" class="item">
										<strong style="color:#F00">Help</strong>
										<div class="tooltip_description" style="display:none" title="" align="center">
											Customer Support <br />
											<b>867-5309<br />Email us: demoemail@nanddevelopment.com</b>
										</div>
									</div>
								</td>
								<td>
									<input type="submit" id="submit" value="Search" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<div style="clear:both"></div>
			<div align="center" style=" padding:160px;">
				All rights reserved <?php echo date('Y'); ?>. <a href="/disclaimer.php">Disclaimer</a>
			</div>
		</div>
	</body>
</html>
