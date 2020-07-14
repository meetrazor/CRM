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
		'js/jquery.gritter.min',
		'js/bootbox.min'
);
include_once "header.php";
?>
    <style type="text/css">
        table#dg_user_permission tfoot {
            display: table-header-group;
        }
    </style>
<script type="text/javascript">
$(document).ready(function() {		

	var breakpointDefinition = {
			pc: 1280,
	        tablet: 1024,
	        phone : 480
	};
	var responsiveHelper2 = undefined;
	dg_user_permission = $('#dg_user_permission').dataTable({
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
        "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
	    "sAjaxSource": 'control/user_permission_master.php?act=fetch',
	    "fnServerParams": function ( aoData ) {
	    	aoData.push({ "name": "act", "value": "fetch" });
		},
		"aaSorting": [[ 1, "asc" ]],
		"aoColumns": [
			{ 
				mData: "user_id",
				bSortable : false,
				mRender: function (v, t, o) {
					return '<label><input type="checkbox" value="'+v+'"/><span class="lbl"></span></label>';
				},
				sClass: 'center',
			},
			{
                "mData": "name",
                "sWidth":"20%"
            },
            {
                "mData": "role_name",
                "sWidth":"15%"
            },
			{
                "mData": "email",
                "sWidth":"25%"
            },
			{ 
				"mData": null,
                "bSortable":false,
				mRender : function ( v, t, o){
					var p = '';
                    <?php
                      if($acl->IsAllowed($login_id,'USERS', 'User Permission', 'Edit User Permission')){
                      ?>
					p = '<a href="user_edit_permission.php?id='+o['user_id']+'&token=<?php echo $token; ?>"> Edit Permission</a> &nbsp;&nbsp;';
					<?php } ?>
                    <?php
                      if($acl->IsAllowed($login_id,'USERS', 'User Permission', 'Delete User Permission')){
                      ?>
					p += '<a href="javascript:void(0)" onclick="DeleteRow('+o['user_id']+')">Remove Permission</a>';
					<?php } ?>
					return p;
				}
			 },
		],
		fnPreDrawCallback: function () {
			if (!responsiveHelper2) {
				responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
			}
		},
        "fnRowCallback" : function(nRow, aData, iDisplayIndex){
            responsiveHelper2.createExpandIcon(nRow);
            return nRow;
        },
        fnDrawCallback : function (oSettings) {
        	responsiveHelper2.respond();
        	$(this).removeAttr('style');
        	$('[data-rel=tooltip]').tooltip();
        }
	});

    $("tfoot input").keyup( function () {
        dg_user_permission.fnFilter( this.value, $(this).attr("colPos") );
    });


    $('#delete_record').click(function(){
		
		var delete_ele = $('#dg_user_permission tbody input[type=checkbox]:checked');
		var selected_length = delete_ele.size();
		
		if(0 == selected_length){
			showGritter('info','Alert!',"Please select a user to delete user's permission.");
			return false;
		}else{
			bootbox.confirm("Are you sure to delete selected user's permissions?", function(result) {
				if(result) {
					
					var delete_id = [];
					$.each(delete_ele, function(i, ele){
						delete_id.push($(ele).val());
					});
					
					$.ajax({
						url: 'control/user_permission_master.php?act=delete',
						type:'post',
						dataType:'json',
						data:{ id : delete_id, },
						success: function(resp){
							dg_user_permission.fnDraw();
							showGritter('success',resp.title,resp.msg);
						}
					});
				}
			});	
		}
	});
});

function DeleteRow(val){

	$('input[type=checkbox][value='+val+']').prop('checked', true);
	$('#delete_record').click();
}
</script>
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class='span12'>
				<div class="table-header">
					User List
					<span class="widget-toolbar pull-right">
                    <?php
                    if($acl->IsAllowed($login_id,'USERS', 'User Permission', 'Add User Permission')){
                        ?>
						<a class='white' id='add_record' href="user_add_permission.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'USERS', 'User Permission', 'Edit User Permission')){
                        ?>
						<a class='white' id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>	
				</div>
				<table id='dg_user_permission' class="table table-condensed table-bordered table-hover">
					<thead>
						<tr>
							<th class="center" width="5%">
								<label>
									<input type="checkbox"/>
									<span class="lbl"></span>
								</label>
							</th>
							<th>Name</th>
							<th>Role</th>
							<th>Email</th>
							<th>Action</th>
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