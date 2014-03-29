#!/bin/sh

# Set up the path so that the script can find everything...
export PATH=$PATH:/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin

# Define some paths
export scripts_dir="/var/www/court.nanddevelopment.com/scripts"
export data_dir="/var/www/court.nanddevelopment.com/data/msicg"
export backup_dir="/var/www/backups/court.nanddevelopment.com/db"

# Database credentials
export db_name="traffic"
export db_user="root"
export db_pw="xxxxxxxx"

# Used to reinforce data file permissions
export ftp_user="root"
export ftp_group="wheel"

# Start the update and error logging
#date >> $scripts_dir/error.log
$scripts_dir/update-server.sh 2> $scripts_dir/tfile
#cat $scripts_dir/tfile >> $scripts_dir/error.log
#echo >> $scripts_dir/error.log
#rm $scripts_dir/tfile
