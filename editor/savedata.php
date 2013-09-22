<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
permisos("General");

$sValue = stripslashes( $_POST['FCKeditor1'] ) ;
$sql="UPDATE Cartas SET Contenido='".$sValue."' where Id=".$_POST["id"];
mysql_query($sql);
//echo $sValue;
mysql_close();
Header("Location:".$_GET["ir"]);
?>
