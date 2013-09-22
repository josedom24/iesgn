<?php


// List of ALL available fonts (incl. styles) in non-Unicode directory
// Always put main font (without styles) before font+style; put preferable defaults first in order
// Do NOT include Arial Helvetica Times Courier Symbol or ZapfDingbats
$this->available_fonts = array(
		'dejavusanscondensed','dejavusanscondensedB','dejavusanscondensedI','dejavusanscondensedBI',
		'dejavuserifcondensed','dejavuserifcondensedB','dejavuserifcondensedI','dejavuserifcondensedBI',
		'dejavusans','dejavusansB','dejavusansI','dejavusansBI',
		'dejavuserif','dejavuserifB','dejavuserifI','dejavuserifBI',
		'dejavusansmono','dejavusansmonoB','dejavusansmonoI','dejavusansmonoBI',
		'freesans','freesansB','freesansI','freesansBI',
		'freeserif','freeserifB','freeserifI','freeserifBI',
		'freemono','freemonoB','freemonoI','freemonoBI',
		);

// List of ALL available fonts (incl. styles) in Unicode directory
// Always put main font (without styles) before font+style; put preferable defaults first in order
// Do NOT include Arial Helvetica Times Courier Symbol or ZapfDingbats
$this->available_unifonts = array(
		'dejavusanscondensed','dejavusanscondensedB','dejavusanscondensedI','dejavusanscondensedBI',
		'dejavuserifcondensed','dejavuserifcondensedB','dejavuserifcondensedI','dejavuserifcondensedBI',
		'dejavusans','dejavusansB','dejavusansI','dejavusansBI',
		'dejavuserif','dejavuserifB','dejavuserifI','dejavuserifBI',
		'dejavusansmono','dejavusansmonoB','dejavusansmonoI','dejavusansmonoBI',
		'freesans','freesansB','freesansI','freesansBI',
		'freeserif','freeserifB','freeserifI','freeserifBI',
		'freemono','freemonoB','freemonoI','freemonoBI',
		'garuda','garudaB','garudaI','garudaBI',
		'norasi','norasiB','norasiI','norasiBI',
		'scheherazade',
		);


// List of all font families in directories (either) 
// + any others that may be read from a stylesheet - to determine 'type'
// should include sans-serif, serif and monospace, arial, helvetica, times and courier
// The order will determine preference when substituting fonts in certain circumstances
$this->sans_fonts = array('dejavusanscondensed','dejavusans','freesans','franklin','tahoma','garuda','calibri',
				'verdana','geneva','lucida','arial','helvetica','sans','sans-serif','cursive','fantasy');

$this->serif_fonts = array('dejavuserifcondensed','dejavuserif','freeserif','constantia','georgia','albertus','times','norasi','scheherazade','serif');

$this->mono_fonts = array('dejavusansmono','freemono','courier','mono','monospace');


?>