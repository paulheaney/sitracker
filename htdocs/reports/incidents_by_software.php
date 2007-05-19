<?php
// incidents_by_software.php - List the number of incidents for each software
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2006 Salford Software Ltd.
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Authors: Paul Heaney <paulheaney[at]users.sourceforge.net>

// Requested by Tech Support team (26 Spet 06)

// Notes:
//  Counts activate calls withing the specified period (i.e. those with a lastupdate time > timespecified)

$permission=37; // Run Reports
$title='Incidents by Software';
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');


if (empty($_REQUEST['mode']))
{
    include('htmlheader.inc.php');

    echo "<h2>$title</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' id='incidentsbysoftware' method='post'>";
    echo "<table class='vertical'>";
    echo "<tr><th>Start Date:</th>";
    echo "<td><input type='text' name='startdate' id='startdate' size='10' /> ";
    echo date_picker('incidentsbysoftware.startdate');
    echo "</td></tr>";
    echo "<tr><th>Month breakdown:</th><td><input type='checkbox' name='monthbreakdown' /></td></tr>";
    echo "<tr><th>Software name</th><td><input type='text' name='software' id='software' size='20'/></td></tr>";
    echo "</table>";
    echo "<p align='center'>";
    echo "<input type='hidden' name='mode' value='report' />";
    echo "<input type='submit' value='Run Report' />";
    echo "</p>";
    echo "</form>";

    include('htmlfooter.inc.php');
}
else
{
    $monthbreakdownstatus = $_REQUEST['monthbreakdown'];
    $startdate = strtotime($_REQUEST['startdate']);
    $sql = "SELECT count(software.id) AS softwarecount, software.name, software.id ";
    $sql .= "FROM software, incidents ";
    $sql .= "WHERE software.id = incidents.softwareid AND incidents.opened > '{$startdate}' ";
    $software = $_REQUEST['software'];
    if(!empty($software)) $sql .= "AND software.name LIKE '%{$software}%' ";
    $sql .= "GROUP BY software.id ORDER BY softwarecount DESC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    $countArray[0]=0;
    $softwareNames[0]='Name';
    $softwareID[0] = 0;
    $c = 0;
    $count = 0;
    while($row = mysql_fetch_array($result))
    {
        $countArray[$c] = $row['softwarecount'];
        $count += $countArray[$c];
        $softwareNames[$c]  = $row['name'];
        $softwareID[$c] = $row['id'];
        $c++;
    }

    include('htmlheader.inc.php');

    $sqlSLA = "SELECT DISTINCT(tag) FROM servicelevels";
    $resultSLA = mysql_query($sqlSLA);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    echo "<h2>Number of incidents by software since ".$_REQUEST['startdate']."</h2>";
    echo "<p>";
    echo "<table class='vertical' align='center'>";
    echo "<tr><th>Number of calls</th><th>%</th><th>Software</th>";
    while($sla = mysql_fetch_object($resultSLA))
    {
        echo "<th>".$sla->tag."</th>";
        $slas[$sla->tag]['name'] = $sla->tag;
        $slas[$sla->tag]['notEscalated'] = 0;
        $slas[$sla->tag]['escalated'] = 0;
    }
    echo "<tr>";

    $others=0;
    for($i = 0; $i < $c; $i++)
    {
        if ($i<=25)
        {
            $data .= $countArray[$i]."|";
            $percentage = number_format(($countArray[$i]/$count) * 100,1);
            $legend .= $softwareNames[$i]." ({$percentage}%)|";
        }
        else
        {
            $others += $countArray[$i];
        }

        $sqlN = "SELECT id, servicelevel, opened FROM incidents WHERE softwareid = '".$softwareID[$i]."'";
        $sqlN .= " AND opened > '{$startdate}' ORDER BY opened";

        $resultN = mysql_query($sqlN);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $numrows = mysql_num_rows($resultN);

        foreach($slas AS &$slaReset)
        {
            $slaReset['notEscalated'] = 0;
            $slaReset['escalated'] = 0;
        }


        if($numrows > 0)
        {
            unset($monthbreakdown);
            while($obj = mysql_fetch_object($resultN))
            {
                $datestr = date("M y",$obj->opened);

                $sqlL = "SELECT count(id) FROM updates WHERE updates.bodytext LIKE \"External ID%\" AND incidentid = '".$obj->id."'";
                $resultL = mysql_query($sqlL);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                list($numrowsL) = mysql_fetch_row($resultL);

                if($numrowsL > 0) $slas[$obj->servicelevel]['escalated']++;
                else $slas[$obj->servicelevel]['notEscalated']++;

                $monthbreakdown[$datestr][$obj->servicelevel]++;
                $monthbreakdown[$datestr]['month']=$datestr;
            }
        }

        echo "<tr><td class='shade1'>{$countArray[$i]}</td>";
        echo "<td class='shade1'>{$percentage}%</td>";
        echo "<td class='shade1'>$softwareNames[$i]</td>";

        foreach($slas AS $sla)
        {
            echo "<td class='shade1'>";
            echo ($sla['notEscalated']+$sla['escalated'])."/".$sla['escalated'];
            echo "</td>";
        }

        if($monthbreakdownstatus === "on")
        {

            echo "<tr><td class='shade1' /><td colspan='".(count($slas)+2)."' class='shade1'>";
            echo "<table><tr>";
            foreach($monthbreakdown AS $month) echo "<th>{$month['month']}</th>";
            echo "</tr><tr>";
            foreach($monthbreakdown AS $month)
            {//echo "<pre>".print_r($month)."</pre>";
	            echo "<td><table>";
                $total=0;
                foreach($slas AS $slaNames)
                {
                    if(empty($month[$slaNames['name']])) $month[$slaNames['name']] = 0;
                    echo "<tr>";
                    echo "<td>".$slaNames['name']."</td><td>".$month[$slaNames['name']]."</td>";
                    echo "</tr>";
                    $total+=$month[$slaNames['name']];
                }
	            echo "<tr><td><strong>TOTAL</strong></td><td><strong>";
                echo $total;
                echo "</strong></td></tr>";
	            $monthtotals[$month['month']]['month']=$month['month'];
                $monthtotals[$month['month']]['value']+=$total;
                echo "</table></td>";
            }
            echo "</tr></table>";
            echo "</td></tr>";
        }
    }
    echo "</table>";

    if($monthbreakdownstatus === "on")
    {
        echo "<p><table align='center'>";
        echo "<tr><th>Month</th><th>Number of calls</th></tr>";
        foreach($monthtotals AS $m)
        {
	    echo "<tr>";
	    echo "<th>".$m['month']."</th><td align='center'>".$m['value']."</td><tr>";
	    $total+=$m['value'];
	    echo "</tr>";
        }
        echo "<td><strong>Total</strong></td><td align='center'><strong>{$total}</strong></td></tr>";
        echo "</table></p>";

    }

    $data .= $others."|";
    $percentage = number_format(($others/$count) * 100,1);
    $legend .= "Others ($percentage)|";


    echo "</p>";

    if (extension_loaded('gd'))
    {
        $data = substr($data,0,strlen($data)-1);
        $legend = substr($legend,0,strlen($legend)-1);
        $title = urlencode("Incidents by software");
        echo "\n<br /><p><div style='text-align:center;'>";
        echo "\n<img src='../chart.php?type=pie&data=$data&legends=$legend&title=$title' />";
        echo "\n</div></p>";
    }

    include('htmlfooter.inc.php');

}

?>
