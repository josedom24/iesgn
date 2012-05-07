<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
permisos("profesores","B",1);
if($_GET)
{
		
		$sql="delete from Profesores where Id=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:profesores.php?uni=".$_GET["uni"]);
}
?>
