<?php
// kb_view_article.php - Display a single knowledge base article
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2006 Salford Software Ltd.
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Authors: Ivan Lucas, Tom Gerrard

$permission=54; // View KB

require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

// Valid user, check permissions
if (user_permission($sit[2],$permission))
{
    if (!empty($_REQUEST['id'])) $id = cleanvar($_REQUEST['id']);
    if (!empty($_REQUEST['kbid'])) $id = cleanvar($_REQUEST['kbid']);
    if (empty($id))
    {
        header("Location: browse_kb.php");
        exit;
    }

    include('htmlheader.inc.php');

    echo "<div id='kbarticle'>";
    echo "<table summary='Knowledge Base Article'><tr><td>";

    $sql = "SELECT * FROM kbarticles WHERE docid='{$id}' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $kbarticle = mysql_fetch_object($result);
    if (empty($kbarticle->title)) $kbarticle->title='Untitled';

    echo "<h2>".remove_slashes($kbarticle->title)."</h2>";

    // Lookup what software this applies to
    $ssql = "SELECT * FROM kbsoftware, software WHERE kbsoftware.softwareid=software.id AND kbsoftware.docid='{$id}' ";
    $ssql .= "ORDER BY software.name";
    $sresult = mysql_query($ssql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    if (mysql_num_rows($sresult) >= 1)
    {
        echo "<p>The information in this article applies to:</p>\n";
        echo "<ul>\n";
        while ($kbsoftware = mysql_fetch_object($sresult))
        {
            echo "<li>{$kbsoftware->name}</li>\n";
        }
        echo "</ul>\n";
    }

    $csql = "SELECT * FROM kbcontent WHERE docid='{$id}' ";
    $cresult = mysql_query($csql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    while ($kbcontent = mysql_fetch_object($cresult))
    {
        switch ($kbcontent->distribution)
        {
            case 'private': echo "<div style='color: blue; background: #FFD8DE;' title='This paragraph is marked PRIVATE'>"; break;
            case 'restricted': echo "<div style='color: red; background: #FFD8DE' title='This paragraph is marked RESTRICTED'>";  break;
            default: echo "<div>";
        }
        echo "<{$kbcontent->headerstyle}>{$kbcontent->header}</{$kbcontent->headerstyle}>\n";
        /*
        switch ($kbcontent->distribution)
        {
            //case 'private': echo " style='color: blue;' title='This paragraph is marked PRIVATE'"; break;
            //case 'restricted': echo " style='color: red;' title='This paragraph is marked RESTRICTED'"; break;
            //case '': echo " style='color: blue;'"; break;
            //default: echo ""; break;
        }
        echo ">{$kbcontent->header}</{$kbcontent->headerstyle}>\n";
        echo "<p ";
        switch ($kbcontent->distribution)
        {
            //case 'private': echo "style='color: blue;' title='This paragraph is marked PRIVATE'"; break;
            //case 'restricted': echo "style='color: red;' title='This paragraph is marked RESTRICTED'"; break;
            //case '': echo " style='color: blue;'"; break;
            //default: echo "<p"; break;
        }
        */
        // $kbcontent->content=nl2br(stripslashes($kbcontent->content));
        $kbcontent->content=nl2br(remove_slashes($kbcontent->content));
        $search = array("/(?<!quot;|[=\"]|:\/{2})\b((\w+:\/{2}|www\.).+?)"."(?=\W*([<>\s]|$))/i", "/(([\w\.]+))(@)([\w\.]+)\b/i");
        $replace = array("<a href=\"$1\">$1</a>", "<a href=\"mailto:$0\">$0</a>");
        $kbcontent->content = preg_replace("/href=\"www/i", "href=\"http://www", preg_replace ($search, $replace, $kbcontent->content));
        // $kbcontent->content = preg_replace("/(([\w\.]+))(@)([\w\.]+)\b/i", "<a href=\"mailto:$0\">$0</a>", $kbcontent->content);
        echo $kbcontent->content;
        $author[]=$kbcontent->ownerid;
        echo "</div>";

    }

    echo "<hr />";
    echo $CONFIG['kb_disclaimer_html'];
    echo "<dl><dd>";
    echo "Document ID: {$CONFIG['kb_id_prefix']}".leading_zero(4,$kbarticle->docid)."<br />";
    $pubdate=mysql2date($kbarticle->published);
    if ($pubdate > 0) echo "Published on: ".date('d M Y',$pubdate) ."<br />";

    if (is_array($author))
    {
        $author=array_unique($author);
        $countauthors=count($author);
        $count=1;
        if ($countauthors > 1) echo "Authors: ";
        else echo "Author: ";
        foreach ($author AS $authorid)
        {
            echo user_realname($authorid);
            if ($count < $countauthors) echo ", " ;
            $count++;
        }
    }
    else echo "Author: {$author}";

    echo "<br />";
    if (!empty($kbarticle->keywords)) echo "Keywords: ".preg_replace("/\[([0-9]+)\]/", "<a href=\"incident_details.php?id=$1\" target=\"_blank\">$0</a>", $kbarticle->keywords)."<br />";
    //      if (!empty($kbarticle->keywords)) echo "Keywords: ".preg_replace("/\s\[(\d(1,5))\]/", "<a href=\"#\$0\">$0</a>$0</a>", $kbarticle->keywords)."<br />";
    echo "</dd></dl>";

    echo "</td></tr></table>";

    echo "</div>";

    echo "<p align='center'><a href='kb_edit_article.php?id={$kbarticle->docid}'>Edit this article</a></p>";

    include('htmlfooter.inc.php');
}
?>