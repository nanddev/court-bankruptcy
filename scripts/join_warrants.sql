SELECT *
FROM citation_test
LEFT JOIN warrant_test
ON citation_test.Last_Name = warrant_test.Last_Name AND citation_test.date_of_birth = warrant_test.date_of_birth;
