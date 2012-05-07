<?
while($row=mysql_fetch_array($result))
	{
	
	
	fputs($fic,utf8_decode($row["Nombre"]).";"); 
	fputs($fic,utf8_decode($row["Unidad"]).";"); 
	fputs($fic,utf8_decode($row["Sancion"]).";"); 
	fputs($fic,utf8_decode(cambiaf_a_normal($row["Fecha"])).";"); 
	fputs($fic,utf8_decode(cambiaf_a_normal($row["Fecha_fin"]))."\n"); 
	
	
	}
?>