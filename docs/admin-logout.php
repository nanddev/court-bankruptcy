<?php
ob_start();
session_start();
session_unregister('user_id');
session_unregister('admin_email');
session_unregister('username');

session_unset();
session_destroy();
//$error_msg="Sortir avec succès";
header("Location:admin-login.php?msg=1");
exit();
?>
