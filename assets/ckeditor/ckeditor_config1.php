<?php
/*
$config = '{
	"enterMode":"CKEDITOR.ENTER_BR",
	"height":"250",
	"width":"650",
	"scrollbars":"yes",
	"toolbar_Full":[	
						
						["Source","Templates"],
						["Cut","Copy","Paste","PasteText","PasteFromWord","-","Print","SpellChecker","Scayt"],
						["Form","Checkbox","Radio","TextField","Textarea","Select","Button","ImageButton","HiddenField"],
						["Find","Replace"],"/",
						
						
						["Undo","Redo"],
						["Bold","Italic","Underline","Strike","-","Subscript","Superscript"],
						["NumberedList","BulletedList","-","Outdent","Indent","Blockquote","CreateDiv"],
						["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"],
						["BidiLtr","BidiRtl"],
						["SelectAll","RemoveFormat"],"/",
						
						
						["Link","Unlink","Anchor"],
						["Styles","Format","Font","FontSize"],
						["TextColor","BGColor"],
						["Maximize","ShowBlocks"],"/",
						
						
						["Image","Flash","Table","HorizontalRule","Smiley"],
						["SpecialChar","PageBreak"]
					]		
}';

echo '<pre>';
print_r(json_decode($config, true));
echo '</pre>';

*/
$config = array(
	'enterMode' => 'CKEDITOR.ENTER_BR', 
	'height' => '250', 
	'width' => '650', 
	'scrollbars' => 'yes', 
	'toolbar_Full' => array(
		array(
			'Source', 
			'Templates', 
		),
		array(
			'Cut', 
			'Copy', 
			'Paste', 
			'PasteText', 
			'PasteFromWord', 
			'-', 
			'Print', 
			'SpellChecker', 
			'Scayt', 
		),
		array
			(
			'Form', 
			'Checkbox', 
			'Radio', 
			'TextField', 
			'Textarea', 
			'Select', 
			'Button', 
			'ImageButton', 
			'HiddenField', 
		),

		array
			(
				'Find', 
			   'Replace', 
		   ),

		'/', 
	   array
			(
				'Undo', 
			   'Redo', 
		   ),

		array
			(
				'Bold', 
			   'Italic', 
			   'Underline', 
			   'Strike', 
			   '-', 
			   'Subscript', 
			   'Superscript', 
		   ),

		array
			(
				'NumberedList', 
			   'BulletedList', 
			   '-', 
			   'Outdent', 
			   'Indent', 
			   'Blockquote', 
			   'CreateDiv', 
		   ),

		array
			(
				'JustifyLeft', 
			   'JustifyCenter', 
			   'JustifyRight', 
			   'JustifyBlock', 
		   ),

		array
			(
				'BidiLtr', 
			   'BidiRtl', 
		   ),

		array
			(
				'SelectAll', 
			   'RemoveFormat', 
		   ),

		'/', 
	   array
			(
				'Link', 
			   'Unlink', 
			   'Anchor', 
		   ),

		array
			(
				'Styles', 
			   'Format', 
			   'Font', 
			   'FontSize', 
		   ),

		array
			(
				'TextColor', 
			   'BGColor', 
		   ),

		array
			(
				'Maximize', 
			   'ShowBlocks', 
		   ),

		'/', 
	   array
			(
				'Image', 
			   'Flash', 
			   'Table', 
			   'HorizontalRule',
			   'Smiley', 
		   ),

		array(
			'SpecialChar', 
			'PageBreak', 
		),
	),
);
//echo json_encode($config);
?>