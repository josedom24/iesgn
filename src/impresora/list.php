<?session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
require($_SERVER["DOCUMENT_ROOT"]."/iesgn/fpdf/fpdf.php");
permisos("General");

class PDF extends FPDF
{
function PDF($orientation='P',$unit='mm',$format='A4')
{
    //Llama al constructor de la clase padre
    $this->FPDF($orientation,$unit,$format);
    //Iniciación de variables
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
}


//Cabecera de pagina
function Header()
{
	if(!$_GET["cab"])
	{
    //Logo
    $this->Image($_SERVER['DOCUMENT_ROOT']."/iesgn/img/contraportada.jpg",10,13,33);
    //Arial bold 15
	$this->SetFont('Arial','B',12);
    //Movernos a la derecha
    $this->Cell(40);
	$this->Cell(20,10,"IES Gonzalo Nazareno");
	$this->Ln(4);
	$this->SetFont('Arial','',10);
	$this->Cell(40);
	$this->Cell(20,10,"C/Las Botijas,10");
	$this->Ln(4);
	$this->Cell(40);
	$this->Cell(20,10,"41710 - Dos Hermanas (Sevilla)");
	$this->Ln(4);
	$this->Cell(40);
	$this->Cell(40,10,"Tfno: 955839911 - Fax: 955839915");
	$this->Ln(25);
	}
	
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    $this->Cell(70);
    //Titulo
	$t=file($_SERVER['DOCUMENT_ROOT']."/iesgn/tmp/".$_GET["f"]."head");
	
    $this->Cell((strlen($t[0])*3),10,$t[0],1,0,'C');
	$this->Ln(10);
	$this->SetFont('Arial','B',12);
	$this->Cell((strlen($t[1])*3),10,$t[1],0,0,'C');
    //Salto de linea
    $this->Ln(10);
}

//Pie de pagina
function Footer()
{
    //Posicion: a 1,5 cm del final
    $this->SetY(-15);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Namero de pagina
    $this->Cell(0,10,$this->PageNo().'/{nb}',0,0,'C');
}

function LoadData($file)
{
    //Leer las líneas del fichero
    $lines=file($file);
    $data=array();
    foreach($lines as $line)
        $data[]=explode(';',chop($line));
    return $data;
}

//Tabla simple
function BasicTable($header,$data,$w)
{
	if($header)
	{
    //Cabecera
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1);
    $this->Ln();
	}
    //Datos
   
	foreach($data as $row)
    {
			for($i=0;$i<count($row);$i++)
			
				$this->Cell($w[$i],7,$row[$i],1);
				     $this->Ln();
				if($_GET["l"]==6)
				{
					for($i=0;$i<count($row);$i++)
						$this->Cell($w[$i],15,"",1);
					$this->Ln();
				}
		
        
    }
}

//Una tabla más completa
function ImprovedTable($header,$data)
{
    //Anchuras de las columnas
    $w=array(40,35,40,45);
    //Cabeceras
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C');
    $this->Ln();
    //Datos
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR');
        $this->Cell($w[1],6,$row[1],'LR');
        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
        $this->Ln();
    }
    //Línea de cierre
    $this->Cell(array_sum($w),0,'','T');
}

//Tabla coloreada
function FancyTable($header,$data)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(40,35,40,45);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    //Datos
    $fill=0;
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
        $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
        $this->Ln();
        $fill=!$fill;
    }
    $this->Cell(array_sum($w),0,'','T');
	
}

function carta($carta,$ids)
{
	$this->SetFont('Arial','',8);
	
	$c="";
	foreach($carta as $linea)
	$c=$c.$linea;
	$c="Hola<BR>que tal";
	//foreach ($ids as $i)
	//{
		
			$this->writeHTML($c);
				
	//	$this->AddPage();
//	}
}



}

//Creacion del objeto de la clase heredada



if($_GET["l"]==3|| $_GET["l"]==4|| $_GET["l"]==7||$_GET["l"]==9) $pdf=new PDF("L");
else $pdf=new PDF();
$pdf->AliasNbPages();

//Títulos de las columnas

//Listado resumen de amonestaciones
if($_GET["l"]==1)
{
$header=array('Nombre','Unidad','Amon.');
$w=array(80,25,15);
//Carga de datos

}
if($_GET["l"]==2)
{
		$header=array('Nombre','F. Nacimiento','Unidad','Edad');
$w=array(80,25,25,15);
}

if($_GET["l"]==3)
{
$header=array('Nombre','Unidad','Sancion','Fecha inicio','Fecha fin.');
$w=array(60,20,145,25,25);
}
if($_GET["l"]==4)
{
$header=array('Tipo','Fecha','Fecha Fin.','Sancion','Comentario');
$w=array(25,20,20,80,120);
}
//Listado de profesores
if($_GET["l"]==5)
{
$header=array('Nombre','Telefono','Movil','Email','Dep.');
$w=array(65,22,22,65,16);
}
//Listado de claustro de profesores
if($_GET["l"]==6)
{
$w=array(65,65,65);
}

//Libros
if($_GET["l"]==7)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	$sql="select * from Libros where Curso=".substr($_GET["uni"],0,1)." order by Id";
	$result=mysql_query($sql);
	$header=array('N.','Nombre');
	$w=array(5,60);
	$c=2;
	while($row=mysql_fetch_array($result))
	{
		$header[$c]=$row["Abr"];
		$w[$c]=13;
		$c++;
	}
	

}

if($_GET["l"]==8)
{
	$header=array('N','Nombre','Am./Ci./Sa.');
        $w=array(15,70,25);
}

if($_GET["l"]==9)
{
   $header=array('N','Fecha','Procedencia','Remitente','Doc.','Contenido');
   $w=array(10,20,25,40,25);	
}

$data=$pdf->LoadData($_SERVER['DOCUMENT_ROOT']."/iesgn/tmp/".$_GET["f"]);
$pdf->SetFont('Arial','',11);
if($_GET["l"]==7 || $_GET["l"]==4 || $_GET["l"]==9) $pdf->SetFont('Arial','',9);
$pdf->AddPage();
$pdf->BasicTable($header,$data,$w);

 $pdf->Output();
?>

