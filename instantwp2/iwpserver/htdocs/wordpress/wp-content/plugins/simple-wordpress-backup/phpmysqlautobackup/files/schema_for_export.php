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
1.5.1      Feb 2009   improved export data - added quotes around field names
1.5.2      April 2009 improved "create table" export and added backup time start & end
1.5.3      Nov 2009   replaced PHP function "ereg_replace" with "str_replace" - all occurances
1.5.4      Nov 2009   replaced PHP function "str_replace" with "substr" line 114
********************************************************************************************/
$phpMySQLAutoBackup_version="1.5.4";
// ---------------------------------------------------------
$link = mysql_connect($db_server,$mysql_username,$mysql_password);
if ($link) mysql_select_db($db);
if (mysql_error()) exit(mysql_error($link));
//add new phpmysqlautobackup table if not there...
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'phpmysqlautobackup' "))==0)
{
   $query = "
    CREATE TABLE phpmysqlautobackup (
    id int(11) NOT NULL,
    version varchar(6) default NULL,
    time_last_run int(11) NOT NULL,
    PRIMARY KEY (id)
    ) TYPE=MyISAM;";
   $result=mysql_query($query);
   $query="INSERT INTO phpmysqlautobackup (id, version, time_last_run)
             VALUES ('1', '$phpMySQLAutoBackup_version', '0');";
   $result=mysql_query($query);
}
//check time last run - to prevent malicious over-load attempts
$query="SELECT * from phpmysqlautobackup WHERE id=1 LIMIT 1 ;";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
if (time() < ($row['time_last_run']+$time_internal)) exit();// exit if already run within last time_interval
//update version number if not already done so
if ($row['version']!=$phpMySQLAutoBackup_version) mysql_query("update phpmysqlautobackup set version='$phpMySQLAutoBackup_version'");
////////////////////////////////////////////////////////////////////////////////////

$query="UPDATE phpmysqlautobackup SET time_last_run = '".time()."' WHERE id=1 LIMIT 1 ;";
$result=mysql_query($query);

if (!isset($table_select))
{
  $t_query = mysql_query('show tables');
  $i=0;
  $table="";
  while ($tables = mysql_fetch_array($t_query, MYSQL_ASSOC) )
        {
         list(,$table) = each($tables);
         $exclude_this_table = isset($table_exclude)? in_array($table, $table_exclude) : false;
         if(!$exclude_this_table) $table_select[$i]=$table;
         $i++;
        }
}

$thedomain = $_SERVER['HTTP_HOST'];
if (substr($thedomain,0,4)=="www.") $thedomain=substr($thedomain,4,strlen($thedomain));

$buffer = '# MySQL backup created by phpMySQLAutoBackup - Version: '.$phpMySQLAutoBackup_version . "\n" .
          '# ' . "\n" .
          '# http://www.dwalker.co.uk/phpmysqlautobackup/' . "\n" .
          '#' . "\n" .
          '# Database: '. $db . "\n" .
          '# Domain name: ' . $thedomain . "\n" .
          '# (c)' . date('Y') . ' ' . $thedomain . "\n" .
          '#' . "\n" .
          '# Backup START time: ' . strftime("%H:%M:%S",time()) . "\n".
          '# Backup END time: #phpmysqlautobackup-endtime#' . "\n".
          '# Backup Date: ' . strftime("%d %b %Y",time()) . "\n";$i=0;
foreach ($table_select as $table)
        {
          $i++;
          $export = "\n" .'drop table if exists `' . $table . '`;' . "\n";

          //export the structure
          $query='SHOW CREATE TABLE `' . $table . '`';
          $rows_query = mysql_query($query);
          $tables = mysql_fetch_array($rows_query);
          $export.= $tables[1] ."; \n";

          $table_list = array();
          $fields_query = mysql_query('show fields from  `' . $table . '`');
          while ($fields = mysql_fetch_array($fields_query))
           {
            $table_list[] = $fields['Field'];
           }

          $buffer.=$export;
          // dump the data
          $query='select * from `' . $table . '` LIMIT '. $limit_from .', '. $limit_to.' ';
          $rows_query = mysql_query($query);
          while ($rows = mysql_fetch_array($rows_query)) {
            $export = 'insert into `' . $table . '` (`' . implode('`, `', $table_list) . '`) values (';
            reset($table_list);
            while (list(,$i) = each($table_list)) {
              if (!isset($rows[$i])) {
                $export .= 'NULL, ';
              } elseif (has_data($rows[$i])) {
                $row = addslashes($rows[$i]);
                $row = str_replace("\n#", "\n".'\#', $row);

                $export .= '\'' . $row . '\', ';
              } else {
                $export .= '\'\', ';
              }
            }
            $export = substr($export,0,-2) . "); \n";
            $buffer.= $export;
          }
        }
mysql_close();
$buffer = str_replace('#phpmysqlautobackup-endtime#', strftime("%H:%M:%S",time()), $buffer);
?>