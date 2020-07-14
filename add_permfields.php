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
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min'
);


include_once 'header.php';

?>
<script type="text/javascript">

var vRules = {
	section_name:{required:true},
	page_name:{required:true},
	realpage_name:{required:true},
	permission_label:{required:true},
	permission_desc:{required:true}
};

var vMessages = {
	section_name:{required:"Please select section name."},
	page_name:{required:"Please enter page name."},
	realpage_name:{required:"Please enter real file name."},
	permission_label:{required:"Please enter permission label."},
	permission_desc:{required:"Please enter permission description."}
};

$(document).ready(function() {
	
	if (jQuery().validate) {
		var e = function(e) {
			$(e).closest(".control-group").removeClass("success");
		};
		$('#title_form').validate({
			
			rules: vRules,
			messages: vMessages,
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
			submitHandler: function(form) {
				act = 'add';
				$(form).ajaxSubmit({
					url: 'control/add_permfields.php?act='+act,
					beforeSubmit: function (formData, jqForm, options) {						
						$(form).find('button').hide();
					},
					dataType: 'json',
					clearForm: false,
					success: function (resp, statusText) {
						
						if(resp.success){
							$(form).clearForm();
							dg_table.fnDraw();
							showGritter('success',resp.title,resp.msg);
							$(form).find('button').show();
                            $("#section_name").focus();
						}
					}
				});
			}
		});
	}

	var breakpointDefinition = {
			pc: 1280,
	        tablet: 1024,
	        phone : 480
	};
	
	var responsiveHelper2 = undefined;
	dg_table = $('#dg_table').dataTable({
		//"sScrollY": "200px",
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
				sNext: '<i class="icon-angle-right"></i>',
			}
		},
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "control/add_permfields.php",
	    "fnServerParams": function ( aoData ) {
	    	aoData.push({ "name": "act", "value": "fetch" });
		},
		"aaSorting": [[ 1, "asc" ]],
		"aoColumns": [
            {
                mData: "perm_id",
                bSortable : false,
                mRender: function (v, t, o) {
                    return '<label><input type="checkbox" id="chk_'+v+'" name="perm_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                },
                sClass: 'center',
            },
			{ "mData": "section_name" },
			{ "mData": "page_name" },
			{ "mData": "realpage_name" },
			{ "mData": "permission_label" },
			{ "mData": "permission_desc" },
			{ "mData": "display_order" },
			{ "mData": "rcd_updated_on" },
			{ "mData": "rcd_updated_by" },
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

    $('#delete').click(function(){

        var delete_ele = $('#dg_table tbody input[type=checkbox]:checked');
        var selected_length = delete_ele.size();

        if(0 == selected_length){
            showGritter('info','Alert!','Please select record to delete.');
            return false;
        }else{
            bootbox.confirm("Are you sure to delete selected record(s)?", function(result) {
                if(result) {

                    var delete_id = [];
                    $.each(delete_ele, function(i, ele){
                        delete_id.push($(ele).val());
                    });

                    $.ajax({
                        url: 'control/add_permfields.php?act=delete',
                        type:'post',
                        dataType:'json',
                        data:{ id : delete_id, },
                        success: function(resp){
                            dg_table.fnDraw();
                            var files = '';
                            showGritter('success',files,resp.msg);
                        }
                    });
                }
            });
        }
    });
});

function DeleteRecord(rid){

    $('#chk_'+rid).prop('checked', true);
    $('#delete').click();
}
</script>
<div class='row-fluid'>
	<div class="span12 widget-container-span ui-sortable">
		<div class="widget-box">
			<div class="widget-header">
				<h5>Add Permission</h5>
				<div class="widget-toolbar">
					<a data-action="collapse" href="#">
						<i class="icon-chevron-up"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="widget-main">
					<form class="form-horizontal" method="post" id="title_form">
						<div class='row-fluid'>
							<div class='span5'>
								<div class="control-group">
									<label class="control-label" for="section_name">Section Name</label>
									<div class="controls">
										<select name="section_name" id="section_name">
										  <option value="">Select section name</option>
										  <?php
										  $enumvals = $db->GetEnumvalues("user_perm_master","section_name");
					                      foreach($enumvals as $enumval) {
					                      ?>
					                      <option value="<?php echo $enumval; ?>" ><?php echo $enumval; ?></option>
					                      <?php 
										  }
										  ?>
					                    </select>
									</div>
								</div>
							</div>
							
							<div class="span5">
								<div class="control-group">
									<label class="control-label" for="page_name">Page Name</label>
									<div class="controls">
										<input type="text" name="page_name" id="page_name" placeholder="Page Name" value =""/>
									</div>
								</div>
							</div>
						</div>
						<div class='row-fluid'>
							<div class="span5">
								<div class="control-group">
									<label class="control-label" for="realpage_name">Real File Name</label>
									<div class="controls">
										<input type="text" name="realpage_name" id="realpage_name" placeholder="Real File Name" value =""/>
									</div>
								</div>
							</div>
                            <div class="span5">
                                <div class="control-group">
                                    <label class="control-label" for="permission_label">Permission Label</label>
                                    <div class="controls">
                                        <input type="text" name="permission_label" id="permission_label" placeholder="Permission Label" value =""/>
                                    </div>
                                </div>
                            </div>
						</div>
						<div class='row-fluid'>
<!--							<div class="span5">-->
<!--								<div class="control-group">-->
<!--									<label class="control-label" for="display_order"> Display Order</label>-->
<!--									<div class="controls">-->
<!--										<select name="display_order" id="display_order" >-->
<!--				                        	<option value="">Select display order</option>-->
<!--				                            --><?php //for($i=1; $i<20; $i++){?>
<!--				                            <option value="--><?php //echo $i;?><!--">--><?php //echo $i;?><!--</option>-->
<!--				                            --><?php //}?>
<!--										</select>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->

                            <div class="span5">
                                <div class="control-group">
                                    <label class="control-label" for="section_name">Permission Description</label>
                                    <div class="controls">
                                        <textarea name="permission_desc" id="permission_desc"></textarea>
                                    </div>
                                </div>
                            </div>
						</div>
						<div class='row-fluid'>
							<div class="form-actions">
								<button type="submit" class="btn btn-primary">
									<i class="icon-ok bigger-110"></i>Submit
								</button>
								<button id='btn_cancel' type="button" class="btn">
									<i class="icon-undo bigger-110"></i>Reset
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class='span12'>
				<div class="table-header">
                    Permission List
                    	<span class="widget-toolbar pull-right">
                            <a id='delete' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete"><i class="icon-trash icon-large white"></i></a>
					</span>
                </div>
				<table id='dg_table' class="table table-condensed table-bordered table-hover">
					<thead>
						<tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
							<th>Section Name</th>
							<th>Page Name</th>
							<th>Realpage Name</th>
							<th>Permission Label</th>
							<th>Permission Desc</th>
							<th>Display Order</th>
							<th>Updated On</th>
							<th>Updated By</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
include_once "footer.php";
?>