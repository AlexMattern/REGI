<!--

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

-->


<?php

    include 'utils.php';
    session_start();
    UTILdbconnect();
    CHUNKgivehead();
    CHUNKstartbody();
    UTILbuildmenu();

    CHUNKstylemessage($_SESSION['Smessage']);
    unset($_SESSION['Smessage']);
    CHUNKstartcontent();


    // If Suser_id defined, populate screen with user profile + set to Update mode
    //  otherwise, set to New mode

    $user_name='';
    $user_password='';
    $first_name='';
    $last_name='';
    $email='';
    $phone_evening='';
    $phone_day='';
    $phone_cell='';
    $member='';
    $member_yes='';
    $member_no='checked';
    $emergency_contact='';
    $experience='';
    $medical='';
    $exercise='';
    $diet='';
    $readonly='';

    $my_user_id='';  // default values, assuming new user
    $my_user_type='';

    if (isset($_GET['event_id']))
        $event_id=$_GET['event_id'];
    else
        $event_id='';


    if (isset($_SESSION['Suser_id']))
    {
        // if User is already logged in, pull profile from DB

        $my_user_id=$_SESSION['Suser_id'];
        $my_user_type=$_SESSION['Suser_type'];
        $formAction='Update My Profile';

        // Get user profile
        //

        $query = "select user_name, first_name, last_name,
            user_type, email, phone_evening, phone_day, phone_cell,
            emergency_contact, member, experience, exercise, medical, diet
            FROM users
            WHERE user_id=$my_user_id;";

        $result = mysql_query($query);
        if (!$result) UTILdberror($query);

        $numrows = mysql_num_rows($result);
        if ($numrows <> 1) {
            print "<br>Can not retrieve user from database<br>";
        } else {
            $row = mysql_fetch_assoc($result);
            $user_name=$row['user_name'];
            $first_name=$row['first_name'];
            $last_name=$row['last_name'];
            $user_type=$row['user_type'];
            $email=$row['email'];
            $phone_evening=$row['phone_evening'];
            $phone_day=$row['phone_day'];
            $phone_cell=$row['phone_cell'];
            $member=$row['member'];
            $emergency_contact=$row['emergency_contact'];
            $experience=$row['experience'];
            $medical=$row['medical'];
            $exercise=$row['exercise'];
            $diet=$row['diet'];

            if ($member=='Y')
                $member_yes='checked';
            else
                $member_no='checked';

            $readonly='readonly';
        }
    }elseif (isset($_SESSION['Semail'])){
        $formAction='New Profile';

        // If Form fields have been cached: if so: repopulate

        //$user_name=$_SESSION['user_name'];
        //$user_password=$_SESSION['user_password'];
        $first_name=$_SESSION['Sfirst_name'];
        //unset($_SESSION['Sfirst_name']);
        $last_name=$_SESSION['Slast_name'];
        unset($_SESSION['Slast_name']);
        $user_type=$_SESSION['Suser_type'];
        unset($_SESSION['Suser_type']);
        $email=$_SESSION['Semail'];
        unset($_SESSION['Semail']);
        $phone_evening=$_SESSION['Sphone_evening'];
        unset($_SESSION['Sphone_evening']);
        $phone_day=$_SESSION['Sphone_day'];
        unset($_SESSION['Sphone_day']);
        $phone_cell=$_SESSION['Sphone_cell'];
        unset($_SESSION['Sphone_cell']);
        $member=$_SESSION['Smember'];
        unset($_SESSION['Smember']);
        $emergency_contact=$_SESSION['Semergency_contact'];
        unset($_SESSION['Semergency_contact']);
        $experience=$_SESSION['Sexperience'];
        unset($_SESSION['Sexperience']);
        $medical=$_SESSION['Smedical'];
        unset($_SESSION['Smedical']);
        $exercise=$_SESSION['Sexercise'];
        unset($_SESSION['Sexercise']);
        $diet=$_SESSION['Sdiet'];
        unset($_SESSION['Sdiet']);

        if ($member=='Y')
            $member_yes='checked';
        else
            $member_no='checked';
    }

?>

<h1>My Profile</h1>

<?php
    if ($formAction=='New Profile') {
    print "<font color='red'>Please note: if you have already created an account, please use that account to login and do not create a new account.  If you don't remember your password, please click <a href='forgotPassword.php'>here</a>.</font>";
    }
?>

<p>Note: When registering for a event, your profile will be shared with the leaders of that event.</p>

<form name='profile' action='action.php' method='post'>
    <table><tr>

    <td><b>* User Name</b></td>
    <td><input type='text' name='user_name' value='<?php echo $user_name; ?>' MAXLENGTH=40 <?php echo $readonly; ?> >  (6-40 chars.) Please don't use the following characters: ' " < > &</td>
    </tr><tr>
    <?php
    print "form action" . $formAction;
    if ($formAction == 'New Profile'){
        print "
    <td><b>* Password</b></td>
    <td><input type='password' name='user_password' value='' MAXLENGTH=50> (minimum 6 characters)</td>
    </tr><tr>";
    }else{
            print "<tr><td colspan='2'>Click <a href='enterNewPassword.php' target='_blank'>Here</a> to reset your password.</td></tr>";
    }
    ?>

    <td><b>* First Name</b></td>
    <td><input type='text' name='first_name' value='<?php echo $first_name; ?>' MAXLENGTH=20>   </td>
    </tr><tr>
    <td><b>* Last Name</b></td>
    <td><input type='text' name='last_name' value='<?php echo $last_name; ?>' MAXLENGTH=20></td>
    </tr><tr>
    <td><b>* Email</b></td>
    <td><input type='email' name='email' value='<?php echo $email; ?>' MAXLENGTH=40></td>
    </tr><tr>
    <td><b>Phone (evening)</b></td>
    <td><input type='text' name='phone_evening' value='<?php echo $phone_evening; ?>' MAXLENGTH=20></td>
    </tr><tr>
    <td><b>Phone (weekday)</b></td>
    <td><input type='text' name='phone_day' value='<?php echo $phone_day; ?>' MAXLENGTH=20></td>
    </tr><tr>
    <td><b>Phone (cell)</b></td>
    <td><input type='text' name='phone_cell' value='<?php echo $phone_cell; ?>' MAXLENGTH=20></td>
    </tr><tr>
</font>
</tr></table>

<p>Are you an AMC member?
<input type="radio" name="member" value="Y" <?php print $member_yes ?> >YES
&nbsp;
<input type="radio" name="member" value="N" <?php print $member_no ?> >NO

<?php
    if ($formAction=='New Profile') {
        print "<p>Are you a current AMC H/B Leader or Coleader?
        <input type='radio' name='leader_request' value='Y'>YES
        &nbsp;
        <input type='radio' name='leader_request' value='N' checked>NO
        <font color='red'><br>Please note: selecting yes will send an email to the administrator to verify your AMC H/B Leader/Coleader status.</p></font>";
    }
?>

<div id='myprofile_narrow'>
<p>What is your previous hiking experience? (If applicable, please name mountains and include approximate distances.)<br />

<textarea name="experience" rows="10" cols="60">
<?php echo $experience; ?>
</textarea></p>

<p>What is your weekly exercise routine? Please be honest.<br />
<textarea name="exercise" rows="6" cols="60">
<?php echo $exercise; ?>
</textarea></p>

<p>Do you have any allergies, are taking any medications, or have other medical conditions that may be important? (Your answer will remain confidential.)<br />
<textarea name="medical" rows="3" cols="60">
<?php echo $medical; ?>
</textarea></p>

<p>In case of emergency, please enter a person to contact, including name and phone number.<br />
<textarea name="emergency_contact" rows="3" cols="60">
<?php echo $emergency_contact; ?>
</textarea></p>

<p>Do you have any dietary preferences or restrictions (vegetarian, food allergies, etc.)?<br />
<textarea name="diet" rows="3" cols="60">
<?php echo $diet; ?>
</textarea></p>

<input type='hidden' name='event_id' value='<?php print $event_id ?>'>
<input type='hidden' name='user_id' value='<?php print $my_user_id ?>'>
<input type='submit' name='action' value='<?php print $formAction; ?>' onclick='return checkProfile()'>
</form>
</div><!-- closing div for #myprofile_narrow, only in this page -->
<?php CHUNKfinishcontent(); ?>
</body>
</html>
