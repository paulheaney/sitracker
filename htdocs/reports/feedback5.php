<?php
// feedback5.php - Feedback scores by Product
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2006 Salford Software Ltd.
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas

$permission=37; // Run Reports

require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

include('htmlheader.inc.php');

$formid=6;
$now = time();

echo "<div style='margin: 20px'>";
echo "<h2><a href='{$CONFIG['application_webpath']}reports/feedback.php'>Feedback</a> Scores: By Product</h2>";
echo "<p>This report shows customer responses and a percentage figure indicating the overall positivity of customers toward ";
echo "incidents logged by the user(s) shown:</p>";

$qsql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}' AND type='rating' ORDER BY taborder";
$qresult = mysql_query($qsql);
if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
while ($qrow = mysql_fetch_object($qresult))
{
    $q[$qrow->taborder]=$qrow;
}

$msql = "SELECT *,  \n";
$msql .= "feedbackreport.id AS reportid, \n";
$msql .= "products.id AS productid, products.name AS productname ";
$msql .= "FROM feedbackreport, incidents, products WHERE feedbackreport.incidentid=incidents.id \n";
$msql .= "AND incidents.product=products.id ";
$msql .= "AND feedbackreport.incidentid > 0 \n";
$msql .= "AND feedbackreport.completed = 'yes' \n"; ///////////////////////
$msql .= "ORDER BY products.name, incidents.id ASC \n";

$mresult = mysql_query($msql);
if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);

if (mysql_num_rows($mresult) >= 1)
{

    $previd=0;
    $countcontacts=0;
    $zero=0;
    $ten=0;
    $twenty=0;
    $thirty=0;
    $forty=0;
    $fifty=0;
    $sixty=0;
    $seventy=0;
    $eighty=0;
    $ninety=0;
    $hundred=0;
    $surveys=0;
    if ($CONFIG['debug']) echo "<h4>$msql</h4>";

    $firstrun=0;
    while ($mrow = mysql_fetch_object($mresult))
    {
        // Only print if we have a value ({$previd} / {$mrow->contactid})
        if ($previd!=$mrow->productid AND $firstrun!=0)
        {
            $numones=count($storeone);
            // if ($numones<10) $numones=10;
            ## echo "<h2><a href='/contact_details.php?id={$mrow->contactid}' title='Jump to Contact'>{$mrow->forenames} {$mrow->surname}</a>, {$mrow->department} &nbsp; <a href='#' title='Jump to site'>{$mrow->sitename}</a></h2>";
            ## $html .= "<h3>[[$mrow->contactid]]</h3>";
            if ($numones>0)
            {
                for($c=1;$c<=$numones;$c++)
                {
                    if ($storeone[$c]>0) $qr=number_format($storeone[$c]/$storetwo[$c],2);
                    else $qr=0;
                    if ($storeone[$c]>0) $qp=number_format((($storeone[$c] / (9 * $storetwo[$c])) * 100), 0);
                    else $qp=0;
                    $html .= "Q$c: {$q[$c]->question} {$qr} <strong>({$qp}%)</strong><br />";
                    $gtotal+=$qr;
                }
                if ($c>0) $c--;
                $total_average=number_format($gtotal/$c,2);
                $total_percent=number_format((($gtotal / (9 * $c)) * 100), 0);

                ## ($gtotal)($c)
                $html .= "<p>Positivity: {$total_average} <strong>({$total_percent}%)</strong>, after $surveys surveys</p>";
                //print_r($storeone);
                //print_r($storetwo);
            }
            else $html = ""; // don't print name where theres  no survey data

            if ($total_average>0)
            {
                echo "{$html}";
                $countcontacts++;

                // Stats
                if ($total_percent>0 AND $total_percent < 10) $zero++;
                if ($total_percent>=10 AND $total_percent < 20) $ten++;
                if ($total_percent>=20 AND $total_percent < 30) $twenty++;
                if ($total_percent>=30 AND $total_percent < 40) $thirty++;
                if ($total_percent>=40 AND $total_percent < 50) $forty++;
                if ($total_percent>=50 AND $total_percent < 60) $fifty++;
                if ($total_percent>=60 AND $total_percent < 70) $sixty++;
                if ($total_percent>=70 AND $total_percent < 80) $seventy++;
                if ($total_percent>=80 AND $total_percent < 90) $eighty++;
                if ($total_percent>=90 AND $total_percent < 100) $ninety++;
                if ($total_percent>=100) $hundred++;
                ## echo "\n<hr />\n";
            }
            // if ($total_average>0) echo "<code>{$dbg}</code>";
            unset($qavgavg);
            unset($qanswer);
            unset($dbg);
            unset($storeone);
            unset($storetwo);
            unset($gtotal);
            $surveys=0;
        }
        $firstrun=1;

    // Loop through reports
    $totalresult=0;
    $numquestions=0;
    $surveys++;
    //$html = "<h2>Incident <a href='/incident_details.php?id={$mrow->incidentid}' title='Jump to Incident'>{$mrow->incidentid}</a>: <a href='#' title='Jump to Contact'>{$mrow->forenames} {$mrow->surname}</a>, {$mrow->department}, <a href='#' title='Jump to site'>{$mrow->sitename}</a></h2>";
    //$html .= "<p><strong>{$mrow->title}</strong>, opened ".date("l jS F Y @ g:i a", $mrow->opened)." for ".format_seconds($mrow->duration)." and {$mrow->closingstatusname} on ".date("l jS F Y @ g:i a", $mrow->closed)."</p>";
    // $html = "<h2><a href='/contact_details.php?id={$mrow->contactid}' title='Jump to Contact'>{$mrow->forenames} {$mrow->surname}</a>, {$mrow->department} &nbsp; <a href='#' title='Jump to site'>{$mrow->sitename}</a></h2>";
    //$html = "<h2>{$mrow->department}&nbsp; <a href='site_details.php?id={$mrow->siteid}' title='Jump to site'>{$mrow->sitename}</a></h2>";
    $html = "<h2><a href='#?id={$mrow->productid}' title='Jump to product'>{$mrow->productname}</a></h2>";
    $qsql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}' AND type='rating' ORDER BY taborder";
    $qresult = mysql_query($qsql);
    ## echo "$qsql";

    if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
    while ($qrow = mysql_fetch_object($qresult))
    {
        $numquestions++;
        // $html .= "Q{$qrow->taborder}: {$qrow->question} &nbsp;";
        $sql = "SELECT * FROM feedbackreport, incidents, users, feedbackresults ";
        $sql .= "WHERE feedbackreport.incidentid=incidents.id ";
        $sql .= "AND incidents.owner=users.id ";
        $sql .= "AND feedbackreport.id=feedbackresults.respondentid ";
        $sql .= "AND feedbackresults.questionid='$qrow->id' ";
        $sql .= "AND feedbackreport.id='$mrow->reportid' ";
        $sql .= "ORDER BY incidents.contact, incidents.id";
        // echo "==== $sql ====";
        $result = mysql_query($sql);

        if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
        $numresults=0;
        $cumul=0;
        $percent=0;
        $average=0;
        $answercount=mysql_num_rows($result);

        if ($answercount>0)
        {
            ## echo "[{$mrow->reportid}] ";
            //echo "answercount = $answercount <br >";
            while ($row = mysql_fetch_object($result))
            {
                // Loop through the results
                if (!empty($row->result))
                {
                    $cumul+=$row->result;
                    // echo "---&gt; {$mrow->surname} Q{$qrow->taborder} Result: {$row->result}<br />";
                    $numresults++;
                    $storeone[$qrow->taborder]+=$row->result;
                    $storetwo[$qrow->taborder]++;
                    $storethree[$qrow->taborder]=$qrow->id;
                }
            }
        }

        if ($numresults>0) $average=number_format(($cumul/$numresults), 2);
        $percent =number_format((($average / 9) * 100), 0);
        $totalresult+=$average;

        $qanswer[$qrow->taborder]+=$average;
        $qavgavg=$qanswer[$qrow->taborder];
        //$html .= "{$average} <strong>({$percent}%)</strong>";
        //$html .= "<br />";
    }
    // $answercount Survey(s) Returned -
    // $html .= "<p>Positivity: {$total_average} <strong>({$total_percent}%)</strong>, after $numresults surveys</p>";
    // $html .= "<hr />\n";

    $previd=$mrow->productid;
    // echo "Total Avg: {$total_average}<hr />\n";
    }

    /*
    echo "<p>This graph shows different levels of positivity of the contacts shown above:</p>";

    // echo $zero+$ten+$twenty+$thirty+$forty+$fifty+$sixty+$seventy+$eighty+$ninety+$hundred;

    $adjust=13;
    $min=4;
    $zero=number_format((($zero / $countcontacts) * 100), 0);
    $ten=number_format((($ten / $countcontacts) * 100), 0);
    $twenty=number_format((($twenty / $countcontacts) * 100), 0);
    $thirty=number_format((($thirty / $countcontacts) * 100), 0);
    $forty=number_format((($forty / $countcontacts) * 100), 0);
    $fifty=number_format((($fifty / $countcontacts) * 100), 0);
    $sixty=number_format((($sixty / $countcontacts) * 100), 0);
    $seventy=number_format((($seventy / $countcontacts) * 100), 0);
    $eighty=number_format((($eighty / $countcontacts) * 100), 0);
    $ninety=number_format((($ninety / $countcontacts) * 100), 0);
    $hundred=number_format((($hundred / $countcontacts) * 100), 0);
    echo "<div style='background: #B00; color: #FFF; float:left; width: ".($min + ($zero * $adjust))."px;'>&nbsp;</div>&nbsp; 0-9% ({$zero}%)<br />";
    echo "<div style='background: #993300; color: #FFF; float:left; width: ".($min + ($ten * $adjust))."px;'>&nbsp;</div>&nbsp; 10-19% ({$ten}%)<br />";
    echo "<div style='background: #993300; color: #FFF; float:left; width: ".($min + ($twenty * $adjust))."px;'>&nbsp;</div>&nbsp; 20-29% ({$twenty}%)<br />";
    echo "<div style='background: #996600; color: #FFF; float:left; width: ".($min + ($thirty * $adjust))."px;'>&nbsp;</div>&nbsp; 30-39% ({$thirty}%)<br />";
    echo "<div style='background: #996600; color: #FFF; float:left; width: ".($min + ($forty * $adjust))."px;'>&nbsp;</div>&nbsp; 40-49% ({$forty}%)<br />";
    echo "<div style='background: #999900; color: #000; float:left; width: ".($min + ($fifty * $adjust))."px;'>&nbsp;</div>&nbsp; 50-59% ({$fifty}%)<br />";
    echo "<div style='background: #999900; color: #000; float:left; width: ".($min + ($sixty * $adjust))."px;'>&nbsp;</div>&nbsp; 60-69% ({$sixty}%)<br />";
    echo "<div style='background: #99CC00; color: #000; float:left; width: ".($min + ($seventy * $adjust))."px;'>&nbsp;</div>&nbsp; 70-79% ({$seventy}%)<br />";
    echo "<div style='background: #99CC00; color: #000; float:left; width: ".($min + ($eighty * $adjust))."px;'>&nbsp;</div>&nbsp; 80-89% ({$eighty}%)<br />";
    echo "<div style='background: #99FF00; color: #000; float:left; width: ".($min + ($ninety * $adjust))."px;'>&nbsp;</div>&nbsp; 90-99% ({$ninety}%)<br />";
    echo "<div style='background: #99FF00; color: #000; float:left; width: ".($min + ($hundred * $adjust))."px;'>&nbsp;</div>&nbsp; 100% ({$hundred}%)<br />";
    */
}
else echo "<p class='error'>No feedback found</p>";
echo "</div>\n";
include('htmlfooter.inc.php');
include('db_disconnect.inc.php');

?>
