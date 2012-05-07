<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
permisos("admin");

if($_GET)
{
	

		$sql="delete from usuarios where usuario='".$_GET["id"]."'";
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		mysql_close();
		Header("Location:usuarios.php");
	}
}
?>