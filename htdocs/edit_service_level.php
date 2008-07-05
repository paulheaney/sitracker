<?php
// edit_service_level.php - Edit a service level
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 53; // Edit Service Levels

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

$title = $strEditServiceLevel;

// External variables
$tag = cleanvar($_REQUEST['tag']);
$priority = cleanvar($_REQUEST['priority']);
$action = $_REQUEST['action'];

if (empty($action) OR $action == "showform")
{
    include ('htmlheader.inc.php');

    ?>
    <script type="text/javascript">
    <!--
    function enableBillingPeriod()
    {
        if ($('timed').checked==true)
        {
            $('engineerBillingPeriod').show();
            $('customerBillingPeriod').show();
            $('limit').show();
            $('allow_reopen').checked=false;
            $('allow_reopen').disable();
        }
        else
        {
            $('engineerBillingPeriod').hide();
            $('customerBillingPeriod').hide();
            $('allow_reopen').enable();
            $('limit').hide();
        }
    }
    --></script>
    <?php

    echo "<h2>".icon('sla', 32)." {$title}</h2>";
    echo "<p align='center'>{$tag} ".priority_name($priority)."</p>";

    $sql = "SELECT * FROM `{$dbServiceLevels}` WHERE tag='$tag' AND priority='$priority'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $sla = mysql_fetch_object($result);

    echo "<form name='edit_servicelevel' action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table class='vertical'>";
    echo "<tr><th>{$strInitialResponse} ".icon('initialresponse', 16)."</th>";
    echo "<td><input type='text' size='5' name='initial_response_mins' maxlength='5' value='{$sla->initial_response_mins}' /> {$strMinutes}</td></tr>";
    echo "<tr><th>{$strProblemDefinition} ".icon('probdef', 16)."</th>";
    echo "<td><input type='text' size='5' name='prob_determ_mins' maxlength='5' value='{$sla->prob_determ_mins}' /> {$strMinutes}</td></tr>";
    echo "<tr><th>{$strActionPlan} ".icon('actionplan', 16)."</th>";
    echo "<td><input type='text' size='5' name='action_plan_mins' maxlength='5' value='{$sla->action_plan_mins}' /> {$strMinutes}</td></tr>";
    echo "<tr><th>{$strResolutionReprioritisation} ".icon('solution', 16)."</th>";
    echo "<td><input type='text' size='5' name='resolution_days' maxlength='3' value='{$sla->resolution_days}' /> {$strDays}</td></tr>";
    echo "<tr><th>{$strReview} ".icon('review', 16)."</th>";
    echo "<td><input type='text' size='5' name='review_days' maxlength='3' value='{$sla->review_days}' /> {$strDays}</td></tr>";
    echo "<tr><th>{$strAllowIncidentReopen}</th><td>".html_checkbox('allow_reopen', $sla->allow_reopen, TRUE)."</td></tr>\n";
    echo "<tr><th>{$strTimed} <img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/16x16/timer.png' width='16' height='16' alt='' /></th><td>";
    if($sla->timed == 'yes')
    {
        echo "<input type='checkbox' name='timed' id='timed' onchange='enableBillingPeriod();' checked='checked' />";
        $billingSQL = "SELECT * FROM `{$dbBillingPeriods}` WHERE servicelevelid = {$sla->id} AND priority = {$priority} AND tag = '{$tag}'";
        $billingResult = mysql_query($billingSQL);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $billing = mysql_fetch_object($billingResult);

        $customerPeriod = $billing->customerperiod;
        $engineerPeriod = $billing->engineerperiod;
        $limit = $billing->limit;
    }
    else
    {
        echo "<input type='checkbox' name='timed' id='timed' onchange='enableBillingPeriod();' />";
        // Set some defaults
        $customerPeriod = "120";
        $engineerPeriod = "60";
    }
    echo help_link('ServiceLevelTimed');
    echo "</td></tr>";
    echo "<tr id='engineerBillingPeriod'><th>{$strBillingEngineerPeriod}</th><td><input type='text' size='5' name='engineerPeriod' maxlength='5' value='{$engineerPeriod}' /> {$strMinutes}</td></tr>";
    echo "<tr id='customerBillingPeriod'><th>{$strBillingCustomerPeriod}</th><td><input type='text' size='5' name='customerPeriod' maxlength='5' value='{$customerPeriod}' /> {$strMinutes}</td></tr>";
    echo "<tr id='limit'><th>{$strLimit}</th><td>{$CONFIG['currency_symbol']} <input type='text' size='5' name='limit' maxlength='5' value='{$limit}' /></td></tr>";
    echo "</table>";
    echo "<script type='text/javascript'>enableBillingPeriod();</script>";
    echo "<input type='hidden' name='action' value='edit' />";
    echo "<input type='hidden' name='tag' value='{$tag}' />";
    echo "<input type='hidden' name='priority' value='{$priority}' />";
    echo "<input type='hidden' name='id' value='{$sla->id}' />";
    echo "<p align='center'><input type='submit' value='{$strSave}' /></p>";
    echo "</form>";
    include ('htmlfooter.inc.php');
}
elseif ($action == "edit")
{
    // External variables
    $id = cleanvar($_POST['id']);
    $initial_response_mins = cleanvar($_POST['initial_response_mins']);
    $prob_determ_mins = cleanvar($_POST['prob_determ_mins']);
    $action_plan_mins = cleanvar($_POST['action_plan_mins']);
    $resolution_days = cleanvar($_POST['resolution_days']);
    $review_days = cleanvar($_POST['review_days']);
    $engineerPeriod = cleanvar($_POST['engineerPeriod']);
    $customerPeriod = cleanvar($_POST['customerPeriod']);
    $allow_reopen = cleanvar($_POST['allow_reopen']);
    if ($allow_reopen != 'yes') $allow_reopen = 'no';
    $limit = cleanvar($_POST['limit']);
    if ($limit == '') $limit = 0;
    if ($_POST['timed'] != 'on')
    {
        $timed = 0;
        // Force allow_reopen=no for timed incidents, since reopening will break billing
        $allow_reopen = 'no';
    }
    else $timed = 1;

    $sql = "UPDATE `{$dbServiceLevels}` SET initial_response_mins='$initial_response_mins', ";
    $sql .= "prob_determ_mins='$prob_determ_mins', ";
    $sql .= "action_plan_mins='$action_plan_mins', ";
    $sql .= "resolution_days='$resolution_days', ";
    $sql .= "review_days='$review_days', ";
    $sql .= "timed='$timed', ";
    $sql .= "allow_reopen='$allow_reopen' ";
    $sql .= "WHERE tag='$tag' AND priority='$priority'";
    mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    //if (mysql_affected_rows() == 0) trigger_error("UPDATE affected zero rows",E_USER_WARNING);
    else
    {
        $billingSQL = "SELECT * FROM `{$dbBillingPeriods}` WHERE servicelevelid = {$id} AND priority = {$priority} AND tag = '{$tag}'";
        $billingResult = mysql_query($billingSQL);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $billing = mysql_fetch_object($billingResult);

        if (!empty($billing))
        {
            //update
            $sql = "UPDATE `{$dbBillingPeriods}` SET customerperiod = '{$customerPeriod}', engineerperiod = '{$engineerPeriod}', `limit` = '{$limit}' ";
            $sql .= "WHERE servicelevelid = '{$id}' AND priority = {$priority} AND tag = '{$tag}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        }
        else
        {
            //insert
            $sql = "INSERT INTO `{$dbBillingPeriods}` (servicelevelid, priority, tag, customerperiod, engineerperiod, `limit`) ";
            $sql .= "VALUES ('{$id}', '{$priority}', '{$tag}', '{$customerPeriod}', '{$engineerPeriod}', '{$limit}')";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        }

        header("Location: service_levels.php");
        exit;
    }
}
?>
