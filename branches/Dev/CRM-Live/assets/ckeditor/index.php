<?php 
include_once 'ckeditor_config1.php';
//echo stripslashes(json_encode($config));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<script type="text/javascript" src="scripts/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="scripts/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="scripts/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.validate.js"></script>
<script type="text/javascript" src="scripts/jquery.form.js"></script>
<script type="text/javascript">
$(function(){

	$('.editor').ckeditor(<?php stripslashes(json_encode($config));?>);

	$('#form_ck').validate({
		ignore: '',
		rules: { 
			title : { required : true},
			page_contents : { 
				required : function (textarea){
					CKEDITOR.instances[textarea.id].updateElement(); // update textarea
					var editorcontent = textarea.value.replace(/<[^>]*>/gi, ''); // strip tags
					return editorcontent.length === 0;
				},
			}
		},
	});
});

</script>

</head>
<body>
	<form id='form_ck'>
	Title : <input type='text' name='title' id='title'/> <br/>
	Page Content : <textarea name="page_contents" cols="50" rows="20" id="page_contents" class="editor"></textarea>

	<br/>
	<input type='submit'>
	</form>
</body>
</html>