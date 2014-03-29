<?php
ob_start();
session_start();
include("includes/dbinfo.php");
include("session_check.php");

    $sql = "select * from administrator ";
    $rsSearchResults = mysql_query($sql) or die(mysql_error());
	
	$out = '';
	$fields  = mysql_list_fields('user','administrator');
	$columns = mysql_num_fields($fields);
	
	// Put the name of all fields
	for ($i = 0; $i < $columns; $i++) {
	$fields_name=mysql_field_name($fields, $i);
	$out .= '"'.$fields_name.'",';
	}
	$out .="\n";
	
	// Add all values in the table
	while ($list_values = mysql_fetch_array($rsSearchResults)) {
	for ($i = 0; $i < $columns; $i++) {
	$out .='"'.$list_values["$i"].'",';
	}
	$out .="\n";
	}
	// Output to browser with appropriate mime type, you choose ;)
	//header("Content-type: text/csv");
	//header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=admin_table.xls");
	header("Content-type: application/octet-stream");
	header("Content-type: application/csv");
    header("Content-Type: application/vnd.ms-excel");

  //  header("Content-type: text/x-csv");
	/*
	header("Content-Disposition: attachment; filename=member_list.csv");
    header("Content-type: text/x-csv");
	*/
	echo $out;
	exit;  
	?>
