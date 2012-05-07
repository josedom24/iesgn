<?
while($row=mysql_fetch_array($result))

	{

	

	

	fputs($fic,utf8_decode($row["Nombre"]).";"); 

	fputs($fic,utf8_decode($row["Unidad"]).";"); 

	fputs($fic,calcular_num_amonestaciones($row["Ida"])."/".calcular_num_sanciones($row["Ida"])."\n"); 

	

	}
?>
