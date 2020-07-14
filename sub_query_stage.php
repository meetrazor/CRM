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
    'data-tables/js/fnStandingRedraw',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min'
);
include_once 'header.php';
?>
<style type="text/css">
    table#dg_sub_query_stage tfoot {
        display: table-header-group;
    }
</style>
<script type="text/javascript">
    $(function() {
        $('[data-rel=tooltip]').tooltip();
        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_sub_query_stage = $('#dg_sub_query_stage').dataTable({
            "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
            "bPaginate":true,
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
            "bScrollCollapse" : true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/sub_query_stage.php",//change here

            "fnServerParams": function ( aoData ) { //send other data to server side
                aoData.push({ "name": "act", "value": "fetch" });
                server_params = aoData;
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData: "sub_query_stage_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" name="sub_query_stage_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData": "loan_type_name" },
                { "mData": "category_name" },
                { "mData": "reason_name" },
                { "mData": "query_stage_name" },
                { "mData": "sub_query_stage_name" },
                { "mData": "sub_query_stage_description" },
                {
                    "mData": "is_active",
                    mRender: function(v,t,o){
                        return (v == 1) ? "Active" : "Inactive";
                    }
                },
                {
                    "mData": "is_default",
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
                {
                    mData: null,
                    bSortable:false,
                    mRender: function(v,t,o){
                        var act_html = "";

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Sub Query Stage', 'Edit Sub Query Stage')))   {
                        ?>
                        act_html = act_html + "<a href='sub_query_stage_addedit.php?id="+ o['sub_query_stage_id'] +"&reason_id="+ o['reason_id'] +"&loan_type_id="+ o['loan_type_id'] +"&category_id="+ o['category_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['sub_query_stage_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Sub Query Stage', 'Delete Sub Query Stage')))   {
                        ?>
                        act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['sub_query_stage_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
                        <?php } ?>
                        return act_html;
                    }
                }
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


        $('#edit_record').click(function (e) {
            var selected_list = $('#dg_sub_query_stage tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if (0 == selected_length) {

                showGritter('info', 'Alert!', 'Please select a sub query stage to edit.');
                return false;
            } else if (selected_length > 1) {
                showGritter('info', 'Alert!', 'Only single record can be edited at a time.');
                return false;
            }

            href = $('#edit_record').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_record').attr('href', href);
            return true;
        });


        $('#delete_query_stage').click(function(){

            var delete_ele = $('#dg_sub_query_stage tbody input[type=checkbox]:checked');
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
                            url: 'control/sub_query_stage.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_sub_query_stage.fnStandingRedraw();
                                if(resp.success) {
                                    showGritter('success',resp.title,resp.msg);
                                } else {
                                    showGritter('error',resp.title,resp.msg);
                                }
                            }
                        });
                    }
                });
            }
        });

    });

    function EditRecord(id){
        $('#chk_'+id).prop('checked', true);
        $('#edit_query_stage').click();
    }

    function DeleteRecord(fid){
        $('#chk_'+fid).prop('checked', true);
        $('#delete_query_stage').click();
    }
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Sub Query Stage List
                    <span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Add Query Stage')))   {
                            ?>
                            <a id='add_query_stage' href="sub_query_stage_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Edit Query Stage')))   {
                            ?>
                            <a id='edit_record' href="sub_query_stage_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Delete Query Stage')))   {
                            ?>
                            <a id='delete_query_stage' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_sub_query_stage' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Loan Type</th>
                        <th>Product Type</th>
                        <th>Reason</th>
                        <th>Query Stage</th>
                        <th>Sub Query Stage</th>
                        <th>Sub Query Stage Description</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th>Actions</th>
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
include_once 'footer.php';
?>
