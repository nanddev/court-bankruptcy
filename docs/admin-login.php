<?php

session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/dbinfo-pdo.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Auth.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Online Court Payment System Demo</title>
		<link rel="stylesheet" type="text/css" href="/css/style.css"/>
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
		<script type="text/javascript">
			function LoginForm_Validate() {
				if(document.form1.username.value=="") {
					alert("Pleae Enter Your Username");
					document.form1.username.focus();
					return false;
				}
				if(document.form1.password.value=="") {
					alert("Pleae Enter Your Password");
					document.form1.password.focus();
					return false;
				}
				else {
					return true;
				}
			}
		</script>
	</head>
<?php
if (!empty($_POST['email']) && !empty($_POST['password'])) {
	$Auth = new Auth();
	$Auth->login($_POST['email'], $_POST['password'], $_POST['referer']);
}
?>
	<body>
		<div id="main">
			<div class="head">
				<div style="width:20%; float:left">
					<strong>Administrative Login</strong>
				</div>                
				<div align="right" style="width:70%; float:left"></div>
			</div>
			<div class="login">
				<p align="center">Please Enter Username and Password Below</p>
<?php

if(!empty($_REQUEST['msg'])) {
	$msg = "";
	switch($_REQUEST['msg']) {
		case '1':
			$msg = "You have been successfully logged in!";
			break;
		case '2':
			$msg = "You have entered an incorrect username or password. Please try again.";
			break;
		case '3':
			$msg = "Please login!";
			break;
		case '4':
			$msg = "Admin user successfully logged out.";
			break;
	}
	if ($msg != "") {
		echo <<< ERR
<p align="center" style="color:#FF0000;">$msg</p>
ERR;
	}
}

?>
				<div style="width:30%; float:left" align="right">
					<a href="index.php">
						<img src="" width="165" height="123" border="0" />
					</a>
				</div>                
				<div align="left" style="width:50%; float:left; margin-left:20px;">
					<form name="form1" id="form1" method="post" action="">
						<input type="hidden" name="referer" value="<?php echo isset($_REQUEST['referer']) ? $_REQUEST['referer'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') ?>" />
						<table width="70%" border="0" cellspacing="6" cellpadding="6" style="border:1px solid #333">
							<tr>
								<td>
									<label for="email">Email</label>
								</td>
								<td>
									<input type="text" name="email" id="email" />
								</td>
							</tr>
							<tr>
								<td>
									<label for="password">Password</label>
								</td>
								<td>
									<input type="password" name="password" id="password" />
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="submit" name="LoginForm" id="LoginForm" value="Login" onClick="return LoginForm_Validate();" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<div style="clear:both"></div>
			<div align="center" style=" padding:160px;">All rights reserved 2013. <a href="/disclaimer.php">Disclaimer</a></div>
		</div>
	</body>
</html>
