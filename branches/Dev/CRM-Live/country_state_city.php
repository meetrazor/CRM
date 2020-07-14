<?php
$asset_css = array(
		'css/jquery.gritter',
		'data-tables/responsive/css/datatables.responsive',
);

$asset_js = array(
		'js/lodash/lodash.min',
		'data-tables/js/jquery.dataTables.min',
		'data-tables/js/DT_bootstrap',
		'data-tables/responsive/js/datatables.responsive',
		'js/jquery-validation/dist/jquery.validate.min',
		'js/jquery-form/jquery.form',
		'js/jquery.gritter.min',
		'js/bootbox.min'
);

include_once 'header.php';
$countryDd = $db->CreateOptions("html","country",array('country_name','country_name'),null,array("country_name" => "ASC"));
$stateDd = $db->CreateOptions("html","state",array('state_name','state_name'),null,array("state_name" => "ASC"));
$tierDd = $db->CreateOptions("html","tier_master",array('tier_name','tier_name'),null,array("tier_name" => "ASC"));
?>
    <style type="text/css">
        table#dg_city tfoot {
            display: table-header-group;
        }
    </style>
<script type="text/javascript">
$(function() {
    $('#country_name').change(function(){
        var country_id = $(this).val();
        $.ajax({
            url: 'control/country_state_city.php?act=getstate', data : { country_id : country_id },type:'post',
            beforeSend: function(){
                $('#state_loader').show();
            },
            complete: function(){
                $('#state_loader').hide();
            },
            success: function(resp){
                $('#state_name').html(resp);
                $("#state_name").trigger("liszt:updated");
            }
        });
    });

	$('#csc_execl').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false,
        whitelist:'xlsx|xls'
    }).on('change', function(){
        data = console.log($(this).data('ace_input_files'));
    });

	$('#form_upload_file').ajaxForm({
		url: 'control/upload_country_state_city.php',
		type:'post',
		dataType: 'json',
		beforeSubmit: function (formData, jqForm, options) {						
			$('#form_upload_file button').hide();
			$('#loader').show();
		},
		success: function(response) {

			g_class = '';
			if(response.status === true){
				g_class = 'success';
				g_title = 'Successful';
			}else{
				g_class = 'error';
				g_title = 'Failed';
			}
			showGritter(g_class,g_title, response.msg);
			$('#csc_execl').ace_file_input('reset_input');
			dg_city.fnDraw();
		},
		complete: function(){
			$('#form_upload_file button').show();
			$('#loader').hide();
		}
	});

	$('[data-rel=tooltip]').tooltip();
	
	var breakpointDefinition = {
	        tablet: 1024,
	        phone : 480
	};
	var responsiveHelper2 = undefined;
	dg_city = $('#dg_city').dataTable({
        "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
		oLanguage : {
			sSearch : "Search _INPUT_",
			sLengthMenu : " _MENU_ ",
			sInfo : "_START_ to _END_ of _TOTAL_",
			sInfoEmpty : "0 - 0 of 0",
			oPaginate : {
				sFirst : '<i class="icon-double-angle-left"></i>',
				sLast : '<i class="icon-double-angle-right"></i>',
				sPrevious: '<i class="icon-angle-left"></i>',
				sNext: '<i class="icon-angle-right"></i>'
			}
		},
	    "bProcessing": true,
	    "bServerSide": true,
        "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
	    "sAjaxSource": "control/country_state_city.php",
	    "fnServerParams": function ( aoData ) {
	    	aoData.push({ "name": "act", "value": "fetch" });
		},
		"aaSorting": [[ 1, "asc" ],[ 2, "asc" ],[ 3, "asc" ]],
		"aoColumns": [
			{ 
				mData: "city_id",
				bSortable : false,
				mRender: function (v, t, o) {
					return '<label><input type="checkbox" id="city_chk_'+v+'" name="city_ids[]" value="'+v+'"/><span class="lbl"></span></label>';
				},
				sClass: 'center'
			},
			{ "mData": "country_name" },
			{ "mData": "state_name" },
			{ "mData": "tier_name" },
			{ "mData": "city_name" },
			{ "mData": "state_id", bVisible: false },
			{ "mData": "country_id", bVisible: false }
		],
		fnPreDrawCallback: function () {
			if (!responsiveHelper2) {
				responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
			}
		}, 
        fnRowCallback  : function (nRow) {
        	responsiveHelper2.createExpandIcon(nRow);
        },
        fnDrawCallback : function (oSettings) {
        	responsiveHelper2.respond();
        	$(this).removeAttr('style');
        }
	});

    $("tfoot input").keyup( function () {
        dg_city.fnFilter( this.value, $(this).attr("colPos") );
    });

	if (jQuery().validate) {
		var e = function(e) {
			$(e).closest(".control-group").removeClass("success");
		};
		// Company type validateion code  
		$("#frm_city").validate({
			rules:{
				country_name:{required: true},
				state_name:{required: true},
				city_name:{required: true},
				tier_name:{required: true}
			},
			messages:{
				country_name:{required: 'Country name is required'},
				state_name:{required: 'State name is required'},
				city_name:{required: 'City name is required'},
                tier_name:{required: 'Tier name is required'}
			},
			errorElement : "span",
			errorClass : "help-inline",
			focusInvalid : false,
			ignore : "",
			invalidHandler : function(e, t) {},
			highlight : function(e) {
				$(e).closest(".control-group").removeClass("success").addClass("error");
			},
			unhighlight : function(t) {
				$(t).closest(".control-group").removeClass("error");
					setTimeout(function() {
						e(t);
					}, 3e3);
			},
			success : function(e) {
				e.closest(".control-group").removeClass("error").addClass("success");
			},
			submitHandler : function(e) {
				
				$(e).ajaxSubmit({
					url: 'control/country_state_city.php?act=addedit',
					type:'post',
                    beforeSubmit: function (formData, jqForm, options) {
                        $(e).find('button').hide();
                        $('#loader').show();
                    },
                    complete: function(){
                        $('#loader').hide();
                        $(e).find('button').show();
                    },
					dataType: 'json',
					clearForm: false,
					success: function (resObj, statusText) {
						$(e).find('button').show();
						if(resObj.success == 'true'){
							$(e).clearForm();
							$('#modal_add_city').modal('hide');
							dg_city.fnDraw();
							showGritter('success',resObj.title,resObj.msg);
						}else if(resObj.success == 'exist'){
							showGritter('info', resObj.title, resObj.msg);
						}
					}
				});
			}
		});
	}
	$('#add_city').click(function(){
		$('form#frm_city input,select').val('');
		$('form#frm_city').find('div.control-group').removeClass("success error");
		$('form#frm_city').find('div.control-group span').text('');
		$('#act_city').text('Add');
		$('#modal_add_city').modal('show');
	});

	$('#edit_city').click( function (e) {
		var selected_list = $('#dg_city tbody input[type=checkbox]:checked');
		var selected_length = selected_list.size();
		
		if(0 == selected_length){
			
			showGritter('info','Alert!','Please select city to edit.');
			return false;
		}else if(selected_length > 1){
			showGritter('info','Alert!','Only single record can be edited at a time.');
			return false;
		}

		var selected_tr = selected_list[0];
		var ele = $(selected_tr).closest('tr').get(0);
		//console.log(ele);
		var aData = dg_city.fnGetData( ele );

		$.each(aData, function(key,val){
			if($('form#frm_city #'+key).length){
				$('form#frm_city #'+key).val(val);
			}
		});
		$('#act_city').text('Edit');
		$('form#frm_city').find('div.control-group').removeClass("success error");
		$('form#frm_city').find('div.control-group span').text('');
		$('#modal_add_city').modal('show');
	});
	
	$('#delete_city').click(function(){
		
		var delete_ele = $('#dg_city tbody input[type=checkbox]:checked');
		var selected_length = delete_ele.size();
		
		if(0 == selected_length){
			showGritter('info','Alert!','Please select city to delete.');
			return false;
		}else{
			bootbox.confirm("Are you sure to delete selected city(s)?", function(result) {
				if(result) {
					
					var delete_id = [];
					$.each(delete_ele, function(i, ele){
						delete_id.push($(ele).val());
					});
					
					$.ajax({
						url: 'control/country_state_city.php?act=delete',
						type:'post',
						dataType:'json',
						data:{ id : delete_id },
						success: function(resp){
							dg_city.fnDraw();
							showGritter('success',resp.title,resp.msg);
						}
					});
				}
			});	
		}
	});
});
</script>
<div class="row-fluid hide">
	<div class="span12">
		<div class="box">
			<div class="box-content">
				<form class="form-horizontal" id="form_upload_file" enctype="multipart/form-data">
					<div class="span2">
						<a class="btn btn-mini btn-primary" href="upload_format/country_state_city.xlsx">
							<i class="icon-download"></i>Upload Format
						</a>
					</div>
					<div class="control-group">
						<label class="control-label" for="country_state_city">Upload File:</label>
						<div class="controls">
							<div class="span4">
								<input multiple="" type="file" name="csc_execl" id="csc_execl" required='true'/>
<!-- 								<input type="submit" value="Upload File"/> -->
								
							</div>
							<div class="span2">
								<button class="btn btn-mini btn-primary" type="submit">
									<i class="icon-arrow-up"></i>Upload File
								</button>
								
							</div>
							<div id='loader_bulk'  class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please wait...</div>
							<div class="clearfix"></div> 
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class='span12'>
				<div class="table-header">
					Country State City
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Country State City', 'Add Country State City')))   {
                        ?>
						<a id='add_city' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Country State City', 'Edit Country State City')))   {
                        ?>
						<a id='edit_city' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
						<?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Country State City', 'Delete Country State City')))   {
                        ?>
                        <a id='delete_city' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>	
				</div>
				<table id='dg_city' class="table table-condensed table-bordered table-hover">
					<thead>
						<tr>
							<th class="center" width="5%">
								<label>
									<input type="checkbox" id='dg_city_chk_master'/>
									<span class="lbl"></span>
								</label>
							</th>
							<th data-hide='phone'>Country</th>
							<th data-hide='phone'>State</th>
							<th data-hide='phone'>Tier</th>
							<th data-class="expand">City</th>
						</tr>
					</thead>
                    <tfoot>
                    <tr>
                        <th class="center">
                        </th>
                        <th>
                            <input type="text"  placeholder="country" name="country" class="span12" colPos="1">
                        </th>
                        <th>
                            <input type="text"  placeholder="state" name="state" class="span10" colPos="2">
                        </th>
                        <th>
                            <input type="text"  placeholder="tier" name="tier" class="Tier" colPos="3">
                        </th>
                        <th>
                            <input type="text"  placeholder="city" name="city" class="City" colPos="4">
                        </th>
                        <th></th>
                    </tr>
                    </tfoot>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div id="modal_add_city" class="modal hide" tabindex="-1">
	<form id='frm_city' class="form-horizontal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="blue bigger"><span id='act_city'>Add</span> City</h4>
	</div>
	<div class="modal-body overflow-auto">
		<div class="row-fluid">
			<div class="span12">
				<div class="control-group">
					<label class="control-label" for="country_name">Country</label>
					<div class="controls">
<!--						<input type="text" name="country_name" id="country_name" placeholder="Country"/>-->
                       <select name="country_name" id="country_name">
                            <option></option>
                            <?php echo $countryDd; ?>
                        </select>
						<input type="hidden" name="country_id" id="country_id"/>
					</div>
				</div>
                <?php /*
				<div class="control-group">
					<label class="control-label" for="country_name">State</label>
					<div class="controls">
						<input type="text" name="state_name" id="state_name" placeholder="State">
                        <i id='state_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
						<input type="hidden" name="state_id" id="state_id"/>
					</div>
				</div>
                */ ?>
                <div class="control-group">
                    <label class="control-label" for="state_name">State</label>
                    <div class="controls">
                        <select name="state_name" id="state_name">
                            <option></option>
                            <?php echo $stateDd; ?>
                        </select>
                        <input type="hidden" name="state_id" id="state_id"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="tier_name">Tier</label>
                    <div class="controls">
                        <select name="tier_name" id="tier_name">
                            <option></option>
                            <?php echo $tierDd; ?>
                        </select>
                        <input type="hidden" name="tier_id" id="tier_id"/>
                    </div>
                </div>
				<div class="control-group">
					<label class="control-label" for="country_name">City</label>
					<div class="controls">
						<input type="text" name="city_name" id="city_name" placeholder="City"/>
						<input type="hidden" name="city_id" id="city_id"/>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<button type="submit" class="btn btn-small btn-primary">
			<i class="icon-ok"></i>
			Save
		</button>
		<button class="btn btn-small" data-dismiss="modal">
			<i class="icon-remove"></i>
			Cancel
		</button>
        <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
            wait...
        </div>
	</div>
	</form>
</div>
<?php 
include_once 'footer.php';
?>