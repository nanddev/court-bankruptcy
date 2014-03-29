truncate table raw_temp;
insert raw_temp select * from raw;

UPDATE raw, raw_temp
SET	raw.VJPID = raw_temp.VJPID,
	raw.Status = raw_temp.Status,	
	raw.First_Name = raw_temp.First_Name,
	raw.Middle_Name = raw_temp.Middle_Name,
	raw.Last_Name = raw_temp.Last_Name,
	raw.Address = raw_temp.Address,
	raw.City = raw_temp.City,
	raw.State = raw_temp.State,
	raw.Zip = raw_temp.Zip,
	raw.date_of_birth = raw_temp.date_of_birth,
	raw.Jurisdiction = raw_temp.Jurisdiction,
	raw.Citation_Number = raw_temp.Citation_Number,
	raw.Court_Date = raw_temp.Court_Date,
	raw.Charges = raw_temp.Charges,
	raw.Violation_Date = raw_temp.Violation_Date,
	raw.Fine_Amount = raw_temp.Fine_Amount,
	raw.Appearance = raw_temp.Appearance,
	raw.Offense_Sentenced = raw_temp.Offense_Sentenced,
	raw.Contract_Start_Date = raw_temp.Contract_Start_Date,
	raw.Payment_Frequency = raw_temp.Payment_Frequency,
	raw.VJSentID = raw_temp.VJSentID,
	raw.Timestamp = raw_temp.Timestamp
WHERE raw.CID = raw_temp.CID
	AND raw.Case_Number = raw_temp.Case_Number
	AND (
		(raw.Timestamp < raw_temp.Timestamp)
		OR (
			(raw.VJSentID < raw_temp.VJSentID)
			AND (raw.Timestamp = raw_temp.Timestamp))
	)
