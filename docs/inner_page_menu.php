<div align="center" style="padding-bottom:10px;"><br />
<span style="font-size:15px; color:#000000;"><?php
		  if($admin_user_type=='Super')
		  {?>
<a href="admin-record.php" style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Setup New Client</strong> </a> 
<?php
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="view-clients.php" style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>View Client</strong></a>&nbsp;&nbsp;&nbsp;&nbsp;|
<?php
		  if($admin_user_type=='Super')
		  {?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin-user.php" style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Setup New User</strong></a> 
<?php
}?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="view-user.php" style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>View Users</strong></a>&nbsp;&nbsp;&nbsp;&nbsp;|

		 
&nbsp;&nbsp;&nbsp;&nbsp;<a href="view-partner.php"  style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>View Partners</strong></a>
 <?php
 if($admin_user_type=='Super')
		  {?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="add-new-partner.php"  style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Setup New Partner</strong></a>
<?php
}
?>
|&nbsp;&nbsp;&nbsp;&nbsp;<!--<a href="payee_search.php"  style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Payee Search</strong></a>--><a href="admin-main.php?name=jeff"  style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Payee Search</strong></a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="reports.php"  style="font-size:15px; color:#FF0000; text-decoration:underline"><strong>Reports</strong></a>

</span>
</div>
