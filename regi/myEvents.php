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
    function my_events_table_start(){
        print "<table class='center'><tr class='table_header'><th>Start</th><th>End</th><th>Event</th><th>My Status</th><th>Event Status</th></tr>";
        print "<tr><td colspan='5' class='row1' style='height:2px; padding:0;'></td></tr>";
    }
    session_start();
    UTILdbconnect();

    // Notice the !!!!!!! in this next line
    if (! SECisUserLoggedIn($PASS_HMAC_SECRET_CODE)) {

        // ERROR?

        $my_user_id='';
        $my_user_type='';
        $_SESSION['Smessage'] = 'Please Log In';
        header("Location: ./login");
        exit();
    }
    else
    {
        // Now that all header redirects are passed, we can write html to page
        CHUNKgivehead();
        CHUNKstartbody();
        //Note that UTILbuildmenu is clear down here to make sure the auth cookie is picked up first
        UTILbuildmenu(3);
        CHUNKstylemessage($_SESSION);
        CHUNKstartcontent();
        print "<h1>My Upcoming Events</h1>";

        $my_user_id=$_SESSION['Suser_id'];
        $my_user_type=$_SESSION['Suser_type'];

        // Show all events I am connected with as: signed up/wait list, selected
        //   leader, or co-leader
        //

        $query = "select events.event_id, event_name, event_status, register_date,
                register_status, program_id, start_date, end_date
                FROM events, user_events
                WHERE events.event_id=user_events.event_id
                AND user_events.user_id=$my_user_id
                ORDER BY start_date DESC, register_date DESC
                LIMIT 100;";

        $result = mysql_query($query);
        if (!$result) UTILdberror($query);

        $numrows = mysql_num_rows($result);
        if ($numrows < 1 && $my_user_type == 'USER')
        {
            print " <h1>Welcome!</h1>
                <h2>You have not yet signed up for any events.</h2>
                <h2>Please view event listings and click the 'Register Online' link for the event you are interested in.</h2>";
        }
        else if ($numrows < 1 && $my_user_type == 'LEADER')
        {
            print " <h2>Welcome AMC Leader!</h3>
                <h2>You have not created any events on this registration system yet.</h3>
                <h2>Please click the 'New Event' menu option above to enter in your first event.</h3>";
        }
        else
        {
            my_events_table_start();
            $rowcount = 0;
            $need_archive_header = true;
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            while($row = mysql_fetch_assoc($result)) {
                if ($row['start_date'] < $yesterday and $need_archive_header){
                    print "</table><h1>My Archived Events</h1><table>";
                    my_events_table_start();
                    $need_archive_header = false;
                }
                $even_or_odd = $rowcount % 2;
                print IN1()."<tr class='row{$even_or_odd}'>";
                print IN2()."<td class='mytrips_nowrap'>".UTILshortdate($row['start_date'])."</td>";
                print IN2()."<td class='mytrips_nowrap'>".UTILshortdate($row['end_date'])."</td>";
                print IN2()."<td class='mytrips_en'><strong><a href=\"$row[event_id]\" >$row[event_name]</a></strong><br>";
                print IN2()."<td class='mytrips'>".ucfirst(strtolower($row['register_status']))."</td>";
                print IN2()."<td class='mytrips'>".ucfirst(strtolower($row['event_status']))."</td>";
                print IN1()."</tr>";
                $rowcount += 1;
            }
            print "</table>";
        }

    }
?>

<br><br>

<?php CHUNKfinishcontent(); ?>

