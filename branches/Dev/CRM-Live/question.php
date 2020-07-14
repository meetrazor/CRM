<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/13/2019
 * Time: 7:08 PM
 */

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
    table#dg_question tfoot {
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
        dg_question = $('#dg_question').dataTable({
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
            "sAjaxSource": "control/question.php",//change here

            "fnServerParams": function ( aoData ) { //send other data to server side
                aoData.push({ "name": "act", "value": "fetch" });
                server_params = aoData;
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData: "question_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" name="question_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData": "question_for" },
                { "mData": "question_name" },
                { "mData": "question_short_name" },
                { "mData": "option_type" },
                { "mData": "option_value" },
                { "mData": "sort_order" },
                {
                    "mData": "is_active",
                    mRender: function(v,t,o){
                        return (v == 1) ? "Active" : "Inactive";
                    }
                },
                {
                    "mData": "is_required",
                    mRender: function(v,t,o){
                        return (v == 1) ? "Yes" : "No";
                    }
                },
                {
                    "mData": "is_multiple",
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
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Question', 'Edit Question')))   {
                        ?>
                        act_html = act_html + "<a href='question_addedit.php?id="+ o['question_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['question_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Question', 'Delete Question')))   {
                        ?>
                        act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['question_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
            var selected_list = $('#dg_question tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if (0 == selected_length) {

                showGritter('info', 'Alert!', 'Please select a question to edit.');
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


        $('#delete_record').click(function(){

            var delete_ele = $('#dg_question tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select record to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected record(s)? All the data related to these record(s) will be deleted permanently!", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/question.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_question.fnStandingRedraw();
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
        $('#delete_record').click();
    }
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    Question List
                    <span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Add Query Stage')))   {
                            ?>
                            <a id='add_record' href="question_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Edit Query Stage')))   {
                            ?>
                            <a id='edit_record' href="question_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Query Stage', 'Delete Query Stage')))   {
                            ?>
                            <a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                </div>
                <table id='dg_question' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>Question For</th>
                        <th>Question</th>
                        <th>Short Name</th>
                        <th>Question Type</th>
                        <th>Option Value</th>
                        <th>Sort Order</th>
                        <th>Active</th>
                        <th>Required</th>
                        <th>Multiple</th>
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
