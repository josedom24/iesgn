<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
permisos("profesores","E",1);
if($_GET)
{
		
		$sql="UPDATE Profesores SET ".$_GET["c"]."=".$_GET['op']." where Id=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:profesores.php");
}
?>
