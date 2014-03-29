#!/usr/local/bin/python3
import argparse
import glob
import mysql.connector
import xml.etree.ElementTree as etree

def main():
	parser = argparse.ArgumentParser()
	parser.add_argument('-d', '--directory', nargs='?', default="/usr/home/james/testdir/", help="specify the directory to import xml files from")
	args = parser.parse_args()
	directoryname = args.directory
	
	if directoryname[-1] != '/':
		directoryname = directoryname + '/'
	directoryname = directoryname + '*.xml'

	filenames = glob.glob(directoryname)
 
	config = {'user': 'test', 
	'password': 'xxxxxxxx',
	'database': 'test_database'}
	cnx = mysql.connector.connect(**config)
	cursor = cnx.cursor()
	
	temp_list = []
	temp_table = []
	
	for name in filenames:
		xmlfile = etree.parse(name)
		data = xmlfile.getroot()
		for data_row in data:
			for element in data_row:
				if element.text is not None:	
					temp_list.append(str(element.text))
				else:
					temp_list.append("")
			temp_table.append(temp_list)
			temp_list = [] 
			
	values = '(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)'
	sql_statement =  "INSERT INTO DEBTORS (cases_trustee_id, cases_court_id, cases_case_nbr, cases_f_case_nbr, cases_begin_date, cases_confirmation, end_date, payment, frequency, debtor_info_view_debtor_first_name, debtor_info_view_debtor_last_name, debtor_info_view_debtor_middle_name, debtor_info_view_d_addr_1, debtor_info_view_d_addr_2, debtor_info_view_d_city, debtor_info_view_d_state, debtor_info_view_d_zip, redacted_ssn ) VALUES %s;"
	sql_statement = sql_statement % values
	cursor.executemany(sql_statement, temp_table)
	cnx.commit()
	cursor.close()
	cnx.close()

if __name__ == '__main__':
	main()
