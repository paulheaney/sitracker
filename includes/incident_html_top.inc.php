<?php
session_name($CONFIG['session_name']);
session_start();
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\"><head><title>";
if (isset($title)) echo $title;
else echo $application_name;
echo "</title>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
echo "<meta name=\"GENERATOR\" content=\"{$CONFIG['application_name']} {$application_version_string}\" />\n";
echo "<style type='text/css'>@import url('{$CONFIG['application_webpath']}styles/webtrack.css');</style>\n";
if ($_SESSION['auth'] == TRUE)
{
    $style = interface_style($_SESSION['style']);
    echo "<link rel='stylesheet' href='{$CONFIG['application_webpath']}styles/{$style['cssurl']}' />\n";
}
else
{
    echo "<link rel=\"stylesheet\" href=\"styles/webtrack1.css\" />\n";
}

echo "<script src='{$CONFIG['application_webpath']}webtrack.js' type='text/javascript'></script>\n";
// javascript popup date library
echo "<script src='{$CONFIG['application_webpath']}calendar.js' type='text/javascript'></script>\n";

// if ($userstyle == "1") echo "<link rel=\"stylesheet\" href=\"styles/webtrack1.css\">\n";
// if ($userstyle == "2") echo "<link rel=\"stylesheet\" href=\"styles/webtrack2.css\">\n";
// FIXME put here some js to set action field then post form
?>
<script type='text/javascript'>
function gotab(tab) {
    document.actiontabs.action.value=tab;
    document.actiontabs.submit();
}
</script>


<style type='text/css'>

#detailsummary
{
    background: #F7FAFF;
    margin-left: auto;
    margin-right: auto;
    border-bottom: 3px solid #3165CD;
}
#detailsummary table, .detailentry table
{
    width: 100%;
    background-color: transparent;
}
#detailsummary table td
{
    vertical-align: top;
}
#detailsummary h1
{
    font-size: 150%;
    margin: 0px;
    padding: 0px;
    text-align: center;
}
#detailsummary img
{
    vertical-align: text-top;
}

div.detailentry, div.detailentryhidden
{
    border: 1px dotted #9C9C9C;
    width: 95%;
    background: #F7FAFF; /* #F7FAFF; */
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 2em;
    padding-top: 20px;
    padding-left: 0.5em;
    padding-right: 0.5em;
    padding-bottom: 0.2em;
}

.detailentry img, .detailentryhidden img { border: 0px; }

.detailentry table tr:hover { color: white; }
.detailentry table td { background-color: transparent; }

div.detailhead, div.detailinfo, div.detailheadhidden, div.detailinfohidden
{
    border: 1px solid #CDCEFF;
    background: #F0F0FF;  /* #C5B2D5; */
    padding: 0.2em 0.7em;
    font-size: 120%;
    margin-top: 10px;
    -moz-border-radius: 6px;
    top: 10px;
    margin-left: 7%;
}

div.detailentryhidden, div.detailheadhidden { background: transparent; }

div.detailhead, div.detailheadhidden
{
    max-width: 80%;
    width: 80%;
    position: relative;
}
div.detailinfo, div.detailinfohidden
{
    width: 80%;
    margin-bottom: 2em;
    position: relative;
}
div.detailinfohidden, div.detailheadhidden
{
    border-color: #BDBDBD;
    background: white;
    /* color: #8A888A; */
}
div.detaildate
{
    float: right;
    color: #06377F;
    padding-left: 1em;
    position: relative;
    top: 3px;
}
div.detailfoot
{
    border: 1px solid black;
    width: 20%;
    background: #D5DEE6;
    margin-bottom: 1em;
    margin-left: 60%;
}


#tabcontainer
{
    width: 100%;
    background: #fff;
    padding-left: 0px;
    border-bottom: 2px dotted #9C9C9C;
}
#tabnav
{
    height: 20px;
    margin: 0;
    padding-left: 10px;
}

#tabnav li
{
    margin: 0;
    padding: 0;
    display: inline;
    list-style-type: none;
}

#tabnav a:link, #tabnav a:visited, .submit
{
    float: left;
    background: #F6F2FF;
    font-size: 10px;
    line-height: 14px;
    font-weight: bold;
    padding: 2px 10px 2px 10px;
    margin-right: 4px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #666;
}

#tabnav a:link.active, #tabnav a:visited.active, .active
{
    border-bottom: 1px solid #fff;
    background: #fff;
    color: #000;
}

#tabnav a:hover
{
    background: #fff;
}


span.quoteirrel { color: grey; }
span.quote1 { color: #749BC2; }
span.quote2 { color: green; }
span.quote3 { color: magenta; }
span.quote4 { color: #FF9933; }
span.sig { color: red; }

.on {background-color:#84C1DF;}
.off {background-color:white;}

#navmenu
{
    color: white;
    background: black;
    padding-left: 20px;
    padding-top: 2px;
    padding-bottom: 2px;
}
#navmenu a { color: white; }

</style>
<script type="text/javascript" src="helptip.js"></script>
<script type="text/javascript">
<!--
function confirm_addword()
{
  return window.confirm("If you add this word to the dictionary, all future spell checks will use this as the correct spelling for all users.  Are you sure you want to continue?");
}

function email_window(incidentid)
{
  URL = "email_incident.php?menu=hide&id=" + incidentid;
  window.open(URL, "email_window", "toolbar=yes,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=700,height=600");
}

function close_window(incidentid)
{
  URL = "close_incident.php?menu=hide&id=" + incidentid;
  window.open(URL, "email_window", "toolbar=yes,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=700,height=600");
}

function help_window(helpid)
{
  URL = "help.php?id=" + helpid;
  window.open(URL, "help_window", "toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=500");
}


if(document.layers)
{
   thisbrowser="NN4";
}
if(document.all)
{
   thisbrowser="ie"
}
if(!document.all && document.getElementById)
{
   thisbrowser="NN6";
}


function ShowHide(button,element)
{
  if (thisbrowser=="NN6" | thisbrowser=="NN4")
  {
      if (document.getElementById(element).style.display != "none") {
        document.getElementById(element).style.display = "none";
        // document.getElementById(button).title = "Double Click to Expand";
        document.getElementById(button).innerHTML = "[+]";
      }
      else {
        document.getElementById(element).style.display = "";
        document.getElementById(button).innerHTML = "[-]";
        // document.getElementById(button).title = "Double Click to Collapse";
      }
  }

  if (thisbrowser=="ie")
  {
      if (document.all[element].style.display != "none") {
        document.all[element].style.display = "none";
        // document.all[button].title = "Double Click to Expand";
        document.all[button].innerHTML = "[+]";
      }
      else {
        document.all[element].style.display = "";
        // document.all[button].title = "Double Click to Collapse";
        document.all[button].innerHTML = "[-]";
      }
  }


}

function Hide(button,element)
{
  if (thisbrowser=="NN6")
  {
      document.getElementById(element).style.display = "none";
      document.getElementById(button).innerHTML = "Expand";
  }
  if (thisbrowser=="ie")
  {
      document.all[element].style.display = "none";
      document.all[button].innerHTML = "Expand";
  }
}

//-->
</script>
</head>
<body onload="self.focus()">

<?php
$incidentid=$id;
// Retrieve incident
// extract incident details
$sql  = "SELECT *, incidents.id AS incidentid, ";
$sql .= "contacts.id AS contactid ";
$sql .= "FROM incidents, contacts ";
$sql .= "WHERE (incidents.id='{$incidentid}' AND incidents.contact=contacts.id) ";
$sql .= " OR incidents.contact=NULL ";
$result = mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
$incident = mysql_fetch_object($result);
$site_name=site_name($incident->siteid);
$product_name=product_name($incident->product);
if ($incident->softwareid > 0) $software_name=software_name($incident->softwareid);
$servicelevel_id=maintenance_servicelevel($incident->maintenanceid);
$servicelevel_tag = $incident->servicelevel;
if ($servicelevel_tag=='') $servicelevel_tag = servicelevel_id2tag(maintenance_servicelevel($incident->maintenanceid));
$servicelevel_name=servicelevel_name($servicelevelid);
$opened_for=format_seconds(time() - $incident->opened);

// Lookup the service level times
$slsql = "SELECT * FROM servicelevels WHERE tag='{$servicelevel_tag}' AND priority='{$incident->priority}' ";
$slresult = mysql_query($slsql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
$servicelevel = mysql_fetch_object($slresult);

// Get next target
$target = incident_get_next_target($incidentid);
// Calculate time remaining in SLA
$working_day_mins = ($CONFIG['end_working_day'] - $CONFIG['start_working_day']) / 60;
switch ($target->type)
{
    case 'initialresponse': $slatarget=$servicelevel->initial_response_mins; break;
    case 'probdef': $slatarget=$servicelevel->prob_determ_mins; break;
    case 'actionplan': $slatarget=$servicelevel->action_plan_mins; break;
    case 'solution': $slatarget=($servicelevel->resolution_days * $working_day_mins); break;
    default: $slaremain=0; $slatarget=0;
}

if ($slatarget >0) $slaremain=($slatarget - $target->since);
else $slaremain=0;
$targettype = target_type_name($target->type);

// Get next review time
$reviewsince = incident_get_next_review($incidentid);  // time since last review in minutes
$reviewtarget = ($servicelevel->review_days * $working_day_mins);          // how often reviews should happen in minutes
if ($reviewtarget > 0) $reviewremain=($reviewtarget - $reviewsince);
else $reviewremain = 0;

// Color the title bar according to the SLA and priority
$class='';
if ($slaremain <> 0 AND $incident->status!=2)
{
    if (($slaremain - ($slatarget * ((100 - $CONFIG['notice_threshold']) /100))) < 0 ) $class='notice';
    if (($slaremain - ($slatarget * ((100 - $CONFIG['urgent_threshold']) /100))) < 0 ) $class='urgent';
    if (($slaremain - ($slatarget * ((100 - $CONFIG['critical_threshold']) /100))) < 0 ) $class='critical';
    if ($incidents["priority"]==4) $class='critical';  // Force critical incidents to be critical always
}

// Print a table showing summary details of the incident

// Tempory hack, don't show this for old incident details page
//if (strpos($_SERVER['PHP_SELF'], 'incident_details.php')===FALSE)
//{
    echo "<h1 class='$class'>{$title}{$incidentid} - {$incident->title}</h1>";
//}

// echo "<h1>$title</h1>";
echo "<div id='navmenu'>";
if ($menu != 'hide')
{
    if (incident_status($id) != 2)
    {
        echo "<a class='barlink' href='update_incident.php?id={$id}&amp;popup={$popup}' accesskey='U'><em>U</em>pdate</a> | ";
        echo "<a class='barlink' href='javascript:close_window({$id});'>Close</a> | ";
        echo "<a class='barlink' href='reassign_incident.php?id={$id}&amp;popup={$popup}' accesskey='R'><em>R</em>eassign</a> | ";
        echo "<a class='barlink' href='edit_incident.php?id={$id}&amp;popup={$popup}'>Edit</a> | ";
        echo "<a class='barlink' href='incident_service_levels.php?id={$id}&amp;popup={$popup}' accesskey='S'><em>S</em>ervice</a> | ";
        echo "<a class='barlink' href='incident_relationships.php?id={$id}&amp;tab=relationships'>Relations</a> | ";
        echo "<a class='barlink' href='javascript:email_window({$id})' accesskey='E'><em>E</em>mail</a> | ";
        echo "<a class='barlink' href='incident_attachments.php?id={$id}&amp;popup={$popup}' accesskey='F'><em>F</em>iles</a> | ";
        echo "<a class='barlink' href='incident_details.php?id={$id}&amp;popup={$popup}' accesskey='D'><em>D</em>etails And Log</a> | ";
        echo "<a class='barlink' href='javascript:help_window({$permission});'>?</a>";
        if (!empty($_REQUEST['popup'])) echo " | <a class=barlink href='javascript:window.close();'>Close Window</a>";
    }
    else
    {
        echo "<a class='barlink' href='reopen_incident.php?id={$id}&amp;popup={$popup}'>Reopen</a> | ";
        echo "<a class='barlink' href='incident_service_levels.php?id={$id}&amp;popup={$poup}' accesskey='S'><em>S</em>ervice</a> | ";
        echo "<a class='barlink' href='incident_relationships.php?id={$id}&amp;tab=relationships'>Relations</a> | ";
        echo "<a class='barlink' href='incident_attachments.php?id={$id}&amp;popup={$popup}' accesskey='F'><em>F</em>iles</a> | ";
        echo "<a class='barlink' href='incident_details.php?id={$id}&amp;popup={$popup}' accesskey='D'><em>D</em>etails And Log</a> | ";
        echo "<a class='barlink' href='javascript:help_window({$permission});'>?</a>";
        if (!empty($_REQUEST['popup'])) echo " | <a class='barlink' href='javascript:window.close();'>Close Window</a>";
    }
}
else
{
    echo "<a class='barlink' href='javascript:window.close();'>Close Window</a>";
}
echo "</div>";

?>