<?PHP
//@ignore_user_abort(true); //no user abort
@set_time_limit(0); //not time limit for restore.
// Set default timezone in PHP 5.
if (function_exists('date_default_timezone_set'))
	date_default_timezone_set('UTC');
//default vars
$backwpup['STEP']='start';
$backwpup['CHARSET']='UTF-8';
$backwpup['ABSPATH']=dirname(__FILE__). '/';
$backwpup['WPCONFIG']['DB_NAME']='';
$backwpup['WPCONFIG']['DB_USER']='';
$backwpup['WPCONFIG']['DB_PASSWORD']='';
$backwpup['WPCONFIG']['DB_HOST']='';
$backwpup['WPCONFIG']['DB_CHARSET']='utf8';
$backwpup['WPCONFIG']['DB_COLLATE']='';
$backwpup['WPCONFIG']['WP_SITEURL']='';
$backwpup['WPCONFIG']['table_prefix']='';
//check write accesses
if (!is_writable($backwpup['ABSPATH']))
	die('this folder must writable!');
//load vars if exists
if (is_file($backwpup['ABSPATH'].'.backwpup_restore'))
	$backwpup=unserialize(file_get_contents($backwpup['ABSPATH'].'.backwpup_restore'));
//check is wp-config.php in this folder to set abs path
if (!(is_file($backwpup['ABSPATH'].'wp-config.php')) and !(is_file(dirname($backwpup['ABSPATH']).'/wp-config.php') and !is_file($backwpup['ABSPATH'].'wp-settings.php')))
	die('wp-config.php not found copy script to blog root folder!');
// set character encoding
if (ini_get('mbstring.func_overload') && function_exists( 'mb_internal_encoding' ) ) {
	if ( !@mb_internal_encoding( $backwpup['CHARSET'] ) )
		mb_internal_encoding( 'UTF-8' );
}
//get start parameters
if ($backwpup['STEP']=='start') {
	//get data from wp-config.php
	if (is_file($backwpup['ABSPATH'].'wp-config.php'))
		$wpconfig=file_get_contents($backwpup['ABSPATH'].'wp-config.php');
	if (is_file(dirname($backwpup['ABSPATH']).'/wp-config.php') and !is_file($backwpup['ABSPATH'].'wp-settings.php'))
		$wpconfig=file_get_contents(dirname($backwpup['ABSPATH']).'/wp-config.php');
	$tokens=token_get_all($wpconfig);
	foreach ($tokens as $tokenarrays) {
		if ($tokenarrays[0]==T_CONSTANT_ENCAPSED_STRING or $tokenarrays[0]==T_VARIABLE) {
			//remove ' or " from first and last char if not a variable
			if ($tokenarrays[0]==T_VARIABLE)
				$value=$tokenarrays[1];
			else
				$value=substr($tokenarrays[1],1,-1);
			if (in_array($value,array('DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','DB_CHARSET','DB_COLLATE','WP_SITEURL','$table_prefix'))) {
				$variable=str_replace('$','',$value);
				unset($value);
			}
			if (isset($variable) and isset($value)) {
				$backwpup['WPCONFIG'][$variable]=$value;
				unset($variable);
			}
		}
	}
	$backwpup['STEP']='user1';
}
//user query
if ($backwpup['STEP']=='user1' and empty($_POST['nextstep'])) {
	//get dump file names
	if ( $dir = opendir($backwpup['ABSPATH'])) {
		$sqlfiles=array();
		while (($file = readdir( $dir ) ) !== false ) {
			if (strtolower(substr($file,-4))==".sql" or strtolower(substr($file,-7))==".sql.gz")
				$sqlfiles[]=$file;
		}
		closedir( $dir );
	}
	?>
	<form action="" method="post">
	Select file to restore:<br />
	<?PHP  foreach ($sqlfiles as $file) {?>
		<input type="radio" name="sqlfile" value="<?PHP echo $file;?>" /> <?PHP echo $file;?><br />
	<?PHP  } ?>
	<input type="hidden" name="nextstep" value="user2" /><br />
	<input type="submit" value="Next" /><br />
	</form>
	<?PHP
}
if ($_POST['nextstep']=='user2') {
	$backwpup['sqlfile']=$_POST['sqlfile'];
	if (strtolower(substr($backwpup['sqlfile'],-4))==".sql")
		$file = fopen ($backwpup['sqlfile'], "r");
	if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz")
		$file = gzopen ($backwpup['sqlfile'], "r");
	while (true){
		if (strtolower(substr($backwpup['sqlfile'],-4))==".sql") {
			if (feof($file))
				break;
			$line = trim(fgets($file));
		}
		if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz") {
			if (gzeof($file))
				break;
			$line = trim(gzgets($file));
		}

		if (substr($line,0,12)=="-- Blog URL:")
			$backwpup['OLD']['BLOGURL']=trim(substr($line,13));
		if (substr($line,0,16)=="-- Blog ABSPATH:")
			$backwpup['OLD']['ABSPATH']=trim(substr($line,17));
		if (substr($line,0,16)=="-- Table Prefix:")
			$backwpup['OLD']['TABELPREFIX']=trim(substr($line,17));
		if (substr($line,0,16)=="-- Blog Charset:")
			$backwpup['OLD']['BLOGCHARSET']=trim(substr($line,17));
		if (substr($line,0,20)=="-- Database charset:")
			$backwpup['OLD']['DBCHARSET']=trim(substr($line,21));
		if (substr($line,0,20)=="-- Database collate:")
			$backwpup['OLD']['DBCOLLATE']=trim(substr($line,21));
		if (isset($backwpup['OLD']['BLOGURL']) and isset($backwpup['OLD']['ABSPATH']) and isset($backwpup['OLD']['TABELPREFIX']) and isset($backwpup['OLD']['BLOGCHARSET']) and isset($backwpup['OLD']['DBCHARSET']) and isset($backwpup['OLD']['DBCOLLATE']))
			break;
	}
	if (strtolower(substr($backwpup['sqlfile'],-4))==".sql")
		fclose($file);
	if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz")
		gzclose($file);
		
	file_put_contents($backwpup['ABSPATH'].'.backwpup_restore',serialize($backwpup));
	?>
	<form action="" method="post">
	DB Name: <input type="text" name="dbname" value="<?PHP echo $backwpup['WPCONFIG']['DB_NAME'];?>" /><br />
	DB User: <input type="text" name="dbuser" value="<?PHP echo $backwpup['WPCONFIG']['DB_USER'];?>" /><br />
	DB Password: <input type="password" name="dbpassword" value="<?PHP echo $backwpup['WPCONFIG']['DB_PASSWORD'];?>" /><br />
	DB Host: <input type="text" name="dbhost" value="<?PHP echo $backwpup['WPCONFIG']['DB_HOST'];?>" /><br />
	DB Charset: <input type="text" name="dbcharset" value="<?PHP echo $backwpup['WPCONFIG']['DB_CHARSET'];?>" /> <i>(must same as before!) Old: <?PHP echo $backwpup['OLD']['DBCHARSET'];?> Default: utf8</i><br />
	DB Collate: <input type="text" name="dbcollate" value="<?PHP echo $backwpup['WPCONFIG']['DB_COLLATE'];?>" /> <i>(must same as before!) Old: <?PHP echo $backwpup['OLD']['DBCOLLATE'];?></i><br />
	DB Table prefix: <input type="text" name="tableprefix" value="<?PHP echo $backwpup['WPCONFIG']['table_prefix'];?>" /> <i>(must same as before!) Old: <?PHP echo $backwpup['OLD']['TABELPREFIX'];?></i><br />
	New Siteurl: <input type="text" name="wpsiteurl" value="<?PHP echo empty($backwpup['WPCONFIG']['WP_SITEURL']) ? $backwpup['OLD']['BLOGURL'] : $backwpup['WPCONFIG']['WP_SITEURL'];?>" /> <i>(if moving to new url) Old: <?PHP echo $backwpup['OLD']['BLOGURL'];?></i><br />
	Blog Charset: <input type="text" name="blogcharset" value="<?PHP echo $backwpup['OLD']['BLOGCHARSET'];?>" /> <i>(must same as before!) Old: <?PHP echo $backwpup['OLD']['BLOGCHARSET'];?> Default: UTF-8</i><br />
	<input type="hidden" name="nextstep" value="restorecheck" /><br />
	<input type="submit" value="Restore" /><br />
	</form>
	<?PHP
}
if ($_POST['nextstep']=='restorecheck') {
	$backwpup['oldabspath']=$_POST['oldabspath'];
	$backwpup['CHARSET']=$_POST['blogcharset'];
	if (empty($backwpup['CHARSET']))
		$backwpup['CHARSET']='UTF-8';
	if (ini_get('mbstring.func_overload') && function_exists( 'mb_internal_encoding' ) ) {
		if ( !@mb_internal_encoding( $backwpup['CHARSET'] ) )
			mb_internal_encoding( 'UTF-8' );
	}
	$backwpup['dbname']=$_POST['dbname'];
	$backwpup['dbuser']=$_POST['dbuser'];
	$backwpup['dbpassword']=$_POST['dbpassword'];
	$backwpup['dbhost']=$_POST['dbhost'];
	$backwpup['dbcharset']=$_POST['dbcharset'];
	$backwpup['dbcollate']=$_POST['dbcollate'];
	$backwpup['tableprefix']=$_POST['tableprefix'];
	$backwpup['wpsiteurl']=$_POST['wpsiteurl'];
	if ($backwpup['dbcharset']!=$backwpup['OLD']['DBCHARSET'])
		echo "You must set 'define('DB_CHARSET', '".$backwpup['OLD']['DBCHARSET']."');' in wp-config.php!<br />";
	if ($backwpup['dbcollate']!=$backwpup['OLD']['DBCOLLATE'])
		echo "You must set 'define('DB_COLLATE', '".$backwpup['OLD']['DBCOLLATE']."');' in wp-config.php!<br />";
	if ($backwpup['tableprefix']!=$backwpup['OLD']['TABELPREFIX'])
		echo "You must set '\$table_prefix  = '".$backwpup['OLD']['TABELPREFIX']."';' in wp-config.php!<br />";			
	$backwpup['STEP']='restoring';
}
//restore database
if ($backwpup['STEP']=='restoring') {
	echo 'Continue Script if needed: <a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'</a><br />';
	// make a mysql connection
	$mysqlconlink=mysql_connect($backwpup['dbhost'], $backwpup['dbuser'], $backwpup['dbpassword'], true);
	if (!$mysqlconlink) 
		die(sprintf('No MySQL connection: %s',mysql_error()));
	//set connecten charset
	if (!empty($backwpup['dbcharset'])) {
		if ( function_exists( 'mysql_set_charset' )) {
			mysql_set_charset( $backwpup['dbcharset'], $mysqlconlink );
		} else {
			$query = "SET NAMES '".$backwpup['dbcharset']."'";
			if (!empty($backwpup['dbcollate']))
				$query .= " COLLATE '".$backwpup['dbcollate']."'";
			mysql_query($query,$mysqlconlink);
		}
	}
	//connect to database
	$mysqldblink = mysql_select_db($backwpup['dbname'], $mysqlconlink);
	if (!$mysqldblink)
		die(sprintf('No MySQL connection to database: %s',mysql_error()));

	//restore
	if (!isset($backwpup['NUMQUERYS']))
		$backwpup['NUMQUERYS']=0;
	if (!isset($backwpup['sqlquery']))
		$backwpup['sqlquery']="";
	if (!isset($backwpup['line']))
		$backwpup['line']="";
	if (!isset($backwpup['filepos']))
		$backwpup['filepos']=0;
	$filepos=0;
	
	if (strtolower(substr($backwpup['sqlfile'],-4))==".sql")
		$file = fopen ($backwpup['sqlfile'], "r");
	if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz")
		$file = gzopen ($backwpup['sqlfile'], "r");
		
	while (true){
		if (strtolower(substr($backwpup['sqlfile'],-4))==".sql") {
			if (feof($file))
				break;
			$backwpup['line'] = trim(fgets($file));
		}
		if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz") {
			if (gzeof($file))
				break;
			$backwpup['line'] = trim(gzgets($file));
		}

		$filepos++;
		if ($filepos<=$backwpup['filepos'])
			continue;
		$backwpup['filepos']=$filepos;

		if (substr($backwpup['line'],0,2)=="--" or empty($backwpup['line']))
			continue;

		$backwpup['line']=str_replace("/*!40000","", $backwpup['line']);
		$backwpup['line']=str_replace("/*!40101","", $backwpup['line']);
		$backwpup['line']=str_replace("/*!40103","", $backwpup['line']);
		$backwpup['line']=str_replace("/*!40014","", $backwpup['line']);
		$backwpup['line']=str_replace("/*!40111","", $backwpup['line']);
		$backwpup['line']=str_replace("*/;",";", trim($backwpup['line']));

		$backwpup['sqlquery'].=$backwpup['line'];  //build query
		if (substr($backwpup['sqlquery'],-1)==";") { //execute query
			$result=mysql_query($backwpup['sqlquery']);
			if ($sqlerr=mysql_error()) 
				echo '<br />ERROR: '.sprintf('BackWPup database error %1$s for query %2$s', $sqlerr, $backwpup['sqlquery'])."<br />\n";
			echo '.';
			flush();
			ob_flush();
			$backwpup['sqlquery']="";
			$backwpup['NUMQUERYS']++;
		}
		file_put_contents($backwpup['ABSPATH'].'.backwpup_restore',serialize($backwpup));
	}
	echo '<br />';
	if (strtolower(substr($backwpup['sqlfile'],-4))==".sql")
		fclose($file);
	if (strtolower(substr($backwpup['sqlfile'],-7))==".sql.gz")
		gzclose($file);
	
	echo sprintf('%1$s Database Querys done.',$backwpup['NUMQUERYS']).'<br />';
	echo 'Make changes for blogurl and ABSPATH in database if needed.<br />';
	if (!empty($backwpup['OLD']['BLOGURL']) and $backwpup['OLD']['BLOGURL']!=$backwpup['wpsiteurl']) {
		mysql_query("UPDATE `".$backwpup['tableprefix']."options` SET option_value = replace(option_value, '".rtrim($backwpup['OLD']['BLOGURL'],'/')."', '".rtrim($backwpup['wpsiteurl'],'/')."');");
		if ($sqlerr=mysql_error())
			echo 'ERROR: '.sprintf('BackWPup database error %1$s for query %2$s', $sqlerr, "UPDATE `".$backwpup['tableprefix']."options` SET option_value = replace(option_value, '".rtrim($backwpup['OLD']['BLOGURL'],'/')."', '".rtrim($backwpup['wpsiteurl'],'/')."');")."<br />\n";
		mysql_query("UPDATE `".$backwpup['tableprefix']."posts` SET post_content = replace(post_content, '".rtrim($backwpup['OLD']['BLOGURL'],'/')."', '".rtrim($backwpup['wpsiteurl'],'/')."');");
		if ($sqlerr=mysql_error())
			echo 'ERROR: '.sprintf('BackWPup database error %1$s for query %2$s', $sqlerr, "UPDATE `".$backwpup['tableprefix']."posts` SET post_content = replace(post_content, '".rtrim($backwpup['OLD']['BLOGURL'],'/')."', '".rtrim($backwpup['wpsiteurl'],'/')."');")."<br />\n";
	}
	if (!empty($backwpup['OLD']['ABSPATH']) and $backwpup['OLD']['ABSPATH']!=$backwpup['ABSPATH']) {
		mysql_query("UPDATE `".$backwpup['tableprefix']."options` SET option_value = replace(option_value, '".rtrim($backwpup['OLD']['ABSPATH'],'/')."', '".rtrim($backwpup['ABSPATH'],'/')."');");
		if ($sqlerr=mysql_error())
			echo __('ERROR:','backwpup').' '.sprintf(__('BackWPup database error %1$s for query %2$s','backwpup'), $sqlerr, "UPDATE `".$backwpup['tableprefix']."options` SET option_value = replace(option_value, '".rtrim($backwpup['OLD']['ABSPATH'],'/')."', '".rtrim($backwpup['ABSPATH'],'/')."');")."<br />\n";
	}
	echo 'Restore Done. Please delete the SQL file and this script.<br />';
	unlink($backwpup['ABSPATH'].'.backwpup_restore');
}
?>