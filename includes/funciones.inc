<?
////////////////////////////////////////////////////
//Convierte fecha de mysql a normal
////////////////////////////////////////////////////
function cambiaf_a_normal($fecha){
    ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    $lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];
    return $lafecha;
}
////////////////////////////////////////////////////
//Convierte fecha de normal a mysql
////////////////////////////////////////////////////

function cambiaf_a_mysql($fecha){
    ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
    $lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1];
    return $lafecha;
} 

function calcular_num_amonestaciones($id)
{
	$sql="select * from Partes where Ida=".$id." and Tipo='a'";
	$result=mysql_query($sql);
	return mysql_num_rows($result);

}
function calcular_num_sanciones($id)
{
	$sql="select * from Partes where Ida=".$id." and Tipo='s'";
	$result=mysql_query($sql);
	return mysql_num_rows($result);
	


}
function calcular_num_citaciones($id)
{
	$sql="select * from Partes where Ida=".$id." and Tipo='c'";
	$result=mysql_query($sql);
	return mysql_num_rows($result);
	

}
function valida_fecha($f)
{
	$datos=explode("/",$f);
	return checkdate((int)$datos[1],(int)$datos[0],(int)$datos[2]);
}
function ponlibros($id)
{
	$sql="select * from LibrosAlumnos where Id=".$id;
	$result=mysql_query($sql);
	$res="<table border=\"1\">";
	$res=$res. "<strong><tr>";
	$res=$res."<td >Libro</td>";
	$res=$res."<td>1ºEv.</td>";
	$res=$res."<td>2ºEv.</td>";
	$res=$res."<td>Final</td>";
	$res=$res."<td>Sept.<br>(S/N)</td>";
	$res=$res."<td>Entr.<br>(S/N)</td>";
	$res=$res."<td width=\"40%\">Observaciones</td></strong>";
	$res=$res. "</tr>";
	
	while($row=mysql_fetch_array($result))
	{
		$res=$res."<tr>\n";
		$sql="select * from Libros where Id=".$row["Idl"];
		$result2=mysql_query($sql);
		if($row2=mysql_fetch_array($result2)) $res=$res."<td>".$row2["Nombre"]."<br><br><br></td>\n";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."</tr>\n";
	}
	$res=$res."</table>\n";
	return $res;
}
function ponalumnos($uni)
{
	
	if(strlen($uni)==1)
			$sql="select * from Alumnos,CompDiv where Id=Ida and Idc=".$uni." order by ".ordenar_bien("Nombre");
	else
			$sql="select * from Alumnos where Unidad='".$uni."' order by ".ordenar_bien("Nombre");
	
	$dias=array("Lunes","Martes","Miercoles","Jueves","Viernes");
	
	$res="<table border=\"1\" width=\"100%\">";
	$res=$res. "<tr>";
	$res=$res."<td width=\"192px\">Alumno</td>";
	foreach($dias as $v)
		$res=$res."<td width=\"105px\">".$v."</td>";
		$res=$res. "</tr>";
		$res=$res."</table>";
		$res=$res."<table border=\"1\">";
		$res=$res. "<tr><td width=\"220px\"></td>";
	for($i=0;$i<5;$i++)
	{
		for($j=1;$j<=6;$j++)
		{
			$res=$res."<td WIDTH=\"12px\">".$j."</td>";
		}
	}
	$res=$res."</tr>";
	//echo $sql;
	$result=mysql_query($sql);

	while($row=mysql_fetch_array($result))
	{
		$res=$res."<tr>\n";
		$res=$res."<td width=\"220px\">".$row["Nombre"]."</td>";
		for($i=0;$i<5;$i++)
		{
			for($j=1;$j<=6;$j++)
			{
				if($j%2==0) $res=$res."<td width=\"12px\" bgcolor=\"#D8D8D8\"></td>";
				else $res=$res."<td width=\"12px\"></td>";
			}
		}
		$res=$res."</tr>\n";
	}
	$res=$res."</table>\n";
	return $res;
}

function ponrevalumnos($unidad,$libro)
{
	$sql="select * from Alumnos,LibrosAlumnos where Alumnos.Id=LibrosAlumnos.Id and Idl=".$libro." and Unidad='".$unidad."' order by ".ordenar_bien("Nombre");
	$result=mysql_query($sql);
	$res="<table border=\"1\">";
	$res=$res. "<strong><tr>";
	$res=$res."<td >Alumno</td>";
	$res=$res."<td >Revisión</td>";
	//$res=$res."<td>Sept.<br>(S/N)</td>";
	$res=$res."<td>Entr.<br>(S/N)</td>";
	$res=$res."<td width=\"45%\">Observaciones</td></strong>";
	$res=$res. "</tr>";	
	while($row=mysql_fetch_array($result))
	{
		$res=$res."<tr>\n";
		$res=$res."<td>".$row["Nombre"];
		$res=$res."<td></td>";
		//$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."<td></td>";
		$res=$res."</tr>\n";
	
		
		
	}
	$res=$res."</table>";
return $res;
}


function ponamonestaciones($id,$tipo)
{
	$sql="select * from Partes where Ida=".$id." and Tipo='".$tipo."' order by Fecha desc limit 0,2";
	
	$result=mysql_query($sql);
	if(mysql_num_rows($result)==1)
	{
		$row=mysql_fetch_array($result);
		$fecha1="1/1/2000";
		$fecha2=cambiaf_a_normal($row["Fecha"]);
	}
	else
	{
		$row=mysql_fetch_array($result);
		$fecha2=cambiaf_a_normal($row["Fecha"]);
		$row=mysql_fetch_array($result);
		$fecha1=cambiaf_a_normal($row["Fecha"]);
		
	}
		
	
	$sql="select * from Partes where Ida=".$id." and Tipo='a' and Fecha<='".cambiaf_a_mysql($fecha2)."'  and Fecha>='".cambiaf_a_mysql($fecha1)."'";
	
	$result=mysql_query($sql);
	
	while($row=mysql_fetch_array($result))
	{
		$res=$res.cambiaf_a_normal($row["Fecha"])." ";
	}
	return($res);
	
}


function cambia($id,$html,$tabla="",$ob="",$ssql="")
{
	
	$sql="select * from ".$tabla." where Id=".$id;
	if($ssql!="") $sql=$ssql;
	if($ob!="") $sql=$sql." order by ".ordenar_bien($ob);
	
	$result=mysql_query($sql);
	$chtml="";
	if($row=mysql_fetch_array($result))
	{
		
		$pos=strpos($html,"##");
		while($pos>-1)
		{
		//echo "posición ".$pos."<br>";
			$chtml=$chtml.substr($html,0,$pos);
		//echo "Va quedando: ".$chtml."<br> ";
			$html=substr($html,$pos+2);
		//echo "Html ". $html."<br> ";
			$pos=strpos($html,"##");
		//echo $pos."<br>";
			$campo=substr($html,0,$pos);
			$html=substr($html,$pos+2);
			//echo "Html ". $html."<br> ";
			//echo "xxx".$row["Unidad"];
			switch($campo)
			{
				case "T":$chtml=$chtml.calcular_num_amonestaciones($id);
						break;
				case "Tutor":if($tabla!="CompDiv") 
						if($row["Unidad"]!="") 
							$chtml=$chtml.tutor($row["Unidad"]);
						else
							$chtml=$chtml."##Tutor##";
						break;
				case "Unidad":if($tabla=="CompDiv") 
							$chtml=$chtml.CursoCompDiv($row["Idc"]); 
					      else 
							if($row["Unidad"]!="")
								
								$chtml=$chtml.$row["Unidad"];
								
							else
								$chtml=$chtml."##Unidad##";
						break;
				case "Dia":$chtml=$chtml.date("d");
						break;
				case "Mes":$chtml=$chtml.mes();
						break;
				case "Ano":$chtml=$chtml.date("Y");
						break;
				case "Hora":$chtml=$chtml.date("G").":00";
						break;
				case "Libros":$chtml=$chtml.ponlibros($row["Id"]);
				break;
				case "RevAlumnos":if($row["Unidad"]!="")
							$chtml=$chtml.ponrevalumnos($row["Unidad"],$id);
						  else
							$chtml=$chtml."##RevAlumnos##";
						  break;
				
				case "Alumnos":if($tabla=="CompDiv") {$chtml=$chtml.ponalumnos($row["Idc"]);} else {$chtml=$chtml.ponalumnos($row["Unidad"]);}
				break;
				case "Amonestaciones":$chtml=$chtml.ponamonestaciones($row["Id"],"s");
				case "Citaciones":$chtml=$chtml.ponamonestaciones($row["Id"],"c");
				break;
					  default:if(comprobar_campo($campo,$result))
					{
						if(substr($campo,0,5)=="Fecha")
						{
							$chtml=$chtml.cambiaf_a_normal($row[$campo]);
						}
						else
							$chtml=$chtml.$row[$campo];
					}
					else
						$chtml=$chtml."##".$campo."##";
					
			}
			
			//$chtml=$chtml.$html;
		//echo "Va quedando: ".$chtml."<br>";
		//echo "----------------------<br><br>";
		
			$pos=strpos($html,"##");
			
			
		}
	}
	$chtml=$chtml.$html;
	return ($chtml);
	}	
	
	function tutor($uni)
	{
		$sql="select * from Profesores where Tutor='".$uni."'";
		$result=mysql_query($sql);
		if($row=mysql_fetch_array($result)) return $row["Nombre"]." ".$row["Apellidos"];
	}
	function mes()
	{
		$m= array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		return $m[date("n")-1];
	}
	
	function comprobar_campo($c,$qry)
	{
		$campos = mysql_num_fields($qry);
		$i=0;
		while($i<$campos){
		if($c==mysql_field_name ($qry, $i)) return true;
		$i++;
		}
		return false;
		
	}
function edad($nacimiento){

//restamos los años (año actual - año cumpleaños)
$f=explode("/",$nacimiento);
$edad = date("Y") - $f[2];
$actual=calcular_dias($f[0],$f[1],$f[2]);
$ncto=calcular_dias(date("d"),date("m"),date("Y"));
//echo $actual."<br>".$ncto;
if($actual>$ncto) $edad--;
if(valida_fecha($nacimiento)) return $edad; else return 0;
}
function calcular_dias($d,$m,$a)
{
$dias=array(31,28,31,30,31,30,31,31,30,31,30,31);
//if(($a%4==0) and ($a%100!=0) or ($a%400==0)) $dias[1]=$dias[1]+1;
$di=0;
for($i=0;$i<$m;$i++) $di=$di+$dias[$i];
return $di+$d;
}
function path()
{
		return($_SERVER['DOCUMENT_ROOT']."/iesgn/");
}
function url()
{
		return("https://".$_SERVER['SERVER_NAME']."/iesgn/");
}
function imagen($img)
{
	return "<img src=\"".url()."/img/".$img."\" border=0>";
}

function fichrand()
 {
	$nueva_pass="";
	for($i=0;$i<6;$i++)
	{
		$aleatorio=rand(97,122);
		$nueva_pass=$nueva_pass.chr($aleatorio);
	}
	return $nueva_pass;
}
function borrar ($fi)
{
   global $sql;
   foreach (glob($fi) as $filename) {$result=mysql_query($sql);
   //echo "$filename size " . filesize($filename) . "\n";
   unlink($filename);
}
}
function escribir_fich($f,$sql)
{
	$result=mysql_query($sql) or die("error:".$sql);
	borrar(path()."tmp/".$_SESSION["usuario"]."*.".$f);
	borrar(path()."tmp/".$_SESSION["usuario"]."*.".$f."head");
	$fich=$_SESSION["usuario"].fichrand().".".$f;
	$fichhead=$fich."head";
	$fic = fopen(path()."tmp/".$fich,"w"); 
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/impresora/escribir".$f.".inc");
	fclose($fic); 
	$fic = fopen(path()."tmp/".$fichhead,"w"); 
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/impresora/escribir".$f."head.inc");
	fclose($fic); 
	return $fich;
}	

function Combo($tabla,$c,$vacio=0,$campo=1,$valor_a_mostrar="",$campo_indice=0,$opcion="",$ordr="")
{
if($ordr=="") $sql="select * from ".$tabla;
if($ordr!="") $sql="select * from ".$tabla." order by ".$ordr;

$result=mysql_query($sql);


echo "<select name=\"".$c."\" size=\"0\" ".$opcion.">";
//if ($_GET) $uni=$_GET["uni"];
if ($_POST) $v=$_POST[$c];
if($_GET) $v=$_GET[$c];
if($valor_a_mostrar!="") $v=$valor_a_mostrar;
if($vacio==0) 
{
	if ($v=="") echo "<option selected value=\"-1\"></option>";
	else  echo "<option value=\"-1\"></option>";
}

while($row=mysql_fetch_array($result))
{
	
	
	if($v==$row[$campo_indice])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row[$campo_indice]."\">".$row[$campo]."</option>\n";
}
echo "</select>\n";
}


function Combo2($tabla,$c,$vacio=0,$campo=1,$valor_a_mostrar="",$campo_indice=0,$opcion="")
{
$sql="select * from ".$tabla;
$result=mysql_query($sql);


echo "<select name=\"".$c."\" size=\"0\" ".$opcion.">";
//if ($_GET) $uni=$_GET["uni"];
if ($_POST) $v=$_POST[$c];
if($_GET) $v=$_GET[$c];
if($valor_a_mostrar!="") $v=$valor_a_mostrar;
if($vacio==0) 
{
	if ($v=="") echo "<option selected value=\"-1\"></option>";
	else  echo "<option value=\"-1\"></option>";
}

while($row=mysql_fetch_array($result))
{
	
	
	if($v==$row[$campo])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row[$campo_indice]."\">".$row[$campo]."</option>\n";
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

function ComboProfe()
{
$sql="select * from Profesores order by Apellidos";

$result=mysql_query($sql);


echo "<select name=\"profe\" size=\"0\">";
echo "<option selected value=\"-1\"></option>";

while($row=mysql_fetch_array($result))
{
		
		echo "<option value=\"".$row[0]."\">".$row[1]." ".$row[2]."</option>\n";
}
echo "</select>\n";
}

function ComboHoras()
{
$a=array("Primera","Segunda","Tercera","Recreo","Cuarta","Quinta","Sexta");
echo "<select name=\"hora\" size=\"0\">";
foreach ($a as $v)
{
	echo "<option value=\"".$v."\">".$v."</option>\n";
}
echo "</select>\n";
}


function ComboPartes($tipo)
{
$sql="SELECT fecha FROM Partes WHERE tipo ='".$tipo."' GROUP BY fecha order by fecha desc";

$result=mysql_query($sql);


echo "<select name=\"fecha\" size=\"0\">";
#echo "<option selected value=\"-1\"></option>";

while($row=mysql_fetch_array($result))
{
		
		echo "<option value=\"".cambiaf_a_normal($row[0])."\">".cambiaf_a_normal($row[0])."</option>\n";
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
		if($_POST["fi"]) $fecini="Registro.Fecha>='".cambiaf_a_mysql($_POST["fi"])."'";
		if($_POST["ff"]) $fecfin="Registro.Fecha<='".cambiaf_a_mysql($_POST["ff"])."'";
		if($_POST["proc"]>-1) $cproc="Registro.Idp=".$_POST["proc"];
		if($_POST["remi"]>-1) $cremi="Registro.Idr=".$_POST["remi"];
		if($_POST["clas"]>-1) $cclas="Registro.Idc=".$_POST["clas"];
		if($_POST["cont"]) $cclas="Contenido like '%".$_POST["cont"]."%'";
		$res=" ".$ctipo;
		if($ccurso!="") $res=$res." and ".$ccurso;
		if($cproc!="") $res=$res." and ".$cproc;
		if($fecini!="") $res=$res." and ".$fecini;
		if($fecfin!="") $res=$res." and ".$fecfin;
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

function ordenar_bien($c)
{
	$cad="replace(replace(replace(replace(replace(".$c.",'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u')";
	$cad="replace(replace(replace(replace(replace(".$cad.",'Á','A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U')";
	return $cad;
	
	}
function perfil($p)
{
	$sql="select * from Perfil where Abr='".$p."'";
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return ($row[2]);
}	
function NombreDe($id)
{
	$sql="select * from Alumnos where Id=".$id;
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return ($row["Nombre"]);
}
function CursoCompDiv($idc)
{
		$sql="select * from Cursos where Id=".$idc;
		
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return ($row["Curso"]);
	
}

function tutoria($curso)
{
        $sql="select * from Profesores where Tutor='".$curso."'";
        $result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return ($row);

}
function correo($l)
{
	$sql="select * from Cartas where Id=".$l;
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return($row);
}
function ProfesorAlumno($l)
{

$sql="select Profesores.Id from Profesores,Alumnos where Alumnos.Unidad=Profesores.Tutor and Alumnos.Unidad<>"" and Alumnos.id=".$l;
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) return($row);
}


?>
