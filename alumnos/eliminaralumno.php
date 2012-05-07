<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
permisos("alumnos","B",1);
if($_GET)
{
		
		$sql="delete from Alumnos where Id=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		$sql="delete from Partes where Ida=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:alumnos.php?uni=".$_GET["uni"]);
		$sql="delete from LibrosAlumno where Id=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
}
?>
