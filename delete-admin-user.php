<?php
ob_start();
include("includes/dbinfo.php");

$view_id=$_REQUEST['view_id'];
$delete_admin=mysql_query("delete from administrator where id='$view_id'");
if($delete_admin)
{
header("location:view-user.php?msg=1");
}
?>
