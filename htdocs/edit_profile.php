<?php
// edit_profile.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//


// This Page Is Valid XHTML 1.0 Transitional!  1Nov05

$permission=4; // Edit your profile
require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

// External variables
$mode = $_REQUEST['mode'];
$edituserpermission = user_permission($sit[2],23); // edit user
if (empty($_REQUEST['userid']) OR $_REQUEST['userid']=='current' OR $edituserpermission==FALSE) $userid = mysql_escape_string($sit[2]);
else $userid = cleanvar($_REQUEST['userid']);


if (empty($mode))
{
    include('htmlheader.inc.php');

    $sql = "SELECT * FROM users WHERE id='$userid' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    if (mysql_num_rows($result) < 1) trigger_error("No such user ".strip_tags($userid),E_USER_ERROR);
    $user = mysql_fetch_object($result);

    // FIXME This form should use one SQL query really, not call all these functions to lookup each field
    // Need to change this sometime.

    echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/user.png' width='32' height='32' alt='' /> ";
    echo sprintf($strEditProfileFor, $user->realname)."</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table align='center' class='vertical'>";
    echo "<col width='250'></col><col width='*'></col>";
    echo "<tr><th colspan='2'>{$strAbout}";
    if ($userid==$sit[2]) echo $strYou;
    else echo $user->realname;
    echo "</td></tr>\n";
    echo "<tr><th>{$strUsername}:</th><td>{$user->username}</td></tr>";
    echo "<tr><th>{$strRole}:</th>";
    if ($userid==$sit[2] OR $userid==1) echo "<td>".db_read_column('rolename', 'roles', $user->roleid)."</td>";
    else echo "<td>".role_drop_down('roleid', $user->roleid)."</td>";
    echo "</tr>";
    echo "<tr><th>{$strRealName}:</th><td><input maxlength='50' name='realname' size='30' type='text' value=\"".stripslashes($user->realname)."\" /></td></tr>\n";
    echo "<tr><th>{$strJobTitle}:</th><td><input maxlength='50' name='jobtitle' size='30' type='text' value=\"".stripslashes($user->title)."\" /></td></tr>\n";
    echo "<tr><th>{$strQualifications}:<br />{$strQualificationsTip}</th>";
    echo "<td><textarea name='qualifications' rows='3' cols='40'>".stripslashes($user->qualifications)."</textarea></td></tr>\n";
    echo "<tr><th>{$strEmailSignature}:<br />{$strEmailSignatureTip}</th>";
    echo "<td><textarea name='signature' rows='4' cols='40'>".strip_tags(stripslashes($user->signature))."</textarea></td></tr>\n";
    $entitlement=user_holiday_entitlement($userid);
    if ($edituserpermission && $userid!=$sit[2])
    {
        echo "<tr><th>{$strHolidayEntitlement}:</th><td>";
        echo "<input type='text' name='holiday_entitlement' value='$entitlement' size='2' /> {$strdays}";
        echo "</td></tr>";
    }
    elseif ($entitlement > 0)
    {
        $holidaystaken=user_count_holidays($userid, 1);
        echo "<tr><th>{$strHolidayEntitlement}:</th><td>";
        echo "{$entitlement} {$strdays}, ";
        echo "{$holidaystaken} {$strtaken}, ";
        echo sprintf($strRemaining, $entitlement-$holidaystaken);
        echo "</td></tr>\n";
        echo "<tr><th>{$strOtherLeave}:</th><td>";
        echo user_count_holidays($userid, 2)." {$strdayssick}, ";
        echo user_count_holidays($userid, 3)." {$strdaysworkingaway}, ";
        echo user_count_holidays($userid, 4)." {$strdaystraining}";
        echo "<br />";
        echo user_count_holidays($userid, 5)." {$strdaysother}";
        echo "</td></tr>";
    }
    echo "<tr><th>{$strGroupMembership}:</th><td valign='top'>";
    if ($user->groupid >= 1)
    {
        $sql="SELECT name FROM groups WHERE id='{$user->groupid}' ";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        $group = mysql_fetch_object($result);
        echo $group->name;
    }
    else
    {
        echo "None set";
    }
    echo "
    </td></tr>
    <tr><th colspan='2'>{$strWorkStatus}</td></tr>";
    if ($edituserpermission AND $userid != $sit[2]) $userdisable=TRUE;
    else $userdisable=FALSE;

    echo "<tr><th>{$strStatus}:</th><td>";
    userstatus_drop_down("status", $user->status, $userdisable);
    echo "</td></tr>\n";
    echo "<tr><th>{$strAccepting} {$strIncidents}:</th><td>";
    accepting_drop_down("accepting", $userid);
    echo "</td></tr>\n";
    echo "<tr><th>{$strMessage}:<br />{$strMessageTip}</th>";
    echo "<td><textarea name='message' rows='4' cols='40'>".strip_tags($user->message)."</textarea></td></tr>\n";
    echo "<tr><th colspan='2'>{$strContactDetails}</td></tr>";
    echo "<tr id='email'><th>{$strEmail}:<sup class='red'>*</sup></th><td><input maxlength='50' name='email' size='30' type='text' value='".strip_tags($user->email)."'></td></tr>";
    echo "<tr id='phone'><th>{$strTelephone}:</th><td><input maxlength='50' name='phone' size='30' type='text' value='".strip_tags($user->phone)."'></td></tr>";
    echo "<tr><th>{$strFax}:</th><td><input maxlength='50' name='fax' size='30' type='text' value='".strip_tags($user->fax)."'></td></tr>";
    echo "<tr><th>{$strMobile}:</th><td><input maxlength='50' name='mobile' size='30' type='text' value='".user_mobile($userid)."'></td></tr>";
    echo "<tr><th>AIM: <img src=\"images/icons/{$iconset}/16x16/aim.png\" width=\"16\" height=\"16\" alt=\"AIM\" /></th>";
    echo "<td><input maxlength=\"50\" name=\"aim\" size=\"30\" type=\"text\" value=\"".strip_tags($user->aim)."\" /></td></tr>";
    echo "<tr><th>ICQ: <img src=\"images/icons/{$iconset}/16x16/icq.png\" width=\"16\" height=\"16\" alt=\"ICQ\" /></th>";
    echo "<td><input maxlength=\"50\" name=\"icq\" size=\"30\" type=\"text\" value=\"".strip_tags($user->icq)."\" /></td></tr>";
    echo "<tr><th>MSN: <img src=\"images/icons/{$iconset}/16x16/msn.png\" width=\"16\" height=\"16\" alt=\"MSN\" /></th>";
    echo "<td><input maxlength=\"50\" name=\"msn\" size=\"30\" type=\"text\" value=\"".strip_tags($user->msn)."\" /></td></tr>";

    echo "<tr><th colspan='2'>{$strDisplayPreferences}</td></tr>\n";
    echo "<tr><th>{$strLanguage}</th><td>";
    echo "<select name='vari18n' id='vari18n'>";
    if (!empty($user->var_i18n)) $selectedlang = $user->var_i18n;
    else $selectedlang = $_SESSION['lang'];
    foreach($availablelanguages AS $langcode => $language)
    {
        if($langcode == $selectedlang) echo "<option value='$langcode' selected='selected'>$language</option>\n";
        else echo "<option value='$langcode'>$language</option>\n";
    }
    echo "</select></label>";
    echo "</td></tr>\n";

    echo "<tr><th>{$strInterfaceStyle}:</th><td>";
    interfacestyle_drop_down('style', $user->var_style);
    echo "</td></tr>\n";
    echo "<tr><th>{$strIncidentRefresh}:</th>";
    echo "<td><input maxlength='10' name='incidentrefresh' size='3' type='text' value=\"{$user->var_incident_refresh}\" /> {$strSeconds}</td></tr>\n";

    echo "<tr><th>{$strIncidentLogOrder}:</th><td>";
    echo "<select name='updateorder'>";
    echo "<option ";
    if ($user->var_update_order == "desc") echo "selected='selected'";
    echo " value='desc'>{$strNewestAtTop}</option>\n";
    echo "<option ";
    if ($user->var_update_order == "asc") echo "selected='selected'";
    echo " value='asc'>{$strNewestAtBottom}</option>\n";
    echo "</select>";
    echo "</td></tr>\n";

    echo "<tr><th>{$strIncidentUpdatesPerPage}:</th>";
    echo "<td><input maxlength='5' name='updatesperpage' size='3' type='text' value=\"".stripslashes($user->var_num_updates_view)."\" /></td></tr>\n";

    echo "<tr><th>{$strCollapseData}:</th><td>";
    html_checkbox('collapse', $user->var_collapse);
    echo "</td></tr>\n";

    echo "<tr><th colspan='2'>{$strNotifications}</td></tr>\n";
    echo "<tr><th>{$strEmailNotificationReassign}</th><td>";
    html_checkbox('emailonreassign', $user->var_notify_on_reassign);
    echo "</td><tr>\n";

    plugin_do('edit_profile_form');

    if ($CONFIG['trusted_server']==FALSE AND $userid==$sit[2])
    {
        echo "<tr class='password'><th colspan='2'>{$strChangePassword}</td></tr>";
        echo "<tr class='password'><th>&nbsp;</th><td>{$strToChangePassword}</td></tr>";
        echo "<tr class='password'><th>{$strOldPassword}:</th><td><input maxlength='50' name='password' size='30' type='password' /></td></tr>";
        echo "<tr class='password'><th>{$strNewPassword}:</th><td><input maxlength='50' name='newpassword1' size='30' type='password' /></td></tr>";
        echo "<tr class='password'><th>{$strConfirmNewPassword}:</th><td><input maxlength='50' name='newpassword2' size='30' type='password' /></td></tr>";
    }
    echo "</table>\n";
    echo "<input type='hidden' name='userid' value='{$userid}' />";
    echo "<input type='hidden' name='mode' value='save' />";
    echo "<p><input name='reset' type='reset' value='{$strReset}' /> <input name='submit' type='submit' value='{$strSave}' /></p>";
    echo "</form>\n";

    include('htmlfooter.inc.php');
}
elseif($mode=='save')
{
    // External variables
    $message = cleanvar($_POST['message']);
    $realname = cleanvar($_POST['realname']);
    $qualifications = cleanvar($qualifications);
    $userid = cleanvar($_POST['userid']);
    $email = cleanvar($_POST['email']);
    $jobtitle = cleanvar($_POST['jobtitle']);
    $qualifications = cleanvar($_POST['qualifications']);
    $phone = cleanvar($_POST['phone']);
    $mobile = cleanvar($_POST['mobile']);
    $aim = cleanvar($_POST['aim']);
    $icq = cleanvar($_POST['icq']);
    $msn = cleanvar($_POST['msn']);
    $fax = cleanvar($_POST['fax']);
    $incidentrefresh = cleanvar($_POST['incidentrefresh']);
    $updateorder = cleanvar($_POST['updateorder']);
    $updatesperpage = cleanvar($_POST['updatesperpage']);
    $signature = cleanvar($_POST['signature']);
    $message = cleanvar($_POST['message']);
    $status = cleanvar($_POST['status']);
    $collapse = cleanvar($_POST['collapse']);
    $emailonreassign = cleanvar($_POST['emailonreassign']);
    $style = cleanvar($_POST['style']);
    $vari18n = cleanvar($_POST['vari18n']);
    $accepting = cleanvar($_POST['accepting']);
    $roleid = cleanvar($_POST['roleid']);
    $holiday_entitlement = cleanvar($_POST['holiday_entitlement']);
    $password = cleanvar($_POST['password']);
    $newpassword1 = cleanvar($_POST['newpassword1']);
    $newpassword2 = cleanvar($_POST['newpassword2']);

    // Some extra checking here so that users can't edit other peoples profiles
    $edituserpermission = user_permission($sit[2],23); // edit user
    if ($userid != $sit[2] AND $edituserpermission==FALSE)
    {
        trigger_error('Error: No permission to edit this users profile', E_USER_ERROR);
        exit;
    }

    // If users status is set to 0 (disabled) force 'accepting' to no
    if ($status==0) $accepting='No';

    // Update user profile
    $errors = 0;

    // check for change of password
    if ($password != "" && $newpassword1 != "" && $newpassword2 != "")
    {
        // verify password fields
        if ($newpassword1 == $newpassword2 && strtoupper(md5($password)) == strtoupper(user_password($userid)))
        {
            $password=strtoupper(md5($password));
            $newpassword1=strtoupper(md5($newpassword1));
            $newpassword2=strtoupper(md5($newpassword2));
            $sql = "UPDATE users SET password='$newpassword1' WHERE id='$userid'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            if (mysql_affected_rows() < 1) { throw_error("!Error password change failed - new password", "$newpassword1"); }
            $confirm_message = "<h2>Password Changed</h2>\n<p align='center'>You should log out and log back in again</p>\n<p align='center'>Please wait while you are redirected.</p>";
        }
        else
        {
            // TODO produce a better message when password change fails
            throw_error("User Error changing password, please try again typing your passwords carefully. $password ($newpassword1 / $newpassword2)",'');
        }
    }
    // check for blank real name
    if ($realname == "")
    {
        $errors = 1;
        $error_string .= "<h5 class='error'>You must enter a real name</h5>\n";
    }
    // check for blank email address
    if ($email == "")
    {
        $errors = 1;
        $error_string .= "<h5 class='error'>You must enter an email address</h5>\n";
    }
    else
    {
        //we updated our email, dimiss notice
        $sql = "DELETE FROM usernotices WHERE userid={$sit[2]} and noticeid=2";
        @mysql_query($sql);
    }
    // Check email address is unique (discount disabled accounts)
    $sql = "SELECT COUNT(id) FROM users WHERE status > 0 AND email='$email'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    list($countexisting) = mysql_fetch_row($result);
    if ($countexisting > 1)
    {
        $errors++;
        $error_string .= "<h5 class='error'>Email must be unique</h5>\n";
    }
    // update database if no errors
    if ($errors == 0)
    {
        if(!empty($collapse)) $collapse = 'true'; else $collapse = 'false';
        if(!empty($emailonreassign)) $emailonreassign = 'true'; else $emailonreassign = 'false';

        $oldstatus = user_status($userid);

        $sql  = "UPDATE users SET realname='$realname', title='$jobtitle', email='$email', qualifications='$qualifications', ";
        $sql .= "phone='$phone', mobile='$mobile', aim='$aim', icq='$icq', msn='$msn', fax='$fax', var_incident_refresh='$incidentrefresh', ";
        if ($userid != 1 AND !empty($_REQUEST['roleid']) AND $edituserpermission==TRUE) $sql .= "roleid='{$roleid}', ";
        if (!empty($holiday_entitlement) AND $edituserpermission==TRUE) $sql .= "holiday_entitlement='{$holiday_entitlement}', ";
        $sql .= "var_update_order='$updateorder', var_num_updates_view='$updatesperpage', var_style='$style', signature='$signature', message='$message', status='$status', accepting='$accepting', ";
        $sql .= "var_collapse='$collapse', var_notify_on_reassign='$emailonreassign', var_i18n='{$vari18n}' WHERE id='$userid' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        // If this is the current user, update the profile in the users session
        if ($userid == $_SESSION['userid'])
        {
            $_SESSION['style'] = $style;
            $_SESSION['realname'] = $realname;
            $_SESSION['email'] = $email;
            $_SESSION['incident_refresh'] = $incidentrefresh;
            $_SESSION['update_order'] = $updateorder;
            $_SESSION['num_update_view'] = $updatesperpage;
            $_SESSION['lang'] = $vari18n;
        }

        //only want to reassign to backup if you've changed you status
        //(i.e. In Office -> On Holiday rather than when youve updated your message) or changes from accepting to not accepting
        if($oldstatus != $status)
        {
            // reassign the users incidents if appropriate
            incident_backup_switchover($userid, $accepting);
        }

        if (!$result)
        {
            include('htmlheader.inc.php');
            throw_error('!Error while updating users table', '');
            include('htmlfooter.inc.php');
        }
        else
        {
            if ($userid==$sit[2]) $redirecturl='index.php';
            else $redirecturl='manage_users.php';
            plugin_do('save_profile_form');

            // password was not changed
            if (!isset($confirm_message)) confirmation_page("2", $redirecturl, "<h2>Profile Modification Successful</h2><h5>{$strPleaseWaitRedirect}...</h5>");
            // password was changed
            else
            {
                //if this was the admin changing his password for the first time
                if($sit[2] == '1')
                {
                    $sql = "UPDATE usernotices SET dimissed=1 WHERE userid={$sit[2]} and updateid=1";
                    @mysql_query($sql);
                }
                journal(CFG_LOGGING_NORMAL, 'User Profile Edited', "User Profile {$sit[2]} Edited", CFG_JOURNAL_USER, $sit[2]);
                confirmation_page("2", $redirecturl, $confirm_message);
            }
        }
    }
    else
    {
        // print error string
        include('htmlheader.inc.php');
        echo $error_string;
        include('htmlfooter.inc.php');
    }
}
elseif($mode='savesessionlang')
{

    $sql = "UPDATE users SET var_i18n = '{$_SESSION['lang']}' WHERE id = {$sit[2]}";
    mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $sql = "DELETE FROM notices WHERE type=".USER_LANG_DIFFERS_TYPE." AND userid={$sit[2]}";
    mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    include('htmlheader.inc.php');
    confirmation_page("2", "main.php", "<h2>Profile Modification Successful</h2><h5>{$strPleaseWaitRedirect}...</h5>");
    include('htmlfooter.inc.php');
}

?>