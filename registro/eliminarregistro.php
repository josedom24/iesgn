<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
permisos("secretaria","B",1);
if($_GET)
{
		
		$sql="delete from Registro where Id=".$_GET["id"]." and Curso='".$_GET["curso"]."' and Tipo='".$_GET["tipo"]."'";
			mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:registro.php?tipo=".$_GET["tipo"]);
}
?>
