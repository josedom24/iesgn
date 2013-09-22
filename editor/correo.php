<?session_start();
if($_POST)
{
	
	
	
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/libgmailer.php");
	permisos("mail");
	if($_POST["asunto"]=="")
	{
		echo "Debes indicar el asunto.<br>";
		echo "<a href=\"".url()."editor/mail.php?msg=".$_POST['FCKeditor1']."\">Volver</a>";
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
		die("");
		
	}
	
	if($_POST["todos"])
	{
		$dest="";
		$sql="select * from Profesores";	
		$result=mysql_query($sql);
		while($row=mysql_fetch_array($result))
		{
			if($row["Email"]!="" && $row["Email"]!="a@a.es") $dest=$dest.$row["Email"].",";
		}
	}
	if($_POST["select2"])
	{
		if(!$_POST["profes"]) 
		{
			$_POST["select"]=1; 
			echo "<center>Debes seleccionar algún profesor...</center>";
		}
		else
		{
			foreach($_POST["profes"] as $v)
			{
				$dest=$dest.$v.",";
			}
		}
	}
	if($_POST["select"] && !$_POST["select2"])
	{
		
		echo "<center><h2>Selecciona destinatario</h2></center>";
		echo "<center><form action=\"correo.php\" method=\"post\">";
		echo "Departamentos:";
		Combo("Departamentos","dep",0,"Departamento");
		if(!$_POST["dep"] || $_POST["dep"]==-1) $cond="";
		else $cond=" where Departamento=".$_POST["dep"];
		
		
		echo "<br>";
		echo "Órganos/Proy.:";
		
		
		//ORGANOS 
		$org=array("","Consejo Escolar","ETCP","TIC","Bilingüe");
		$campos=array("","Ce","Etcp","Tic","Bil");
		
		echo "<select name=\"organos\" size=\"0\">";
		foreach($org as $i=>$o)
		{
		if($i==$_POST["organos"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$i."\">".$o."</option>\n";
		}
		echo "</select><br><br>\n";
		echo "<input type=\"submit\" value=\"Seleccionar\">";
		if($_POST["organos"])
		{
			if($cond=="") $cond=" where ".$campos[$_POST["organos"]]."=1";
			else $cond=$cond." and ".$campos[$_POST["organos"]]."=1";
		}
		$dest="";
		$sql="select * from Profesores ".$cond." order by ".ordenar_bien("Apellidos");	
		
		$result=mysql_query($sql);
		echo "<hr>";
		
		echo "<input type=\"hidden\" name=\"asunto\" value=\"".$_POST["asunto"]."\">";
		echo "<input type=\"hidden\" name=\"select\" value=\"".$_POST["asunto"]."\">";
		echo "<input type=\"hidden\" name=\"FCKeditor1\" value=\"".$_POST["FCKeditor1"]."\">";
		echo "<table>";
		
		while($row=mysql_fetch_array($result))
		{
			if($row["Email"]!="" && $row["Email"]!="a@a.es") 
			{
				echo "<tr>";
				echo "<td><center><input type=\"checkbox\" value=\"".$row["Email"]."\" id=\"".$row["Id"]."\"name=\"profes[]\"";
				if($_POST["all"]) echo "checked";
				echo "></center></td>\n";
				echo "<td>".$row["Apellidos"]." ".$row["Nombre"]."</td>";
				echo "</tr>";
			}	
		}
		
		echo "</table><br><input type=\"submit\" value=\"Enviar\" name=\"select2\">";
		echo "<input type=\"submit\" value=\"Seleccionar todos\" name=\"all\"></form><br>";
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
		die("");
	}
	
	

$gmail_acc="secretaria.gonzalonazareno";
$gmail_pwd="iesgn0708";
$my_timezone="0";

$gmailer = new GMailer();
if ($gmailer->created) {
	$gmailer->setLoginInfo($gmail_acc, $gmail_pwd, $my_timezone);
	if ($gmailer->connectNoCookie()) {
	$sValue = stripslashes( $_POST['FCKeditor1'] ) ;
	
	 move_uploaded_file($_FILES['file']['tmp_name'],path()."tmp/".$_FILES['file']['name']);
		  if($gmailer->send($dest,$_POST["asunto"],$sValue,"","","","",array(path()."tmp/".$_FILES['file']['name']),0,"",1,"")){
			if($_FILE) unlink(path()."tmp/".$_FILES['file']['name']);  
			echo "Correo enviado con éxito...";
			echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
			
		}
		else
		{
			die("Fail to connect because: ".$gmailer->lastActionStatus());
			}
		  
 } else {
	die("Fail to connect because: ".$gmailer->lastActionStatus());
 }
 } else {
 die("Failed to create GMailer because: ".$gmailer->lastActionStatus());
 }
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");


}
?>
