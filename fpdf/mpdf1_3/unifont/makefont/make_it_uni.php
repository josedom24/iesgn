<?php
/* Obtain .TTF file
NB May not contain All typefaces (bold/italic etc.) or characters for all codepages.
Use ttf2ufm Windows binary(ttf2ufm.exe) From TCPDF

e.g.
ttf2ufm -a c:\windows\fonts\ArialN.ttf arialnarrow 
ttf2ufm -a c:\windows\fonts\ArialNb.ttf arialnarrowb
ttf2ufm -a c:\windows\fonts\ArialNbi.ttf arialnarrowbi 
ttf2ufm -a c:\windows\fonts\ArialNi.ttf arialnarrowi

cf. makefont.bat

Then run this script

*/


// EDIT THIS ARRAY THEN RUN THIS SCRIPT TO GENERATE THE .php .ctg.z. and .z files FROM the .ufm .t1a and .afm files

$typegroup[] = array(	'normal'=> 'arialnarrow',
			'bold'=> 'arialnarrowb',
			'italic'=> 'arialnarrowi',
			'bolditalic'=> 'arialnarrowbi'
			);






require('makefontuni.php');



foreach($typegroup AS $types) {
	MakeFont($types['normal'].'.ttf', $types['normal'].'.ufm');
	if ($types['bold']) { MakeFont($types['bold'].'.ttf', $types['bold'].'.ufm'); }
	if ($types['bolditalic']) { MakeFont($types['bolditalic'].'.ttf', $types['bolditalic'].'.ufm'); }
	if ($types['italic']) { MakeFont($types['italic'].'.ttf', $types['italic'].'.ufm'); }
}


?>