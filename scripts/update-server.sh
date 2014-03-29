#!/bin/sh

##### New chain of commands with better sorting/dupe removing
#grep -Ph '^\d' $court/*.txt | sort -nurt \| | cut -d \| -f 2,4-25 | uniq | tac | sed -e 's/^/(cid)|/' -e 's/\"/\\\"/g' > $court/records.tmp
#python3 $scripts_dir/generate-sql.py $court/records.tmp > $court/update.sql

# Backup the database first
#mysqldump -u $db_user -p$db_pw $db_name | gzip -c > $backup_dir/$(date +%Y-%m-%d-%H.%M.%S).sql.gz

# Iterate through each folder and process

mysql -u $db_user -p$db_pw $db_name < clean_raw.sql
for court in $(find $data_dir/* -type d -prune)
do
	# Change to this court's directory
	cd $court

	# Get the court id from a hidden file called ".courtid"
	court_id=$(cat .courtid) 

	# Get the court name from a hidden file called ".courtname"
	court_name=$(cat .courtname) 

	echo "Processing $court_name..."

	# Modify the data
	grep -HE 'New Record|Change Record' *.txt | sed -e 's/^\([0-9]\{4\}\)\([0-9]\{2\}\)\([0-9]\{6\}\)\.txt:/'"$(echo $court_id)"'|20\2\1\3|/' -e 's/\"/\\\"/g' | sort -t \| -k 2n -k 3n > records.tmp

	# Generate a SQL script based on the modified data
	python3.3 $scripts_dir/generate-new.py records.tmp > script.sql

	# Apply the SQL script
	mysql -u $db_user -p$db_pw $db_name < script.sql
	# Cleanup
	#rm *.tmp *.sql
	#mv *.txt old_files/

	# Permissions
	#chown $ftp_user:$ftp_group old_files/*.txt
	#chmod 050 old_files/*.txt
done
mysql -u $db_user -p$db_pw $db_name < /var/www/court.nanddevelopment.com/scripts/raw_update.sql
mysql -u $db_user -p$db_pw $db_name < /var/www/court.nanddevelopment.com/scripts/insert_unique.sql
	
# Clean up the database
#mysql -u $db_user -p$db_pw $db_name < $scripts_dir/clean.sql

echo "Done."
