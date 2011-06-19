<!--

    AMC Trip Registration System
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

-->


<?php

    include 'utils.php';
    session_start();
    UTILdbconnect();
    CHUNKgivehead();
    CHUNKstartbody();
    UTILbuildmenu();
    if (isset($_SESSION['Smessage']))
        CHUNKstylemessage($_SESSION['Smessage']);
    // SECURITY
    // - User must be logged in

    if (isset($_SESSION['Suser_id']))
    {
        $my_user_id = $_SESSION['Suser_id'];
        $user_type = $_SESSION['Suser_type'];
    }
    else
    {
        header("Location: ./errorPage.php?errTitle=Error&errMsg=User must be logged in to view this page.");
        exit(0);
    }

    // NOTE: Currently: ALL users can admin a trip IF their register_status is set to CO/LEADER or REGISTRAR
    //       They do NOT need to be AMC LEADERS.

    //if ($_SESSION['Suser_type'] <> 'LEADER' && $_SESSION['Suser_type'] <> 'ADMIN')
    //{
    //  header("Location: ./errorPage.php?errTitle=Error&errMsg=User must be an AMC Leader or Administrator to view this page.");
    //  exit(0);
    //}

    if (isset($_GET['event_id']))
        $event_id = $_GET['event_id'];
    else
        $event_id = '';

    $submitValue='New Event';
    $event_name='';
    $event_status='OPEN';
    $event_is_program='N';
    $event_is_programY='';
    $event_is_programP='';
    $event_is_programN='';
    $description='';
    $gear_list='';
    $trip_info='';
    $confirmation_page='Thank you for registering. A trip leader will be in contact with you soon regarding your participation in this event.';
    $question1='';
    $question2='';

    $program_id='';
    $program_name='';

    if ($event_id <> '')
    {
        // Check if current user is a leader, co-leader, or registrar of this trip
        //
        if ( ! UTILdb_proceed($my_user_id, $event_id))
        {
            header("Location: ./errorPage.php?errTitle=Error&errMsg=User must be a designated trip leader, co-leader, or registrar to view this page. Please contact the trip leader.");
            exit(0);
        }

        // Get event summary info
        //

        $query = "select event_name, event_status, event_is_program, program_id, description, gear_list, trip_info, confirmation_page, question1, question2, start_date, end_date, rating
                FROM events
                WHERE event_id=$event_id;";

        $result = mysql_query($query);
        if (!$result) UTILdberror($query);

        $numrows = mysql_num_rows($result);
        if ($numrows <> 1) {
            print "<p>ERROR: Can not retrieve event from database.</p>";
            exit();
        } else {
            $row = mysql_fetch_assoc($result);
            $event_name=$row['event_name'];
            $event_status=$row['event_status'];
            $event_is_program=$row['event_is_program'];
            $program_id=$row['program_id'];
            $description=$row['description'];
            $gear_list=$row['gear_list'];
            $trip_info=$row['trip_info'];
            $confirmation_page=$row['confirmation_page'];
            $question1=$row['question1'];
            $question2=$row['question2'];
            $start_date=$row['start_date'];
            $end_date=$row['end_date'];
            $rating=$row['rating'];

            if ($start_date == "0000-00-00") //This accounts for the events that were created with this date when
                $start_date = '';           //the start_date field was added to the database.


            $submitValue='Update Event';

            if ($event_is_program == 'Y')
                $event_is_programY ='checked';
            if ($event_is_program == 'P')
                $event_is_programP ='checked';
            if ($event_is_program == 'N')
                $event_is_programN ='checked';

            if ($program_id < 1)
                $program_id = '';
        }

        // Get program name
        //

        if ($program_id > 0)
        {
            $query = "select event_name
                FROM events
                WHERE event_id=$program_id;";

            $result = mysql_query($query);
            if (!$result) UTILdberror($query);

            $numrows = mysql_num_rows($result);
            if ($numrows == 1) {
                $row_program = mysql_fetch_assoc($result);
                $program_name=$row_program['event_name'];
            }
        }

    }  // end: $event_id<>''

    CHUNKstartcontent($my_user_id, $event_id, 'admin');
?>

<h1>Event Administration</h1>

<form name='trip_essence' action='action.php' method='post'>

* <span style="font-weight: bold">Event Name:</span> (include location, rating)<br>
<input type='text' required=required maxlength='60' name='event_name' value='<?php print $event_name; ?>' size=60><br><br>

* <span style="font-weight: bold">Event (Start) Date:</span> (format: YYYY-MM-DD)<br>
<input type='date' required=required maxlength='10' name='start_date' value='<?php print $start_date; ?>' size=10><br><br>

<span style="font-weight: bold">Event End Date:</span> (Optional. Only fill this out if event is more than one day.)<br>
<input type='date' maxlength='10' name='end_date' value='<?php print $end_date; ?>' size=10><br><br>



<?php

  if ($submitValue == 'Update Event')
  {
    print "<b>* Registration URL: http://www.hbbostonamc.org/registrationSystem/login.php?event_id=$event_id</b><br>";
    print "<i>Note: Copy and paste this URL into your AMC trip posting to direct registrants to the Registration page.</i><br><br>";
  }

?>
<table>
    <tr>
        <td>* <span style="font-weight: bold">Event Status</span>:
<select name='event_status'>
        <option value='<?php print $event_status; ?>'><?php print $event_status; ?>
        <option disabled>----------
        <option value='OPEN'>OPEN
        <option value='WAIT LIST'>WAIT LIST
        <option value='PENDING'>PENDING
        <option value='FULL'>FULL
        <option value='CLOSED'>CLOSED
        <option value='CANCELED'>CANCELED
</select><br>
<i style="color: #096">Note: Registration is ONLY active when status is set to 'OPEN' or 'WAIT LIST'.  All other status do NOT allow new registrations.</i>
<br><br></td>
    <td><span style="font-weight: bold">Event Rating:</span> (Optional).<br>
<input type='text' maxlength='4' name='rating' value='<?php print $rating; ?>' size=4><br><br>
    </td>
    </tr>
</table>


<span style="font-weight: bold">* General Description:</span><br>
<textarea name='description' rows=8 cols=60><?php print $description; ?></textarea><br><br>

<span style="font-weight: bold">Gear List</span> (if no gear necessary, please type: "No gear necessary"):
<textarea name='gear_list' rows=8 cols=60><?php print $gear_list; ?></textarea><br><br>

<span style="font-weight: bold">Confirmation Page:</span> (This information will be displayed once a user registers for this event.)
<?php if ($event_id <> '') print "<br>Click here to <a href='./confirmationPage.php?event_id=$event_id'><big>Preview</big></a> the confirmation page.<br>"; ?>
<textarea name='confirmation_page' rows=8 cols=60><?php print $confirmation_page; ?></textarea><br><br>

<span style="font-weight: bold">Participant Info:</span> (Visible only for APPROVED participants, Directions to trailhead, etc).
<textarea name='trip_info' rows=8 cols=60><?php print $trip_info; ?></textarea><br><br>

<p>Two questions are automatically asked of participants upon registering. They are: </p>

<ol>
<li><span style="font-weight: bold"><?php print "\"$SET_QUESTION_1\""; ?></span></li>
<li><span style="font-weight: bold"><?php print "\"$SET_QUESTION_2\""; ?></span></li>
</ol>


<p>If you would like to ask additional questions, list them here:</p>

<span style="font-weight: bold">Additional Trip Question </span>(Optional)<br>
<input type='text' name='question1' value='<?php print $question1; ?>' size=80><br><br>
<!-- Do Not Display Second Question
<span style="font-weight: bold">Additional Trip Question 2 (Optional)</span><br>
<input type='text' name='question2' value='<?php print $question2; ?>' size=80><br><br>
-->

<h2>Program Info</h2>
<?php  //Set one button to be checked by default
    if ($event_is_programN + $event_is_programY + $event_is_programP == "")
        $event_is_programN = "checked";
?>

<input type="radio" name="event_is_program" value="N" <?php print $event_is_programN; ?> >This is a STANDALONE EVENT<br>

<input type="radio" name="event_is_program" value="Y" <?php print $event_is_programY; ?> >This is a PROGRAM<br>

<input type="radio" name="event_is_program" value="P" <?php print $event_is_programP; ?> >This event is PART OF A PROGRAM. The Program ID is
<input type='text' name='program_id' value='<?php print $program_id; ?>' size=10><?php print $program_name; ?><br>
<i style="color: #096">Note: Please contact the program leader for the Event ID of the program. If this event is not part of a program, leave blank or enter '0'</i>
<br>





<br><br>

<input type='hidden' name='event_id' value='<?php print $event_id; ?>'>
<input type='submit' name='action' value='<?php print $submitValue; ?>' onclick='return checkAdmin()'>
</form>

<?php

    if ($event_id == '')
        exit(0);
?>

<br>

</div>
</body>
</html>
