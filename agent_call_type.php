<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen',
    'css/bootstrap-timepicker',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/js/fnStandingRedraw',
    'js/jquery.gritter.min',
    'js/chosen.jquery.min',
    'js/bootbox.min',
    'js/date-time/bootstrap-datepicker.min',
);
include_once 'header.php';
?>

    <script type="text/javascript">
    $(function() {

        $('.modal').on('shown.bs.modal', function () {
            $('.chzn-select', this).chosen({
                allow_single_deselect:true
            });
            $(".chzn-select option[value='Lead']").prop("selected","selected");
            $(".chzn-select").trigger("liszt:updated");
        });

        $('[data-rel=tooltip]').tooltip();


        $('.date-picker').datepicker({
            orientation: 'top',
            autoclose: true,
        }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_bank = $('#dg_bank').dataTable({
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
                    sNext: '<i class="icon-angle-right"></i>',
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/agent_call_type.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'agentcalltype_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="agentcalltype_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"name" },              
                {
                    "mData": "is_active",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Active" : "Inactive";
                    }
                },
            
                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                       if(($acl->IsAllowed($login_id,'MASTERS', 'Edit Agent Call Type', 'Edit Agent call type')))   {
                        ?>
                        act_html = act_html + "<a href='agentcalltype_addedit.php?id="+ o['agentcalltype_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['bank_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Agent Call Type', 'Delete Agent Call Type')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['agentcalltype_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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


        $('#delete_bank').click(function(){

            var delete_ele = $('#dg_bank tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select prospect to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected Agent call type(s)? It will delete all bank related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/agent_call_type.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_bank.fnStandingRedraw();
                                if(resp.success){
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

        $('#edit_record').click( function (e) {
            var selected_list = $('#dg_bank tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();
            console.log(selected_list);
    
            if(0 == selected_length){
                showGritter('info','Alert!','Please select a Agent Call Type to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }
    
            var rowIndex = dg_bank.fnGetPosition( $("#ids_"+selected_list.val()+"").closest('tr')[0] );
            var aData = dg_bank.fnGetData( rowIndex  );
            var isClose = aData.is_close;
            <?php
                if(!$acl->IsAllowed($login_id,'MASTERS', 'Edit Agent Call Type', 'Update Agent Call Type After Close')){ ?>
            if(1 == isClose){
                showGritter('error','Alert!','You can\'t Update this record');
                return false;
            }
            <?php } ?> 
            href = $('#edit_record').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_record').attr('href',href);
            return true;
        });
    });

    function DeleteRecord(bankId){

        $('#ids_'+bankId).prop('checked', true);
        $('#delete_bank').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_bank').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Agent Call Type
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Agent Call Type', 'Add Agent Call Type')))   {
                            ?>
                            <a id='add_bank' href="agentcalltype_addedit.php?token=<?php echo $token; ?>" data-placement="bottom" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Edit Agent Call Type', 'Edit Agent call type')))   {
                            ?>
                            <a id='edit_record' href="agentcalltype_addedit.php?token=<?php echo $token; ?>" data-placement="bottom" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Agent Call Type', 'Delete Agent Call Type')))   {
                            ?>
                            <a id='delete_bank' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_bank' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Name</th>
                            <th>Status</th>
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
include_once 'footer.php';
?>