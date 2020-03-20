#
# Table structure for table 'sys_file'
#
CREATE TABLE sys_file (
	tx_admiralcloudconnector_linkhash varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'sys_file_reference'
#
CREATE TABLE sys_file_reference (
	tx_admiralcloudconnector_crop text
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	first_name varchar(50) DEFAULT '' NOT NULL,
	last_name varchar(50) DEFAULT '' NOT NULL
);
