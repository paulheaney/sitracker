<?php
// book_holidays.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page Is Valid XHTML 1.0 Transitional! 13Sep06
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
@include ('set_include_path.inc.php');
$permission = 27; // view your calendar

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

// External variables
$step = $_REQUEST['step'];
$date = cleanvar($_REQUEST['date']);
if (!empty($_REQUEST['user']) AND user_permission($sit[2], 68))
{
    $user = cleanvar($_REQUEST['user']); // Manage holidays
}
else
{
    $user = $sit[2];
}

if (empty($step))
{
    include ('htmlheader.inc.php');
    // The JavaScript date picker used on this page came from an article at
    // http://www.dagblastit.com/~tmcclure/dhtml/calendar.html
    // The website states
    // "You may use the strategies and code in these articles license and royalty free unless otherwise directed.
    // "If I helped you build something cool I'd like to hear about it. Drop me a line at tom@dagblastit.com."

    if ($user == $sit[2])
    {
        echo "<h2>".icon('holiday', 32)." {$strBookHoliday}</h2>";
    }
    else
    {
        echo "<h2>".icon('holiday', 32)." {$strBookHoliday}: ".user_realname($user)."</h2>";
    }

    echo "<form name='date' action='{$_SERVER['PHP_SELF']}' method='post'>\n";
    echo "<table class='vertical'>";
    echo "<tr><th>{$strHolidayType}:</th><td>".holidaytype_drop_down('type', 1)."</td></tr>";
    echo "<tr><th>{$strStartDate}:</th><td title='date picker'>";
    echo "<input name='start' size='10' value='{$date}' /> ";
    echo date_picker('date.start');
    echo "</td></tr>\n";
    echo "<tr><th>{$strEndDate}:</th><td align='left' class='shade1' title='date picker'>";
    echo "<input name='end' size='10' /> ";
    echo date_picker('date.end');
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "<p align='center'>";
    echo "<input type='hidden' name='user' value='{$user}' />";
    echo "<input type='hidden' name='step' value='1' />";
    echo "<input type='submit' value=\"{$strBookHoliday}\" /></p>";
    echo "<p align='center'><a href='holidays.php?user={$user}'>{$strBackToList}</a></p>";
    echo "</form>";
    include ('htmlfooter.inc.php');
}
elseif ($step == '1')
{
    // External variables
    $start = cleanvar($_REQUEST['start']);
    $end = cleanvar($_REQUEST['end']);
    $type = cleanvar($_REQUEST['type']);

    include ('htmlheader.inc.php');
    $start = strtotime("{$start} 09:00");
    $end = strtotime($end);
    if ($start == 0 && $end == 0)
    {
        $start = $today;
        $end = $today;
    }
    elseif ($end == 0 && $start>0)
    {
        $end=$start;
    }
    elseif ($start == 0 && $end > 0)
    {
        $start = $end;
    }

    if ($user == $sit[2])
    {
        echo "<h2>{$strBook} ".holiday_type($type)."</h2>";
    }
    else
    {
        echo "<h2>{$strBook} ".holiday_type($type)." for ".user_realname($user)."</h2>";
    }

    if ($type == HOL_SICKNESS)
    {
        echo "<p class='error'>Sickness, can of course only be booked for today or days that have passed.</p>";// FIXME i18n
    }

    if ($type == HOL_HOLIDAY)
    {
        $entitlement = user_holiday_entitlement($user);
        $holiday_resetdate = user_holiday_resetdate($user);
        $holidaystaken = user_count_holidays($user, HOL_HOLIDAY, $holiday_resetdate);
        if (($entitlement - $holidaystaken) <= 0 )
        {
            echo "<p class='error'>{$strNoHolidayEntitlement}</p>";
        }
    }

    // swap dates around if end is before start
    if ($start > $end)
    {
        $newend = $start;
        $start = $end;
        $end = $newend;
        unset($newend);
    }

    echo "<p align='center'><strong>{$strSelectDays}</strong></p>";

    echo "<table align='center' width='550' class='vertical'>";
    echo "<tr><th>{$strStartDate}</th><td>".ldate($CONFIG['dateformat_date'],$start)."</td></tr>";
    echo "<tr><th>{$strEndDate}</th><td>".ldate($CONFIG['dateformat_date'],$end)."</td></tr>";
    echo "</table><br />";

    echo "<form name='date' action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<input type='hidden' name='user' value='{$user}' />";
    echo "<input type='hidden' name='type' value='{$type}' />";

    echo "<table align='center' width='550'>";
    echo "<tr><th>{$strDate}</th><th>{$strNone}</th><th>{$strDay}</th><th>{$strAM}</th><th>{$strPM}</th></tr>\n";

    $daynumber = 1;
    $options = 0;
    // if ($end==$start)
    $end += 86400;  // ensure we still loop for single day bookings by setting end to next day
    for ($day = $start; $day < $end; $day = $day + 86400)
    {
        if (date('D',$day) != 'Sat' && date('D',$day) != 'Sun')
        {
            $sql = "SELECT * FROM `{$dbHolidays}` WHERE `date` = FROM_UNIXTIME($day) AND userid='{$user}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

            // need to do something different when there are more than one row
            if (mysql_num_rows($result) > 0)
            {
                while ($existing_holiday = mysql_fetch_array($result))
                {
                    $holiday_type = holiday_type($existing_holiday['type']);
                    $holiday_legend = strtoupper(substr($holiday_type,0,1));
                    echo "<tr>";
                    echo "<td class='shade2' align='right'> ".ldate('l jS M y',$day)." </td>";
                    echo "<td class='shade1' align='center'>";
                    if ($existing_holiday['length'] == 'day')
                    {
                        echo "<input type='radio' name='dummy{$daynumber}' value='day' disabled='disabled' />";
                    }
                    else
                    {
                        echo "<input type='radio' name='length{$daynumber}' value='none' checked='checked' />";
                    }
                    echo "</td>";
                    echo "<td class='shade1' align='center'>";
                    if ($existing_holiday['length'] == 'day')
                    {
                        echo $holiday_legend;
                    }
                    else
                    {
                        echo "<input type='radio' name='dummy{$daynumber}' value='day' disabled='disabled' />";
                    }
                    echo "</td>";

                    // am
                    echo "<td class='shade2' align='center'>";
                    if ($existing_holiday['length'] == 'am' )
                    {
                        echo $holiday_legend;
                    }
                    elseif ($existing_holiday['length'] != 'day')
                    {
                        if (($type == HOL_SICKNESS && $day < $today) || ($type != HOL_SICKNESS))
                        {
                            echo "<input type='radio' name='length{$daynumber}' value='am' checked='checked' />";
                            $options++;
                        }
                        else echo "<input type='radio' name='dummy{$daynumber}' disabled='disabled' />";
                    }
                    else
                    {
                        echo "<input type='radio' name='dummy{$daynumber}' disabled='disabled' />";
                    }
                    echo "</td>";

                    // pm
                    echo "<td class='shade1' align='center'>";
                    if ($existing_holiday['length'] == 'pm')
                    {
                        echo $holiday_legend;
                    }
                    elseif ($existing_holiday['length'] != 'day')
                    {
                        if (($type == HOL_SICKNESS && $day < $today) || ($type != HOL_SICKNESS))
                        {
                            echo "<input type='radio' name='length{$daynumber}' value='pm' checked='checked' />";
                            $options++;
                        }
                        else echo "<input type='radio' name='dummy{$daynumber}' disabled='disabled' />";
                    }
                    else
                    {
                        echo "<input type='radio' name='dummy{$daynumber}' disabled='disabled' />";
                    }
                    echo "</td>";
                    echo "</tr>\n";
                }
            }
            else
            {
                $sql = "SELECT * FROM `{$dbHolidays}` WHERE `date` = FROM_UNIXTIME({$day}) AND type='".HOL_PUBLIC."' ";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
                if (mysql_num_rows($result) > 0)
                {
                    echo "<tr><td class='shade1' align='right'>".ldate('l jS M y',$day)."</td><td colspan='4'>{$strPublicHoliday}</td></tr>";
                }
                else
                {
                    echo "<tr><td class='shade2' align='right'>".ldate('l jS M y',$day)." </td>";
                    // Don't allow booking sickness in the future, still not sure
                    // whether we should allow this or not - it could be useful
                    // in the case of long term illness
                    // If overbooking is disabled, prevent booking more holidays
                    // in the current year than you have entitlement for
                    if (($type == HOL_SICKNESS AND $day <= $today) OR ($type != HOL_SICKNESS) AND
                        ($CONFIG['holiday_allow_overbooking'] == TRUE OR
                        ($CONFIG['holiday_allow_overbooking'] == FALSE AND $day < $holiday_resetdate)))
                    {
                        echo "<td class='shade1' align='center'><input type='radio' name='length{$daynumber}' value='none' /></td>";
                        echo "<td class='shade1' align='center'><input type='radio' name='length{$daynumber}' value='day' checked='checked' /></td>";
                        echo "<td class='shade2' align='center'><input type='radio' name='length{$daynumber}' value='am' /></td>";
                        echo "<td class='shade1' align='center'><input type='radio' name='length{$daynumber}' value='pm' /></td>";
                        $options++;
                    }
                    else
                    {
                        echo "<td class='shade1' align='center'>-</td>";
                        echo "<td class='shade2' align='center'>-</td>";
                        echo "<td class='shade1' align='center'>-</td>";
                        echo "<td class='shade2' align='center'>-</td>";
                    }
                    echo "</tr>\n";
                }
            }
            echo "<input type='hidden' name='day{$daynumber}' value='{$day}' />";
            $daynumber++;
        }
    }
    echo "</table>";
    echo "<input type='hidden' name='numberofdays' value='{$daynumber}' />";
    echo "<input type='hidden' name='step' value='3' />";

    if ($options > 0)
    {
        echo "<p align='center'>";
        echo "<input type='submit' value='{$strSelect}' />";
        echo "</p>";
    }
    echo "</form>";


    echo "<br />";

    echo "<p align='center'><a href='book_holidays.php?user={$user}'>{$strReturnWithoutSaving}</a></p>";
    include ('htmlfooter.inc.php');
}
else
{
    $approvaluser = cleanvar($_REQUEST['approvaluser']);
    $memo = cleanvar($_REQUEST['memo']);
    $type = cleanvar($_REQUEST['type']);
    $numberofdays = cleanvar($_REQUEST['numberofdays']);
    for ($h = 1; $h < $numberofdays; $h++)
    {
        $dayfield = "day{$h}";
        $lengthfield = "length{$h}";
        $$dayfield = cleanvar($_REQUEST[$dayfield]);
        $$lengthfield = cleanvar($_REQUEST[$lengthfield]);
    }

    // SAVE REQUEST TO DATABASE
    for ($holiday = 1; $holiday < $numberofdays; $holiday++)
    {
        $len = "length{$holiday}";
        $d = "day{$holiday}";
        if (empty($$len))
        {
            $$len = 'day';
        }

        if ($$len != 'none')
        {
            // check to see if there is other holiday booked on this day
            // and modify that where required.
            $sql = "INSERT INTO `{$dbHolidays}` (userid, type, date, length, approved, approvedby) ";
            $sql .= "VALUES ('{$user}', '$type', FROM_UNIXTIME({$$d}), '{$$len}', '0', '$approvaluser') ";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }
    }
    header("location:holiday_request.php?user={$user}");
}
?>
