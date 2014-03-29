CREATE TABLE IF NOT EXISTS debtors (
	id INTEGER NOT NULL AUTO_INCREMENT,
	trustee_id INTEGER NOT NULL,
	case_number VARCHAR(100) NOT NULL,
	full_name VARCHAR(100) NOT NULL, 
	codebtor_full_name VARCHAR(100), 
	payment_amount INTEGER NOT NULL, 
	payment_frequency VARCHAR(100) NOT NULL, 
	total_debt_left DECIMAL(10,2) NOT NULL,
	PRIMARY KEY (id)
);
/*
	it would allow for more efficient queries if debtors containted the trustee office name
*/

CREATE TABLE IF NOT EXISTS trustees (
	id INTEGER NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
);
/*
	trustees needs to contain merchant keys
*/

CREATE TABLE IF NOT EXISTS payments (
	id INTEGER NOT NULL AUTO_INCREMENT,
	trustee_id INTEGER NOT NULL,
	case_number VARCHAR(100) NOT NULL,
	full_name VARCHAR(100) NOT NULL, 
	codebtor_full_name VARCHAR(100),
	employer VARCHAR(100),
	payment_amount INTEGER NOT NULL COMMENT 'The amount of debt paid in pennies', 
	processing_fee INTEGER NOT NULL COMMENT 'The processing fee taken in pennies',
	payment_total INTEGER NOT NULL COMMENT 'The total amount charged in pennies',
	payment_time TIMESTAMP NOT NULL,
	PRIMARY KEY (id)
); 
/*
	payments needs to contain data required for the payment gateway as well
*/
