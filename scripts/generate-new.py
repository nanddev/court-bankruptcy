#!/usr/bin/python3
import fileinput

keys = ['CID', 'Timestamp', 'VJSentID', 'Status', 'Record_Type', 'VJPID', 'Last_Name', 'First_Name', 'Middle_Name', 'Address', 'City', 'State', 'Zip', 'Date_of_Birth', 'Citation_Number', 'Case_Number', 'Violation_Date', 'Jurisdiction', 'Charges', 'Court_Date', 'Appearance', 'Fine_Amount', 'Offense_Sentenced', 'Contract_Num', 'Contract_Start_Date', 'Min_Payment', 'Payment_Frequency']
columns = ['CID', 'VJPID', 'Status', 'First_Name', 'Middle_Name', 'Last_Name', 'Address', 'City', 'State', 'Zip', 'Date_of_Birth', 'Jurisdiction', 'Citation_Number', 'Case_Number', 'Contract_Num', 'Court_Date', 'Charges', 'Violation_Date', 'Fine_Amount', 'Appearance', 'Offense_Sentenced', 'Contract_Start_Date', 'Min_Payment', 'Payment_Frequency', 'VJSentID', 'Timestamp']

data = []

print("START TRANSACTION;")
# put all the data into the data list as a dictionary that maps the keys to values
for line in fileinput.input():
	values = [x.strip() for x in line.split('|')] # strip the whitespace
	dictio = {k: v for (k, v) in zip(keys, values)}

	# Concatenate the tiemstamp and version together. Make sure the version has enough leading zeros so it can be accurately compared.
	# We are assuming that no version number will be more than seven digits
	#dictio['TimeVersion'] = dictio['Timestamp'] + dictio['VJSentID'].zfill(7) #removed from columns variable

	data.append(dictio)

# Do a comprehension to get a list of all records where the "Charges" column isn't "Court Automation Fee"
rec = [ dictio for dictio in data if not dictio['Citation_Number'].startswith('WR-') and dictio['Charges'] != "Court Automation Fee" ]
if (len(rec) > 0):
	sql = "INSERT INTO raw (" + ",".join(columns) + ") VALUES "

	for row in rec:
		sql = sql + '("' + '","'.join([row[x] for x in columns]) + '")'

	sql = sql.replace(")(", "),(") + ";"
	print(sql)

# Do a comprehension to get a list of all update records where the "Charges" column isn't "Court Automation Fee"
rec = [ dictio for dictio in data if not dictio['Citation_Number'].startswith('WR-') and dictio['Charges'] != 'Court Automation Fee' ]
if (len(rec) > 0):
	for row in rec:
		diff = "INSERT INTO raw (" + ",".join(columns) + ") VALUES "
		diff = diff + '("' + '","'.join([row[x] for x in columns]) + '");'

		#sql = "UPDATE citation_test SET " + ",".join([x + "=\"" + row[x] + "\"" for x in columns])
		#sql = sql + " WHERE CID = \"" + row['CID'] + "\" and Case_Number = \"" + row['Case_Number'] + "\" and TimeVersion < \"" + row['TimeVersion'] + "\";"
		print(diff)
		#print(sql)

rec = [ dictio for dictio in data if dictio['Citation_Number'].startswith('WR-') and dictio['Charges'] != 'Court Automation Fee' ]
if (len(rec) > 0):
	for row in rec:
		diff = "INSERT INTO wraw (" + ",".join(columns) + ") VALUES "
		diff = diff + '("' + '","'.join([row[x] for x in columns]) + '");'

		#sql = "UPDATE citation_test SET " + ",".join([x + "=\"" + row[x] + "\"" for x in columns])
		#sql = sql + " WHERE CID = \"" + row['CID'] + "\" and Case_Number = \"" + row['Case_Number'] + "\" and TimeVersion < \"" + row['TimeVersion'] + "\";"
		print(diff)
		#print(sql)


print("COMMIT;")
