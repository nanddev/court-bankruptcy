<?php
ob_start();
include("includes/dbinfo.php");

$view_id=$_REQUEST['view_id'];
$delete_admin=mysql_query("delete from client where id='$view_id'");
if($delete_admin)
{
header("location:view-clients.php?msg=2");
}
?>
