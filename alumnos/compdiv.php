<?
if($_POST["alumnoselect"])
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	$sql="delete from CompDiv where Idc=".$_POST["curso"];
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
	$vector=explode(",",$_POST["alumnoselect"]);
	foreach($vector as $alumno)
	{
		$sql="insert into CompDiv values (".$_POST["curso"].",".$alumno.")";
		if($alumno>0) mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
		
	}
	
}


include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("alumnos");

if ($_GET) $uni=$_GET["uni"];
if ($_POST) $uni=$_POST["uni"];
;
if ($_POST) $curso=$_POST["curso"];
if(!$curso) $curso=1;
$sql2="select Unidad from Alumnos group by Unidad";
$result2=mysql_query($sql2);
while($row=mysql_fetch_array($result2))
{
	if($uni=="") { $uni=$row["Unidad"];}
}
$cond=" where Unidad='".$uni."'";
$sql="select * from Alumnos".$cond." order by ".ordenar_bien("Nombre");


echo "<center><h2>Gestión de Compensatoria/Diversificación</h2></center>";
echo "<form name=\"f1\" action=\"compdiv.php\" method=\"post\">\n";


echo "<center>";
echo "<table border=\"0\">";
echo "<tr><td>Unidad:<br/>";
echo "<select onchange=\"activar();document.f1.submit();\" name=\"uni\" size=\"0\">";
$sql2="select Unidad from Alumnos group by Unidad";
$result2=mysql_query($sql2);
while($row=mysql_fetch_array($result2))
{

	
	if($uni==$row["Unidad"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select>\n";
echo "</td><td></td><td>Grupo:<br>";
Combo("Cursos","curso",1,2,$_POST["curso"],0,"onchange=\"document.f1.submit();\"");
echo "</td>";
//echo "<input type=\"submit\" value=\"Aceptar\">\n";

echo "</center>";



echo "<br><center>";
echo "<tr><td>";
echo "<select   name=\"alu\" id=\"alu\" size=\"10\">";
$result=mysql_query($sql);

while($row=mysql_fetch_array($result))
 {
	echo "<option value=\"".$row["Id"]."\">".$row["Nombre"] ."</option>\n";
	
}
echo "</select>\n";
echo "</td>";
echo "<td>";
if(permisos("alumnos","E")) echo "<input type=\"button\" onclick=pasar(); value=\">>\">\n";
if(permisos("alumnos","E")) echo "<br/><br/><br/><input type=\"button\" onclick=nopasar(); value=\"<<\">\n";
echo "</td>";
echo "<td>";
echo "<select  name=\"grup\" id=\"grupo\" size=\"10\">";

$sql="select * from CompDiv where Idc=".$curso;
$result=mysql_query($sql);

while($row=mysql_fetch_array($result))
 {
	echo "<option value=\"".$row["Ida"]."\">".NombreDe($row["Ida"]) ."</option>\n";
	
}

echo "</select>";
echo "<input type=\"hidden\" name=\"alumnoselect\" id=\"alumnoselect\">";
echo "</td>";
echo"</tr>";
echo "</table>";
if(permisos("alumnos","E")) echo "<input type=\"submit\"  value=\"Guardar\" onclick=activar();>\n";
echo "</form>";
echo "</center>";


include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>
<script>
function pasar() {
	
	obj=document.getElementById('alu');
	
	if (obj.selectedIndex==-1) return;
	valor=obj.options[obj.selectedIndex].value;
	txt=obj.options[obj.selectedIndex].text;
	obj.options[obj.selectedIndex]=null;
	obj2=document.getElementById('grupo');
	opc = new Option(txt,valor);
	eval(obj2.options[obj2.options.length]=opc);	
}
function nopasar() {
	obj=document.getElementById('grupo');
	if (obj.selectedIndex==-1) return;
	valor=obj.options[obj.selectedIndex].value;
	txt=obj.options[obj.selectedIndex].text;
	obj.options[obj.selectedIndex]=null;
	obj2=document.getElementById('alu');
	opc = new Option(txt,valor);
	eval(obj2.options[obj2.options.length]=opc);	
}
function activar() {
	var cad="";
	obj=document.getElementById('grupo');
	for(i=0;i<obj.options.length;i++)
	{
		cad=cad+obj.options[i].value;
		if(i<obj.options.length-1) cad=cad+",";
	}
	obj2=document.getElementById('alumnoselect');
	if(cad=="") cad=-1;
	obj2.value=cad;
	
}
</script>