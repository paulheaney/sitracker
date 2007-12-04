<?php
// qbe.php - Very simple query by example
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

$permission=37; // Run Reports

require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

$title = $strQueryByExample;

if (empty($_REQUEST['mode']))
{
    include('htmlheader.inc.php');
    echo "<h2>{$title}</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table align='center'>";
    echo "<tr><th>{$strTable}:</th>";
    echo "<td>";
    $result = mysql_list_tables($CONFIG['db_database']);
    echo "<select name='table1'>";
    while ($row = mysql_fetch_row($result))
    {
        echo "<option value='{$row[0]}'>{$row[0]}</option>\n";
    }
    echo "</select>";
    echo "</td></tr>\n";
    /*
    echo "<tr><td align='right' width='200' class='shade1'><b>Table 2</b>:</td>";
    echo "<td width=400 class='shade2'>";
    $result = mysql_list_tables($db_database);
    echo "<select name='table1'>";
    while ($row = mysql_fetch_row($result))
    {
        echo "<option value='{$row[0]}'>{$row[0]}</option>\n";
    }
    echo "</select>";
    echo "</td></tr>\n";
    */
    echo "</table>";
    echo "<p align='center'>";
    echo "<input type='hidden' name='mode' value='selectfields' />";
    echo "<input type='submit' value='{$strRunReport}' />";
    echo "</p>";
    echo "</form>";
    include('htmlfooter.inc.php');
}
elseif ($_REQUEST['mode']=='selectfields')
{
    $table1 = cleanvar($_REQUEST['table1']);
    include('htmlheader.inc.php');
    echo "<h2>{$title}</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table align='center'>";
    echo "<tr><th>{$strTable}:</th>";
    echo "<td class='shade2'>{$table1}</td></tr>";

    echo "<tr><th valign='top'>{$strFields}:</th>";
    echo "<td width='400' class='shade2'>";
    $result = mysql_list_fields($CONFIG['db_database'],$table1);
    $columns = mysql_num_fields($result);
    echo "<select name='fields[]' multiple='multiple'>";
    for ($i = 0; $i < $columns; $i++)
    {
        $fieldname=mysql_field_name($result, $i);
        echo "<option value='$fieldname'>$fieldname</option>\n";
    }
    echo "</select>";
    echo "</td></tr>\n";
    echo "<tr><th>{$strSort}:</th>";
    echo "<td class='shade2'>";
    echo "<select name='sortby'>";
    for ($i = 0; $i < $columns; $i++)
    {
        $fieldname=mysql_field_name($result, $i);
        echo "<option value='$fieldname'>$fieldname</option>\n";
    }
    echo "</select>";
    echo "<select name='sortorder'>";
    echo "<option value='none' selected='selected'>{$strNone}</option>";
    echo "<option value='ASC'>{$strSortAscending}</option>";
    echo "<option value='DESC'>{$strSortDescending}</option>";
    echo "</select>";
    echo "</td></tr>";

    echo "<tr><th>{$strCriteria}:</th>";
    echo "<td class='shade2'>";
    echo "<select name='criteriafield'>";
    for ($i = 0; $i < $columns; $i++)
    {
        $fieldname=mysql_field_name($result, $i);
        echo "<option value='$fieldname'>$fieldname</option>\n";
    }
    echo "</select>";
    echo "<select name='criteriaop'>";
    echo "<option value='=' selected>=</option>";
    echo "<option value='<'>&lt;</option>";
    echo "<option value='>'>&gt;</option>";
    echo "<option value='LIKE'>LIKE</option>";
    echo "</select>";
    echo "<input type='text' name='criteriaval' />";
    echo "</td></tr>";

    echo "<tr><th>{$strLimitTo}:</th>";
    echo "<td><input type='text' name='limit' value='1000' size='4' /> {$strResults}</td></tr>";

    echo "<tr><th>{$strOutput}:</th>";
    echo "<td>";
    echo "<select name='output'>";
    echo "<option value='screen'>{$strScreen}</option>";
    // echo "<option value='printer'>Printer</option>";
    echo "<option value='csv'>{$strCSVfile}</option>";
    echo "</select>";
    echo "</td></tr>";
    echo "</table>";
    echo "<p align='center'>";
    echo "<input type='hidden' name='table1' value='{$_POST['table1']}' />";
    echo "<input type='hidden' name='mode' value='report' />";
    echo "<input type='submit' value='{$strRunReport}' />";
    echo "</p>";
    echo "</form>";
    include('htmlfooter.inc.php');
}
elseif ($_REQUEST['mode']=='report')
{
    // External variables
    $columns=count($_POST[fields]);
    if ($columns >= 1)
    {
        $htmlfieldheaders="<tr>";
        for ($i = 0; $i < $columns; $i++)
        {
            $fieldname=cleanvar($_POST[fields][$i]);
            $fieldlist.=$fieldname;
            if ($i < ($columns-1)) $fieldlist.=',';
            $htmlfieldheaders.="<th>$fieldname</th>";
            $csvfieldheaders.=$fieldname;
            if ($i < ($columns-1)) $csvfieldheaders .= ",";
        }
        $fieldheaders.="</tr>\n";
        $csvfieldheaders.="\r\n";
    }
    else $fieldlist='*';

    $sql = "SELECT $fieldlist FROM {$_POST['table1']} ";
    if (!empty($_POST['criteriaval'])) $sql .= "WHERE {$_POST['criteriafield']} {$_POST['criteriaop']} '{$_POST['criteriaval']}' ";
    if ($_POST['sortorder']!='none') $sql .= "ORDER BY {$_POST['sortby']} {$_POST['sortorder']} ";
    if ($_POST['limit']>=1) $sql .= "LIMIT {$_POST['limit']} ";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $html .= "<p align='center'><code>$sql</code></p>\n";
    $html .= "<table width='100%'>";
    $html .= $htmlfieldheaders;
    $shade='shade1';
    while ($row = mysql_fetch_row($result))
    {
        $columns = count($row);
        $html .= "<tr class='$shade'>";
        for ($i = 0; $i < $columns; $i++)
        {
            $html .= "<td>{$row[$i]</td>";
            $csv .= strip_comma($row[$i]);
            if ($i < ($columns-1)) $csv .= ",";
        }
        $html .= "</tr>\n";
        if ($shade=='shade1') $shade='shade2';
        else $shade='shade1';
        $csv.="\r\n";
    }
    $html .= "</table>";
    if ($_POST['output']=='screen')
    {
        include('htmlheader.inc.php');
        echo $html;
        include('htmlfooter.inc.php');
    }
    elseif ($_POST['output']=='csv')
    {
        // --- CSV File HTTP Header
        header("Content-type: text/csv\r\n");
        header("Content-disposition-type: attachment\r\n");
        header("Content-disposition: filename=qbe_report.csv");
        echo $csvfieldheaders;
        echo $csv;
    }
}
?>