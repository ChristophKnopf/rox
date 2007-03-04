<?php

require_once("layouttools.php");

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
global $_SYSHCVOL ;
echo "<head>\n";
echo "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
if (isset ($title)) {
	echo "  <title>", $title, "</title>\n";
} else {
	echo "\n<title>", $_SYSHCVOL['SiteName'], "</title>\n";
}
echo "<LINK REL=\"SHORTCUT ICON\" HREF=\"".bwlink("favicon.ico")."\">\n";

$stylesheet = "stylesheet1";

// If is logged try to load appropriated style sheet
if (IsLoggedIn()) {
	// todo set a cache for this
	$rrstylesheet = LoadRow("select Value from memberspreferences where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=6");
	if (isset ($rrstylesheet->Value)) {
		$stylesheet = $rrstylesheet->Value;
	}
}
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/undohtml.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/screen_micha.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
//echo "  <link href=\"".bwlink("styles/". $stylesheet. "/screen_micha_exp.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/fake51.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "<!--[if lte IE 7]>";
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/iehacks.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "<![endif]-->";

echo "</head>\n";
echo "<html>\n";
echo "<body>\n";

if ($_SYSHCVOL['SiteStatus'] == 'Closed') {
	echo "<br><br>", $_SYSHCVOL['SiteCloseMessage'], "<br>\n";
	echo "</body>\n</html>\n";
	exit (0);
}
?>
