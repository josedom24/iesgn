<?
while($row=mysql_fetch_array($result))

	{

	
		fputs($fic,$row["Id"].";"); 

	fputs($fic,utf8_decode(cambiaf_a_normal($row["Fecha"])).";"); 

	fputs($fic,utf8_decode(substr($row["Procedencia"],0,14)).";"); 
	fputs($fic,utf8_decode(substr($row["Remitente"],0,18)).";"); 
	fputs($fic,utf8_decode($row["ClaseDocumento"]).";"); 
	if(strlen($row["Contenido"])>90) $cad=substr($row["Contenido"],0,90)."...";
	else $cad=$row["Contenido"];
	fputs($fic,utf8_decode($cad)."\n"); 



	}
?>
