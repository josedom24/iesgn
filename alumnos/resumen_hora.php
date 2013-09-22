<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("partes");
//Recibe la fecha de resumen de amonestaciones
echo "<h1>Resumen por horas</h1>";

$sql="SELECT Hora,count(*) FROM Partes group by hora";

$result=mysql_query($sql) or die("error:".$sql);
echo "<table>";

        while($row=mysql_fetch_array($result))
        {
                echo "<tr>";
                echo "<td>".$row[0]."-> ".$row[1]."</td>";
                echo "</tr>";

        }
	echo "</table>";


include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>

