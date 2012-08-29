<?php

// Main class
class SWB {
	
	// Register settings page
	public function buildAdminMenu(){
		
		// Add page to the admin options
		add_options_page('Simple Wordpress Backup', 'WP Backup', 'manage_options', 'swb', array('SWB', 'pageSettings'));
	}
	
	// User settings, twitter oauth authentication (GUI)
	public function pageSettings(){
		
		// Backup script configuration
		
		// Script Version
		$phpMySQLAutoBackup_version	=	'1.5.0';
		
		// Database connection data
		$db_server 		= 	DB_HOST; 		// your MySQL server - localhost will normally suffice
		$db 			= 	DB_NAME; 		// your MySQL database name
		$mysql_username = 	DB_USER;  		// your MySQL username
		$mysql_password = 	DB_PASSWORD;  	// your MySQL password
		
		// SQL Dump File data
		$save_backup_zip_file_to_server = 	1; // if set to 1 then the backup files will be saved in the folder: /phpMySQLAutoBackup/backups/
		$backup_file_location			= 	dirname(__FILE__).'/backups/';
		
		// Needs to be simplified in the next release!
			$backup_file_name 				= 	'swb_db_'.preg_replace('#[^a-z]#', '', preg_replace('#[http|https]#', '', strtolower(get_bloginfo('wpurl')))).'_'.date("Ymd_his").'.sql.gz';
		
		//echo $backup_file_location;
		
		// E-Mail information
		$from_emailaddress 	= 	'';		// your email address to show who the email is from (should be different $to_emailaddress)
		$to_emailaddress 	= 	''; 	// your email address to send backup files to
		
		//interval between backups - stops malicious attempts at bringing down your server by making multiple requests to run the backup
		$time_internal	=	1;			// 3600 = one hour - only allow the backup to run once each hour
		
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
		//error_reporting(E_ALL);			// Enable all error reporting
		error_reporting(0);			// Turn off all error reporting
		
		// Defines
		define('LOCATION', dirname(__FILE__) ."/phpmysqlautobackup/files/");
		define('SWB_BACKUP_LOCATION', $backup_file_location);
		
		
		// Display settings form
		echo '<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2>Simple Wordpress Backup</h2></div>';
		
		// Create Backup
		if($_GET['swbAction'] == 'createBackup'){
			
			// Execute backup
			require_once(LOCATION."phpmysqlautobackup.php");
	
			// Show success message
			echo '<div id="message" class="updated"><p>Database Backup has been created!</p></div>';
		}
		
		// Delete Backup
		if($_GET['swbAction'] == 'deleteBackup'){
			
			// Variables
			$file = base64_decode($_GET['file']);
			
			// Delete file
			unlink(SWB_BACKUP_LOCATION.$file);
			
			// Show success message
			echo '<div id="message" class="updated"><p>Database Backup has been deleted!</p></div>';
		}
		
		echo '
		
		<h2>Create new Database Backup</h2>
		
		<p>Create a full Batabase Backup by just clicking the button below.</p>
		<p>Attention: this plugin does not allow to restore a Database in the current version!</p>
		
		<input class="button" value="Create Database Backup" onclick="document.location.href=\'options-general.php?page=swb&swbAction=createBackup\'" type="button">
		
		<p>&nbsp;</p>
		
		<h2>Manage existing Database Backups</h2>
		
		<p>Download or delete Database Backups below.</p>
		
		<table>
		<tr>
			<td style="padding-right: 10px;"><b>Database Backup Filename</b></td>
			<td style="padding-right: 10px;">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		
		';
		
		$fp = opendir(SWB_BACKUP_LOCATION);
		
		while($file = readdir($fp)){
			
			if($file != ".." and $file != "." and $file != '.htaccess' and !is_dir($file)){
				
				echo '
				<tr>
					<td style="padding-right: 10px;">'.$file.'</td>
					<td style="padding-right: 10px;">
						<a href="'.get_bloginfo('wpurl').'/wp-content/plugins/simple-wordpress-backup/backups/'.$file.'"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/simple-wordpress-backup/images/script_go.png" alt="" titel="Download" style="border: 0;" /></a></td>
					<td>
						<a href="javascript:if(confirm(\'Are you sure?\')===true){document.location.href=\'options-general.php?page=swb&swbAction=deleteBackup&file='.base64_encode($file).'\'}"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/simple-wordpress-backup/images/delete.png" alt="" titel="Delete" style="border: 0;" /></td>
				</tr>
				';
			}
		}
		
		closedir($fp);
		
		echo '
		
		</table>
		
		<p>&nbsp;</p>
		
		<h3>Donate</h3>
			
		<p>Help us to keep this plugin up-to-date, to add more features, to give free support and to fix bugs with just a small amount of money.</p>
		
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="Q97G6AMJHPQ3G">
		<input type="image" src="https://www.paypal.com/en_US/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		
		
		

		<p>&nbsp;</p>
		
		</div>
		';
	}
}

?>