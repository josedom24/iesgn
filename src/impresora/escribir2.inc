<?
while($row=mysql_fetch_array($result))

	{

	

	$edad=edad(cambiaf_a_normal($row["Fecha_nacimiento"]));
	if($edad>18)
	{

	fputs($fic,utf8_decode($row["Nombre"]).";"); 
	fputs($fic,utf8_decode(cambiaf_a_normal($row["Fecha_nacimiento"])).";"); 

	fputs($fic,utf8_decode($row["Unidad"]).";"); 

	fputs($fic,$edad."\n"); 

	}

	}
?>
