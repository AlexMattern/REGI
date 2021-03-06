<?php
/*
    AMC Event Registration System
    Copyright (C) 2010 Dirk Koechner
    Copyright (C) 2011 Jack Desert <jackdesert556@gmail.com>>

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
    include 'utils.php';
    session_start();
    UTILdbconnect();
    CHUNKgivehead();
    CHUNKstartbody();
    UTILbuildmenu(3);

    CHUNKstylemessage($_SESSION);
    if (SECisUserLoggedIn($PASS_HMAC_SECRET_CODE)) {
        $my_user_id = $_SESSION['Suser_id'];
        $user_type = $_SESSION['Suser_type'];
    } else {
        CHUNKstartcontent();
        print "<p>You must be logged in to register for an event.</p><p>If you do not have an account, you may create a new account <a href='myProfile.php' >here</a>.<br>";
        exit();
    }

    if (isset($_GET['event_id']))
        $event_id = $_GET['event_id'];
    else
        exit(0);

    // Get event summary info
    //

    $query = "select event_name, event_status, confirmation_page, start_date
            FROM events
            WHERE
            event_id=$event_id;";

    $result = mysql_query($query);
    if (!$result) UTILdberror($query);

    $numrows = mysql_num_rows($result);
    if ($numrows <> 1) {
        print "<p>ERROR: Invalid event.</p>";
        exit();
    } else {
        $row = mysql_fetch_assoc($result);
        $event_name=$row['event_name'];
        $event_status=$row['event_status'];
        $confirmation_page=$row['confirmation_page'];
        $start_date = UTIL_date_prettify($row['start_date']);
    }

    CHUNKstartcontent($my_user_id, $event_id, 'admin');
    CHUNKshowtripname($event_id);

?>


<h1>Event Registration Confirmation</h1>
You just signed up for
<h1><?php print $event_name?></h1>
<b>Event status:</b> <?php print $event_status ?><br>
<b>Start Date:</b> <?php print $start_date ?><br><br>

<?php print htmlspecialchars_decode(str_replace("\n", "<br>", $confirmation_page)); ?>

<br>
<br>
<?php CHUNKfinishcontent(); ?>

