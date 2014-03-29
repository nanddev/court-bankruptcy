CREATE TABLE DEBTORS (
	debtor_id INTEGER NOT NULL AUTO_INCREMENT,
	cases_trustee_id INTEGER NOT NULL,
	cases_court_id INTEGER NOT NULL, 
	cases_case_nbr VARCHAR(100) NOT NULL, 
	cases_f_case_nbr VARCHAR(100) NOT NULL, 
	cases_begin_date DATETIME NOT NULL, 
	cases_confirmation DATETIME, 
	end_date DATETIME NOT NULL, 
	payment INTEGER NOT NULL, 
	frequency VARCHAR(100) NOT NULL, 
	debtor_info_view_debtor_first_name VARCHAR(100) NOT NULL, 
	debtor_info_view_debtor_last_name VARCHAR(100) NOT NULL, 
	debtor_info_view_debtor_middle_name VARCHAR(100) NOT NULL, 
	debtor_info_view_d_addr_1 VARCHAR(100) NOT NULL, 
	debtor_info_view_d_addr_2 VARCHAR(100), 
	debtor_info_view_d_city VARCHAR(100) NOT NULL, 
	debtor_info_view_d_state VARCHAR(100) NOT NULL, 
	debtor_info_view_d_zip INTEGER NOT NULL, 
	redacted_ssn INTEGER NOT NULL,
	PRIMARY KEY (debtor_id)
);
CREATE TABLE TRUSTEES (
	id INTEGER NOT NULL AUTO_INCREMENT, 
	trustee_id INTEGER NOT NULL, /* For this to be int we need to elimnate spaces*/ 
	trustee_name VARCHAR(100) NOT NULL, 
	trustee_addr_1 VARCHAR(100) NOT NULL, 
	trustee_addr_2 VARCHAR(100) NOT NULL, 
	trustee_city VARCHAR(100) NOT NULL, 
	trustee_state VARCHAR(100) NOT NULL, /* Maybe we should have global enumeration */ 
	trustee_zip INTEGER NOT NULL, 
	trustee_title VARCHAR(100) NOT NULL, 
	PRIMARY KEY (id)
);
CREATE TABLE PAYMENTS (
	payment_id INTEGER NOT NULL AUTO_INCREMENT,
	cases_trustee_id INTEGER NOT NULL, 
	cases_court_id INTEGER NOT NULL, 
	cases_case_nbr INTEGER NOT NULL, 
	debtor_info_view_d_last_name VARCHAR(100) NOT NULL, 
	debtor_info_view_d_first_name VARCHAR(100) NOT NULL, 
	debtor_info_view_d_middle_name VARCHAR(100) NOT NULL,
	debtor_info_view_d_addr_1 VARCHAR(100) NOT NULL,
	debtor_info_view_d_addr_2 VARCHAR(100) NOT NULL,
	debtor_info_view_d_city VARCHAR(100) NOT NULL,
	debtor_info_view_d_state VARCHAR(100) NOT NULL,
	debtor_info_view_d_zip INTEGER NOT NULL,
	payment_frequency VARCHAR(100) NOT NULL, 
	payment_amount INTEGER NOT NULL, 
	payment_source VARCHAR(100) NOT NULL,
	payment_time TIMESTAMP NOT NULL,
	processing_fee INTEGER NOT NULL,
	payment_total INTEGER NOT NULL,
	redacted_ssn INTEGER NOT NULL,
	PRIMARY KEY (payment_id)
)
