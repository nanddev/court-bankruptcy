SELECT * FROM citation_test
WHERE ROW(CID, Last_Name, date_of_birth) NOT IN
	(SELECT CID, Last_Name, date_of_birth 
		FROM warrant_test)
