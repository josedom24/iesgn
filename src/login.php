<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
if($_COOKIE["iesgn"])
{
	
	$_POST["usuario"]=$_COOKIE["iesgn"][0];
	$pass=$_COOKIE["iesgn"][1];
	
}
else
	$pass=md5($_POST["pass"]);


// Buscar un registro en la tabla con el usuario que hemos introducido en el formulario
	
		
		$sql="select * from usuarios where usuario='".$_POST["usuario"]."'";
		$result=mysql_query($sql) or die ("Error:".$sql."<br>".mysql_error());
		
		// Si existe el registro

		if($row=mysql_fetch_array($result))	
		{
				
				
					
	
				//La contraseña guardada en la tabla es igual 
				//al md5 de la contraseña introducida en el formulario
			
				//Si las contraseñas son iguales
				//Abrimos la pagina prohibida.php
				//Utilizando Header("Location:.......
						
						
					if($row["Pass"]==$pass)
					{
					
						//Creamos variables de sesision
						$_SESSION["usuario"]=$_POST["usuario"];
						$_SESSION["perfil"]=$row["Perfil"];
						setcookie("ult_usuario",$_POST["usuario"],time()+1000000);
						
						//Si queremos reocrdad el usuario
						if($_POST["recordar"]  || $_COOKIE["iesgn"] )
						{
							setcookie("iesgn[0]",$_POST["usuario"],time()+1000000);
							setcookie("iesgn[1]",$pass,time()+1000000);
						}
						else
						{
							setcookie("iesgn[0]",$_POST["usuario"],time()-1);
							setcookie("iesgn[1]",md5($_POST["pass"]),time()-1);
						}
						Header("Location: index.php");
						
						die("");
					}
		
			//Si no son iguales
			// Abrir la pagina entrada.php con parametro get error=2
			
		
					else
					{
						Header("Location:index.php?error=2");
						die("");
					}
		
	}

				

		//Si llegamos al final significa que no hemos entrado en algun if
	//Saltaremos entrada.php con el parametro get error=1

			Header("Location: index.php?error=1");

?>
