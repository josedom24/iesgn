<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
permisos("partes","B",1);
if($_GET)
{
		
		$sql="delete from Partes where Id=".$_GET["id"];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		Header("Location:historial.php?id=".$_GET["ida"]);
}
?>

