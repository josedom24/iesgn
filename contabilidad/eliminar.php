<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
permisos("contabilidad","B",1);
if($_GET)
{
		
		$sql="delete from Contabilidad where Id=".$_GET["id"];
			mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:contabilidad.php?cg=".$_GET["cg"]);
}
?>
