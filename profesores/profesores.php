<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("profesores");

$menu="profesores";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");


echo "<center><h2>Gestión de profesores</h2></center>";


echo "<center><form action=\"profesores.php\" method=\"post\">";
Combo("Departamentos","dep",0,"Departamento");
if(!$_POST["dep"] || $_POST["dep"]==-1) $cond="";
else $cond="Profesores.Departamento=".$_POST["dep"]." and ";
echo "<input type=\"submit\" value=\"Seleccionar\">";
echo "</form></center>";


echo "<center><table id=\"datos\" border=\"1\">";
echo "<tr>";
	echo "<td>N</td>";
	echo "<td>Nombre</td>";
	echo "<td>Telefono</td>";
	echo "<td>Movil</td>";
	echo "<td>Tutor</td>";
	echo "<td>Dep.</td>";
	echo "<td>Baja</td>";
	echo "<td>CE</td>";
	echo "<td>ETCP</td>";
	echo "<td>TIC</td>";
	echo "<td>BIL</td>";
	if(permisos("profesores","E"))	echo "<td>E</td>";
	if(permisos("profesores","B"))	echo "<td>B</td>";
			
	
	echo "</tr>";
$sql="select * from Departamentos,Profesores where ".$cond." Profesores.Departamento=Departamentos.Id order by ".ordenar_bien("Apellidos");

$result=mysql_query($sql);
$cont=1;
while($row=mysql_fetch_array($result))
{
	
	echo "<tr>";
	echo "<td>".$cont."</td>";
	echo "<td>".$row["Apellidos"]." ".$row["Nombre"]."</td>";
	echo "<td>".$row["Telefono"]."</td>";
	echo "<td>".$row["Movil"]."</td>";
	echo "<td>".$row["Tutor"]."</td>";
	echo "<td>".$row[1]."</td>";
	
	if(permisos("profesores","E"))
	{
		
		if($row["Baja"]==0)	echo "<td align=\"center\"<a href=\"darbaja.php?c=Baja&op=1&id=".$row["Id"]."\">".imagen("hide.gif")."</a></td>";
		else	echo "<td align=\"center\"><a href=\"darbaja.php?c=Baja&op=0&id=".$row["Id"]."\">".imagen("show.gif")."</a></td>";
		if($row["Ce"]==0)	echo "<td align=\"center\"><a href=\"darbaja.php?c=Ce&op=1&id=".$row["Id"]."\">".imagen("stop.gif")."</a></td>";
		else	echo "<td align=\"center\"><a href=\"darbaja.php?c=Ce&op=0&id=".$row["Id"]."\">".imagen("go.gif")."</a></td>";
		if($row["Etcp"]==0)	echo "<td align=\"center\"><a href=\"darbaja.php?c=Etcp&op=1&id=".$row["Id"]."\">".imagen("stop.gif")."</a></td>";
		else	echo "<td align=\"center\"><a href=\"darbaja.php?c=Etcp&op=0&id=".$row["Id"]."\">".imagen("go.gif")."</a></td>";
		if($row["Tic"]==0)	echo "<td align=\"center\"><a href=\"darbaja.php?c=Tic&op=1&id=".$row["Id"]."\">".imagen("stop.gif")."</a></td>";
		else	echo "<td align=\"center\"><a href=\"darbaja.php?c=Tic&op=0&id=".$row["Id"]."\">".imagen("go.gif")."</a></td>";
		if($row["Bil"]==0)	echo "<td align=\"center\"><a href=\"darbaja.php?c=Bil&op=1&id=".$row["Id"]."\">".imagen("stop.gif")."</a></td>";
		else	echo "<td align=\"center\"><a href=\"darbaja.php?c=Bil&op=0&id=".$row["Id"]."\">".imagen("go.gif")."</a></td>";
		echo "<td><a href=\"modificarprofesor.php?id=".$row["Id"]."\">".imagen("mod.gif")."</a></td>";
	}
	if(permisos("profesores","B")){
	echo "<td><a href=\"eliminarprofesor.php?id=".$row["Id"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar a ".$row["Apellidos"]." ".$row["Nombre"]."?')\">".imagen("del.gif")."</ajosedom></td>";
	}
	
	if(!permisos("profesores","E"))
	{
		if($row["Baja"]==0)	echo "<td align=\"center\"></td>";
		else	echo "<td align=\"center\">".imagen("show.gif")."</td>";
		if($row["Ce"]==0)	echo "<td align=\"center\"></td>";
		else	echo "<td align=\"center\">".imagen("go.gif")."</td>";
		if($row["Etcp"]==0)echo "<td align=\"center\"></td>";
		else	echo "<td align=\"center\">".imagen("go.gif")."</td>";
		if($row["Tic"]==0)echo "<td align=\"center\"></td>";
		else	echo "<td align=\"center\">".imagen("go.gif")."</td>";
		if($row["Bil"]==0)echo "<td align=\"center\"></td>";
		else	echo "<td align=\"center\">".imagen("go.gif")."</td>";
		}
	
	echo "</tr>";
	$cont++;
}
echo "</table></center>";
echo "<br><br>";
if(permisos("profesores","A"))
	{
		echo imagen("add_user.png")."<a href=\"registro.php\">Insertar nuevo</a>";
	}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

