<?session_start();
	
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
	permisos("libros","A",1);
	
	$sql="select * from Alumnos where Unidad='".$_GET["uni"]."'";
	$result=mysql_query($sql);
	while($row=mysql_fetch_array($result))
	{
		$sql="delete from LibrosAlumnos where Id=".$row[0];
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
	}

if($_POST)
{
	
	
	foreach($_POST as $ind => $v)
	{

		$d=explode("-",$v);
		
		$sql="insert into LibrosAlumnos values (".$d[0].",".$d[1].")";
		mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
		
	}
	
}
Header("Location:libros.php?uni=".$_GET["uni"]."&e=1");