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
1.5.4      Nov 2009   Version printed in email
********************************************************************************************/
$phpMySQLAutoBackup_version="1.5.4";
// ---------------------------------------------------------
function has_data($value)
{
 if (is_array($value)) return (sizeof($value) > 0)? true : false;
 else return (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) ? true : false;
}

function xmail ($to_emailaddress,$from_emailaddress, $subject, $content, $file_name, $backup_type, $newline, $ver)
{
 $mail_attached = "";
 $boundary = "----=_NextPart_000_01FB_010".md5($to_emailaddress);
 $mail_attached.="--".$boundary.$newline
                       ."Content-Type: application/octet-stream;$newline name=\"$file_name\"$newline"
                       ."Content-Transfer-Encoding: base64$newline"
                       ."Content-Disposition: attachment;$newline filename=\"$file_name\"$newline$newline"
                       .chunk_split(base64_encode($content)).$newline;
 $mail_attached .= "--".$boundary."--$newline";
 $add_header ="MIME-Version: 1.0".$newline."Content-Type: multipart/mixed;$newline boundary=\"$boundary\" $newline";
 $mail_content="--".$boundary.$newline."Content-Type: text/plain; $newline charset=\"iso-8859-1\"$newline"."Content-Transfer-Encoding: 7bit$newline $newline BACKUP Successful...$newline $newline Please see attached for your zipped Backup file; $backup_type $newline If this is the first backup then you should test it restores correctly to a test server.$newline $newline phpMySQLAutoBackup (version $ver) is developed by http://www.dwalker.co.uk/ $newline $newline Have a good day now you have a backup of your MySQL db  :-) $newline $newline Please consider making a donation at: $newline http://www.dwalker.co.uk/make_a_donation.php $newline (any amount is gratefully received)$newline".$mail_attached;
 return mail($to_emailaddress, $subject, $mail_content, "From: $from_emailaddress".$newline."Reply-To:$from_emailaddress".$newline.$add_header);
}

function write_backup($gzdata, $backup_file_name)
{
 $fp = fopen(SWB_BACKUP_LOCATION.$backup_file_name, "w");
 fwrite($fp, $gzdata);
 fclose($fp);
 
 //check folder is protected - stop HTTP access
 if (!file_exists(SWB_BACKUP_LOCATION.'.htaccess'))
 {
  $fp = fopen(SWB_BACKUP_LOCATION.'.htaccess', "w");
  fwrite($fp, "deny from all");
  fclose($fp);
 }
}

class transfer_backup
{
      function transfer_data($ftp_username,$ftp_password,$ftp_server,$ftp_path,$filename)
      {
       if (function_exists('curl_exec'))
       {
        $file = SWB_BACKUP_LOCATION.$filename;
        $fp = fopen($file, "r");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "ftp://$ftp_username:$ftp_password@$ftp_server.$ftp_path".$filename);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        curl_setopt($ch, CURLOPT_TRANSFERTEXT, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']." - via phpMySQLAutoBackup");
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        if (empty($info['http_code'])) die("ERROR - Failed to transfer backup file to remote ftp server");
        else
        {
         $http_codes = parse_ini_file(LOCATION."http_codes.ini");
         if ($info['http_code']!=226) echo "ERROR - server response: <br />".$info['http_code']
                                            ." " . $http_codes[$info['http_code']]."<br><br>"
                                            ."for more detail please refer to: http://www.w3.org/Protocols/rfc959/4_FileTransfer.html"                                            ;
        }
        curl_close($ch);
       }
      }
}
?>