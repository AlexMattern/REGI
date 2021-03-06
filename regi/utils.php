<?php

/*
    AMC Event Registration System
    Copyright (C) 2010 Dirk Koechner

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, version 3 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, please refer to
    <http://www.gnu.org/licenses/>.
*/

// Common Utilities
//

    // include global settings (applied whereever 'utils.php' included)
    include 'settings.php';
    include 'security.php';
    include 'chunks.php';
    include 'emails.php';
    include 'swift.php';

    //return date format
    function UTILdate($datestr) {
        if ($datestr == '0000-00-00')
            return 'not specified';
        else
            return date('D, M j, Y', strtotime($datestr));
    }

    function UTILshortdate($datestr) {
        if ($datestr == '0000-00-00')   //start_dates of old events will be this
            return 'not specified';
        elseif ($datestr == '')         //end_dates that have no end date will be this
            return '';
        else
            return date('D m/j/y', strtotime($datestr));
    }
    //return date+time format
    function UTILtime($timestr) {
            return date('j M Y', strtotime($timestr));
    }

    // Build top menu
    function UTILbuildmenu($num_tab) {
        $id_string = " id='selected_top_tab' ";
        $idArray = array('','','','','','','');
        $idArray[$num_tab] = $id_string;
        $bit = '';
        $bit .= IN2()."<div id='searchbox'>";
        $bit .= IN3()."<div class='top_tab' $idArray[0] style='cursor: pointer;' onClick='location.href=\"hbTrips\"' >HB Trip Listings</div>";
        $bit .= IN3()."<div class='top_tab' $idArray[1] style='cursor: pointer;' onClick='location.href=\"support\"' >Support</div>";

        if (isset($_SESSION['Suser_id'])){
            if (isset($_SESSION['Suser_type']))
                if ($_SESSION['Suser_type'] == 'ADMIN' || $_SESSION['Suser_type'] == 'LEADER')
                    $bit .= IN3()."<div class='top_tab' $idArray[2] style='cursor: pointer;' onClick='location.href=\"newEvent\"' >Create New Event</div>";

            $bit .= IN3()."<div class='top_tab' $idArray[3] style='cursor: pointer;' onClick='location.href=\".\"' >My Events</div>";
            $bit .= IN3()."\n<div class='top_tab' $idArray[4] style='cursor: pointer;' onClick='location.href=\"myProfile\"' >My Profile</div>";
            $bit .= IN3()."\n<div class='top_tab' style='cursor: pointer;' onClick='location.href=\"logout\"' >Logout</div>";
        }
        else
        {
            $bit .= IN3()."<div class='top_tab' $idArray[5] style='cursor: pointer;' onClick='location.href=\"login\"' >Login</div>";
        }

        $bit .= "<br>";
        print $bit;
        return $bit;
    }



    // DB Connect
    //

    function UTILdbconnect() {

        global $PASS_DB_HOST, $PASS_DB_USER, $PASS_DB_PASSWORD, $PASS_DB_NAME;

        $dbconn = mysql_pconnect("$PASS_DB_HOST", "$PASS_DB_USER", "$PASS_DB_PASSWORD");
        if (!$dbconn) {
            UTILlog(mysql_error());
            header("Location: ./errorPage.php?errTitle=Database Error&errMsg=" . mysql_error());
            exit();
        }
        mysql_select_db("$PASS_DB_NAME", $dbconn);
        $query = "set sql_mode = 'traditional';";
        $result = mysql_query($query);
        //Complain about any errors that just happened.
        //We REALLY want that query to go through, otherwise we have no way of enforcing proper date format.
        if (mysql_error($dbconn))
            UTILdberror($query);
        return 1;
    }

    // Web field cleanup (escape chars.) and trim to max length
    //  - 1) escape + XSS attack handling via: htmlspecialchars()
    //      - escapes symbols: &, ', ", <, >
    //  - 2) trim via substr()
    // TBD  - 2) trim via mb_substr() - multi-byte safe string truncation

    //  Other functions:
    //  - mysql_real_escape_string() to escape '''
    //  - for XSS attack: htmlentities(), htmlspecialchars(), strip_tags()

    function UTILclean($field, $max_len, $req_field) {
        if ($max_len == 0) $max_len = 3000;     // 3000 chars. (~500 words) is the default max len.

        if ($req_field != '' && $field == '')
            $_SESSION['reqfields'].="$req_field . ";

        // TBD: check for obvious XSS attacks?  "<script" token

        return substr(htmlspecialchars($field, ENT_QUOTES), 0, $max_len);
    }


    // Check for validation error chain
    //

    function UTILrequiredfields() {

        if (isset($_SESSION['reqfields'])) {
            //echo "Required fields missing:<br>$_SESSION[reqfields]";
            $_SESSION['Smessage'] = "The following are required fields: ".$_SESSION['reqfields'];
            unset($_SESSION['reqfields']);
            return 0;
        }
        return 1;
    }


    // return string proportional size refit: width="" height=""
    //  $size: size[0] = width, size[1] = height
    //  $target: max pixels in either X or Y

    function UTILimageResize($size, $target) {

        if ($size[0] < 5 && $size[1] < 5)
            return "width=\"0\" height=\"0\"";

        if ($size[0] > $size[1]) {
            $percentage = ($target / $size[0]);
        } else {
            $percentage = ($target / $size[1]);
        }

        //gets the new value and applies the percentage, then rounds the value
        $width = round($size[0] * $percentage);
        $height = round($size[1] * $percentage);

        return "width=\"$width\" height=\"$height\"";
    }


    // Upload external image file (name='photo')
    //

    function UTILuploadphoto($localfname) {

        //global $SET_MAX_PHOTO_LEN, $SET_IMG_FILE_DIR;
        $SET_MAX_PHOTO_LEN = 1000000;
        $SET_IMG_FILE_DIR = "./image_files/";

        $uploadfile = "";
        $filetmp = $_FILES['photo']['tmp_name'];
        $filesize = $_FILES['photo']['size'];
        $filetype = $_FILES['photo']['type'];
        $fileerr = $_FILES['photo']['error'];

        //UTILlog ("FILE UPLOAD: TMP FILE: $filetmp (FILE ERROR: $fileerr)".
        //  "\r\nFILE SIZE: $filesize  FILE TYPE: ".basename($filetype));

        if ($filesize > $SET_MAX_PHOTO_LEN) {
            $_SESSION['Smessage'] = "Note: File attachment too big: $filesize bytes. Must be $SET_MAX_PHOTO_LEN or less.";
            return -1;
        } elseif ($filesize > 100) {

            if ($filetype != 'image/jpeg' && $filetype != 'image/gif' && $filetype != 'image/pjpeg') {
                $_SESSION['Smessage'] = "Note: File attachment wrong type, must be either a gif or jpeg.";
                return -1;
            }

            $uploadfile = $localfname.'.'.basename($filetype);
            $uploadfilewithpath = $SET_IMG_FILE_DIR . $uploadfile;

            if (move_uploaded_file($filetmp, $uploadfilewithpath )) {
                UTILlog ("File: >$filetmp< was successfully uploaded to >$uploadfilewithpath<");
            } else {
                UTILlog ("ERROR moving File: >$filetmp< to >$uploadfilewithpath<");
            }
        }

        return $uploadfile;
    }

    // DB Error
    //

    function UTILdberror($query) {
        $errmess = mysql_error();

        UTILlog("QUERY:".$query."\r\nERROR CODE:".mysql_errno()."\r\nERROR:".$errmess);

        // ROLLBACK TRANSACTION
        //  - if there is a transaction

        $result_trans = mysql_query('ROLLBACK;');
        if (!$result_trans) UTILlog('DB ERROR : Unable to Rollback Transaction');

        header("Location: ./errorPage.php?errTitle=Database Error&errMsg=".$errmess);
        exit();
    }

    function UTILuser_may_admin($user_id, $event_id){
        // Only user_type of Admin or (event) register_status of Leader, Coleader, or Registrar can View/Edit the Roster and Admin pages
        // What this means is that the leader of a trip can promote anyone on the trip to be the new Leader/Coleader/Admin and that
        // appointee will be able to administer the trip, even without being a user_type LEADER in the system.
        // This is helpful when the leader suddenly becomes ill and has to bail from the trip--the trip must go on,
        // and somebody must have access to the people's info to coordinate it.
        // Reference: Julie LePage
        $query = "select users.user_id
            FROM users, user_events
            WHERE users.user_id = user_events.user_id
            AND (register_status='LEADER' or register_status='CO-LEADER' or register_status='REGISTRAR')
            AND event_id=$event_id
            AND users.user_id=$user_id;";

        $result = mysql_query($query);
        if (!$result) UTILdberror($query);

        $numrows = mysql_num_rows($result);
        if ($numrows <> 1 && $_SESSION['Suser_type'] <> 'ADMIN')
            return false;
        else
            return true;
    }


    // log
    //

    function UTILlog($mess) {
        if (isset ($_SESSION))
            $from_whom = print_r($_SESSION, true);
        else
            $from_whom = "Session is empty";
        $line = "\n--------------------------------------------\n";
        $log_this = $line . $mess . $line . $from_whom . $line;
        error_log($log_this);
    }



    function UTILsessionlog(){
        $bit = "Session info:\n";
        $bit .= print_r($_SESSION, true);   # the 'true makes it return a string instead of printing to screen
        $bit = "Post info:\n";
        $bit .= print_r($_POST, true);   # the 'true makes it return a string instead of printing to screen
        UTILlog($bit);
    }
    // Functions to convert arrays to strings, and vice-versa
    //  - from a post at http://us2.php.net/array

    // Converts an array to a string that is safe to pass via a URL
    function array_to_string($array) {
      $retval = '';
      foreach ($array as $index => $value) {
          $retval .= urlencode(base64_encode($index)) . '|' . urlencode(base64_encode($value)) . '||';
       }
       return urlencode(substr($retval, 0, -2));
    }

    // Converts a string created by array_to_string() back into an array.
    function string_to_array($string) {
     $retval = array();
      $string = urldecode($string);
      $tmp_array = explode('||', $string);

        foreach ($tmp_array as $tmp_val) {
            list($index, $value) = explode('|', $tmp_val);
            $retval[base64_decode(urldecode($index))] = base64_decode(urldecode($value));
        }
        return $retval;
    }

    // Send Email
    //  - Requires: SMTP mail server

    function UTIL_failover_sendEmail($to, $subject, $message) {
        // We now use SWIFTsend() instead. However this is still in place
        // as a failover mechanism

        //define the headers we want passed. Note that they are separated with \r\n

        $headers = "Content-Type: text/plain; charset=\"utf-8\"\n";
        $headers.= "From: AMC.Event.Registration\r\nReply-To: Do.Not.Reply";


        //send the email
        $mail_sent = mail($to, $subject, $message, $headers);
        //UTILlog("Mail probably sent TO: ($to) SUBJECT: ($subject) HEADERS: $headers ");

        if (!$mail_sent)
            UTILlog("ERROR: email not sent from UTIL_failover_sendEmail. TO: ($to) SUBJECT: ($subject) MESSAGE: ($message)");

    }



define('SALT_LENGTH', 10);

function UTILcheckhash($plainText, $stored)
{
    //print "   stored:   " . $stored;
    $hash_40 = substr($stored, SALT_LENGTH);
    //print "   hash_40:   " . $hash_40;
    $salt = substr($stored, 0, SALT_LENGTH);
    //print "    salt:   " . $salt;
    $new_hash = sha1($salt . $plainText);
    //print "   new_hash:   " . $new_hash;

    if ($hash_40 == $new_hash)
        return true;
    else
        return false;
}

function UTILgenhash($plainText)
{
    $salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    return $salt . sha1($salt . $plainText);
}

function UTIL_gen_all_passhashes(){
    $first_query = "select user_password, user_passhash FROM users;";
    $result = mysql_query($first_query);
    $default = 'none generated';
    while($row = mysql_fetch_assoc($result)) {
        $plain = $row['user_password'];
        $hashed = UTILgenhash($plain);
        $second_query = "update users set user_passhash='$hashed' where user_password='$plain' and user_passhash='$default';";
        $iter_result = mysql_query($second_query);
    }


}

function UTIL_disp_excel($in_string){
    if ($in_string == ''){
        return " ";     //Return a space so following cells will show up as empty
    }else{
        //remove carriage returns
        $on_one_line = preg_replace("/\r\n/", " || ", $in_string);
        //display special chars that may be encoded
        $with_html_chars = htmlspecialchars_decode($on_one_line, ENT_QUOTES);
        return $with_html_chars;
    }
}

function UTIL_date_prettify($standard){
    //incoming $standard is in format YYYY-MM-DD
    if ($standard == ""){
        return "";
    }else{
        $year = substr($standard, 0, 4);
        $month = substr($standard, 5, 2);
        $day = substr($standard, 8, 2);
        $pretty = $month . "/" . $day . "/" . $year;
        return $pretty;
    }
}

function UTIL_date_standardize($pretty){
    //incoming $pretty is in format MM/DD/YYYY
    if ($pretty == ""){
        return "";
    }else{
        $year = substr($pretty, 6, 4);
        $month = substr($pretty, 0, 2);
        $day = substr($pretty, 3, 2);
        $ugly = $year . "-" . $month . "-" . $day;
        return $ugly;
    }
}

function UTILselectUser($in_user_name){
    $query = "select user_id, user_passhash, first_name, last_name, user_type
        from users where user_name = '$in_user_name';";
    $error_message = "User name not found in Database.";
    $row = UTILreturn_single_row($query, $error_message);
    return $row;
}

function UTILgetEmail($user_id){
    $query = "select email from users where user_id = '$user_id';";
    $error_message = "User ID $user_id not found in database.";
    $email = UTILreturn_single_row($query, $error_message);
    return $email['email'];
}


function UTILgetFirstName($user_id){
    $query = "select first_name from users where user_id = '$user_id';";
    $error_message = "User ID $user_id not found in database.";
    $name = UTILreturn_single_row($query, $error_message);
    return $name['first_name'];
}

function UTILgetEventName($event_id){
    $query = "SELECT event_name FROM events WHERE event_id = $event_id";
    $error_message = "Could not find event # " . $event_id . " in database.";
    $row = UTILreturn_single_row($query, $error_message);
    return $row['event_name'];
}


function UTILeventisprogram($event_id){
    $query = "SELECT event_is_program FROM events WHERE event_id = $event_id;";
    $error_message = "Could not find event # " . $event_id . " in database.";
    $row = UTILreturn_single_row($query, $error_message);
    return $row['event_is_program'];
}
function UTILreturn_single_row($query, $error_message){
    UTILdbconnect();
    $result = mysql_query($query);
    if (!$result)
        UTILdberror($query);

    $numrows = mysql_num_rows($result);
    if ($numrows != 1) {
        $_SESSION['Smessage'] = $error_message;
        return false;
    }else{
        $row = mysql_fetch_assoc($result);
        return $row;
    }
}

function UTILtattletale($input){
    $_SESSION['Smessage'] = "An error has occurred.<br>Please log in again.<br>If this happens again, please contact REGI support.";
    $err = "TattleTale thinks something fishy is going on. User will be logged out.\n";
    $err .= "Message: " . $input . "\n";
    $err .= print_r($_SESSION, true);
    $err .= "\nReferring page: ";
    $err .= $_SERVER['HTTP_REFERER'];
    $err .= "All server details: \n";
    $err .= print_r ($_SERVER, true);
    UTILlog($err);
    header("Location: ./logout");
    exit();
}
/*Note there is purposefully no closing php tag here, because
if you accidentally put extra characters (even line breaks)
after a closing php tag, you will get a warning when this
file is included in another file. Such a warning can wreak
havoc when the warning ends up inside an Excel export. (It's
gibberish. So I'm removing this closing tag */
