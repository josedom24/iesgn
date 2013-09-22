<?php


/*******************************************************************************
* Software: mPDF, Unicode-HTML Free PDF generator                              *
* Version:  1.3    based on                                                    *
*           FPDF 1.52 by Olivier PLATHEY                                       *
*           UFPDF 0.1 by Steven Wittens                                        *
*           HTML2FPDF 3.0.2beta by Renato Coelho                               *
* Date:     2008-09-21                                                         *
* Author:   Ian Back <ianb@bpm1.com>                                           *
* License:  GPL                                                                *
*                                                                              *
* Changes:  See changelog.txt                                                  *
*******************************************************************************/



define('mPDF_VERSION','1.3');
if (!defined('_MPDF_PATH')) define('_MPDF_PATH','');
require_once(_MPDF_PATH.'htmltoolkit.php');


class mPDF
{

// Added mPDF 1.3
var $PageNumSubstitutions = array();

// See mpdf_config.php for these next 5 values
var $available_fonts;
var $available_unifonts;
var $sans_fonts;
var $serif_fonts;
var $mono_fonts;

// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)
var $available_CJK_fonts = array(
		'gb','big5','sjis','uhc',
		'gb-hw','big5-hw','sjis-hw','uhc-hw',
		'gbB','big5B','sjisB','uhcB',
		'gb-hwB','big5-hwB','sjis-hwB','uhc-hwB',
		'gbI','big5I','sjisI','uhcI',
		'gb-hwI','big5-hwI','sjis-hwI','uhc-hwI',
		'gbBI','big5BI','sjisBI','uhcBI',
		'gb-hwBI','big5-hwBI','sjis-hwBI','uhc-hwBI',
		);

// Added v1.2 option to continue if invalid UTF-8 chars - used in function is_utf8()
var $ignore_invalid_utf8 = false;

// Added in mPDF v1.2
var $allowedCSStags = 'DIV|P|H1|H2|H3|H4|H5|H6|A|BODY|TABLE|HR|THEAD|TH|TR|TD|UL|OL|LI|PRE|BLOCKQUOTE|ADDRESS|DL|DT|DD';
var $cascadeCSS = array();

// Added mPDF 1.2 HTML headers and Footers
var $HTMLHeader;
var $HTMLFooter;
var $HTMLHeaderE;	// for Even pages
var $HTMLFooterE;	// for Even pages
var $bufferoutput = false; 

// This will force all fonts to be substituted with Arial(Helvetica) Times or Courier when using codepage win-1252 - makes smaller files
var $use_embeddedfonts_1252 = false;

// If using a CJK codepage with only CJK/ASCII or embedded characters, this will prevent laoding of Unicode fonts - makes smaller files
var $use_CJK_only = false;

// Allows automatic character set conversion if "charset=xxx" detected in html header (WriteHTML() )
var $allow_charset_conversion = true;

var $jSpacing;	// Spacing method when Justifying [ C, W or blank (for mixed 40/60) ]
var $jSWord = 0.4;	// Percentage(/100) of space (when justifying margins) to allocate to Word vs. Character
var $jSmaxChar = 2;	// Maximum spacing to allocate to character spacing. (0 = no maximum)

var $orphansAllowed = 5;	// No of SUP or SUB characters to include on line to avoid leaving e.g. end of line//<sup>32</sup>
var $max_colH_correction = 1.15;	// Maximum ratio to adjust column height when justifying - too large a value can give ugly results


var $table_error_report = false;		// Die and report error if table is too wide to contain whole words
var $table_error_report_param = '';		// Parameter which can be passed to show in error report i.e. chapter number being processed//
var $BiDirectional=false;	// automatically determine BIDI text in LTR page
var $text_input_as_HTML = false; // Converts all entities in Text inputs to UTF-8 before encoding
var $Anchor2Bookmark = 0;	// makes <a name=""> into a bookmark as well as internal link target; 1 = just name; 2 = name (p.34)
var $list_indent_first_level = 0;	// 1/0 yex/no to indent first level of list
var $shrink_tables_to_fit = 2.2;	// automatically reduce fontsize in table if words would have to split ( not in CJK)
						// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize

var $rtlCSS = 2; 	// RTL: 0 overrides defaultCSS; 1 overrides stylesheets; 2 overrides inline styles - TEXT-ALIGN left => right etc.
			// when directionality is set to rtl

// Automatically correct for tags where HTML specifies optional end tags e.g. P,LI,DD,TD
// If you are confident input html is valid XHTML, turning this off may make it more reliable(?)
var $allow_html_optional_endtags = true;

var $img_dpi = 96;	// Default dpi to output images if size not defined

// Values used if simple FOOTER/HEADER given i.e. not array
var $defaultheaderfontsize = 8;	// pt
var $defaultheaderfontstyle = 'BI';	// '', or 'B' or 'I' or 'BI'
var $defaultheaderline = 1;		// 1 or 0 - line under the header
var $defaultfooterfontsize = 8;	// pt
var $defaultfooterfontstyle = 'BI';	// '', or 'B' or 'I' or 'BI'
var $defaultfooterline = 1;		// 1 or 0 - line over the footer
var $header_line_spacing = 0.25;	// spacing between bottom of header and line (if present) - function of fontsize
var $footer_line_spacing = 0.25;	// spacing between bottom of header and line (if present) - function of fontsize


var $showdefaultpagenos = true;	// If ->startPageNums not used, ?show the default document page numbers in headers/footers

var $chrs;	// Added mPDF 1.1 used to store chr() and ord() - quicker than using functions
var $ords;

//////////////////////////////////////////////

// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
var $defaultCSS = array(
	'BODY' => array(
		'FONT-FAMILY' => 'sans-serif',
		'FONT-SIZE' => '11pt',
		'TEXT-ALIGN' => 'left',
		'LINE-HEIGHT' => 1.33,
		'MARGIN-COLLAPSE' => collapse, /* Custom property to collapse top/bottom margins at top/bottom of page - ignored in tables/lists */
	),
	'P' => array(
		'TEXT-INDENT' => '0pt',	/* ?HTML SPEC is INDENT? */
		'TEXT-ALIGN' => 'left',
		'MARGIN' => '1.12em 0',
	),
	'H1' => array(
		'FONT-SIZE' => '2em',
		'FONT-WEIGHT' => 'bold',
		'PAGE-BREAK-AFTER' => 'avoid',
		'MARGIN' => '0.67em 0',
	),
	'H2' => array(
		'FONT-SIZE' => '1.5em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.75em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H3' => array(
		'FONT-SIZE' => '1.17em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.83em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H4' => array(
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.12em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H5' => array(
		'FONT-SIZE' => '0.83em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.5em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H6' => array(
		'FONT-SIZE' => '0.75em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.67em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'HR' => array(
		'COLOR' => '#888888',
		'TEXT-ALIGN' => 'center',
		'WIDTH' => '100%',
		'HEIGHT' => '0.2mm',
		'MARGIN-TOP' => '0.83em',
		'MARGIN-BOTTOM' => '0.83em',
	),
	'PRE' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'S' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'STRIKE' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'DEL' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'SUB' => array(
		'VERTICAL-ALIGN' => 'sub',
		'FONT-SIZE' => '55%',	/* Recommended 0.83em */
	),
	'SUP' => array(
		'VERTICAL-ALIGN' => 'super',
		'FONT-SIZE' => '55%',	/* Recommended 0.83em */
	),
	'U' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'INS' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'B' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'STRONG' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'I' => array(
		'FONT-STYLE' => 'italic',
	),
	'CITE' => array(
		'FONT-STYLE' => 'italic',
	),
	'Q' => array(
		'FONT-STYLE' => 'italic',
	),
	'EM' => array(
		'FONT-STYLE' => 'italic',
	),
	'VAR' => array(
		'FONT-STYLE' => 'italic',
	),
	'SAMP' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'CODE' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'KBD' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'TT' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'SMALL' => array(
		'FONT-SIZE' => '83%',
	),
	'BIG' => array(
		'FONT-SIZE' => '117%',
	),
	'ACRONYM' => array(
		'FONT-SIZE' => '77%',
		'FONT-WEIGHT' => 'bold',
	),
	'ADDRESS' => array(
		'FONT-STYLE' => 'italic',
	),
	'BLOCKQUOTE' => array(
		'MARGIN-LEFT' => '40px',
		'MARGIN-RIGHT' => '40px',
		'MARGIN-TOP' => '1.12em',
		'MARGIN-BOTTOM' => '1.12em',
	),
	'A' => array(
	/*	'TEXT-DECORATION' => 'underline',	*/
		'COLOR' => '#000066',
	),
	'UL' => array(
		'MARGIN' => '0.83em 0',		/* only applied to top-level of nested lists */
		'TEXT-INDENT' => '1.3em',	/* Custom effect - list indent */
		'LINE-HEIGHT' => 1.3,
	),
	'OL' => array(
		'MARGIN' => '0.83em 0',		/* only applied to top-level of nested lists */
		'TEXT-INDENT' => '1.3em',	/* Custom effect - list indent */
		'LINE-HEIGHT' => 1.3,
	),
	'DL' => array(
		'MARGIN' => '1.67em 0',
	),
	'DT' => array(
	),
	'DD' => array(
		'PADDING-LEFT' => '40px',
	),
	'TABLE' => array(
		'MARGIN' => '0.83em 0',
		'PADDING' => '0.35em',
/*		'BORDER' => '0px solid #000000',	Edited in mPDF 1.3 */
		'TEXT-ALIGN' => 'left',
		'VERTICAL-ALIGN' => 'top',
		'LINE-HEIGHT' => '1.2',
	),
	'THEAD' => array(
		'FONT-WEIGHT' => 'bold',
		'VERTICAL-ALIGN' => 'bottom',
		'TEXT-ALIGN' => 'center',
	),
	'TH' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'TD' => array(
	),
	'IMG' => array(
		'MARGIN' => '0.2em',	/* 0.5em is HTML default */
		'VERTICAL-ALIGN' => 'middle',
	),
	'INPUT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'VERTICAL-ALIGN' => 'middle',
		'FONT-SIZE' => '0.9em',
	),
	'SELECT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'middle',
	),
	'TEXTAREA' => array(
		'FONT-FAMILY' => 'monospace',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'top',
	),
);

///////////END OF USER-DEFINED VARIABLES//////////////////

//internal attributes
var $form_element_spacing;
var $textarea_lineheight = 1.25;
var $linemaxfontsize;
var $lineheight_correction;
var $lastoptionaltag = '';	// Save current block item which HTML specifies optionsl endtag
var $pageoutput;
var $charset_in = '';
var $blk = array();
var $blklvl = 0;
var $ColumnAdjust;
var $ws;	// Word spacing
var $HREF; //! string
var $pgwidth; //! float
var $fontlist; //! array 
var $issetfont; //! bool
var $issetcolor; //! bool
var $titulo; //! string
var $oldx; //! float
var $oldy; //! float
var $B; //! int
var $U; //! int
var $I; //! int

var $tablestart; //! bool
var $tdbegin; //! bool
var $table; //! array
var $cell; //! array 
var $col; //! int
var $row; //! int

var $divbegin; //! bool
var $divalign; //! char
var $divwidth; //! float
var $divheight; //! float
var $divrevert; //! bool
var $spanbgcolor; //! bool

var $spanlvl;
var $listlvl; //! int
var $listnum; //! int
var $listtype; //! string
//array(lvl,# of occurrences)
var $listoccur; //! array
//array(lvl,occurrence,type,maxnum)
var $listlist; //! array
//array(lvl,num,content,type)
var $listitem; //! array

var $pjustfinished; //! bool
var $ignorefollowingspaces; //! bool
var $SUP; //! bool
var $SUB; //! bool
var $SMALL; //! bool
var $BIG; //! bool
var $toupper; //! bool
var $tolower; //! bool
var $dash_on; //! bool
var $dotted_on; //! bool
var $strike; //! bool

var $CSS; //! array
var $textbuffer; //! array
var $currentfontstyle; //! string
var $currentfontfamily; //! string
var $currentfontsize; //! string
var $colorarray; //! array
var $bgcolorarray; //! array
var $internallink; //! array
var $enabledtags; //! string

var $lineheight; //! int
var $basepath; //! string
// array('COLOR','WIDTH','OLDWIDTH')
var $outlineparam; //! array
var $outline_on; //! bool

var $specialcontent; //! string
var $selectoption; //! array

//options attributes
var $usecss; //! bool
var $usepre; //! bool
var $usetableheader; //! bool
var $shownoimg; //! bool

var $objectbuffer;

// Table Rotation
var $table_rotate;	// flag used for table rotation
var $tbrot_maxw;		// Max width for rotated table
var $tbrot_maxh;		// Max height
var $tablebuffer;		// Buffer used when rotating table

// Edited mPDF 1.1 keeping block together on one page
var $divbuffer;		// Buffer used when keeping DIV on one page
var $keep_block_together;	// Keep a Block from page-break-inside: avoid
var $ktLinks;		// Keep-together Block links array
var $ktBlock;		// Keep-together Block array
var $ktReference;
var $ktBMoutlines;
var $_kttoc;


var $tbrot_y0;		// y position starting table rotate
var $tbrot_x0;		// x position starting table rotate
var $tbrot_w;		// Actual printed width
var $tbrot_h;		// Actual printed height
var $TOCmark = 0;		// Page to insert Table of Contents

var $isunicode=false;
var $codepage='win-1252';
var $isCJK = false;
var $mb_encoding='windows-1252';
var $directionality='ltr';

var $pregRTLchars = "[\x{0590}-\x{07BF}\x{FB50}-\x{FDFF}]";	// pattern used to detect RTL characters -> force RTL

var $pregNonLTRchars = "[^\x{0590}-\x{07BF}\x{FB50}-\x{FDFF}\x{A0}\"\'\(\). ,;:\-]";// pattern used to detect LTR characters within RTL chunk
		// Includes RTL chars PLUS Non-directional characters \x{A0} = nbsp

// Removed in mPDF v1.2
//var $memory_opt = false;	// Memory Optimization - added mPDF1.1
var $extgstates; // Used for alpha channel - Transparency (Watermark)
var $tt_savefont;
var $mgl;
var $mgt;
var $mgr;
var $mgb;

var $tts = false;
var $ttz = false;
var $tta = false;

var $headerDetails=array();
var $footerDetails=array();
var $TopicIsUnvalidated = 0;
var $useOddEven = 0;

var $splitdivborderwidth = 0.2;	// Linewidth used when drawing border split across pages

// Best to alter the below variables using default stylesheet above
var $div_margin_bottom;	
var $div_bottom_border = '';
var $p_margin_bottom;
var $p_bottom_border = '';
var $page_break_after_avoid = false;
var $margin_bottom_collapse = false;
var $img_margin_top;	// default is set at top of fn.openTag 'IMG'
var $img_margin_bottom;
var $text_indent = 0;	// Indent hanging margin for <p>
var $list_indent;	// array
var $list_align;	// array
var $list_margin_bottom; 
var $default_font_size;	// in pts
var $default_lineheight_correction=1.2;	// Value 1 sets lineheight=fontsize height; 
var $original_default_font_size;	// used to save default sizes when using table default
var $original_default_font;
var $watermark_font = '';
var $defaultAlign = 'L';

// TABLE
var $defaultTableAlign = 'L';
var $tablethead = 0;
var $thead_font_weight;	
var $thead_font_style;	
var $thead_valign_default;	
var $thead_textalign_default;	
// Added mPDF 1.3 for rotated text in cell
var $trow_text_rotate;	// 90,-90

var $cellPaddingL;
var $cellPaddingR;
var $cellPaddingT;
var $cellPaddingB;
var $table_lineheight;
var $table_margin_bottom;
// Added mPDF 1.1 for correct table border inheritance
var $table_border_attr_set = 0;
var $table_border_css_set = 0;

var $shrin_k = 1.0;			// factor with which to shrink tables - used internally - do not change
var $shrink_this_table_to_fit = 0;	// flag used when table autosize turned on and off by tags
						// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
var $UnvalidatedText = '';

var $MarginCorrection = 0;	// corrects for OddEven Margins
var $margin_footer=15;	// in mm
var $margin_header=15;	// in mm

var $tabletheadjustfinished = false;
var $usingembeddedfonts = false;
var $charspacing=0;

//Private properties FROM FPDF
var $DisplayPreferences=''; //EDITEI - added
var $outlines=array(); //EDITEI - added
var $flowingBlockAttr; //EDITEI - added
var $page;               //current page number
var $n;                  //current object number
var $offsets;            //array of object offsets
var $buffer;             //buffer holding in-memory PDF
var $pages;              //array containing pages
var $state;              //current document state
var $compress;           //compression flag
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $OrientationChanges; //array indicating orientation changes
var $k;                  //scale factor (number of points in user unit)
var $fwPt,$fhPt;         //dimensions of page format in points
var $fw,$fh;             //dimensions of page format in user unit
var $wPt,$hPt;           //current dimensions of page in points
var $w,$h;               //current dimensions of page in user unit
var $lMargin;            //left margin
var $tMargin;            //top margin
var $rMargin;            //right margin
var $bMargin;            //page break margin
var $cMarginL;            //cell margin Left
var $cMarginR;            //cell margin Right
var $cMarginT;            //cell margin Left
var $cMarginB;            //cell margin Right
var $DeflMargin;            //Default left margin
var $DefrMargin;            //Default right margin
var $x,$y;               //current position in user unit for cell positioning
var $lasth;              //height of last cell printed
var $LineWidth;          //line width in user unit
var $CoreFonts;          //array of standard font names
var $fonts;              //array of used fonts
var $FontFiles;          //array of font files
var $diffs;              //array of encoding differences
var $images;             //array of used images
var $PageLinks;          //array of links in pages
var $links;              //array of internal links
var $FontFamily;         //current font family
var $FontStyle;          //current font style
var $underline;          //underlining flag
var $CurrentFont;        //current font info
var $FontSizePt;         //current font size in points
var $FontSize;           //current font size in user unit
var $DrawColor;          //commands for drawing color
var $FillColor;          //commands for filling color
var $TextColor;          //commands for text color
var $ColorFlag;          //indicates whether fill and text colors are different
var $AutoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
var $InFooter;           //flag set when processing footer

// Added mPDF 1.3 as flag to prevent page triggering in footers containing table
var $InHTMLFooter;

var $processingFooter;   //flag set when processing footer - added for columns
var $processingHeader;   //flag set when processing header - added for columns
var $ZoomMode;           //zoom display mode
var $LayoutMode;         //layout display mode
var $title;              //title
var $subject;            //subject
var $author;             //author
var $keywords;           //keywords
var $creator;            //creator
var $AliasNbPages;       //alias for total number of pages
var $ispre=false;


// NOT Currently used
var $outerblocktags = array('DIV','FORM','CENTER','DL');
var $innerblocktags = array('P','BLOCKQUOTE','ADDRESS','PRE','HR','H1','H2','H3','H4','H5','H6','DT','DD');
var $inlinetags = array('SPAN','TT','I','B','BIG','SMALL','EM','STRONG','DFN','CODE','SAMP','KBD','VAR','CITE','ABBR','ACRONYM','STRIKE','S','U','DEL','INS','Q','FONT','TTS','TTZ','TTA');
var $listtags = array('UL','OL','LI');
var $tabletags = array('TABLE','THEAD','TBODY','TFOOT','TR','TH','TD');
var $formtags = array('TEXTAREA','INPUT','SELECT');


//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************
//**********************************

function mPDF($codepage='win-1252',$format='A4',$default_font_size=0,$default_font='',$mgl=15,$mgr=15,$mgt=16,$mgb=16,$mgh=9,$mgf=9) {
	$orientation='P';
	$unit='mm';


	//Some checks
	$this->_dochecks();
	//Initialization of properties
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->objectbuffer = array();
	$this->pages=array();
	$this->OrientationChanges=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->diffs=array();
	$this->images=array();
	$this->links=array();
	$this->InFooter=false;
	$this->processingFooter=false;
	$this->processingHeader=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=9;
	$this->underline=false;
	$this->DrawColor='0 G';
	$this->FillColor='0 g';
	$this->TextColor='0 g';
	$this->ColorFlag=false;
	$this->extgstates = array();
	// Added mPDF 1.3 as flag to prevent page triggering in footers containing table
	$this->InHTMLFooter = false;

	// FORM ELEMENT SPACING
	$this->form_element_spacing['select']['outer']['h'] = 0.5;	// Horizontal spacing around SELECT
	$this->form_element_spacing['select']['outer']['v'] = 0.5;	// Vertical spacing around SELECT
	$this->form_element_spacing['select']['inner']['h'] = 0.7;	// Horizontal padding around SELECT
	$this->form_element_spacing['select']['inner']['v'] = 0.7;	// Vertical padding around SELECT
	$this->form_element_spacing['input']['outer']['h'] = 0.5;
	$this->form_element_spacing['input']['outer']['v'] = 0.5;
	$this->form_element_spacing['input']['inner']['h'] = 0.7;
	$this->form_element_spacing['input']['inner']['v'] = 0.7;
	$this->form_element_spacing['textarea']['outer']['h'] = 0.5;
	$this->form_element_spacing['textarea']['outer']['v'] = 0.5;
	$this->form_element_spacing['textarea']['inner']['h'] = 1;
	$this->form_element_spacing['textarea']['inner']['v'] = 0.5;
	$this->form_element_spacing['button']['outer']['h'] = 0.5;
	$this->form_element_spacing['button']['outer']['v'] = 0.5;
	$this->form_element_spacing['button']['inner']['h'] = 2;
	$this->form_element_spacing['button']['inner']['v'] = 1;


	//Scale factor
	$this->k=72/25.4;	// Will only use mm


	//Page format
	if(is_string($format))
	{
		$format=strtolower($format);
		if($format=='a3')	$format=array(841.89,1190.55);	// Sizes in Pt
		elseif($format=='a4')	$format=array(595.28,841.89);
		elseif($format=='a5')	$format=array(420.94,595.28);
		elseif($format=='letter')	$format=array(612,792);
		elseif($format=='legal') $format=array(612,1008);
		elseif($format=='a3-l')	$format=array(1190.55,841.89);
		elseif($format=='a4-l')	$format=array(841.89,595.28);
		elseif($format=='a5-l')	$format=array(595.28,420.94);
		elseif($format=='letter-l')	$format=array(792,612);
		elseif($format=='legal-l') $format=array(1008,612);
		elseif($format=='b') $format=array(362.83,561.26 );	//	'B' format paperback size 128x198mm
		elseif($format=='a') $format=array(314.65,504.57 );	//	'A' format paperback size 111x178mm
		elseif($format=='demy') $format=array(382.68,612.28 );	//	'Demy' format paperback size 135x216mm
		elseif($format=='royal') $format=array(433.70,663.30 );	//	'Royal' format paperback size 153x234mm

		else $this->Error('Unknown page format: '.$format);
		$this->fwPt=$format[0];
		$this->fhPt=$format[1];
	}
	else
	{
		$this->fwPt=$format[0]*$this->k;
		$this->fhPt=$format[1]*$this->k;
	}
	$this->fw=$this->fwPt/$this->k;
	$this->fh=$this->fhPt/$this->k;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' or $orientation=='portrait')
	{
		$this->DefOrientation='P';
		$this->wPt=$this->fwPt;
		$this->hPt=$this->fhPt;
	}
	elseif($orientation=='l' or $orientation=='landscape')
	{
		$this->DefOrientation='L';
		$this->wPt=$this->fhPt;
		$this->hPt=$this->fwPt;
	}
	else $this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$this->DefOrientation;

	$this->w=$this->wPt/$this->k;
	$this->h=$this->hPt/$this->k;

	//PAGE MARGINS
	//mm=2.835/$this->k;

	$this->margin_header=$mgh;
	$this->margin_footer=$mgf;

	$margin=($mgl*(2.835/$this->k));
	$bmargin=($mgb*(2.835/$this->k));

	$this->DeflMargin = $mgl*(2.835/$this->k); 
	$this->DefrMargin = $mgr*(2.835/$this->k); 

	$this->SetMargins($this->DeflMargin,$this->DefrMargin,($mgt*(2.835/$this->k)));
	//Automatic page break
	$this->SetAutoPageBreak(true,$bmargin);

	//Interior cell margin (1 mm)
	$this->cMarginL = 1;
	$this->cMarginR = 1;
	//Line width (0.2 mm)
	$this->LineWidth=.567/$this->k;

	//To make the function Footer() work - replaces {nb} with page number
	//$this->AliasNbPages();

	//Enable all tags as default
	$this->DisableTags();
	//Full width display mode
	$this->SetDisplayMode(100);	// fullwidth?		'fullpage'
	//Compression
	$this->SetCompression(true);
	//Set default display preferences
	$this->DisplayPreferences('');

	require_once(_MPDF_PATH.'mpdf_config.php');	// font data

	//Standard fonts
	$this->CoreFonts=array('courier'=>'Courier','courierB'=>'Courier-Bold','courierI'=>'Courier-Oblique','courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica','helveticaB'=>'Helvetica-Bold','helveticaI'=>'Helvetica-Oblique','helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman','timesB'=>'Times-Bold','timesI'=>'Times-Italic','timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats');
	$this->fontlist=array("times","courier","helvetica","symbol","zapfdingbats");

	if (strtolower($codepage) == 'utf-8') { $codepage = 'UTF-8'; }
	else if (strtolower($codepage) == 'utf8') { $codepage = 'UTF-8'; }
	else if (strtolower($codepage) == 'big5') { $codepage = 'BIG5'; }
	else if (strtolower($codepage) == 'big-5') { $codepage = 'BIG5'; }
	else if (strtolower($codepage) == 'gbk') { $codepage = 'GBK'; }
	else if (strtolower($codepage) == 'cp936') { $codepage = 'GBK'; }
	else if (strtolower($codepage) == 'uhc') { $codepage = 'UHC'; }
	else if (strtolower($codepage) == 'cp949') { $codepage = 'UHC'; }
	else if (strtolower($codepage) == 'shift_jis') { $codepage = 'SHIFT_JIS'; }
	else if (strtolower($codepage) == 'shift-jis') { $codepage = 'SHIFT_JIS'; }
	else if (strtolower($codepage) == 'sjis') { $codepage = 'SHIFT_JIS'; }
	else if (strtolower($codepage) == 'win-1251') { $codepage = 'win-1251'; }
	else if (strtolower($codepage) == 'windows-1251') { $codepage = 'win-1251'; }
	else if (strtolower($codepage) == 'cp1251') { $codepage = 'win-1251'; }
	else if (strtolower($codepage) == 'win-1252') { $codepage = 'win-1252'; }
	else if (strtolower($codepage) == 'windows-1252') { $codepage = 'win-1252'; }
	else if (strtolower($codepage) == 'cp1252') { $codepage = 'win-1252'; }
	else if (strtolower($codepage) == 'iso-8859-2') { $codepage = 'iso-8859-2'; }
	else if (strtolower($codepage) == 'iso-8859-4') { $codepage = 'iso-8859-4'; }
	else if (strtolower($codepage) == 'iso-8859-7') { $codepage = 'iso-8859-7'; }
	else if (strtolower($codepage) == 'iso-8859-9') { $codepage = 'iso-8859-9'; }


	// Autodetect IF codepage is a language_country string (en-GB or en_GB or en)
	if (((strlen($codepage) == 5) && ($codepage != 'UTF-8')) || (strlen($codepage) == 2)) {
		// in HTMLToolkit
		list ($codepage,$mpdf_pdf_unifonts,$mpdf_directionality,$mpdf_jSpacing) = GetCodepage($codepage);
		$this->jSpacing = $mpdf_jSpacing;
		$this->RestrictUnicodeFonts($mpdf_pdf_unifonts); 
		$this->SetDirectionality($mpdf_directionality);
	}

	$this->codepage =  $codepage;
	if ($codepage == 'UTF-8') { $this->isunicode = true; }
	if (($codepage == 'BIG5') || ($codepage == 'GBK') || ($codepage == 'UHC') || ($codepage == 'SHIFT_JIS')) { 
		$this->isCJK = true;
		require(_MPDF_PATH . 'CJKdata.php');
		// FONTS
		if ($codepage == 'BIG5') { $this->AddCJKFont('big5'); $default_font = 'big5';}
		else if ($codepage == 'GBK') { $this->AddCJKFont('gb'); $default_font = 'gb'; }
		else if ($codepage == 'SHIFT_JIS') { $this->AddCJKFont('sjis'); $default_font = 'sjis'; }
		else if ($codepage == 'UHC') { $this->AddCJKFont('uhc'); $default_font = 'uhc';}

		$this->isunicode = true; 
		$this->use_CJK_only = true;

	}

	if ($this->isunicode) { define('FPDF_FONTPATH',_MPDF_PATH.'unifont/'); }
	else { define('FPDF_FONTPATH',_MPDF_PATH.'font/'); }

	if ($default_font=='') { 
	  if ($codepage == 'win-1252') { $default_font = 'helvetica' ; }
	  else { $default_font = $this->defaultCSS['BODY']['FONT-FAMILY'] ; }
	}
	if (!$default_font_size) { 
		$mmsize = ConvertSize($this->defaultCSS['BODY']['FONT-SIZE'],$this->default_font_size);
		$default_font_size = $mmsize*(72/25.4);
	}

	if ($default_font) { $this->SetDefaultFont($default_font); }
	if ($default_font_size) { $this->SetDefaultFontSize($default_font_size); }

	$this->setMBencoding($this->codepage);	// sets $this->mb_encoding
	@mb_regex_encoding('UTF-8'); 	// Edit mPDF 1.1 Required for mb_split

	$this->setHiEntitySubstitutions(GetHiEntitySubstitutions());

	$this->SetLineHeight();	// lineheight is in mm

	$this->pgwidth = $this->fw - $this->lMargin - $this->rMargin ;

	$this->SetFillColor(255);
	$this->HREF='';
	$this->titulo='';
	$this->oldy=-1;
	$this->B=0;
	$this->U=0;
	$this->I=0;

	$this->listlvl=0;
	$this->listnum=0; 
	$this->listtype='';
	$this->listoccur=array();
	$this->listlist=array();
	$this->listitem=array();

	$this->tablestart=false;
	$this->tdbegin=false; 
	$this->table=array(); 
	$this->cell=array();  
	$this->col=-1; 
	$this->row=-1; 

	$this->divbegin=false;
	$this->divalign=$this->defaultAlign;
	$this->divwidth=0; 
	$this->divheight=0; 
	$this->spanbgcolor=false;
	$this->divrevert=false;

	$this->issetfont=false;
	$this->issetcolor=false;

	$this->blockjustfinished=false;
	$this->ignorefollowingspaces = true; //in order to eliminate exceeding left-side spaces
	$this->toupper=false;
	$this->tolower=false;
	$this->dash_on=false;
	$this->dotted_on=false;
	$this->SUP=false;
	$this->SUB=false;
	$this->strike=false;

	$this->currentfontfamily='';
	$this->currentfontsize='';
	$this->currentfontstyle='';
	$this->colorarray=array();
	$this->spanbgcolorarray=array();
	$this->textbuffer=array();
	$this->CSS=array();
	$this->internallink=array();

	$this->basepath = "";
  	if ($_REQUEST['SCRIPT_URI']) { $this->basepath = dirname($_REQUEST['SCRIPT_URI']).'/'; }

	$this->outlineparam = array();
	$this->outline_on = false;

	$this->specialcontent = '';
	$this->selectoption = array();

	$this->shownoimg=true;
	$this->usetableheader=false;
	$this->usecss=true;
	$this->usepre=true;

	for($i=0;$i<256;$i++) {
		$this->chrs[$i] = chr($i);
		$this->ords[chr($i)] = $i;

	}
}


function RestrictUnicodeFonts($res) {
	// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
	if (count($res)) {	// Leave full list of available fonts if passed blank array
	   foreach($this->available_unifonts AS $k => $f) {
		if (!in_array($f,$res)) { 
			unset($this->available_unifonts[$k]);
		}
	   }
	}
	if (count($this->available_unifonts) == 0) { die("You have restricted the number of available fonts to 0!"); }
	$this->available_unifonts = array_values($this->available_unifonts);
}


function setMBencoding($enc) {
	// Edited mPDF1.1 - only call mb_internal_encoding if need to change
	$curr = $this->mb_encoding;
	// Sets encoding string for use in mb_string functions
	if ($enc == 'win-1252') { $this->mb_encoding = 'windows-1252'; }
	else if ($enc == 'win-1251') { $this->mb_encoding = 'windows-1251'; }
	else if ($enc == 'UTF-8') { $this->mb_encoding = 'UTF-8'; }
	else if ($enc == 'BIG5') { $this->mb_encoding = 'BIG-5'; }
	else if ($enc == 'GBK') { $this->mb_encoding = 'CP936'; }	// cp936
	else if ($enc == 'SHIFT_JIS') { $this->mb_encoding = 'SJIS'; }
	else if ($enc == 'UHC') { $this->mb_encoding = 'UHC'; }	// cp949
	else { $this->mb_encoding = $enc; }	// works for iso-8859-n
	if ($this->mb_encoding && $curr != $this->mb_encoding) { 
		mb_internal_encoding($this->mb_encoding); 
	}
}

function getMBencoding() {
	return $this->mb_encoding;
}



function SetMargins($left,$right,$top)
{
	//Set left, top and right margins
	$this->lMargin=$left;
	$this->rMargin=$right;
	$this->tMargin=$top;
}

function ResetMargins()
{

	//ReSet left, top margins
	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
		$this->lMargin=$this->DefrMargin;
		$this->rMargin=$this->DeflMargin;
		$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;

	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS
		$this->lMargin=$this->DeflMargin;
		$this->rMargin=$this->DefrMargin;
		if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
	}
	$this->x=$this->lMargin;

}

function SetLeftMargin($margin)
{
	//Set left margin
	$this->lMargin=$margin;
	if($this->page>0 and $this->x<$margin) $this->x=$margin;
}

function SetTopMargin($margin)
{
	//Set top margin
	$this->tMargin=$margin;
}

function SetRightMargin($margin)
{
	//Set right margin
	$this->rMargin=$margin;
}

function SetAutoPageBreak($auto,$margin=0)
{
	//Set auto page break mode and triggering margin
	$this->AutoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function SetDisplayMode($zoom,$layout='continuous')
{
	//Set display mode in viewer
	if($zoom=='fullpage' or $zoom=='fullwidth' or $zoom=='real' or $zoom=='default' or !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' or $layout=='continuous' or $layout=='two' or $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	//Set page compression
	if(function_exists('gzcompress'))	$this->compress=$compress;
	else $this->compress=false;
}

function SetTitle($title)
{
	//Title of document // Arrives as UTF-8
	$this->title = $title;
	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->title); }
}

function SetSubject($subject)
{
	//Subject of document
	$this->subject= $subject;
	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->subject); }
}

function SetAuthor($author)
{
	//Author of document
	$this->author= $author;
	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->author); }
}

function SetKeywords($keywords)
{
	//Keywords of document
	$this->keywords= $keywords;
	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->keywords); }
}

function SetCreator($creator)
{
	//Creator of document
	$this->creator= $creator;
	if ($this->directionality == 'rtl') { $this->magic_reverse_dir($this->creator); }
}


function setAnchor2Bookmark($x) {
	$this->Anchor2Bookmark = $x;
}

function AliasNbPages($alias='{nb}')
{
	//Define an alias for total number of pages
	$this->AliasNbPages=$alias;
}

function SetAlpha($alpha, $bm='Normal') {
// alpha: real value from 0 (transparent) to 1 (opaque)
// bm:    blend mode, one of the following:
//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
// set alpha for stroking (CA) and non-stroking (ca) operations
	$gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
	$this->SetExtGState($gs);
}

function AddExtGState($parms) {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
}

function SetExtGState($gs) {
	$this->_out(sprintf('/GS%d gs', $gs));
}


function Error($msg)
{
	//Fatal error
	header('Content-Type: text/html; charset=utf-8');
	die('<B>mPDF error: </B>'.$msg);
}

function Open()
{
	//Begin document
	if($this->state==0)	$this->_begindoc();
}

function Close()
{
	//Terminate document
	if($this->state==3)	return;
	if($this->page==0) $this->AddPage();
	if (count($this->columnbuffer)) { $this->ColActive = 0; $this->printcolumnbuffer(); }
	if (count($this->tablebuffer)) { $this->printtablebuffer(); }
	// Edited mPDF 1.1 keeping block together on one page
	if (count($this->divbuffer)) { $this->printdivbuffer(); }

	if (!$this->TOCmark) { //Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
	}

	// Added mPDF 1.2 HTML headers and Footers
	if ($this->HTMLHeader) { $this->writeHTMLHeaders(); }
	if ($this->HTMLFooter) { $this->writeHTMLFooters(); }

	// Moved to after writeHTMLHeaders etc.
	if ($this->TOCmark) { $this->insertTOC(); }


	//Close page
	$this->_endpage();

	//Close document
	$this->_enddoc();
}


// Added in mPDF1.3
function AddPages($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='')
{
	if ($condition == 'NEXT-EVEN') {	// always adds at least one new page to create an Even page
	   if (!$this->useOddEven) { $this->AddPage(''); }
	   else { 
		$this->AddPage('','O'); 
		$this->AddPage('', '', $resetpagenum, $pagenumstyle, $suppress); 
	   }
	}
	if ($condition == 'NEXT-ODD') {	// always adds at least one new page to create an Odd page
	   if (!$this->useOddEven) { $this->AddPage(''); }
	   else { 
		$this->AddPage('','E'); 
		$this->AddPage('', '', $resetpagenum, $pagenumstyle, $suppress); 
	   }
	}
}

// Edited in mPDF1.3
// New parameters - AddPage('',$type="E|O", $resetpagenum="1|0", $pagenumstyle="I|i|A|a|1", $suppress="on|off")
function AddPage($orientation='',$condition='', $resetpagenum='', $pagenumstyle='', $suppress='')
{
	//Start a new page
	if($this->state==0) $this->Open();

	if ($condition == 'E') {	// only adds new page if needed to create an Even page
	   if (!$this->useOddEven || ($this->page)%2==0) { return false; }
	}
	if ($condition == 'O') {	// only adds new page if needed to create an Odd page
	   if (!$this->useOddEven || ($this->page)%2==1) { return false; }
	}

	$this->PageNumSubstitutions[] = array('from'=>($this->page+1), 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);

	// Paint Div Border if necessary
   	//PAINTS BACKGROUND COLOUR OR BORDERS for DIV - DISABLED FOR COLUMNS (cf. AcceptPageBreak) AT PRESENT in ->PaintDivBorder
   	if (!$this->ColActive && $this->blklvl > 0) {
	   if ($this->y == $this->blk[$this->blklvl]['y0']) {  $this->blk[$this->blklvl]['startpage']++; }
	   if (($this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] ) {
		$sy = $this->y;
		for ($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->PaintDivBorder('pagebottom',0,$bl);
		}
		$this->y = $sy;
		// RESET block y0 and x0 - see below
	   }
	}

	$family=$this->FontFamily;
	$style=$this->FontStyle.($this->underline ? 'U' : '');
	$size=$this->FontSizePt;
	$this->ColumnAdjust = true;	// enables column height adjustment for the page
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Page footer
		$this->InFooter=true;
		$this->Footer();
		//Close page
		$this->_endpage();
	}
	//Start new page
	$this->_beginpage($orientation);
	//Set line cap style to square
	$this->_out('2 J');
	//Set line width
	$this->LineWidth=$lw;
	$this->_out(sprintf('%.2f w',$lw*$this->k));
	//Set font
	if($family)	$this->SetFont($family,$style,$size);
	//Set colors
	$this->DrawColor=$dc;
	if($dc!='0 G') $this->_out($dc);
	$this->FillColor=$fc;
	if($fc!='0 g') $this->_out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
	//Page header
	$this->Header();
	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.2f w',$lw*$this->k));
	}
	//Restore font
	if($family)	$this->SetFont($family,$style,$size);
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->_out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
 	$this->InFooter=false;

	if ($this->ColActive) { $this->SetCol(0); }

   	//RESET BLOCK BORDER TOP
   	if (!$this->ColActive) {
		for($bl=1;$bl<=$this->blklvl;$bl++) {
			$this->blk[$bl]['y0'] = $this->y;
			$this->blk[$bl]['x0'] += $this->MarginCorrection;
		}
	}
}


function PageNo()
{
	//Get current page number
	return $this->page;
}

function SetDrawColor($r,$g=-1,$b=-1)
{
	//Set color for all stroking operations
	if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->DrawColor=sprintf('%.3f G',$r/255);
	else $this->DrawColor=sprintf('%.3f %.3f %.3f RG',$r/255,$g/255,$b/255);
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor || $this->keep_block_together)) { $this->_out($this->DrawColor); }
	$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
}

function SetFillColor($r,$g=-1,$b=-1)
{
	//Set color for all filling operations
	if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->FillColor=sprintf('%.3f g',$r/255);
	else $this->FillColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor != $this->TextColor);
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['FillColor'] != $this->FillColor || $this->keep_block_together)) { $this->_out($this->FillColor); }
	$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
}

function SetTextColor($r,$g=-1,$b=-1)
{
	//Set color for text
	if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->TextColor=sprintf('%.3f g',$r/255);
	else $this->TextColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor != $this->TextColor);
}

function GetStringWidth($s)
{
			//Get width of a string in the current font
			$s = (string)$s;
			$cw = &$this->CurrentFont['cw'];
			$w = 0;
			if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
				$unicode = $this->UTF8StringToArray($s);
				foreach($unicode as $char) {
					if (isset($cw[$char])) {
						$w+=$cw[$char];
					} elseif(isset($cw[$this->ords[$char]])) {
						$w+=$cw[$this->ords[$char]];
					} elseif(isset($cw[$this->chrs[$char]])) {
						$w+=$cw[$this->chrs[$char]];
					} elseif(isset($this->CurrentFont['desc']['MissingWidth'])) {
						$w += $this->CurrentFont['desc']['MissingWidth']; // set default size
					} else {
						$w += 500;
					}
				}
			} 
			// from class PDF_Chinese CJK EXTENSIONS
			else if (($this->isCJK) && ($this->CurrentFont['type']=='Type0')) {
				//Multi-byte version of GetStringWidth() used for GB/CJK Chinese
				$l=0;
				$nb=strlen($s);
				$i=0;

				if ($this->FontFamily == 'sjis') {	// SHIFT_JIS
				   while($i<$nb)
				   {
					$o=$this->ords[$s{$i}];
					if ($o<128) {
						//ASCII
						$l+=$cw[$s{$i}];
						$i++;
					}
					else if ($o>=161 and $o<=223) {
						//Half-width katakana
						$l+=500;
						$i++;
					}
					else {
						//Full-width character
						$l+=1000;
						$i+=2;
					}
				   }
				}

				else {
				   while($i<$nb) {
					$c=$s[$i];
					if ($this->ords[$c]<128) {
						$l+=$cw[$c];
						$i++;
					}
					else {
					   $l+=1000;
					   $i+=2;
					}
				   }
				}
				return $l*$this->FontSize/1000;
			}
			else {
				$l = strlen($s);
				for($i=0; $i<$l; $i++) {
					if (isset($cw[$s{$i}])) {
						$w += $cw[$s{$i}];
					} else if (isset($cw[$this->ords[$s{$i}]])) {
						$w += $cw[$this->ords[$s{$i}]];
					}
				}
			}
			return ($w * $this->FontSize/ 1000);
}

function SetLineWidth($width)
{
	//Set line width
	$this->LineWidth=$width;
	$lwout = (sprintf('%.2f w',$width*$this->k));
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['LineWidth'] != $lwout || $this->keep_block_together)) {
		 $this->_out($lwout); 
	}
	$this->pageoutput[$this->page]['LineWidth'] = $lwout;
}

function Line($x1,$y1,$x2,$y2)
{
	//Draw a line
	$this->_out(sprintf('%.2f %.2f m %.2f %.2f l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Arrow($x1,$y1,$x2,$y2,$headsize=3,$fill='B',$angle=25)
{
  //F == fill //S == stroke //B == stroke and fill 
  // angle = splay of arrowhead - 1 - 89 degrees
  $s = '';
  $s.=sprintf('%.3f %.3f m %.3f %.3f l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k);
  $this->_out($s);

  $a = atan2(($y2-$y1),($x2-$x1));
  $b = $a + deg2rad($angle);
  $c = $a - deg2rad($angle);
  $x3 = $x2 - ($headsize* cos($b));
  $y3 = $this->h-($y2 - ($headsize* sin($b)));
  $x4 = $x2 - ($headsize* cos($c));
  $y4 = $this->h-($y2 - ($headsize* sin($c)));

  $s = '';
  $s.=sprintf('%.3f %.3f m %.3f %.3f l %.3f %.3f l %.3f %.3f l ',$x2*$this->k,($this->h-$y2)*$this->k,$x3*$this->k,$y3*$this->k,$x4*$this->k,$y4*$this->k,$x2*$this->k,($this->h-$y2)*$this->k);
  $s.=$fill;
  $this->_out($s);
}


function Rect($x,$y,$w,$h,$style='')
{
	//Draw a rectangle
	if($style=='F')	$op='f';
	elseif($style=='FD' or $style=='DF') $op='B';
	else $op='S';
	$this->_out(sprintf('%.2f %.2f %.2f %.2f re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddFont($family,$style='',$file='')
{

	if ($this->isCJK && $this->use_CJK_only) { return; }
	if(empty($family)) { return; }
	//Add a TrueType or Type1 font
	$family = strtolower($family);

	$style=strtoupper($style);
	$style=str_replace('U','',$style);
	if($style=='IB') $style='BI';
	$fontkey = $family.$style;
	// check if the font has been already added
	if(isset($this->fonts[$fontkey])) {
		return;
	}

	if (($this->isunicode) && (!$this->usingembeddedfonts)) {
			if($file=='') {
				$file = str_replace(' ', '', $family).strtolower($style).'.php';
			}
			if(!file_exists($this->_getfontpath().$file)) {
				// try to load the basic file without styles
				$file = str_replace(' ', '', $family).'.php';
			}
			include($this->_getfontpath().$file);

			if(!isset($name) AND !isset($fpdf_charwidths)) {
				$this->Error('Could not include font definition file');
			}

			$i = count($this->fonts)+1;

			$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'enc'=>$enc, 'file'=>$file, 'ctg'=>$ctg);
			$fpdf_charwidths[$fontkey] = $cw;

			if(isset($diff) AND (!empty($diff))) {
				//Search existing encodings
				$d=0;
				$nb=count($this->diffs);
				for($i=1;$i<=$nb;$i++) {
					if($this->diffs[$i]==$diff) {
						$d=$i;
						break;
					}
				}
				if($d==0) {
					$d=$nb+1;
					$this->diffs[$d]=$diff;
				}
				$this->fonts[$fontkey]['diff']=$d;
			}
			if(!empty($file)) {
				if((strcasecmp($type,"TrueType") == 0) OR (strcasecmp($type,"TrueTypeUnicode") == 0)) {
					$this->FontFiles[$file]=array('length1'=>$originalsize);
				}
				else {
					$this->FontFiles[$file]=array('length1'=>$size1,'length2'=>$size2);
				}
			}
	}
	else { 	// if not unicode (or embedded)
		if($file=='') {
			$file=str_replace(' ','',$family).strtolower($style);

			if ($this->isunicode) {
				$file=$file.'.php';
			}
			else if ($this->codepage != 'win-1252') {
				$file=$file.'-'.$this->codepage.'.php';
			}
			else {	// is there any other?
				$file=$file.'.php';
			}


		}
		if(defined('FPDF_FONTPATH')) { $file=FPDF_FONTPATH.$file; }
		include($file);
		if(!isset($name))	$this->Error('Could not include font definition file - '.$family.' '.$style);
		$i=count($this->fonts)+1;
		$this->fonts[$family.$style]=array('i'=>$i,'type'=>$type,'name'=>$name,'desc'=>$desc,'up'=>$up,'ut'=>$ut,'cw'=>$cw,'enc'=>$enc,'file'=>$file);
		if($diff)
		{
			//Search existing encodings
			$d=0;
			$nb=count($this->diffs);
			for($i=1;$i<=$nb;$i++)
				if($this->diffs[$i]==$diff)
				{
					$d=$i;
					break;
				}
			if($d==0)
			{
				$d=$nb+1;
				$this->diffs[$d]=$diff;
			}
			$this->fonts[$family.$style]['diff']=$d;
		}
		if($file)
		{
			if($type=='TrueType')	$this->FontFiles[$file]=array('length1'=>$originalsize);
			else $this->FontFiles[$file]=array('length1'=>$size1,'length2'=>$size2);
		}
		// ADDED fontlist is defined in html2fpdf
		if (isset($this->fontlist)) { $this->fontlist[] = strtolower($family); }
	}
}



function SetFont($family,$style='',$size=0, $write=true, $forcewrite=false)
{
	$family=strtolower($family);
	// save previous values
	$this->prevFontFamily = $this->FontFamily;
	$this->prevFontStyle = $this->FontStyle;
	//Select a font; size given in points
	global $fpdf_charwidths;

	if($family=='') { 
		if ($this->FontFamily) { $family=$this->FontFamily; }
		else if ($this->default_font) { $family=$this->default_font; }
		else { die("ERROR - No font or default font set!"); }
	}


	if (($family == 'symbol') || ($family == 'zapfdingbats')  || ($family == 'times')  || ($family == 'courier') || ($family == 'helvetica')) { $this->usingembeddedfonts = true; }
	else {  $this->usingembeddedfonts = false; }

	if($family=='symbol' or $family=='zapfdingbats') { $style=''; }
	$style=strtoupper($style);
	if(is_int(strpos($style,'U'))) {
		$this->underline=true;
		$style=str_replace('U','',$style);
	}
	else { $this->underline=false; }
	if ($style=='IB') $style='BI';
	if ($size==0) $size=$this->FontSizePt;


	$fontkey=$family.$style;

	if (($this->isunicode || $this->isCJK) && (!$this->usingembeddedfonts)) {
		// CJK fonts
		if (in_array($fontkey,$this->available_CJK_fonts)) {
			if(!isset($this->fonts[$fontkey])) {	// already added
				if (empty($this->Big5_widths)) { require(_MPDF_PATH . 'CJKdata.php'); }
				$this->AddCJKFont($family);	// don't need to add style
			}
			$this->isCJK = true;
			// RESET MB-ENCODING
			if ($family == 'big5') { $this->setMBencoding('BIG5');}
			else if ($family == 'gb') { $this->setMBencoding('GBK'); }
			else if ($family == 'sjis') { $this->setMBencoding('SHIFT_JIS'); }
			else if ($family == 'uhc') { $this->setMBencoding('UHC');}
		}
		else if ($this->use_CJK_only) {
			$family = $this->default_font;
			$this->isCJK = true;
			// RESET MB-ENCODING
			if ($family == 'big5') { $this->setMBencoding('BIG5');}
			else if ($family == 'gb') { $this->setMBencoding('GBK'); }
			else if ($family == 'sjis') { $this->setMBencoding('SHIFT_JIS'); }
			else if ($family == 'uhc') { $this->setMBencoding('UHC');}
		}
		// Test to see if requested font/style is available - or substitute
		else if (!in_array($fontkey,$this->available_unifonts)) {
			// If font[nostyle] exists - set it
			if (in_array($family,$this->available_unifonts)) {
				$style = '';
			}

			// Else if only one font available - set it (assumes if only one font available it will not have a style)
			else if (count($this->available_unifonts) == 1) {
				$family = $this->available_unifonts[0];
				$style = '';
			}

			else {
				$found = 0;
				// else substitute font of similar type
				if (in_array($family,$this->sans_fonts)) { 
					$i = array_intersect($this->sans_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->serif_fonts)) { 
					$i = array_intersect($this->serif_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->mono_fonts)) {
					$i = array_intersect($this->mono_fonts,$this->available_unifonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_unifonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}

				if (!$found) {
					// set first available font
					$fs = $this->available_unifonts[0];
					preg_match('/^([a-z_]+)([BI]{0,2})$/',$fs,$fas);
					// with requested style if possible
					$ws = $fas[1].$style;
					if (in_array($ws,$this->available_unifonts)) {
						$family = $fas[1]; // leave $style as is
					}
					else if (in_array($fas[1],$this->available_unifonts)) {
					// or without style
						$family = $fas[1];
						$style = '';
					}
					else {
					// or with the style specified 
						$family = $fas[1];
						$style = $fas[2];
					}
				}
			}

			$this->isCJK = false;
			$this->setMBencoding('UTF-8');

			$fontkey = $family.$style; 
		}
		else {
			$this->isCJK = false;
			$this->setMBencoding('UTF-8');
		}

		// try to add font (if not already added)
		$this->AddFont($family, $style);

		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}

		// mPDF 1.1 added line
		$fontkey = $family.$style; 

		//Select it
		$this->FontFamily = $family;
		$this->FontStyle = $style;
		$this->FontSizePt = $size;
		$this->FontSize = $size / $this->k;
		$this->CurrentFont = &$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}



		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->underline ? 'U' : '');
	}

	else { 	// if not unicode/CJK - or core embedded font
		$this->isCJK = false;
		$this->setMBencoding($this->codepage);

		// Edit mPDF 1.1 - brought forward to increase efficiency
		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}

		// ALWAYS SUBSTITUTE ARIAL TIMES COURIER IN 1252
		if (!isset($this->CoreFonts[$fontkey]) && ($this->use_embeddedfonts_1252) && ($this->codepage == 'win-1252')) {
			if (in_array($family,$this->serif_fonts)) { $family = 'times'; }
			else if (in_array($family,$this->mono_fonts)) { $family = 'courier'; }
			else { $family = 'helvetica'; }
			$this->usingembeddedfonts = true;
			$fontkey = $family.$style; 
		}

		// Test to see if requested font/style is available - or substitute
		if (!in_array($fontkey,$this->available_fonts) && (!$this->usingembeddedfonts) ) {

			// If font[nostyle] exists - set it
			if (in_array($family,$this->available_fonts)) {
				$style = '';
			}

			// Else if only one font available - set it (assumes if only one font available it will not have a style)
			else if (count($this->available_fonts) == 1) {
				$family = $this->available_fonts[0];
				$style = '';
			}

			else {
				$found = 0;
				// else substitute font of similar type
				if (in_array($family,$this->sans_fonts)) { 
					$i = array_intersect($this->sans_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->serif_fonts)) { 
					$i = array_intersect($this->serif_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}
				else if (in_array($family,$this->mono_fonts)) {
					$i = array_intersect($this->mono_fonts,$this->available_fonts);
					if (count($i)) {
						$i = array_values($i);
						// with requested style if possible
						if (!in_array(($i[0].$style),$this->available_fonts)) {
							$style = '';
						}
						$family = $i[0]; 
						$found = 1;
					}
				}

				if (!$found) {
					// set first available font
					$fs = $this->available_unifonts[0];
					preg_match('/^([a-z_]+)([BI]{0,2})$/',$fs,$fas);
					// with requested style if possible
					$ws = $fas[1].$style;
					if (in_array($ws,$this->available_fonts)) {
						$family = $fas[1]; // leave $style as is
					}
					else if (in_array($fas[1],$this->available_fonts)) {
					// or without style
						$family = $fas[1];
						$style = '';
					}
					else {
					// or with the style specified 
						$family = $fas[1];
						$style = $fas[2];
					}
				}
			}
			$fontkey = $family.$style; 
		}

		if(!isset($this->fonts[$fontkey])) 	{
			// STANDARD CORE FONTS
			if (isset($this->CoreFonts[$fontkey])) {
				if(!isset($fpdf_charwidths[$fontkey])) {
					//Load metric file
					$file=$family;
					if($family=='times' or $family=='helvetica') { $file.=strtolower($style); }
					$file.='.php';
					if(defined('FPDF_FONTPATH')) $file=FPDF_FONTPATH.$file;
					include($file);
					if(!isset($fpdf_charwidths[$fontkey])) $this->Error('Could not include font metric file');
				}

				$i=count($this->fonts)+1;
				$this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->CoreFonts[$fontkey],'up'=>-100,'ut'=>50,'cw'=>$fpdf_charwidths[$fontkey]);
			}
			else {
				// try to add font 
				$this->AddFont($family, $style);
			}
		}
		//Test if font is already selected
		if(($this->FontFamily == $family) AND ($this->FontStyle == $style) AND ($this->FontSizePt == $size) && !$forcewrite) {
			return $family;
		}
		//Select it
		$this->FontFamily=$family;
		$this->FontStyle=$style;
		$this->FontSizePt=$size;
		$this->FontSize=$size/$this->k;
		$this->CurrentFont=&$this->fonts[$fontkey];
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
		// Added - currentfont (lowercase) used in HTML2PDF
		$this->currentfontfamily=$family;
		$this->currentfontsize=$size;
		$this->currentfontstyle=$style.($this->underline ? 'U' : '');

	}
	return $family;
}

function SetFontSize($size,$write=true)
{
	//Set font size in points
	if($this->FontSizePt==$size) return;
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	$this->currentfontsize=$size;
		if ($write) { 
			$fontout = (sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 1.1 keeping block together on one page
			if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
}

function AddLink()
{
	//Create a new internal link
	$n=count($this->links)+1;
	$this->links[$n]=array(0,0);
	return $n;
}

function SetLink($link,$y=0,$page=-1)
{
	//Set destination of internal link
	if($y==-1) $y=$this->y;
	if($page==-1)	$page=$this->page;
	$this->links[$link]=array($page,$y);
}

function Link($x,$y,$w,$h,$link)
{
	// Edited mPDF 1.1 keeping block together on one page
	if ($this->keep_block_together) {	// Save to array - don't write yet
		$this->ktLinks[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
		return;
	}
	//Put a link on the page
	$this->PageLinks[$this->page][]=array($x*$this->k,$this->hPt-$y*$this->k,$w*$this->k,$h*$this->k,$link);
	// Save cross-reference to Column buffer
	$ref = count($this->PageLinks[$this->page])-1;
	$this->columnLinks[$this->CurrCol][INTVAL($this->x)][INTVAL($this->y)] = $ref;

}

function WriteText($x,$y,$txt)
{
	// Output a string using Text() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->isunicode) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($txt);
	$this->Text($x,$y,$txt);
}

function Text($x,$y,$txt)
{
	// Output a string
	// Called (only) by Watermark
	// Expects input to be mb_encoded if necessary and RTL reversed
	// NON_BREAKING SPACE
	if ($this->isunicode && !$this->isCJK && !$this->usingembeddedfonts) {
	      $txt2 = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$txt); 
		if (!$this->usingembeddedfonts) {
			//Convert string to UTF-16BE without BOM
			$txt2= $this->UTF8ToUTF16BE($txt2, false);
		}
	}
	else {
	      $txt2 = str_replace($this->chrs[160],$this->chrs[32],$txt);
	}
	$s=sprintf('BT %.2f %.2f Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt2));
	if($this->underline and $txt!='') {
		$s.=' '.$this->_dounderline($x,$y + (0.1* $this->FontSize),$txt);
	}
	if($this->ColorFlag) $s='q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function WriteCell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0) //EDITEI
{
	//Output a cell using Cell() but does encoding and text reversing of RTL
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->isunicode) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($txt);
	$this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link, $currentx);
}


// WORD SPACING
function GetJspacing($nc,$ns,$w) {
	$ws = 0; 
	$charspacing = 0;
	$ww = $this->jSWord;
	if ($nc == 0 && $ns == 0) { return array(0,0); }
	if ($this->jSpacing == 'C') {
		if ($nc) { $charspacing = $w / $nc; }
	}
	else if ($this->jSpacing == 'W') {
		if ($ns) { $ws = $w / $ns; }
	}
	else if (!$ns) {
		if ($nc) { $charspacing = $w / $nc; }
	}
	else if ($ns == $nc) {
		$charspacing = $w / $ns;
	}
	else {
		if ($nc) { 
		   if (($this->isunicode || $this->isCJK) && !$this->usingembeddedfonts) {
			$cs = ($w * (1 - $this->jSWord)) / ($nc-$ns);
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($nc-$ns))/$w);
			}
			$charspacing = $cs; 
			$ws = (($w * ($ww) ) / $ns) - $charspacing;
		   }
		   else {
			$cs = ($w * (1 - $this->jSWord)) / ($nc);
			if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
				$cs = $this->jSmaxChar;
				$ww = 1 - (($cs * ($nc))/$w);
			}
			$charspacing = $cs; 
			$ws = ($w * ($ww) ) / $ns;
		   }
		}
	}
	return array($charspacing,$ws); 
}


function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='', $currentx=0, $lcpaddingL=0, $lcpaddingR=0, $valign='M') //EDITEI
{
	//Output a cell
	// Expects input to be mb_encoded if necessary and RTL reversed
	// NON_BREAKING SPACE
	if ($this->isunicode) {
	      $txt = str_replace($this->chrs[194].$this->chrs[160],$this->chrs[32],$txt); 
	}
	else {
	      $txt = str_replace($this->chrs[160],$this->chrs[32],$txt);
	}

	$k=$this->k;

	$oldcolumn = $this->CurrCol;
	// Automatic page break
	// Allows PAGE-BREAK-AFTER = avoid to work

	if ((($this->y+$this->divheight>$this->PageBreakTrigger) || ($this->y+$h>$this->PageBreakTrigger) || 
		($this->y+($h*2)>$this->PageBreakTrigger && $this->blk[$this->blklvl]['page_break_after_avoid'])) and !$this->InFooter and $this->AcceptPageBreak()) {

		$x=$this->x;//Current X position

		// WORD SPACING
		$ws=$this->ws;//Word Spacing
		if($ws>0) {
			$this->ws=0;
			$this->_out('BT 0 Tw ET'); 
		}
		$charspacing=$this->charspacing;//Character Spacing
		if($charspacing>0) {
			$this->charspacing=0;
			$this->_out('BT 0 Tc ET'); 
		}

		$this->AddPage($this->CurOrientation);
		// Added to correct for OddEven Margins
		$x=$x +$this->MarginCorrection;
		if ($currentx) { 
			$currentx += $this->MarginCorrection;
		} 
		$this->x=$x;
		// WORD SPACING
		if($ws>0) {
			$this->ws=$ws;
			$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
		}
		if($charspacing>0) {
			$this->charspacing=$charspacing;
			$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
		}
	}

	// COLS
	// COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		if ($currentx) { 
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		} 
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	}

	// COLUMNS Update/overwrite the lowest bottom of printing y value for a column
	if ($this->ColActive) {
		if ($h) { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h; }
		else { $this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$this->divheight; }
	}

	// Edited mPDF 1.1 keeping block together on one page
	// KEEP BLOCK TOGETHER Update/overwrite the lowest bottom of printing y value on first page
	if ($this->keep_block_together) {
		if ($h) { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h; }
//		else { $this->ktBlock[$this->page]['bottom_margin'] = $this->y+$this->divheight; }
	}

	if($w==0) $w = $this->w-$this->rMargin-$this->x;
	$s='';

	if($fill==1 && $this->FillColor) { 
		// Edited mPDF 1.1 keeping block together on one page
		if($this->pageoutput[$this->page]['FillColor'] != $this->FillColor || $this->keep_block_together) { $s .= $this->FillColor.' '; }
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}
//$fill=1;//DEBUG
	if($fill==1 or $border==1)
	{
		if ($fill==1) $op=($border==1) ? 'B' : 'f';
		else $op='S';
//$op='S'; $this->SetLineWidth(0.02); $this->SetDrawColor(0);//DEBUG

		$s.=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}

	if(is_string($border))
	{
		$x=$this->x;
		$y=$this->y;
		if(is_int(strpos($border,'L')))
			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(is_int(strpos($border,'T')))
			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(is_int(strpos($border,'R')))
			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(is_int(strpos($border,'B')))
			$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}

	if($txt!='')
	{

		$stringWidth = $this->GetStringWidth($txt) + ( $this->charspacing * mb_strlen( $txt, $this->mb_encoding ) / $k )
				 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_encoding ) / $k );

		// Set x OFFSET FOR PRINTING
		if($align=='R') {
			$dx=$w-$this->cMarginR - $stringWidth - $lcpaddingR;
		}
		elseif($align=='C') {
			$dx=(($w - $stringWidth )/2);
		}
		elseif($align=='L' or $align=='J') $dx=$this->cMarginL + $lcpaddingL;
    		else $dx = 0;

		if($this->ColorFlag) $s.='q '.$this->TextColor.' ';

		// OUTLINE
		if($this->outline_on)
		{
			$s.=' '.sprintf('%.2f w',$this->LineWidth*$k).' ';
			$s.=" $this->DrawColor ";
			$s.=" 2 Tr ";
    		}


		// FONT SIZE - this determines the baseline caculation
		if ($this->linemaxfontsize && !$this->processingHeader) { $bfs = $this->linemaxfontsize; }
		else  { $bfs = $this->FontSize; }

    		//Calculate baseline Superscript and Subscript Y coordinate adjustment
		$bfx = 0.35;
    		$baseline = $bfx*$bfs;
		if($this->SUP) { $baseline += ($bfx-1.05)*$this->FontSize; }
		else if($this->SUB) { $baseline += ($bfx + 0.04)*$this->FontSize; }
		else if($this->bullet) { $baseline += ($bfx-0.7)*$this->FontSize; }

		// Vertical align (for Images)
		if ($this->lineheight_correction) { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle - default
		}
		else { 
			if ($valign == 'T') { $va = (0.5 * $bfs * $this->default_lineheight_correction); }
			else if ($valign == 'B') { $va = $h-(0.5 * $bfs * $this->default_lineheight_correction); }
			else { $va = 0.5*$h; }	// Middle - default
		}
		// THE TEXT
		// WORD SPACING
		// IF multibyte - Tw has no effect - need to do word spacing by setting character spacing for spaces between words
		if (($this->ws) && (($this->isunicode) || ($this->isCJK))) {
		  $space = ' ';
		  if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
			//Convert string to UTF-16BE without BOM
			$space= $this->UTF8ToUTF16BE($space , false);
		  }
		  $space=$this->_escape($space ); 

		  $s.=sprintf('BT %.2f %.2f Td',($this->x+$dx)*$k,($this->h-($this->y+$baseline+$va))*$k);
		  $t = preg_split('/[ ]/u',$txt);
		  for($i=0;$i<count($t);$i++) {
			$tx = $t[$i]; 
		  	if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
				//Convert string to UTF-16BE without BOM
				$tx = $this->UTF8ToUTF16BE($tx , false);
			}

			$tx = $this->_escape($tx); 

			$s.=sprintf(' %.3f Tc (%s) Tj',$this->charspacing,$tx);
			if (($i+1)<count($t)) {
				$s.=sprintf(' %.3f Tc (%s) Tj',$this->ws+$this->charspacing,$space);
			}
		  }
		  $s.=' ET';
		}
		else {
		  $txt2= $txt;
		  if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
			//Convert string to UTF-16BE without BOM
			$txt2= $this->UTF8ToUTF16BE($txt2, false);
		  }
		  $txt2=$this->_escape($txt2); 
		  $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+$baseline+$va))*$k,$txt2);
		}

		// UNDERLINE
		if($this->underline) {
			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$va+ (0.1* $this->FontSize),$txt);
		}

   		// STRIKETHROUGH
		if($this->strike) {
    			//Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
			$ch=$this->CurrentFont['desc']['CapHeight'];
			if (!$ch) {
				if ($this->FontFamily == 'helvetica') { $ch = 716; }
				else if ($this->FontFamily == 'times') { $ch = 662; }
				else if ($this->FontFamily == 'courier') { $ch = 571; }
				else { $ch = 700; }
			}
			$adjusty = (-$ch/1000* $this->FontSize) * 0.35;	

			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+$baseline+$adjusty+$va,$txt);
		}

		// COLOR
		if($this->ColorFlag) $s.=' Q';

		// LINK
		if($link!='') $this->Link($this->x+$dx,$this->y+$va-.5*$this->FontSize,$stringWidth,$this->FontSize,$link);
	}
	if($s) $this->_out($s);

	// WORD SPACING
	if (($this->ws) && (($this->isunicode) || ($this->isCJK))) {
		$this->_out(sprintf('BT %.3f Tc ET',$this->charspacing));//add-on 
	}

	$this->lasth=$h;
	if( strpos($txt,"\n") !== false) $ln=1; //EDITEI - cell now recognizes \n! << comes from <BR> tag
	if($ln>0)
	{
		//Go to next line
		$this->y += $h;
		if($ln==1) //EDITEI
		{
			//Move to next line
			if ($currentx != 0) { $this->x=$currentx; }	
			else { $this->x=$this->lMargin; }
   		}
	}
	else $this->x+=$w;


}




function MultiCell($w,$h,$txt,$border=0,$align='',$fill=0,$link='',$directionality='ltr',$encoded=false)
{
	if (!$encoded) {
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if (!$this->isunicode || $this->isCJK) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
	}


	// Parameter encoded - When called internally from ->Reference mb_encoding already done - but not reverse RTL
	if (!$align) { $align = $this->defaultAlign; }

	//Output text with automatic or explicit line breaks
	$cw=&$this->CurrentFont['cw'];
	if($w==0)	$w=$this->w-$this->rMargin-$this->x;

	if ($this->isunicode && !$this->isCJK) {
			$wmax = ($w - ($this->cMarginL+$this->cMarginR));
	}
	else {
			$wmax=($w- ($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
	}
	if ($this->isunicode && !$this->isCJK)  {
		$s=preg_replace("/\r/u",'',$txt);
		$nb=mb_strlen($s, $this->mb_encoding );
		while($nb>0 and mb_substr($s,$nb-1,1,$this->mb_encoding )=="\n")	$nb--;
	}
	else if ($this->isCJK)  {
		//$s=mb_ereg_replace("\r",'',$txt);	//????
		//$s=mb_ereg_replace("\n*$",'',$s);	//????
		$s=str_replace("\r",'',$txt);		// Edit mPDF 1.1
		$s=preg_replace("/\n*$/",'',$s);	// Edit mPDF 1.1

		$nb=strlen($s);
	}
	else {
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		while($nb>0 and $s[$nb-1]=="\n")	$nb--;
	}
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(is_int(strpos($border,'L')))	$b2.='L';
			if(is_int(strpos($border,'R')))	$b2.='R';
			$b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;

   // from class PDF_Chinese CJK EXTENSIONS
   if (($this->isCJK) && ($this->CurrentFont['type']=='Type0')) {
	if ($this->FontFamily == 'big5' || $this->FontFamily == 'gb' || $this->FontFamily == 'uhc') {	// BIG5 or GBK or UHC
 	   while($i<$nb)
	   {
		//Get next character
		$c=$s[$i];
		//Check if ASCII or MB
		$ascii=($this->ords[$c]<128);
		if($c=="\n")
		{
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0)
			{
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
			$this->Cell($w,$h,$tmp,$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2) { $b=$b2; }
			continue;
		}
		if(!$ascii)
		{
			$sep=$i;
			$ls=$l;
		}
		elseif($c==' ')
		{
			$sep=$i;
			$ls=$l;
		}
		$l+=$ascii ? $cw[$c] : 1000;
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
						$this->_out('BT 0 Tw ET'); 
			}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				if($i==$j) { $i+=$ascii ? 1 : 2; }
				$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
				$this->Cell($w,$h,$tmp,$b,2,$align,$fill);
			}
			else
			{
				$tmp = mb_rtrim(substr($s,$j,$sep-$j),$this->mb_encoding);
				if($align=='J') {
					//////////////////////////////////////////
					// JUSTIFY J (Use character spacing)
 					// WORD SPACING
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////

				}
				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2) { $b=$b2; }
		}
		else { $i+=$ascii ? 1 : 2; }
	   }
	}
	else if ($this->FontFamily == 'sjis') {	// SHIFT_JIS
	   while($i<$nb)
	   {
		//Get next character
		$c=$s{$i};
		$o=$this->ords[$c];
		if($o==10)
		{
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0)
			{
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
			$this->Cell($w,$h,$tmp,$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2)
				$b=$b2;
			continue;
		}
		if($o<128)
		{
			//ASCII
			$l+=$cw[$c];
			$n=1;
			if($o==32) { $sep=$i; }
		}
		elseif($o>=161 and $o<=223)
		{
			//Half-width katakana
			$l+=500;
			$n=1;
			$sep=$i;
		}
		else
		{
			//Full-width character
			$l+=1000;
			$n=2;
			$sep=$i;
		}
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				if($i==$j) { $i+=$n; }
				$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
				$this->Cell($w,$h,$tmp,$b,2,$align,$fill);
			}
			else
			{
				$tmp = mb_rtrim(substr($s,$j,$sep-$j),$this->mb_encoding);
				if($align=='J') {
					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING SHIFT_JIS
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////
				}
				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			if($border and $nl==2) { $b=$b2; }
		}
		else
		{
			$i+=$n;
			if($o>=128)
				$sep=$i;
		}
	   }
	}
	//Last chunk
	// WORD SPACING
	if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	}
	if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	}
   }

   else if ($this->codepage == 'UTF-8')  {
	while($i<$nb)
	{
		//Get next character
		$c = mb_substr($s,$i,1,$this->mb_encoding );
		if(preg_match("/[\n]/u", $c)) {
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0) {
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
			// DIRECTIONALITY
			$this->magic_reverse_dir($tmp);

			$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if(preg_match("/[ ]/u", $c)) {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l = $this->GetStringWidth(mb_substr($s, $j, $i-$j,$this->mb_encoding ));

		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1) {	// Only one word
				if($i==$j) $i++;
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp);

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
			}
			else {
				$tmp = mb_rtrim(mb_substr($s,$j,$sep-$j,$this->mb_encoding),'UTF-8');
				if($align=='J') {
					//$this->ws=($ns>1) ? ((($wmax-$ls)/($ns-1))) : 0;
					//$this->_out(sprintf('%.3f Tw',$this->ws*$this->k));

					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING UNICODE
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////
				}

				// DIRECTIONALITY
				$this->magic_reverse_dir($tmp);

				$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING
	if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	}
	if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	}

   }


   else {
	while($i<$nb)
	{
		//Get next character
		$c=$s{$i};
		if(preg_match("/[\n]/u", $c)) {
			//Explicit line break
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
			continue;
		}
		if(preg_match("/[ ]/u", $c)) {
			$sep=$i;
			$ls=$l;
			$ns++;
		}

		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($i==$j) $i++;
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
			}
			else
			{
				if($align=='J')
				{
					$tmp = rtrim(substr($s,$j,$sep-$j));
					//////////////////////////////////////////
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING NON_UNICDOE/CJK
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = strlen( $tmp ) ;  
					$nb_spaces = substr_count( $tmp ,' ' ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
					//////////////////////////////////////////
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border and $nl==2) $b=$b2;
		}
		else $i++;
	}
	//Last chunk
	// WORD SPACING
	if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	}
	if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	}

   }

	//Last chunk
   if($border and is_int(strpos($border,'B')))	$b.='B';
   if ($this->isunicode && !$this->isCJK)  {
		$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
		// DIRECTIONALITY
		$this->magic_reverse_dir($tmp);
   		$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
   }
   else if ($this->isCJK)  {
		$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
   		$this->Cell($w,$h,$tmp,$b,2,$align,$fill,$link);
   }
   else { $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link); }
   $this->x=$this->lMargin;
}




function Write($h,$txt,$currentx=0,$link='',$directionality='ltr',$align='') //EDITEI
{
	if (!$align) { $align = $this->defaultAlign; }	// NB Cannot use Align=J or C using Write??
	if ($h == 0) { $this->SetLineHeight(); $h = $this->lineheight; }
	//Output text in flowing mode
	$cw = &$this->CurrentFont['cw'];
	$w = $this->w - $this->rMargin - $this->x; 

	if ($this->isunicode && !$this->isCJK) {
			$wmax = ($w - ($this->cMarginL+$this->cMarginR));
	}
	else {
			$wmax=($w- ($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
	}

	if ($this->isunicode && !$this->isCJK)  {
		$s=preg_replace("/\r/u",'',$txt);	//????
		$nb=mb_strlen($s, $this->mb_encoding );
			// handle single space character
			if(($nb==1) AND preg_match("/[ ]/u", $s)) {
				$this->x += $this->GetStringWidth($s);
				return;
			}
	}
	else if ($this->isCJK)  {
		//$s=mb_ereg_replace("\r",'',$txt);	//????
		//$s=mb_ereg_replace("\n*$",'',$s);	//????
		$s=str_replace("\r",'',$txt);		// Edit mPDF 1.1
		$s=preg_replace("/\n*$/",'',$s);	// Edit mPDF 1.1
		$nb=strlen($s);
	}
	else {
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
	}


			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;

	// from class PDF_Chinese CJK EXTENSIONS
	if (($this->isCJK) && ($this->CurrentFont['type']=='Type0')) {
	//Multi-byte version of Write() for GB/CJK Chinese

	if ($this->FontFamily == 'big5' || $this->FontFamily == 'gb' || $this->FontFamily == 'uhc') {	// BIG5 or GBK or UHC
	   	while($i<$nb)
	   	{
		   //Get next character
		   $c=$s[$i];
		   //Check if ASCII or MB
		   $ascii=($this->ords[$c]<128);
		   if($c=="\n")
		   {
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0)
			{
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
			$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
			}
			$nl++;
			continue;
		   }
		   if(!$ascii or $c==' ')
			$sep=$i;
		   $l+=$ascii ? $cw[$c] : 1000;
		   if($l>$wmax)
		   {
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j) { $i+=$ascii ? 1 : 2; }
				$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
				$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
			}
			else
			{
				$tmp = mb_rtrim(substr($s,$j,$sep-$j),$this->mb_encoding);
				if($align=='J') {
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
				}
				$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;


			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
			}
			$nl++;
		   }
		   else
			$i+=$ascii ? 1 : 2;
	   	}

	    }

	    else if ($this->FontFamily == 'sjis') {	// SHIFT_JIS
	   	while($i<$nb)
	   	{
		   //Get next character
		   $c=$s{$i};
		   $o=$this->ords[$c];
		   if($o==10)
		   {
			//Explicit line break
			// WORD SPACING
			if($this->ws>0) {
				$this->ws=0;
				$this->_out('BT 0 Tw ET'); 
			}
			if($this->charspacing>0)
			{
				$this->charspacing=0;
				$this->_out('BT 0 Tc ET'); 
			}
			$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
			$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				//Go to left margin
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
			}
			$nl++;
			continue;
		   }
		   if($o<128)
		   {
			//ASCII
			$l+=$cw[$c];
			$n=1;
			if($o==32)
				$sep=$i;
		   }
		   elseif($o>=161 and $o<=223)
		   {
			//Half-width katakana
			$l+=500;
			$n=1;
			$sep=$i;
		   }
		   else
		   {
			//Full-width character
			$l+=1000;
			$n=2;
			$sep=$i;
		   }
		   if($l>$wmax)
		   {
			//Automatic line break
			if($sep==-1 or $i==$j)
			{
				// WORD SPACING
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				if($this->charspacing>0)
				{
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					$i+=$n;
					$nl++;
					continue;
				}
				if($i==$j) { $i+=$n; }
				$tmp = mb_rtrim(substr($s,$j,$i-$j),$this->mb_encoding);
				$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
			}
			else
			{
				$tmp = mb_rtrim(substr($s,$j,$sep-$j),$this->mb_encoding);
				if($align=='J') {
					// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
					// WORD SPACING
					$len_ligne = $this->GetStringWidth($tmp );
					$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
					$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
					list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
					if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
					else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
					$this->charspacing=$charspacing;
					if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
					else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
					$this->ws=$ws;
				}
				$this->Cell($w,$h,$tmp,0,2,$align,0,$link);
				$i=($s[$sep]==' ') ? $sep+1 : $sep;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
			}
			$nl++;
		   }
		   else
		   {
			$i+=$n;
			if($o>=128)
				$sep=$i;
		   }
	   	}
	    }
	    //Last chunk
	    // WORD SPACING
	    if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	    }
	    if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	    }


	}


	else if ($this->isunicode) {
			while($i<$nb) {
				//Get next character
				$c = mb_substr($s,$i,1,$this->mb_encoding );
				if(preg_match("/[\n]/u", $c)) {
					// WORD SPACING
					if($this->ws>0) {
						$this->ws=0;
						$this->_out('BT 0 Tw ET'); 
					}
					if($this->charspacing>0)
					{
						$this->charspacing=0;
						$this->_out('BT 0 Tc ET'); 
					}
					//Explicit line break
					$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
					if ($this->directionality == 'rtl') {
					   if ($align == 'J') { $align = 'R'; }
					}
					// DIRECTIONALITY
					$this->magic_reverse_dir($tmp);

					$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl == 1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w = $this->w - $this->rMargin - $this->x;
						$wmax = ($w - ($this->cMarginL+$this->cMarginR));
					}
					$nl++;
					continue;
				}
				if(preg_match("/[ ]/u", $c)) {
					$sep= $i;
				}

				$l = $this->GetStringWidth(mb_substr($s, $j, $i-$j,$this->mb_encoding));

				if($l > $wmax) {
					//Automatic line break (word wrapping)
					if($sep == -1) {
						// WORD SPACING
						if($this->ws>0) {
							$this->ws=0;
							$this->_out('BT 0 Tw ET'); 
						}
						if($this->charspacing>0)
						{
							$this->charspacing=0;
							$this->_out('BT 0 Tc ET'); 
						}
						if($this->x > $this->lMargin) {
							//Move to next line
							if ($currentx != 0) $this->x=$currentx;//EDITEI
							else $this->x=$this->lMargin;
							$this->y+=$h;
							$w=$this->w-$this->rMargin-$this->x;
							$wmax = ($w - ($this->cMarginL+$this->cMarginR));
							$i++;
							$nl++;
							continue;
						}
						if($i==$j) {
							$i++;
						}
						$tmp = mb_rtrim(mb_substr($s,$j,$i-$j,$this->mb_encoding),'UTF-8');
						if ($this->directionality == 'rtl') {
						   if ($align == 'J') { $align = 'R'; }
						}
						// DIRECTIONALITY
						$this->magic_reverse_dir($tmp);

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
					}
					else {
						$tmp = mb_rtrim(mb_substr($s,$j,$sep-$j,$this->mb_encoding),'UTF-8');
						if ($this->directionality == 'rtl') {
						   if ($align == 'J') { $align = 'R'; }
						}
						// DIRECTIONALITY
						$this->magic_reverse_dir($tmp);

						if($align=='J') {
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING
							$len_ligne = $this->GetStringWidth($tmp );
							$nb_carac = mb_strlen( $tmp , $this->mb_encoding ) ;  
							$nb_spaces = mb_substr_count( $tmp ,' ', $this->mb_encoding ) ;  
							list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
							if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
							else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
							$this->charspacing=$charspacing;
							if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
							else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
							$this->ws=$ws;
							//////////////////////////////////////////
						}

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
						$i=$sep+1;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl==1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w=$this->w-$this->rMargin-$this->x;
						$wmax = ($w - ($this->cMarginL+$this->cMarginR));
					}
					$nl++;
				}
				else {
					$i++;
				}
			}


	    //Last chunk
	    // WORD SPACING
	    if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	    }
	    if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	    }

	}


	else {
			while($i<$nb) {
				//Get next character
				$c=$s{$i};
				if(preg_match("/[\n]/u", $c)) {
					//Explicit line break
					// WORD SPACING
					if($this->ws>0) {
						$this->ws=0;
						$this->_out('BT 0 Tw ET'); 
					}
					if($this->charspacing>0)
					{
						$this->charspacing=0;
						$this->_out('BT 0 Tc ET'); 
					}
					$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl == 1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w = $this->w - $this->rMargin - $this->x;
						$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					}
					$nl++;
					continue;
				}
				if(preg_match("/[ ]/u", $c)) {
					$sep= $i;
				}

				$l += $cw[$c];

				if($l > $wmax) {
					//Automatic line break (word wrapping)
					if($sep == -1) {
						// WORD SPACING
						if($this->ws>0) {
							$this->ws=0;
							$this->_out('BT 0 Tw ET'); 
						}
						if($this->charspacing>0)
						{
							$this->charspacing=0;
							$this->_out('BT 0 Tc ET'); 
						}
						if($this->x > $this->lMargin) {
							//Move to next line
							if ($currentx != 0) $this->x=$currentx;//EDITEI
							else $this->x=$this->lMargin;
							$this->y+=$h;
							$w=$this->w-$this->rMargin-$this->x;
							$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
							$i++;
							$nl++;
							continue;
						}
						if($i==$j) {
							$i++;
						}
						$this->Cell($w, $h, substr($s, $j, $i-$j), 0, 2, $align, $fill, $link);
					}
					else {
						$tmp = substr($s, $j, $sep-$j);
						if($align=='J') {
							//////////////////////////////////////////
							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING
							$len_ligne = $this->GetStringWidth($tmp );
							$nb_carac = strlen( $tmp ) ;  
							$nb_spaces = substr_count( $tmp ,' ' ) ;  
							list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,((($w-2) - $len_ligne) * $this->k));
							if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
							else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
							$this->charspacing=$charspacing;
							if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
							else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
							$this->ws=$ws;
							//////////////////////////////////////////
						}

						$this->Cell($w, $h, $tmp, 0, 2, $align, $fill, $link);
						$i=$sep+1;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					if($nl==1) {
						if ($currentx != 0) $this->x=$currentx;//EDITEI
						else $this->x=$this->lMargin;
						$w=$this->w-$this->rMargin-$this->x;
						$wmax=($w-($this->cMarginL+$this->cMarginR))*1000/$this->FontSize;
					}
					$nl++;
				}
				else {
					$i++;
				}
			}

	    //Last chunk
	    // WORD SPACING
	    if($this->ws>0) {
		$this->ws=0;
		$this->_out('BT 0 Tw ET'); 
	    }
	    if ($this->charspacing>0) { 
		$this->charspacing=0;
		$this->_out('BT 0 Tc ET');
	    }
	}

	//Last chunk
	if($i!=$j) {
	  if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
		$tmp = mb_substr($s,$j,$i-$j,$this->mb_encoding);
		if ($this->directionality == 'rtl') {
		   if ($align == 'J') { $align = 'R'; }
		}
		// DIRECTIONALITY
		$this->magic_reverse_dir($tmp);

	  }
	  else {
		$tmp = substr($s,$j,$i-$j);	// Including CJK which has processed each byte (not multibyte)
	  }
   	  $this->Cell($this->GetStringWidth($tmp),$h,$tmp,0,0,'C',$fill,$link);
	}
}


function saveInlineProperties()
{
   $saved = array();
   $saved[ 'family' ] = $this->FontFamily;
   $saved[ 'style' ] = $this->FontStyle;
   $saved[ 'sizePt' ] = $this->FontSizePt;
   $saved[ 'size' ] = $this->FontSize;
   $saved[ 'HREF' ] = $this->HREF; 
   $saved[ 'underline' ] = $this->underline; 
   $saved[ 'strike' ] = $this->strike;
   $saved[ 'SUP' ] = $this->SUP; 
   $saved[ 'SUB' ] = $this->SUB; 
   $saved[ 'linewidth' ] = $this->LineWidth;
   $saved[ 'drawcolor' ] = $this->DrawColor;
   $saved[ 'is_outline' ] = $this->outline_on;
   $saved[ 'outlineparam' ] = $this->outlineparam;
   $saved[ 'toupper' ] = $this->toupper;
   $saved[ 'tolower' ] = $this->tolower;

   $saved[ 'I' ] = $this->I;
   $saved[ 'B' ] = $this->B;
   $saved[ 'colorarray' ] = $this->colorarray;
   $saved[ 'bgcolorarray' ] = $this->spanbgcolorarray;
   $saved[ 'color' ] = $this->TextColor; 
   $saved[ 'bgcolor' ] = $this->FillColor;

   return $saved;
}

function restoreInlineProperties( $saved)
{

   $this->FontFamily = $saved[ 'family' ];
   $this->FontStyle = $saved[ 'style' ];
   $this->FontSizePt = $saved[ 'sizePt' ];
   $this->FontSize = $saved[ 'size' ];

   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well

   $this->HREF = $saved[ 'HREF' ]; //EDITEI
   $this->underline = $saved[ 'underline' ]; //EDITEI
   $this->strike = $saved[ 'strike' ]; //EDITEI
   $this->SUP = $saved[ 'SUP' ]; //EDITEI
   $this->SUB = $saved[ 'SUB' ]; //EDITEI
   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI
   $this->outlineparam = $saved[ 'outlineparam' ];

   $this->toupper = $saved[ 'toupper' ];
   $this->tolower = $saved[ 'tolower' ];

   $this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ],false);

   $this->currentfontstyle = $saved[ 'style' ].($this->underline ? 'U' : '');
   $this->currentfontfamily = $saved[ 'family' ];
   $this->currentfontsize = $saved[ 'sizePt' ];
   $this->SetStyle('U',$this->underline);
   $this->SetStyle('B',$saved[ 'B' ]);
   $this->SetStyle('I',$saved[ 'I' ]);

   $this->TextColor = $saved[ 'color' ]; //EDITEI
   $this->FillColor = $saved[ 'bgcolor' ]; //EDITEI
   $this->colorarray = $saved[ 'colorarray' ];
   	$cor = $saved[ 'colorarray' ] ;
   	if ($cor) $this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
   $this->spanbgcolorarray = $saved[ 'bgcolorarray' ];
   	$cor = $saved[ 'bgcolorarray' ] ;
   	if ($cor) $this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
}




function GetFirstBlockFill() {
	// Returns the first blocklevel that uses a bgcolor fill
	$startfill = 0;
	for ($i=1;$i<=$this->blklvl;$i++) {
		if ($this->blk[$i]['bgcolor']) {
			$startfill = $i;
			break;
		}
	}
	return $startfill;
}

function SetBlockFill($blvl) {
	if ($this->blk[$blvl]['bgcolor']) {
		$this->SetFillColor($this->blk[$blvl]['bgcolorarray']['R'],$this->blk[$blvl]['bgcolorarray']['G'],$this->blk[$blvl]['bgcolorarray']['B']);
		return 1;
	}
	else {
		$this->SetFillColor(255);
		return 0;
	}
}


//-------------------------FLOWING BLOCK------------------------------------//
//EDITEI some things (added/changed)                                        //
//The following functions were originally written by Damon Kohler           //
//--------------------------------------------------------------------------//

function saveFont()
{
   $saved = array();
   $saved[ 'family' ] = $this->FontFamily;
   $saved[ 'style' ] = $this->FontStyle;
   $saved[ 'sizePt' ] = $this->FontSizePt;
   $saved[ 'size' ] = $this->FontSize;
   $saved[ 'curr' ] = &$this->CurrentFont;
   $saved[ 'color' ] = $this->TextColor; //EDITEI
   $saved[ 'spanbgcolor' ] = $this->spanbgcolor; //EDITEI
   $saved[ 'spanbgcolorarray' ] = $this->spanbgcolorarray; //EDITEI
   $saved[ 'HREF' ] = $this->HREF; //EDITEI
   $saved[ 'underline' ] = $this->underline; //EDITEI
   $saved[ 'strike' ] = $this->strike; //EDITEI
   $saved[ 'SUP' ] = $this->SUP; //EDITEI
   $saved[ 'SUB' ] = $this->SUB; //EDITEI
   $saved[ 'linewidth' ] = $this->LineWidth; //EDITEI
   $saved[ 'drawcolor' ] = $this->DrawColor; //EDITEI
   $saved[ 'is_outline' ] = $this->outline_on; //EDITEI
   $saved[ 'outlineparam' ] = $this->outlineparam;
   return $saved;
}

function restoreFont( $saved, $write=true)
{

   $this->FontFamily = $saved[ 'family' ];
   $this->FontStyle = $saved[ 'style' ];
   $this->FontSizePt = $saved[ 'sizePt' ];
   $this->FontSize = $saved[ 'size' ];
   $this->CurrentFont = &$saved[ 'curr' ];
   $this->TextColor = $saved[ 'color' ]; //EDITEI
   $this->spanbgcolor = $saved[ 'spanbgcolor' ]; //EDITEI
   $this->spanbgcolorarray = $saved[ 'spanbgcolorarray' ]; //EDITEI
   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
   $this->HREF = $saved[ 'HREF' ]; //EDITEI
   $this->underline = $saved[ 'underline' ]; //EDITEI
   $this->strike = $saved[ 'strike' ]; //EDITEI
   $this->SUP = $saved[ 'SUP' ]; //EDITEI
   $this->SUB = $saved[ 'SUB' ]; //EDITEI
   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI
   $this->outlineparam = $saved[ 'outlineparam' ];
   if ($write) { 
   	$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ],true,true);	// force output
	$fontout = (sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['Font'] != $fontout || $this->keep_block_together)) { $this->_out($fontout); }
	$this->pageoutput[$this->page]['Font'] = $fontout;
   }
   else 
   	$this->SetFont($saved[ 'family' ],$saved[ 'style' ].($this->underline ? 'U' : ''),$saved[ 'sizePt' ]);
}

function newFlowingBlock( $w, $h, $a = '', $is_table = false, $is_list = false, $blockstate = 0, $newblock=true )
{
   if (!$a) { $a = $this->defaultAlign; }
   // cell width in points
   $this->flowingBlockAttr[ 'width' ] = ($w * $this->k);
   // line height in user units
   $this->flowingBlockAttr[ 'is_table' ] = $is_table;
   $this->flowingBlockAttr[ 'is_list' ] = $is_list;
   $this->flowingBlockAttr[ 'height' ] = $h;
   $this->flowingBlockAttr[ 'lineCount' ] = 0;
   $this->flowingBlockAttr[ 'align' ] = $a;
   $this->flowingBlockAttr[ 'font' ] = array();
   $this->flowingBlockAttr[ 'content' ] = array();
   $this->flowingBlockAttr[ 'contentWidth' ] = 0;
   $this->flowingBlockAttr[ 'blockstate' ] = $blockstate;

   $this->flowingBlockAttr[ 'newblock' ] = $newblock;
   $this->flowingBlockAttr[ 'valign' ] = 'M';
}

function finishFlowingBlock($endofblock=false)
{
   $currentx = $this->x;
   //prints out the last chunk
   $is_table = $this->flowingBlockAttr[ 'is_table' ];
   $is_list = $this->flowingBlockAttr[ 'is_list' ];
   $maxWidth =& $this->flowingBlockAttr[ 'width' ];
   $lineHeight =& $this->flowingBlockAttr[ 'height' ];
   $align =& $this->flowingBlockAttr[ 'align' ];
   $content =& $this->flowingBlockAttr[ 'content' ];
   $font =& $this->flowingBlockAttr[ 'font' ];
   $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
   $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
   $valign =& $this->flowingBlockAttr[ 'valign' ];
   $blockstate = $this->flowingBlockAttr[ 'blockstate' ];

   $newblock = $this->flowingBlockAttr[ 'newblock' ];

	//*********** BLOCK BACKGROUND COLOR *****************//
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		$fill = 1;
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	}
	else {
		$this->SetFillColor(255);
		$fill = 0;
	}

	// set normal spacing
	// WORD SPACING
	if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
	$this->ws=0;
	if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
	$this->charspacing=0;

	// the amount of space taken up so far in user units
	$usedWidth = 0;

	// COLS
	$oldcolumn = $this->CurrCol;

	// Print out each chunk

	if ($is_table) { 
		$ipaddingL = $this->cellPaddingL; 
		$ipaddingR = $this->cellPaddingR; 
		$paddingL = ($ipaddingL * $this->k); 
		$paddingR = ($ipaddingR * $this->k); 
	} 
	else { 
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * $this->k); 
		$paddingR = ($ipaddingR * $this->k);
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];
	}

		if ($is_list && $this->list_lineheight[$this->listlvl]) {
			$this->lineheight_correction = $this->list_lineheight[$this->listlvl]; 
		} 
		else if ($is_table) {
			$this->lineheight_correction = $this->table_lineheight; 
		}
		else if ($this->blk[$this->blklvl]['line_height']) {
			$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
		} 
		else {
			$this->lineheight_correction = $this->default_lineheight_correction; 
		}

		//  correct lineheight to maximum fontsize
		$maxlineHeight = 0;
		$maxfontsize = 0;
		foreach ( $content as $k => $chunk )
		{
              $this->restoreFont( $font[ $k ],false );
		  if ($this->objectbuffer[$k]) { 
			$maxlineHeight = max($maxlineHeight,$this->objectbuffer[$k]['OUTER-HEIGHT']);
		  }
              else { 
			// Special case of sub/sup carried over on its own to last line
			if (($this->SUB || $this->SUP) && count($content)==1) { $actfs = $this->FontSize*100/55; } // 55% is font change for sub/sup
			else { $actfs = $this->FontSize; }
			$maxlineHeight = max($maxlineHeight,$actfs * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$actfs);
		  }
		}
		$lineHeight = $maxlineHeight;
		$this->linemaxfontsize = $maxfontsize;

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight,$lineHeight);
		if (($endofblock) && ($blockstate > 1) && ($this->blklvl > 0) && (!$is_table)) { 
		   if ($this->blk[$this->blklvl]['page_break_after_avoid']) {  $check_h += $lineHeight; }
		   if ($lineCount == 0) {
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		   }
		   $check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
		}

		// PAGEBREAK
		/*'If' below used in order to fix "first-line of other page with justify on" bug*/
		if($this->y+$check_h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {

      	     		$bak_x=$this->x;//Current X position

				// WORD SPACING
				$ws=$this->ws;//Word Spacing
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				$charspacing=$this->charspacing;//Character Spacing
				if($charspacing>0) {
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}

		          	$this->AddPage($this->CurOrientation);

		          	$this->x=$bak_x;
				// Added to correct for OddEven Margins
				$currentx += $this->MarginCorrection;
				$this->x += $this->MarginCorrection;

				// WORD SPACING
				if($ws>0) {
					$this->ws=$ws;
					$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
				}
				if($charspacing>0) {
					$this->charspacing=$charspacing;
					$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
				}
		}

		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);

			$oldcolumn = $this->CurrCol;
		}

		// TOP MARGIN
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && ($lineCount == 0) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
		}

		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) && (!$is_table) && (!$is_list)) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
		}

	// ADDED for Paragraph_indent
	$WidthCorrection = 0;
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$WidthCorrection = ($this->blk[$this->blklvl]['text_indent']*$this->k); 
	} 

	// PADDING and BORDER spacing/fill
	if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w']); 
			$this->x = $currentx;
	}


	if ($content) {

		$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR) ;
		$empty /= $this->k;

		// In FinishFlowing Block no lines are justified as it is always last line
		// but if orphansAllowed have allowed content width to go over max width, use J charspacing to compress line
		// JUSTIFICATION J - NOT!
		$nb_carac = 0;
		$nb_spaces = 0;
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		// If "orphans" in fact is just a final space - ignore this
		if (($contentWidth > $maxWidth) && ($content[count($content)-1] != ' ') )  {
 		  // WORD SPACING
			foreach ( $content as $k => $chunk ) {
		  		if (!$this->objectbuffer[$k]) {
					$nb_carac += mb_strlen( $chunk, $this->mb_encoding ) ;  
					$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_encoding ) ;  
				}
			}
			list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$contentWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*$this->k)-($paddingL+$paddingR)));
			if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
			$this->charspacing=$charspacing;
			if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
			$this->ws=$ws;
			$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR) - ( $this->charspacing * $nb_carac) - ( $this->ws * $nb_spaces);
			$empty /= $this->k;
		}

		$arraysize = count($content);

		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR);


		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;

   		if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->BiDirectional)))  { 
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				$reversed = $this->magic_reverse_dir($chunk);
				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if ($this->directionality == 'rtl') { 
				if ($contains_rtl) {
					$content = array_reverse($content,false);
				}
			}
			else if (($this->directionality == 'ltr') && ($this->BiDirectional)) { 
				if ($all_rtl) {
					$content = array_reverse($content,false);
				}
			}
		}


		$this->x = $currentx + $this->cMarginL + $ipaddingL;
		if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'J')	{
			if ($this->directionality == 'rtl' && $contains_rtl) { $this->x += $empty; }
			else if ($this->directionality == 'ltr' && $all_rtl) { $this->x += $empty; }
		}
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		$WidthCorrection = 0; 
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align !='C')) { 
		  	$this->x += $this->blk[$this->blklvl]['text_indent']; 
		}


          foreach ( $content as $k => $chunk )
          {

			// FOR IMAGES
		if (($this->directionality=='rtl' && $contains_rtl) || $all_rtl) { $dirk = $arraysize-1 - $k ; } else { $dirk = $k; }
		if ($this->objectbuffer[$dirk]) {
			$xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X'] ; 
			$this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
			$this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
			$this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			if ($valign == 'M' || $valign == '') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT'])/2;
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'B') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'T') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
		}



			// DIRECTIONALITY RTL
			if ((($this->directionality == 'rtl') && ($contains_rtl )) || ($all_rtl )) { $this->restoreFont( $font[ $arraysize-1 - $k ] ); }
			else { $this->restoreFont( $font[ $k ] ); }
	 		//*********** SPAN BACKGROUND COLOR *****************//
			if ($this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}

			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk ) + ( $this->charspacing * mb_strlen($chunk,$this->mb_encoding ) / $this->k )  
				+ ( $this->ws * mb_substr_count($chunk,' ',$this->mb_encoding ) / $this->k );
			if ($this->objectbuffer[$dirk]) { $stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH']; }


              if ($k == $arraysize-1 ) $this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF , $currentx,0,0,$valign ); //mono-style line or last part (skips line)
              else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,$valign );//first or middle part


	 		//*********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR *****************//
			if ($spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']); }
			}
          }

	$this->printobjectbuffer($is_table);

	$this->objectbuffer = array();

	// LIST BULLETS/NUMBERS
	if ($is_list && is_array($this->bulletarray) && ($lineCount == 0) ) {
	  $bull = $this->bulletarray;
	  $this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']]);
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = true;
		$this->SetFont('zapfdingbats','',$this->FontSizePt/2.5);
	  }
	  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
        //Output bullet
	  $this->x = $currentx + $bull['x'];
	  $this->y -= $lineHeight;
        $this->Cell($bull['w'],$bull['h'],$bull['txt'],'','',$bull['align']);
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = false;
	  }
	  $this->x = $currentx;	// Reset
	  $this->y += $lineHeight;

	  $this->restoreFont( $savedFont );
	  $font = array( $savedFont );
	  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
	}


	}	// END IF CONTENT

	// PADDING and BORDER spacing/fill
	if (($endofblock) && ($blockstate > 1) && (($this->blk[$this->blklvl]['padding_bottom']) || ($this->blk[$this->blklvl]['border_bottom'])) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']); 
			$this->x = $currentx;
	}

	// SET Bottom y1 of block (used for painting borders)
	if (($endofblock) && ($blockstate > 1) && (!$is_table) && (!$is_list)) { 
		$this->blk[$this->blklvl]['y1'] = $this->y;
	}

	// BOTTOM MARGIN
	if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table) && (!$is_list)) { 
		if($this->y+$this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and !$this->InFooter) {
		$this->DivLn($this->blk[$this->blklvl]['margin_bottom'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
		}
	}

	// Reset lineheight
	$lineHeight = $this->divheight;
}





function printobjectbuffer($is_table=false) {
		$save_y = $this->y;
		$save_x = $this->x;
		$save_currentfontfamily = $this->Font;
		$save_currentfontsize = $this->FontSizePt;
		$save_currentfontstyle = $this->FontStyle.($this->underline ? 'U' : '');
		if ($this->directionality == 'rtl') { $rtlalign = 'R'; } else { $rtlalign = 'L'; }
		foreach ($this->objectbuffer AS $ib => $objattr) { 
		   $y = $objattr['OUTER-Y'];
		   $x = $objattr['OUTER-X'];
		   $w = $objattr['OUTER-WIDTH'];
		   $h = $objattr['OUTER-HEIGHT'];
		   $texto = $objattr['text'];
		   $this->y = $y;
		   $this->x = $x;
		   $this->SetFont($objattr['fontfamily'],'',$objattr['fontsize'] );
		// HR
		   if ($objattr['type'] == 'hr') {
      		$this->SetDrawColor($objattr['color']['R'],$objattr['color']['G'],$objattr['color']['B']);
      		switch($objattr['align']) {
      		    case 'C':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $empty /= 2;
      		        $x += $empty;
      		        break;
      		    case 'R':
      		        $empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
      		        $x += $empty;
      		        break;
      		}
      		$oldlinewidth = $this->LineWidth;
			$this->SetLineWidth($objattr['linewidth']);
			$this->y += ($objattr['linewidth']/2) + $objattr['margin_top'];
			$this->Line($x,$this->y,$x+$objattr['INNER-WIDTH'],$this->y);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		// IMAGE
		   if ($objattr['type'] == 'image') {
			$this->y = $objattr['INNER-Y'];
			$this->_out( sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q",$objattr['INNER-WIDTH'] *$this->k,$objattr['INNER-HEIGHT'] *$this->k,$objattr['INNER-X'] *$this->k,($this->h-($objattr['INNER-Y'] +$objattr['INNER-HEIGHT'] ))*$this->k,$objattr['ID'] ) );
			// LINK
			if($objattr['link']) $this->Link($objattr['INNER-X'],$objattr['INNER-Y'],$objattr['INNER-WIDTH'],$objattr['INNER-HEIGHT'],$objattr['link']);
			if ($objattr['BORDER-WIDTH']) { $this->PaintImgBorder($objattr); }
		   }
		// TEXT/PASSWORD INPUT
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD')) {
				$w -= $this->form_element_spacing['input']['outer']['h']*2;
				$h -= $this->form_element_spacing['input']['outer']['v']*2;
				$this->x += $this->form_element_spacing['input']['outer']['h'];
				$this->y += $this->form_element_spacing['input']['outer']['v'];
			// Chop texto to max length $w-inner-padding
			while ($this->GetStringWidth($texto) > $w-($this->form_element_spacing['input']['inner']['h']*2)) {
				$texto = mb_substr($texto,0,mb_strlen($texto,$this->mb_encoding)-1,$this->mb_encoding);
			}
			$this->SetFillColor(235,235,235);
			// DIRECTIONALITY
			$this->magic_reverse_dir($texto);
			$this->Cell($w,$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['input']['inner']['h']/*internal text x offset*/,$this->form_element_spacing['input']['inner']['h'], 'M') ;
			$this->SetFillColor(255);
		   }
		// SELECT
		   if ($objattr['type'] == 'select') {
			$this->SetLineWidth(0.2);
			$this->SetFillColor(235,235,235);
				$w -= $this->form_element_spacing['select']['outer']['h']*2;
				$h -= $this->form_element_spacing['select']['outer']['v']*2;
				$this->x += $this->form_element_spacing['select']['outer']['h'];
				$this->y += $this->form_element_spacing['select']['outer']['v'];
			// DIRECTIONALITY
			$this->magic_reverse_dir($texto);
			$this->Cell($w-($this->FontSize*1.4),$h,$texto,1,0,$rtlalign,1,'',0,$this->form_element_spacing['select']['inner']['h']/*internal text x offset*/,$this->form_element_spacing['select']['inner']['h'], 'M') ;
			$this->SetFillColor(190,190,190); //dark gray
			$save_font = $this->FontFamily;
           		$save_currentfont = $this->currentfontfamily;
			$this->SetFont('zapfdingbats','',0);
			$this->Cell(($this->FontSize*1.4),$h,$this->chrs[116],1,0,'C',1,'',0,0,0, 'M') ;
			$this->SetFont($save_font,'',0);
           		$this->currentfontfamily = $save_currentfont;
			$this->SetFillColor(255);
		   }
		// BUTTON
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'SUBMIT' || $objattr['subtype'] == 'RESET' || $objattr['subtype'] == 'IMAGE' || $objattr['subtype'] == 'BUTTON')) {
			$this->SetLineWidth(0.2);
			$this->SetFillColor(190,190,190);
				$w -= $this->form_element_spacing['button']['outer']['h']*2;
				$h -= $this->form_element_spacing['button']['outer']['v']*2;
				$this->x += $this->form_element_spacing['button']['outer']['h'];
				$this->y += $this->form_element_spacing['button']['outer']['v'];
			$this->RoundedRect($this->x, $this->y, $w, $h, 0.5, 'DF');
				$w -= $this->form_element_spacing['button']['inner']['h']*2;
				$h -= $this->form_element_spacing['button']['inner']['v']*2;
				$this->x += $this->form_element_spacing['button']['inner']['h'];
				$this->y += $this->form_element_spacing['button']['inner']['v'];
			// DIRECTIONALITY
			$this->magic_reverse_dir($texto);
			$this->Cell($w,$h,$texto,'',0,'C',0,'',0,0,0, 'M') ;
			$this->SetFillColor(255);
		   }
		// TEXTAREA
		   if ($objattr['type'] == 'textarea') {
			    $this->SetLineWidth(0.2);
                      $this->SetFillColor(235,235,235);
			    $w -= $this->form_element_spacing['textarea']['outer']['h']*2;
			    $h -= $this->form_element_spacing['textarea']['outer']['v']*2;
                      $this->x += $this->form_element_spacing['textarea']['outer']['h'];
                      $this->y += $this->form_element_spacing['textarea']['outer']['v'];
 			    $this->Rect($this->x,$this->y,$w,$h,'DF');
    	                $this->SetFillColor(255);
			    $w -= $this->form_element_spacing['textarea']['inner']['h']*2;
                      $this->x += $this->form_element_spacing['textarea']['inner']['h'];
                      $this->y += $this->form_element_spacing['textarea']['inner']['v'];
                      $linesneeded = $this->WordWrap($texto,$w);
                      if ($linesneeded > $objattr['rows']) { //Too many words inside textarea
				$textoaux = preg_split('/[\n]/u',$texto);
                        $texto = '';
                        for($i=0;$i<$objattr['rows'];$i++) {
                          if ($i == ($objattr['rows']-1)) $texto .= $textoaux[$i];
                          else $texto .= $textoaux[$i] . "\n";
                        }

				$texto = mb_substr($texto,0,mb_strlen($texto,$this->mb_encoding)-4,$this->mb_encoding) . "...";
                      }
			if ($texto != '') $this->MultiCell($w,$this->FontSize*$this->textarea_lineheight,$texto,0,'',0,'',$this->directionality,true);
		   }

		// CHECKBOX
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'CHECKBOX')) {
			$this->SetLineWidth(0.2);
        		$this->SetFillColor(235,235,235);
			$iw = $w * 0.7;
			$ih = $h * 0.7;
			$lx = $x + (($w-$iw)/2); 
			$ty = $y + (($h-$ih)/2);
			$rx = $lx + $iw;
			$by = $ty + $ih;
			$this->Rect($lx,$ty,$iw,$ih,'DF');
			if ($objattr['checked']) {
				//Round join and cap
				$this->_out('1 J');
				$this->Line($lx,$ty,$rx,$by);
				$this->Line($lx,$by,$rx,$ty);
				//Set line cap style back to square
				$this->_out('2 J');
			}
			$this->SetFillColor(255);
		   }
		// RADIO
		   if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'RADIO')) {
			$this->SetLineWidth(0.2);
			$radius = $this->FontSize*0.35;
			$cx = $x + ($w/2); 
			$cy = $y + ($h/2);
			$this->Circle($cx,$cy,$radius,'D');
			$this->SetFillColor(0);
			if ($objattr['checked']) {
				$this->Circle($cx,$cy,$radius*0.4,'DF');
			}
			$this->SetFillColor(255);
		   }
		}
		$this->SetFont($save_currentfontfamily,$save_currentfontstyle,$save_currentfontsize);
		$this->y = $save_y;
		$this->x = $save_x;
}


function WriteFlowingBlock( $s)
{
    $currentx = $this->x; 
    $is_table = $this->flowingBlockAttr[ 'is_table' ];
    $is_list = $this->flowingBlockAttr[ 'is_list' ];
    // width of all the content so far in points
    $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
    // cell width in points
    $maxWidth =& $this->flowingBlockAttr[ 'width' ];
    $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
    // line height in user units
    $lineHeight =& $this->flowingBlockAttr[ 'height' ];
    $align =& $this->flowingBlockAttr[ 'align' ];
    $content =& $this->flowingBlockAttr[ 'content' ];
    $font =& $this->flowingBlockAttr[ 'font' ];
    $valign =& $this->flowingBlockAttr[ 'valign' ];
    $blockstate = $this->flowingBlockAttr[ 'blockstate' ];


    $newblock = $this->flowingBlockAttr[ 'newblock' ];


	//*********** BLOCK BACKGROUND COLOR *****************//
	if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
		$fill = 1;
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	}
	else {
		$this->SetFillColor(255);
		$fill = 0;
	}


    $font[] = $this->saveFont();
    $content[] = '';

    $currContent =& $content[ count( $content ) - 1 ];

    // where the line should be cutoff if it is to be justified
    $cutoffWidth = $contentWidth;

	$curlyquote = mb_convert_encoding("\xe2\x80\x9e",$this->mb_encoding,'UTF-8');
	$curlylowquote = mb_convert_encoding("\xe2\x80\x9d",$this->mb_encoding,'UTF-8');

	// COLS
	$oldcolumn = $this->CurrCol;

   if ($is_table) { 
	$ipaddingL = $this->cellPaddingL; 
	$ipaddingR = $this->cellPaddingR; 
	$paddingL = (($this->cellPaddingL) * $this->k); 
	$paddingR = (($this->cellPaddingR) * $this->k); 
	$cpaddingadjustL = -$this->cMarginL;
	$cpaddingadjustR = -$this->cMarginR;
   } 
   else { 
		$ipaddingL = $this->blk[$this->blklvl]['padding_left']; 
		$ipaddingR = $this->blk[$this->blklvl]['padding_right']; 
		$paddingL = ($ipaddingL * $this->k); 
		$paddingR = ($ipaddingR * $this->k); 
		$this->cMarginL =  $this->blk[$this->blklvl]['border_left']['w'];
		$cpaddingadjustL = -$this->cMarginL;
		$this->cMarginR =  $this->blk[$this->blklvl]['border_right']['w'];
		$cpaddingadjustR = -$this->cMarginR;
   }

     //OBJECTS - IMAGES & FORM Elements (NB has already skipped line/page if required - in printbuffer)
      if ($s{0} == '»' and $s{1} == '¤' and $s{2} == '¬') { //identifier has been identified!
		$sccontent = split("»¤¬",$s,2);
		$sccontent = split(",",$sccontent[1],2);
		foreach($sccontent as $scvalue) {
			$scvalue = split("=",$scvalue,2);
			$specialcontent[$scvalue[0]] = $scvalue[1];
		}
		$objattr = unserialize($specialcontent['objattr']);
		$h_corr = 0; 
		if ($is_table) {
			$maximumW = ($maxWidth/$this->k) - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR); 
		}
		else {
			if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 0) && (!$is_table)) { $h_corr = $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w']; }
			$maximumW = ($maxWidth/$this->k) - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w']); 
		}
		$objattr = $this->inlineObject($objattr['type'],$this->lMargin + ($contentWidth/$this->k),($this->y + $h_corr), $objattr, $this->lMargin,($contentWidth/$this->k),$maximumW,$lineHeight,true,$is_table);

		// SET LINEHEIGHT for this line ================ RESET AT END
		$lineHeight = MAX($lineHeight,$objattr['OUTER-HEIGHT']);
		$this->objectbuffer[count($content)-1] = $objattr;
		$valign = $objattr['vertical-align'];
		$contentWidth += ($objattr['OUTER-WIDTH'] * $this->k);
		return;
	}


   if ((($this->isunicode)  || ($this->isCJK)) && (!$this->usingembeddedfonts)) {
	$tmp = mb_strlen( $s, $this->mb_encoding );
   }
   else {
	$tmp = strlen( $s );
   }

   $orphs = 0; 
   $check = 0;

   // for every character in the string
   for ( $i = 0; $i < $tmp; $i++ )  {
	// extract the current character
	// get the width of the character in points
	if ((($this->isunicode)  || ($this->isCJK)) && (!$this->usingembeddedfonts)) {
	      $c = mb_substr($s,$i,1,$this->mb_encoding );
		$cw = ($this->GetStringWidth($c) * $this->k);
	}
	else {
       	$c = $s{$i};
		$cw = $this->CurrentFont[ 'cw' ][ $c ] * ( $this->FontSizePt / 1000 );
	}
	if ($c==' ') { $check = 1; }

	// CHECK for ORPHANS - edited mPDF 1.1 to add brackets
	else if ($c=='.' || $c==',' || $c==')' || $c==';' || $c==':' || $c=='!' || $c=='?'|| $c=='"' || $c==$curlyquote || $c==$curlylowquote)  {$check++; }
	else { $check = 0; }
	// There's an orphan '. ' or ', ' or <sup>32</sup> about to be cut off at the end of line
	if($check==1) {
		$currContent .= $c;
		$cutoffWidth = $contentWidth;
		$contentWidth += $cw;
		continue;
	}
	if(($this->SUP || $this->SUB) && ($orphs < $this->orphansAllowed)) {	// ? disable orphans in table if  borders used
		$currContent .= $c;
		$cutoffWidth = $contentWidth;
		$contentWidth += $cw;
		$orphs++;
		continue;
	}
	else { $orphs = 0; }

	// ADDED for Paragraph_indent
	$WidthCorrection = 0; 
	if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && (!$is_list) && ($align != 'C')) { 
		$WidthCorrection = ($this->blk[$this->blklvl]['text_indent']*$this->k); 
	} 


       // try adding another char
	if (( $contentWidth + $cw > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR) +  0.001))  {// 0.001 is to correct for deviations converting mm=>pts
		// it won't fit, output what we already have
		$lineCount++;
 
		// contains any content that didn't make it into this print
		$savedContent = '';
		$savedFont = array();


           // first, cut off and save any partial words at the end of the string
           $words = explode( ' ', $currContent );

		// If CJK only break at space if in ASCII string else break after current character
	     if (($this->isCJK) && ($this->ords[$c]>127)) { $words = array(); $words[] = $currContent; }


           // if it looks like we didn't finish any words for this chunk
           if ( count( $words ) == 1 ) {
		// TO correct for error when word too wide for page - but only when one long word from left to right margin
		if (count($content) == 1 && $currContent != ' ') {
			$lastContent = '';
 			//****************************
  			if ((($this->isunicode)  || ($this->isCJK)) && (!$this->usingembeddedfonts)) {
			   for ( $cc = 0; $cc < mb_strlen( $currContent, $this->mb_encoding ) - 1; $cc++) $lastContent .= "{$words[ $cc ]}";
			}
			else {
			   for ( $cc = 0; $cc < strlen( $currContent ) - 1; $cc++) { $lastContent .= "{$words[ $cc ]}"; }
			}
 			//****************************
			$savedFont = $this->saveFont();
			// replace the current content with the cropped version
			$currContent = mb_rtrim( $lastContent, $this->mb_encoding );
		}
		else {
			/* this was the original with no if-else */
			// save and crop off the content currently on the stack
			$savedContent = array_pop( $content );
			$savedFont = array_pop( $font );
			// trim any trailing spaces off the last bit of content
			$currContent =& $content[ count( $content ) - 1 ];
			$currContent = mb_rtrim( $currContent, $this->mb_encoding );
		}
          }
          else // otherwise, we need to find which bit to cut off
           {
              $lastContent = '';
              for ( $w = 0; $w < count( $words ) - 1; $w++) $lastContent .= "{$words[ $w ]} ";
              $savedContent = $words[ count( $words ) - 1 ];
              $savedFont = $this->saveFont();
              // replace the current content with the cropped version
             $currContent = mb_rtrim( $lastContent, $this->mb_encoding );
          }

		// Set Current lineheight (correction factor)
		if ($is_list && $this->list_lineheight[$this->listlvl]) {
			$this->lineheight_correction = $this->list_lineheight[$this->listlvl]; 
		} 
		else if ($is_table) {
			$this->lineheight_correction = $this->table_lineheight; 
		}
		else if ($this->blk[$this->blklvl]['line_height']) {
			$this->lineheight_correction = $this->blk[$this->blklvl]['line_height']; 
		} 
		else {
			$this->lineheight_correction = $this->default_lineheight_correction; 
		}

		// update $contentWidth and $cutoffWidth since they changed with cropping
		// Also correct lineheight to maximum fontsize (not for tables)
		$contentWidth = 0;
		$maxlineHeight = 0;
		$maxfontsize = 0;
		foreach ( $content as $k => $chunk )
		{
              $this->restoreFont( $font[ $k ]);
		  if ($this->objectbuffer[$k]) { 
			$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * $this->k; 
			$maxlineHeight = max($maxlineHeight,$this->objectbuffer[$k]['OUTER-HEIGHT']);
		  }
              else { 
			$contentWidth += $this->GetStringWidth( $chunk ) * $this->k; 
			$maxlineHeight = max($maxlineHeight,$this->FontSize * $this->lineheight_correction ); 
			$maxfontsize = max($maxfontsize,$this->FontSize); 
		  }
		}
		$lineHeight = $maxlineHeight; 
		$cutoffWidth = $contentWidth;
		$this->linemaxfontsize = $maxfontsize;


		// JUSTIFICATION J
		$nb_carac = 0;
		$nb_spaces = 0;
		// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
		if(( $align == 'J' ) || ($cutoffWidth > $maxWidth - $WidthCorrection - (($this->cMarginL+$this->cMarginR)*$this->k) - ($paddingL+$paddingR) +  0.001)) {   // 0.001 is to correct for deviations converting mm=>pts
		  // JUSTIFY J (Use character spacing)
 		  // WORD SPACING
			foreach ( $content as $k => $chunk ) {
		  		if (!$this->objectbuffer[$k]) {
					$nb_carac += mb_strlen( $chunk, $this->mb_encoding ) ;  
					$nb_spaces += mb_substr_count( $chunk,' ', $this->mb_encoding ) ;  
				}
			}
			list($charspacing,$ws) = $this->GetJspacing($nb_carac,$nb_spaces,($maxWidth-$cutoffWidth-$WidthCorrection-(($this->cMarginL+$this->cMarginR)*$this->k)-($paddingL+$paddingR)));
			if ($charspacing) { $this->_out(sprintf('BT %.3f Tc ET',$charspacing)); }
			else if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
			$this->charspacing=$charspacing;
			if ($ws) { $this->_out(sprintf('BT %.3f Tw ET',$ws)); }
			else if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
			$this->ws=$ws;
		}

		// otherwise, we want normal spacing
		else {
			if ($this->charspacing != 0) { $this->_out('BT 0 Tc ET'); }
			$this->charspacing=0;
			if ($this->ws != 0) { $this->_out('BT 0 Tw ET'); }
			$this->ws=0;
		}

		// WORD SPACING
		$empty = $maxWidth - $WidthCorrection - $contentWidth - (($this->cMarginL+$this->cMarginR)* $this->k) - ($paddingL+$paddingR) - ( $this->charspacing * $nb_carac) - ( $this->ws * $nb_spaces);
		$empty /= $this->k;
		$b = ''; //do not use borders

		// Get PAGEBREAK TO TEST for height including the top border/padding
		$check_h = max($this->divheight,$lineHeight);
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blklvl > 0) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		}

		// PAGEBREAK
		/*'If' below used in order to fix "first-line of other page with justify on" bug*/
		if($this->y+$check_h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {

      	     		$bak_x=$this->x;//Current X position

				// WORD SPACING
				$ws=$this->ws;//Word Spacing
				if($this->ws>0) {
					$this->ws=0;
					$this->_out('BT 0 Tw ET'); 
				}
				$charspacing=$this->charspacing;//Character Spacing
				if($charspacing>0) {
					$this->charspacing=0;
					$this->_out('BT 0 Tc ET'); 
				}

		          	$this->AddPage($this->CurOrientation);

		          	$this->x = $bak_x;
				// Added to correct for OddEven Margins
				$currentx += $this->MarginCorrection;
				$this->x += $this->MarginCorrection;

				// WORD SPACING
				if($ws>0) {
					$this->ws=$ws;
					$this->_out(sprintf('BT %.3f Tw ET',$ws)); 
				}
				if($charspacing>0) {
					$this->charspacing=$charspacing;
					$this->_out(sprintf('BT %.3f Tc ET',$charspacing));//add-on 
				}
		}

		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
			$oldcolumn = $this->CurrCol;
		}

		// TOP MARGIN
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($this->blk[$this->blklvl]['margin_top']) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['margin_top'],$this->blklvl-1,true,$this->blk[$this->blklvl]['margin_collapse']); 
		}


		// Update y0 for top of block (used to paint border)
		if (($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
		}

		// TOP PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate==1 || $blockstate==3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 1) && (!$is_table) && (!$is_list)) { 
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w']);
		}

		$arraysize = count($content);

		$margins = ($this->cMarginL+$this->cMarginR) + ($ipaddingL+$ipaddingR);
 
		// PAINT BACKGROUND FOR THIS LINE
		if (!$is_table) { $this->DivLn($lineHeight,$this->blklvl,false); }	// false -> don't advance y

		$this->x = $currentx + $this->cMarginL + $ipaddingL;
		if ($align == 'R') { $this->x += $empty; }
		else if ($align == 'C') { $this->x += ($empty / 2); }

		// Paragraph INDENT
		if (($this->blk[$this->blklvl]['text_indent']) && ($newblock) && ($blockstate==1 || $blockstate==3) && ($lineCount == 1) && (!$is_table) && ($this->directionality!='rtl') && ($align !='C')) { 
			$this->x += $this->blk[$this->blklvl]['text_indent'];
		}


		// DIRECTIONALITY RTL
		$all_rtl = false;
		$contains_rtl = false;

   		if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->BiDirectional)))  { 
			$all_rtl = true;
			foreach ( $content as $k => $chunk ) {
				$reversed = $this->magic_reverse_dir($chunk);
				if ($reversed > 0) { $contains_rtl = true; }
				if ($reversed < 2) { $all_rtl = false; }
				$content[$k] = $chunk;
			}
			if ($this->directionality == 'rtl') { 
				if ($contains_rtl) {
					$content = array_reverse($content,false);
				}
			}
			else if (($this->directionality == 'ltr') && ($this->BiDirectional)) { 
				if ($all_rtl) {
					$content = array_reverse($content,false);
				}
			}
		}

		foreach ( $content as $k => $chunk )
                 {

			// FOR IMAGES - UPDATE POSITION
			if (($this->directionality=='rtl' && $contains_rtl) || $all_rtl) { $dirk = $arraysize-1 - $k ; } else { $dirk = $k; }
			if ($this->objectbuffer[$dirk]) {
				$xadj = $this->x - $this->objectbuffer[$dirk]['OUTER-X'] ; 

				$this->objectbuffer[$dirk]['OUTER-X'] += $xadj;
				$this->objectbuffer[$dirk]['BORDER-X'] += $xadj;
				$this->objectbuffer[$dirk]['INNER-X'] += $xadj;
			if ($valign == 'M' || $valign == '') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT'])/2;
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'B') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y'])+($lineHeight - $this->objectbuffer[$dirk]['OUTER-HEIGHT']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			else if ($valign == 'T') { 
				$yadj = ($this->y - $this->objectbuffer[$dirk]['OUTER-Y']);
				$this->objectbuffer[$dirk]['OUTER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['BORDER-Y'] += $yadj;
				$this->objectbuffer[$dirk]['INNER-Y'] += $yadj;
			}
			}

			// DIRECTIONALITY RTL
			if ((($this->directionality == 'rtl') && ($contains_rtl )) || ($all_rtl )) { $this->restoreFont($font[$arraysize-1 - $k]); }
			else { $this->restoreFont( $font[ $k ] ); }

	 		//*********** SPAN BACKGROUND COLOR *****************//
			if ($this->spanbgcolor) { 
				$cor = $this->spanbgcolorarray;
				$this->SetFillColor($cor['R'],$cor['G'],$cor['B']);
				$save_fill = $fill; $spanfill = 1; $fill = 1;
			}

			// WORD SPACING
		      $stringWidth = $this->GetStringWidth($chunk ) + ( $this->charspacing * mb_strlen($chunk,$this->mb_encoding ) / $this->k )  
				+ ( $this->ws * mb_substr_count($chunk,' ',$this->mb_encoding ) / $this->k );
			if ($this->objectbuffer[$dirk]) { $stringWidth = $this->objectbuffer[$dirk]['OUTER-WIDTH'];  }

			if ($stringWidth > 0) {
                     if ($k == $arraysize-1 ) $this->Cell( $stringWidth, $lineHeight, $chunk, '', 1, '', $fill, $this->HREF , $currentx,0,0,$valign ); //mono-style line or last part (skips line)
                     else $this->Cell( $stringWidth, $lineHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0,0,$valign );//first or middle part
			}
			else {	// If a space started a new chunk at the end of a line
				$this->x = $currentx; $this->y += $lineHeight; 
			}
	 		//*********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR *****************//
			if ($spanfill) { 
				$fill = $save_fill; $spanfill = 0; 
				if ($fill) { $this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']); }
			}
		    }
		// move on to the next line, reset variables, tack on saved content and current char

		$this->printobjectbuffer($is_table);
		$this->objectbuffer = array();

	// LIST BULLETS/NUMBERS
	if ($is_list && is_array($this->bulletarray) && ($lineCount == 1) ) {
	  $bull = $this->bulletarray;
	  $this->restoreInlineProperties($this->InlineProperties['LIST'][$bull['level']]);
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = true;
		$this->SetFont('zapfdingbats','',$this->FontSizePt/2.5);
	  }
	  else { $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true); }	// force output
        //Output bullet
	  $this->x = $currentx + $bull['x'];
	  $this->y -= $lineHeight;
        $this->Cell($bull['w'],$bull['h'],$bull['txt'],'','',$bull['align']);
	  if ($bull['font'] == 'zapfdingbats') {
		$this->bullet = false;
	  }
	  $this->x = $currentx;	// Reset
	  $this->y += $lineHeight;
	  $this->bulletarray = array();	// prevents repeat of bullet/number if <li>....<br />.....</li>
	}

		// Reset lineheight
		$lineHeight = $this->divheight;
		$valign = 'M';


		$this->restoreFont( $savedFont );
		$font = array( $savedFont );
		//****************************//
		$content = array( $savedContent . $c );
		//****************************//

		$currContent =& $content[ 0 ];
		$contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
		$cutoffWidth = $contentWidth;
      }
      // another character will fit, so add it on
	else {
		$contentWidth += $cw;
		$currContent .= $c;
	}
    }

}
//----------------------END OF FLOWING BLOCK------------------------------------//

//EDITEI
//Thanks to Ron Korving for the WordWrap() function
////////////////////////////////////////////////////////////////////////////////
// ADDED forcewrap - to call from TABLE functions to breakwords if necessary in cell
////////////////////////////////////////////////////////////////////////////////
function WordWrap(&$text, $maxwidth, $forcewrap = 0)
{
    $biggestword=0;//EDITEI
    $toonarrow=false;//EDITEI

    $text = ltrim($text);
    $text = mb_rtrim($text, $this->mb_encoding);

    if ($text==='') return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;
    foreach ($lines as $line) {

	//****************************// Edited mPDF 1.1
	if ($this->isunicode && !$this->usingembeddedfonts) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//
	$width = 0;
	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);

		//EDITEI
		//Warn user that maxwidth is insufficient
		if ($wordwidth > $maxwidth + 0.0001) {
			if ($wordwidth > $biggestword) { $biggestword = $wordwidth; }
			$toonarrow=true;//EDITEI
			// ADDED
			if ($forcewrap) {
			  while($wordwidth > $maxwidth) {
				$chw = 0;	// check width
				for ( $i = 0; $i < mb_strlen($word, $this->mb_encoding ); $i++ ) {
					$chw = $this->GetStringWidth(mb_substr($word,0,$i+1,$this->mb_encoding ));
					if ($chw > $maxwidth ) {
						if ($text) {
							$text = mb_rtrim($text, $this->mb_encoding)."\n".mb_substr($word,0,$i,$this->mb_encoding );
							$count++;
						}
						else {
							$text = mb_substr($word,0,$i,$this->mb_encoding );
						}
						$word = mb_substr($word,$i,mb_strlen($word, $this->mb_encoding )-$i,$this->mb_encoding );
						$wordwidth = $this->GetStringWidth($word);
						$width = $maxwidth; 
						break;
					}
				}
			  }
			}
		}

		if ($width + $wordwidth  < $maxwidth - 0.0001) {
			$width += $wordwidth + $space;
			$text .= $word.' ';
		}
		else {
			$width = $wordwidth + $space;
			$text = mb_rtrim($text, $this->mb_encoding)."\n".$word.' ';
			$count++;
            }
	}

	$text = mb_rtrim($text, $this->mb_encoding)."\n";
	$count++;
    }
    $text = mb_rtrim($text, $this->mb_encoding);

    //Return -(wordsize) if word is bigger than maxwidth 

	// ADDED
	if ($forcewrap) { return $count; }
      if (($toonarrow) && ($this->table_error_report)) {
		die("Word is too long to fit in table - ".$this->table_error_report_param); 
	}
    if ($toonarrow) return -$biggestword;
    else return $count;
}

function _SetTextRendering($mode) { 
	if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) 
	$this->Error("Text rendering mode should be 0, 1 or 2 (value : $mode)"); 
	$tr = ($mode.' Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;

} 

function SetTextOutline($width, $r=0, $g=-1, $b=-1) //EDITEI
{ 
  if ($width == false) //Now resets all values
  { 
    $this->outline_on = false;
    $this->SetLineWidth(0.2); 
    $this->SetDrawColor(0); 
    $this->_setTextRendering(0); 
    $tr = ('0 Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  }
  else
  { 
    $this->SetLineWidth($width); 
    $this->SetDrawColor($r, $g , $b); 
    $tr = ('2 Tr'); 
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['TextRendering'] != $tr || $this->keep_block_together)) { $this->_out($tr); }
	$this->pageoutput[$this->page]['TextRendering'] = $tr;
  } 
}

function Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true)
{
	//Put an image on the page
	if(!isset($this->images[$file]))
	{
		//First use of image, get info
		if($type=='')
		{
			$pos=strrpos($file,'.');
			if(!$pos)	$this->Error('Image file has no extension and no type was specified: '.$file);
			$type=substr($file,$pos+1);
		}
		$type=strtolower($type);
		$mqr=get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		if($type=='jpg' or $type=='jpeg')	$info=$this->_parsejpg($file);
		elseif($type=='png') $info=$this->_parsepng($file);
		elseif($type=='gif') $info=$this->_parsegif($file); //EDITEI - GIF format included
		else
		{
			//Allow for additional formats
			$mtd='_parse'.$type;
			if(!method_exists($this,$mtd)) $this->Error('Unsupported image type: '.$type);
			$info=$this->$mtd($file);
		}
		set_magic_quotes_runtime($mqr);
		$info['i']=count($this->images)+1;
		$this->images[$file]=$info;
	}
	else $info=$this->images[$file];
	//Automatic width and height calculation if needed
	if($w==0 and $h==0) {
		//Put image at default dpi
		$w=($info['w']/$this->k) * (72/$this->img_dpi);
		$h=($info['h']/$this->k) * (72/$this->img_dpi);
	}
	if($w==0)	$w=$h*$info['w']/$info['h'];
	if($h==0)	$h=$w*$info['h']/$info['w'];

	// Automatically resize to maximum dimensions of page
	if ($this->blk[$this->blklvl]['inner_width']) { $maxw = $this->blk[$this->blklvl]['inner_width']; }
	else { $maxw = $this->pgwidth; }
	if ($w > $maxw) {
		$w = $maxw;
		$h=$w*$info['h']/$info['w'];
	}

	if ($h > $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10))  {  // see below - +10 to avoid drawing too close to border of page
		$h = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
		$w=$h*$info['w']/$info['h'];
	}


	//Avoid drawing out of the paper(exceeding width limits). //EDITEI
	if ( ($x + $w) > $this->fw ) {
		$x = $this->lMargin;
		$y += 5;
	}

	$changedpage = false; //EDITEI
	$oldcolumn = $this->CurrCol;
	//Avoid drawing out of the page. //EDITEI
	if($y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak()) {
		$this->AddPage();
		// Added to correct for OddEven Margins
		$x=$x +$this->MarginCorrection;
		$y = $tMargin + $this->margin_header;
		$changedpage = true;
	}
	// COLS
	// COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		$y = $this->y0;
		$x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
		$this->x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
	}

	$outstring = sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q",$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']);

	if($paint) { //EDITEI
		$this->_out($outstring);
		if($link) $this->Link($x,$y,$w,$h,$link);

		//Avoid writing text on top of the image. // THIS WAS OUTSIDE THE if ($paint) bit!!!!!!!!!!!!!!!!
		$this->y = $y + $h;
	}


	//Return width-height array //EDITEI
	$sizesarray['WIDTH'] = $w;
	$sizesarray['HEIGHT'] = $h;
	$sizesarray['X'] = $x; //Position before painting image
	$sizesarray['Y'] = $y; //Position before painting image
	$sizesarray['OUTPUT'] = $outstring;
	return $sizesarray;
}



//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================

function inlineObject($type,$x,$y,$objattr,$Lmargin,$widthUsed,$maxWidth,$lineHeight,$paint=false,$is_table=false)
{
   // NB $x is only used when paint=true
	// Lmargin not used
   $w = $objattr['width'];
   $h = $objattr['height'];

   $widthLeft = $maxWidth - $widthUsed;
   $maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
	// For Images
   $extraWidth = $objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left']+ $objattr['margin_right'];
   $extraHeight = $objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top']+ $objattr['margin_bottom'];


   if ($type == 'image') {
	$file = $objattr['file'];
	$info=$this->images[$file];
   }

   // TEST whether need to skipline
   if (!$paint) {
	if ($type == 'hr') {	// always force new line
		if ($y + $h + $lineHeight > $this->PageBreakTrigger) { return array(-2,$w ,$h ); } // New page + new line
		else { return array(1,$w ,$h ); } // new line
	}
	else {
		if (($widthUsed > 0) && ($w > $widthLeft)) { 	// New line needed
			if ($y + $h + $lineHeight > $this->PageBreakTrigger) { return array(-2,$w ,$h ); } // New page + new line
			return array(1,$w ,$h ); // new line
		}
		// Will fit on line but NEW PAGE REQUIRED
		else if ($y + $h > $this->PageBreakTrigger) { return array(-1,$w ,$h ); }	// ? y0 in cols ? what in tables
		else { return array(0,$w ,$h ); }
	}
   }

   if ($type == 'image') {
	// Automatically resize to width remaining
	if ($w > $widthLeft ) {
		$w = $widthLeft ;
		$h=$w*$info['h']/$info['w'];
	}
	$img_w = $w - $extraWidth ;
	$img_h = $h - $extraHeight ;
	if ($objattr['border_left']['w']) {
		$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w'] + $objattr['border_right']['w'])/2) ;
		$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w'] + $objattr['border_bottom']['w'])/2) ;
		$objattr['BORDER-X'] = $x + $objattr['margin_left'] + ($objattr['border_left']['w']/2) ;
		$objattr['BORDER-Y'] = $y + $objattr['margin_top'] + ($objattr['border_top']['w']/2) ;
	}
	$objattr['INNER-WIDTH'] = $img_w;
	$objattr['INNER-HEIGHT'] = $img_h;
	$objattr['INNER-X'] = $x + $objattr['margin_left'] + ($objattr['border_left']['w']);
	$objattr['INNER-Y'] = $y + $objattr['margin_top'] + ($objattr['border_top']['w']) ;
	$objattr['ID'] = $info['i'];
   }

   if ($type == 'textarea') {
	// Automatically resize to width remaining
	if ($w > $widthLeft ) {
		$w = $widthLeft ;
	}
	if ($y + $h > $this->PageBreakTrigger) {
		$h=$this->h - $y - $this->bMargin;
	}
   }

   if ($type == 'hr') {
	if ($is_table) { $objattr['INNER-WIDTH'] = $maxWidth * $objattr['W-PERCENT']/100; $objattr['width'] = $objattr['INNER-WIDTH']; }
	else { $objattr['INNER-WIDTH'] = $w; }
	$w = $maxWidth ;
   }

   if (($type == 'select') || ($type == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD'))) {
	// Automatically resize to width remaining
	if ($w > $widthLeft ) {
		$w = $widthLeft;
	}
   }

   //Return width-height array
   $objattr['OUTER-WIDTH'] = $w;
   $objattr['OUTER-HEIGHT'] = $h;
   $objattr['OUTER-X'] = $x;
   $objattr['OUTER-Y'] = $y;

   return $objattr;
}


//=============================================================
//=============================================================
//=============================================================
//=============================================================
//=============================================================




//EDITEI - Done after reading a little about PDF reference guide
function DottedRect($x=100,$y=150,$w=50,$h=50,$dotsize=0.2,$spacing=2)
{
  // $spacing: Spacing between dots in mm
  // dotsize - passed to DrawDot()  radius in mm (user units)
  $x *= $this->k ;
  $y = ($this->h-$y)*$this->k;
  $w *= $this->k ;
  $h *= $this->k ;// - h?
   
  $herex = $x;
  $herey = $y;

  //Make fillcolor == drawcolor
  $bak_fill = $this->FillColor;
  $this->FillColor = $this->DrawColor;
  $this->FillColor = str_replace('RG','rg',$this->FillColor);
  $this->_out($this->FillColor);
 
  while ($herex < ($x + $w)) //draw from upper left to upper right
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herex += ($spacing *$this->k);
  }
  $herex = $x + $w;
  while ($herey > ($y - $h)) //draw from upper right to lower right
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herey -= ($spacing *$this->k);
  }
  $herey = $y - $h;
  while ($herex > $x) //draw from lower right to lower left
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herex -= ($spacing *$this->k);
  }
  $herex = $x;
  while ($herey < $y) //draw from lower left to upper left
  {
  $this->DrawDot($herex,$herey,$dotsize);
  $herey += ($spacing *$this->k);
  }
  $herey = $y;

  $this->FillColor = $bak_fill;
  $this->_out($this->FillColor); //return fillcolor back to normal
}

//EDITEI - Done after reading a little about PDF reference guide
function DrawDot($x,$y,$r=0) //center x, y, $r = radius in mm (user units) Optional
{
  if ($r == 0) { $r = 0.2 * $this->k;  }	// default 0.2mm
  else { $r = $r * $this->k; }  //DOT SIZE = radius
  $op = 'B'; // draw Filled Dots
  //F == fill //S == stroke //B == stroke and fill 
  
  //Start Point
  $x1 = $x - $r;
  $y1 = $y;
  //End Point
  $x2 = $x + $r;
  $y2 = $y;
  //Auxiliar Point
  $x3 = $x;
  $y3 = $y + (2*$r);// 2*raio to make a round (not oval) shape  

  //Round join and cap
  $s="\n".'1 J'."\n";
  $s.='1 j'."\n";

  //Upper circle
  $s.=sprintf('%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);//Bezier curve
  //Lower circle
  $y3 = $y - (2*$r);
  $s.=sprintf("\n".'%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);
  $s.=$op."\n"; //stroke and fill

  //Draw in PDF file
  $this->_out($s);

  //Set line cap style back to square
  $this->_out('2 J');
}

function SetDash($black=false,$white=false)
{
        if($black and $white) $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
        else $s='[] 0 d';
	// Edited mPDF 1.1 keeping block together on one page
	if($this->page>0 && ($this->pageoutput[$this->page]['Dash'] != $s || $this->keep_block_together)) { $this->_out($s); }
	$this->pageoutput[$this->page]['Dash'] = $s;

}

function DisplayPreferences($preferences)
{
    $this->DisplayPreferences .= $preferences;
}


function Ln($h='',$collapsible=0)
{
// Added collapsible to allow collapsible top-margin on new page
	//Line feed; default value is last cell height
	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
	if ($collapsible && ($this->y==$this->tMargin) && (!$this->ColActive)) { $h = 0; }
	if(is_string($h)) $this->y+=$this->lasth;
	else $this->y+=$h;
}


function DivLn($h,$level=-3,$move_y=true,$collapsible=false) {
  // this->x is returned as it was
  // adds lines (y) where DIV bgcolors are filled in
  if ($collapsible && ($this->y==$this->tMargin) && (!$this->ColActive)) { return; }
  if ($collapsible && ($this->y==$this->y0) && ($this->ColActive) && $this->CurrCol == 0) { return; }
  if ($level == -3) { $level = $this->blklvl; }
  $firstblockfill = $this->GetFirstBlockFill();
  if ($firstblockfill && $this->blklvl > 0 && $this->blklvl >= $firstblockfill) {
	$last_x = 0;
	$last_w = 0;
	$last_fc = $this->FillColor;
	$bak_x = $this->x;
	$bak_h = $this->divheight;
	$this->divheight = 0;	// Temporarily turn off divheight - as Cell() uses it to check for PageBreak
	for ($blvl=$firstblockfill;$blvl<=$level;$blvl++) {
		$this->SetBlockFill($blvl);
		$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		if ($last_x != $this->lMargin + $this->blk[$blvl]['outer_left_margin'] || $last_w != $this->blk[$blvl]['width'] || $last_fc != $this->FillColor) {
			$this->Cell( ($this->blk[$blvl]['width']), $h, '', '', 0, '', 1);
		}
		$last_x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
		$last_w = $this->blk[$blvl]['width'];
		$last_fc = $this->FillColor;
	}
	// Reset current block fill
	$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
	$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
	$this->x = $bak_x;
	$this->divheight = $bak_h;
  }
  if ($move_y) { $this->y += $h; }
}

function GetX()
{
	//Get x position
	return $this->x;
}

function SetX($x)
{
	//Set x position
	if($x >= 0)	$this->x=$x;
	else $this->x = $this->w + $x;
}

function GetY()
{
	//Get y position
	return $this->y;
}

function SetY($y)
{
	//Set y position and reset x
	$this->x=$this->lMargin;
	if($y>=0)
		$this->y=$y;
	else
		$this->y=$this->h+$y;
}

function SetXY($x,$y)
{
	//Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}

function Output($name='',$dest='')
{
	// mPDF 1.1 Added temporary disablement of encryption in CJK as doesn't work
	if ($this->isCJK) { $this->encrypted=false; }

	//Output PDF to some destination
	global $_SERVER;
	//Finish document if necessary
	if($this->state < 3) $this->Close();
	//Normalize parameters
	if(is_bool($dest)) $dest=$dest ? 'D' : 'F';
	$dest=strtoupper($dest);
	if($dest=='')
	{
		if($name=='')
		{
			$name='doc.pdf';
			$dest='I';
		}
		else
			$dest='F';
	}
	switch($dest)
	{
		case 'I':
			//Send to standard output
			if(isset($_SERVER['SERVER_NAME']))
			{
				//We send to a browser
				Header('Content-Type: application/pdf');
				if(headers_sent())
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				Header('Content-Length: '.strlen($this->buffer));
				Header('Content-disposition: inline; filename='.$name);
			}
			echo $this->buffer;
			break;
		case 'D':
			//Download file
			if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
				Header('Content-Type: application/force-download');
			else
				Header('Content-Type: application/octet-stream');
			if(headers_sent())
				$this->Error('Some data has already been output to browser, can\'t send PDF file');
			Header('Content-Length: '.strlen($this->buffer));
			Header('Content-disposition: attachment; filename='.$name);
 			echo $this->buffer;
			break;
		case 'F':
			//Save to local file
			$f=fopen($name,'wb');
			if(!$f) $this->Error('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			//Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}




/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/
function _dochecks()
{
	//Check for locale-related bug
	if(1.1==1)
		$this->Error('Don\'t alter the locale before including class file');
	//Check for decimal separator
	if(sprintf('%.1f',1.0)!='1.0')
		setlocale(LC_NUMERIC,'C');
}

function _begindoc()
{
	//Start document
	$this->state=1;
	$this->_out('%PDF-1.4');
}

function _putpages()
{
	$nb=$this->page;
	if(!empty($this->AliasNbPages))
	{
		//Replace number of pages
		for($n=1;$n<=$nb;$n++) {
		// Removed in mPDF v1.2
		//  if ($this->memory_opt) {	// mPDF1.1
		//	$this->pages[$n]=($this->compress) ? gzcompress(str_replace($this->AliasNbPages,$nb,gzuncompress($this->pages[$n]))) : str_replace($this->AliasNbPages,$nb,$this->pages[$n]) ;
		//  }
		//  else {
			$this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
		//  }
		}
	}
	if($this->DefOrientation=='P')
	{
		$wPt=$this->fwPt;
		$hPt=$this->fhPt;
	}
	else
	{
		$wPt=$this->fhPt;
		$hPt=$this->fwPt;
	}
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		if(isset($this->OrientationChanges[$n]))
			$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$hPt,$wPt));
		$this->_out('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			//Links
			$annots='/Annots [';
			foreach($this->PageLinks[$n] as $pl)
			{
				$rect=sprintf('%.2f %.2f %.2f %.2f',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(is_string($pl[4])) {
					if ($this->encrypted) {
						$pl[4] = $this->_RC4($this->_objectkey($this->n), $pl[4]);
					}
					$annots.='/A <</S /URI /URI ('.$this->_escape($pl[4]).')>>>>';
					// NB Previously for non-unicode, was:
					//$annots.='/A <</S /URI /URI '.$this->_UTF16BEtextstring($pl[4]).'>>>>';
				}
				else
				{
					$l=$this->links[$pl[4]];
					$h=isset($this->OrientationChanges[$l[0]]) ? $wPt : $hPt;
					$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]>>',1+2*$l[0],$h-$l[1]*$this->k);
				}
			}
			$this->_out($annots.']');
		}

		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');
		//Page content
		$this->_newobj();

		// Removed in mPDF v1.2
//		if ($this->memory_opt) {	// mPDF1.1
//			$this->_out('<<'.$filter.'/Length '.strlen($this->pages[$n]).'>>');
//			$this->_putstream($this->pages[$n]);
//		}
//		else {
			$p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
			$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
			$this->_putstream($p);
//		}
		$this->_out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$wPt,$hPt));
	$this->_out('>>');
	$this->_out('endobj');
}

function _putfonts() {
	if ($this->isunicode || $this->isCJK) {
			$nf=$this->n;
			foreach($this->diffs as $diff) {
				//Encodings
				$this->_newobj();
				$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
				$this->_out('endobj');
			}
			$mqr=get_magic_quotes_runtime();
			set_magic_quotes_runtime(0);
			foreach($this->FontFiles as $file=>$info) {
				//Font file embedding
				$this->_newobj();
				$this->FontFiles[$file]['n']=$this->n;
				$font='';
				$f=fopen($this->_getfontpath().$file,'rb',1);
				if(!$f) {
					$this->Error('Font file not found');
				}
				while(!feof($f)) {
					$font .= fread($f, 8192);
				}
				fclose($f);
				$compressed=(substr($file,-2)=='.z');
				if(!$compressed && isset($info['length2'])) {
					$header=($this->ords[$font{0}]==128);
					if($header) {
						//Strip first binary header
						$font=substr($font,6);
					}
					if($header && $this->ords[$font{$info['length1']}]==128) {
						//Strip second binary header
						$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
					}
				}
				$this->_out('<</Length '.strlen($font));
				if($compressed) {
					$this->_out('/Filter /FlateDecode');
				}
				$this->_out('/Length1 '.$info['length1']);
				if(isset($info['length2'])) {
					$this->_out('/Length2 '.$info['length2'].' /Length3 0');
				}
				$this->_out('>>');
				$this->_putstream($font);
				$this->_out('endobj');
			}
			set_magic_quotes_runtime($mqr);
			foreach($this->fonts as $k=>$font) {
				//Font objects
				$this->fonts[$k]['n']=$this->n+1;
				$type=$font['type'];
				$name=$font['name'];
				if($type=='Type0') { 
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_putType0($font);
				}
				else if($type=='core') {
					//Standard font
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_out('/BaseFont /'.$name);
					$this->_out('/Subtype /Type1');
					if($name!='Symbol' && $name!='ZapfDingbats') {
						$this->_out('/Encoding /WinAnsiEncoding');
					}
					$this->_out('>>');
					$this->_out('endobj');
				} elseif($type=='Type1' || $type=='TrueType') {
					//Additional Type1 or TrueType font
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_out('/BaseFont /'.$name);
					$this->_out('/Subtype /'.$type);
					$this->_out('/FirstChar 32 /LastChar 255');
					$this->_out('/Widths '.($this->n+1).' 0 R');
					$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
					if($font['enc']) {
						if(isset($font['diff'])) {
							$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
						} else {
							$this->_out('/Encoding /WinAnsiEncoding');
						}
					}
					$this->_out('>>');
					$this->_out('endobj');
					//Widths
					$this->_newobj();
					$cw=&$font['cw'];
					$s='[';
					for($i=32;$i<=255;$i++) {
						$s.=$cw[$this->chrs[$i]].' ';
					}
					$this->_out($s.']');
					$this->_out('endobj');
					//Descriptor
					$this->_newobj();
					$s='<</Type /FontDescriptor /FontName /'.$name;
					foreach($font['desc'] as $k=>$v) {
						$s.=' /'.$k.' '.$v;
					}
					$file = $font['file'];
					if($file) {
						$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
					}
					$this->_out($s.'>>');
					$this->_out('endobj');
				} 
				else {
					//Allow for additional types
					$mtd='_put'.strtolower($type);
					if(!method_exists($this, $mtd)) {
						$this->Error('Unsupported font type: '.$type.' ('.$name.')');
					}
					$this->$mtd($font);
				}
			}
	}
	else {

		$nf=$this->n;
		foreach($this->diffs as $diff)
		{
			//Encodings
			$this->_newobj();
			$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
			$this->_out('endobj');
		}
		$mqr=get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		foreach($this->FontFiles as $file=>$info)
		{
			//Font file embedding
			$this->_newobj();
			$this->FontFiles[$file]['n']=$this->n;
			if(defined('FPDF_FONTPATH'))
				$file=FPDF_FONTPATH.$file;
			$size=filesize($file);
			if(!$size)
				$this->Error('Font file not found');
			$this->_out('<</Length '.$size);
			if(substr($file,-2)=='.z')
				$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$info['length1']);
			if(isset($info['length2']))
				$this->_out('/Length2 '.$info['length2'].' /Length3 0');
			$this->_out('>>');
			$f=fopen($file,'rb');
			$this->_putstream(fread($f,$size));
			fclose($f);
			$this->_out('endobj');
		}
		set_magic_quotes_runtime($mqr);
		foreach($this->fonts as $k=>$font)
		{
			//Font objects
			$this->fonts[$k]['n']=$this->n+1;
			$type=$font['type'];
			$name=$font['name'];
			if($type=='core')
			{
				//Standard font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /'.$name);
				$this->_out('/Subtype /Type1');
				if($name!='Symbol' and $name!='ZapfDingbats')
					$this->_out('/Encoding /WinAnsiEncoding');
				$this->_out('>>');
				$this->_out('endobj');
			}
			elseif($type=='Type1' or $type=='TrueType')
			{
				//Additional Type1 or TrueType font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /'.$name);
				$this->_out('/Subtype /'.$type);
				$this->_out('/FirstChar 32 /LastChar 255');
				$this->_out('/Widths '.($this->n+1).' 0 R');
				$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
				if($font['enc'])
				{
					if(isset($font['diff']))
						$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
					else
						$this->_out('/Encoding /WinAnsiEncoding');
				}
				$this->_out('>>');
				$this->_out('endobj');
				//Widths
				$this->_newobj();
				$cw=&$font['cw'];
				$s='[';
				for($i=32;$i<=255;$i++)
					$s.=$cw[$this->chrs[$i]].' ';
				$this->_out($s.']');
				$this->_out('endobj');
				//Descriptor
				$this->_newobj();
				$s='<</Type /FontDescriptor /FontName /'.$name;
				foreach($font['desc'] as $k=>$v)
					$s.=' /'.$k.' '.$v;
				$file=$font['file'];
				if($file)
					$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
				$this->_out($s.'>>');
				$this->_out('endobj');
			}
			else
			{
				//Allow for additional types including TrueTypeUnicode
				$mtd='_put'.strtolower($type);
				if(!method_exists($this,$mtd))
					$this->Error('Unsupported font type: '.$type.' ('.$name.')');
				$this->$mtd($font);
			}
		}


	}
}




// Unicode fonts
function _puttruetypeunicode($font) {
			// Type0 Font
			// A composite font - a font composed of other fonts, organized hierarchically
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /Type0');
			$this->_out('/BaseFont /'.$font['name'].'');
			$this->_out('/Encoding /Identity-H'); //The horizontal identity mapping for 2-byte CIDs; may be used with CIDFonts using any Registry, Ordering, and Supplement values.
			$this->_out('/DescendantFonts ['.($this->n + 1).' 0 R]');
			$this->_out('>>');
			$this->_out('endobj');
			
			// CIDFontType2
			// A CIDFont whose glyph descriptions are based on TrueType font technology
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /CIDFontType2');
			$this->_out('/BaseFont /'.$font['name'].'');
			$this->_out('/CIDSystemInfo '.($this->n + 1).' 0 R'); 
			$this->_out('/FontDescriptor '.($this->n + 2).' 0 R');
			if (isset($font['desc']['MissingWidth'])){
				$this->_out('/DW '.$font['desc']['MissingWidth'].''); // The default width for glyphs in the CIDFont MissingWidth
			}
			$w = "";
			foreach ($font['cw'] as $cid => $width) {
				$w .= ''.$cid.' ['.$width.'] '; // define a specific width for each individual CID
			}
			$this->_out('/W ['.$w.']'); // A description of the widths for the glyphs in the CIDFont

		   if($font['ctg']) {
			$this->_out('/CIDToGIDMap '.($this->n + 3).' 0 R');
		   }

			$this->_out('>>');
			$this->_out('endobj');
			
			// CIDSystemInfo dictionary
			// A dictionary containing entries that define the character collection of the CIDFont.
			$this->_newobj();
			$this->_out('<</Registry (Adobe)'); // A string identifying an issuer of character collections
			$this->_out('/Ordering (UCS)'); // A string that uniquely names a character collection issued by a specific registry
			$this->_out('/Supplement 0'); // The supplement number of the character collection.
			$this->_out('>>');
			$this->_out('endobj');
			
			// Font descriptor
			// A font descriptor describing the CIDFont's default metrics other than its glyph widths
			$this->_newobj();
			$this->_out('<</Type /FontDescriptor');
			$this->_out('/FontName /'.$font['name']);
			foreach ($font['desc'] as $key => $value) {
				$this->_out('/'.$key.' '.$value);
			}
			if ($font['file']) {
				// A stream containing a TrueType font program
				$this->_out('/FontFile2 '.$this->FontFiles[$font['file']]['n'].' 0 R');
			}
			$this->_out('>>');
			$this->_out('endobj');

		   if($font['ctg']) {
			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			$this->_newobj();
			$ctgfile = $this->_getfontpath().$font['ctg'];
			if(!file_exists($ctgfile)) {
				$this->Error('Font file not found: '.$ctgfile);
			}
			$size = filesize($ctgfile);
			$this->_out('<</Length '.$size.'');
			if(substr($ctgfile, -2) == '.z') { // check file extension
				/* Decompresses data encoded using the public-domain 
				zlib/deflate compression method, reproducing the 
				original text or binary data */
				$this->_out('/Filter /FlateDecode');
			}
			$this->_out('>>');
			$this->_putstream(file_get_contents($ctgfile));
			$this->_out('endobj');
		   }

}


// from class PDF_Chinese CJK EXTENSIONS
function _putType0($font)
{
	//Type0
	$this->_out('/Subtype /Type0');
	$this->_out('/BaseFont /'.$font['name'].'-'.$font['CMap']);
	$this->_out('/Encoding /'.$font['CMap']);
	$this->_out('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_out('>>');
	$this->_out('endobj');
	//CIDFont
	$this->_newobj();
	$this->_out('<</Type /Font');
	$this->_out('/Subtype /CIDFontType0');
	$this->_out('/BaseFont /'.$font['name']);
	$this->_out('/CIDSystemInfo <</Registry (Adobe) /Ordering ('.$font['registry']['ordering'].') /Supplement '.$font['registry']['supplement'].'>>');
	$this->_out('/FontDescriptor '.($this->n+1).' 0 R');

	if (strpos($font['name'], ',')) {
		$family = substr($font['name'],0,strpos($font['name'], ','));	// Split off ,Bold etc.
	}
	else {
		$family = $font['name'];
	}
   if (($family == 'MSungStd-Light-Acro') || ($family  == 'STSongStd-Light-Acro')) { 	// BIG5 or GBK
	if($font['CMap']=='ETen-B5-H')
		$W='13648 13742 500';
	elseif($font['CMap']=='GBK-EUC-H')
		$W='814 907 500 7716 [500]';
	else
		$W='1 ['.implode(' ',$font['cw']).']';
	$this->_out('/W ['.$W.']>>');
   }

   if ($family == 'KozMinPro-Regular-Acro') {	// SHIFT_JIS
	$W='/W [1 [';
	foreach($font['cw'] as $w)
		$W.=$w.' ';
	$this->_out($W.'] 231 325 500 631 [500] 326 389 500]');
	$this->_out('>>');
   }

   if ($family == 'HYSMyeongJoStd-Medium-Acro') {	// UHC
	if($font['CMap']=='KSCms-UHC-HW-H')
		$W='8094 8190 500';
	else
		$W='1 ['.implode(' ',$font['cw']).']';
	$this->_out('/W ['.$W.']>>');
   }

	$this->_out('endobj');

	//Font descriptor
	$this->_newobj();
	$this->_out('<</Type /FontDescriptor');
	$this->_out('/FontName /'.$font['name']);
	$this->_out('/Flags 6');
	$this->_out('/FontBBox [0 -200 1000 900]');
	$this->_out('/ItalicAngle 0');
	$this->_out('/Ascent 800');
	$this->_out('/Descent -200');
	$this->_out('/CapHeight 800');
   if ($family == 'KozMinPro-Regular-Acro') {	// SHIFT_JIS
	$this->_out('/StemV 60');
   }
   else {	// GB. BIG5 and UHC
	$this->_out('/StemV 50');
   }
	$this->_out('>>');
	$this->_out('endobj');
}




function _putimages()
{
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	reset($this->images);
	while(list($file,$info)=each($this->images))
	{
		$this->_newobj();
		$this->images[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_out('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		$this->_out('/Filter /'.$info['f']);
		if(isset($info['parms']))
			$this->_out($info['parms']);
		if(isset($info['trns']) and is_array($info['trns']))
		{
			$trns='';
			for($i=0;$i<count($info['trns']);$i++)
				$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstream($info['data']);
		unset($this->images[$file]['data']);
		$this->_out('endobj');
		//Palette
		if($info['cs']=='Indexed')
		{
			$this->_newobj();
			$pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstream($pal);
			$this->_out('endobj');
		}
	}
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_UTF16BEtextstring('mPDF '.mPDF_VERSION));
	if(!empty($this->title))
		$this->_out('/Title '.$this->_UTF16BEtextstring($this->title));
	if(!empty($this->subject))
		$this->_out('/Subject '.$this->_UTF16BEtextstring($this->subject));
	if(!empty($this->author))
		$this->_out('/Author '.$this->_UTF16BEtextstring($this->author));
	if(!empty($this->keywords))
		$this->_out('/Keywords '.$this->_UTF16BEtextstring($this->keywords));
	if(!empty($this->creator))
		$this->_out('/Creator '.$this->_UTF16BEtextstring($this->creator));
	$this->_out('/CreationDate '.$this->_UTF16BEtextstring(date('YmdHis')));
}

function _putcatalog()
{
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')	$this->_out('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth') $this->_out('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')	$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))	$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
	if($this->LayoutMode=='single')	$this->_out('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')	$this->_out('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two') {
	  if ($this->useOddEven) { $this->_out('/PageLayout /TwoColumnRight'); }
	  else { $this->_out('/PageLayout /TwoColumnLeft'); }
	}
  //EDITEI - added lines below
  if(count($this->BMoutlines)>0)
  {
      $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
      $this->_out('/PageMode /UseOutlines');
  }
  if(is_int(strpos($this->DisplayPreferences,'FullScreen'))) $this->_out('/PageMode /FullScreen');
  if($this->DisplayPreferences || ($this->directionality == 'rtl'))
  {
     $this->_out('/ViewerPreferences<<');
     if(is_int(strpos($this->DisplayPreferences,'HideMenubar'))) $this->_out('/HideMenubar true');
     if(is_int(strpos($this->DisplayPreferences,'HideToolbar'))) $this->_out('/HideToolbar true');
     if(is_int(strpos($this->DisplayPreferences,'HideWindowUI'))) $this->_out('/HideWindowUI true');
     if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle'))) $this->_out('/DisplayDocTitle true');
     if(is_int(strpos($this->DisplayPreferences,'CenterWindow'))) $this->_out('/CenterWindow true');
     if(is_int(strpos($this->DisplayPreferences,'FitWindow'))) $this->_out('/FitWindow true');
     if($this->directionality == 'rtl') $this->_out('/Direction /R2L');
     $this->_out('>>');
  }
}

function _enddoc()
{
	$this->_putpages();
	$this->_putresources();
	//Info
	$this->_newobj();
	$this->_out('<<');
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');
	//Catalog
	$this->_newobj();
	$this->_out('<<');
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1; $i <= $this->n ; $i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
	$this->state=3;
}

function _beginpage($orientation)
{
	$this->page++;
	$this->pages[$this->page]='';
	$this->state=2;

	// My add
	$this->ResetMargins();

	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->FontFamily='';
	//Page orientation
	if(!$orientation)
		$orientation=$this->DefOrientation;
	else
	{
		$orientation=strtoupper($orientation{0});
		if($orientation!=$this->DefOrientation)
			$this->OrientationChanges[$this->page]=true;
	}
	if($orientation!=$this->CurOrientation)
	{
		//Change orientation
		if($orientation=='P')
		{
			$this->wPt=$this->fwPt;
			$this->hPt=$this->fhPt;
			$this->w=$this->fw;
			$this->h=$this->fh;
		}
		else
		{
			$this->wPt=$this->fhPt;
			$this->hPt=$this->fwPt;
			$this->w=$this->fh;
			$this->h=$this->fw;
		}
		$this->PageBreakTrigger=$this->h-$this->bMargin;
		$this->CurOrientation=$orientation;
	}
}

function _endpage()
{
	//End of page contents
// Removed in mPDF v1.2
//	if ($this->memory_opt) {	// mPDF1.1
//	   $this->pages[$this->page] = ($this->compress) ? gzcompress($this->pages[$this->page]) : $this->pages[$this->page];
//	}
	$this->state=1;
}

function _newobj()
{
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n]=strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
}

function _dounderline($x,$y,$txt)
{
	// Now print line exactly where $y secifies - called from Text() and Cell() - adjust  position there
	// WORD SPACING
      $w =($this->GetStringWidth($txt)*$this->k) + ($this->charspacing * mb_strlen( $txt, $this->mb_encoding )) 
		 + ( $this->ws * mb_substr_count( $txt, ' ', $this->mb_encoding ));
	return sprintf('%.2f %.2f %.2f %.2f re f',$x*$this->k,($this->h-$y)*$this->k,$w,0.05*$this->FontSizePt);
}

function _parsejpg($file)
{
	//Edit mPDF 1.1: first get remote file to local location
	if (!ini_get('allow_url_fopen') && preg_match('/^http.*?\/([^\/]*)$/',$file,$match)) {
		$localfile = '_tmpImage_'.$match[1];
		$ch = curl_init($file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$lFile = fopen( $localfile, 'w' );
      	curl_setopt ( $ch , CURLOPT_FILE , $lFile );
		$data = curl_exec($ch);
		curl_close($ch);
		fclose( $lFile );
	}
	else { $localfile = $file; }

	//Extract info from a JPEG file
	$a=GetImageSize($localfile);
	if(!$a) { if ($localfile != $file) unset($localfile); $this->Error('Missing or incorrect image file: '.$localfile); }
	if($a[2]!=2) { if ($localfile != $file) unset($localfile); $this->Error('Not a JPEG file: '.$localfile); }
	if(!isset($a['channels']) or $a['channels']==3)
		$colspace='DeviceRGB';
	elseif($a['channels']==4)
		$colspace='DeviceCMYK';
	else
		$colspace='DeviceGray';
	$bpc=isset($a['bits']) ? $a['bits'] : 8;
	//Read whole file
	$f=fopen($localfile,'rb');
	$data='';
	while(!feof($f))
		$data.=fread($f,4096);
	fclose($f);
	if ($localfile != $file) unset($localfile);
	return array('w'=>$a[0],'h'=>$a[1],'cs'=>$colspace,'bpc'=>$bpc,'f'=>'DCTDecode','data'=>$data);
}

function _parsepng($file)
{
	//Edit mPDF 1.1: first get remote file to local location
	if (!ini_get('allow_url_fopen') && preg_match('/^http.*?\/([^\/]*)$/',$file,$match)) {
		$localfile = '_tmpImage_'.$match[1];
		$ch = curl_init($file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$lFile = fopen( $localfile, 'w' );
      	curl_setopt ( $ch , CURLOPT_FILE , $lFile );
		$data = curl_exec($ch);
		curl_close($ch);
		fclose( $lFile );
	}
	else { $localfile = $file; }

	//Extract info from a PNG file
	$f=fopen($localfile,'rb');
	//Extract info from a PNG file
	if(!$f) { if ($localfile != $file) unset($localfile); $this->Error('Can\'t open image file: '.$localfile); }
	//Check signature
	if(fread($f,8)!=$this->chrs[137].'PNG'.$this->chrs[13].$this->chrs[10].$this->chrs[26].$this->chrs[10]) { if ($localfile != $file) unset($localfile); $this->Error('Not a PNG file: '.$localfile); }
	//Read header chunk
	fread($f,4);
	if(fread($f,4)!='IHDR') { if ($localfile != $file) unset($localfile); $this->Error('Incorrect PNG file: '.$localfile); }
	$w=$this->_freadint($f);
	$h=$this->_freadint($f);
	$bpc=$this->ords[fread($f,1)];
	if($bpc>8) { if ($localfile != $file) unset($localfile); $this->Error('16-bit depth not supported: '.$localfile); }
	$ct=$this->ords[fread($f,1)];
	if($ct==0) $colspace='DeviceGray';
	elseif($ct==2) $colspace='DeviceRGB';
	elseif($ct==3) $colspace='Indexed';
	else { if ($localfile != $file) unset($localfile); $this->Error('Alpha channel not supported: '.$localfile); }
	if($this->ords[fread($f,1)]!=0) { if ($localfile != $file) unset($localfile); $this->Error('Unknown compression method: '.$localfile); }
	if($this->ords[fread($f,1)]!=0) { if ($localfile != $file) unset($localfile); $this->Error('Unknown filter method: '.$localfile); }
	if($this->ords[fread($f,1)]!=0) { if ($localfile != $file) unset($localfile); $this->Error('Interlacing not supported: '.$localfile); }
	fread($f,4);
	$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
	//Scan chunks looking for palette, transparency and image data
	$pal='';
	$trns='';
	$data='';
	do
	{
		$n=$this->_freadint($f);
		$type=fread($f,4);
		if($type=='PLTE')
		{
			//Read palette
			$pal=fread($f,$n);
			fread($f,4);
		}
		elseif($type=='tRNS')
		{
			//Read transparency info
			$t=fread($f,$n);
			if($ct==0) $trns=array($this->ords[substr($t,1,1)]);
			elseif($ct==2) $trns=array($this->ords[substr($t,1,1)],$this->ords[substr($t,3,1)],$this->ords[substr($t,5,1)]);
			else
			{
				$pos=strpos($t,$this->chrs[0]);
				if(is_int($pos)) $trns=array($pos);
			}
			fread($f,4);
		}
		elseif($type=='IDAT')
		{
			//Read image data block
			$data.=fread($f,$n);
			fread($f,4);
		}
		elseif($type=='IEND')	break;
		else fread($f,$n+4);
	}
	while($n);
	if ($localfile != $file) unset($localfile);
	if($colspace=='Indexed' and empty($pal)) $this->Error('Missing palette in '.$localfile);
	fclose($f);
	return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
}

function _parsegif($file) //EDITEI - GIF support is now included
{ 
	//Edit mPDF 1.1: first get remote file to local location
	if (!ini_get('allow_url_fopen') && preg_match('/^http.*?\/([^\/]*)$/',$file,$match)) {
		$localfile = '_tmpImage_'.$match[1];
		$ch = curl_init($file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$lFile = fopen( $localfile, 'w' );
      	curl_setopt ( $ch , CURLOPT_FILE , $lFile );
		$data = curl_exec($ch);
		curl_close($ch);
		fclose( $lFile );
	}
	else { $localfile = $file; }

	//Function by Jérôme Fenal
	require_once(_MPDF_PATH .'gif.php'); //GIF class in pure PHP from Yamasoft (http://www.yamasoft.com/php-gif.zip)

	$h=0;
	$w=0;
	$gif=new CGIF();
	if (!$gif->loadFile($localfile, 0)) { if ($localfile != $file) unset($localfile); $this->Error("GIF parser: unable to open file $localfile"); }

	if($gif->m_img->m_gih->m_bLocalClr) {
		$nColors = $gif->m_img->m_gih->m_nTableSize;
		$pal = $gif->m_img->m_gih->m_colorTable->toString();
		if($bgColor != -1) {
			$bgColor = $this->m_img->m_gih->m_colorTable->colorIndex($bgColor);
		}
		$colspace='Indexed';
	} elseif($gif->m_gfh->m_bGlobalClr) {
		$nColors = $gif->m_gfh->m_nTableSize;
		$pal = $gif->m_gfh->m_colorTable->toString();
		if((isset($bgColor)) and $bgColor != -1) {
			$bgColor = $gif->m_gfh->m_colorTable->colorIndex($bgColor);
		}
		$colspace='Indexed';
	} else {
		$nColors = 0;
		$bgColor = -1;
		$colspace='DeviceGray';
		$pal='';
	}

	$trns='';
	if($gif->m_img->m_bTrans && ($nColors > 0)) {
		$trns=array($gif->m_img->m_nTrans);
	}

	$data=$gif->m_img->m_data;
	$w=$gif->m_gfh->m_nWidth;
	$h=$gif->m_gfh->m_nHeight;

	if ($localfile != $file) unset($localfile);
	if($colspace=='Indexed' and empty($pal))
		$this->Error('Missing palette in '.$file);

	if ($this->compress) {
		$data=gzcompress($data);
		return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'f'=>'FlateDecode', 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
	} else {
		return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
	} 
}

function _freadint($f)
{
	//Read a 4-byte integer from file
	$i=$this->ords[fread($f,1)]<<24;
	$i+=$this->ords[fread($f,1)]<<16;
	$i+=$this->ords[fread($f,1)]<<8;
	$i+=$this->ords[fread($f,1)];
	return $i;
}

function _UTF16BEtextstring($s) {
	$s = $this->UTF8ToUTF16BE($s, true);
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->n), $s);
	}
	return '('. $this->_escape($s).')';
}



function _escape($s)
{
	// the chr(13) substitution fixes the Bugs item #1421290.
	return strtr($s, array(')' => '\\)', '(' => '\\(', '\\' => '\\\\', $this->chrs[13] => '\r'));
}

function _putstream($s) {
	if ($this->encrypted) {
		$s = $this->_RC4($this->_objectkey($this->n), $s);
	}
	$this->_out('stream');
	$this->_out($s);
	$this->_out('endstream');
}


function _out($s)
{
	//Add a line to the document]
	if($this->state==2) {
	   // Added mPDF 1.2 HTML headers and Footers - saves to buffer when writeHTMLHeader/Footer
	   if ($this->bufferoutput) {
		$this->headerbuffer.= $s."\n";
	   }
	   else if (($this->ColActive) && (!$this->processingHeader) && (!$this->processingFooter)) {
		// Captures everything in buffer for columns; Almost everything is sent from fn. Cell() except:
		// Images sent from Image() or
		// later sent as _out($textto) in printbuffer
		// Line()
		if (preg_match('/q \d+\.\d\d 0 0 (\d+\.\d\d) \d+\.\d\d \d+\.\d\d cm \/I\d+ Do Q/',$s,$m)) {	// Image data
			$h = ($m[1]/$this->k);
			// Update/overwrite the lowest bottom of printing y value for a column
			$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y+$h;
		}
		else { 	// Td Text Set in Cell()
			$h = $this->ColDetails[$this->CurrCol]['bottom_margin'] - $this->y; 
		}
		if ($h < 0) { $h = -$h; }
		$this->columnbuffer[] = array(
		's' => $s,							/* Text string to output */
		'col' => $this->CurrCol, 				/* Column when printed */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* this->y when printed (after column break) */
		'h' => $h						 	/* actual y at bottom when printed = y+h */
		);
	   }
	   else if (($this->table_rotate) && (!$this->processingHeader) && (!$this->processingFooter)) {
		// Captures eveything in buffer for rotated tables; 
		$this->tablebuffer[] = array(
		's' => $s,							/* Text string to output */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* y when printed (after column break) */
		);
	   }
	// Added mPDF 1.1 keeping block together on one page
	   else if (($this->keep_block_together) && (!$this->processingHeader) && (!$this->processingFooter)) {
		// Captures eveything in buffer; 
		if (preg_match('/q \d+\.\d\d 0 0 (\d+\.\d\d) \d+\.\d\d \d+\.\d\d cm \/I\d+ Do Q/',$s,$m)) {	// Image data
			$h = ($m[1]/$this->k);
			// Update/overwrite the lowest bottom of printing y value for Keep together block
			$this->ktBlock[$this->page]['bottom_margin'] = $this->y+$h;
		}
		else { 	// Td Text Set in Cell()
			$h = $this->ktBlock[$this->page]['bottom_margin'] - $this->y; 
		}
		if ($h < 0) { $h = -$h; }
		$this->divbuffer[] = array(
		'page' => $this->page,
		's' => $s,							/* Text string to output */
		'x' => $this->x, 						/* x when printed */
		'y' => $this->y,					 	/* y when printed (after column break) */
		'h' => $h						 	/* actual y at bottom when printed = y+h */
		);
	   }
	   else {
		$this->pages[$this->page] .= $s."\n";
	   }

	}
	else {
		$this->buffer .= $s."\n";
	}
}

// add a watermark 
function watermark( $texte, $angle=45, $fontsize=96, $alpha=0.2 )
{

	if (!$this->watermark_font) { $this->watermark_font = $this->default_font; }
      $this->SetFont( $this->watermark_font, "B", $fontsize, false );	// Don't output
	$texte= $this->purify_utf8_text($texte);
	if ($this->text_input_as_HTML) {
		$texte= $this->all_entities_to_utf8($texte);
	}
	if (!$this->isunicode || $this->isCJK) { $texte = mb_convert_encoding($texte,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($texte);

	$this->SetAlpha($alpha);

	$this->SetTextColor(0);
	$szfont = $fontsize;
	$loop   = 0;
	$maxlen = (min($this->w,$this->h) );	// sets max length of text as 7/8 width/height of page
	while ( $loop == 0 )
	{
       $this->SetFont( $this->watermark_font, "B", $szfont, false );	// Don't output
	 $offset =  ((sin(deg2rad($angle))) * ($szfont/$this->k));

       $strlen = $this->GetStringWidth($texte);
       if ( $strlen > $maxlen - $offset  )
          $szfont --;
       else
          $loop ++;
	}

	$this->SetFont( $this->watermark_font, "B", $szfont-0.1, true, true);	// Output The -0.1 is because SetFont above is not written to PDF
											// Repeating it will not output anything as mPDF thinks it is set
	$adj = ((cos(deg2rad($angle))) * ($strlen/2));
	$opp = ((sin(deg2rad($angle))) * ($strlen/2));
	$wx = ($this->w/2) - $adj + $offset/3;
	$wy = ($this->h/2) + $opp;
	$this->Rotate($angle,$wx,$wy);
	$this->Text($wx,$wy,$texte);
	$this->Rotate(0);
	$this->SetTextColor(0,0,0);

	$this->SetAlpha(1);

}

function Rotate($angle,$x=-1,$y=-1)
{
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0)
	{
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*$this->k;
		$cy=($this->h-$y)*$this->k;
		$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}

// From Invoice
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
	$k = $this->k;
	$hp = $this->h;
	if($style=='F')
		$op='f';
	elseif($style=='FD' or $style=='DF')
		$op='B';
	else
		$op='S';
	$MyArc = 4/3 * (sqrt(2) - 1);
	$this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
	$xc = $x+$w-$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

	$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
	$xc = $x+$w-$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
	$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
	$xc = $x+$r ;
	$yc = $y+$h-$r;
	$this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
	$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
	$xc = $x+$r ;
	$yc = $y+$r;
	$this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
	$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
	$this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
	$h = $this->h;
	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
						$x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}

// Label and number of invoice/estimate
function shaded_box( $text,$font='',$fontstyle='B',$szfont='',$width='70%',$style='DF',$radius=2.5,$fill='#FFFFFF',$color='#000000',$pad=2 )
{
// F (shading - no line),S (line, no shading),DF (both)
	if (!$font) { $font= $this->default_font; }
	if (!$szfont) { $szfont = ($this->default_font_size * 1.8); }

	$text = $this->purify_utf8_text($text);
	if ($this->text_input_as_HTML) {
		$text = $this->all_entities_to_utf8($text);
	}
	if (!$this->isunicode || $this->isCJK) { $text = mb_convert_encoding($text,$this->mb_encoding,'UTF-8'); }
	// DIRECTIONALITY
	$this->magic_reverse_dir($text);
	$text = ' '.$text.' ';
	if (!$width) { $width = $this->pgwidth; } else { $width=ConvertSize($width,$this->pgwidth); }
	$midpt = $this->lMargin+($this->pgwidth/2);
	$r1  = $midpt-($width/2);		//($this->w / 2) - 40;
	$r2  = $r1 + $width; 		//$r1 + 80;
	$y1  = $this->y;


	$mid = ($r1 + $r2 ) / 2;
	$loop   = 0;
    
	while ( $loop == 0 )
	{
		$this->SetFont( $font, $fontstyle, $szfont );
		$sz = $this->GetStringWidth( $text );
		if ( ($r1+$sz) > $r2 )
			$szfont --;
		else
			$loop ++;
	}

	$y2  = $this->FontSize+($pad*2);

	$this->SetLineWidth(0.1);
	$fc = ConvertColor($fill);
	$tc = ConvertColor($color);
	$this->SetFillColor($fc['R'],$fc['G'],$fc['B']);
	$this->SetTextColor($tc['R'],$tc['G'],$tc['B']);
	$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, $radius, $style);
	$this->SetX( $r1);
	$this->Cell($r2-$r1, $y2, $text, 0, 1, "C" );
	$this->SetY($y1+$y2+2);	// +2 = mm margin below shaded box
	$this->Reset();
}





/**
* Converts UTF-8 strings to codepoints array.<br>
 * @author Nicola Asuni
* @since 1.53.0.TC005 (2005-01-05)
*/
function UTF8StringToArray($str) {
			$unicode = array(); // array containing unicode values
			$bytes  = array(); // array containing single character byte sequences
			$numbytes  = 1; // number of octetc needed to represent the UTF-8 character
			
			$str .= ""; // force $str to be a string
			$length = strlen($str);
			
			for($i = 0; $i < $length; $i++) {
				$char = $this->ords[$str{$i}]; // get one string character at time
				if(count($bytes) == 0) { // get starting octect
					if ($char <= 0x7F) {
						$unicode[] = $char; // use the character "as is" because is ASCII
						$numbytes = 1;
					} elseif (($char >> 0x05) == 0x06) { // 2 bytes character (0x06 = 110 BIN)
						$bytes[] = ($char - 0xC0) << 0x06; 
						$numbytes = 2;
					} elseif (($char >> 0x04) == 0x0E) { // 3 bytes character (0x0E = 1110 BIN)
						$bytes[] = ($char - 0xE0) << 0x0C; 
						$numbytes = 3;
					} elseif (($char >> 0x03) == 0x1E) { // 4 bytes character (0x1E = 11110 BIN)
						$bytes[] = ($char - 0xF0) << 0x12; 
						$numbytes = 4;
					} else {
						// use replacement character for other invalid sequences
						$unicode[] = 0xFFFD;
						$bytes = array();
						$numbytes = 1;
					}
				} elseif (($char >> 0x06) == 0x02) { // bytes 2, 3 and 4 must start with 0x02 = 10 BIN
					$bytes[] = $char - 0x80;
					if (count($bytes) == $numbytes) {
						// compose UTF-8 bytes to a single unicode value
						$char = $bytes[0];
						for($j = 1; $j < $numbytes; $j++) {
							$char += ($bytes[$j] << (($numbytes - $j - 1) * 0x06));
						}
						if ((($char >= 0xD800) AND ($char <= 0xDFFF)) OR ($char >= 0x10FFFF)) {
							/* The definition of UTF-8 prohibits encoding character numbers between
							U+D800 and U+DFFF, which are reserved for use with the UTF-16
							encoding form (as surrogate pairs) and do not directly represent
							characters. */
							$unicode[] = 0xFFFD; // use replacement character
						}
						else {
							$unicode[] = $char; // add char to array
						}
						// reset data for next char
						$bytes = array(); 
						$numbytes = 1;
					}
				} else {
					// use replacement character for other invalid sequences
					$unicode[] = 0xFFFD;
					$bytes = array();
					$numbytes = 1;
				}
			}
			return $unicode;
}



/**
* Converts UTF-8 strings to UTF16-BE.
*/
function UTF8ToUTF16BE($str, $setbom=true) {
			$outstr = ""; // string to be returned
			$unicode = $this->UTF8StringToArray($str); // array containing UTF-8 unicode values
			$numitems = count($unicode);
			
			if ($setbom) {
				$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
			}
			foreach($unicode as $char) {
				if($char == 0xFFFD) {
					$outstr .= "\xFF\xFD"; // replacement character
				} elseif ($char < 0x10000) {
					$outstr .= $this->chrs[$char >> 0x08];
					$outstr .= $this->chrs[$char & 0xFF];
				} else {
					$char -= 0x10000;
					$w1 = 0xD800 | ($char >> 0x10);
					$w2 = 0xDC00 | ($char & 0x3FF);	
					$outstr .= $this->chrs[$w1 >> 0x08];
					$outstr .= $this->chrs[$w1 & 0xFF];
					$outstr .= $this->chrs[$w2 >> 0x08];
					$outstr .= $this->chrs[$w2 & 0xFF];
				}
			}
			return $outstr;
}




function _getfontpath() {
	if(!defined('FPDF_FONTPATH') AND is_dir(dirname(__FILE__).'/font')) {
		define('FPDF_FONTPATH', dirname(__FILE__).'/font/');
	}
	return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
}




// ====================================================
// ====================================================
// from class PDF_Chinese CJK EXTENSIONS

var $Big5_widths;
var $GB_widths;
var $SJIS_widths;
var $UHC_widths;



function AddCIDFont($family,$style,$name,$cw,$CMap,$registry)
{
	$fontkey=strtolower($family).strtoupper($style);
	if(isset($this->fonts[$fontkey]))
		$this->Error("Font already added: $family $style");
	$i=count($this->fonts)+1;
	$name=str_replace(' ','',$name);
	if ($family == 'sjis') { $up = -120; } else { $up = -130; }
	$this->fonts[$fontkey]=array('i'=>$i,'type'=>'Type0','name'=>$name,'up'=>$up,'ut'=>40,'cw'=>$cw,'CMap'=>$CMap,'registry'=>$registry);
}

function AddCIDFonts($family,$name,$cw,$CMap,$registry)
{
	$this->AddCIDFont($family,'',$name,$cw,$CMap,$registry);
	$this->AddCIDFont($family,'B',$name.',Bold',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'I',$name.',Italic',$cw,$CMap,$registry);
	$this->AddCIDFont($family,'BI',$name.',BoldItalic',$cw,$CMap,$registry);
}

function AddCJKFont($family) {
	if ($family == 'big5') { $this->AddBig5Font(); }
	else if ($family == 'big5-hw') { $this->AddBig5hwFont(); }
	else if ($family == 'gb') { $this->AddGBFont(); }
	else if ($family == 'gb-hw') { $this->AddGBhwFont(); }
	else if ($family == 'sjis') { $this->AddSJISFont(); }
	else if ($family == 'sjis-hw') { $this->AddSJIShwFont(); }
	else if ($family == 'uhc') { $this->AddUHCFont(); }
	else if ($family == 'uhc-hw') { $this->AddUHChwFont(); }
}

function AddBig5Font()
{
	//Add Big5 font with proportional Latin
	$family='big5';
	$name='MSungStd-Light-Acro';
	$cw=$this->Big5_widths;
	$CMap='ETenms-B5-H';
	$registry=array('ordering'=>'CNS1','supplement'=>0);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddBig5hwFont()
{
	//Add Big5 font with half-width Latin
	$family='big5-hw';
	$name='MSungStd-Light-Acro';
	for($i=32;$i<=126;$i++) $cw[$this->chrs[$i]]=500;
	$CMap='ETen-B5-H';
	$registry=array('ordering'=>'CNS1','supplement'=>0);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddGBFont()
{
	//Add GB font with proportional Latin
	$family='gb';
	$name='STSongStd-Light-Acro';
	$cw=$this->GB_widths;
	$CMap='GBKp-EUC-H';
	$registry=array('ordering'=>'GB1','supplement'=>2);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddGBhwFont()
{
	//Add GB font with half-width Latin
	$family='gb-hw';
	$name='STSongStd-Light-Acro';
	for($i=32;$i<=126;$i++) $cw[$this->chrs[$i]]=500;
	$CMap='GBK-EUC-H';
	$registry=array('ordering'=>'GB1','supplement'=>2);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddSJISFont()
{
	//Add SJIS font with proportional Latin
	$family='sjis';
	$name='KozMinPro-Regular-Acro';
	$cw=$this->SJIS_widths;
	$CMap='90msp-RKSJ-H';
	$registry=array('ordering'=>'Japan1','supplement'=>2);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddSJIShwFont()
{
	//Add SJIS font with half-width Latin
	$family='sjis-hw';
	$name='KozMinPro-Regular-Acro';
	for($i=32;$i<=126;$i++) $cw[$this->chrs[$i]]=500;
	$CMap='90ms-RKSJ-H';
	$registry=array('ordering'=>'Japan1','supplement'=>2);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddUHCFont()
{
	//Add UHC font with proportional Latin
	$family='uhc';
	$name='HYSMyeongJoStd-Medium-Acro';
	$cw=$this->UHC_widths;
	$CMap='KSCms-UHC-H';
	$registry=array('ordering'=>'Korea1','supplement'=>1);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}

function AddUHChwFont()
{
	//Add UHC font with half-witdh Latin
	$family='uhc-hw';
	$name='HYSMyeongJoStd-Medium-Acro';
	for($i=32;$i<=126;$i++) $cw[$this->chrs[$i]]=500;
	$CMap='KSCms-UHC-HW-H';
	$registry=array('ordering'=>'Korea1','supplement'=>1);
	$this->AddCIDFonts($family,$name,$cw,$CMap,$registry);
}






//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////



function SetDefaultFont($font) {
	// Disallow embedded fonts to be used as defaults except in win-1252
	if ($this->codepage != 'win-1252') {
		if (strtolower($font) == 'times') { $font = 'serif'; }
		if (strtolower($font) == 'courier') { $font = 'monospace'; }
		if ((strtolower($font) == 'arial') || (strtolower($font) == 'helvetica')) { $font = 'sans-serif'; }
	}
  	$font = $this->SetFont($font);	// returns substituted font if necessary
	$this->default_font = $font;
	$this->original_default_font = $font;
	if (!$this->watermark_font ) { $this->watermark_font = $font; }
	$this->setSubstitutions(GetSubstitutions($this->codepage,$this->default_font));
}

function SetDefaultFontSize($fontsize) {
	$this->default_font_size = $fontsize;
	$this->original_default_font_size = $fontsize;
	$this->SetFontSize($fontsize);
	// Added mPDF 1.1 allows SetDefaultFont to override that set in defaultCSS
	$this->defaultCSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
}

function SetDirectionality($dir='ltr') {
	if (strtolower($dir) == 'rtl') { 
		$this->directionality = 'rtl'; 
		$this->defaultAlign = 'R';
		$this->defaultTableAlign = 'R';
		// Swop L/R Margins so page 1 RTL is an 'even' page
		$tmp = $this->DeflMargin;
		$this->DeflMargin = $this->DefrMargin; 
		$this->DefrMargin = $tmp; 
		$this->SetMargins($this->DeflMargin,$this->DefrMargin,$this->tMargin);
	}
	else  { 
		$this->directionality = 'ltr'; 
		$this->defaultAlign = 'L';
		$this->defaultTableAlign = 'L';
	}
}

function reverse_align(&$align) {
	if (strtolower($align) == 'right') { $align = 'left'; }
	else if (strtolower($align) == 'left') { $align = 'right'; }
	if (strtoupper($align) == 'R') { $align = 'L'; }
	else if (strtoupper($align) == 'L') { $align = 'R'; }
}


// Added to set line-height-correction
function SetLineHeightCorrection($val) {
	if ($val > 0) { $this->default_lineheight_correction = $val; }
	else { $this->default_lineheight_correction = 1.2; }
}

// Added to Set the lineheight - either to named fontsize(pts) or default
function SetLineHeight($FontPt='',$spacing = '') {
   if ($spacing > 0) { 
	if ($FontPt) { $this->lineheight = (($FontPt/2.834) *$spacing); }
	else { $this->lineheight = (($this->FontSizePt/2.834) *$spacing); }
   }
   else {
	if ($FontPt) { $this->lineheight = (($FontPt/2.834) *$this->default_lineheight_correction); }
	else { $this->lineheight = (($this->FontSizePt/2.834) *$this->default_lineheight_correction); }
   }
}




function setBasePath($str)
{
  $str .= 'htm';	// in case $str ends in / e.g. http://www.bbc.co.uk/
  $this->basepath = dirname($str) . "/";	// returns e.g. e.g. http://www.google.com/dir1/dir2/dir3/
  $this->basepath = str_replace("\\","/",$this->basepath); //If on Windows
}

function ShowNOIMG_GIF($opt=true)
{
  $this->shownoimg=$opt;
}

function UseCSS($opt=true)
{
  $this->usecss=$opt;
}

function UseTableHeader($opt=true)
{
  $this->usetableheader=$opt;
}

function UsePRE($opt=true)
{
  $this->usepre=$opt;
}

// Added mPDF 1.3
function docPageNum($num = 0) {
	if ($num < 1) { $num = $this->page; }
	$type = '1';	// set default decimal
	$ppgno = $num;
	$suppress = 0;
	foreach($this->PageNumSubstitutions AS $psarr) {
		if ($num >= $psarr['from']) {
			if ($psarr['reset']) { $ppgno = $num - $psarr['from'] + 1; }
			if ($psarr['type']) { $type = $psarr['type']; }
			if (strtoupper($psarr['suppress'])=='ON') { $suppress = 1; }
			else if (strtoupper($psarr['suppress'])=='OFF') { $suppress = 0; }
		}
	}
	if ($suppress) { return ''; }
	if ($type=='A') { $ppgno = dec2alpha($ppgno,true); }
	else if ($type=='a') { $ppgno = dec2alpha($ppgno,false);}
	else if ($type=='I') { $ppgno = dec2roman($ppgno,true); }
	else if ($type=='i') { $ppgno = dec2roman($ppgno,false); }
	return $ppgno;
}

//Page header
function Header($content='')
{
  $this->processingHeader=true;

  $h = $this->headerDetails;
  if(count($h)) {

	$this->y = ($this->margin_header);
	$this->SetTextColor(0);
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	$pgno = $this->docPageNum($this->page); 

	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
	  }
	}
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if ($h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		else { $hff = $this->default_font; }
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
		$hfst = $h[$side][$pos]['font-style'];
		if (!$hfst) { $hfst = ''; }
		$this->SetFont($hff,$hfst,$hfsz);
		$this->x = $this->lMargin;
		//$this->y = $this->margin_header + ($hfsz*0.5/$this->k);
		$this->y = $this->margin_header;

		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if (!$this->isunicode || $this->isCJK) { $hd = mb_convert_encoding($hd,$this->mb_encoding,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd);
		$align = $pos;
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}
		$this->Cell(0,$maxfontheight/$this->k ,$hd,0,0,$align,0,'',0,0,0,'M');
	  }
	}
	//Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);
	// LINE
	if ($h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDrawColor(0);
		$this->Line($this->lMargin , $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/$this->k), $this->lMargin+$this->pgwidth, $this->margin_header + ($maxfontheight*(1+$this->header_line_spacing)/$this->k) );
	}
  }
  $this->SetY($this->tMargin);
  if ($this->ColActive) { $this->pgwidth = $this->ColWidth; }

  $this->processingHeader=false;
}



function TableHeader($content='',$tablestartpage='',$tablestartcolumn ='') {
  if($this->usetableheader and $content != '')
  {
    $y = $this->y;
	//OUTER FILL BGCOLOR of DIVS
	if ($this->blklvl > 0) {
	  $firstblockfill = $this->GetFirstBlockFill();
	  if ($firstblockfill && $this->blklvl >= $firstblockfill) {
		$divh = $content[0]['h'];
		$bak_x = $this->x;
		for ($blvl=$firstblockfill;$blvl<=$this->blklvl;$blvl++) {
			$this->SetBlockFill($blvl);
			$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
			$this->Cell( ($this->blk[$blvl]['width']), $divh, '', '', 0, '', 1);
		}
		// Reset current block fill
		$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
		$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
		$this->x = $bak_x;
	  }
	}

    foreach($content as $tableheader)
    {
      $this->y = $y;
      //Set some cell values
      $x = $tableheader['x'];
	if (($this->useOddEven) && ($tablestartpage == 'ODD') && (($this->page)%2==0)) {	// EVEN
		$x = $x +$this->MarginCorrection;
	}
	else if (($this->useOddEven) && ($tablestartpage == 'EVEN') && (($this->page)%2==1)) {	// ODD
		$x = $x +$this->MarginCorrection;
	}
	// Added to correct for Columns
	if ($this->ColActive) {
	   if ($this->directionality == 'rtl') {
		$x -= ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);
	   }
	   else {
		$x += ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth+$this->ColGap);
	   }
	}

      $w = $tableheader['w'];
      $h = $tableheader['h'];
      $va = $tableheader['va'];
	// Edited mPDF 1.3 for rotated text in cell
      $R = $tableheader['R'];
      $mih = $tableheader['mih'];
      $fill = $tableheader['bgcolor'];
      $border = $tableheader['border'];
      $border_details = $tableheader['border_details'];
	$this->tabletheadjustfinished = true;

      $align = $tableheader['a'];
      //Align
      $this->divalign=$align;
	$this->x = $x;
	//Vertical align
	if (!isset($va) || $va=='M') $this->y += ($h-$mih)/2;
      elseif (isset($va) && $va=='B') $this->y += $h-$mih;
	if ($fill)
      {
 		$color = ConvertColor($fill);
 		$this->SetFillColor($color['R'],$color['G'],$color['B']);
 		$this->Rect($x, $y, $w, $h, 'F');
	}
   	//Border
  	$this->_tableRect($x, $y, $w, $h, $border, $border_details);
 	//Print cell content
      $this->divwidth = $w;	// originally $w-2
      $this->divheight = $this->table_lineheight*$this->lineheight;
      $textbuffer = $tableheader['textbuffer'];
      if (!empty($textbuffer)) {

		// Edited mPDF 1.3 for rotated text in cell
		if ($R) {
					$cellPtSize = $textbuffer[0][11] / $this->shrin_k;
					$cellFontHeight = ($cellPtSize/$this->k);
					$opx = $this->x;
					$opy = $this->y;
					$angle = INTVAL($R);
					// Only allow 45 - 90 degrees (when bottom-aligned) or -90
					if ($angle > 90) { $angle = 90; }
					else if ($angle > 0 && (isset($va) && $va!='B')) { $angle = 90; }
					else if ($angle > 0 && $angle <45) { $angle = 45; }
					else if ($angle < 0) { $angle = -90; }
					$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
					if (!isset($align) || $align =='R') { 
						$this->x += ($w) + ($offset) - ($cellFontHeight/3) - ($this->cellPaddingR + $this->cMarginR); 
					}
					else if (!isset($align ) || $align =='C') { 
						$this->x += ($w/2) + ($offset); 
					}
					else { 
						$this->x += ($offset) + ($cellFontHeight/3)+($this->cellPaddingL + $this->cMarginL); 
					}
					$str = ltrim(implode(' ',$tableheader['text']));
					$str = mb_rtrim($str ,$this->mb_encoding);

					if (!isset($va) || $va=='M') { 
						$this->y -= ($h-$mih)/2; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += (($h-$mih)/2)+($this->cellPaddingT + $this->cMarginT) + ($mih-($this->cellPaddingT + $this->cMarginT+$this->cMarginB+$this->cellPaddingB)); }
						else if ($angle < 0) { $this->y += (($h-$mih)/2)+($this->cellPaddingT + $this->cMarginT); }
					}
					else if (isset($va) && $va=='B') { 
						$this->y -= $h-$mih; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += $h-($this->cMarginB+$this->cellPaddingB); }
						else if ($angle < 0) { $this->y += $h-$mih+($this->cellPaddingT + $this->cMarginT); }
					}
					else if (isset($va) && $va=='T') { 
						if ($angle > 0) { $this->y += $mih-($this->cMarginB+$this->cellPaddingB); }
						else if ($angle < 0) { $this->y += ($this->cellPaddingT + $this->cMarginT); }
					}

					$this->Rotate($angle,$this->x,$this->y);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($textbuffer[0][4],$textbuffer[0][2],$cellPtSize,true,true);
					$this->Text($this->x,$this->y,$str);
					$this->Rotate(0);
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$this->x = $opx;
					$this->y = $opy;
		}
		else {
			$this->y += $this->cellPaddingT+$this->cMarginT;
			$this->printbuffer($textbuffer,'',true/*inside a table*/);
			$this->y -= $this->cellPaddingT+$this->cMarginT;
		}


	}
      $textbuffer = array();
    }
    $this->y = $y + $h; //Update y coordinate
  }//end of 'if usetableheader ...'
}

// Added mPDF 1.2 HTML headers and Footers
function setHTMLHeader($html='',$OE='') {
	if ($OE == 'E') {
		$this->HTMLHeaderE = $html;
	}
	else {
		$this->HTMLHeader = $html;
	}
	$this->headerDetails = array();	// override and clear any other header/footer
}

function setHTMLFooter($html='',$OE='') {
	if ($OE == 'E') {
		$this->HTMLFooterE = $html;
	}
	else {
		$this->HTMLFooter = $html;
	}
	$this->footerDetails = array();	// override and clear any other header/footer
}

// Called internally from function Close() at end of document
function writeHTMLHeaders() {
	$html = $this->purify_utf8_text($this->HTMLHeader);
	if ($this->text_input_as_HTML) {
		$html = $this->all_entities_to_utf8($html);
	}
	if (!$this->isunicode || $this->isCJK) { $html = mb_convert_encoding($html,$this->mb_encoding,'UTF-8'); }
	$this->magic_reverse_dir($html);
	if ($this->HTMLHeaderE) {
		$htmlE = $this->purify_utf8_text($this->HTMLHeaderE);
		if ($this->text_input_as_HTML) {
			$htmlE = $this->all_entities_to_utf8($htmlE);
		}
		if (!$this->isunicode || $this->isCJK) { $htmlE = mb_convert_encoding($htmlE,$this->mb_encoding,'UTF-8'); }
		$this->magic_reverse_dir($htmlE);
	}
	else { $htmlE=''; }
	// SET MARGINS + TOP, LEFT position (ODD / EVEN)
	$numpages = count($this->pages);
	for($i = 1;$i<=$numpages;$i++) {
		if ($this->useOddEven && ($i)%2==0) {	// EVEN
			$this->lMargin=$this->DefrMargin;
			$this->rMargin=$this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;
			$usehtml = $htmlE;
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$this->lMargin=$this->DeflMargin;
			$this->rMargin=$this->DefrMargin;
			if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
			$usehtml = $html;
		}
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->PageBreakTrigger=$this->h;
		$this->x=$this->lMargin;
		$this->y = $this->margin_header;
		$this->FontFamily='';
		$printpageno = $this->docPageNum($i);
		$hd = str_replace('{PAGENO}',$printpageno,$usehtml);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		$this->writeHTML($hd , 4);	// parameter 4 saves output to $this->headerbuffer
		$this->pages[$i] = $this->headerbuffer . $this->pages[$i] ;
	}
}

function writeHTMLFooters() {
	// Added mPDF 1.3 as flag to prevent page triggering in footers containing table
	$this->InHTMLFooter = true;
	$html = $this->purify_utf8_text($this->HTMLFooter);
	if ($this->text_input_as_HTML) {
		$html = $this->all_entities_to_utf8($html);
	}
	if (!$this->isunicode || $this->isCJK) { $html = mb_convert_encoding($html,$this->mb_encoding,'UTF-8'); }
	$this->magic_reverse_dir($html);
	if ($this->HTMLFooterE) {
		$htmlE = $this->purify_utf8_text($this->HTMLFooterE);
		if ($this->text_input_as_HTML) {
			$htmlE = $this->all_entities_to_utf8($htmlE);
		}
		if (!$this->isunicode || $this->isCJK) { $htmlE = mb_convert_encoding($htmlE,$this->mb_encoding,'UTF-8'); }
		$this->magic_reverse_dir($htmlE);
	}
	else { $htmlE=''; }
	$numpages = count($this->pages);
	for($i = 1;$i<=$numpages;$i++) {
		if ($this->useOddEven && ($i)%2==0) {	// EVEN
			$this->lMargin=$this->DefrMargin;
			$this->rMargin=$this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin-$this->DeflMargin;
			$usehtml = $htmlE;
		}
		else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$this->lMargin=$this->DeflMargin;
			$this->rMargin=$this->DefrMargin;
			if ($this->useOddEven) { $this->MarginCorrection = $this->DeflMargin-$this->DefrMargin; }
			$usehtml = $html;
		}
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->PageBreakTrigger=$this->h;
		$this->x=$this->lMargin;
		$this->y=($this->h - $this->margin_footer);
		$this->FontFamily='';
		$printpageno = $this->docPageNum($i);
		$hd = str_replace('{PAGENO}',$printpageno,$usehtml);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		$this->writeHTML($hd , 4);	// parameter 4 saves output to $this->headerbuffer
		$this->pages[$i] .= $this->headerbuffer;
	}
}



function setHeader($Harray=array()) {
  if (is_string($Harray)) {
    if (strpos($Harray,'|')) {
	$hdet = explode('|',$Harray);
	$this->headerDetails = array (
  		'odd' => array (
	'L' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'R' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'R' => array ('content' => $hdet[0], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'C' => array ('content' => $hdet[1], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'L' => array ('content' => $hdet[2], 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
    else {
	$this->headerDetails = array (
  		'odd' => array (
	'R' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
  		),
  		'even' => array (
	'L' => array ('content' => $Harray, 'font-size' => $this->defaultheaderfontsize, 'font-style' => $this->defaultheaderfontstyle),
	'line' => $this->defaultheaderline,
		)
	);
    }
  }
  else if (is_array($Harray)) {
	$this->headerDetails = $Harray;
  }
}

function setFooter($Farray=array()) {
  if (is_string($Farray)) {
    if (strpos($Farray,'|')) {
	$fdet = explode('|',$Farray);
	$this->footerDetails = array (
		'odd' => array (
	'L' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'R' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'R' => array ('content' => $fdet[0], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'C' => array ('content' => $fdet[1], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'L' => array ('content' => $fdet[2], 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
    else {
	$this->footerDetails = array (
		'odd' => array (
	'R' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		),
		'even' => array (
	'L' => array ('content' => $Farray, 'font-size' => $this->defaultfooterfontsize, 'font-style' => $this->defaultfooterfontstyle),
	'line' => $this->defaultfooterline,
		)
	);
    }
  }
  else if (is_array($Farray)) {
	$this->footerDetails = $Farray;
  }
}


function setUnvalidatedText($txt) {
	$this->UnvalidatedText = $txt;
}


//Page footer
function Footer() {

  $this->processingHeader=true;
  $this->ResetMargins();	// necessary after columns in Reference (Index)
  $this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
  if (($this->UnvalidatedText) && ($this->TopicIsUnvalidated)) {
		$this->watermark( $this->UnvalidatedText, 45, 120 );	// Watermark (angle/fontsize)
  }

  $h = $this->footerDetails;
  if(count($h)) {

	$this->SetY(-$this->margin_footer);
	$this->SetTextColor(0);
    	$this->SUP = false;
	$this->SUB = false;
	$this->bullet = false;

	// only show pagenumber if numbering on
	$pgno = $this->docPageNum($this->page); 

	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			$side = 'even';
	}
	else {	// ODD	// OR NOT MIRRORING MARGINS/FOOTERS = DEFAULT
			$side = 'odd';
	}
	$maxfontheight = 0;
	// LEFT-CENTER-RIGHT
	foreach(array('L','C','R') AS $pos) {
	  if ($h[$side][$pos]['content']) {
		$hd = str_replace('{PAGENO}',$pgno,$h[$side][$pos]['content']);
		$hd = preg_replace('/\{DATE\s+(.*?)\}/e',"date('\\1')",$hd);
		if ($h[$side][$pos]['font-family']) { $hff = $h[$side][$pos]['font-family']; }
		else { $hff = $this->default_font; }
		if ($h[$side][$pos]['font-size']) { $hfsz = $h[$side][$pos]['font-size']; }
		else { $hfsz = $this->default_font_size; }
		$maxfontheight = max($maxfontheight,$hfsz);
		if ($h[$side][$pos]['font-style']) { $hfst = $h[$side][$pos]['font-style']; }
		else { $hfst = ''; }
		$this->SetFont($hff,$hfst,$hfsz);
		$this->x = $this->lMargin;
		$hd = $this->purify_utf8_text($hd);
		if ($this->text_input_as_HTML) {
			$hd = $this->all_entities_to_utf8($hd);
		}
		// CONVERT CODEPAGE
		if (!$this->isunicode || $this->isCJK) { $hd = mb_convert_encoding($hd,$this->mb_encoding,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($hd);
		$align = $pos;
		if ($this->directionality == 'rtl') { 
			if ($pos == 'L') { $align = 'R'; }
			else if ($pos == 'R') { $align = 'L'; }
		}

		$this->Cell(0,0,$hd,0,0,$align);
	  }
	}
	//Return Font to normal
	$this->SetFont($this->default_font,'',$this->original_default_font_size);

	// LINE
	if ($h[$side]['line']) { 
		$this->SetLineWidth(0.1);
		$this->SetDrawColor(0);
		$this->Line($this->lMargin , $this->y-(($maxfontheight*$this->footer_line_spacing/$this->k)+($this->FontSize*0.4)) , $this->lMargin+$this->pgwidth, $this->y-(($maxfontheight*$this->footer_line_spacing/$this->k)+($this->FontSize*0.4)));
	}
  }
  $this->processingHeader=false;

}



///////////////////
/// HTML parser ///
///////////////////
function WriteHTML($html,$sub=0) {	// $sub ADDED - 0 = default; 1=headerCSS only; 2=HTML body only; 3 - HTML parses only
	// mPDF 1.2 added $sub = 4 used to buffer output for HTML Headers and Footers
	if($this->state==0) $this->AddPage();

	if ($this->allow_charset_conversion) {
		if ($sub < 2) { 
			if ($sub == 1) { $html = '<style> '.$html.' </style>'; }	// stylesheet only
			$this->ReadCharset($html); 
		}
		if ($this->charset_in) { 
			$success = iconv($this->charset_in,'UTF-8//TRANSLIT',$html); 
			if ($success) { $html = $success; }
		}
	}

	$html = $this->purify_utf8($html,false);

	$this->blklvl = 0;
	$this->lastblocklevelchange = 0;
	$this->blk = array();
	$this->blk[0]['width'] =& $this->pgwidth;
	$this->blk[0]['inner_width'] =& $this->pgwidth;

	if ($sub < 2) { 
		$this->ReadMetaTags($html); 
		if ($this->usecss) { 
			$html = $this->ReadCSS($html); 
			// SET Blocklevel[0] CSS if defined in <body> or from default
			$properties = $this->MergeCSS('','BODY','');
			$this->setCSS($properties,'BLOCK','BODY'); 
		}
		if ($sub == 1) { return ''; }
		if (preg_match('/<body.*?>(.*?)<\/body>/ism',$html,$m)) { $html = $m[1]; }
	}


	// Edited mPDF 1.2 HTML headers and Footers
	$this->parseonly = false; 
	$this->bufferoutput = false; 
	if ($sub == 3) { 
		$this->parseonly = true; 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->textbuffer=array();
	} 
	else if ($sub == 4) { 
		$this->bufferoutput = true; 
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
		// Output any text left in buffer
		if (count($this->textbuffer)) { $this->printbuffer($this->textbuffer); }
		$this->textbuffer=array();
		$this->headerbuffer='';
	} 

	mb_internal_encoding('UTF-8'); 

	$html = AdjustHTML($html,$this->directionality,$this->usepre); //Try to make HTML look more like XHTML

	$html=str_replace('<?','< ',$html); //Fix '<?XML' bug from HTML code generated by MS Word
	$html = $this->SubstituteChars($html);
	// Don't allow non-breaking spaces that are converted to substituted chars or will break anyway and mess up table width calc.
	$html = str_replace('<tta>160</tta>',$this->chrs[32],$html); 
	$html = str_replace('</tta><tta>','|',$html); 
	$html = str_replace('</tts><tts>','|',$html); 
	$html = str_replace('</ttz><ttz>','|',$html); 

	//Add new supported tags in the DisableTags function
	$html=strip_tags($html,$this->enabledtags); //remove all unsupported tags, but the ones inside the 'enabledtags' string

	//Explode the string in order to parse the HTML code
	$a=preg_split('/<(.*?)>/ums',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	// ? more accurate regexp that allows e.g. <a name="Silly <name>">
	// if changing - also change in fn.SubstituteChars()
	// $a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ums', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

	if ($this->mb_encoding) { 
		mb_internal_encoding($this->mb_encoding); 
	}

	foreach($a as $i => $e) {

		if($i%2==0) {
		//TEXT
			if (strlen($e) == 0) { continue; }

			$e = strcode2utf($e);	
			$e = lesser_entity_decode($e);


			// CONVERT CODEPAGE
			if (!$this->isunicode || $this->isCJK) { $e = mb_convert_encoding($e,$this->mb_encoding,'UTF-8'); }
			if (($this->isunicode && !$this->isCJK) && (!$this->usingembeddedfonts)) {
				if ($this->toupper) { $e = mb_strtoupper($e,$this->mb_encoding); }
				if ($this->tolower) { $e = mb_strtolower($e,$this->mb_encoding); }
			}
			else if (!$this->isCJK) {
				if ($this->toupper) { $e = strtoupper($e); }
				if ($this->tolower) { $e = strtolower($e); }
			}
			if (($this->tts) || ($this->ttz) || ($this->tta)) {
				$es = explode('|',$e);
				$e = '';
				foreach($es AS $val) {
					$e .= $this->chrs[$val];
				}
			}
			//Adjust lineheight
      		//$this->SetLineHeight($this->FontSizePt); //should be inside printbuffer? // does nothing

			//  FORM ELEMENTS
  			if ($this->specialcontent) {
			   //SELECT tag (form element)
			   if ($this->specialcontent == "type=select") { 
				$e = ltrim($e); 
				$stringwidth = $this->GetStringWidth($e);
				if (!isset($this->selectoption['MAXWIDTH']) or $stringwidth > $this->selectoption['MAXWIDTH']) { $this->selectoption['MAXWIDTH'] = $stringwidth; }
				if (!isset($this->selectoption['SELECTED']) or $this->selectoption['SELECTED'] == '') { $this->selectoption['SELECTED'] = $e; }
			   }
			   // TEXTAREA
			   else { 
				$objattr = unserialize($this->specialcontent);
				$objattr['text'] = $e;
				$te = "»¤¬type=textarea,objattr=".serialize($objattr)."»¤¬";
				if ($this->tdbegin) {
	  				$this->cell[$this->row][$this->col]['textbuffer'][] = array($te,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
				}
				else {
					$this->textbuffer[] = array($te,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
				}
			   }
		      }
			// TABLE
			else if ($this->tablestart) {
				if ($this->tdbegin) {
     				   if ($this->ignorefollowingspaces and !$this->ispre) { $e = ltrim($e); }
				   if ($e) {
				    if ($this->blockjustfinished) {
	  				$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
  					$this->cell[$this->row][$this->col]['text'][] = "\n";
					if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
						$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
					}
					elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
						$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
					}
					$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);// reset
					$this->blockjustfinished=false;

				    }
	  				$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
  					$this->cell[$this->row][$this->col]['text'][] = $e;
					// Edited mPDF 1.3 for rotated text in cell
            			if (!$this->cell[$this->row][$this->col]['R']) {
						$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e);
					}
				   }
				}
			}
			// ALL ELSE
			else {
     				if ($this->ignorefollowingspaces and !$this->ispre) { $e = ltrim($e); }
				if ($e) $this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize); 
			}
		}


		else { // TAG **
		   if($e{0}=='/') { // END TAG
		    // Check for tags where HTML specifies optional end tags,
    		    // and/or does not allow nesting e.g. P inside P, or 
		    $endtag = strtoupper(substr($e,1));
		    if ($this->allow_html_optional_endtags && !$this->parseonly) {
			if (($endtag == 'DIV' || $endtag =='FORM' || $endtag =='CENTER') && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'OL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'LI' && $endtag == 'UL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DD' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'DT' && $endtag == 'DL') { $this->CloseTag($this->lastoptionaltag ); }
			if ($this->lastoptionaltag == 'OPTION' && $endtag == 'SELECT') { $this->CloseTag($this->lastoptionaltag ); }
			if ($endtag == 'TABLE') {
				if ($this->lastoptionaltag == 'THEAD' || $this->lastoptionaltag == 'TBODY' || $this->lastoptionaltag == 'TFOOT') { 
					$this->CloseTag($this->lastoptionaltag);
				}
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'THEAD' || $endtag == 'TBODY' || $endtag == 'TFOOT') { 
				if ($this->lastoptionaltag == 'TR') { $this->CloseTag('TR'); }
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); $this->CloseTag('TR'); }
			}
			if ($endtag == 'TR') {
				if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }
			}
		    }
		    $this->CloseTag($endtag); 
		   }

		   else {	// OPENING TAG
			$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
      		$e = preg_replace($regexp,"=\"\$1\"",$e);
			$regexp = '| (\\w+?)=([^\\s>"]+)|si'; // changes anykey=anyvalue to anykey="anyvalue" (only do this inside tags)
      		$e = preg_replace($regexp," \$1=\"\$2\"",$e);


      		//Fix path values, if needed
			if ((stristr($e,"href=") !== false) or (stristr($e,"src=") !== false) ) {
				$regexp = '/ (href|src)="(.*?)"/i';
				preg_match($regexp,$e,$auxiliararray);
				$path = $auxiliararray[2];
				$path = str_replace("\\","/",$path); //If on Windows
				//Get link info and obtain its absolute path
				$regexp = '|^./|';
				$path = preg_replace($regexp,'',$path);
				if($path{0} != '#') { //It is not an Internal Link
				  if (strpos($path,"../") !== false ) { //It is a Relative Link
					$backtrackamount = substr_count($path,"../");
					$maxbacktrack = substr_count($this->basepath,"/") - 1;
					$filepath = str_replace("../",'',$path);
					$path = $this->basepath;
					//If it is an invalid relative link, then make it go to directory root
					if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
					//Backtrack some directories
					for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
					$path = $path . "/" . $filepath; //Make it an absolute path
				  }
				  elseif( strpos($path,":/") === false || strpos($path,":/") > 10) //It is a Local Link
				  {
					if (substr($path,0,1) == "/") { 
						$tr = parse_url($this->basepath);
						$root = $tr['scheme'].'://'.$tr['host'];
						$path = $root . $path; 
					}
					else { $path = $this->basepath . $path; }
				  }
				  //Do nothing if it is an Absolute Link
				}
				$regexp = '/ (href|src)="(.*?)"/i';
				$e = preg_replace($regexp,' \\1="'.$path.'"',$e);
			}//END of Fix path values


			//Extract attributes
			$contents=array();
			preg_match_all('/\\S*=["\'][^"\']*["\']/',$e,$contents);
			preg_match('/\\S+/',$e,$a2);
			$tag=strtoupper($a2[0]);
			$attr=array();
			if (!empty($contents)) {
				foreach($contents[0] as $v) {
  					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3)) {
					  if (strtoupper($a3[1])=='ID' || strtoupper($a3[1])=='CLASS' || strtoupper($a3[1])=='STYLE') {
    						$attr[strtoupper($a3[1])]=trim(strtoupper($a3[2]));
					  }
					  else {
    						$attr[strtoupper($a3[1])]=trim($a3[2]);
					  }
     					}
  				}
			}
			$this->OpenTag($tag,$attr);
		   }

		} // end TAG
	} //end of	foreach($a as $i=>$e)

	// Close any open block tags
	for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }

	// Output any text left in buffer
	if (count($this->textbuffer) && !$this->parseonly) { $this->printbuffer($this->textbuffer); }
	if (!$this->parseonly) $this->textbuffer=array();

	//Create Internal Links, if needed
	if (!empty($this->internallink) ) {
		foreach($this->internallink as $k=>$v) {
			if (strpos($k,"#") !== false ) { continue; } //ignore
			$ypos = $v['Y'];
			$pagenum = $v['PAGE'];
			$sharp = "#";
			while (array_key_exists($sharp.$k,$this->internallink)) {
				$internallink = $this->internallink[$sharp.$k];
				$this->SetLink($internallink,$ypos,$pagenum);
				$sharp .= "#";
			}
		}
	}

	$this->linemaxfontsize = '';
	$this->lineheight_correction = $this->default_lineheight_correction;
}



// NEW FUNCTION FOR BORDER-DETAILS
function border_details($bd) {
	$prop = explode(' ',trim($bd));
	if ( count($prop) == 1 ) { 
		$bsize = ConvertSize($prop[0],$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
		if ($bsize > 0) {
			return array('s' => '1', 'w' => $bsize, 'c' => array('R'=>0,'G'=>0,'B'=>0), 'style'=>'solid');
		}
		else { return array(); }
	}
	if ( count($prop) != 3 ) { return array(); } 
	// Change #000000 1px solid to 1px solid #000000 (proper)
	if ($prop[0]{0} == '#') { $tmp = $prop[0]; $prop[0] = $prop[1]; $prop[1] = $prop[2]; $prop[2] = $tmp; }
	// Size
	$bsize = ConvertSize($prop[0]);
	//color
	$coul = ConvertColor($prop[2]);	// returns array
	// Style
	$prop[1] = strtolower($prop[1]);
	if ((($prop[1] == 'solid') || ($prop[1] == 'dashed') || ($prop[1] == 'dotted')) && ($bsize > 0)) { $on = '1'; } 
	else { $on = '0'; $bsize = ''; $coul = ''; $style = ''; }
	return array('s' => $on, 'w' => $bsize, 'c' => $coul, 'style'=> $prop[1] );
}


// NEW FUNCTION FOR CSS MARGIN or PADDING called from SetCSS
function fixCSS($prop) {
	if (!is_array($prop) || (count($prop)==0)) return array(); 
	$newprop = array(); 
	foreach($prop AS $k => $v) {
		if ($k == 'MARGIN') {
			$tmp =  $this->margin_padding_expand($v);
			$newprop['MARGIN-TOP'] = $tmp['T'];
			$newprop['MARGIN-RIGHT'] = $tmp['R'];
			$newprop['MARGIN-BOTTOM'] = $tmp['B'];
			$newprop['MARGIN-LEFT'] = $tmp['L'];
		}
		else if ($k == 'PADDING') {
			$tmp =  $this->margin_padding_expand($v);
			$newprop['PADDING-TOP'] = $tmp['T'];
			$newprop['PADDING-RIGHT'] = $tmp['R'];
			$newprop['PADDING-BOTTOM'] = $tmp['B'];
			$newprop['PADDING-LEFT'] = $tmp['L'];
		}
		else if ($k == 'BORDER') {
			if ($v == '1') { $v = '1px solid #000000'; }
			// Added mPDF 1.3
			if (preg_match('/ none /i',$v)) { continue; }
			$newprop['BORDER-TOP'] = $v;
			$newprop['BORDER-RIGHT'] = $v;
			$newprop['BORDER-BOTTOM'] = $v;
			$newprop['BORDER-LEFT'] = $v;
		}
		else { 
			$newprop[$k] = $v; 
		}
	}
	return $newprop;
}

function margin_padding_expand($mp) {
	$prop = explode(' ',trim($mp));
	if (count($prop) == 1 ) { 
		return array('T' => $prop[0], 'R' => $prop[0], 'B' => $prop[0], 'L'=> $prop[0]);
	}
	if (count($prop) == 2 ) { 
		return array('T' => $prop[0], 'R' => $prop[1], 'B' => $prop[0], 'L'=> $prop[1]);
	}
	if (count($prop) == 4 ) { 
		return array('T' => $prop[0], 'R' => $prop[1], 'B' => $prop[2], 'L'=> $prop[3]);
	}
	return array(); 
}


function MergeCSS($inherit,$tag,$attr) {
	// Extensively Rewritten in mPDF 1.2 
		$properties = array();
		$zproperties = array(); 

		//===============================================
		// Set Inherited properties
		if ($inherit == 'TABLE') {	// $tag = TABLE

		//===============================================
		// Save Cascading CSS e.g. "div.topic p" at this block level
		if ($this->cascadeCSS[$tag]) {
		   $carry = $this->cascadeCSS['cascadeCSS'][$tag];
		   if ($this->tablecascadeCSS) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		}
		if (isset($attr['CLASS'])) {
			if ($this->cascadeCSS['CLASS>>'.$attr['CLASS']]) {
		   		$carry = $this->cascadeCSS['CLASS>>'.$attr['CLASS']];
		   		if ($this->tablecascadeCSS) {
					$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		 		}
		 		else {
					$this->tablecascadeCSS = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']]) {
		   		$carry = $this->cascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']];
		   		if ($this->tablecascadeCSS) {
					$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		 		}
		 		else {
					$this->tablecascadeCSS = $carry;
				}
			}
		}
		if (isset($attr['ID'])) {
			if ($this->cascadeCSS['ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS['ID>>'.$attr['ID']];
		   		if ($this->tablecascadeCSS) {
					$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		 		}
		 		else {
					$this->tablecascadeCSS = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']]) {
		   		$carry = $this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']];
		   		if ($this->tablecascadeCSS) {
					$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		 		}
		 		else {
					$this->tablecascadeCSS = $carry;
				}
			}
		}
		//===============================================
		// Cascading forward CSS e.g. "table.topic td" for this table in $this->tablecascadeCSS 
		//===============================================
		// STYLESHEET TAG e.g. table
		if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag]) && !$this->blk[$this->blklvl]['cascadeCSS'][$tag]['depth']) { 
		   $carry = $this->blk[$this->blklvl]['cascadeCSS'][$tag];
		   if ($this->tablecascadeCSS) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']]) && !$this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']]['depth']) { 
		   $carry = $this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']];
		   if ($this->tablecascadeCSS ) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']]['depth']) { 
		   $carry = $this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']];
		   if ($this->tablecascadeCSS ) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]) && !$this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]['depth']) { 
		   $carry = $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']];
		   if ($this->tablecascadeCSS ) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		  }
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']) { 
		   $carry = $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
		   if ($this->tablecascadeCSS ) {
			$this->tablecascadeCSS = array_merge_recursive_unique($this->tablecascadeCSS, $carry);
		   }
		   else {
			$this->tablecascadeCSS = $carry;
		   }
		  }
		}
		//===============================================
		}
		//===============================================
		// Set Inherited properties
		if ($inherit == 'BLOCK') {

		//===============================================
		// Save Cascading CSS e.g. "div.topic p" at this block level
		if ($this->cascadeCSS[$tag]) {
			$carry =  $this->cascadeCSS[$tag];
			if ($this->blk[$this->blklvl]['cascadeCSS']) {
				$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   	}
		   	else {
				$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
			}
		}
		if (isset($attr['CLASS'])) {
			if ($this->cascadeCSS['CLASS>>'.$attr['CLASS']]) {
				$carry =  $this->cascadeCSS['CLASS>>'.$attr['CLASS']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']]) {
				$carry =  $this->cascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
		}
		if (isset($attr['ID'])) {
			if ($this->cascadeCSS['ID>>'.$attr['ID']]) {
				$carry =  $this->cascadeCSS['ID>>'.$attr['ID']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
			if ($this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']]) {
				$carry =  $this->cascadeCSS[$tag.'>>ID>>'.$attr['ID']];
				if ($this->blk[$this->blklvl]['cascadeCSS']) {
					$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   		}
		   		else {
					$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
				}
			}
		}
		//===============================================
		// Cascading forward CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag]['depth']) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']]) && !$this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']]['depth']) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]['depth']) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]['depth']) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && !$this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']) { 
		   $carry = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
		   if ($this->blk[$this->blklvl]['cascadeCSS']) {
			$this->blk[$this->blklvl]['cascadeCSS'] = array_merge_recursive_unique($this->blk[$this->blklvl]['cascadeCSS'], $carry);
		   }
		   else {
			$this->blk[$this->blklvl]['cascadeCSS'] = $carry;
		   }
		  }
		}
		//===============================================
		  // Block properties
		  if ($this->blk[$this->blklvl-1]['margin_collapse']) { $properties['MARGIN-COLLAPSE'] = 'COLLAPSE'; }	// custom tag, but follows CSS principle that border-collapse is inherited
		  if ($this->blk[$this->blklvl-1]['line_height']) { $properties['LINE-HEIGHT'] = $this->blk[$this->blklvl-1]['line_height']; }	
		  if ($this->blk[$this->blklvl-1]['align']) { 
			if ($this->blk[$this->blklvl-1]['align'] == 'L') { $properties['TEXT-ALIGN'] = 'left'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'J') { $properties['TEXT-ALIGN'] = 'justify'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'R') { $properties['TEXT-ALIGN'] = 'right'; } 
			else if ($this->blk[$this->blklvl-1]['align'] == 'C') { $properties['TEXT-ALIGN'] = 'center'; } 
		  }
		  if ($this->blk[$this->blklvl-1]['bgcolor']) { // Doesn't officially inherit, but default value is transparent (?=inherited)
			$cor = $this->blk[$this->blklvl-1]['bgcolorarray' ];
			$properties['BACKGROUND-COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
		  }

		// Text characterisics (and text-indent) are only inherited by P or DIV blocks
//		  if ($this->blk[$this->blklvl]['tag'] == 'P' || $this->blk[$this->blklvl]['tag'] == 'DIV') {

		    if ($this->blk[$this->blklvl-1]['TEXT-INDENT']) { $properties['TEXT-INDENT'] = $this->blk[$this->blklvl-1]['TEXT-INDENT']; }
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'family' ]) {
			$properties['FONT-FAMILY'] = $this->blk[$this->blklvl-1]['InlineProperties'][ 'family' ];
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'I' ]) {
			$properties['FONT-STYLE'] = 'italic';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'sizePt' ]) {
			$properties['FONT-SIZE'] = $this->blk[$this->blklvl-1]['InlineProperties'][ 'sizePt' ] . 'pt';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'B' ]) {
			$properties['FONT-WEIGHT'] = 'bold';
		    }
   		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'colorarray' ]) {
			$cor = $this->blk[$this->blklvl-1]['InlineProperties'][ 'colorarray' ];
			$properties['COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
		    }
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'toupper' ]) {
			$properties['TEXT-TRANSFORM'] = 'uppercase';
		    }
		    else if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'tolower' ]) {
			$properties['TEXT-TRANSFORM'] = 'lowercase';
		    }
			// CSS says text-decoration is not inherited, but IE7 does??
		    if ($this->blk[$this->blklvl-1]['InlineProperties'][ 'underline' ]) {
			$properties['TEXT-DECORATION'] = 'underline';
		    }
//		  }

		}
		//===============================================
		// Set Inherited properties
		if ($inherit == 'LIST') {
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'family' ]) {
			$properties['FONT-FAMILY'] = $this->blk[$this->blklvl]['InlineProperties'][ 'family' ];
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'I' ]) {
			$properties['FONT-STYLE'] = 'italic';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'sizePt' ]) {
			$properties['FONT-SIZE'] = $this->blk[$this->blklvl]['InlineProperties'][ 'sizePt' ] . 'pt';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'B' ]) {
			$properties['FONT-WEIGHT'] = 'bold';
		    }
   		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'colorarray' ]) {
			$cor = $this->blk[$this->blklvl]['InlineProperties'][ 'colorarray' ];
			$properties['COLOR'] = 'RGB('.$cor['R'].','.$cor['G'].','.$cor['B'].')';
		    }
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'toupper' ]) {
			$properties['TEXT-TRANSFORM'] = 'uppercase';
		    }
		    else if ($this->blk[$this->blklvl]['InlineProperties'][ 'tolower' ]) {
			$properties['TEXT-TRANSFORM'] = 'lowercase';
		    }
			// CSS says text-decoration is not inherited, but IE7 does??
		    if ($this->blk[$this->blklvl]['InlineProperties'][ 'underline' ]) {
			$properties['TEXT-DECORATION'] = 'underline';
		    }
		    if ($this->list_lineheight[$this->listlvl]) { 
			$properties['LINE-HEIGHT'] = $this->list_lineheight[$this->listlvl]; 
		    }
		}
		//===============================================
		// DEFAULT for this TAG set in DefaultCSS
		if (isset($this->defaultCSS[$tag])) { 
			$zproperties = $this->fixCSS($this->defaultCSS[$tag]);
			if (($this->directionality == 'rtl') && ($this->rtlCSS == 0)) { 
				$this->reverse_align($zproperties['TEXT-ALIGN']);
				$pl =  $zproperties['PADDING-LEFT'];
				$pr =  $zproperties['PADDING-RIGHT'];
				if ($pl || $pr) { $zproperties['PADDING-RIGHT'] = $pl; $zproperties['PADDING-LEFT'] = $pr; }
				$ml =  $zproperties['MARGIN-LEFT'];
				$mr =  $zproperties['MARGIN-RIGHT'];
				if ($ml || $mr) { $zproperties['MARGIN-RIGHT'] = $ml; $zproperties['MARGIN-LEFT'] = $mr; }
				$bl =  $zproperties['BORDER-LEFT'];
				$br =  $zproperties['BORDER-RIGHT'];
				if ($bl || $br) { $zproperties['BORDER-RIGHT'] = $bl; $zproperties['BORDER-LEFT'] = $br; }
			}
			if (is_array($zproperties)) { $properties = array_merge($zproperties,$properties); }	// Inherited overwrites default
		}
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->CSS[$tag])) { 
			$zproperties = $this->CSS[$tag];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zproperties = $this->CSS['CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET ID e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zproperties = $this->CSS['ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. p.smallone{}  div.redletter{}
		if (isset($attr['CLASS'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zproperties = $this->CSS[$tag.'>>CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. p#smallone{}  div#redletter{}
		if (isset($attr['ID'])) {
			// Edited mPDF 1.2 to allow tag, class and ID to be distinct
			$zproperties = $this->CSS[$tag.'>>ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
	// Cascaded e.g. div.class p only works for block level
	if ($inherit == 'BLOCK') {
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']]) && $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl-1]['cascadeCSS']['CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]) && $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl-1]['cascadeCSS']['ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl-1]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
	}
	else if ($inherit == 'TABLE') {	// NB looks at current blklvl not previous one for cascading CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag]) && $this->blk[$this->blklvl]['cascadeCSS'][$tag]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl]['cascadeCSS'][$tag];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']]) && $this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl]['cascadeCSS']['CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']]) && $this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl]['cascadeCSS']['ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]) && $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]) && $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->blk[$this->blklvl]['cascadeCSS'][$tag.'>>ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
	}
	else if ($inherit == 'TR' || $inherit == 'TH' || $inherit == 'TD') { // NB looks at $this->tablecascadeCSS for cascading CSS
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->tablecascadeCSS[$tag]) && $this->tablecascadeCSS[$tag]['depth']>1) { 
			$zproperties = $this->tablecascadeCSS[$tag];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->tablecascadeCSS['CLASS>>'.$attr['CLASS']]) && $this->tablecascadeCSS['CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->tablecascadeCSS['CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS['ID>>'.$attr['ID']]) && $this->tablecascadeCSS['ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->tablecascadeCSS['ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		if (isset($attr['CLASS'])) {
		  if (isset($this->tablecascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']]) && $this->tablecascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']]['depth']>1) { 
			$zproperties = $this->tablecascadeCSS[$tag.'>>CLASS>>'.$attr['CLASS']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
		  if (isset($this->tablecascadeCSS[$tag.'>>ID>>'.$attr['ID']]) && $this->tablecascadeCSS[$tag.'>>ID>>'.$attr['ID']]['depth']>1) { 
			$zproperties = $this->tablecascadeCSS[$tag.'>>ID>>'.$attr['ID']];
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		  }
		}
		//===============================================
	}
		//===============================================
		if (($this->directionality == 'rtl') && ($this->rtlCSS == 1)) { $this->reverse_align($properties['TEXT-ALIGN']); }
		//===============================================
		// INLINE STYLE e.g. style="CSS:property"
		if (isset($attr['STYLE'])) {
			$zproperties = $this->readInlineCSS($attr['STYLE']);
			if (is_array($zproperties)) { $properties = array_merge($properties,$zproperties); }
		}
		//===============================================
		if (($this->directionality == 'rtl') && ($this->rtlCSS == 2)) { $this->reverse_align($properties['TEXT-ALIGN']); }
		//===============================================
		// INLINE ATTRIBUTES e.g. .. ALIGN="CENTER">
		if (isset($attr['COLOR']) and $attr['COLOR']!='') {
			$properties['COLOR'] = $attr['COLOR'];
		}
	  if ($tag != 'INPUT') {
		if (isset($attr['WIDTH']) and $attr['WIDTH']!='') {
			$properties['WIDTH'] = $attr['WIDTH'];
		}
		if (isset($attr['HEIGHT']) and $attr['HEIGHT']!='') {
			$properties['HEIGHT'] = $attr['HEIGHT'];
		}
	  }
	  if ($tag == 'FONT') {
		if (isset($attr['FACE'])) {
			$properties['FONT-FAMILY'] = $attr['FACE'];
		}
		if (isset($attr['SIZE']) and $attr['SIZE']!='') {
			$s = '';
			if ($attr['SIZE'] === '+1') { $s = '120%'; }
			else if ($attr['SIZE'] === '-1') { $s = '86%'; }
			else if ($attr['SIZE'] === '1') { $s = 'XX-SMALL'; }
			else if ($attr['SIZE'] == '2') { $s = 'X-SMALL'; }
			else if ($attr['SIZE'] == '3') { $s = 'SMALL'; }
			else if ($attr['SIZE'] == '4') { $s = 'MEDIUM'; }
			else if ($attr['SIZE'] == '5') { $s = 'LARGE'; }
			else if ($attr['SIZE'] == '6') { $s = 'X-LARGE'; }
			else if ($attr['SIZE'] == '7') { $s = 'XX-LARGE'; }
			if ($s) $properties['FONT-SIZE'] = $s;
		}
	  }
		if (isset($attr['VALIGN']) and $attr['VALIGN']!='') {
			$properties['VERTICAL-ALIGN'] = $attr['VALIGN'];
		}
		if (isset($attr['VSPACE']) and $attr['VSPACE']!='') {
			$properties['MARGIN-TOP'] = $attr['VSPACE'];
			$properties['MARGIN-BOTTOM'] = $attr['VSPACE'];
		}
		if (isset($attr['HSPACE']) and $attr['HSPACE']!='') {
			$properties['MARGIN-LEFT'] = $attr['HSPACE'];
			$properties['MARGIN-RIGHT'] = $attr['HSPACE'];
		}
		//===============================================

		return $properties;
}



function OpenTag($tag,$attr)
{
  // What this gets: < $tag $attr['WIDTH']="90px" > does not get content here </closeTag here>
  // Correct tags where HTML specifies optional end tags,
  // and/or does not allow nesting e.g. P inside P, or 
  if ($this->allow_html_optional_endtags) {
    if (($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'UL' || $tag == 'OL' || $tag == 'TABLE' || $tag=='PRE' || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DL' || $tag == 'HR' ) && $this->lastoptionaltag == 'P') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DD' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DD') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'DT' && $this->lastoptionaltag == 'DT') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'LI' && $this->lastoptionaltag == 'LI') { $this->CloseTag($this->lastoptionaltag ); }
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag ); }
    if (($tag == 'TD' || $tag == 'TH') && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TR') { $this->CloseTag($this->lastoptionaltag ); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TD') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }
    if ($tag == 'TR' && $this->lastoptionaltag == 'TH') { $this->CloseTag($this->lastoptionaltag );  $this->CloseTag('TR'); $this->CloseTag('THEAD'); }
    if ($tag == 'OPTION' && $this->lastoptionaltag == 'OPTION') { $this->CloseTag($this->lastoptionaltag ); }
  }

  if ($tag == 'INPUT' && $attr['TYPE'] == 'HIDDEN') { $this->ignorefollowingspaces=true; return; }

  $align = array('left'=>'L','center'=>'C','right'=>'R','top'=>'T','text-top'=>'T','middle'=>'M','baseline'=>'M','bottom'=>'B','text-bottom'=>'B','justify'=>'J');

  $this->ignorefollowingspaces=false;

  //Opening tag
  switch($tag){


     case 'INDEXENTRY': //added custom-tag
	if ($attr['CONTENT']) {
		$this->Reference($attr['CONTENT']);
	}
	break;

     case 'BOOKMARK': //added custom-tag
	if ($attr['CONTENT']) {
		if ($attr['LEVEL']) { $bklevel = $attr['LEVEL']; } else { $bklevel = 0; }
		$this->Bookmark($attr['CONTENT'],$bklevel,'-1');
	}
	break;

     case 'TOCENTRY': //added custom-tag
	if ($attr['CONTENT']) {
		if ($attr['LEVEL']) { $toclevel = $attr['LEVEL']; } else { $toclevel = 0; }
		$this->TOC_Entry($attr['CONTENT'],$toclevel);
	}
	break;

     case 'TOC': //added custom-tag - set Marker for insertion later of ToC
	if ($attr['FONT-SIZE']) { $tocfontsize = $attr['FONT-SIZE']; } else { $tocfontsize = ''; }
	if ($attr['FONT']) { $tocfont = $attr['FONT']; } else { $tocfont = ''; }
	if ($attr['INDENT']) { $tocindent = $attr['INDENT']; } else { $tocindent = ''; }
	if ($attr['RESETPAGENUM']) { $resetpagenum = $attr['RESETPAGENUM']; } else { $resetpagenum = ''; }
	if ($attr['PAGENUMSTYLE']) { $pagenumstyle = $attr['PAGENUMSTYLE']; } else { $pagenumstyle= ''; }
	if ($attr['SUPPRESS']) { $suppress = $attr['SUPPRESS']; } else { $suppress = ''; }
	$this->TOC($tocfont,$tocfontsize,$tocindent,$resetpagenum, $pagenumstyle, $suppress);
	break;

    case 'COLUMNS': //added custom-tag
	if ($attr['COLUMN-COUNT']) {
		// Close any open block tags
		for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }

		if ($attr['VALIGN']) { 
			if ($attr['VALIGN'] == 'J') { $valign = 'J'; }
			else { $valign = $align[$attr['VALIGN']]; }
		}
 		else { $valign = ''; }
		if ($attr['COLUMN-GAP']) { $this->SetColumns($attr['COLUMN-COUNT'],$valign,$attr['COLUMN-GAP']); }
		else { $this->SetColumns($attr['COLUMN-COUNT'],$valign); }
	}
	break;

    case 'COLUMN_BREAK': //custom-tag
    case 'COLUMNBREAK': //custom-tag
    case 'NEWCOLUMN': //custom-tag
	$this->ignorefollowingspaces = true;
	$this->NewColumn();
	$this->ColumnAdjust = false;	// disables all column height adjustment for the page.
	break;

    case 'PAGE_BREAK': //custom-tag
    case 'PAGEBREAK': //custom-tag
    case 'NEWPAGE': //custom-tag
	// Close any open block tags
	for ($b= $this->blklvl;$b>0;$b--) { $this->CloseTag($this->blk[$b]['tag']); }
	if(!empty($this->textbuffer))  {	//Output previously buffered content
    	  	$this->printbuffer($this->textbuffer);
        	$this->textbuffer=array(); 
      }
	$this->ignorefollowingspaces = true;
	$save_cols = false;
	if ($this->ColActive) {
		$save_cols = true;
		$save_nbcol = $this->NbCol;	// other values of gap and vAlign will not change by setting Columns off
		$this->SetColumns(0);
	}
	// Added/Edited mPDF 1.3
	$resetpagenum = '';
	$pagenumstyle = '';
	$suppress = '';
	if (isset($attr['RESETPAGENUM'])) { $resetpagenum = $attr['RESETPAGENUM']; }
	if (isset($attr['PAGENUMSTYLE'])) { $pagenumstyle = $attr['PAGENUMSTYLE']; }
	if (isset($attr['SUPPRESS'])) { $suppress = $attr['SUPPRESS']; }
	if ($attr['TYPE'] == 'E' or $attr['TYPE'] == 'EVEN') { $this->AddPage('','E', $resetpagenum, $pagenumstyle, $suppress); }
	else if ($attr['TYPE'] == 'O' or $attr['TYPE'] == 'ODD') { $this->AddPage('','O', $resetpagenum, $pagenumstyle, $suppress); }
	else if ($attr['TYPE'] == 'NEXT-ODD') { $this->AddPages('','NEXT-ODD', $resetpagenum, $pagenumstyle, $suppress); }
	else if ($attr['TYPE'] == 'NEXT-EVEN') { $this->AddPages('','NEXT-EVEN', $resetpagenum, $pagenumstyle, $suppress); }
	else { $this->AddPage('','', $resetpagenum, $pagenumstyle, $suppress); }

	if ($save_cols) {
		// Restore columns
		$this->SetColumns($save_nbcol,$this->colvAlign,$this->ColGap);
	}
	break;

    case 'BDO':
	$this->BiDirectional = true;
	break;


    case 'TTZ':
		$this->ttz = true;
		$this->InlineProperties[$tag] = $this->saveInlineProperties();
		$this->setCSS(array('FONT-FAMILY'=>'zapfdingbats','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
		break;

    case 'TTS':
		$this->tts = true;
		$this->InlineProperties[$tag] = $this->saveInlineProperties();
		$this->setCSS(array('FONT-FAMILY'=>'symbol','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
		break;

    case 'TTA':
		$this->tta = true;
		$this->InlineProperties[$tag] = $this->saveInlineProperties();
		$this->setCSS(array('FONT-FAMILY'=>'helvetica-embedded','FONT-WEIGHT'=>'normal','FONT-STYLE'=>'normal'),'INLINE');
		break;



    // INLINE PHRASES OR STYLES
    case 'SUB':
    case 'SUP':
    case 'ACRONYM':
    case 'BIG':
    case 'SMALL':
    case 'INS':
    case 'S':
    case 'STRIKE':
    case 'DEL':
    case 'STRONG':
    case 'CITE':
    case 'Q':
    case 'EM':
    case 'B':
    case 'I':
    case 'U':
    case 'SAMP':
    case 'CODE':
    case 'KBD':
    case 'TT':
    case 'VAR':
    case 'FONT':
    case 'SPAN':
	if ($tag == 'SPAN') {
		$this->spanlvl++;
		$this->InlineProperties['SPAN'][$this->spanlvl] = $this->saveInlineProperties();
	}
	else { $this->InlineProperties[$tag] = $this->saveInlineProperties(); }

	$properties = $this->MergeCSS('',$tag,$attr);
	if (!empty($properties)) $this->setCSS($properties,'INLINE');
	break;



    case 'A':
	if (isset($attr['NAME']) and $attr['NAME'] != '') { 
		$this->textbuffer[] = array('','','',array(),'',false,false,$attr['NAME']); //an internal link (adds a space for recognition)
	}
	if (isset($attr['HREF'])) { 
		$this->InlineProperties['A'] = $this->saveInlineProperties();
		$properties = $this->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');
		$this->HREF=$attr['HREF'];
	}
	break;



    case 'BR':
	if($this->tablestart) {
	   // If already something in the Cell
	   if ((isset($this->cell[$this->row][$this->col]['maxs']) && $this->cell[$this->row][$this->col]['maxs'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) || $this->cell[$this->row][$this->col]['s'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = "\n";
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];  
		}
		$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);// reset
	   }
	}
	else  {
		$this->textbuffer[] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}
	$this->ignorefollowingspaces = true; 
	$this->blockjustfinished=false;
	break;


	// *********** BLOCKS  ********************

	//NB $outerblocktags = array('DIV','FORM','CENTER','DL');
	//NB $innerblocktags = array('P','BLOCKQUOTE','ADDRESS','PRE','HR','H1','H2','H3','H4','H5','H6','DT','DD');

    case 'PRE':
	$this->ispre=true;	// ADDED - Prevents left trim of textbuffer in printbuffer()

    case 'DIV':
    case 'FORM':
    case 'CENTER':

    case 'BLOCKQUOTE':
    case 'ADDRESS': 

    case 'P':
    case 'H1':
    case 'H2':
    case 'H3':
    case 'H4':
    case 'H5':
    case 'H6':
    case 'DL':
    case 'DT':
    case 'DD':


	// Start Block
	$this->InlineProperties = array(); 
	$this->spanlvl = 0;
	$this->ignorefollowingspaces = true; 
	$this->blockjustfinished=false;
	$this->divbegin=true;

	if ($this->tablestart) {
	   // If already something in the Cell
	   if ((isset($this->cell[$this->row][$this->col]['maxs']) && $this->cell[$this->row][$this->col]['maxs'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) || $this->cell[$this->row][$this->col]['s'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,''/*internal link*/,$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = "\n";
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);// reset
	   }
	   // Cannot set block properties inside table - use Bold to indicate h1-h6
	   if ($tag == 'CENTER' && $this->tdbegin) { $this->cell[$this->row][$this->col]['a'] = $align['center']; }

		$this->InlineProperties['BLOCKINTABLE'] = $this->saveInlineProperties();
		$properties = $this->MergeCSS('',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');


	   break;
	}

	if ($tag == 'P' || $tag == 'DT' || $tag == 'DD') { $this->lastoptionaltag = $tag; } // Save current HTML specified optional endtag
	else { $this->lastoptionaltag = ''; }



	if ($this->lastblocklevelchange == 1) { $blockstate = 1; }	// Top margins/padding only
	else if ($this->lastblocklevelchange < 1) { $blockstate = 0; }	// NO margins/padding
	$this->printbuffer($this->textbuffer,$blockstate);
	$this->textbuffer=array();

	$this->blklvl++;
	$this->blk[$this->blklvl]['tag'] = $tag;

	$this->Reset();
	$properties = $this->MergeCSS('BLOCK',$tag,$attr);

	// Added mPDF 1.1 keeping block together on one page
	if (strtoupper($properties['PAGE-BREAK-INSIDE']) == 'AVOID' && !$this->ColActive && !$this->keep_block_together) {
		$this->blk[$this->blklvl]['keep_block_together'] = 1;
		$this->blk[$this->blklvl]['y00'] = $this->y;
		$this->keep_block_together = 1;
		$this->divbuffer = array();
		$this->ktLinks = array();
		$this->ktBlock = array();
		$this->ktReference = array();
		$this->ktBMoutlines = array();
		$this->_kttoc = array();
	}


	$this->setCSS($properties,'BLOCK',$tag); //name(id/class/style) found in the CSS array!
	$this->blk[$this->blklvl]['InlineProperties'] = $this->saveInlineProperties();


	if(isset($attr['ALIGN'])) { $this->blk[$this->blklvl]['block-align'] = $align[strtolower($attr['ALIGN'])]; }

	// Hanging indent - if negative indent: ensure padding is >= indent
	if ($this->blk[$this->blklvl]['text_indent'] < 0) {
	  $hangind = -($this->blk[$this->blklvl]['text_indent']);
	  if ($this->directionality == 'rtl') {
		$this->blk[$this->blklvl]['padding_right'] = max($this->blk[$this->blklvl]['padding_right'],$hangind);
	  }
	  else {
		$this->blk[$this->blklvl]['padding_left'] = max($this->blk[$this->blklvl]['padding_left'],$hangind);
	  }
	}

	$this->blk[$this->blklvl]['outer_left_margin'] = $this->blk[$this->blklvl-1]['outer_left_margin'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl-1]['border_left']['w'] + $this->blk[$this->blklvl-1]['padding_left'];
	$this->blk[$this->blklvl]['outer_right_margin'] = $this->blk[$this->blklvl-1]['outer_right_margin']  + $this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl-1]['border_right']['w'] + $this->blk[$this->blklvl-1]['padding_right'];

	$this->blk[$this->blklvl]['width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin']);
	$this->blk[$this->blklvl]['inner_width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);

	// Check DIV is not now too narrow to fit text
	$mw = $this->getStringWidth('WW');
	if ($this->blk[$this->blklvl]['inner_width'] < $mw) {
		$this->blk[$this->blklvl]['padding_left'] = 0;
		$this->blk[$this->blklvl]['padding_right'] = 0;
		$this->blk[$this->blklvl]['border_left']['w'] = 0.2;
		$this->blk[$this->blklvl]['border_right']['w'] = 0.2;
		$this->blk[$this->blklvl]['margin_left'] = 0;
		$this->blk[$this->blklvl]['margin_right'] = 0;
		$this->blk[$this->blklvl]['outer_left_margin'] = $this->blk[$this->blklvl-1]['outer_left_margin'] + $this->blk[$this->blklvl]['margin_left'] + $this->blk[$this->blklvl-1]['border_left']['w'] + $this->blk[$this->blklvl-1]['padding_left'];
		$this->blk[$this->blklvl]['outer_right_margin'] = $this->blk[$this->blklvl-1]['outer_right_margin']  + $this->blk[$this->blklvl]['margin_right'] + $this->blk[$this->blklvl-1]['border_right']['w'] + $this->blk[$this->blklvl-1]['padding_right'];
		$this->blk[$this->blklvl]['width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin']);
		$this->blk[$this->blklvl]['inner_width'] = $this->pgwidth - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);
		if ($this->blk[$this->blklvl]['inner_width'] < $mw) { die("DIV is too narrow for text to fit!"); }
	}

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	//Save x,y coords in case we need to print borders...
	$this->blk[$this->blklvl]['y0'] = $this->y;
	$this->blk[$this->blklvl]['x0'] = $this->x;
	$this->blk[$this->blklvl]['startpage'] = $this->page;
	$this->oldy = $this->y;

	$this->lastblocklevelchange = 1 ;

	break;



    case 'HR':
	$this->ignorefollowingspaces = true; 
	$objattr = array();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['MARGIN-TOP']) { $objattr['margin_top'] = ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
	if ($properties['MARGIN-BOTTOM']) { $objattr['margin_bottom'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
	if ($properties['WIDTH']) { $objattr['width'] = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']); }
	if ($properties['TEXT-ALIGN']) { $objattr['align'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
	if ($properties['COLOR']) { $objattr['color'] = ConvertColor($properties['COLOR']); }
	if ($properties['HEIGHT']) { $objattr['linewidth'] = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }

	if($attr['WIDTH'] != '') $objattr['width'] = ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if($attr['ALIGN'] != '') $objattr['align'] = $align[strtolower($attr['ALIGN'])];
	if($attr['COLOR'] != '') $objattr['color'] = ConvertColor($attr['COLOR']);

	if ($this->tablestart) {
		$objattr['W-PERCENT'] = 100;
		if (stristr($properties['WIDTH'],'%')) { 
			$properties['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $properties['WIDTH'];
		}
		if (stristr($attr['WIDTH'],'%')) { 
			$attr['WIDTH'] += 0;  //make "90%" become simply "90" 
			$objattr['W-PERCENT'] = $attr['WIDTH'];
		}
	}


	$objattr['type'] = 'hr';
	$objattr['height'] = $objattr['linewidth'] + $objattr['margin_top'] + $objattr['margin_bottom'];
	$e = "»¤¬type=image,objattr=".serialize($objattr)."»¤¬";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tablestart) {
		if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
		}
		elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
			$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
		}
		$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);// reset
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	break;



	// *********** FORM ELEMENTS ********************

    case 'SELECT':
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$ptsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size);
  		$this->SetFontSize($ptsize,false);
	}
	$properties = array();
	$this->specialcontent = "type=select"; 
	break;

    case 'OPTION':
	$this->lastoptionaltag = 'OPTION'; // Save current HTML specified optional endtag
	$this->selectoption['ACTIVE'] = true;
	if (empty($this->selectoption)) {
		$this->selectoption['MAXWIDTH'] = '';
		$this->selectoption['SELECTED'] = '';
	}
	if (isset($attr['SELECTED'])) $this->selectoption['SELECTED'] = '';
	break;

    case 'TEXTAREA':
	$objattr = array();
	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],'',0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$ptsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size);
  		$this->SetFontSize($ptsize,false);
	}

	$this->SetLineHeight('',$this->textarea_lineheight); 

	$w = 0;
	$h = 0;
	if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if(isset($properties['HEIGHT'])) $h = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);
	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$colsize = 20; //HTML default value 
	$rowsize = 2; //HTML default value
	if (isset($attr['COLS'])) $colsize = intval($attr['COLS']);
	if (isset($attr['ROWS'])) $rowsize = intval($attr['ROWS']);

	$charsize = $this->GetStringWidth('w');
	if ($w) { $colsize = round(($w-($this->form_element_spacing['textarea']['outer']['h']*2)-($this->form_element_spacing['textarea']['inner']['h']*2))/$charsize); }
	if ($h) { $rowsize = round(($h-($this->form_element_spacing['textarea']['outer']['v']*2)-($this->form_element_spacing['textarea']['inner']['v']*2))/$this->lineheight); }

	$objattr['type'] = 'textarea';
	$objattr['width'] = ($colsize * $charsize) + ($this->form_element_spacing['textarea']['outer']['h']*2)+($this->form_element_spacing['textarea']['inner']['h']*2);
	$objattr['height'] = ($rowsize * $this->lineheight) + ($this->form_element_spacing['textarea']['outer']['v']*2)+($this->form_element_spacing['textarea']['inner']['v']*2);
	$objattr['rows'] = $rowsize;
	$objattr['cols'] = $colsize;

	$this->specialcontent = serialize($objattr); 

	if ($this->tablestart) {
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
	}

	// Clear properties - tidy up
	$properties = array();
	break;



	// *********** FORM - INPUT ********************

    case 'INPUT':
	if (!isset($attr['TYPE'])) $attr['TYPE'] == ''; 
	$objattr = array();
	$objattr['type'] = 'input';

	$this->InlineProperties[$tag] = $this->saveInlineProperties();
	$properties = $this->MergeCSS('',$tag,$attr);

	$objattr['vertical-align'] = '';

	if ($properties['FONT-FAMILY']) { 
	   if (!$this->isCJK) { 
		$this->SetFont($properties['FONT-FAMILY'],$this->FontStyle,0,false);
	   }
	}
	if ($properties['FONT-SIZE']) { 
		$ptsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size);
  		$this->SetFontSize($ptsize,false);
	}

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;


	$type = '';
      $texto='';
	$height = $this->FontSize;
	$width = 0;
	$spacesize = $this->GetStringWidth(' ');

	$w = 0;
	if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);

	if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

	switch(strtoupper($attr['TYPE'])){
	   case 'HIDDEN':
      		$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
			if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
			unset($this->InlineProperties[$tag]);
			break 2;
	   case 'CHECKBOX': //Draw Checkbox
                $type = 'CHECKBOX';
                if (isset($attr['CHECKED'])) $objattr['checked'] = true;
                $width = $this->FontSize;
                $height = $this->FontSize;
                break;


	   case 'RADIO': //Draw Radio button
                $type = 'RADIO';
                if (isset($attr['CHECKED'])) $objattr['checked'] = true;
                $width = $this->FontSize;
                $height = $this->FontSize;
               break;


	   case 'IMAGE': $type = 'X'; // Draw a button
	   case 'BUTTON': $type = 'BUTTON'; // Draw a button
	   case 'SUBMIT': if ($type == '') $type = 'SUBMIT';
	   case 'RESET': if ($type == '') $type = 'RESET';
                $texto=' X ';
                if (isset($attr['VALUE'])) $texto = " " . $attr['VALUE'] . " ";
                $width = $this->GetStringWidth($texto) + ($this->form_element_spacing['button']['outer']['h']*2)+($this->form_element_spacing['button']['inner']['h']*2);
		    $height = $this->FontSize + ($this->form_element_spacing['button']['outer']['v']*2)+($this->form_element_spacing['button']['inner']['v']*2);
                break;


	   case 'PASSWORD':
               $type = 'PASSWORD';
               if (isset($attr['VALUE'])) {
                    $num_stars = strlen($attr['VALUE']);
                    $texto = str_repeat('*',$num_stars);
                }
		    $xw = ($this->form_element_spacing['input']['outer']['h']*2)+($this->form_element_spacing['input']['inner']['h']*2);
		    if ($w) { $width = $w + $xw; } 
		    else { $width = (20 * $spacesize) + $xw; }	// Default width in chars
                if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $width = ($attr['SIZE'] * $spacesize) + $xw;
		    $height = $this->FontSize + ($this->form_element_spacing['input']['outer']['v']*2)+($this->form_element_spacing['input']['inner']['v']*2);
                break;

	   case 'TEXT': 
	   default:
                if ($type == '') $type = 'TEXT';
                if (isset($attr['VALUE'])) $texto = $attr['VALUE'];
		    $xw = ($this->form_element_spacing['input']['outer']['h']*2)+($this->form_element_spacing['input']['inner']['h']*2);
		    if ($w) { $width = $w + $xw; } 
		    else { $width = (20 * $spacesize) + $xw; }	// Default width in chars
                if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']) ) $width = ($attr['SIZE'] * $spacesize) + $xw;
		    $height = $this->FontSize + ($this->form_element_spacing['input']['outer']['v']*2)+($this->form_element_spacing['input']['inner']['v']*2);
                break;
	}

	$objattr['subtype'] = $type;
	$objattr['text'] = $texto;
	$objattr['width'] = $width;
	$objattr['height'] = $height;
	$e = "»¤¬type=input,objattr=".serialize($objattr)."»¤¬";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tablestart) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);

		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;

	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

	break;	// END of INPUT



	// *********** IMAGE  ********************

    case 'IMG':
	$objattr = array();
	if(isset($attr['SRC']))	{
     		$srcpath = $attr['SRC'];
		$properties = $this->MergeCSS('',$tag,$attr);
		// VSPACE and HSPACE converted to margins in MergeCSS
		if ($properties['MARGIN-TOP']) { $objattr['margin_top']=ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
		if ($properties['MARGIN-BOTTOM']) { $objattr['margin_bottom'] = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
		if ($properties['MARGIN-LEFT']) { $objattr['margin_left'] = ConvertSize($properties['MARGIN-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
		if ($properties['MARGIN-RIGHT']) { $objattr['margin_right'] = ConvertSize($properties['MARGIN-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }


		if ($properties['BORDER-TOP']) { $objattr['border_top'] = $this->border_details($properties['BORDER-TOP']); }
		if ($properties['BORDER-BOTTOM']) { $objattr['border_bottom'] = $this->border_details($properties['BORDER-BOTTOM']); }
		if ($properties['BORDER-LEFT']) { $objattr['border_left'] = $this->border_details($properties['BORDER-LEFT']); }
		if ($properties['BORDER-RIGHT']) { $objattr['border_right'] = $this->border_details($properties['BORDER-RIGHT']); }

		if ($properties['VERTICAL-ALIGN']) { $objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }

		$w = 0;
		$h = 0;
		if(isset($properties['WIDTH'])) $w = ConvertSize($properties['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
		if(isset($properties['HEIGHT'])) $h = ConvertSize($properties['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

		if ($this->HREF) { $objattr['link'] = $this->HREF; }	// ? this isn't used

		$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
		$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		// Image file
		$found_img = false;
		if (@fopen($srcpath,"rb")) { $found_img = true; }
		else if (function_exists("curl_init")) {
			$ch = curl_init($srcpath);
			curl_setopt($ch, CURLOPT_HEADER, 0);
      			curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
			$test = curl_exec($ch);
			curl_close($ch);
			if ($test) { $found_img = true; }
		}
		if (!$found_img) {
			if(!$this->shownoimg) break;
			$srcpath = str_replace("\\","/",dirname(__FILE__)) . "/";
		//	$srcpath .= 'no_img.gif';
		//	$w = $h = (21 * 0.2645); 	// 21 x 21px
			$srcpath .= 'no_img2.gif';
			$w = (14 * 0.2645); 	// 14 x 16px
			$h = (16 * 0.2645); 	// 14 x 16px
		}
			// Gets Image Info
			if(!isset($this->images[$srcpath])) {
				//First use of image, get info
				$pos=strrpos($srcpath,'.');
				if(!$pos)	$this->Error('Image file has no extension and no type was specified: '.$srcpath);
				$itype=substr($srcpath,$pos+1);
				$itype=strtolower($itype);
				$mqr=get_magic_quotes_runtime();
				set_magic_quotes_runtime(0);
				if($itype=='jpg' or $itype=='jpeg')	$info=$this->_parsejpg($srcpath);
				elseif($itype=='png') $info=$this->_parsepng($srcpath);
				elseif($itype=='gif') $info=$this->_parsegif($srcpath); 
				else { 
					//Allow for additional formats
					$mtd='_parse'.$itype;
					if(!method_exists($this,$mtd)) $this->Error('Unsupported image type: '.$itype);
					$info=$this->$mtd($srcpath);
				}
				set_magic_quotes_runtime($mqr);
				$info['i']=count($this->images)+1;
				$this->images[$srcpath]=$info;
			}
			else $info=$this->images[$srcpath];
			$objattr['file'] = $srcpath;

			////////////////////////////////////////////////////////////////////
			//Default width and height calculation if needed
			if($w==0 and $h==0) {
				//Put image at default dpi
				$w=($info['w']/$this->k) * (72/$this->img_dpi);
				$h=($info['h']/$this->k) * (72/$this->img_dpi);
			}
			////////////////////////////////////////////////////////////////////
			// IF WIDTH OR HEIGHT SPECIFIED
			if($w==0)	$w=$h*$info['w']/$info['h'];
			if($h==0)	$h=$w*$info['h']/$info['w'];
			////////////////////////////////////////////////////////////////////
			// Resize to maximum dimensions of page
			$maxWidth = $this->blk[$this->blklvl]['inner_width'];
   			$maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10) ;
			if ($w + $extrawidth > $maxWidth ) {
				$w = $maxWidth - $extrawidth;
				$h=$w*$info['h']/$info['w'];
			}

			if ($h + $extraheight > $maxHeight ) {
				$h = $maxHeight - $extraheight;
				$w=$h*$info['w']/$info['h'];
			}
			////////////////////////////////////////////////////////////////////
		$objattr['type'] = 'image';
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
		$e = "»¤¬type=image,objattr=".serialize($objattr)."»¤¬";

		// Clear properties - tidy up
		$properties = array();

		// Output it to buffers
		if ($this->tablestart) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
		}
		else {
			$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);

		}
	}
	break;


	// *********** TABLES ********************

    case 'TABLE': // TABLE-BEGIN
	$this->lastoptionaltag = '';
	// Disable vertical justification in columns
	if ($this->ColActive) { $this->colvAlign = ''; }


	if ($this->tablestart) { // nested tables - can't do
		$this->CloseTag('TD');
		$this->CloseTag('TR');
		$this->CloseTag('TABLE');
	}

	// called from block after new div e.g. <div> ... <table> ...    Outputs block top margin/border and padding
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1) {
		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,1,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
	}
	else { $this->printbuffer($this->textbuffer); }
	$this->textbuffer=array();
	//++++++++++++++++++++++++++++
	$this->Reset();
	$this->table_lineheight = $default_lineheight_correction; 
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$this->tablestart = true;
	$this->table['nc'] = $this->table['nr'] = 0;
	$this->tablethead = 0;
	$this->tabletheadjustfinished = false;

	// Added mPDF 1.2 
	$this->tablecascadeCSS = array();

		// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add parameter 'TABLE'
		$properties = $this->MergeCSS('TABLE',$tag,$attr);


		if ($properties['BACKGROUND-COLOR']) { $this->table['bgcolor'][-1] = $properties['BACKGROUND-COLOR'];	}
		if ($properties['VERTICAL-ALIGN']) { $this->table['va'] = $align[strtolower($properties['VERTICAL-ALIGN'])]; }
		if ($properties['TEXT-ALIGN']) { $this->table['txta'] = $align[strtolower($properties['TEXT-ALIGN'])]; }
		if ($properties['AUTOSIZE'])	{ 
			$this->shrink_this_table_to_fit = $properties['AUTOSIZE']; 
			if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 0; }
		}
		if ($properties['ROTATE'])	{ 
			$this->table_rotate = $properties['ROTATE']; 
		}
		if ($properties['TOPNTAIL']) { $this->table['topntail'] = $properties['TOPNTAIL']; }
		if ($properties['THEAD-UNDERLINE']) { $this->table['thead-underline'] = $properties['THEAD-UNDERLINE']; }

		if ($properties['BORDER']) { 
			$bord = $this->border_details($properties['BORDER']);
			if ($bord['s']) {
				$this->table['border'] = '1111';
				$this->table['border_details']['R'] = $bord;
				$this->table['border_details']['L'] = $bord;
				$this->table['border_details']['T'] = $bord;
				$this->table['border_details']['B'] = $bord;
			}
		}
		if ($properties['BORDER-RIGHT']) { 
		  if ($this->directionality == 'rtl') { 
			$this->table['border_details']['R'] = $this->border_details($properties['BORDER-LEFT']);
		  }
		  else {
			$this->table['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
		  }
		}
		if ($properties['BORDER-LEFT']) { 
		  if ($this->directionality == 'rtl') { 
			$this->table['border_details']['L'] = $this->border_details($properties['BORDER-RIGHT']);
		  }
		  else {
			$this->table['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
		  }
		}
		if ($properties['BORDER-BOTTOM']) { 
			$this->table['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
		}
		if ($properties['BORDER-TOP']) { 
			$this->table['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
		}
		if (($properties['BORDER-RIGHT']) || ($properties['BORDER-LEFT']) || ($properties['BORDER-BOTTOM']) || ($properties['BORDER-TOP'])){ 
			if (!$this->table['border_details']['T']['s']) { $this->table['border_details']['T']['s'] = '0'; }
			if (!$this->table['border_details']['B']['s']) { $this->table['border_details']['B']['s'] = '0'; }
			if (!$this->table['border_details']['L']['s']) { $this->table['border_details']['L']['s'] = '0'; }
			if (!$this->table['border_details']['R']['s']) { $this->table['border_details']['R']['s'] = '0'; }
			$this->table['border'] = $this->table['border_details']['T']['s'].$this->table['border_details']['R']['s'].$this->table['border_details']['B']['s'].$this->table['border_details']['L']['s'];
			// Edited mPDF 1.1 for correct table border inheritance
			  $this->table_border_css_set = 1;
		}
		// Edited mPDF 1.1 for correct table border inheritance
		else {
		  $this->table_border_css_set = 0;
		}

		if ($properties['FONT-FAMILY']) { 
		   if (!$this->isCJK) { 
			$this->default_font = $properties['FONT-FAMILY'];
			$this->SetFont($this->default_font,'',0,false);
		   }
		}
		if ($properties['FONT-SIZE']) { 
			$mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size);
			$this->default_font_size = $mmsize*(72/25.4);
   			$this->SetFontSize($this->default_font_size,false);
		}

		// Added mPDF 1.2 to add CSS
		if ($properties['FONT-WEIGHT']) {
			if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->SetStyle('B',true); }
		}
		if ($properties['FONT-STYLE']) {
			if (strtoupper($properties['FONT-STYLE']) == 'ITALIC')	{ $this->SetStyle('I',true); }
		}
		if ($properties['COLOR']) {
		  $cor = ConvertColor($properties['COLOR']);
		  if ($cor) { 
			$this->colorarray = $cor;
			$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
			$this->issetcolor=true;
		  }
		}


		if ($properties['PADDING-LEFT']) { 
			$this->cellPaddingL = ConvertSize($properties['PADDING-LEFT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
		}
		if ($properties['PADDING-RIGHT']) { 
			$this->cellPaddingR = ConvertSize($properties['PADDING-RIGHT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
		}
		if ($properties['PADDING-TOP']) { 
			$this->cellPaddingT = ConvertSize($properties['PADDING-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
		}
		if ($properties['PADDING-BOTTOM']) { 
			$this->cellPaddingB = ConvertSize($properties['PADDING-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
		}

		if ($properties['MARGIN-TOP']) { 
			$this->table_margin_top = ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); 
		}

		if ($properties['MARGIN-BOTTOM']) { 
			$this->table_margin_bottom = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); 
		}
		else { $this->table_margin_bottom = 0; }

		if ($properties['LINE-HEIGHT']>=1) { 
			$this->table_lineheight = $properties['LINE-HEIGHT']; 
		}


	$properties = array();

	if (isset($attr['BORDER'])) {
		// Edited mPDF 1.1 for correct table border inheritance
		  $this->table_border_attr_set = 1;
		if ($attr['BORDER']=='1') {
			$bord = $this->border_details('#000000 1px solid');
		}
		if ($bord['s']) {
			$this->table['border'] = '1111';
			$this->table['border_details']['R'] = $bord;
			$this->table['border_details']['L'] = $bord;
			$this->table['border_details']['T'] = $bord;
			$this->table['border_details']['B'] = $bord;
		}
	}
	// Edited mPDF 1.1 for correct table border inheritance
	else {
	  $this->table_border_attr_set = 0;
	}
	if (isset($attr['REPEAT_HEADER']) and $attr['REPEAT_HEADER'] == true) { $this->UseTableHeader(true); } 
		else { $this->UseTableHeader(false); }
	if (isset($attr['WIDTH'])) 	$this->table['w']	= ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if (isset($attr['HEIGHT']))	$this->table['h']	= ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);
	if (isset($attr['ALIGN']))	$this->table['a']	= $align[strtolower($attr['ALIGN'])];
	if (!$this->table['a']) { $this->table['a'] = $this->defaultTableAlign; }
	if (isset($attr['BGCOLOR'])) $this->table['bgcolor'][-1]	= $attr['BGCOLOR'];

	if (isset($attr['AUTOSIZE']))	{ 
		$this->shrink_this_table_to_fit = $attr['AUTOSIZE']; 
		if ($this->shrink_this_table_to_fit < 1) { $this->shrink_this_table_to_fit = 0; }
	}
	if (isset($attr['ROTATE']))	{ 
		$this->table_rotate = $attr['ROTATE']; 
	}

	// Set cMarginX values from Border
	if ($this->table['border_details']['R']['w'] || $this->table['border_details']['L']['w']) { 
		$this->cMarginR = max($this->table['border_details']['L']['w'],$this->table['border_details']['R']['w'])/2; 
		$this->cMarginL = $this->cMarginR; 
	}
	if ($this->table['border_details']['T']['w'] || $this->table['border_details']['B']['w']) { 
		$this->cMarginT = max($this->table['border_details']['T']['w'],$this->table['border_details']['B']['w'])/2; 
		$this->cMarginB = $this->cMarginT; 
	}


	//++++++++++++++++++++++++++++
	// Added mPDF 1.1 keeping block together on one page
	// ? need to disable Table autosize if keep block together - or vice versa?
//	if ($this->keep_block_together) {
//		$this->table_rotate = 0;
//		$this->shrink_this_table_to_fit = 0;
//	}
	if (($this->table_rotate || $this->shrink_this_table_to_fit ) && $this->keep_block_together) {
		$this->keep_block_together = 0;
		$this->printdivbuffer();
		$this->blk[$this->blklvl]['keep_block_together'] = 0;
	}
	//++++++++++++++++++++++++++++


	break;



    case 'THEAD':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->tablethead = 1;
	$this->UseTableHeader(true);
	$properties = $this->MergeCSS('',$tag,$attr);
	if ($properties['FONT-WEIGHT']) {
		if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->thead_font_weight = 'B'; }
		else { $this->thead_font_weight = ''; }
	}

	// Added in mPDF 1.1
	if ($properties['FONT-STYLE']) {
		if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') { $this->thead_font_style = 'I'; }
		else { $this->thead_font_style = ''; }
	}

	if ($properties['VERTICAL-ALIGN']) {
		$this->thead_valign_default = $properties['VERTICAL-ALIGN'];
	}
	if ($properties['TEXT-ALIGN']) {
		$this->thead_textalign_default = $properties['TEXT-ALIGN'];
	}
	$properties = array();
	break;



    case 'TR':
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->row++;
	$this->table['nr']++;
	$this->col = -1;
	// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add parameter 'TR
	$properties = $this->MergeCSS('TR',$tag,$attr);
	if ($properties['BACKGROUND-COLOR']) { $this->table['bgcolor'][$this->row] = $properties['BACKGROUND-COLOR']; }
	// Edited mPDF 1.3 for rotated text in cell
	if ($properties['TEXT-ROTATE']) {
		$this->trow_text_rotate = $properties['TEXT-ROTATE'];
	}
	if (isset($attr['TEXT-ROTATE'])) $this->trow_text_rotate = $attr['TEXT-ROTATE'];

	if (isset($attr['BGCOLOR'])) $this->table['bgcolor'][$this->row]	= $attr['BGCOLOR'];
	$properties = array();
	break;



    case 'TH':
    case 'TD':
	$this->ignorefollowingspaces = true; 
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
	$this->InlineProperties = array();
	$this->spanlvl = 0;
	$this->tdbegin = true;
	$this->col++;
	while (isset($this->cell[$this->row][$this->col])) { $this->col++; }
	//Update number column
	if ($this->table['nc'] < $this->col+1) { $this->table['nc'] = $this->col+1; }
	$this->cell[$this->row][$this->col] = array();
	$this->cell[$this->row][$this->col]['text'] = array();
	$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);

	// INHERITED TABLE PROPERTIES (or ROW for BGCOLOR)
	// If cell bgcolor not set specifically, set to TR row bgcolor (if set)
	if ((!$this->cell[$this->row][$this->col]['bgcolor']) && ($this->table['bgcolor'][$this->row])) {
		$this->cell[$this->row][$this->col]['bgcolor'] = $this->table['bgcolor'][$this->row];
	}
	else if ($this->table['bgcolor'][-1]) { $this->cell[$this->row][$this->col]['bgcolor'] = $this->table['bgcolor'][-1]; }
	if ($this->table['va']) { $this->cell[$this->row][$this->col]['va'] = $this->table['va']; }
	if ($this->table['txta']) { $this->cell[$this->row][$this->col]['a'] = $this->table['txta']; }
	// Edited mPDF 1.1 for correct table border inheritance
	if ($this->table_border_attr_set) {
	  if ($this->table['border_details']) {
		$this->cell[$this->row][$this->col]['border_details']['R'] = $this->table['border_details']['R'];
		$this->cell[$this->row][$this->col]['border_details']['L'] = $this->table['border_details']['L'];
		$this->cell[$this->row][$this->col]['border_details']['T'] = $this->table['border_details']['T'];
		$this->cell[$this->row][$this->col]['border_details']['B'] = $this->table['border_details']['B'];
	  }
	} 
	// INHERITED THEAD CSS Properties
	if ($this->tablethead) { 
		if ($this->thead_valign_default) $this->cell[$this->row][$this->col]['va'] = $align[strtolower($this->thead_valign_default)]; 
		if ($this->thead_textalign_default) $this->cell[$this->row][$this->col]['a'] = $align[strtolower($this->thead_textalign_default)]; 
		if ($this->thead_font_weight == 'B') { $this->SetStyle('B',true); }
		// ADDED in mPDF 1.1
		if ($this->thead_font_style == 'I') { $this->SetStyle('I',true); }
	}
	// Edited mPDF 1.3 for rotated text in cell
	if ($this->trow_text_rotate) {
		$this->cell[$this->row][$this->col]['R'] = $this->trow_text_rotate; 
	}

	// ADDED CSS FUNCIONS FOR TABLE // mPDF 1.2 add 1st parameter 'TD or TH as $tag
		$properties = $this->MergeCSS($tag,$tag,$attr);

		if ($properties['BACKGROUND-COLOR']) { $this->cell[$this->row][$this->col]['bgcolor'] = $properties['BACKGROUND-COLOR']; }
		if ($properties['VERTICAL-ALIGN']) { $this->cell[$this->row][$this->col]['va']=$align[strtolower($properties['VERTICAL-ALIGN'])]; }
		if ($properties['TEXT-ALIGN']) { $this->cell[$this->row][$this->col]['a'] = $align[strtolower($properties['TEXT-ALIGN'])]; }

		// Added mPDF 1.3 for rotated text in cell
		if ($properties['TEXT-ROTATE'])	{ 
			$this->cell[$this->row][$this->col]['R'] = $properties['TEXT-ROTATE']; 
		}
		if ($properties['BORDER']) { 
			$bord = $this->border_details($properties['BORDER']);
			if ($bord['s']) {
				 $this->cell[$this->row][$this->col]['border'] = '1111';
				 $this->cell[$this->row][$this->col]['border_details']['R'] = $bord;
				 $this->cell[$this->row][$this->col]['border_details']['L'] = $bord;
				 $this->cell[$this->row][$this->col]['border_details']['T'] = $bord;
				 $this->cell[$this->row][$this->col]['border_details']['B'] = $bord;
			}
		}

		if ($properties['BORDER-RIGHT']) { 
			 $this->cell[$this->row][$this->col]['border_details']['R'] = $this->border_details($properties['BORDER-RIGHT']);
		}
		if ($properties['BORDER-LEFT']) { 
			 $this->cell[$this->row][$this->col]['border_details']['L'] = $this->border_details($properties['BORDER-LEFT']);
		}
		if ($properties['BORDER-BOTTOM']) { 
			 $this->cell[$this->row][$this->col]['border_details']['B'] = $this->border_details($properties['BORDER-BOTTOM']);
		}
		if ($properties['BORDER-TOP']) { 
			 $this->cell[$this->row][$this->col]['border_details']['T'] = $this->border_details($properties['BORDER-TOP']);
		}
		if (($properties['BORDER-RIGHT']) || ($properties['BORDER-LEFT']) || ($properties['BORDER-BOTTOM']) || ($properties['BORDER-TOP'])){ 
			if (! $this->cell[$this->row][$this->col]['border_details']['T']['s']) {  $this->cell[$this->row][$this->col]['border_details']['T']['s'] = '0'; }
			if (! $this->cell[$this->row][$this->col]['border_details']['B']['s']) {  $this->cell[$this->row][$this->col]['border_details']['B']['s'] = '0'; }
			if (! $this->cell[$this->row][$this->col]['border_details']['L']['s']) {  $this->cell[$this->row][$this->col]['border_details']['L']['s'] = '0'; }
			if (! $this->cell[$this->row][$this->col]['border_details']['R']['s']) {  $this->cell[$this->row][$this->col]['border_details']['R']['s'] = '0'; }
			 $this->cell[$this->row][$this->col]['border'] =  $this->cell[$this->row][$this->col]['border_details']['T']['s']. $this->cell[$this->row][$this->col]['border_details']['R']['s']. $this->cell[$this->row][$this->col]['border_details']['B']['s'].$this->cell[$this->row][$this->col]['border_details']['L']['s'];
		}

		// Added mPDF 1.2 to add CSS
		if ($properties['COLOR']) {
		  $cor = ConvertColor($properties['COLOR']);
		  if ($cor) { 
			$this->colorarray = $cor;
			$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
			$this->issetcolor=true;
		  }
		}
		if ($properties['FONT-FAMILY']) { 			// NOT CHANGE DEFAULT
		   if (!$this->isCJK) { 
			$this->SetFont($properties['FONT-FAMILY'],'',0,false);
		   }
		}
		if ($properties['FONT-SIZE']) { 
			$mmsize = ConvertSize($properties['FONT-SIZE'],$this->default_font_size);
   			$this->SetFontSize($mmsize*(72/25.4),false);
		}


		if ($properties['FONT-WEIGHT']) {
			if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD')	{ $this->SetStyle('B',true); }
		}
		if ($properties['FONT-STYLE']) {
			if (strtoupper($properties['FONT-STYLE']) == 'ITALIC')	{ $this->SetStyle('I',true); }
		}

		$properties = array();


	if (isset($attr['WIDTH'])) $this->cell[$this->row][$this->col]['w'] = ConvertSize($attr['WIDTH'],$this->blk[$this->blklvl]['inner_width']);
	if (isset($attr['HEIGHT'])) $this->cell[$this->row][$this->col]['h']	= ConvertSize($attr['HEIGHT'],$this->blk[$this->blklvl]['inner_width']);

	if (isset($attr['ALIGN'])) $this->cell[$this->row][$this->col]['a'] = $align[strtolower($attr['ALIGN'])];
	if (isset($attr['VALIGN'])) $this->cell[$this->row][$this->col]['va'] = $align[strtolower($attr['VALIGN'])];

	if (isset($attr['BORDER'])) $this->cell[$this->row][$this->col]['border'] = $attr['BORDER'];
	if (isset($attr['BGCOLOR'])) $this->cell[$this->row][$this->col]['bgcolor'] = $attr['BGCOLOR'];
	$cs = $rs = 1;
	if (isset($attr['COLSPAN']) && $attr['COLSPAN']>1)	$cs = $this->cell[$this->row][$this->col]['colspan']	= $attr['COLSPAN'];
	if (isset($attr['ROWSPAN']) && $attr['ROWSPAN']>1)	$rs = $this->cell[$this->row][$this->col]['rowspan']	= $attr['ROWSPAN'];
	// Added mPDF 1.3 for rotated text in cell
	if (isset($attr['TEXT-ROTATE']))	{ 
		$this->cell[$this->row][$this->col]['R'] = $attr['TEXT-ROTATE']; 
	}
	for ($k=$this->row ; $k < $this->row+$rs ;$k++) {
		for($l=$this->col; $l < $this->col+$cs ;$l++) {
			if ($k-$this->row || $l-$this->col)	$this->cell[$k][$l] = 0;
		}
	}
	if (isset($attr['NOWRAP'])) $this->cell[$this->row][$this->col]['nowrap']= 1;

	break;



	// *********** LISTS ********************

    case 'OL':
	if ( !isset($attr['TYPE']) or $attr['TYPE'] == '' ) $this->listtype = '1'; //OL default == '1'
	else $this->listtype = $attr['TYPE']; 

    case 'UL':
	$this->lastoptionaltag = ''; // Save current HTML specified optional endtag
	if((!$this->tablestart) && ($this->listlvl == 0)) {
	//++++++++++++++++++++++++++++
	// called from non-block after new div e.g. <div> ... <ul> ...    Outputs block top margin/border and padding
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1) {
		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,1,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
	}
	else { $this->printbuffer($this->textbuffer); }
	$this->textbuffer=array();
	//++++++++++++++++++++++++++++
	}
	// ol and ul types are mixed here
	if ($this->listlvl == 0) {
		$this->list_indent = array();
		$this->list_align = array();
		$this->list_lineheight = array();
	}
	$this->list_indent[$this->listlvl] = 5;	// mm dfault indent for each level
	if ( (!isset($attr['TYPE']) or $attr['TYPE'] == '') and $tag=='UL') {
		//Insert UL defaults
		if ($this->listlvl == 0) $this->listtype = 'disc';
		elseif ($this->listlvl == 1) $this->listtype = 'circle';
		else $this->listtype = 'square';
	}
	elseif (isset($attr['TYPE']) and $tag=='UL') $this->listtype = $attr['TYPE'];

	// A simple list for inside a table
	if($this->tablestart) {
      	if ($this->listlvl == 0) {
			$this->listlvl++; // first depth level
			$this->listnum = 0; // reset
			$this->listlist[$this->listlvl] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
		}
		else {
			$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
			$this->listlvl++;
			$this->listnum = 0; // reset
			$this->listlist[$this->listlvl] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
		}
		break;
	}

		$properties = $this->MergeCSS('LIST',$tag,$attr);
		if (!empty($properties)) $this->setCSS($properties,'INLINE');
		// TOP LEVEL ONLY
		if ($this->listlvl == 0) {
		   if ($properties['MARGIN-TOP']) { 
			$this->DivLn(ConvertSize($properties['MARGIN-TOP'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize),$this->blklvl,true,1); 	// collapsible
		   }
		   if ($properties['MARGIN-BOTTOM']) { 
			$this->list_margin_bottom = ConvertSize($properties['MARGIN-BOTTOM'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); 
		   }
		}
		if ($properties['TEXT-INDENT']) { $this->list_indent[$this->listlvl+1] = ConvertSize($properties['TEXT-INDENT'],$this->blk[$this->blklvl]['inner_width'],$this->FontSize); }
		if ($properties['TEXT-ALIGN']) { $this->list_align[$this->listlvl+1] = $align[strtolower($properties['TEXT-ALIGN'])]; }
		if ($properties['LINE-HEIGHT']) { $this->list_lineheight[$this->listlvl+1] = $properties['LINE-HEIGHT']; }

		$this->InlineProperties['LIST'][$this->listlvl+1] = $this->saveInlineProperties();

      if ($this->listlvl == 0)
      {
        $this->listlvl++; // first depth level
        $this->listnum = 0; // reset
        $this->listoccur[$this->listlvl] = 1;
        $this->listlist[$this->listlvl][1] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }
      else
      {
        if (!empty($this->textbuffer))
        {
          $this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
          $this->listnum++;
        }
  		  $this->textbuffer = array();
  		  $occur = $this->listoccur[$this->listlvl];
        $this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
        $this->listlvl++;
        $this->listnum = 0; // reset

        if ($this->listoccur[$this->listlvl] == 0) $this->listoccur[$this->listlvl] = 1;
        else $this->listoccur[$this->listlvl]++;
  	  $occur = $this->listoccur[$this->listlvl];
        $this->listlist[$this->listlvl][$occur] = array('TYPE'=>$this->listtype,'MAXNUM'=>$this->listnum);
      }

	$properties = array();

     break;



    case 'LI':
	// Start Block
	$this->lastoptionaltag = $tag; // Save current HTML specified optional endtag
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	// A simple list for inside a table
	if($this->tablestart) {
	   // mPDF 1.1 Prevents newline after first bullet of list within table
	   $this->blockjustfinished=false;

	   // If already something in the Cell
	   if ((isset($this->cell[$this->row][$this->col]['maxs']) && $this->cell[$this->row][$this->col]['maxs'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) || $this->cell[$this->row][$this->col]['s'] > ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) {
			$this->cell[$this->row][$this->col]['textbuffer'][] = array("\n",$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
			$this->cell[$this->row][$this->col]['text'][] = "\n";
			if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
			}
			elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
				$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
			}
			$this->cell[$this->row][$this->col]['s'] = ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);
		}
		if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
			$this->listlvl++; // first depth level
			$this->listnum = 0; // reset
			$this->listlist[$this->listlvl] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
		}
		$this->listnum++;
		switch($this->listlist[$this->listlvl]['TYPE']) {
		case 'A':
			$blt = dec2alpha($this->listnum,true).'.';
			break;
		case 'a':
			$blt = dec2alpha($this->listnum,false).'.';
			break;
		case 'I':
			$blt = dec2roman($this->listnum,true).'.';
			break;
		case 'i':
			$blt = dec2roman($this->listnum,false).'.';
			break;
		case '1':
			$blt = $this->listnum.'.';
            	break;
		default:
			$blt = '-';
			break;
		}

		$ls = str_repeat('  ',($this->listlvl-1)*2) . $blt . ' ';
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($ls,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['text'][] = $ls;
		$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($ls);
		break;
	}
	//Observation: </LI> is ignored
	if ($this->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
	//First of all, skip a line
		$this->listlvl++; // first depth level
		$this->listnum = 0; // reset
		$this->listoccur[$this->listlvl] = 1;
		$this->listlist[$this->listlvl][1] = array('TYPE'=>'disc','MAXNUM'=>$this->listnum);
	}
	if ($this->listnum == 0) {
		$this->listnum++;
		$this->textbuffer = array();
	}
	else {
		if (!empty($this->textbuffer)) {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
			$this->listnum++;
		}
		$this->textbuffer = array();
      }
      break;

  }//end of switch
}



function CloseTag($tag)
{
	$this->ignorefollowingspaces = false; //Eliminate exceeding left-side spaces
    //Closing tag
    if($tag=='OPTION') { $this->selectoption['ACTIVE'] = false; 	$this->lastoptionaltag = ''; }

    if($tag=='TTS' or $tag=='TTA' or $tag=='TTZ') {
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
	$ltag = strtolower($tag);
	$this->$ltag = false;
    }


    if($tag=='FONT' || $tag=='SPAN' || $tag=='CODE' || $tag=='KBD' || $tag=='SAMP' || $tag=='TT' || $tag=='VAR' 
	|| $tag=='INS' || $tag=='STRONG' || $tag=='CITE' || $tag=='SUB' || $tag=='SUP' || $tag=='S' || $tag=='STRIKE' || $tag=='DEL'
	|| $tag=='Q' || $tag=='EM' || $tag=='B' || $tag=='I' || $tag=='U' | $tag=='SMALL' || $tag=='BIG' || $tag=='ACRONYM') {

	if ($tag == 'SPAN') {
		if ($this->InlineProperties['SPAN'][$this->spanlvl]) { $this->restoreInlineProperties($this->InlineProperties['SPAN'][$this->spanlvl]); }
		unset($this->InlineProperties['SPAN'][$this->spanlvl]);
		$this->spanlvl--;
	}
	else { 
		if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
		unset($this->InlineProperties[$tag]);
	}
    }


    if($tag=='A') {
	$this->HREF=''; 
	if ($this->InlineProperties['A']) { $this->restoreInlineProperties($this->InlineProperties['A']); }
	unset($this->InlineProperties['A']);
    }



	// *********** FORM ELEMENTS ********************

    if($tag=='TEXTAREA')
    {
	$this->specialcontent = '';
	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);
    }


    if($tag=='SELECT')
    {
	$this->lastoptionaltag = '';
	$texto = $this->selectoption['SELECTED'];
	$w = $this->GetStringWidth($texto);
	if ($w == 0) { $w = 5; }
	$objattr['type'] = 'select';	// need to add into objattr
	$objattr['text'] = $texto;

	$objattr['fontfamily'] = $this->FontFamily;
	$objattr['fontsize'] = $this->FontSizePt;

	$objattr['width'] = $w + ($this->form_element_spacing['select']['outer']['h']*2)+($this->form_element_spacing['select']['inner']['h']*2) + ($this->FontSize*1.4);
	$objattr['height'] = $this->FontSize + ($this->form_element_spacing['select']['outer']['v']*2)+($this->form_element_spacing['select']['inner']['v']*2);
	$e = "»¤¬type=select,objattr=".serialize($objattr)."»¤¬";

	// Clear properties - tidy up
	$properties = array();

	// Output it to buffers
	if ($this->tablestart) {
		$this->cell[$this->row][$this->col]['textbuffer'][] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
		$this->cell[$this->row][$this->col]['s'] += $objattr['width'] ;
	}
	else {
		$this->textbuffer[] = array($e,$this->HREF,$this->currentfontstyle,$this->colorarray,$this->currentfontfamily,$this->SUP,$this->SUB,'',$this->strike,$this->outlineparam,$this->spanbgcolorarray,$this->currentfontsize);
	}

	$this->selectoption = array();
	$this->specialcontent = '';

	if ($this->InlineProperties[$tag]) { $this->restoreInlineProperties($this->InlineProperties[$tag]); }
	unset($this->InlineProperties[$tag]);

    }


	// *********** BLOCKS ********************

    if($tag=='P' || $tag=='DIV' || $tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4' || $tag=='H5' || $tag=='H6' || $tag=='PRE' 
	 || $tag=='FORM' || $tag=='ADDRESS' || $tag=='BLOCKQUOTE' || $tag=='CENTER' || $tag=='DT'  || $tag=='DD'  || $tag=='DT' ) { 
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->blockjustfinished=true;
	if($this->tablestart) {
		if ($this->InlineProperties['BLOCKINTABLE']) { $this->restoreInlineProperties($this->InlineProperties['BLOCKINTABLE']); }
		unset($this->InlineProperties['BLOCKINTABLE']);
		return;
	}
	$this->lastoptionaltag = '';
	$this->divbegin=false;

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

	//Print content
	if ($this->lastblocklevelchange == 1) { $blockstate = 3; }	// Top & bottom margins/padding
	else if ($this->lastblocklevelchange == -1) { $blockstate = 2; }	// Bottom margins/padding only
	// called from after e.g. </table> </div> </div> ...    Outputs block margin/border and padding

	// Edited mPDF 1.1 - </div></div> CSS padding from 1st blck was not printing
//	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange == 1) {
	if (count($this->textbuffer) == 0 && $this->lastblocklevelchange != 0) {

		$this->newFlowingBlock( $this->block[$this->blocklevel]['width'],$this->lineheight,'',false,false,2,true);	// true = newblock
		$this->finishFlowingBlock(true);	// true = END of flowing block
		$this->PaintDivBorder('',$blockstate);
	}
	else {
		$this->printbuffer($this->textbuffer,$blockstate); 
	}
	$this->textbuffer=array();

	// Added mPDF 1.1 keeping block together on one page
	if ($this->blk[$this->blklvl]['keep_block_together']) {
		$this->printdivbuffer(); 
	}

	if($tag=='PRE') { $this->ispre=false; }

	//Reset values
	$this->Reset();

	if ($this->blklvl > 0) {	// ==0 SHOULDN'T HAPPEN - NOT XHTML 
	   if ($this->blk[$this->blklvl]['tag'] == $tag) {	// ==0 SHOULDN'T HAPPEN - NOT XHTML 
		unset($this->blk[$this->blklvl]);
		$this->blklvl--;
	   }
	}

	$this->lastblocklevelchange = -1 ;
	// Reset Inline-type properties
	if ($this->blk[$this->blklvl]['InlineProperties']) { $this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']); }

	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

    }


	// *********** TABLES ********************

    if($tag=='TH') $this->SetStyle('B',false);

    if(($tag=='TH' or $tag=='TD') && $this->tablestart) {
	$this->lastoptionaltag = 'TR';
	$this->tdbegin = false;

	// Added for correct calculation of cell column width - otherwise misses the last line if not end </p> etc.
	if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}
	elseif($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
		$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s']; 
	}

	// Remove last <br> if at end of cell
	$ntb = count($this->cell[$this->row][$this->col]['textbuffer']);
	if ($this->cell[$this->row][$this->col]['textbuffer'][$ntb-1][0] == "\n") {
		unset($this->cell[$this->row][$this->col]['textbuffer'][$ntb-1]);
	}



    }

    if($tag=='TR' && $this->tablestart) {
	$this->lastoptionaltag = '';
	// Edited mPDF 1.3 for rotated text in cell
	$this->trow_text_rotate = '';
	$this->tabletheadjustfinished = false;
   }

    if($tag=='THEAD') {
	$this->lastoptionaltag = '';
	$this->tablethead = 0;
	$this->tabletheadjustfinished = true;
	$this->thead_font_weight = '';
	$this->SetStyle('B',false);
	// Added mPDF 1.1
	$this->thead_font_style = '';
	$this->SetStyle('I',false);

	$this->thead_valign_default = '';
	$this->thead_textalign_default = '';
    }



    if($tag=='TABLE' && $this->tablestart) { // TABLE-END (if NOT tablestart it means a nested table was closed earlier)
	$this->lastoptionaltag = '';
	$this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->table['cells'] = $this->cell;
	$this->table['wc'] = array_pad(array(),$this->table['nc'],array('miw'=>0,'maw'=>0));
	$this->table['hr'] = array_pad(array(),$this->table['nr'],0);
	if ($this->table_margin_top ) { $this->DivLn($this->table_margin_top ,$this->blklvl,true,1); } 	// collapsible
	if ($this->ColActive) { $this->table_rotate = 0; }
	if ($this->table_rotate <> 0) {
		$this->tablebuffer = array();
		$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5);		// Max width for rotated table
		$this->tbrot_maxh = $this->w - ($this->x + $this->rMargin);		// Max width for rotated table
	}
	$this->shrin_k = 1;
	$save_table = $this->table;

	$check = $this->_tableColumnWidth($this->table);

	$originalcellPaddingL = $this->cellPaddingL;
	$originalcellPaddingR = $this->cellPaddingR;
	$originalcellPaddingT = $this->cellPaddingT;
	$originalcellPaddingB = $this->cellPaddingB;
	$originalcMarginL = $this->cMarginL;
	$originalcMarginR = $this->cMarginR;
	$originalcMarginT = $this->cMarginT;
	$originalcMarginB = $this->cMarginB;

	if ($this->table_rotate && (($this->shrink_tables_to_fit && $check > $this->shrink_tables_to_fit) || ($this->shrink_this_table_to_fit && $check > $this->shrink_this_table_to_fit)) && (!$this->isCJK)) {
		$this->AddPage();
		// Reset 
		$this->tbrot_maxw = $this->h - ($this->y + $this->bMargin + 5);		// Max width for rotated table
		$this->tbrot_maxh = $this->w - ($this->x + $this->rMargin);		// Max width for rotated table
	}

	if (($this->shrink_tables_to_fit || $this->shrink_this_table_to_fit) && (!$this->isCJK) && ($check > 1)) {	
		if ($this->shrink_this_table_to_fit) {
			if ($check > $this->shrink_this_table_to_fit) { $check = $this->shrink_this_table_to_fit; }
		}
		else {	// General setting
			if ($check > $this->shrink_tables_to_fit) { $check = $this->shrink_tables_to_fit; }
		}
		$this->shrin_k = $check;
 		$this->cellPaddingR = $this->cellPaddingR / ($this->shrin_k) ;
 		$this->cellPaddingL = $this->cellPaddingL / ($this->shrin_k) ;
 		$this->cellPaddingT = $this->cellPaddingT / ($this->shrin_k) ;
 		$this->cellPaddingB = $this->cellPaddingB / ($this->shrin_k) ;
 		$this->cMarginR = $this->cMarginR / ($this->shrin_k) ;
 		$this->cMarginL = $this->cMarginL / ($this->shrin_k) ;
 		$this->cMarginT = $this->cMarginT / ($this->shrin_k) ;
 		$this->cMarginB = $this->cMarginB / ($this->shrin_k) ;
 		$this->default_font_size = $this->default_font_size / ($this->shrin_k) ;
		$this->SetFontSize($this->default_font_size, false );
		$this->table = $save_table ;			// reinstate
		for($j = 0 ; $j < $this->table['nc'] ; $j++ ) { //columns
		   for($i = 0 ; $i < $this->table['nr']; $i++ ) { //rows
			$c = &$this->table['cells'][$i][$j];
			if (isset($c) && $c)  {
				if (isset($c['maxs']) and $c['maxs'] != '') { 
					$c['maxs'] = $c['maxs'] / $this->shrin_k;
				}
				if (isset($c['s']) and $c['s'] != '') { 
					$c['s']  = $c['s']  / $this->shrin_k;
				}
			}
		   }//rows
		}//columns
		$check = $this->_tableColumnWidth($this->table);	// repeat
	}

	$this->SetLineHeight('',$this->table_lineheight);
	$this->_tableWidth($this->table);
	$check = $this->_tableHeight($this->table);

	// This time IF CELL TOO HIGH FOR PAGE?
	if (($this->shrink_tables_to_fit || $this->shrink_this_table_to_fit) && (!$this->isCJK) && ($check > 1)) {	
		if ($this->shrink_this_table_to_fit) {
			if ($check > $this->shrink_this_table_to_fit) { $check = $this->shrink_this_table_to_fit; }
		}
		else {	// General setting
			if ($check > $this->shrink_tables_to_fit) { $check = $this->shrink_tables_to_fit; }
		}
		$this->shrin_k1 = $check;
		$this->shrin_k *= $check;
 		$this->cellPaddingR = $originalcellPaddingR / ($this->shrin_k) ;
 		$this->cellPaddingL = $originalcellPaddingL / ($this->shrin_k) ;
 		$this->cellPaddingT = $originalcellPaddingT / ($this->shrin_k) ;
 		$this->cellPaddingB = $originalcellPaddingB / ($this->shrin_k) ;
 		$this->cMarginR = $originalcMarginR / ($this->shrin_k) ;
 		$this->cMarginL = $originalcMarginL / ($this->shrin_k) ;
 		$this->cMarginT = $originalcMarginT / ($this->shrin_k) ;
 		$this->cMarginB = $originalcMarginB / ($this->shrin_k) ;
 		$this->default_font_size = $this->default_font_size / ($this->shrin_k1) ;
		$this->SetFontSize($this->default_font_size, false );
		$this->table = $save_table ;			// reinstate
		for($j = 0 ; $j < $this->table['nc'] ; $j++ ) { //columns
		   for($i = 0 ; $i < $this->table['nr']; $i++ ) { //rows
			$c = &$this->table['cells'][$i][$j];
			if (isset($c) && $c)  {
				if (isset($c['maxs']) and $c['maxs'] != '') { 
					$c['maxs'] = $c['maxs'] / $this->shrin_k;
				}
				if (isset($c['s']) and $c['s'] != '') { 
					$c['s']  = $c['s']  / $this->shrin_k;
				}
			}
		   }//rows
		}//columns
		$check = $this->_tableColumnWidth($this->table);	// repeat
		$this->SetLineHeight('',$this->table_lineheight);
		$this->_tableWidth($this->table);
		$this->_tableHeight($this->table);
	}


	$this->_tableWrite($this->table);
	if ($this->table_rotate) {
		if (count($this->tablebuffer)) { $this->printtablebuffer(); }
	}
	$this->table_rotate = 0;	// flag used for table rotation


	//Reset values
	$this->shrin_k = 1;
	$this->shrink_this_table_to_fit = 0;
	$this->tablestart=false; //bool
	$this->table=array(); //array
	$this->cell=array(); //array 
	$this->col=-1; //int
	$this->row=-1; //int
	$this->Reset();
	// SPACING AFTER TABLE
	if ($this->table_margin_bottom) {
		$this->DivLn($this->table_margin_bottom,$this->blklvl,true,1); 	// collapsible
	}
	$this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

 	$this->cellPaddingL = $originalcellPaddingL;
 	$this->cellPaddingT = $originalcellPaddingT;
 	$this->cellPaddingR = $originalcellPaddingR;
 	$this->cellPaddingB = $originalcellPaddingB;
 	$this->cMarginL = $originalcMarginL;
 	$this->cMarginT = $originalcMarginT;
 	$this->cMarginR = $originalcMarginR;
 	$this->cMarginB = $originalcMarginB;
 	$this->default_font_size = $this->original_default_font_size;
	$this->default_font = $this->original_default_font;
   	$this->SetFontSize($this->default_font_size, false);
	$this->SetFont($this->default_font,'',0,false);
	$this->SetLineHeight();
	if ($this->blk[$this->blklvl]['InlineProperties']) {$this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']);}

    }


	// *********** LISTS ********************

    if($tag=='LI') { $this->lastoptionaltag = ''; }

    if(($tag=='UL') or ($tag=='OL')) {
      $this->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
	$this->lastoptionaltag = '';
	// A simple list for inside a table
	if($this->tablestart) {
		$this->listlist[$this->listlvl]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		unset($this->listlist[$this->listlvl]);
		$this->listlvl--;
		$this->listnum = $this->listlist[$this->listlvl]['MAXNUM']; // restore previous levels
		return;
	}

	if ($this->listlvl > 1) { // returning one level
		$this->listjustfinished=true;
		if (!empty($this->textbuffer)) { 
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
		}
		$this->textbuffer = array();
		$occur = $this->listoccur[$this->listlvl]; 
		$this->listlist[$this->listlvl][$occur]['MAXNUM'] = $this->listnum; //save previous lvl's maxnum
		$this->listlvl--;
		$occur = $this->listoccur[$this->listlvl];
		$this->listnum = $this->listlist[$this->listlvl][$occur]['MAXNUM']; // recover previous level's number
		$this->listtype = $this->listlist[$this->listlvl][$occur]['TYPE']; // recover previous level's type
		if ($this->InlineProperties['LIST'][$this->listlvl]) { $this->restoreInlineProperties($this->InlineProperties['LIST'][$this->listlvl]); }

	}
	else { // We are closing the last OL/UL tag
		if (!empty($this->textbuffer)) {
			$this->listitem[] = array($this->listlvl,$this->listnum,$this->textbuffer,$this->listoccur[$this->listlvl]);
		}
		$this->textbuffer = array();
		$this->listlvl--;

		$this->printlistbuffer();
		unset($this->InlineProperties['LIST']);
		// SPACING AFTER LIST (Top level only)
		$this->Ln(0);
		if ($this->list_margin_bottom) {
			$this->DivLn($this->list_margin_bottom,$this->blklvl,true,1); 	// collapsible
		}
		if ($this->blk[$this->blklvl]['InlineProperties']) {$this->restoreInlineProperties($this->blk[$this->blklvl]['InlineProperties']);}
	}
    }


}



function printlistbuffer()
{
    //Save x coordinate
    $x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];
    $this->cMarginL = 0;
    $this->cMarginR = 0;
    $bak_page = $this->page;

    foreach($this->listitem as $item)
    {
	// COLS
	$oldcolumn = $this->CurrCol;

	  $this->bulletarray = array();
        //Get list's buffered data
        $this->listlvl = $lvl = $item[0];
        $num = $item[1];
        $this->textbuffer = $item[2];
        $occur = $item[3];
        $type = $this->listlist[$lvl][$occur]['TYPE'];
        $maxnum = $this->listlist[$lvl][$occur]['MAXNUM'];

	  $this->restoreInlineProperties($this->InlineProperties['LIST'][$lvl]);
	  $this->SetFont($this->FontFamily,$this->FontStyle,$this->FontSizePt,true,true);	// force to write


	if ($this->list_lineheight[$lvl]) {
		$this->SetLineHeight('',$this->list_lineheight[$lvl]);
	}
	else {
		$this->SetLineHeight();
	}

	$clh = $this->lineheight;

        //Set default width & height values

        $this->divwidth = $this->blk[$this->blklvl]['inner_width'];
        $this->divheight = $this->lineheight;
	  $typefont = $list_base_font;
        switch($type) //Format type
        {
          case 'A':
              $num = dec2alpha($num,true);
              $maxnum = dec2alpha($maxnum,true);
	  	if ($this->directionality == 'rtl') { $type = "." . str_pad($num,strlen($maxnum),' ',STR_PAD_RIGHT); }
		else { $type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . "."; }
              break;
          case 'a':
              $num = dec2alpha($num,false);
              $maxnum = dec2alpha($maxnum,false);
	  	if ($this->directionality == 'rtl') { $type = "." . str_pad($num,strlen($maxnum),' ',STR_PAD_RIGHT); }
		else { $type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . "."; }
              break;
          case 'I':
              $num = dec2roman($num,true);
              $maxnum = dec2roman($maxnum,true);
	  	if ($this->directionality == 'rtl') { $type = "." . str_pad($num,strlen($maxnum),' ',STR_PAD_RIGHT); }
		else { $type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . "."; }
              break;
          case 'i':
              $num = dec2roman($num,false);
              $maxnum = dec2roman($maxnum,false);
	  	if ($this->directionality == 'rtl') { $type = "." . str_pad($num,strlen($maxnum),' ',STR_PAD_RIGHT); }
		else { $type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . "."; }
              break;
          case '1':
	  	if ($this->directionality == 'rtl') { $type = "." . str_pad($num,strlen($maxnum),' ',STR_PAD_RIGHT); }
		else { $type = str_pad($num,strlen($maxnum),' ',STR_PAD_LEFT) . "."; }
            break;
          case 'disc':
              $type = $this->chrs[108]; // bullet disc in Zapfdingbats  'l'
		  $typefont = 'zapfdingbats';
              break;
          case 'circle':
              $type = $this->chrs[109]; // circle in Zapfdingbats   'm'
		  $typefont = 'zapfdingbats';
            break;
          case 'square':
              $type = $this->chrs[110]; //black square in Zapfdingbats font   'n'
		  $typefont = 'zapfdingbats';
              break;
          default: break;
        }

      $space_width = $this->GetStringWidth(' ');
	if ($typefont == 'zapfdingbats') {
		if ($type == 'l') { $blt_width = (0.791 * $this->FontSize/2.5)+($space_width * 1.5); }
		else if ($type == 'm') { $blt_width = (0.873 * $this->FontSize/2.5)+($space_width * 1.5); }
		else if ($type == 'n') { $blt_width = (0.761 * $this->FontSize/2.5)+($space_width * 1.5); }
	}
	else { 
	      $blt_width = $this->GetStringWidth($type)+($space_width * 1.5);	// spacing from bullet/number to List item
	}

	$indent = ($this->list_indent[$lvl-1]*($lvl-1+$this->list_indent_first_level));
      $this->divwidth = $this->blk[$this->blklvl]['width'] - ($indent + $blt_width) ;
	$this->divalign = $this->list_align[$this->listlvl];

	if ($this->directionality == 'rtl') { 
        $type = ' ' . $type;
        $xb = $this->blk[$this->blklvl]['inner_width'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] - $indent - $blt_width; //Bullet position (relative)

        //Output bullet
	  $this->bulletarray = array('w'=>$blt_width,'h'=>$clh,'txt'=>$type,'x'=>$xb,'align'=>'R','font'=>$typefont,'level'=>$lvl );

	  $this->x = $x;
	}
	else {
        $type .= ' ';
	  $xb =  $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left'] - $blt_width; //Bullet position (relative)

        //Output bullet
	  $this->bulletarray = array('w'=>$blt_width,'h'=>$clh,'txt'=>$type,'x'=>$xb,'align'=>'L','font'=>$typefont,'level'=>$lvl );

	  $this->x = $x + $indent + $blt_width;

	}

      //Print content
  	$this->printbuffer($this->textbuffer,'',false,true);
      $this->textbuffer=array();

	// Added to correct for OddEven Margins
   	if  ($this->page != $bak_page) {
		$x=$x +$this->MarginCorrection;
		$bak_page = $this->page;
	}
	// OR COLUMN CHANGE
	if ($this->CurrCol != $oldcolumn) {
		if ($this->directionality == 'rtl') {
			$x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
		}
		else {
			$x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
		}
		$oldcolumn = $this->CurrCol;
	}

    }
    //Reset all used values
    $this->listoccur = array();
    $this->listitem = array();
    $this->listlist = array();
    $this->listlvl = 0;
    $this->listnum = 0;
    $this->listtype = '';
    $this->textbuffer = array();
    $this->divwidth = 0;
    $this->divheight = 0;
    $this->x = $this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'];

}




function printbuffer($arrayaux,$blockstate=0,$is_table=false,$is_list=false)
{
// $blockstate = 0;	// NO margins/padding
// $blockstate = 1;	// Top margins/padding only
// $blockstate = 2;	// Bottom margins/padding only
// $blockstate = 3;	// Top & bottom margins/padding

	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;


    	$bak_y = $this->y;
	$bak_x = $this->x;
	if (!$is_table && !$is_list) {
		if ($this->blk[$this->blklvl]['align']) { $align = $this->blk[$this->blklvl]['align']; }
		// Block-align is set by e.g. <.. align="center"> Takes priority for this block but not inherited
		if ($this->blk[$this->blklvl]['block-align']) { $align = $this->blk[$this->blklvl]['block-align']; }
		$this->divwidth = $this->blk[$this->blklvl]['width'];
	}
	else {
		$align = $this->divalign;
	}
	$oldpage = $this->page;

	// ADDED for Out of Block now done as Flowing Block
	if ($this->divwidth == 0) { 
		$this->divwidth = $this->pgwidth; 
	}



	if (!$is_table && !$is_list) { $this->SetLineHeight($this->FontSizePt,$this->blk[$this->blklvl]['line_height']); }
	$this->divheight = $this->lineheight;
	$old_height = $this->divheight;

    // As a failsafe - if font has been set but not output to page
    $this->SetFont($this->default_font,'',$this->default_font_size,true,true);	// force output to page

    $array_size = count($arrayaux);

    $this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,true);	// true = newblock

	// Added in mPDF 1.1 - Otherwise <div><div><p> did not output top margins/padding for 1st/2nd div
    if ($array_size == 0) { $this->finishFlowingBlock(true); }	// true = END of flowing block


    for($i=0;$i < $array_size; $i++)
    {

	// COLS
	$oldcolumn = $this->CurrCol;
     $vetor = $arrayaux[$i];
      if ($i == 0 and $vetor[0] != "\n" and !$this->ispre) {
		$vetor[0] = ltrim($vetor[0]);
	}

	// FIXED TO ALLOW IT TO SHOW '0' 
      if (empty($vetor[0]) && !($vetor[0]==='0') && empty($vetor[7])) { //Ignore empty text and not carrying an internal link
		//Check if it is the last element. If so then finish printing the block
	     	if ($i == ($array_size-1)) { $this->finishFlowingBlock(true); }	// true = END of flowing block
		continue;
	}

      //Activating buffer properties
      if(isset($vetor[11]) and $vetor[11] != '') { 	 // Font Size
		if ($is_table && $this->shrin_k) {
			$this->SetFontSize($vetor[11]/$this->shrin_k,false); 
		}
		else {
			$this->SetFontSize($vetor[11],false); 
		}
	}
      if(isset($vetor[10]) and !empty($vetor[10])) //Background color
      {
		$this->spanbgcolorarray = $vetor[10];
		$this->spanbgcolor = true;
      }
      if(isset($vetor[9]) and !empty($vetor[9])) // Outline parameters
      {
          $cor = $vetor[9]['COLOR'];
          $outlinewidth = $vetor[9]['WIDTH'];
          $this->SetTextOutline($outlinewidth,$cor['R'],$cor['G'],$cor['B']);
          $this->outline_on = true;
      }
      if(isset($vetor[8]) and $vetor[8] === true) // strike-through the text
      {
          $this->strike = true;
      }
      if(isset($vetor[7]) and $vetor[7] != '') // internal link: <a name="anyvalue">
      {
	  if ($this->ColActive) { $ily = $this->y0; } else { $ily = $this->y; }	// use top of columns
        $this->internallink[$vetor[7]] = array("Y"=>$ily,"PAGE"=>$this->page );
	  if ($this->Anchor2Bookmark ==1) {
		$this->Bookmark($vetor[7],0,$ily);
	  }
	  else if ($this->Anchor2Bookmark == 2) {
		$this->Bookmark($vetor[7]." (p. $this->page)",0,$ily);
	  }
        if (empty($vetor[0])) { //Ignore empty text
		//Check if it is the last element. If so then finish printing the block
      	if ($i == ($array_size-1)) { $this->finishFlowingBlock(true); }	// true = END of flowing block
		continue;
	  }
      }
      if(isset($vetor[6]) and $vetor[6] === true) // Subscript 
      {
  		$this->SUB = true;
      }
      if(isset($vetor[5]) and $vetor[5] === true) // Superscript
      {
		$this->SUP = true;
      }
      if(isset($vetor[4]) and $vetor[4] != '') {  // Font Family
		$font = $this->SetFont($vetor[4],$this->FontStyle,0,false); 
	}
      if (!empty($vetor[3])) //Font Color
      {
		$cor = $vetor[3];
		$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
      }
      if(isset($vetor[2]) and $vetor[2] != '') //Bold,Italic,Underline styles
      {
          if (strpos($vetor[2],"B") !== false) $this->SetStyle('B',true);
          if (strpos($vetor[2],"I") !== false) $this->SetStyle('I',true);
          if (strpos($vetor[2],"U") !== false) $this->SetStyle('U',true); 
      }
      if(isset($vetor[1]) and $vetor[1] != '') //LINK
      {
        if (strpos($vetor[1],".") === false) //assuming every external link has a dot indicating extension (e.g: .html .txt .zip www.somewhere.com etc.) 
        {
          //Repeated reference to same anchor?
          while(array_key_exists($vetor[1],$this->internallink)) $vetor[1]="#".$vetor[1];
          $this->internallink[$vetor[1]] = $this->AddLink();
          $vetor[1] = $this->internallink[$vetor[1]];
        }
        $this->HREF = $vetor[1];					// HREF link style set here ******
      }

	// SPECIAL CONTENT - IMAGES & FORM OBJECTS
      //Print-out special content
      if (isset($vetor[0]) and $vetor[0]{0} == '»' and $vetor[0]{1} == '¤' and $vetor[0]{2} == '¬') { //identifier has been identified!

        $content = split("»¤¬",$vetor[0],2);
        $content = split(",",$content[1],2);
        foreach($content as $value) {
          $value = split("=",$value,2);
          $specialcontent[$value[0]] = $value[1];
        }

		$objattr = unserialize($specialcontent['objattr']);
		if ($is_table) {
			$maxWidth = $this->divwidth - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR); 
		}
		else {
			$maxWidth = $this->divwidth - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w']); 
		}
		list($skipln) = $this->inlineObject($objattr['type'],'',$this->y,$objattr,$this->lMargin, ($this->flowingBlockAttr['contentWidth']/$this->k) , $maxWidth,$this->flowingBlockAttr['height'],false,$is_table);
		//  1 -> New line needed because of width
		// -1 -> Will fit width on line but NEW PAGE REQUIRED because of height
		// -2 -> Will not fit on line therefore needs new line but thus NEW PAGE REQUIRED

		$iby = $this->y;
		$oldpage = $this->page;
		if ($skipln == 1 || $skipln == -2) {
            	$this->finishFlowingBlock();
	           	$this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,false); //false=newblock
		}
		$thispage = $this->page;

		if ($skipln <0 && $this->AcceptPageBreak() && $thispage==$oldpage) { 	// the previous lines can already have triggered page break
			$this->AddPage(); 
	  		// Added to correct Images already set on line before page advanced
			// i.e. if second inline image on line is higher than first and forces new page
			if (count($this->objectbuffer)) {
				$yadj = $iby - $this->y;
				foreach($this->objectbuffer AS $ib=>$val) {
					if ($this->objectbuffer[$ib]['OUTER-Y'] ) $this->objectbuffer[$ib]['OUTER-Y'] -= $yadj;
					if ($this->objectbuffer[$ib]['BORDER-Y']) $this->objectbuffer[$ib]['BORDER-Y'] -= $yadj;
					if ($this->objectbuffer[$ib]['INNER-Y']) $this->objectbuffer[$ib]['INNER-Y'] -= $yadj;
				}
			}
		}


	  	// Added to correct for OddEven Margins
   	  	if  ($this->page != $oldpage) {
			$bak_x += $this->MarginCorrection;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		}
		$this->x = $bak_x;
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}


		$this->WriteFlowingBlock($vetor[0]); 


      }	// END If special content

      else { //THE text
	  if ($this->tablestart) { $paint_ht_corr = 0; }	// To move the y up when new column/page started if div border needed
	  else { $paint_ht_corr = $this->blk[$this->blklvl]['border_top']['w']; }

        if ($vetor[0] == "\n") { //We are reading a <BR> now turned into newline ("\n")
		if ($this->flowingBlockAttr['content']) {
			$this->finishFlowingBlock();
		}
		else if ($is_table && $i > 0 && ($i != ($array_size-1))) {
			$this->DivLn($this->lineheight); 
		}
		else if (!$is_table) {
			$this->DivLn($this->lineheight); 
		}
	  	// Added to correct for OddEven Margins
   	  	if  ($this->page != $oldpage) {
			$bak_x=$bak_x +$this->MarginCorrection;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		}
		$this->x = $bak_x;
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}

		$this->newFlowingBlock( $this->divwidth,$this->divheight,$align,$is_table,$is_list,$blockstate,false);	// false = newblock
          }
          else {


		$this->WriteFlowingBlock( $vetor[0]);
		  // Added to correct for OddEven Margins
   		  if  ($this->page != $oldpage) {
			$bak_x=$bak_x +$this->MarginCorrection;
			$this->x = $bak_x;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		  }
		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}
	    }

      }

      //Check if it is the last element. If so then finish printing the block
      if ($i == ($array_size-1)) {
		$this->finishFlowingBlock(true);	// true = END of flowing block
		  // Added to correct for OddEven Margins
   		  if  ($this->page != $oldpage) {
			$bak_x += $this->MarginCorrection;
			$this->x = $bak_x;
			$oldpage = $this->page;
				$y = $this->tMargin - $paint_ht_corr ;
				$this->oldy = $this->tMargin - $paint_ht_corr ;
				$old_height = 0;
		  }

		// COLS
		// OR COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($this->directionality == 'rtl') {
				$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			else {
				$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth+$this->ColGap);
			}
			$this->x = $bak_x;
			$oldcolumn = $this->CurrCol;
			$y = $this->y0 - $paint_ht_corr ;
			$this->oldy = $this->y0 - $paint_ht_corr ;
			$old_height = 0;
		}

	}

	// RESETTING VALUES
	$this->SetTextColor(0);
	$this->SetDrawColor(0);
	$this->SetFillColor(255);
	$this->colorarray = array();
	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;
	$this->issetcolor = false;
	$this->HREF = '';
	$this->outlineparam = array();
	$this->SetTextOutline(false);
      $this->outline_on = false;
	$this->SUP = false;
	$this->SUB = false;

	$this->strike = false;

	$this->currentfontfamily = '';  
	$this->currentfontsize = '';  
	$this->currentfontstyle = '';  
	if ($this->tablestart) {
		$this->SetLineHeight('',$this->table_lineheight);
	}
	else {
		$this->SetLineHeight('',$this->blk[$this->blklvl]['line_height']);	// sets default line height
	}
	$this->SetStyle('B',false);
	$this->SetStyle('I',false);
	$this->SetStyle('U',false);
	$this->toupper = false;
	$this->tolower = false;
	$this->SetDash(); //restore to no dash
	$this->dash_on = false;
	$this->dotted_on = false;

    }//end of for(i=0;i<arraysize;i++)



    // PAINT DIV BORDER	// DISABLED IN COLUMNS AS DOESN'T WORK WHEN BROKEN ACROSS COLS??
    if (($this->blk[$this->blklvl]['border']) && $blockstate  && ($this->y != $this->oldy)) {
	$bottom_y = $this->y;	// Does not include Bottom Margin

	if ($this->blk[$this->blklvl]['startpage'] != $this->page) {
		$this->PaintDivBorder('pagetop',$blockstate);
	}
	else {
		$this->PaintDivBorder('',$blockstate);
	}
	$this->y = $bottom_y; 
	$this->x = $bak_x;
    }

    // Reset Font
    $this->SetFontSize($this->default_font_size,false); 


}

function PaintDivBorder($divider='',$blockstate=0,$blvl=0) {

	// Borders are disabled in columns - messes up the repositioning in printcolumnbuffer
	if ($this->ColActive) { return ; }
	$save_y = $this->y;
	if (!$blvl) { $blvl = $this->blklvl; }

	$x0 = $this->blk[$blvl]['x0'];	// left
	$y1 = $this->blk[$blvl]['y1'];	// bottom
	if (!$y1) { $y1 = $this->y; }
	if ($this->blk[$this->blklvl]['startpage'] != $this->page) { $continuingpage = true; } else { $continuingpage = false; } 


		// BORDERS
		$y0 = $this->blk[$blvl]['y0'];
		$h = $y1 - $y0;
		$w = $this->blk[$blvl]['width'];

		//if ($this->blk[$blvl]['border_top']) {
		// Reinstate line above for dotted line divider when block border crosses a page
		if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
			$tbd = $this->blk[$blvl]['border_top'];
			if ($tbd['s']) {
				if ($divider == 'pagetop' || $continuingpage) { $tbd['w'] = $this->splitdivborderwidth; }
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				$this->y = $y0 + ($tbd['w']/2);
				if ($divider == 'pagetop' || $continuingpage) { $this->y -= $this->splitdivborderwidth; }
				if (($tbd['style'] == 'dashed' && $divider != 'pagetop' && !$continuingpage)) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted' || $divider == 'pagetop' || $continuingpage) {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2) , $this->y , $x0 + $w - ($tbd['w']/2), $this->y);
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_left']) { 
			$tbd = $this->blk[$blvl]['border_left'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				$this->y = $y0 + ($tbd['w']/2);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2), $this->y, $x0 + ($tbd['w']/2), $y0 + $h -($tbd['w']/2));
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($this->blk[$blvl]['border_right']) { 
			$tbd = $this->blk[$blvl]['border_right'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
			 	$this->y = $y0 + ($tbd['w']/2);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + $w - ($tbd['w']/2), $this->y, $x0 + $w - ($tbd['w']/2), $y0 + $h - ($tbd['w']/2));
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		//if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1) { 
		// Reinstate line above for dotted line divider when block border crosses a page
		if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') { 
			$tbd = $this->blk[$blvl]['border_bottom'];
			if ($tbd['s']) {
				if ($divider == 'pagebottom') { $tbd['w'] = $this->splitdivborderwidth; }
				$this->SetLineWidth($tbd['w']);
				$this->y = $y0 + $h - ($tbd['w']/2);
				if ($divider == 'pagebottom') { $this->y += $this->splitdivborderwidth; }
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if (($tbd['style'] == 'dashed' && $divider != 'pagebottom')) {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted' || $divider == 'pagebottom') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + ($tbd['w']/2) , $this->y, $x0 + $w - ($tbd['w']/2), $this->y);
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		$this->SetDash(); 


	$this->y = $save_y; 
}


function PaintImgBorder($objattr) {

	// Borders are disabled in columns - messes up the repositioning in printcolumnbuffer
	if ($this->ColActive) { return ; }

		$h = $objattr['BORDER-HEIGHT'];
		$w = $objattr['BORDER-WIDTH'];
		$x0 = $objattr['BORDER-X'];
		$y0 = $objattr['BORDER-Y'];

		// BORDERS
		if ($objattr['border_top']) { 
			$tbd = $objattr['border_top'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0, $x0 + $w, $y0);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_left']) { 
			$tbd = $objattr['border_left'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0, $x0, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_right']) { 
			$tbd = $objattr['border_right'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0 + $w, $y0, $x0 + $w, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		if ($objattr['border_bottom']) { 
			$tbd = $objattr['border_bottom'];
			if ($tbd['s']) {
				$this->SetLineWidth($tbd['w']);
		      	$this->SetDrawColor($tbd['c']['R'],$tbd['c']['G'],$tbd['c']['B']);
				if ($tbd['style'] == 'dashed') {
					$dashsize = 2;	// final dash will be this + 1*linewidth
					$dashsizek = 1.5;	// ratio of Dash/Blank
					$this->SetDash($dashsize,($dashsize/$dashsizek)+($this->LineWidth*2));
				}
				else if ($tbd['style'] == 'dotted') {
  					//Round join and cap
  					$this->_out("\n".'1 J'."\n".'1 j'."\n");
					$this->SetDash(0.001,($this->LineWidth*3));
				}
				$this->Line($x0, $y0 + $h, $x0 + $w, $y0 + $h);
				// Reset Corners and Dash off
  				$this->_out("\n".'2 J'."\n".'2 j'."\n");
				$this->SetDash(); 

			}
		}
		$this->SetDash(); 

}





function Reset()
{

	$this->SetTextColor(0);
	$this->SetDrawColor(0);
	$this->SetFillColor(255);
	$this->colorarray = array();
	$this->spanbgcolorarray = array();
	$this->spanbgcolor = false;
	$this->issetcolor = false;
	$this->HREF = '';
	$this->outlineparam = array();
      $this->outline_on = false;
	$this->SetTextOutline(false);
	$this->SUP = false;
	$this->SUB = false;
	$this->strike = false;

	$this->SetFont($this->default_font,'',0,false);
	$this->SetFontSize($this->default_font_size,false);

	$this->currentfontfamily = '';  
	$this->currentfontsize = '';  
	if ($this->tablestart) {
		$this->SetLineHeight('',$this->table_lineheight);
	}
	else {
		$this->SetLineHeight('',$this->blk[$this->blklvl]['line_height']);	// sets default line height
	}
	$this->SetStyle('B',false);
	$this->SetStyle('I',false);
	$this->SetStyle('U',false);
	$this->toupper = false;
	$this->tolower = false;
	$this->SetDash(); //restore to no dash
	$this->dash_on = false;
	$this->dotted_on = false;
	$this->divwidth = 0;
	$this->divheight = 0;
	$this->divalign = $this->defaultAlign;
	$this->divrevert = false;
	$this->oldy = -1;
}

function ReadMetaTags($html)
{
	// changes anykey=anyvalue to anykey="anyvalue" (only do this when this happens inside tags)
	$regexp = '/ (\\w+?)=([^\\s>"]+)/si'; 
 	$html = preg_replace($regexp," \$1=\"\$2\"",$html);

	if (preg_match('/<title>(.*?)<\/title>/si',$html,$m)) {
		$this->SetTitle($m[1]); 
	}

  preg_match_all('/<meta .*?(name|content)="(.*?)" .*?(name|content)="(.*?)".*?>/si',$html,$aux);
  $firstattr = $aux[1];
  $secondattr = $aux[3];
  for( $i = 0 ; $i < count($aux[0]) ; $i++)
  {

     $name = ( strtoupper($firstattr[$i]) == "NAME" )? strtoupper($aux[2][$i]) : strtoupper($aux[4][$i]);
     $content = ( strtoupper($firstattr[$i]) == "CONTENT" )? $aux[2][$i] : $aux[4][$i];
     switch($name)
     {
       case "KEYWORDS": $this->SetKeywords($content); break;
       case "AUTHOR": $this->SetAuthor($content); break;
       case "DESCRIPTION": $this->SetSubject($content); break;
     }
  }
}


function ReadCharset($html)
{
	// Charset conversion
	if ($this->allow_charset_conversion) {
	   if (preg_match('/charset=([^\'\"\s]*)/si',$html,$m)) {
		if (strtoupper($m[1]) != 'UTF-8') {
			$this->charset_in = strtoupper($m[1]); 
		}
	   }
	}

}

//////////////////
/// CSS parser ///
//////////////////
//////////////////
/// CSS parser ///
//////////////////
//////////////////
/// CSS parser ///
//////////////////
function ReadCSS($html)
{
/*
* Rewritten in mPDF 1.2 This version supports:  .class {...} / #id { .... }
* ADDED p {...}  h1[-h6] {...}  a {...}  table {...}   thead {...}  th {...}  td {...}  hr {...}
* body {...} sets default font and fontsize
* It supports some cascaded CSS e.g. div.topic table.type1 td
* Does not support non-block level e.g. a#hover { ... }
*/

	$match = 0; // no match for instance
	$regexp = ''; // This helps debugging: showing what is the REAL string being processed


	//CSS inside external files
	$regexp = '/<link rel="stylesheet".*?href="(.+?)".*?>/si'; 
	$match = preg_match_all($regexp,$html,$CSSext);
	$ind = 0;

  if (!$match) { 	// No linked stylesheet - look for @import stylesheets
	$regexp = '/@import url\(\"(.*?\.css)\"\)/si';
	$match = preg_match_all($regexp,$html,$CSSext);
  }

  $CSSstr = '';	
  $this->cascadeCSS = array();

    while($match){
	//Fix path value
	$path = $CSSext[1][$ind];
	$path = str_replace("\\","/",$path); //If on Windows
	//Get link info and obtain its absolute path
	$regexp = '|^./|';
	$path = preg_replace($regexp,'',$path);
	if (strpos($path,"../") !== false ) { //It is a Relative Link
       $backtrackamount = substr_count($path,"../");
       $maxbacktrack = substr_count($this->basepath,"/") - 1;
       $filepath = str_replace("../",'',$path);
       $path = $this->basepath;
       //If it is an invalid relative link, then make it go to directory root
       if ($backtrackamount > $maxbacktrack) $backtrackamount = $maxbacktrack;
       //Backtrack some directories
       for( $i = 0 ; $i < $backtrackamount + 1 ; $i++ ) $path = substr( $path, 0 , strrpos($path,"/") );
       $path = $path . "/" . $filepath; //Make it an absolute path
	}
	else if( strpos($path,":/") === false) { //It is a Local Link
					if (substr($path,0,1) == "/") { 
						$tr = parse_url($this->basepath);
						$root = $tr['scheme'].'://'.$tr['host'];
						$path = $root . $path; 
					}
					else { $path = $this->basepath . $path; }
	}
	//Do nothing if it is an Absolute Link
	//END of fix path value
	// Edited mPDF 1.1 in case PHP_INI allow_url_fopen set to false
	if (ini_get('allow_url_fopen')) {
		$CSSextblock = @file_get_contents($path);
	}
	else {
		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , 1 );
		$CSSextblock = curl_exec($ch);
		curl_close($ch);
	}


	if ($CSSextblock) {
		$CSSstr .= ' '.$CSSextblock;
	}	

	$match--;
	$ind++;
    } //end of match

    $match = 0; // reset value, if needed


    $regexp = '/<style.*?>(.*?)<\/style>/si'; 
    $match = preg_match_all($regexp,$html,$CSSblock);
    if ($match) {
		$CSSstr .= ' '.implode(' ',$CSSblock[1]);
    }

    // Remove comments
    $CSSstr = preg_replace('|/\*.*?\*/|s',' ',$CSSstr);
    $CSSstr = preg_replace('/[\s\n\r\t\f]/s',' ',$CSSstr);

    if ($CSSstr ) {

	preg_match_all('/(.*?)\{(.*?)\}/',$CSSstr,$styles);
	for($i=0; $i < count($styles[1]) ; $i++)  {
		// SET array e.g. $classproperties['COLOR'] = '#ffffff';
	 	$stylestr= trim($styles[2][$i]);
		$stylearr = explode(';',$stylestr);
		foreach($stylearr AS $sta) {
			list($property,$value) = explode(':',$sta);
			$property = trim($property);
			$value = trim($value);
			if ($property && $value) {
	  			$classproperties[strtoupper($property)] = $value;
			}
		}
		$classproperties = $this->fixCSS($classproperties);


		$tagstr = strtoupper(trim($styles[1][$i]));
		$tagarr = explode(',',$tagstr);



		foreach($tagarr AS $tg) {
		  $tags = preg_split('/\s+/',trim($tg));
		  $level = count($tags);
		  if ($level == 1) {		// e.g. p or .class or #id or p.class or p#id
		     $t = trim($tags[0]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^[.](.*)$/',$t,$m)) { $tag = 'CLASS>>'.$m[1]; }
			else if (preg_match('/^[#](.*)$/',$t,$m)) { $tag = 'ID>>'.$m[1]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[.](.*)$/',$t,$m)) { $tag = $m[1].'>>CLASS>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[#](.*)$/',$t,$m)) { $tag = $m[1].'>>ID>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }

			if ($this->CSS[$tag] && $tag) { $this->CSS[$tag] = array_merge_recursive_unique($this->CSS[$tag], $classproperties); }
			else if ($tag) { $this->CSS[$tag] = $classproperties; }
		     }
		  }

		  else {
		   $tmp = array();
		   for($n=0;$n<$level;$n++) {
		     $t = trim($tags[$n]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^[.](.*)$/',$t,$m)) { $tag = 'CLASS>>'.$m[1]; }
			else if (preg_match('/^[#](.*)$/',$t,$m)) { $tag = 'ID>>'.$m[1]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[.](.*)$/',$t,$m)) { $tag = $m[1].'>>CLASS>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[#](.*)$/',$t,$m)) { $tag = $m[1].'>>ID>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }

			$tmp[] = $tag;
		    }
		   }
		   $x = &$this->cascadeCSS; 
		   foreach($tmp AS $tp) {
			$x = &$x[$tp];
		   }
		   $x = array_merge_recursive_unique($x, $classproperties); 
		   $x['depth'] = $level;
		  }


/* Fall-back - this will take the last part e.g. "div.bpmtopic table td" will just parse the td
		  else if(!$usecascadingCSS) {
		     $t = trim($tags[$level-1]);
		     if ($t) {
			$tag = '';
			if (preg_match('/^[.](.*)$/',$t,$m)) { $tag = 'CLASS>>'.$m[1]; }
			else if (preg_match('/^[#](.*)$/',$t,$m)) { $tag = 'ID>>'.$m[1]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[.](.*)$/',$t,$m)) { $tag = $m[1].'>>CLASS>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')[#](.*)$/',$t,$m)) { $tag = $m[1].'>>ID>>'.$m[2]; }
			else if (preg_match('/^('.$this->allowedCSStags.')$/',$t)) { $tag= $t; }

			if ($this->CSS[$tag] && $tag) { $this->CSS[$tag] = array_merge_recursive_unique($this->CSS[$tag], $classproperties); }
			else if ($tag) { $this->CSS[$tag] = $classproperties; }
		     }
		  }
*/

		}

  		$properties = array();
  		$values = array();
  		$classproperties = array();
	}

    } // end of if
    //Remove CSS (tags and content), if any
    $regexp = '/<style.*?>(.*?)<\/style>/si'; // it can be <style> or <style type="txt/css"> 
    $html = preg_replace($regexp,'',$html);
//print_r($this->cascadeCSS); exit;
    return $html;
}

function readInlineCSS($html)
{
  //Fix incomplete CSS code
  $size = strlen($html)-1;
  if ($html{$size} != ';') $html .= ';';
  //Make CSS[Name-of-the-class] = array(key => value)
  $regexp = '|\\s*?(\\S+?):(.+?);|i';
	preg_match_all( $regexp, $html, $styleinfo);
	$properties = $styleinfo[1];
	$values = $styleinfo[2];
	//Array-properties and Array-values must have the SAME SIZE!
	$classproperties = array();
	for($i = 0; $i < count($properties) ; $i++) $classproperties[strtoupper($properties[$i])] = trim($values[$i]);
 	
  return $this->fixCSS($classproperties);
}



function setCSS($arrayaux,$type='',$tag='')	// type= INLINE | BLOCK
{
	if (!is_array($arrayaux)) return; //Removes PHP Warning
	// Set font size first so that e.g. MARGIN 0.83em works on font size for this element
	if ($arrayaux['FONT-SIZE']) {
		$v = $arrayaux['FONT-SIZE'];
		if(is_numeric($v{0})) {
			$mmsize = ConvertSize($v,$this->FontSize);
			$this->SetFontSize( $mmsize*(72/25.4),false ); //Get size in points (pt)
		}
		else{
  			$v = strtoupper($v);
  			switch($v) {
  				case 'XX-SMALL': $this->SetFontSize( (0.7)* $this->default_font_size,false);
  		             break;
                		case 'X-SMALL': $this->SetFontSize( (0.77) * $this->default_font_size,false);
		             break;
				case 'SMALL': $this->SetFontSize( (0.86)* $this->default_font_size,false);
  		             break;
  				case 'MEDIUM': $this->SetFontSize($this->default_font_size,false);
  		             break;
  				case 'LARGE': $this->SetFontSize( (1.2)*$this->default_font_size,false);
  		             break;
  				case 'X-LARGE': $this->SetFontSize( (1.5)*$this->default_font_size,false);
  		             break;
  				case 'XX-LARGE': $this->SetFontSize( 2*$this->default_font_size,false);
				 break;
			}
		}
		if ($tag == 'BODY') { $this->SetDefaultFontSize($this->FontSizePt); }
	}

	// FOR INLINE and BLOCK OR 'BODY'
	if ($arrayaux['FONT-FAMILY']) {
		$v = $arrayaux['FONT-FAMILY'];
		//If it is a font list, get all font types
		$aux_fontlist = explode(",",$v);
		$fonttype = $aux_fontlist[0];
		$fonttype = strtolower(trim($fonttype));
		if(($fonttype == 'helvetica') || ($fonttype == 'arial')) { $fonttype = 'sans-serif'; }
		else if($fonttype == 'helvetica-embedded')  { $fonttype = 'helvetica'; }
		else if($fonttype == 'times')  { $fonttype = 'serif'; }
		else if($fonttype == 'courier')  { $fonttype = 'monospace'; }
		if ($tag == 'BODY') { 
			$this->SetDefaultFont($fonttype); 
		}
		$this->SetFont($fonttype,$this->FontStyle,0,false);
	}

   foreach($arrayaux as $k => $v) {
	if ($type != 'INLINE' && $tag != 'BODY') {
	  switch($k){
		// BORDERS
		case 'BORDER-TOP':
			$this->blk[$this->blklvl]['border_top'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_top']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-BOTTOM':
			$this->blk[$this->blklvl]['border_bottom'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_bottom']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-LEFT':
			$this->blk[$this->blklvl]['border_left'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_left']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;
		case 'BORDER-RIGHT':
			$this->blk[$this->blklvl]['border_right'] = $this->border_details($v);
			if ($this->blk[$this->blklvl]['border_right']['s']) { $this->blk[$this->blklvl]['border'] = 1; }
			break;

		// PADDING
		case 'PADDING-TOP':
			$this->blk[$this->blklvl]['padding_top'] = ConvertSize($v,$this->FontSize);
			break;
		case 'PADDING-BOTTOM':
			$this->blk[$this->blklvl]['padding_bottom'] = ConvertSize($v,$this->FontSize);
			break;
		case 'PADDING-LEFT':
			$this->blk[$this->blklvl]['padding_left'] = ConvertSize($v,$this->FontSize);
			break;
		case 'PADDING-RIGHT':
			$this->blk[$this->blklvl]['padding_right'] = ConvertSize($v,$this->FontSize);
			break;

		// MARGINS
		case 'MARGIN-TOP':
			$this->blk[$this->blklvl]['margin_top'] = ConvertSize($v,$this->blk[$this->blklvl]['width'],$this->FontSize);
			break;
		case 'MARGIN-BOTTOM':
			$this->blk[$this->blklvl]['margin_bottom'] = ConvertSize($v,$this->blk[$this->blklvl]['width'],$this->FontSize);
			break;
		case 'MARGIN-LEFT':
			$this->blk[$this->blklvl]['margin_left'] = ConvertSize($v,$this->blk[$this->blklvl]['width'],$this->FontSize);
			break;
		case 'MARGIN-RIGHT':
			$this->blk[$this->blklvl]['margin_right'] = ConvertSize($v,$this->blk[$this->blklvl]['width'],$this->FontSize);
			break;

		case 'PAGE-BREAK-AFTER':
			if (strtoupper($v) == 'AVOID') { $this->blk[$this->blklvl]['page_break_after_avoid'] = true; }
			break;

		case 'WIDTH':
			$this->divwidth = ConvertSize($v,$this->blk[$this->blklvl]['inner_width']);
			break;

		case 'TEXT-INDENT':
			$this->blk[$this->blklvl]['text_indent'] = ConvertSize($v,$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
			break;


	  }//end of switch($k)
	}


	if ($type != 'INLINE') {	// includes BODY tag
	  switch($k){

		case 'MARGIN-COLLAPSE':	// Custom tag to collapse margins at top and bottom of page
			if (strtoupper($v) == 'COLLAPSE') { $this->blk[$this->blklvl]['margin_collapse'] = true; }
			break;

		case 'LINE-HEIGHT':	
			if ($v >= 1) { $this->blk[$this->blklvl]['line_height'] = $v; }
			break;

		case 'TEXT-ALIGN': //left right center justify
			switch (strtoupper($v)) {
				case 'LEFT': 
                        $this->blk[$this->blklvl]['align']="L";
                        break;
				case 'CENTER': 
                        $this->blk[$this->blklvl]['align']="C";
                        break;
				case 'RIGHT': 
                        $this->blk[$this->blklvl]['align']="R";
                        break;
				case 'JUSTIFY': 
                        $this->blk[$this->blklvl]['align']="J";
                        break;
			}
			break;

	  }//end of switch($k)
	}


	if ($k == 'BACKGROUND' || $k == 'BACKGROUND-COLOR') {
		// bgcolor only - to stay consistent with original html2fpdf
		$cor = ConvertColor($v);
		if ($cor) { 
		   if ($type == 'INLINE') {
			$this->spanbgcolorarray = $cor;
			$this->spanbgcolor = true;
		   }
		   else {
			$this->blk[$this->blklvl]['bgcolorarray'] = $cor;
			$this->blk[$this->blklvl]['bgcolor'] = true;
		   }
		}
	}



	// FOR INLINE and BLOCK
	  switch($k){

		case 'FONT-STYLE': // italic normal oblique
			switch (strtoupper($v)) {
				case 'ITALIC': 
				case 'OBLIQUE': 
            			$this->SetStyle('I',true);
					break;
				case 'NORMAL': break;
			}
			break;

		case 'FONT-WEIGHT': // normal bold //Does not support: bolder, lighter, 100..900(step value=100)
			switch (strtoupper($v))	{
				case 'BOLD': 
            			$this->SetStyle('B',true);
					break;
				case 'NORMAL': break;
			}
			break;

		case 'VERTICAL-ALIGN': //super and sub only dealt with here e.g. <SUB> and <SUP>
			switch (strtoupper($v)) {
				case 'SUPER': 
                        $this->SUP=true;
                        break;
				case 'SUB': 
                        $this->SUB=true;
                        break;
			}
			break;

		case 'TEXT-DECORATION': // none underline line-through (strikeout) //Does not support: overline, blink
			if (stristr($v,'LINE-THROUGH')) {
					$this->strike = true;
			}
			if (stristr($v,'UNDERLINE')) {
            			$this->SetStyle('U',true);
			}
			break;

		case 'TEXT-TRANSFORM': // none uppercase lowercase //Does not support: capitalize
			switch (strtoupper($v)) { //Not working 100%
				case 'UPPERCASE':
					$this->toupper=true;
					break;
				case 'LOWERCASE':
 					$this->tolower=true;
					break;
				case 'NONE': break;
			}
			break;

		case 'OUTLINE-WIDTH': 
			switch(strtoupper($v)) {
				case 'THIN': $v = '0.03em'; break;
				case 'MEDIUM': $v = '0.05em'; break;
				case 'THICK': $v = '0.07em'; break;
			}
			$this->outlineparam['WIDTH'] = ConvertSize($v,$this->blk[$this->blklvl]['inner_width'],$this->FontSize);
			break;

		case 'OUTLINE-COLOR': 
			if (strtoupper($v) == 'INVERT') {
			   if ($this->colorarray) {
				$cor = $this->colorarray;
				$this->outlineparam['COLOR'] = array('R'=> (255-$cor['R']), 'G'=> (255-$cor['G']), 'B'=> (255-$cor['B']));
			   }
			   else {
				$this->outlineparam['COLOR'] = array('R'=> 255, 'G'=> 255, 'B'=> 255);
			   }
			}
			else { 
		  	  $cor = ConvertColor($v);
			  if ($cor) { $this->outlineparam['COLOR'] = $cor ; }	  
			}
			break;

		case 'COLOR': // font color
		  $cor = ConvertColor($v);
		  if ($cor) { 
			$this->colorarray = $cor;
			$this->SetTextColor($cor['R'],$cor['G'],$cor['B']);
			$this->issetcolor=true;
		  }
		  break;

		case 'DIR': 
			$this->BiDirectional = true;
			break;

	  }//end of switch($k)


   }//end of foreach
}

function SetStyle($tag,$enable)
{
	//Modify style and select corresponding font
	$this->$tag+=($enable ? 1 : -1);
	$style='';
  //Fix some SetStyle misuse
	if ($this->$tag < 0) $this->$tag = 0;
	if ($this->$tag > 1) $this->$tag = 1;
	foreach(array('B','I','U') as $s) {
		if($this->$s>0) {
			$style.=$s;
		}
	}
	$this->currentfontstyle=$style;
	$this->SetFont('',$style,0,false);
}

function GetStyle()
{
	$style='';
	foreach(array('B','I','U') as $s) {
		if($this->$s>0) {
			$style.=$s;
		}
	}
	return($style);
}


function DisableTags($str='')
{
  if ($str == '') //enable all tags
  {
    //Insert new supported tags in the long string below.
	///////////////////////////////////////////////////////
	// Added custom tags <indexentry>
    $this->enabledtags = "<span><s><strike><del><bdo><big><small><ins><cite><acronym><font><sup><sub><b><u><i><a><strong><em><code><samp><tt><kbd><var><q><table><thead><tr><th><td><ol><ul><li><dl><dt><dd><form><input><select><textarea><option><div><p><h1><h2><h3><h4><h5><h6><pre><center><blockquote><address><hr><img><br><indexentry><bookmark><tts><ttz><tta><column_break><columnbreak><newcolumn><newpage><page_break><pagebreak><columns><toc><tocentry>";
  }
  else
  {
    $str = explode(",",$str);
    foreach($str as $v) $this->enabledtags = str_replace(trim($v),'',$this->enabledtags);
  }
}




function TableWordWrap($maxwidth, $forcewrap = 0, $textbuffer = '', $returnarray=false)
{
    $biggestword=0;//EDITEI
    $toonarrow=false;//EDITEI

	$curlyquote = mb_convert_encoding("\xe2\x80\x9e",$this->mb_encoding,'UTF-8');
	$curlylowquote = mb_convert_encoding("\xe2\x80\x9d",$this->mb_encoding,'UTF-8');

	$textbuffer[0][0] = ltrim($textbuffer[0][0]);
	if ((count($textbuffer) == 0) or ((count($textbuffer) == 1) && ($textbuffer[0][0] == ''))) { return 0; }

    $text = '';
    $lh = $this->lineheight;
    $ch = 0;
    $width = 0;
    $ln = 1;	// Counts line number
    $mxw = $this->getStringWidth('WW');	// Kepp tabs on Maxwidth of actual text
    foreach ($textbuffer as $cctr=>$chunk) {
		$line = $chunk[0];


		//IMAGE
      	if ($line{0} == '»' and $line{1} == '¤' and $line{2} == '¬') { //identifier has been identified!
	        $sccontent = split("»¤¬",$line,2);
      	  $sccontent = split(",",$sccontent[1],2);
      	  foreach($sccontent as $scvalue) {
      	    $scvalue = split("=",$scvalue,2);
      	    $specialcontent[$scvalue[0]] = $scvalue[1];
      	  }
			$objattr = unserialize($specialcontent['objattr']);
			list($skipln,$iw,$ih) = $this->inlineObject($specialcontent['type'],0,0, $objattr, $this->lMargin,$width,$maxwidth,$lh,false,true);
			if ($objattr['type'] == 'hr') {
				$text = "";
				$width = 0;
				$ch += $lh;
				$ch += $ih;
				$lh = $this->lineheight; // Reset lineheight
				$ln++;
				continue;
			}
			if ($skipln==1 || $skipln==-2) {
				// Finish last line
				$text = "";
				$width = 0;
				$ch += $lh;
				$ln++;
				$lh = $this->lineheight; // Reset lineheight
			}
			$lh = MAX($lh,$ih);
			$width += $objattr['OUTER-WIDTH'];
			continue;
		}


		if ($line == "\n") {
			$text = "";
			$width = 0;
			$ch += $lh;
			$ln++;
			$lh = $this->lineheight; // Reset lineheight
			continue;
		}
		// SET FONT SIZE/STYLE from $chunk[n]
		// FONTSIZE
	      if(isset($chunk[11]) and $chunk[11] != '') { 
		   if ($this->shrin_k) {
			$this->SetFontSize($chunk[11]/$this->shrin_k,false); 
		   }
		   else {
			$this->SetFontSize($chunk[11],false); 
		   }

		   $fh = $this->table_lineheight*$this->FontSize; // Reset lineheight
		   $lh = MAX($lh,$fh);


		}
		// FONTFAMILY
	      if(isset($chunk[4]) and $chunk[4] != '') { $font = $this->SetFont($chunk[4],$this->FontStyle,0,false); }

		// FONT STYLE B I U
	      if(isset($chunk[2]) and $chunk[2] != '') {
	          if (strpos($chunk[2],"B") !== false) $this->SetStyle('B',true); 

	          if (strpos($chunk[2],"I") !== false) $this->SetStyle('I',true); 

	      }

		$space = $this->GetStringWidth(' ');

	if (mb_substr($line,0,1,$this->mb_encoding ) == ' ') { 	// line (chunk) starts with a space
		$width += $space;
		$text .= ' ';
	}

	if (mb_substr($line,(mb_strlen($line,$this->mb_encoding )-1),1,$this->mb_encoding ) == ' ') { $lsend = true; }	// line (chunk) ends with a space
	else { $lsend = false; }
	$line= ltrim($line);
	$line= mb_rtrim($line, $this->mb_encoding);
	if ($line == '') { continue; }

	//****************************// Edited mPDF 1.1
	if ($this->isunicode && !$this->usingembeddedfonts) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//

	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);

		//maxwidth is insufficient for one word
		if ($wordwidth > $maxwidth + 0.0001) {
			  while($wordwidth > $maxwidth) {
				$chw = 0;	// check width
				for ( $i = 0; $i < mb_strlen($word, $this->mb_encoding ); $i++ ) {
					$chw = $this->GetStringWidth(mb_substr($word,0,$i+1,$this->mb_encoding ));
					if ($chw > $maxwidth ) {
						if ($text) {
							$ch += $lh;
							$lh = $this->lineheight; // Reset lineheight
							$ln++;
							$mxw = $maxwidth;
						}
						$text = mb_substr($word,0,$i,$this->mb_encoding );
						$word = mb_substr($word,$i,mb_strlen($word, $this->mb_encoding )-$i,$this->mb_encoding );
						$wordwidth = $this->GetStringWidth($word);
						$width = $maxwidth; 
					}
				}
			  }
		}
		// Word fits on line...
		if ($width + $wordwidth  < $maxwidth + 0.0001) {
			$mxw = max($mxw, ($width+$wordwidth));
			$width += $wordwidth + $space;
			$text .= $word.' ';
		}
		// Word does not fit on line...
		else {
			$alloworphans = false;
			// In case of orphan punctuation or SUB/SUP
			// Strip end punctuation
			$tmp = preg_replace('/[\.,;:!?"'.$curlyquote . $curlylowquote .']*$/','',$word);
			if ($tmp != $word) {
				$tmpwidth = $this->GetStringWidth($tmp);
				if ($width + $tmpwidth  < $maxwidth + 0.0001) { $alloworphans = true; }
			}
			// If line = SUB/SUP to max of orphansallowed ( $this->SUP || $this->SUB )
			if(( (isset($chunk[5]) and $chunk[5]) || (isset($chunk[6]) and $chunk[6])) && $orphs <= $this->orphansAllowed) {
				$alloworphans = true;
			}


			// if [stripped] word fits
			if ($alloworphans) {
				$mxw = $maxwidth;
				$width += $wordwidth + $space;
				$text .= $word.' ';
			}
			else {
				// else
				$width = $wordwidth + $space;
				$text = $word.' ';
				$ch += $lh;
				$ln++;
				$mxw = $maxwidth;
				$lh = $this->lineheight; // Reset lineheight
			}
            }
	}

	// End of textbuffer chunk
	if (!$lsend) {
		$width -= $space;
		$text = mb_rtrim($text , $this->mb_encoding);
	}

		// RESET FONT SIZE/STYLE
		// RESETTING VALUES
	      //Now we must deactivate what we have used
	      if(isset($chunk[2]) and $chunk[2] != '') {
	        $this->SetStyle('B',false);
	        $this->SetStyle('I',false);
	      }
	      if(isset($chunk[4]) and $chunk[4] != '') {
			$this->SetFont($this->default_font,$this->FontStyle,0,false);
		}
	      if(isset($chunk[11]) and $chunk[11] != '') { 
			$this->SetFontSize($this->default_font_size,false);
	      }
    }
	if ($returnarray) { return array(($ch + $lh),$ln,$mxw); }
	else { return ($ch + $lh) ; }
}


function TableCheckMinWidth(&$text, $maxwidth, $forcewrap = 0, $textbuffer = '')
{
    $biggestword=0;//EDITEI
    $toonarrow=false;//EDITEI
	if ((count($textbuffer) == 0) or ((count($textbuffer) == 1) && ($textbuffer[0][0] == ''))) { return 0; }

    foreach ($textbuffer as $chunk) {

		$line = $chunk[0];


		// IMAGES & FORM ELEMENTS
	      if ($line{0} == '»' and $line{1} == '¤' and $line{2} == '¬') { //inline object - FORM element or IMAGE!
			continue;
		}

		if ($line == "\n") {
			continue;
		}
    		$line = ltrim($line );
    		$line = mb_rtrim($line , $this->mb_encoding);
		// SET FONT SIZE/STYLE from $chunk[n]

		// FONTSIZE
	      if(isset($chunk[11]) and $chunk[11] != '') { 
		   if ($this->shrin_k) {
			$this->SetFontSize($chunk[11]/$this->shrin_k,false); 
		   }
		   else {
			$this->SetFontSize($chunk[11],false); 
		   }
		}
		// FONTFAMILY
	      if(isset($chunk[4]) and $chunk[4] != '') { $font = $this->SetFont($chunk[4],$this->FontStyle,0,false); }
		// B I U
	      if(isset($chunk[2]) and $chunk[2] != '') {
	          if (strpos($chunk[2],"B") !== false) $this->SetStyle('B',true);
	          if (strpos($chunk[2],"I") !== false) $this->SetStyle('I',true);
	      }

	//****************************// Edited mPDF 1.1
	if ($this->isunicode && !$this->usingembeddedfonts) {
		$words = mb_split(' ', $line);
	}
	else {
		$words = split(' ', $line);
	}
	//****************************//
	foreach ($words as $word) {
		$word = mb_rtrim($word, $this->mb_encoding);
		$word = ltrim($word);
		$wordwidth = $this->GetStringWidth($word);

		//EDITEI
		//Warn user that maxwidth is insufficient
		if ($wordwidth > $maxwidth + 0.0001) {
			if ($wordwidth > $biggestword) { $biggestword = $wordwidth; }
			$toonarrow=true;//EDITEI
		}

	}
	// RESET FONT SIZE/STYLE
	// RESETTING VALUES
	//Now we must deactivate what we have used
	if(isset($chunk[2]) and $chunk[2] != '') {
	       $this->SetStyle('B',false);
	       $this->SetStyle('I',false);
	}
	if(isset($chunk[4]) and $chunk[4] != '') {
		$this->SetFont($this->default_font,$this->FontStyle,0,false);
	}
	if(isset($chunk[11]) and $chunk[11] != '') { 
		$this->SetFontSize($this->default_font_size,false);
	}
    }

    //Return -(wordsize) if word is bigger than maxwidth 

	// ADDED
      if (($toonarrow) && ($this->table_error_report)) {
		die("Word is too long to fit in table - ".$this->table_error_report_param); 
	}
    if ($toonarrow) return -$biggestword;
    else return 1;
}



////////////////////////TABLE CODE (from PDFTable)/////////////////////////////////////
//Thanks to vietcom (vncommando at yahoo dot com)
/*     Modified by Renato Coelho
   in order to print tables that span more than 1 page and to allow 
   bold,italic and the likes inside table cells (and alignment now works with styles!)
*/

//table		Array of (w, h, bc, nr, wc, hr, cells)
//w			Width of table
//h			Height of table
//nc			Number column
//nr			Number row
//hr			List of height of each row
//wc			List of width of each column
//cells		List of cells of each rows, cells[i][j] is a cell in the table
function _tableColumnWidth(&$table){
	$cs = &$table['cells'];
	$mw = $this->getStringWidth('W') + ($this->cellPaddingL+$this->cellPaddingR) + ($this->cMarginL+$this->cMarginR); // Added
	$nc = $table['nc'];
	$nr = $table['nr'];
	$listspan = array();
	// ADDED table['l'][colno] 
	// = total length of text approx (using $c['s']) in that column - used to approximately distribute col widths in _tableWidth
	//
	for($j = 0 ; $j < $nc ; $j++ ) { //columns
		$wc = &$table['wc'][$j];
		for($i = 0 ; $i < $nr ; $i++ ) { //rows
			if (isset($cs[$i][$j]) && $cs[$i][$j])  {
				$c = &$cs[$i][$j];
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) {
					$c['maw'] = $c['miw'] = $this->FontSize + ($this->cellPaddingL+$this->cellPaddingR) + ($this->cMarginL+$this->cMarginR);
					if (isset($c['w'])) {	// If cell width is specified
						if ($c['miw'] <$c['w'])	{ $c['miw'] = $c['w']; }
					}
					if (!isset($c['colspan'])) {
						if ($wc['miw'] < $c['miw']) { $wc['miw']	= $c['miw']; }
						if ($wc['maw'] < $c['maw']) { $wc['maw']	= $c['maw']; }

						if (isset($table['l'][$j]) ) { 
							$table['l'][$j] += $c['miw'] ;
						}
						else {
							$table['l'][$j] = $c['miw'] ;
						}
					}
					if ($c['miw'] > $wc['miw']) { $wc['miw'] = $c['miw']; } 
        				if ($wc['miw'] > $wc['maw']) { $wc['maw'] = $wc['miw']; }
					continue;
				}
				$miw = $mw;
				if (isset($c['maxs']) and $c['maxs'] != '') { $c['s'] = $c['maxs']; }
				$c['maw'] = $c['s'];
				if (isset($c['nowrap'])) { $miw = $c['maw']; }

				if (isset($c['w'])) {	// If cell width is specified
					if ($miw<$c['w'])	{ $c['miw'] = $c['w']; }	// Cell min width = that specified
					if ($miw>$c['w'])	{ $c['miw'] = $c['w'] = $miw; } // If width specified is less than minimum allowed (W) increase it
					if (!isset($wc['w'])) { $wc['w'] = 1; }		// If the Col width is not specified = set it to 1
				}
				else { $c['miw'] = $miw; }	// If cell width not specified -> set Cell min width it to minimum allowed (W)

				if ($c['maw']  < $c['miw']) { $c['maw'] = $c['miw']; }	// If Cell max width < Minwidth - increase it to =
				if (!isset($c['colspan'])) {
					if ($wc['miw'] < $c['miw']) { $wc['miw']	= $c['miw']; }	// Update Col Minimum and maximum widths
					if ($wc['maw'] < $c['maw']) { $wc['maw']	= $c['maw']; }

					if (isset($table['l'][$j]) ) { 
						$table['l'][$j] += $c['s'];
					}
					else {
						$table['l'][$j] = $c['s'];
					}

				}
				else $listspan[] = array($i,$j);

 			//Check if minimum width of the whole column is big enough for a huge word to fit
        			$auxtext = implode("",$c['text']);
	       		$minwidth = $this->TableCheckMinWidth($auxtext,$wc['miw']- ($this->cMarginL+$this->cMarginR)-($this->cellPaddingL+$this->cellPaddingR),0,$c['textbuffer']); 
        			if ($minwidth < 0 && 
				   ((-$minwidth) + ($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR)) > $wc['miw']) { 
					//increase minimum width
					$wc['miw'] = (-$minwidth) +($this->cMarginL+$this->cMarginR)+($this->cellPaddingL+$this->cellPaddingR);  
				}
        			if ($wc['miw'] > $wc['maw']) { $wc['maw'] = $wc['miw']; } //update maximum width, if needed
			}
		}//rows
	}//columns


	//
	$wc = &$table['wc'];
	foreach ($listspan as $span) {
		list($i,$j) = $span;
		$c = &$cs[$i][$j];
		$lc = $j + $c['colspan'];
		if ($lc > $nc) { $lc = $nc; }
		
		$wis = $wisa = 0;
		$was = $wasa = 0;
		$list = array();
		for($k=$j;$k<$lc;$k++) {
			if (isset($table['l'][$k]) ) { 
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) { $table['l'][$k] += $c['miw']/$c['colspan'] ; }
				else { $table['l'][$k] += $c['s']/$c['colspan']; }
			}
			else {
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) { $table['l'][$k] = $c['miw']/$c['colspan'] ; }
				else { $table['l'][$k] = $c['s']/$c['colspan']; }
			}
			$wis += $wc[$k]['miw'];
			$was += $wc[$k]['maw'];
			if (!isset($c['w'])) {
				$list[] = $k;
				$wisa += $wc[$k]['miw'];
				$wasa += $wc[$k]['maw'];
			}
		}
		if ($c['miw'] > $wis) {
			if (!$wis) {
				for($k=$j;$k<$lc;$k++) { $wc[$k]['miw'] = $c['miw']/$c['colspan']; }
			}
			elseif (!count($list)) {
				$wi = $c['miw'] - $wis;
				for($k=$j;$k<$lc;$k++) { $wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi; }
			}
			else {
				$wi = $c['miw'] - $wis;
				foreach ($list as $k) { $wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi; }
			}
		}
		if ($c['maw'] > $was) {
			if (!$wis) {
				for($k=$j;$k<$lc;$k++) { $wc[$k]['maw'] = $c['maw']/$c['colspan']; }
			}
			elseif (!count($list)) {
				$wi = $c['maw'] - $was;
				for($k=$j;$k<$lc;$k++) { $wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi; }
			}
			else {
				$wi = $c['maw'] - $was;
				foreach ($list as $k) { $wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi; }
			}
		}
	}
	if (($this->shrink_tables_to_fit || $this->shrink_this_table_to_fit) && !$this->isCJK) {
	 $checkwidth = 0;
	 for( $i = 0 ; $i < $nc ; $i++ ) {
		$checkwidth += $table['wc'][$i]['miw'];
	 }
	 $checkwidth += ($this->cMarginL + $this->cMarginR);


	if ($this->table_rotate) {
		$temppgwidth = $this->tbrot_maxw;
	}
	else {
		$temppgwidth = $this->blk[$this->blklvl]['inner_width'];
	}
	 $mxw = isset($table['w']) ? $table['w'] : $temppgwidth ;
	 $ratio = $checkwidth/$mxw;
	 if ($checkwidth > $mxw) { return $ratio; }
	}
	return 0;
}

function _tableWidth(&$table){
	$widthcols = &$table['wc'];
	$numcols = $table['nc'];
	$tablewidth = 0;
	if ($this->table_rotate) {
		$temppgwidth = $this->tbrot_maxw;
	}
	else {
		$temppgwidth = $this->blk[$this->blklvl]['inner_width'];
	}

	// Final check - If table cannot fit
	$this->shrinktableborders = false;
	$mw = $this->getStringWidth('W') + ($this->cellPaddingL+$this->cellPaddingR) + ($this->cMarginL+$this->cMarginR); // Added
	$checkwidth = ($mw * $this->table['nc']) + ($this->cMarginL + $this->cMarginR);
	if ($checkwidth > $this->blk[$this->blklvl]['inner_width']) { 
		if ($this->table_rotate) { die("Cannot fit table in width of page"); }
		$this->cellPaddingL = 0;
		$this->cellPaddingR = 0;
		$this->cMarginL = 0.1;
		$this->cMarginL = 0.1;
		$mw = $this->getStringWidth('W') + ($this->cellPaddingL+$this->cellPaddingR) + ($this->cMarginL+$this->cMarginR); // Added
		$checkwidth = ($mw * $this->table['nc']) + ($this->cMarginL + $this->cMarginR);
		if ($checkwidth > $this->blk[$this->blklvl]['inner_width']) { die("Cannot fit table in width of page"); }
		$this->shrinktableborders = true;
	}


	$totaltextlength = 0;	// Added - to sum $table['l'][colno]
	$totalatextlength = 0;	// Added - to sum $table['l'][colno] for those columns where width not set

	for ( $i = 0 ; $i < $numcols ; $i++ ) {
		$tablewidth += isset($widthcols[$i]['w']) ? $widthcols[$i]['miw'] : $widthcols[$i]['maw'];
		$totaltextlength += $table['l'][$i];
	}
	$tablewidth += ($this->cMarginL + $this->cMarginR);	// Outer half of borders

	// IF table width set by DEFINED or by sum of MAX widths of columns is too wide for page: set table width as pagewidth
	if ($tablewidth > $temppgwidth) { 
		$table['w'] = $temppgwidth; 
	}

	// IF the table width is now set - Need to distribute columns widths
	if (isset($table['w'])) {
		$wis = $wisa = 0;
		$list = array();
		for( $i = 0 ; $i < $numcols ; $i++ ) {
			$wis += $widthcols[$i]['miw'];
			if (!isset($widthcols[$i]['w'])){ 
				$list[] = $i;  
				$wisa += $widthcols[$i]['miw'];
				$totalatextlength += $table['l'][$i];
			}
		}

		// Allocate spare (more than col's minimum width across the cols according to their approx total text length
		// Do it by setting minimum width here
		if ($table['w'] > $wis+($this->cMarginL + $this->cMarginR)) {
			$surplus = 0;  $nsc = 0;	// number of surplus columns
			if (!count($list)) {
				$wi = ($table['w']-($this->cMarginL + $this->cMarginR + $wis));	//	 /$numcols;
				for($k=0;$k<$numcols;$k++) {
					$spareratio = ($table['l'][$k] / $totaltextlength); //  gives ratio to divide up free space

					// Don't allocate more than Maximum required width - save rest in surplus
					if ($widthcols[$k]['miw'] + ($wi * $spareratio) > $widthcols[$k]['maw']) {
						$nsc++;
						$surplus += ($wi * $spareratio) - ($widthcols[$k]['maw']-$widthcols[$k]['miw']);
						$widthcols[$k]['miw'] = $widthcols[$k]['maw'];
					}
					else { $widthcols[$k]['miw'] += ($wi * $spareratio); }

				}
			}
			else {
				$wi = ($table['w'] - ($this->cMarginL + $this->cMarginR + $wisa));	//	/count($list);		// ?? wis or wisa
				foreach ($list as $k) {
					$spareratio = ($table['l'][$k] / $totalatextlength); //  gives ratio to divide up free space

					// Don't allocate more than Maximum required width - save rest in surplus
					if ($widthcols[$k]['miw'] + ($wi * $spareratio) > $widthcols[$k]['maw']) {
						$nsc++;
						$surplus += ($wi * $spareratio) - ($widthcols[$k]['maw']-$widthcols[$k]['miw']);
						$widthcols[$k]['miw'] = $widthcols[$k]['maw'];
					}
					else { $widthcols[$k]['miw'] += ($wi * $spareratio); }

				}
			}
			// If surplus left over apportion it across columns
			if ($surplus) { 
			   if ($nsc < $numcols) {
				$sur = $surplus / ($numcols-$nsc);
				for ($i=0;$i<$numcols;$i++) {
				   if ($widthcols[$i]['miw'] < $widthcols[$i]['maw']) {
					$widthcols[$i]['miw'] += $sur;
				   }
				}
			   }
			   else {	// If all columns
				$sur = $surplus / ($numcols);
				for ($i=0;$i<$numcols;$i++) {
					$widthcols[$i]['miw'] += $sur;
				}
			   }
			}
		}


		// This sets the columns all to minimum width (which has been increased above if appropriate)
		for ($i=0;$i<$numcols;$i++) {
			$widthcols[$i] = $widthcols[$i]['miw'];
		}


		// TABLE NOT WIDE ENOUGH EVEN FOR MINIMUM CONTENT WIDTH
		// If sum of column widths set are too wide for table
		$checktablewidth = 0;
		for ( $i = 0 ; $i < $numcols ; $i++ ) {
			$checktablewidth += $widthcols[$i];
		}
		if ($checktablewidth > ($temppgwidth + 0.001 - ($this->cMarginL + $this->cMarginR))) { 
		   $usedup = 0; $numleft = 0;
		   for ($i=0;$i<$numcols;$i++) {
			if (($widthcols[$i] > (($temppgwidth - ($this->cMarginL + $this->cMarginR)) / $numcols)) && (!isset($widthcols[$i]['w']))) { 
				$numleft++; 
				unset($widthcols[$i]); 
			}
			else { $usedup += $widthcols[$i]; }
		   }
		   for ($i=0;$i<$numcols;$i++) {
			if (!$widthcols[$i]) { 
				$widthcols[$i] = ((($temppgwidth - ($this->cMarginL + $this->cMarginR)) - $usedup)/ ($numleft)); 
			}
		   }
		}



	}
	else { //table has no width defined
		$table['w'] = $tablewidth;  
		for ( $i = 0 ; $i < $numcols ; $i++) {
			$colwidth = isset($widthcols[$i]['w']) ? $widthcols[$i]['miw'] : $widthcols[$i]['maw'];
			unset($widthcols[$i]);
			$widthcols[$i] = $colwidth;
		}
	}
}
	
function _tableHeight(&$table){
	$cells = &$table['cells'];
	$numcols = $table['nc'];
	$numrows = $table['nr'];
	$listspan = array();
	$checkmaxheight = 0;
	$headerrowheight = 0;
	if ($this->table_rotate) {
		$temppgheight = $this->tbrot_maxh;
	}
	else {
		$temppgheight = ($this->fh - $this->bMargin - $this->tMargin);
	}
	$extraWLR = $this->cellPaddingL+$this->cellPaddingR+$this->cMarginL+$this->cMarginR;	// Extra Width L+R

	for( $i = 0 ; $i < $numrows ; $i++ ) { //rows
		$heightrow = &$table['hr'][$i];
		for( $j = 0 ; $j < $numcols ; $j++ ) { //columns
			if (isset($cells[$i][$j]) && $cells[$i][$j]) {
				$c = &$cells[$i][$j];
				list($x,$cw) = $this->_tableGetWidth($table, $i,$j);
				//Check whether width is enough for this cells' text
				$auxtext = implode("",$c['text']);
				$auxtext2 = $auxtext; //in case we have text with styles

				$aux3 = $auxtext; //in case we have text with styles


				// Get CELL HEIGHT = NO OF LINES
				// ++ extra parameter forces wrap to break word
				// Added mPDF 1.3 for rotated text in cell
				if ($c['R']) {
					$aux4 = implode(" ",$c['text']);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($c['textbuffer'][0][4],$c['textbuffer'][0][2],$c['textbuffer'][0][11] / $this->shrin_k,true,true);
					$aux4 = ltrim($aux4);
					$aux4= mb_rtrim($aux4,$this->mb_encoding);
	       			$tempch = $this->GetStringWidth($aux4);
					if ($c['R'] >= 45 && $c['R'] < 90) {
						$tempch = ((sin(deg2rad($c['R']))) * $tempch ) + ((sin(deg2rad($c['R']))) * (($c['textbuffer'][0][11]/$this->k) / $this->shrin_k));
					} 
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$ch = ($tempch ) +($this->cMarginT+$this->cMarginB)+($this->cellPaddingT+$this->cellPaddingB);  
				}
				else {
					$tempch = $this->TableWordWrap(($cw-$extraWLR),1,$c['textbuffer']);  
					// Added cellpadding top and bottom. (Lineheight already adjusted to table_lineheight)
					$ch = $tempch + ($this->cellPaddingT+$this->cellPaddingB) + ($this->cMarginT+$this->cMarginB);	
				}

				//If height is bigger than page height...
//				if ($ch > $temppgheight) { $ch = $temppgheight; }

				//If height is defined and it is bigger than calculated $ch then update values
				if (isset($c['h']) && $c['h'] > $ch) {
					$c['mih'] = $ch; //in order to keep valign working
					$ch = $c['h'];
				}
				else $c['mih'] = $ch;
				if (isset($c['rowspan']))	$listspan[] = array($i,$j);
				elseif ($heightrow < $ch) $heightrow = $ch;
				if ($i == 0 && $this->usetableheader) {
					$headerrowheight = max($headerrowheight,$ch);
				}
				else {
					$checkmaxheight = max($checkmaxheight,$ch);
				}
			}
		}//end of columns
	}//end of rows
	$heightrow = &$table['hr'];
	foreach ($listspan as $span) {
		list($i,$j) = $span;
		$c = &$cells[$i][$j];
		$lr = $i + $c['rowspan'];
		if ($lr > $numrows) $lr = $numrows;
		$hs = $hsa = 0;
		$list = array();
		for($k=$i;$k<$lr;$k++) {
			$hs += $heightrow[$k];
			if (!isset($c['h'])) {
				$list[] = $k;
				$hsa += $heightrow[$k];
			}
		}
		if ($c['mih'] > $hs) {
			if (!$hs) {
				for($k=$i;$k<$lr;$k++) $heightrow[$k] = $c['mih']/$c['rowspan'];
			}
			elseif (!count($list)) {
				$hi = $c['mih'] - $hs;
				for($k=$i;$k<$lr;$k++) $heightrow[$k] += ($heightrow[$k]/$hs)*$hi;
			}
			else {
				$hi = $c['mih'] - $hsa;
				foreach ($list as $k) $heightrow[$k] += ($heightrow[$k]/$hsa)*$hi;
			}
		}
	}

	if ($checkmaxheight + $headerrowheight > $temppgheight) { return ($checkmaxheight + $headerrowheight) / $temppgheight; }
	else { return 0; }
}

function _tableGetWidth(&$table, $i,$j){
	$cell = &$table['cells'][$i][$j];
	if ($cell)
  {
		if (isset($cell['x0'])) return array($cell['x0'], $cell['w0']);
		$x = 0;
		$widthcols = &$table['wc'];
		for( $k = 0 ; $k < $j ; $k++ ) $x += $widthcols[$k];
		$w = $widthcols[$j];
		if (isset($cell['colspan']))
    {
			 for ( $k = $j+$cell['colspan']-1 ; $k > $j ; $k-- )	$w += $widthcols[$k];
		}
		$cell['x0'] = $x;
		$cell['w0'] = $w;
		return array($x, $w);
	}
	return array(0,0);
}

function _tableGetHeight(&$table, $i,$j){
	$cell = &$table['cells'][$i][$j];
	if ($cell){
		if (isset($cell['y0'])) return array($cell['y0'], $cell['h0']);
		$y = 0;
		$heightrow = &$table['hr'];
		for ($k=0;$k<$i;$k++) $y += $heightrow[$k];
		$h = $heightrow[$i];
		if (isset($cell['rowspan'])){
			for ($k=$i+$cell['rowspan']-1;$k>$i;$k--)
				$h += $heightrow[$k];
		}
		$cell['y0'] = $y;
		$cell['h0'] = $h;
		return array($y, $h);
	}
	return array(0,0);
}



// CHANGED TO ALLOW TABLE BORDER TO bE SPECIFIED CORRECTLY - added border_details
function _tableRect($x, $y, $w, $h, $type=1, $details=array()){
	if (($type==1) && ($type != '0001')) { $this->Rect($x, $y, $w, $h); }
	else if (strlen($type)==4){
		if ($this->shrin_k) { $sk = $this->shrin_k; } else { $sk = 1; }	// shrink factor when autosize tables
		$x2 = $x + $w; $y2 = $y + $h;
		if (intval($type{0})) {	// TOP
		   if ($details['T']['w']) { 
			$oldlinewidth = $this->LineWidth;
			if ($this->shrinktableborders) { $this->SetLineWidth(0.2); }
			else { $this->SetLineWidth($details['T']['w']/$sk); }
			if ($details['T']['c']) { 
				$this->SetDrawColor($details['T']['c']['R'],$details['T']['c']['G'],$details['T']['c']['B']);
			}
			$this->Line($x , $y , $x2, $y );
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		}
		if (intval($type{1})) {	// RIGHT
		   if ($details['R']['w']) { 
			$oldlinewidth = $this->LineWidth;
			if ($this->shrinktableborders) { $this->SetLineWidth(0.2); }
			else { $this->SetLineWidth($details['R']['w']/$sk); }
			if ($details['R']['c']) { 
				$this->SetDrawColor($details['R']['c']['R'],$details['R']['c']['G'],$details['R']['c']['B']);
			}
			$this->Line($x2, $y , $x2, $y2);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		}
		if (intval($type{2})) {	// BOTTOM
		   if ($details['B']['w']) { 
			$oldlinewidth = $this->LineWidth;
			if ($this->shrinktableborders) { $this->SetLineWidth(0.2); }
			else { $this->SetLineWidth($details['B']['w']/$sk); }
			if ($details['B']['c']) { 
				$this->SetDrawColor($details['B']['c']['R'],$details['B']['c']['G'],$details['B']['c']['B']);
			}
			$this->Line($x , $y2, $x2, $y2);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		}
		if (intval($type{3})) {	// LEFT
		   if ($details['L']['w']) { 
			$oldlinewidth = $this->LineWidth;
			if ($this->shrinktableborders) { $this->SetLineWidth(0.2); }
			else { $this->SetLineWidth($details['L']['w']/$sk); }
			if ($details['L']['c']) { 
				$this->SetDrawColor($details['L']['c']['R'],$details['L']['c']['G'],$details['L']['c']['B']);
			}
			$this->Line($x , $y , $x , $y2);
			$this->SetLineWidth($oldlinewidth);
			$this->SetDrawColor(0);
		   }
		}
	}
}

// Added mPDF 1.1 for correct table border inheritance
function bord_bitadd($x,$y) {
	$x = str_pad($x,4,'0',STR_PAD_LEFT);
	$y = str_pad($y,4,'0',STR_PAD_LEFT);
	if (intval($x{0}) || intval($y{0})) { $a = '1'; } else { $a = '0'; }
	if (intval($x{1}) || intval($y{1})) { $b = '1'; } else { $b = '0'; }
	if (intval($x{2}) || intval($y{2})) { $c = '1'; } else { $c = '0'; }
	if (intval($x{3}) || intval($y{3})) { $d = '1'; } else { $d = '0'; }
	return $a . $b . $c . $d; 
}


function _tableWrite(&$table){
	$cells = &$table['cells'];
	$numcols = $table['nc'];
	$numrows = $table['nr'];

	// Advance down page by half width of top border
	if ($this->cMarginT) { 
	   if ($this->table_rotate) {
		$this->y += ($this->cMarginT);
	   }
	   else {
		$this->DivLn($this->cMarginT); 
	   }
	}

	$this->x = $this->lMargin  + $this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'];

	$x0 = $this->x; 
	$y0 = $this->y;
	$right = $x0 + $this->blk[$this->blklvl]['inner_width'];
	$outerfilled = $this->y;	// Keep track of how far down the outer DIV bgcolor is painted (NB rowspans)

	if ($this->table_rotate) {
		$this->tbrot_y0 = $this->y;
		$this->tbrot_x0 = $this->x;
		$temppgwidth = $this->tbrot_maxw;
		$pagetrigger = $y0 + ($this->blk[$this->blklvl]['inner_width']);
		$this->tbrot_w = $table['w'];
		$this->tbrot_h = 0;
	}
	else {
		$temppgwidth = $this->blk[$this->blklvl]['inner_width'];

		// Added mPDF 1.3 as flag to prevent page triggering in footers containing table
		if ($this->InHTMLFooter) {
			$pagetrigger = ($this->fh);
		}
		else {
			$pagetrigger = ($this->fh - $this->bMargin);
		}

	   if (isset($table['a']) and ($table['w'] < $this->blk[$this->blklvl]['inner_width'])) {
		if ($table['a']=='C') { $x0 += ((($right-$x0) - $table['w'])/2); }
		elseif ($table['a']=='R') { $x0 = $right - $table['w']; }
	   }
	}
	$x0 += $this->cMarginL;

	$returny = 0;
	$tableheader = array();
	//Draw Table Contents and Borders
	for( $i = 0 ; $i < $numrows ; $i++ ) { //Rows

	  // Get Maximum row/cell height in row - including rowspan>1
	  $maxrowheight = 0;
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		list($y,$h) = $this->_tableGetHeight($table, $i, $j);
		$maxrowheight = max($maxrowheight,$h);
	  }


	  $skippage = false;
	  for( $j = 0 ; $j < $numcols ; $j++ ) { //Columns
		if (isset($cells[$i][$j]) && $cells[$i][$j]) {
			$cell = &$cells[$i][$j];
			list($x,$w) = $this->_tableGetWidth($table, $i, $j);
			list($y,$h) = $this->_tableGetHeight($table, $i, $j);
			$x += $x0;
			$y += $y0;
			$y -= $returny;
			if ((($y + $maxrowheight + $this->cMarginB) > $pagetrigger) && ($y0 >0 || $x0 > 0)) {
				if (!$skippage) {
					$y -= $y0;
					$returny += $y;

					$oldcolumn = $this->CurrCol;
					if ($this->AcceptPageBreak()) {
						$this->y = $y + $y0;
						$this->AddPage();
						// Added to correct for OddEven Margins
						$x=$x +$this->MarginCorrection;
						$x0=$x0 +$this->MarginCorrection;
						if ($this->table_rotate) {
							$this->tbrot_x0 = $x0;
							$this->tbrot_h = 0;
							$this->tbrot_y0 = $this->y;
							$pagetrigger = $y0 + ($this->blk[$this->blklvl]['inner_width']);
						}

						// Added mPDF 1.1 keeping block together on one page
						// Disable Table header repeat if Keep Block together
             				if ($this->usetableheader && !$this->keep_block_together) { 
							$this->TableHeader($tableheader,$tablestartpage,$tablestartcolumn);

							if ($this->table_rotate) {
								$this->tbrot_h = $tableheader[0]['h'];
							}

						}
						$outerfilled = 0;
						$y0 = $this->y; 
						$y = $y0;
					}
					// COLS
					// COLUMN CHANGE
					if ($this->CurrCol != $oldcolumn) {
						// Added to correct for Columns
						$x += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
						$x0 += $this->ChangeColumn * ($this->ColWidth+$this->ColGap);
						if ($this->CurrCol == 0) { 	// just added a page - possibly with tableheader
							$y0 = $this->y; 	// this->y0 is global used by Columns - $y0 is internal to tablewrite
						}
						else {
							$y0 = $this->y0; 	// this->y0 is global used by Columns - $y0 is internal to tablewrite
						}
						$y = $y0;
						$outerfilled = 0;
					}
				}
				$skippage = true;
			}

			$this->x = $x; 
			$this->y = $y;


			// Set the Page & Column where table starts
			if ($i==0 && $j==0) {
				if (($this->useOddEven) && (($this->page)%2==0)) {				// EVEN
					$tablestartpage = 'EVEN'; 
				}
				else if (($this->useOddEven) && (($this->page)%2==1)) {				// ODD
					$tablestartpage = 'ODD'; 
				}
				else { $tablestartpage = ''; }
				if ($this->ColActive) { $tablestartcolumn = $this->CurrCol; }
			}


			//ALIGN
			$align = $cell['a'];

			//OUTER FILL BGCOLOR of DIVS
			if ($this->blklvl > 0 && ($j==0 || $cell['rowspan']>1) && !$this->table_rotate) {	// Disable in rotated tables
			  $firstblockfill = $this->GetFirstBlockFill();
			  if ($firstblockfill && $this->blklvl >= $firstblockfill) {
			   $divh = $maxrowheight;
	  		   if (($i == $numrows-1 && $cell['rowspan']<2) || ($cell['rowspan']>1 && ($i + $cell['rowspan']-1) == $numrows-1)) { 
				$divh += $this->cMarginB;  //last row: fill bottom half of bottom border (y advanced at end)
			   }
			   if (($this->y + $divh) > $outerfilled ) {	// if not already painted by previous rowspan
				$bak_x = $this->x;
				$bak_y = $this->y;
				if ($outerfilled > $this->y) { 
					$divh = ($this->y + $divh) - $outerfilled;
					$this->y = $outerfilled; 
				}
				for ($blvl=$firstblockfill;$blvl<=$this->blklvl;$blvl++) {
					$this->SetBlockFill($blvl);
					$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
					$this->Cell( ($this->blk[$blvl]['width']), $divh, '', '', 0, '', 1);
				}
				$outerfilled = $this->y + $divh;
				// Reset current block fill
				$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
				$this->SetFillColor($bcor['R'],$bcor['G'],$bcor['B']);
				$this->x = $bak_x;
				$this->y = $bak_y;
			    }
			  }
			}

			//VERTICAL ALIGN
			// Added mPDF 1.3 for rotated text in cell
			if ($cell['R'] && INTVAL($cell['R']) > 0 && isset($cell['va']) && $cell['va']!='B') { $cell['va']='B';}

			if (!isset($cell['va']) || $cell['va']=='M') $this->y += ($h-$cell['mih'])/2;
			elseif (isset($cell['va']) && $cell['va']=='B') $this->y += $h-$cell['mih'];


			//FILL BGCOLOR
			$fill = isset($cell['bgcolor']) ? $cell['bgcolor']
  					: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
  					: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));
			if ($fill) {
  				$color = ConvertColor($fill);
  				$this->SetFillColor($color['R'],$color['G'],$color['B']);
  				$this->Rect($x, $y, $w, $h, 'F');
			}

			//Set Borders

			// CHANGED TO ALLOW TABLE BORDER TO BE SPECIFIED CORRECTLY - TopnTail > Cell > Table definitions
			$bord = ''; 

			// Edited mPDF 1.1 for correct table border inheritance
			$bord_det = array();

  			if ($cell['border']) {
				$bord = $cell['border'];
				$bord_det = $cell['border_details'];
			}
			// Edited mPDF 1.1 for correct table border inheritance
  			elseif (isset($table['border']) && $table['border'] && ($this->table_border_attr_set)) {
				$bord = $table['border'];
				$bord_det = $table['border_details'];
			}

			// Copy Right and bottom borders to adjacent cells (left and top) - otherwise would be overwritten
			if (is_array($bord_det) && ((!$this->usetableheader) || ($i>0))){

			 if ($cell['colspan']>1) { $csp = $cell['colspan']; }
			 else { $csp = 1; }
			 for ($cspi = 0; $cspi<$csp; $cspi++) {

			   if (($bord_det['B']['s']) && ($i < ($numrows-1))) {	// Bottom
				// already defined Top for adjacent cell below
				if ((is_array($cells[($i+1)][$j+$cspi]['border_details']['T'])) && ($cells[$i+1][$j+$cspi]['border_details']['T']['s'] == 1))  {
				   // if below-cell is lesser color than current
				   $cadj = $cells[($i+1)][$j+$cspi]['border_details']['T']['c'];
				   $csadj = $cadj['R']+$cadj['G']+$cadj['B'];
				   $csthis = $bord_det['B']['c']['R']+$bord_det['B']['c']['G']+$bord_det['B']['c']['B'];
				   if ($csadj > $csthis) {
					$cells[($i+1)][$j+$cspi]['border_details']['T'] = $bord_det['B'];
					if (strlen($cells[($i+1)][$j+$cspi]['border']) == 4) {
						$badj = $cells[($i+1)][$j+$cspi]['border'];
						$cells[($i+1)][$j+$cspi]['border'] =  '1'.substr($badj,1);
					}
					else { $cells[($i+1)][$j+$cspi]['border'] = '1000'; }
				   }
				}
				else {
				   // if below-cell border is not set
				   if (is_array($cells[($i+1)][$j+$cspi])) {	// check there is a cell n.b. colspan/rowspan
					$cells[($i+1)][$j+$cspi]['border_details']['T'] = $bord_det['B'];
					if (strlen($cells[($i+1)][$j+$cspi]['border']) == 4) {
						$badj = $cells[($i+1)][$j+$cspi]['border'];
						$cells[($i+1)][$j+$cspi]['border'] =  '1'.substr($badj,1);
					}
					else { $cells[($i+1)][$j+$cspi]['border'] = '1000'; }
				   }
				}
			   }
			 }

			 if ($cell['rowspan']>1) { $csp = $cell['rowspan']; }
			 else { $csp = 1; }
			 for ($cspi = 0; $cspi<$csp; $cspi++) {


			   if (($bord_det['R']['s']) && ($j < ($numcols-1))) {	// Right
				// already defined Left for adjacent cell to R
				if ((is_array($cells[$i+$cspi][$j+1]['border_details']['L'])) && ($cells[$i+$cspi][$j+1]['border_details']['L']['s'] == 1)) {	
				   // if right-cell is lesser color than current
				   $cadj = $cells[$i+$cspi][$j+1]['border_details']['L']['c'];
				   $csadj = $cadj['R']+$cadj['G']+$cadj['B'];
				   $csthis = $bord_det['R']['c']['R']+$bord_det['R']['c']['G']+$bord_det['R']['c']['B'];
				   if ($csadj > $csthis) {
					$cells[$i+$cspi][$j+1]['border_details']['L'] = $bord_det['R'];
					if (strlen($cells[$i+$cspi][$j+1]['border']) == 4) {
						$badj = $cells[$i+$cspi][$j+1]['border'];
						$cells[$i+$cspi][$j+1]['border'] =  substr($badj,0,3).'1';
					}
					else { $cells[$i+$cspi][$j+1]['border'] = '0001'; }
				   }
				}
				else {
				   // if right-cell border is not set
				   if (is_array($cells[$i+$cspi][$j+1])) {	// check there is a cell n.b. colspan/rowspan
					$cells[$i+$cspi][$j+1]['border_details']['L'] = $bord_det['R'];
					if (strlen($cells[$i+$cspi][$j+1]['border']) == 4) {
						$badj = $cells[$i+$cspi][$j+1]['border'];
						$cells[$i+$cspi][$j+1]['border'] =  substr($badj,0,3).'1';
					}
					else { $cells[$i+$cspi][$j+1]['border'] = '0001'; }
				   }
				}
			   }
			 }

			}


			// Added mPDF 1.1 for correct table border inheritance
			 if ($cell['colspan']>1) { $ccolsp = $cell['colspan']; }
			 else { $ccolsp = 1; }
			 if ($cell['rowspan']>1) { $crowsp = $cell['rowspan']; }
			 else { $crowsp = 1; }



			// Added mPDF 1.1 for correct table border inheritance
			if ($this->table_border_css_set) {

				if ($i == 0) {
				  if ($this->table['border_details']['T']) {
					$bord_det['T'] = $this->table['border_details']['T'];
				  }
				}
				// Edited mPDF 1.1 for correct table border inheritance
				if ($i == ($numrows-1) || ($i+$crowsp) == ($numrows) ) {
				  if ($this->table['border_details']['B']) {
					$bord_det['B'] = $this->table['border_details']['B'];
				  }
				}

				if ($j == 0) {
				  if ($this->table['border_details']['L']) {
					$bord_det['L'] = $this->table['border_details']['L'];
				  }
				}
				// Edited mPDF 1.1 for correct table border inheritance
				if ($j == ($numcols-1) || ($j+$ccolsp) == ($numcols) ) {
				  if ($this->table['border_details']['R']) {
					$bord_det['R'] = $this->table['border_details']['R'];
				  }
				}

				// Edited mPDF 1.1 for correct table border inheritance
				if ( $i == 0 || ($i == ($numrows-1)) || ($i+$crowsp) == ($numrows)  || $j == 0 || ($j == ($numcols-1)) || ($j+$ccolsp) == ($numcols)  ) {
					//////////////////////////////////////////////////////////////////////////
					if (! $bord_det['T']['s']) {  $bord_det['T']['s'] = '0'; }
					if (! $bord_det['B']['s']) {  $bord_det['B']['s'] = '0'; }
					if (! $bord_det['L']['s']) {  $bord_det['L']['s'] = '0'; }
					if (! $bord_det['R']['s']) {  $bord_det['R']['s'] = '0'; }
					// Edited mPDF 1.1 for correct table border inheritance
					$tmp =  $bord_det['T']['s']. $bord_det['R']['s']. $bord_det['B']['s'].$bord_det['L']['s'];
					$bord = $this->bord_bitadd($tmp,$bord);
					//////////////////////////////////////////////////////////////////////////
				}
			}


			if (isset($this->table['topntail'])) {
				if ($i == 0) {
					$bord_det['T'] = $this->border_details($this->table['topntail']);
				}
				else if (($i == 1) && $this->usetableheader) {
					$bord_det['T'] = $this->border_details($this->table['topntail']);
				}
				else if ($this->tabletheadjustfinished) {	// $this->tabletheadjustfinished called from tableheader
					$bord_det['T'] = $this->border_details($this->table['topntail']);
				}
				// edited mPDF 1.1 for correct rowspan
				if ($i == ($numrows-1) || ($i+$crowsp) == ($numrows) ) {
					$bord_det['B'] = $this->border_details($this->table['topntail']);
				}
				if ((($i == 0) || (($i == 1) && $this->usetableheader) || ($i == ($numrows-1)) || $this->tabletheadjustfinished) || ($i+$crowsp) == ($numrows) ) {
					//////////////////////////////////////////////////////////////////////////
					if (! $bord_det['T']['s']) {  $bord_det['T']['s'] = '0'; }
					if (! $bord_det['B']['s']) {  $bord_det['B']['s'] = '0'; }
					if (! $bord_det['L']['s']) {  $bord_det['L']['s'] = '0'; }
					if (! $bord_det['R']['s']) {  $bord_det['R']['s'] = '0'; }
					// Edited mPDF 1.1 for correct table border inheritance
					$tmp =  $bord_det['T']['s']. $bord_det['R']['s']. $bord_det['B']['s'].$bord_det['L']['s'];
					$bord = $this->bord_bitadd($tmp,$bord);
					//////////////////////////////////////////////////////////////////////////
				}
			}

			// Edited mPDF 1.1 add special CSS style thead-underline
			if (isset($this->table['thead-underline'])) {
				if (($i == 1) && $this->usetableheader) {
					$bord_det['T'] = $this->border_details($this->table['thead-underline']);
				}
				else if ($this->tabletheadjustfinished) {	// $this->tabletheadjustfinished called from tableheader
					$bord_det['T'] = $this->border_details($this->table['thead-underline']);
				}
				if ((($i == 1) && $this->usetableheader) || $this->tabletheadjustfinished) {
					//////////////////////////////////////////////////////////////////////////
					if (! $bord_det['T']['s']) {  $bord_det['T']['s'] = '0'; }
					if (! $bord_det['B']['s']) {  $bord_det['B']['s'] = '0'; }
					if (! $bord_det['L']['s']) {  $bord_det['L']['s'] = '0'; }
					if (! $bord_det['R']['s']) {  $bord_det['R']['s'] = '0'; }
					// Edited mPDF 1.1 for correct table border inheritance
					$tmp =  $bord_det['T']['s']. $bord_det['R']['s']. $bord_det['B']['s'].$bord_det['L']['s'];
					$bord = $this->bord_bitadd($tmp,$bord);
					//////////////////////////////////////////////////////////////////////////
				}
			}

			//Get info of first row ==>> table header
			if ($this->usetableheader and $i == 0 ) {
				$tableheader[$j]['x'] = $x;
				$tableheader[$j]['y'] = $y;
				$tableheader[$j]['h'] = $h;
				$tableheader[$j]['w'] = $w;
				$tableheader[$j]['text'] = $cell['text'];
				$tableheader[$j]['textbuffer'] = $cell['textbuffer'];
				$tableheader[$j]['a'] = $cell['a'];
				// Added mPDF 1.3 for rotated text in cell
				$tableheader[$j]['R'] = $cell['R'];

				$tableheader[$j]['va'] = $cell['va'];
				$tableheader[$j]['mih'] = $cell['mih'];
				$tableheader[$j]['bgcolor'] = $fill;
				//if ($table['border']) $tableheader[$j]['border'] = 'all';
				$tableheader[$j]['border'] = $bord;
				$tableheader[$j]['border_details'] = $bord_det;
			}

			// BORDER
			if ($bord || $bord_det) { $this->_tableRect($x, $y, $w, $h, $bord, $bord_det); }

			// TEXT

			$this->divalign=$align;

			$this->divwidth=$w;

			if (!empty($cell['textbuffer'])) {
				$opy = $this->y;
				// Edited mPDF 1.3 for rotated text in cell
				if ($cell['R']) {
					$cellPtSize = $cell['textbuffer'][0][11] / $this->shrin_k;
					$cellFontHeight = ($cellPtSize/$this->k);
					$opx = $this->x;
					$angle = INTVAL($cell['R']);
					// Only allow 45 - 90 degrees (when bottom-aligned) or -90
					if ($angle > 90) { $angle = 90; }
					// else if ($angle > 0 && (isset($cell['va']) && $cell['va']!='B')) { $angle = 90;}
					else if ($angle > 0 && $angle <45) { $angle = 45; }
					else if ($angle < 0) { $angle = -90; }
					$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
					if (!isset($cell['a']) || $cell['a']=='R') { 
						$this->x += ($w) + ($offset) - ($cellFontHeight/3) - ($this->cellPaddingR + $this->cMarginR); 
					}
					else if (!isset($cell['a']) || $cell['a']=='C') { 
						$this->x += ($w/2) + ($offset); 
					}
					else { 
						$this->x += ($offset) + ($cellFontHeight/3)+($this->cellPaddingL + $this->cMarginL); 
					}
					$str = ltrim(implode(' ',$cell['text']));
					$str = mb_rtrim($str,$this->mb_encoding);
					if (!isset($cell['va']) || $cell['va']=='M') { 
						$this->y -= ($h-$cell['mih'])/2; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += (($h-$cell['mih'])/2)+($this->cellPaddingT + $this->cMarginT) + ($cell['mih']-($this->cellPaddingT + $this->cMarginT+$this->cMarginB+$this->cellPaddingB)); }
						else if ($angle < 0) { $this->y += (($h-$cell['mih'])/2)+($this->cellPaddingT + $this->cMarginT); }
					}
					elseif (isset($cell['va']) && $cell['va']=='B') { 
						$this->y -= $h-$cell['mih']; //Undo what was added earlier VERTICAL ALIGN
						if ($angle > 0) { $this->y += $h-($this->cMarginB+$this->cellPaddingB); }
						else if ($angle < 0) { $this->y += $h-$cell['mih']+($this->cellPaddingT + $this->cMarginT); }
					}
					elseif (isset($cell['va']) && $cell['va']=='T') { 
						if ($angle > 0) { $this->y += $cell['mih']-($this->cMarginB+$this->cellPaddingB); }
						else if ($angle < 0) { $this->y += ($this->cellPaddingT + $this->cMarginT); }
					}
					$this->Rotate($angle,$this->x,$this->y);
					$s_fs = $this->FontSizePt;
					$s_f = $this->Font;
					$s_st = $this->Style;
					$this->SetFont($cell['textbuffer'][0][4],$cell['textbuffer'][0][2],$cellPtSize,true,true);
					$this->Text($this->x,$this->y,$str);
					$this->Rotate(0);
					$this->SetFont($s_f,$s_st,$s_fs,true,true);
					$this->x = $opx;
				}
				else {
					$this->y += ($this->cellPaddingT + $this->cMarginT);
					$this->printbuffer($cell['textbuffer'],'',true/*inside a table*/);
				}
				$this->y = $opy;
			}
			//Reset values
			$this->Reset();
		}//end of (if isset(cells)...)
	  }// end of columns

	  $this->tabletheadjustfinished = false;

	  if ($i == $numrows-1) { $this->y = $y + $h; } //last row jump (update this->y position)
	  if ($this->table_rotate) {
		$this->tbrot_h += $h;
	  }
	}// end of rows

	// Advance down page by half width of bottom border
	if ($this->cMarginB) { $this->y += $this->cMarginB; }

}//END OF FUNCTION _tableWrite()



/////////////////////////END OF TABLE CODE//////////////////////////////////


function _putextgstates() {
	for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k=>$v)
                $this->_out('/'.$k.' '.$v);
            $this->_out('>>');
            $this->_out('endobj');
	}
}




	var $encrypted=false;    //whether document is protected
	var $Uvalue;             //U entry in pdf document
	var $Ovalue;             //O entry in pdf document
	var $Pvalue;             //P entry in pdf document
	var $enc_obj_id;         //encryption object id
	var $last_rc4_key;       //last RC4 key encrypted (cached for optimisation)
	var $last_rc4_key_c;     //last RC4 computed key



	function SetProtection($permissions=array(),$user_pass='',$owner_pass=null)
	{
		$this->encrypted=false;
		if (!is_array($permissions)) { return 0; }


		if (count($permissions)<1) { return 0; }
		$this->last_rc4_key='';
		$this->padding="\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
						"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
		$protection = 192;
		foreach($permissions as $permission){
			if (!isset($options[$permission]))
				$this->Error('Incorrect permission: '.$permission);
			$protection += $options[$permission];
		}
		if ($owner_pass === null)
			$owner_pass = uniqid(rand());
		$this->encrypted = true;
		$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
	}

/**
* Compute key depending on object number where the encrypted data is stored
*/
function _objectkey($n)
{
		return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
}


function _putresources()
{
	$this->_putextgstates();
	$this->_putfonts();
	$this->_putimages();
	//Resource dictionary
	$this->offsets[2]=strlen($this->buffer);
	$this->_out('2 0 obj');
	$this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_out('>>');

	// mPDF 1.2
	if (count($this->extgstates)) {
		$this->_out('/ExtGState <<');
		foreach($this->extgstates as $k=>$extgstate)
			$this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
		$this->_out('>>');
	}


	if(count($this->images))
	{
		$this->_out('/XObject <<');
		foreach($this->images as $image)
			$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
		$this->_out('>>');
	}
	$this->_out('>>');
	$this->_out('endobj');
	$this->_putbookmarks(); //EDITEI
	if ($this->encrypted) {
		$this->_newobj();
		$this->enc_obj_id = $this->n;
		$this->_out('<<');
		$this->_putencryption();
		$this->_out('>>');
		$this->_out('endobj');
	}
}

	function _putencryption()
	{
		$this->_out('/Filter /Standard');
		$this->_out('/V 1');
		$this->_out('/R 2');
		$this->_out('/O ('.$this->_escape($this->Ovalue).')');
		$this->_out('/U ('.$this->_escape($this->Uvalue).')');
		$this->_out('/P '.$this->Pvalue);
	}

	function _puttrailer()
	{
		$this->_out('/Size '.($this->n+1));
		$this->_out('/Root '.$this->n.' 0 R');
		$this->_out('/Info '.($this->n-1).' 0 R');
		if ($this->encrypted) {
			$this->_out('/Encrypt '.$this->enc_obj_id.' 0 R');
			$this->_out('/ID [()()]');
		}
	}

	/**
	* RC4 is the standard encryption algorithm used in PDF format
	*/
	function _RC4($key, $text)
	{
		if ($this->last_rc4_key != $key) {
			$k = str_repeat($key, 256/strlen($key)+1);
			$rc4 = range(0,255);
			$j = 0;
			for ($i=0; $i<256; $i++){
				$t = $rc4[$i];
				$j = ($j + $t + $this->ords[$k{$i}]) % 256;
				$rc4[$i] = $rc4[$j];
				$rc4[$j] = $t;
			}
			$this->last_rc4_key = $key;
			$this->last_rc4_key_c = $rc4;
		} else {
			$rc4 = $this->last_rc4_key_c;
		}

		$len = strlen($text);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i=0; $i<$len; $i++){
			$a = ($a+1)%256;
			$t= $rc4[$a];
			$b = ($b+$t)%256;
			$rc4[$a] = $rc4[$b];
			$rc4[$b] = $t;
			$k = $rc4[($rc4[$a]+$rc4[$b])%256];
			$out.=$this->chrs[$this->ords[$text{$i}] ^ $k];
		}

		return $out;
	}

	/**
	* Get MD5 as binary string
	*/
	function _md5_16($string)
	{
		return pack('H*',md5($string));
	}

	/**
	* Compute O value
	*/
	function _Ovalue($user_pass, $owner_pass)
	{
		$tmp = $this->_md5_16($owner_pass);
		$owner_RC4_key = substr($tmp,0,5);
		return $this->_RC4($owner_RC4_key, $user_pass);
	}

	/**
	* Compute U value
	*/
	function _Uvalue()
	{
		return $this->_RC4($this->encryption_key, $this->padding);
	}

	/**
	* Compute encryption key
	*/
	function _generateencryptionkey($user_pass, $owner_pass, $protection)
	{
		// Pad passwords
		$user_pass = substr($user_pass.$this->padding,0,32);
		$owner_pass = substr($owner_pass.$this->padding,0,32);
		// Compute O value
		$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
		// Compute encyption key
		$tmp = $this->_md5_16($user_pass.$this->Ovalue.$this->chrs[$protection]."\xFF\xFF\xFF");
		$this->encryption_key = substr($tmp,0,5);
		// Compute U value
		$this->Uvalue = $this->_Uvalue();
		// Compute P value
		$this->Pvalue = -(($protection^255)+1);
	}

//=========================================
// FROM class PDF_Bookmark

	var $BMoutlines=array();
	var $OutlineRoot;

function Bookmark($txt,$level=0,$y=0)
{
	//****************************//
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if($y==-1) {
		if (!$this->ColActive){ $y=$this->GetY(); }
		else { $y = $this->y0; }	// If columns are on - mark top of columns
	}
	// else y is used as set, or =0 i.e. top of page
	// DIRECTIONALITY RTL
	$this->magic_reverse_dir($txt);
	// Edited mPDF 1.1 Keep Block together
	if ($this->keep_block_together) {
		$this->ktBMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
	else {
		$this->BMoutlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->page);
	}
}


function _putbookmarks()
{
	$nb=count($this->BMoutlines);
	if($nb==0)
		return;
	$lru=array();
	$level=0;
	foreach($this->BMoutlines as $i=>$o)
	{
		if($o['l']>0)
		{
			$parent=$lru[$o['l']-1];
			//Set parent and last pointers
			$this->BMoutlines[$i]['parent']=$parent;
			$this->BMoutlines[$parent]['last']=$i;
			if($o['l']>$level)
			{
				//Level increasing: set first pointer
				$this->BMoutlines[$parent]['first']=$i;
			}
		}
		else
			$this->BMoutlines[$i]['parent']=$nb;
		if($o['l']<=$level and $i>0)
		{
			//Set prev and next pointers
			$prev=$lru[$o['l']];
			$this->BMoutlines[$prev]['next']=$i;
			$this->BMoutlines[$i]['prev']=$prev;
		}
		$lru[$o['l']]=$i;
		$level=$o['l'];
	}
	//Outline items
	$n=$this->n+1;
	foreach($this->BMoutlines as $i=>$o)
	{
		$this->_newobj();

		$this->_out('<</Title '.$this->_UTF16BEtextstring($o['t']));

		$this->_out('/Parent '.($n+$o['parent']).' 0 R');
		if(isset($o['prev']))
			$this->_out('/Prev '.($n+$o['prev']).' 0 R');
		if(isset($o['next']))
			$this->_out('/Next '.($n+$o['next']).' 0 R');
		if(isset($o['first']))
			$this->_out('/First '.($n+$o['first']).' 0 R');
		if(isset($o['last']))
			$this->_out('/Last '.($n+$o['last']).' 0 R');
		// np_toc is 0 unless TOC generated (set in PDF_TOC)
		$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*($o['p']),($this->h-$o['y'])*$this->k));
		$this->_out('/Count 0>>');
		$this->_out('endobj');
	}
	//Outline root
	$this->_newobj();
	$this->OutlineRoot=$this->n;
	$this->_out('<</Type /BMoutlines /First '.$n.' 0 R');
	$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	$this->_out('endobj');
}



//======================================================
// FROM class PDF_TOC

	var $_toc=array();
	var $np_toc = 0;		// only set if TOC generated - used to adjust Bookmark pages
	var $TOCfont;
	var $TOCfontsize;
	var $TOCindent;
	var $TOCheader;
	var $TOCfooter;
	var $TOCpreHTML;
	var $TOCpostHTML;
	var $TOCbookmarkText;

	// Edited mPDF 1.3 - DEPRACATED but included for backwards compatability
	function startPageNums() {
	}


	// Initiate, and Mark a place for ther Table of Contents to be inserted at the end
	function TOC($tocfont='', $tocfontsize=8, $tocindent=5, $resetpagenum='', $pagenumstyle='', $suppress='') {
		if (!$tocfont) { $tocfont = $this->default_font; }
		if (!$tocfontsize) { $tocfontsize = $this->default_font_size; }
		// To use odd and even pages
		// Cannot start table of contents on an even page
		if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
			if ($this->ColActive) {
				if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			}
			$this->AddPage('','',$resetpagenum, $pagenumstyle, $suppress);
		}
		else { 
			$this->PageNumSubstitutions[] = array('from'=>$this->page, 'reset'=> $resetpagenum, 'type'=>$pagenumstyle, 'suppress'=>$suppress);
		}
		$this->TOCmark = $this->page; 
		$this->TOCfont = $tocfont;
		$this->TOCfontsize = $tocfontsize;
		$this->TOCindent = $tocindent;
	}

	function TOC_Entry($txt,$level=0) {
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if (!$this->isunicode || $this->isCJK) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }
		// DIRECTIONALITY RTL
		$this->magic_reverse_dir($txt);
		// Edited mPDF 1.1 Keep Block together
		if ($this->keep_block_together) {
			$this->_kttoc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->docPageNum(), 'op'=>$this->page);
		}
		else {
			$this->_toc[]=array('t'=>$txt,'l'=>$level,'p'=>$this->docPageNum());
		}
	}

	function insertTOC() {
	    if ($this->TOCmark) {
		if ($this->ColActive) { $this->SetColumns(0); }
		if (($this->useOddEven) && (($this->page)%2==1)) {	// ODD
			$this->AddPage();
			$extrapage = true;
		}
		if (is_array($this->TOCheader)) { $this->setHeader($this->TOCheader); }
		else { $this->setHeader(); }
		// Edited mPDF 1.3 - supress pagenumbers
		$this->AddPage('','', '', '', 'on');
		if (is_array($this->TOCfooter)) { $this->setFooter($this->TOCfooter); }
		else { $this->setFooter(); }

		$tocstart=$this->page;

		if ($this->TOCpreHTML) { $this->WriteHTML($this->TOCpreHTML); }

		foreach($this->_toc as $t) {
		   $lineheightcorr = 2-$t['l'];
		   //Offset
		   $level=$t['l'];

		   if ($this->directionality == 'rtl') {
			$PageCellSize=$this->GetStringWidth($t['p'])+2;
			$weight='';
			if($level==0)
				$weight='B';
			$str=$t['t'];
			$this->SetFont($this->TOCfont,$weight,$this->TOCfontsize);
			$strsize=$this->GetStringWidth($str);

			//Page number
			$this->Cell($PageCellSize,$this->FontSize+$lineheightcorr,$t['p'],0,0,'R');

			//Filling dots
			$this->SetFont($this->TOCfont,'',$this->TOCfontsize);
			$w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*$this->TOCindent)-($strsize+2);
			$nb=$w/$this->GetStringWidth('.');
			$dots=str_repeat('.',$nb);
			$this->Cell($w,$this->FontSize+$lineheightcorr,$dots,0,0,'R');

			// Text
			$this->Cell($strsize+2,$this->FontSize+$lineheightcorr,$str,0,1,'L');

		   }
		   else {
			if($level>0) { $this->Cell($level*$this->TOCindent,$this->FontSize+$lineheightcorr); }
			$PageCellSize=$this->GetStringWidth($t['p'])+2;

			// Text
			$weight='';
			if($level==0)
				$weight='B';
			$str=$t['t'];
			$this->SetFont($this->TOCfont,$weight,$this->TOCfontsize);
			$strsize=$this->GetStringWidth($str);
			$this->Cell($strsize+2,$this->FontSize+$lineheightcorr,$str);

			//Filling dots
			$this->SetFont($this->TOCfont,'',$this->TOCfontsize);
			$w=$this->w-$this->lMargin-$this->rMargin-$PageCellSize-($level*$this->TOCindent)-($strsize+2);
			$nb=$w/$this->GetStringWidth('.');
			$dots=str_repeat('.',$nb);
			$this->Cell($w,$this->FontSize+$lineheightcorr,$dots,0,0,'R');

			//Page number
			$this->Cell($PageCellSize,$this->FontSize+$lineheightcorr,$t['p'],0,1,'R');
		   }
		}

		if ($this->TOCpostHTML) { $this->WriteHTML($this->TOCpostHTML); }
		$this->AddPage('','E');
		//Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;

		//grab TOC and move to selected location
		$n=$this->page;
		$n_toc = $n - $tocstart + 1;

		$this->np_toc = $n_toc;
		$last = array();
		//store toc pages
		for($i = $tocstart;$i <= $n;$i++)
			$last[]=$this->pages[$i];
		//move pages
		for($i=$tocstart - 1;$i>=$this->TOCmark-1;$i--) {
			$this->pages[$i+$n_toc]=$this->pages[$i];
		}
		//Put toc pages at insert point
		for($i = 0;$i < $n_toc;$i++) {
			$this->pages[$this->TOCmark + $i]=$last[$i];
		}

		// Update Bookmarks
		foreach($this->BMoutlines as $i=>$o) {
			if($o['p']>=$this->TOCmark) {
				$this->BMoutlines[$i]['p'] += $n_toc;
			}
		}

		// Insert new Bookmark for Bookmark
		if ($this->TOCbookmarkText) {
			$insert = 0;
			foreach($this->BMoutlines as $i=>$o) {
				if($o['p']<$this->TOCmark) {	// i.e. before point of insertion
					$insert = $i;
				}
			}
			$txt = $this->purify_utf8_text($this->TOCbookmarkText);
			if ($this->text_input_as_HTML) {
				$txt = $this->all_entities_to_utf8($txt);
			}
			// DIRECTIONALITY RTL
			$this->magic_reverse_dir($txt);
			$newBookmark[0] = array('t'=>$txt,'l'=>0,'y'=>0,'p'=>$this->TOCmark);

			array_splice($this->BMoutlines,($insert),0,$newBookmark);

		}

		// Update Page Links
		if (count($this->PageLinks)) {
		   $newarr = array();
		   foreach($this->PageLinks as $i=>$o) {
			if($i>=$this->TOCmark) {
				$newarr[($i + $n_toc)] = $this->PageLinks[$i];
			}
			else {
				$newarr[$i] = $this->PageLinks[$i];
			}
		   }
		   $this->PageLinks = $newarr;
		}

		// Update Internal Links
		if (count($this->internallink)) {
		   foreach($this->internallink as $key=>$o) {
			if($o['PAGE']>=$this->TOCmark) {
				$this->internallink[$key]['PAGE'] += $n_toc;
			}
		   }
		}

		// Update Links
		if (count($this->links)) {
		   foreach($this->links as $key=>$o) {
			if($o[0]>=$this->TOCmark) {
				$this->links[$key][0] += $n_toc;	// 	$this->links[$link]=array($page,$y);
			}
		   }
		}

		// Delete empty page that was inserted earlier
		if ($extrapage) {
			unset($this->pages[count($this->pages)]);
			$this->page--;	// Reset page pointer
		}


	    }
	}

//======================================================
// FROM class PDF_Ref == INDEX
	var $ColActive=0;        //Flag indicating that columns are on (the index is being processed)
	var $ChangePage=0;       //Flag indicating that a page break has occurred
	var $Reference=array();  //Array containing the references
					// 
	var $CurrCol=0;              //Current column number
	var $NbCol;              //Total number of columns
	var $y0;                 //Top ordinate of columns
	var $ColL = array(0);			// Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
	var $ColWidth;		// Column width
	var $ColGap=5;


function Reference($txt)
{
	$txt = strip_tags($txt);
	$txt = $this->purify_utf8_text($txt);
	if ($this->text_input_as_HTML) {
		$txt = $this->all_entities_to_utf8($txt);
	}
	if (!$this->isunicode || $this->isCJK) { $txt = mb_convert_encoding($txt,$this->mb_encoding,'UTF-8'); }

	$Present=0;
	$size=sizeof($this->Reference);

	if ($this->directionality == 'rtl') {
		$txt = str_replace(':',' - ',$txt);
	}
	else {
		$txt = str_replace(':',', ',$txt);
	}
	//Search the reference (AND Ref/PageNo) in the array
	for ($i=0;$i<$size;$i++){
		if ($this->Reference[$i]['t']==$txt){
			$Present=1;
			$currpno = $this->docPageNum();
			if ($this->directionality == 'rtl') {
				$currpgs = explode(' . ', $this->Reference[$i]['p']);
				if (!in_array($currpno,$currpgs)) {
					$this->Reference[$i]['p'].=' . '.$currpno;
				}
			}
			else {
				$currpgs = explode(', ', $this->Reference[$i]['p']);
				if (!in_array($currpno,$currpgs)) {
					$this->Reference[$i]['p'].=', '.$currpno;
				}
			}
		}
	}
	//If not found, add it
	if ($Present==0) {
		// Edited mPDF 1.1 Keep Block together
		if ($this->keep_block_together) {
			$this->ktReference[]=array('t'=>$txt,'p'=>$this->docPageNum(), 'op'=>$this->page);
		}
		else {
			$this->Reference[]=array('t'=>$txt,'p'=>$this->docPageNum());
		}
	}
}

// Added function to add a reference "Elephants. See Chickens"
function ReferenceSee($txta,$txtb)
{
	$txta = strip_tags($txta);
	$txtb = strip_tags($txtb);
	$txta = $this->purify_utf8_text($txta);
	$txtb = $this->purify_utf8_text($txtb);
	if ($this->text_input_as_HTML) {
		$txta = $this->all_entities_to_utf8($txta);
		$txtb = $this->all_entities_to_utf8($txtb);
	}
	if (!$this->isunicode || $this->isCJK) { 
		$txta = mb_convert_encoding($txta,$this->mb_encoding,'UTF-8'); 
		$txtb = mb_convert_encoding($txtb,$this->mb_encoding,'UTF-8'); 
	}
	if ($this->directionality == 'rtl') {
		$txta = str_replace(':',' - ',$txta);
		$txtb = str_replace(':',' - ',$txtb);
	}
	else {
		$txta = str_replace(':',', ',$txta);
		$txtb = str_replace(':',', ',$txtb);
	}
	$this->Reference[]=array('t'=>$txta.' - see '.$txtb,'p'=>'');
}

function CreateReference($NbCol=1, $reffontsize='', $linespacing=1, $offset=3, $usedivletters=1, $divlettfontsize='', $gap=5, $reffont='',$divlettfont='')
{
	if (!$reffontsize) { $reffontsize = $this->default_font_size; }
	if (!$divlettfontsize) { $divlettfontsize = ($this->default_font_size * 1.8); }
	if (!$reffont) { $reffont = $this->default_font; }
	if (!$divlettfont) { $divlettfont = $reffont; }
	if (!$linespacing) { $linespacing= 1; }
	if ($this->ColActive) { $this->SetColumns(0); }
	$size=sizeof($this->Reference);
	if ($size == 0) { return false; }

	if ($NbCol<2) { $NbCol = 1; }
	else { $this->SetColumns($NbCol,'',$gap); }
	$this->SetFont($reffont,'',$reffontsize);
	if ($this->directionality == 'rtl') { $align = 'R'; }
	else { $align = 'L'; }
	$lett = '';
	function cmp ($a, $b) {
	    return strnatcmp(strtolower($a['t']), strtolower($b['t']));
	}
	//Alphabetic sort of the references
	usort($this->Reference, 'cmp');

	for ($i=0;$i<$size;$i++){
		$str=$this->Reference[$i]['t'];
		if ($this->Reference[$i]['p']) { 
			$t = $this->Reference[$i]['p'];
			if ($this->directionality == 'rtl') {
				$rpgs = explode(' . ', $t);
				$rstr = make_range_string($rpgs,' . ');
			}
			else {
				$rpgs = explode(', ', $t);
				$rstr = make_range_string($rpgs,', ');
			}
			$str .= '  ' . $rstr;
		}
		if ($usedivletters) {
		   $lett = mb_substr($str,0,1,$this->mb_encoding );
		   if ($lett != $last_lett) {
			if ($i>0) { $this->Ln(); }
			$this->SetFont($divlettfont,'B',$divlettfontsize);
			$this->SetStyle('B',true);
			$this->Cell($this->ColWidth,$this->FontSize,$lett,0,1,$align);
			$this->SetFont($reffont,'',$reffontsize);
		   }
		}


		$strsize=$this->GetStringWidth($str);

		if ($strsize > $this->ColWidth - 2) {

			//Split into $str1 and $str2
			$str1 = '';	$str2 = '';
    			// for every character in the string
			for ( $j = 0; $j < mb_strlen( $str, $this->mb_encoding ); $j++ ) {
				$strsize=$this->GetStringWidth(mb_substr($str,0,$j,$this->mb_encoding ));
				if ($strsize > $this->ColWidth - 2) { break; }

				if ((($this->isunicode)  || ($this->isCJK)) && (!$this->usingembeddedfonts)) {
					$c = mb_substr($str,$j,1,$this->mb_encoding );
					if (preg_match("/[ ]/u", $c)) {
						$str1 = mb_rtrim(mb_substr($str,0,$j,$this->mb_encoding ),$this->mb_encoding);
						$str2 = mb_rtrim(mb_substr($str,$j,999,$this->mb_encoding ),$this->mb_encoding);
					}
				}
				else {
					$c = $str{$j};
					if ($c == ' ') { 
						$str1 = trim(substr($str,0,$j));
						$str2 = trim(substr($str,$j,999));
					}
				}
			}
			if (!$str1) { 	// Failsafe if goes wrong
				$this->MultiCell($this->ColWidth,$this->FontSize*$linespacing,'FAIL'.$str,0,'L',0,'','ltr',true);
			}
			else {
				// DIRECTIONALITY RTL
				$this->magic_reverse_dir($str1);	// MultiCell does magic reverse - Cell doesn't
				$this->Cell($this->ColWidth,$this->FontSize*$linespacing,$str1,0,1,$align);
				if ($this->directionality == 'rtl') {
					$this->SetX($this->lMargin);
					$this->x=$this->lMargin;
				}
				else {
					$this->SetX($this->lMargin+$offset);
					$this->x=$this->lMargin+$offset;
				}
				$this->MultiCell($this->ColWidth-$offset,$this->FontSize*$linespacing,$str2,0,$align,0,'','ltr',true);
				$this->SetX($this->lMargin);
				$this->x=$this->lMargin;
			}
		}
		else {
			// DIRECTIONALITY RTL
			$this->magic_reverse_dir($str);
			$this->Cell($this->ColWidth,$this->FontSize*$linespacing,$str,0,1,$align);	// was strsize - changed for RTL
		}


		$last_lett = $lett;
	}
	if ($this->ColActive) { $this->SetColumns(0);  }
}



//----------- COLUMNS ---------------------
	var $ColR = array(0);			// Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
	var $ChangeColumn = 0;
	var $columnbuffer = array();
	var $ColDetails = array();		// Keeps track of some column details
	var $columnLinks = array();		// Cross references PageLinks
	var $colvAlign;				// Vertical alignment for columns

function SetColumns($NbCol,$vAlign='',$gap=5) {
// NbCol = number of columns
// CurrCol = Number of the current column starting at 0
// Called externally to set columns on/off and number
// Integer 2 upwards sets columns on to that number
// Anything less than 2 turns columns off
	if ($NbCol<2) {	// SET COLUMNS OFF
		if ($this->ColActive) { 
			$this->ColActive=0;
			if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			$this->NbCol=1;
			$this->ResetMargins(); 
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->divwidth = 0;
			$this->Ln(); 
		}
		$this->ColActive=0;
		$this->columnbuffer = array();
		$this->ColDetails = array();
		$this->columnLinks = array();
	}
	else {	// SET COLUMNS ON

		if (($NbCol != $this->NbCol) && ($this->ColActive)) { 
			$this->ColActive=0;
			if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
			$this->ResetMargins(); 
		}
		$this->Ln();
		$this->NbCol=$NbCol;
		$this->ColGap = $gap;
		$this->divwidth = 0;
		$this->ColActive=1;
		$this->ColumnAdjust = true;	// enables column height adjustment for the page
		$this->columnbuffer = array();
		$this->ColDetails = array();
		$this->columnLinks = array();
		if ((strtoupper($vAlign) == 'J') || (strtoupper($vAlign) == 'JUSTIFY')) { $vAlign = 'J'; } 
		else { $vAlign = ''; }
		$this->colvAlign = $vAlign;
		//Save the ordinate
		$absL = $this->DeflMargin-($gap/2);
		$absR = $this->DefrMargin-($gap/2);
		$PageWidth = $this->w-$absL-$absR;	// virtual pagewidth for calculation only
		$ColWidth = (($PageWidth - ($gap * ($NbCol)))/$NbCol);
		$this->ColWidth = $ColWidth;
		if ($this->directionality == 'rtl') { 
			for ($i=0;$i<$this->NbCol;$i++) {
				$this->ColL[$i] = $absL + ($gap/2) + (($NbCol - ($i+1))*($PageWidth/$NbCol)) ;
				$this->ColR[$i] = $this->ColL[$i] + $ColWidth;	// NB This is not R margin -> R pos
			}
		} 
		else { 
			for ($i=0;$i<$this->NbCol;$i++) {
				$this->ColL[$i] = $absL + ($gap/2) + ($i* ($PageWidth/$NbCol)   );
				$this->ColR[$i] = $this->ColL[$i] + $ColWidth;	// NB This is not R margin -> R pos
			}
		}
		$this->pgwidth = $ColWidth;
		$this->SetCol(0);
		$this->y0=$this->GetY();
	}
	$this->x = $this->lMargin;
}

function SetCol($CurrCol) {
// Used internally to set column by number 0 is 1st column
	//Set position on a column
	$this->CurrCol=$CurrCol;
	$x = $this->ColL[$CurrCol];
	$xR = $this->ColR[$CurrCol];	// NB This is not R margin -> R pos
	if (($this->useOddEven) && (($this->page)%2==0)) {	// EVEN
		$x += $this->MarginCorrection ;
		$xR += $this->MarginCorrection ;
	}
	$this->SetMargins($x,($this->w - $xR),$this->tMargin);
}

function AcceptPageBreak()
{
	if ($this->ColActive==1) {
	    if($this->CurrCol<$this->NbCol-1) {
		// Paint Div Border if necessary
   		//PAINT BACKGROUND COLOUR OR BORDERS for DIV - DISABLED AT PRESENT in ->PaintDivBorder
		// END PAINT BACKGROUND/BORDER
        	//Go to the next column
		$this->CurrCol++;
       	$this->SetCol($this->CurrCol);
		$this->y=$this->y0;
       	$this->ChangeColumn=1;	// Number (and direction) of columns changed +1, +2, -2 etc.
		//****************************//
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
		//****************************//
        	//Stay on the page
        	return false;
	   }
	   else {
    		//Go back to the first column - NEW PAGE
		// Paint Div Border if necessary
   		//PAINT BACKGROUND COLOUR OR BORDERS for DIV - DISABLED AT PRESENT in ->PaintDivBorder
		// END PAINT BACKGROUND/BORDER
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
		$this->SetCol(0);
        	$this->ChangePage=1;
		$this->y0 = $this->tMargin;
        	$this->ChangeColumn= -($this->NbCol-1);
		//****************************//
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
		//****************************//
        	//Page break
       	return true;
	   }
	}
	else if ($this->table_rotate) {
		if (count($this->tablebuffer)) { $this->printtablebuffer(); }
		return true;
	}
	else {
        	$this->ChangeColumn=0;
		return true;
	}
	return true;
}

function NewColumn()
{
	if ($this->ColActive==1) {
	    if($this->CurrCol<$this->NbCol-1) {
        	//Go to the next column
		$this->CurrCol++;
        	$this->SetCol($this->CurrCol);
        	$this->y = $this->y0;
        	$this->ChangeColumn=1;
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
        	//Stay on the page
    		}
    		else {
    		//Go back to the first column
        	//Page break
		if (count($this->columnbuffer)) { $this->printcolumnbuffer(); }
		$this->AddPage();
		$this->SetCol(0);
        	$this->ChangePage=1;
		$this->y0 = $this->tMargin;
        	$this->ChangeColumn= -($this->NbCol-1);
		// DIRECTIONALITY RTL
		if ($this->directionality == 'rtl') { $this->ChangeColumn = -($this->ChangeColumn); }
    		}
		$this->x = $this->lMargin;
	}
	else {
		$this->AddPage();
	}
}


function printcolumnbuffer() {
   // Columns ended (but page not ended) -> try to match all columns - unless disabled by using a custom column-break
   $k = $this->k;
   if ((!$this->ColActive) && ($this->ColumnAdjust)) {
	// Calculate adjustment to add to each column to calculate rel_y value
	$this->ColDetails[0]['add_y'] = 0;
	$last_col = 0;
	// Recursively add previous column's height
	for($i=1;$i<$this->NbCol;$i++) { 
		if ($this->ColDetails[$i]['bottom_margin']) { // If any entries in the column
			$this->ColDetails[$i]['add_y'] = ($this->ColDetails[$i-1]['bottom_margin'] - $this->y0) + $this->ColDetails[$i-1]['add_y'];
			$last_col = $i; 	// Last column actually printed
		}
	}

	// Calculate value for each position sensitive entry as though for one column
	foreach($this->columnbuffer AS $key=>$s) { 
		$t = $s['s'];
		if (preg_match('/BT \d+\.\d\d (\d+\.\d\d) Td/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d (\d+\.\d\d) \d+\.\d\d [\-]{0,1}\d+\.\d\d re/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) m/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d (\d+\.\d\d) l/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/q \d+\.\d\d 0 0 \d+\.\d\d \d+\.\d\d (\d+\.\d\d) cm \/I\d+ Do Q/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}
		else if (preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ c/',$t)) {
			$this->columnbuffer[$key]['rel_y'] = $s['y'] + $this->ColDetails[$s['col']]['add_y'] - $this->y0;
		}

	}
	$sum_h = $this->ColDetails[$last_col]['add_y'] + $this->ColDetails[$last_col]['bottom_margin'] - $this->y0;
	$target_h = ($sum_h / $this->NbCol);

	// Now update the columns - divide into columns of approximately equal value
	$last_new_col = 0; 
	$yadj = 0;	// mm
	$xadj = 0;
	$last_col_bottom = 0;
	$lowest_bottom_y = 0;
	$block_bottom = 0;
	foreach($this->columnbuffer AS $key=>$s) { 
	  if (isset($s['rel_y'])) {	// only process position sensitive data

		if (($s['rel_y']+$s['h']) < $block_bottom) {
			$newcolumn = $last_new_col ;
		}
		else {
			$tmp = ($s['rel_y'] / $target_h);	
			$newcolumn = INTVAL("$tmp");
		}

		$block_bottom = max($block_bottom,($s['rel_y']+$s['h']));

		if ($this->directionality == 'rtl') {
			$xadj = -(($newcolumn - $s['col']) * ($this->ColWidth + $this->ColGap));
		}
		else {
			$xadj = ($newcolumn - $s['col']) * ($this->ColWidth + $this->ColGap);
		}

		if ($last_new_col != $newcolumn) {	// Added new column
			$last_col_bottom = $this->columnbuffer[$key]['rel_y'];
			$block_bottom = 0;
		}
		$yadj = ($s['rel_y'] - $s['y']) - ($last_col_bottom)+$this->y0;
		// callback function in htmltoolkit
		$t = $s['s'];
		$t = preg_replace('/BT (\d+\.\d\d) (\d+\.\d\d) Td/e',"columnAdjustAdd('Td',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) ([\-]{0,1}\d+\.\d\d) re/e',"columnAdjustAdd('re',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) l/e',"columnAdjustAdd('l',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/q (\d+\.\d\d) 0 0 (\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) cm \/I/e',"columnAdjustAdd('img',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"columnAdjustAdd('draw',$k,$xadj,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"columnAdjustAdd('bezier',$k,$xadj,$yadj,'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		$this->columnbuffer[$key]['s'] = $t;
		$this->columnbuffer[$key]['newcol'] = $newcolumn;
		$this->columnbuffer[$key]['newy'] = $s['y'] + $yadj;
		$last_new_col = $newcolumn;
		$clb = $s['y'] + $yadj + $s['h'] ;	// bottom_margin of current
		if ($clb > $this->ColDetails[$newcolumn]['max_bottom']) { $this->ColDetails[$newcolumn]['max_bottom'] = $clb; }
		if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
		// Adjust LINKS
		if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][0] += ($xadj*$k);
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);
		}

	  }
	}


	// Adjust column length to be equal
	if ($this->colvAlign == 'J') {
	 foreach($this->columnbuffer AS $key=>$s) { 
	   if (isset($s['rel_y'])) {	// only process position sensitive data
	    // Set ratio to expand y values or heights
	    if ($this->ColDetails[$s['newcol']]['max_bottom'] > ($this->y0 + $s['h'])) { 
		$ratio = ($lowest_bottom_y - ($this->y0 +($s['h']))) / ($this->ColDetails[$s['newcol']]['max_bottom'] - ($this->y0 + ($s['h'])));
	    }
	    else { $ratio = 1; }
	    if (($ratio > 1) && ($ratio < $this->max_colH_correction)) {
		$yadj = ($s['newy'] - $this->y0) * ($ratio - 1);
		// callback function in htmltoolkit
		$t = $s['s'];
		$t = preg_replace('/BT (\d+\.\d\d) (\d+\.\d\d) Td/e',"columnAdjustRatio('Td',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) ([\-]{0,1}\d+\.\d\d) re/e',"columnAdjustRatio('re',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) l/e',"columnAdjustRatio('l',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/q (\d+\.\d\d) 0 0 (\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) cm \/I/e',"columnAdjustRatio('img',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"columnAdjustRatio('draw',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"columnAdjustRatio('bezier',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		$this->columnbuffer[$key]['s'] = $t;

		// Adjust LINKS
		if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);	// y value
			$this->PageLinks[$this->page][$ref][3] *= $ratio;	// height
		}
	    }
	  }
	 }
	}

	// Now output the adjusted values
	foreach($this->columnbuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
	if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }
   }

   // Columns not ended but new page -> align columns (can leave the columns alone - just tidy up the height)
   else if (($this->colvAlign == 'J') && ($this->ColumnAdjust))  {

	$k = $this->k;
	// calculate the lowest bottom margin
	$lowest_bottom_y = 0;
	foreach($this->columnbuffer AS $key=>$s) { 
	   // Only process output data
	   $t = $s['s'];
	   if ((preg_match('/BT \d+\.\d\d (\d+\.\d\d) Td/',$t)) || (preg_match('/\d+\.\d\d (\d+\.\d\d) \d+\.\d\d [\-]{0,1}\d+\.\d\d re/',$t)) ||
		(preg_match('/\d+\.\d\d (\d+\.\d\d) l/',$t)) || 
		(preg_match('/q \d+\.\d\d 0 0 \d+\.\d\d \d+\.\d\d (\d+\.\d\d) cm \/I\d+ Do Q/',$t)) || 
		(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) m/',$t)) || 
		(preg_match('/\d+\.\d\d+ (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ c/',$t)) ) {

		$clb = $s['y'] + $s['h'];
		if ($clb > $this->ColDetails[$s['col']]['max_bottom']) { $this->ColDetails[$s['col']]['max_bottom'] = $clb; }
		if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
	   }
	}
	// Adjust column length equal
	 foreach($this->columnbuffer AS $key=>$s) { 
	    // Set ratio to expand y values or heights
	    if ($this->ColDetails[$s['col']]['max_bottom'] > ($this->y0 + $s['h'])) { 
		$ratio = ($lowest_bottom_y - ($this->y0 +($s['h']))) / ($this->ColDetails[$s['col']]['max_bottom'] - ($this->y0 + ($s['h'])));
	    }
	    else { $ratio = 1; }
	    if (($ratio > 1) && ($ratio < $this->max_colH_correction)) {
		$yadj = ($s['y'] - $this->y0) * ($ratio - 1);
		// callback function in htmltoolkit
		$t = $s['s'];
		$t = preg_replace('/BT (\d+\.\d\d) (\d+\.\d\d) Td/e',"columnAdjustRatio('Td',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) ([\-]{0,1}\d+\.\d\d) re/e',"columnAdjustRatio('re',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/q (\d+\.\d\d) 0 0 (\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) cm \/I/e',"columnAdjustRatio('img',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) l/e',"columnAdjustRatio('l',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"columnAdjustRatio('draw',$k,$ratio,$yadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"columnAdjustRatio('bezier',$k,$ratio,$yadj,'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		// Adjust LINKS
		if ($this->columnbuffer[$key]['s'] != $t) {	// something was changed i.e. a relevant entry
		   // otherwise triggers for all entries in column buffer (.e.g. formatting) and makes below adjustments more than once
		   if (isset($this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])])) {
			$ref = $this->columnLinks[$s['col']][INTVAL($s['x'])][INTVAL($s['y'])];
			$this->PageLinks[$this->page][$ref][1] -= ($yadj*$k);	// y value
			$this->PageLinks[$this->page][$ref][3] *= $ratio;	// height
		   }
		}

		$this->columnbuffer[$key]['s'] = $t;
	    }
	 }

	// Now output the adjusted values
	foreach($this->columnbuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
//	if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }	// Not needed?
   }


   // Just reproduce the page as it was
   else {
	// If page has not ended but height adjustment was disabled by custom column-break - adjust y
	if ((!$this->ColumnAdjust) && (!$this->ColActive)) {
		$k = $this->k;
		// calculate the lowest bottom margin
		$lowest_bottom_y = 0;
		foreach($this->columnbuffer AS $key=>$s) { 
			$clb = $s['y'] + $s['h'];
			if ($clb > $this->ColDetails[$s['col']]['max_bottom']) { $this->ColDetails[$s['col']]['max_bottom'] = $clb; }
			if ($clb > $lowest_bottom_y) { $lowest_bottom_y = $clb; }
		}
		if ($lowest_bottom_y > 0) { $this->y = $lowest_bottom_y ; }
	}
	foreach($this->columnbuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
   }
   $this->columnbuffer = array();
   $this->ColDetails = array();
   $this->columnLinks = array();
}


//==================================================================
function printtablebuffer() {
	if (!$this->table_rotate) { 
		foreach($this->tablebuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }
		return; 
	}
	// For rotated tables
	$k = $this->k;

	$y0 = $this->tbrot_y0 * $k;
	$x0 =	$this->tbrot_x0 * $k;
	$w = $this->tbrot_w * $k;
	$h =	$this->tbrot_h * $k;
	$ph = $this->h * $k;

	// Adjust to centre align the table
	$xadj = (($this->pgwidth - $this->tbrot_h)/2) * $k;

	// Update the tablebuffer entries
	foreach($this->tablebuffer AS $key=>$s) { 
		// callback function in htmltoolkit
		$t = $s['s'];
		$t = preg_replace('/BT (\d+\.\d\d) (\d+\.\d\d) Td/e',"tableRotate('Td',$k,$y0,$x0,$w,$h,$this->table_rotate,$ph,$xadj,'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) (\-\d+\.\d\d) re/e',"tableRotate('re',$k,$y0,$x0,$w,$h,$this->table_rotate,$ph,$xadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/q (\d+\.\d\d) 0 0 (\d+\.\d\d) (\d+\.\d\d) (\d+\.\d\d) cm \/I/e',"tableRotate('img',$k,$y0,$x0,$w,$h,$this->table_rotate,$ph,$xadj,'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d) (\d+\.\d\d) m (\d+\.\d\d) (\d+\.\d\d) l S/e',"tableRotate('l',$k,$y0,$x0,$w,$h,$this->table_rotate,$ph,$xadj,'\\1','\\2','\\3','\\4')",$t);

		$this->tablebuffer[$key]['s'] = $t;
	}



	// Now output the adjusted values
	foreach($this->tablebuffer AS $s) { $this->pages[$this->page] .= $s['s']."\n"; }

	$this->y = $y0 + $this->tbrot_maxw;
	$this->x = $this->lMargin;

	$this->tablebuffer = array();
}

//==================================================================
function printdivbuffer() {
	// Edited mPDF 1.1 keeping block together on one page
	$k = $this->k;
	$p1 = $this->blk[$this->blklvl]['startpage'];
	$p2 = $this->page;
	$bottom[$p1] = $this->ktBlock[$p1]['bottom_margin'];
	$bottom[$p2] = $this->y;	// $this->ktBlock[$p2]['bottom_margin'];
	$top[$p1] = $this->blk[$this->blklvl]['y00'];
	$top2 = $this->h;
	foreach($this->divbuffer AS $key=>$s) { 
		if ($s['page'] == $p2) {
			$top2 = MIN($s['y'], $top2);
		}
	}
	$top[$p2] = $top2;
	$height[$p1] = ($bottom[$p1] - $top[$p1]);
	$height[$p2] = ($bottom[$p2] - $top[$p2]);
	$xadj[$p1] = $this->MarginCorrection;
	$yadj[$p1] = -($top[$p1] - $top[$p2]);
	$xadj[$p2] = 0;
	$yadj[$p2] = $height[$p1];

	if ($this->ColActive || !$this->keep_block_together || $this->blk[$this->blklvl]['startpage'] == $this->page || ($this->page - $this->blk[$this->blklvl]['startpage']) > 1 || ($height[$p1]+$height[$p2]) > $this->h) { 
		foreach($this->divbuffer AS $s) { $this->pages[$s['page']] .= $s['s']."\n"; }
		foreach($this->ktLinks AS $p => $l) {
		   foreach($l AS $v) {
			$this->PageLinks[$p][] = $v;
		   }
		}
	      // Adjust Reference (index)
	      foreach($this->ktReference AS $v) {
			$this->Reference[]=array('t'=>$v['t'],'p'=>$v['p']);
	      }

	      // Adjust Bookmarks
	      foreach($this->ktBMoutlines AS $v) {
			$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$v['p']);
	      }

	      // Adjust ToC
	      foreach($this->_kttoc AS $v) {
			$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p']);
	      }
		$this->divbuffer = array();
		$this->ktLinks = array();
		$this->ktBlock = array();
		$this->ktReference = array();
		$this->ktBMoutlines = array();
		$this->_kttoc = array();
		$this->keep_block_together = 0;
		return; 
	}
	else {
	   foreach($this->divbuffer AS $key=>$s) { 
		// callback function in htmltoolkit
		$t = $s['s'];
		$p = $s['page'];
		$t = preg_replace('/BT (\d+\.\d\d+) (\d+\.\d\d+) Td/e',"blockAdjust('Td',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) ([\-]{0,1}\d+\.\d\d+) re/e',"blockAdjust('re',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) l/e',"blockAdjust('l',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/q (\d+\.\d\d+) 0 0 (\d+\.\d\d+) (\d+\.\d\d) (\d+\.\d\d) cm \/I/e',"blockAdjust('img',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) m/e',"blockAdjust('draw',$k,$xadj[$p],$yadj[$p],'\\1','\\2')",$t);
		$t = preg_replace('/(\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) (\d+\.\d\d+) c/e',"blockAdjust('bezier',$k,$xadj[$p],$yadj[$p],'\\1','\\2','\\3','\\4','\\5','\\6')",$t);

		$this->pages[$this->page] .= $t."\n"; 
	   }
	   // Adjust hyperLinks
	   foreach($this->ktLinks AS $p => $l) {
	    foreach($l AS $v) {
		$v[0] += ($xadj[$p]*$k);
		$v[1] -= ($yadj[$p]*$k);
		$this->PageLinks[$p2][] = $v;
	    }
	   }


	   // Adjust Bookmarks
	   foreach($this->ktBMoutlines AS $v) {
		if ($v['y'] != 0) { $v['y'] += ($yadj[$v['p']]); }
		$this->BMoutlines[]=array('t'=>$v['t'],'l'=>$v['l'],'y'=>$v['y'],'p'=>$p2);
	   }

	   // Adjust Reference (index)
	   foreach($this->ktReference AS $v) {
		if ($v['op'] == $p1) { $v['p'] += 1; }
		$this->Reference[]=array('t'=>$v['t'],'p'=>$v['p']);
	   }

	   // Adjust ToC
	   foreach($this->_kttoc AS $v) {
		if ($v['op'] == $p1) { $v['p'] += 1; }
		$this->_toc[]=array('t'=>$v['t'],'l'=>$v['l'],'p'=>$v['p']);
	   }

	   $this->y = $top[$p2] + $height[$p1] + $height[$p2];
	   $this->x = $this->lMargin;

	   $this->divbuffer = array();
	   $this->ktLinks = array();
	   $this->ktBlock = array();
	   $this->ktReference = array();
	   $this->ktBMoutlines = array();
	   $this->_kttoc = array();
	   $this->keep_block_together = 0;
	}
}


//==================================================================
// Added ELLIPSES and CIRCLES
function Circle($x,$y,$r,$style='S') {
	$this->Ellipse($x,$y,$r,$r,$style);
}

function Ellipse($x,$y,$rx,$ry,$style='S') {
	if($style=='F') {$op='f'; }
	elseif($style=='FD' or $style=='DF') { 	$op='B'; }
	else { $op='S'; }
	$lx=4/3*(M_SQRT2-1)*$rx;
	$ly=4/3*(M_SQRT2-1)*$ry;
	$k=$this->k;
	$h=$this->h;
	$this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c', ($x+$rx)*$k,($h-$y)*$k, ($x+$rx)*$k,($h-($y-$ly))*$k, ($x+$lx)*$k,($h-($y-$ry))*$k, $x*$k,($h-($y-$ry))*$k));
	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c', ($x-$lx)*$k,($h-($y-$ry))*$k, 	($x-$rx)*$k,($h-($y-$ly))*$k, 	($x-$rx)*$k,($h-$y)*$k)); 
	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c', ($x-$rx)*$k,($h-($y+$ly))*$k, ($x-$lx)*$k,($h-($y+$ry))*$k, $x*$k,($h-($y+$ry))*$k)); 
	$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s', ($x+$lx)*$k,($h-($y+$ry))*$k, ($x+$rx)*$k,($h-($y+$ly))*$k, ($x+$rx)*$k,($h-$y)*$k, $op));
}

// Added adaptation of shaded_box = AUTOSIZE-TEXT
// Label and number of invoice/estimate
function AutosizeText($text,$w,$font,$style,$szfont=72) {
	$text = $this->purify_utf8_text($text);
	if ($this->text_input_as_HTML) {
		$text = $this->all_entities_to_utf8($text);
	}
	if (!$this->isunicode || $this->isCJK) { $text = mb_convert_encoding($text,$this->mb_encoding,'UTF-8'); }
	$text = ' '.$text.' ';
	$width = ConvertSize($w);
	$loop   = 0;
	while ( $loop == 0 ) {
		$this->SetFont($font,$style,$szfont);
		$sz = $this->GetStringWidth( $text );
		if ( $sz > $w ) { $szfont --; }
		else { $loop ++; }
	}
 	$this->SetFont($font,$style,$szfont);
	$this->Cell($w, 0, $text, 0, 0, "C");
}





// ====================================================
// ====================================================
function reverse_letters($str) {
	return mb_strrev($str, $this->mb_encoding); 
}

function magic_reverse_dir(&$chunk) {
   if (!$this->isunicode || $this->isCJK) { return 0; }
   if (($this->directionality == 'rtl') || (($this->directionality == 'ltr') && ($this->BiDirectional)))  { 
	$contains_rtl = false;
	$all_rtl = true;
	if (preg_match("/".$this->pregRTLchars."/u",$chunk)) {	// Chunk contains RTL characters
		if (preg_match("/".$this->pregNonLTRchars."/u",$chunk)) {	// Chunk also contains LTR characters
			$all_rtl = false;
			$bits = preg_split('/[ ]/u',$chunk);
			foreach($bits AS $bitkey=>$bit) {
				if (preg_match("/".$this->pregRTLchars."/u",$bit)) {	// Chunk also contains LTR characters
					$bits[$bitkey] = $this->reverse_letters($bit); 
				}
				else { 
					$bits[$bitkey] = $bit; 
				}
			}
			$bits = array_reverse($bits,false);
			$chunk = implode(' ',$bits);
		}
		else {
			$chunk = $this->reverse_letters($chunk); 
		}
		$contains_rtl = true;
	}
	else { $all_rtl = false; }
	if ($all_rtl) { return 2; }
	else if ($contains_rtl) { return 1; }
	else { return 0; }
   }
   return 0;
}

//****************************//
//****************************//
//****************************//

var $subsearch = array();	// Array of search expressions to substitute characters
var $substitute = array();	// Array of substitution strings e.g. <ttz>112</ttz>
var $entsearch = array();	// Array of HTML entities (>ASCII 127) to substitute
var $entsubstitute = array();	// Array of substitution decimal unicode for the Hi entities


function setSubstitutions($subsarr) {
   if (count($subsarr) == 0) {
	$this->subsearch = array();
	$this->substitute = array();
   }
   else {
	foreach($subsarr AS $key => $val) {
		//$this->subsearch[] = '/'.preg_quote(code2utf($key),'/').'/u';
		//$this->substitute[] = $val;
		$this->substitute[code2utf($key)] = $val;
	}
    }
}


function SubstituteChars($html) {
	// only substitute characters between tags
	if (count($this->substitute)) {	// set in includes/pdf/config.php for VIEW, publish.php for PUBLISH
		$a=preg_split('/(<.*?>)/ums',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		// ? more accurate regexp that allows e.g. <a name="Silly <name>">
		//	$a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ums', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		$html = '';
		foreach($a as $i => $e) {
			if($i%2==0) {
			   //TEXT
			   //$e = preg_replace($this->subsearch, $this->substitute, $e);
			   $e = strtr($e, $this->substitute);
			}
			$html .= $e;
		}
	}
	return $html;
}


function setHiEntitySubstitutions($entarr) {
   if (count($entarr) == 0) {
	$this->entsearch = array();
	$this->entsubstitute = array();
   }
   else {
	foreach($entarr AS $key => $val) {
		$this->entsearch[] = '&'.$key.';';
		$this->entsubstitute[] = code2utf($val);
	}
    }
}

function SubstituteHiEntities($html) {
	// converts html_entities > ASCII 127 to unicode (defined in includes/pdf/config.php
	// Leaves in particular &lt; to distinguish from tag marker
	if (count($this->entsearch)) {
		$html = str_replace($this->entsearch,$this->entsubstitute,$html);
	}
	return $html;
}


// Edited v1.2 Pass by reference; option to continue if invalid UTF-8 chars
function is_utf8(&$string) {
	if ($string === mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
		return true;
	} 
	else {
	  if ($this->ignore_invalid_utf8) {
		$string = mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32") ;
		return true;
	  }
	  else {
		return false;
	  }
	}
} 


function purify_utf8($html,$lo=true) {
	// For HTML
	// Checks string is valid UTF-8 encoded
	// converts html_entities > ASCII 127 to UTF-8
	// Leaves in particular &lt; to distinguish from tag marker
	// Only exception - leaves low ASCII entities e.g. &lt; &amp; etc.
	if (!$this->is_utf8($html)) { $this->Error("HTML contains invalid UTF-8 character(s)"); }
	$html = preg_replace("/\r/u", "", $html );

	// converts html_entities > ASCII 127 to UTF-8 
	// Leaves in particular &lt; to distinguish from tag marker
	$html = $this->SubstituteHiEntities($html);

	// converts all &#nnn; or &#xHHH; to UTF-8 multibyte
	// If $lo==true then includes ASCII < 128
	$html = strcode2utf($html,$lo);	

	// NON-BREAKING SPACE - convert to space as doesn't exist in CJK codepages
	if ($this->isCJK) {
		$html = preg_replace("/\xc2\xa0/"," ",$html);	// non-breaking space
	}

	return ($html);
}

function purify_utf8_text($txt) {
	// For TEXT
	// Make sure UTF-8 string of characters
	if (!$this->is_utf8($txt)) { $this->Error("Text contains invalid UTF-8 character(s)"); }

	$txt = preg_replace("/\r/u", "", $txt );

	// NON-BREAKING SPACE - convert to space as doesn't exist in CJK codepages
	if ($this->isCJK) {
		$txt = preg_replace("/\xc2\xa0/"," ",$txt);	// non-breaking space
	}

	return ($txt);
}
function all_entities_to_utf8($txt) {
	// converts txt_entities > ASCII 127 to UTF-8 
	// Leaves in particular &lt; to distinguish from tag marker
	$txt = $this->SubstituteHiEntities($txt);

	// converts all &#nnn; or &#xHHH; to UTF-8 multibyte
	$txt = strcode2utf($txt);	


	$txt = lesser_entity_decode($txt);
	return ($txt);
}

// ====================================================
// ====================================================




}//end of Class



?>