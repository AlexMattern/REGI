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



function SECcreateCookie($user_name, $special ){
    $cookiePayload = 'AMCuser_name=' . $user_name;
    $hmac_key = hash_hmac('sha1',$cookiePayload, $special);
    $delimiter = '&';
    $cookie = $cookiePayload . $delimiter . $hmac_key;
    return $cookie;
}

function SECtestCookie($in_cookie, $special){
    $delimiter = '&';
    $parts = explode($delimiter, $in_cookie, 2);
    if (count($parts) != 2){
        //print 'should have split into exactly two pieces';
        return false;   // should be exactly one delimiter in the in_cookie
    }elseif(strlen($parts[0]) < 14){
        //print 'not long enough';
        return false;   // not long enough to contain the 'AMCuser_name' prepend
    }else{
        $cookiePayload = $parts[0];
        $proposed_user_name = substr($cookiePayload, 13);
        $in_hmac_key = $parts[1];
        $generated_hmac_key = hash_hmac('sha1',$cookiePayload, $special);
        if ($in_hmac_key == $generated_hmac_key){
            return $proposed_user_name;
        }else{
            //print 'hmac key did not match';
            return false;
        }
    }
}

function SECsetAuthCookie($cookie_val){
    $expiry = time() + 60*60*24*30; //One month
    setcookie("regi", $cookie_val, $expiry, '', '', true, true);    // not valid until next time around
    return 0;
}


function SECwrapSetCookie($user_name, $special){
    $cookie_val = SECcreateCookie($user_name, $special);
    SECsetAuthCookie($cookie_val);
    return 0;
}

function SECwrapCheckCookie($special){
    if (isset($_COOKIE["regi"])){
        $hopefully_a_user_name = SECtestCookie($_COOKIE["regi"], $special);
    }else{
        return false;
    }
    if ($hopefully_a_user_name)
        return $hopefully_a_user_name;
    else
        return false;
}

function SECisUserLoggedIn($special){
    if (isset($_SESSION['Suser_id'])){
        return true;
    }else{
        $hopeful = SECwrapCheckCookie($special);
    }
    if($hopeful){
        $row = UTILselectUser($hopeful);
        if($row){
            SECpushToSession($row);
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }

}

function SECpushToSession($row){
    //$row = mysql_fetch_assoc($result);
    $_SESSION['Suser_id'] = $row['user_id'];
    $_SESSION['Sfirst_name'] = $row['first_name'];
    $_SESSION['Slast_name'] = $row['last_name'];
    $_SESSION['Suser_type'] = $row['user_type'];
}

function SECdestroyCookie(){
    $in_the_past = time() - 100;
    $nothing = '';
    setcookie("regi", $nothing, $in_the_past);    // not valid until next time around

}

/*Note there is purposefully no closing php tag here, because
if you accidentally put extra characters (even line breaks)
after a closing php tag, you will get a warning when this
file is included in another file. Such a warning can wreak
havoc when the warning ends up inside an Excel export. (It's
gibberish. So I'm removing this closing tag */
