<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("partes");
//Recibe la fecha de resumen de amonestaciones
echo "<h1>Resumen por profesores</h1>";
$v=array("a"=>"Amonestaciones","s"=>"Sanciones","c"=>"Citaciones");

foreach($v as $k=>$v)
{
$sql="SELECT Profesores.Id, Profesores.Nombre,Profesores.Apellidos,count(Partes.Id) FROM Partes,Profesores where Id_prof=Profesores.Id and Partes.Tipo='".$k."' group by  Profesores.Id order by Profesores.Apellidos";

$result=mysql_query($sql) or die("error:".$sql);
echo "<h2>".$v."</h2>";

	echo "<table>";
        while($row=mysql_fetch_array($result))
        {
                echo "<tr>";
                echo "<td>".$row[1]." ".$row[2]."</td>";
                echo "<td>-> ".$row[3]."</td>";
                echo "</tr>";

        }
	echo "</table>";

}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>

