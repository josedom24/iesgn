<?php



/* 
Obtain .TTF file
NB May not contain All typefaces (bold/italic etc.) or characters for all codepages.
Use ttf2pt1 (http://ttf2pt1.sourceforge.net/) or Windows binary(ttf2pt1.exe) in this folder
e.g. 
ttf2pt1 -b -L win-1251.map c:\windows\fonts\ARIALN.ttf arialnarrow 

cf. makefont.bat

Then run this script
*/


// EDIT THIS ARRAY THEN RUN THIS SCRIPT TO GENERATE THE .php and .z files FROM the .pfb and .afm files

$types[] = array(	'normal'=> 'arialnarrow',
			'bold'=> 'arialnarrowb',
			'italic'=> 'arialnarrowi',
			'bolditalic'=> 'arialnarrowbi'
			);



$cpages = array('iso-8859-2','iso-8859-4','iso-8859-7','iso-8859-9','win-1251');
require('makefont.php');
foreach($types AS $type) {
  foreach($cpages AS $cpage) {
	MakeFont($type['normal'].'-'.$cpage.'.pfb', $type['normal'].'-'.$cpage.'.afm', $cpage);
	if ($type['bold']) MakeFont($type['bold'].'-'.$cpage.'.pfb', $type['bold'].'-'.$cpage.'.afm', $cpage);
	if ($type['bolditalic']) MakeFont($type['bolditalic'].'-'.$cpage.'.pfb', $type['bolditalic'].'-'.$cpage.'.afm',$cpage);
	if ($type['italic']) MakeFont($type['italic'].'-'.$cpage.'.pfb', $type['italic'].'-'.$cpage.'.afm', $cpage);
  }

  // win-1252
  MakeFont($type['normal'].'.pfb', $type['normal'].'.afm', $cpage);
  if ($type['bold']) MakeFont($type['bold'].'.pfb', $type['bold'].'.afm', $cpage);
  if ($type['bolditalic']) MakeFont($type['bolditalic'].'.pfb', $type['bolditalic'].'.afm',$cpage);
  if ($type['italic']) MakeFont($type['italic'].'.pfb', $type['italic'].'.afm', $cpage);
}

?>