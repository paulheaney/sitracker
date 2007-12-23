<?php
// manager_dashboard.php - Page to install a new dashboard component
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission=66; // Install dashboard components
require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

function beginsWith( $str, $sub ) {
   return ( substr( $str, 0, strlen( $sub ) ) === $sub );
}
function endsWith( $str, $sub ) {
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

// A duplicate of that in setup.php - Probably wants moving to functions.inc.php eventually PH 9/12/07
function setup_exec_sql($sqlquerylist)
{
    global $CONFIG;
    if (!empty($sqlquerylist))
    {
        $sqlqueries = explode( ';', $sqlquerylist);
        // We don't need the last entry it's blank, as we end with a ;
        array_pop($sqlqueries);
        foreach ($sqlqueries AS $sql)
        {
            mysql_query($sql);
            if (mysql_error())
            {
                $html .= "<p><strong>FAILED:</strong> ".htmlspecialchars($sql)."</p>";
                $html .= "<p class='error'>".mysql_error()."<br />A MySQL error occurred, this could be because the MySQL user '{$CONFIG['db_username']}' does not have appropriate permission to modify the database schema.<br />";
                //echo "The SQL command was:<br /><code>$sql</code><br />";
                $html .= "An error might also be caused by an attempt to upgrade a version that is not supported by this script.<br />";
                $html .= "Alternatively, you may have found a bug, if you think this is the case please report it.</p>";
            }
            else $html .= "<p><strong>OK:</strong> ".htmlspecialchars($sql)."</p>";
        }
    }
    return $html;
}

switch ($_REQUEST['action'])
{
    case 'install':
        include ('htmlheader.inc.php');

        $sql = "SELECT name FROM `{$dbDashboard}`";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/dashboard.png' width='32' height='32' alt='' /> ";
        echo "Install new dashboard component</h2>";
        echo "<p align='center'>Please note the component must has been placed in the dashboard directory and named <var>dashboard_NAME</var></p>";
        while ($dashboardnames = mysql_fetch_object($result))
        {
            $dashboard[$dashboardnames->name] = $dashboardnames->name;
        }

        $path = "{$CONFIG['application_fspath']}dashboard/";

        $dir_handle = @opendir($path) or die("Unable to open dashboard directory $path");

        while ($file = readdir($dir_handle))
        {
            if (beginsWith($file, "dashboard_") && endsWith($file, ".php"))
            {
                //echo "file name ".$file."<br />";
                if (empty($dashboard[substr($file, 10, strlen($file)-14)]))  //this is 14 due to .php =4 and dashboard_ = 10
                {
                    //echo "file name ".$file." - ".substr($file, 10, strlen($file)-14)."<br />";
                    //$html .= "echo "<option value='{$row->id}'>$row->realname</option>\n";";
                    $html .= "<option value='".substr($file, 10, strlen($file)-14)."'>".substr($file, 10, strlen($file)-14)." ({$file})</option>";
                }
            }
        }

        closedir($dir_handle);

        if (empty($html))
        {
            echo "<p align='center'>No new dashboard components available</p>";
            echo "<p align='center'><a href='manage_dashboard.php'>{$strBackToList}</a></p>";
        }
        else
        {
            echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>\n";
            echo "<table align='center' class='vertical'><tr><td>\n";
            echo "<select name='comp[]' multiple='multiple' size='20'>\n";
            echo $html;
            echo "</select>\n";
            echo "</td></tr></table>\n";
            echo "<input type='hidden' name='action' value='installdashboard' />";
            echo "<p align='center'><input type='submit' value='{$strInstall}' /></p>";
            echo "</form>\n";
        }

        include ('htmlfooter.inc.php');

        break;
    case 'installdashboard':
        $dashboardcomponents = $_REQUEST['comp'];
        if (is_array($dashboardcomponents))
        {
            $count = count($dashboardcomponents);

            $sql = "INSERT INTO `{$dbDashboard}` (name) VALUES ";
            for($i = 0; $i < $count; $i++)
            {
                $sql .= "('{$dashboardcomponents[$i]}'), ";
            }
            $result = mysql_query(substr($sql, 0, strlen($sql)-2));
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            if (!$result) echo "<p class='error'>Instalation of plugin(s) failed</p>";
            else
            {
                // run the post install compoents
                foreach ($dashboardcomponents AS $comp)
                {
                    include ("{$CONFIG['application_fspath']}dashboard/dashboard_{$comp}.php");
                    $func = "dashboard_".$comp."_install";
                    if (function_exists($func)) $func();
                }

                html_redirect("manage_dashboard.php");
            }
        }
        break;
    case 'upgradecomponent':
        $id = $_REQUEST['id'];
        $sql = "SELECT * FROM `{$dbDashboard}` WHERE id = {$id}";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if (mysql_num_rows($result) > 0)
        {
            $obj = mysql_fetch_object($result);

            $version = 1;
            include ("{$CONFIG['application_fspath']}dashboard/dashboard_{$obj->name}.php");
            $func = "dashboard_{$obj->name}_get_version";

            if (function_exists($func))
            {
                $version = $func();
            }

            if ($version > $dashboardnames->version)
            {
                // apply all upgrades since running version
                $func = "dashboard_{$obj->name}_upgrade";

                if (function_exists($func))
                {
                    $schema = $func();
                    for($i = $obj->version; $i <= $version; $i++)
                    {
                        setup_exec_sql($schema[$i]);
                    }

                    $sql = "UPDATE dashboard SET version = '{$version}' WHERE id = {$obj->id}";
                    mysql_query($sql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                    html_redirect($_SERVER['PHP_SELF']);
                }
                else
                {
                    echo "<p class='error'>No schema available to upgrade</p>"; //TODO i18n
                }
            }
            else
            {
                echo "<p class='error'>No upgrades for {$obj->name} dashboard component</p>"; //TODO i18n
            }
        }
        else
        {
            echo "<p class='error'>Dashboard component {$id} doesn't exist</p>"; //TODO i18n
        }

        break;
    case 'enable':
        $id = $_REQUEST['id'];
        $enable = $_REQUEST['enable'];
        $sql = "UPDATE dashboard SET enabled = '{$enable}' WHERE id = '{$id}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if (!$result)
        {
            echo "<p class='error'>Changed enabled state failed</p>"; //TODO i18n
        }
        else
        {
            html_redirect("manage_dashboard.php");
        }
        break;
    default:
        include ('htmlheader.inc.php');

        $sql = "SELECT * FROM `{$dbDashboard}`";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/dashboard.png' width='32' height='32' alt='' /> ";
        echo "{$strManageDashboardComponents}</h2>";
        echo "<table class='vertical' align='center'><tr>";
        echo colheader('id',$strID);
        echo colheader('name',$strName);
        echo colheader('enabled',$strEnabled);
        echo colheader('version',$strVersion);
        echo colheader('upgrade',"Upgrade"); //FIXME i18n after release
        echo "</tr>";
        while ($dashboardnames = mysql_fetch_object($result))
        {
            if ($dashboardnames->enabled == "true") $opposite = "false";
            else $opposite = "true";
            echo "<tr class='shade2'><td>{$dashboardnames->id}</td>";
            echo "<td>{$dashboardnames->name}</td>";
            echo "<td><a href='".$_SERVER['PHP_SELF']."?action=enable&amp;id={$dashboardnames->id}&amp;enable={$opposite}'>{$dashboardnames->enabled}</a></td>";

            echo "<td>{$dashboardnames->version}</td>";
            echo "<td>";

            $version = 1;
            include ("{$CONFIG['application_fspath']}dashboard/dashboard_{$dashboardnames->name}.php");
            $func = "dashboard_{$dashboardnames->name}_get_version";

            if (function_exists($func))
            {
                $version = $func();
            }

            if ($version > $dashboardnames->version)
            {
                echo "<a href='{$_SERVER['PHP_SELF']}?action=upgradecomponent&amp;id={$dashboardnames->id}'>{$strYes}</a>";
            }
            else
            {
                echo $strNo;
            }

            echo "</td></tr>";
        }
        echo "</table>";

        echo "<p align='center'><a href='".$_SERVER['PHP_SELF']."?action=install'>{$strInstall}</a></p>";

        include ('htmlfooter.inc.php');
        break;
}

?>