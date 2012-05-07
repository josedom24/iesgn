<?
function Combo($tabla,$c,$vacio=0)
{
$sql="select * from ".$tabla;
$result=mysql_query($sql);


echo "<select name=\"".$c."\" size=\"0\">";
//if ($_GET) $uni=$_GET["uni"];
if ($_POST) $v=$_POST[$c];
if($_GET) $v=$_GET[$c];
if($vacio==0) 
{
	if ($v=="") echo "<option selected value=\"-1\"></option>";
	else  echo "<option value=\"-1\"></option>";
}

while($row=mysql_fetch_array($result))
{
	
	
	if($v==$row[0])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row[0]."\">".$row[1]."</option>\n";
}
echo "</select>\n";
}

function ComboCurso($c)
{
$sql="select Curso from Registro group by Curso";
$result=mysql_query($sql);
echo "<select name=\"curso\" size=\"0\">";
//if ($_GET) $uni=$_GET["uni"];
if($c=="") $c=curso();
while($row=mysql_fetch_array($result))
{
	
	
	if($c==$row[0])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row[0]."\">".$row[0]."</option>\n";
}
echo "</select>\n";
}
function curso($f=0)
{
	if($f==0)
	{
	if(date("m")<9) $curso=(date("Y")-1)."-".date("Y");
	else $curso=date("Y")."-".(date("Y")+1);
	}
	else
	{
	$datos=explode("/",$f);	
	if($datos[1]<9) $curso=($datos[2]-1)."-".$datos[2];
	else $curso=$datos[2]."-".($datos[2]+1);
	}
  return $curso;
}  

function criterio($tipo,$curso)
{
		$ctipo="Tipo='".$tipo."'";
		if($_POST["curso"]) $ccurso="Curso='".$_POST["curso"]."'"; else $ccurso="Curso='".$curso."'";
		if($_POST["proc"]>-1) $cproc="Registro.Idp=".$_POST["proc"];
		if($_POST["remi"]>-1) $cremi="Registro.Idr=".$_POST["remi"];
		if($_POST["clas"]>-1) $cclas="Registro.Idc=".$_POST["clas"];
		if($_POST["cont"]) $cclas="Contenido like '%".$_POST["cont"]."%'";
		$res=" ".$ctipo;
		if($ccurso!="") $res=$res." and ".$ccurso;
		if($cproc!="") $res=$res." and ".$cproc;
		if($cremi!="") $res=$res." and ".$cremi;
		if($cclas!="") $res=$res." and ".$cclas;
		if($ccont!="") $res=$res." and ".$ccont;
		$res=$res." order by Id desc";
		
		return $res;
		
}
function calcula_id($t,$c)
{
	$sql="select * from Registro where Tipo='".$t."' and Curso='".$c."' order by Id desc";
	
$result=mysql_query($sql);
if($row=mysql_fetch_array($result)) return ($row["Id"]+1); else return (1);
}	
?>	