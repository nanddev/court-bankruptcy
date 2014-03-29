#!/usr/local/bin/python3
import argparse
import mysql.connector
from xml.etree.ElementTree import Element, SubElement, tostring
from datetime import date, timedelta, datetime

def main():
	parser = argparse.ArgumentParser()
	parser.add_argument('-d', '--directory', default="/usr/home/james/", help="the directory for output of payment.xml")
	
	parser.add_argument('-t', '--trusteeID', default="*", help="the ID of the trustee office payments")
	args = parser.parse_args()
	directoryname = args.directory
	tid = args.trusteeID

	if directoryname[-1] != "/":
		directoryname = directoryname + "/"
	
	config = {'user': 'test',
	'password': 'xxxxxxxx', 
	'database': 'test_database'}
	cnx = mysql.connector.connect(**config)
	cursor = cnx.cursor()
	
	yesterday = date.today() - timedelta(1)
	
	if tid == "*":
		select_query = ("SELECT * FROM PAYMENTS WHERE LEFT(payment_time, 10)=%s;")
		cursor.execute(select_query, [yesterday])
	else:
		select_query = ("SELECT * FROM PAYMENTS WHERE cases_trustee_id=%s AND LEFT(payment_time, 10) = %s;")
		cursor.execute(select_query, [tid, yesterday])
	
	data = Element('data')
	datarow = Element('datarow')
	cases_trustee_id = SubElement(datarow, 'cases_trustee_id')
	cases_court_id = SubElement(datarow, 'cases_court_id')
	cases_case_nbr = SubElement(datarow, 'cases_case_nbr')
	debtor_info_view_d_last_name = SubElement(datarow, 'debtor_info_view_d_last_name')
	debtor_info_view_d_first_name = SubElement(datarow, 'debtor_info_view_d_first_name')
	payment_amount = SubElement(datarow, 'payment_amount')
	payment_source = SubElement(datarow, 'payment_source')
	
	row = cursor.fetchone()
	while row is not None:
		cases_trustee_id.text = str(row[1])
		cases_court_id.text = str(row[2])
		cases_case_nbr.text = str(row[3])	
		debtor_info_view_d_last_name.text = row[4]
		debtor_info_view_d_first_name.text = row[5]
		payment_amount.text = str(row[13])
		payment_source.text = row[14]
		data.append(datarow)
		row = cursor.fetchone()

	cursor.close()
	cnx.close()
	
	f = open(directoryname + 'payment.xml', 'w')
	f.write('<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>')
	f.write(tostring(data).decode('UTF-8'))
	f.close()

if __name__ == '__main__':
	main()
