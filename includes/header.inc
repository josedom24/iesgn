<?session_start();
require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/funciones.inc");
require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
if(!$_SESSION["usuario"] && $_COOKIE["iesgn"])
{	
print "<meta http-equiv=refresh content=0;URL=".url()."login.php>";
echo "<a href=\"login.php\">Direccionando...</a>";
die("");
}
?>
<html>
<head>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>IES Gonzalo Nazareno</title>
	<script language="JavaScript" src="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/javascript.js"></script>
	<script language="JavaScript" src="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/calendar1.js"></script>
	 <script language="JavaScript" src="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/fsmenu.js"></script>
	<link href="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/style.css" rel="stylesheet" type="text/css" />
 	<link rel="stylesheet" type="text/css" id="listmenu-v"
  href="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/listmenu_v.css" title="Vertical 'Earth'" />
 <link rel="stylesheet" type="text/css" id="listmenu-h"
  href="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/listmenu_h.css" title="Horizontal 'Earth'" />
	
	<link rel="stylesheet" type="text/css" id="fsmenu-fallback" href="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/listmenu_fallback.css" />


 <link rel="stylesheet" type="text/css" href="https://<?echo $_SERVER['SERVER_NAME']?>/iesgn/includes/divmenu.css" />
	

</head>


<body>
<div id="container">
<center><div id="header"><h1>IES Gonzalo Nazareno</h1><br></div></center>
<table border="0" cellspacing="10">
<tr><td valign="top">
<div id="navigation">
<?include("entrada.inc");?>
</div>
<div id="extra">
<?include("menu.inc");?>
</div>
</td>
<td>
<div id="wrapper">
<div id="content">



