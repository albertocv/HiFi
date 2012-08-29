<?php

/*******************************************************************************************
    phpMySQLAutoBackup  -  Author:  http://www.DWalker.co.uk - released under GPL License
           For support and help please try the forum at: http://www.dwalker.co.uk/forum/
********************************************************************************************
Version    Date              Comment
0.2.0      7th July 2005     GPL release
0.3.0      June 2006  Upgrade - added ability to backup separate tables
0.4.0      Dec 2006   removed bugs/improved code
1.4.0      Dec 2007   improved faster version
1.5.0      Dec 2008   improved and added FTP backup to remote site
********************************************************************************************/

// Script Version
$phpMySQLAutoBackup_version	=	'1.5.0';

// Database connection data
$db_server 		= 	DB_HOST; 		// your MySQL server - localhost will normally suffice
$db 			= 	DB_NAME; 		// your MySQL database name
$mysql_username = 	DB_USER;  		// your MySQL username
$mysql_password = 	DB_PASSWORD;  	// your MySQL password

// SQL Dump File data
$save_backup_zip_file_to_server = 	1; // if set to 1 then the backup files will be saved in the folder: /phpMySQLAutoBackup/backups/
$save_backup_zip_file_path		= 	'';
$save_backup_zip_file_name		= 	'';

// E-Mail information
$from_emailaddress 	= 	'';		// your email address to show who the email is from (should be different $to_emailaddress)
$to_emailaddress 	= 	''; 	// your email address to send backup files to

//interval between backups - stops malicious attempts at bringing down your server by making multiple requests to run the backup
$time_internal	=	10;			// 3600 = one hour - only allow the backup to run once each hour

//FTP settings
//when the 4 lines below are uncommented will attempt to push the compressed backup file to the remote site ($ftp_server)
//$ftp_username=""; // your ftp username
//$ftp_password=""; // your ftp password
//$ftp_server=""; // eg. ftp.yourdomainname.com
//$ftp_path="/public_html/"; // can be just "/" or "/public_html/securefoldername/"


// Advanced settings
$newline	=	"\r\n"; 		//email attachment - if backup file is included within email body then change this to "\n"
$limit_to	=	1000000000; 	//total rows to export - IF YOU ARE NOT SURE LEAVE AS IS
$limit_from	=	0; 				//record number to start from - IF YOU ARE NOT SURE LEAVE AS IS
error_reporting(0);				// Turn off all error reporting

// Defines
define('LOCATION', dirname(__FILE__) ."/files/");

// Execute backup
require_once(LOCATION."phpmysqlautobackup.php");

?>
