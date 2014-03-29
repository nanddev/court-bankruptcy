<?php
ob_start();
include("includes/dbinfo.php");
$date_from=$_REQUEST['date_from'];
$date_to=$_REQUEST['date_to'];
$CID=$_REQUEST['CID'];

$view_id=$_REQUEST['view_id'];
$delete_admin=mysql_query("delete from orders where oid='$view_id'");
if($delete_admin)
{
header("location:payee_search.php?msg=2&date_from=$date_from&date_to=$date_to&CID=$CID");
}
?>
