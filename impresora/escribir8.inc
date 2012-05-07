<?
$cont=1;
while($row=mysql_fetch_array($result))
	{
	
	fputs($fic,$cont.";"); 
	$cont++;
	fputs($fic,utf8_decode($row["Nombre"]).";"); 

	fputs($fic,calcular_num_amonestaciones($row["Id"])."/".calcular_num_citaciones($row["Id"])."/".calcular_num_sanciones($row["Id"])."\n"); 
	
	
	}
?>