<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen',
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
    'js/bootbox.min',
    'js/chosen.jquery.min',

);
include_once 'header.php';
?>
    <style type="text/css">
        table#dg_status tfoot {
            display: table-header-group;
        }
    </style>

    <script type="text/javascript">
        $(function() {

            $("#state_id").chosen();

            $('[data-rel=tooltip]').tooltip();

            var breakpointDefinition = {
                tablet: 1024,
                phone : 480
            };
            var responsiveHelper2 = undefined;
            var dg_status = $('#dg_status').dataTable({
                "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
                "oColVis": {
                    "buttonText": "Change columns"
                },
                "bPaginate":true,
                oLanguage: {
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
                "lengthMenu": [[10,25,50,100], [10,25,50,100]],
                "sAjaxSource": "control/status.php",
                "fnServerParams": function ( aoData ) {
                    aoData.push({ "name": "act", "value": "fetch" });
                    server_params = aoData;
                },
                "aaSorting": [[ 2, "asc" ]],
                "aoColumns": [
                    {
                        mData:'status_id',
                        bSortable: false,
                        mRender: function (v, t, o) {
                            return '<label><input type="checkbox" id="ids_'+o['status_id']+'" name="ids[]" value="'+o['status_id']+'"/><span class="lbl"></span></label>';
                        },
                        sClass: 'center'
                    },
                    {
                        "mData":"status_name"
                    },
                    {
                        "mData":"status_type"
                    },
                    {
                        "mData":"activity_type"
                    },
                    {
                        "mData":"sort_order",
                        "bSortable":false
                    },
                    {
                        "mData":"is_default",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            var title = (v == 1) ? "Is Default" : "Make It Default";
                            //return "<a class='btn btn-minier btn-warning default' id='default_"+o['status_id']+"' title='"+title+"'>"+label+"</a>";
                            return ""+label+"";

                        }

                    },
                    {
                        "mData":"is_close",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            var title = (v == 1) ? "Is Closed" : "Make It Closed";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"cal_price",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"is_active",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"is_callback",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"is_show_to_kc",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"is_email_send",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"is_sms_send",
                        "bSortable":true,
                        mRender: function(v,t,o){
                            var label = (v == 1) ? "Yes" : "No";
                            return ""+label+"";

                        }
                    },
                    {
                        "mData":"lead_count",
                        bSortable: false,
                        mRender: function(v,t,o){
                            var act_html = '';
                            act_html = act_html + "<a href='lead.php?token=<?php echo $token; ?>&status_id="+o['status_id']+"'  data-rel='tooltip' title='Total Lead'>"+o['lead_count']+" </a>&nbsp";
                            return act_html;
                        }
                    },

                    {
                        mData: null,
                        bSortable: false,
                        mRender: function(v,t,o){
                            var act_html = '';
                            <?php
                            if(($acl->IsAllowed($login_id,'MASTERS', 'Status', 'Edit Status')))   {
                            ?>
                            act_html = act_html + "<a href='status_edit.php?status_id="+o['status_id']+"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>  ";
                            <?php } ?>

                            <?php
                            if(($acl->IsAllowed($login_id,'MASTERS', 'Status', 'Delete Status')))   {
                            ?>
                            act_html = act_html +  "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['status_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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
                "fnRowCallback" : function(nRow, aData, iDisplayIndex){
                    responsiveHelper2.createExpandIcon(nRow);
                },
                fnDrawCallback : function (oSettings) {
                    responsiveHelper2.respond();
                    $(this).removeAttr('style');
                }
            });

//            var colvis = new $.fn.dataTable.ColVis( dg_status );
//            $( colvis.button() ).appendTo('div#show_hide');

            $("tfoot input").keyup( function () {
                dg_status.fnFilter( this.value, $("tfoot input").index(this) );
            });

            if (jQuery().validate) {
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                // Company type validateion code  
                $("#frm_status").validate({
                    rules:{
                        status_name:{required:true }
                    },
                    messages:{
                        status_name:{required:'Please enter status name'}
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
                            url: 'control/status.php?act=addedit',/*i have changed here*/
                            type:'post',
                            beforeSubmit: function (formData, jqForm, options) {
                                $(e).find('button').hide();
                            },
                            dataType: 'json',
                            clearForm: false,
                            success: function (resObj, statusText) {
                                $(e).find('button').show();
                                if(resObj.success){
                                    $(e).clearForm();
                                    $('#modal_add_status').modal('hide');
                                    dg_status.fnDraw();
                                    showGritter('success',resObj.title,resObj.msg);
                                }else{
                                    showGritter('info', resObj.title, resObj.msg);
                                }
                            }
                        });
                    }
                });
            }

            $('#edit_status').click( function (e) {
                var selected_list = $('#dg_status tbody input[type=checkbox]:checked');
                var selected_length = selected_list.size();
                if(0 == selected_length){

                    showGritter('info','Alert!','Please select a record to edit.');
                    return false;
                }else if(selected_length > 1){
                    showGritter('info','Alert!','Only single record can be edited at a time.');
                    return false;
                }

                href = $('#edit_status').attr('href');
                href += '&status_id=' + selected_list.val();
                $('#edit_status').attr('href',href);
                return true;
            });

            $('#delete_status').click(function(){

                var delete_ele = $('#dg_status tbody input[type=checkbox]:checked');
                var selected_length = delete_ele.size();

                if(0 == selected_length){
                    showGritter('info','Alert!','Please select status to delete.');
                    return false;
                }else{
                    bootbox.confirm("Are you sure to delete selected status(s)?", function(result) {
                        if(result) {

                            var delete_id = [];
                            $.each(delete_ele, function(i, ele){
                                delete_id.push($(ele).val());
                            });

                            $.ajax({
                                url: 'control/status.php?act=delete',
                                type:'post',
                                dataType:'json',
                                data:{ id : delete_id },
                                success: function(resp){
                                    dg_status.fnStandingRedraw();
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
        });

        $(document).on('click', '.default',function(e){
            var ele = $(this).closest('tr').get(0);
            var data = dg_status.fnGetData( ele );
            $.ajax({
                url:'control/status.php?act=default',
                dataType:'json',
                type:'post',
                data:{data:data},
                success: function(resp){
                    dg_status.fnDraw();
                    showGritter('success',resp.title,resp.msg);
                }

            });
        });
        $(document).on('click', '.closed',function(e){
            var ele = $(this).closest('tr').get(0);
            var data = dg_status.fnGetData( ele );
            $.ajax({
                url:'control/status.php?act=close',
                dataType:'json',
                type:'post',
                data:{data:data},
                success: function(resp){
                    dg_status.fnDraw();
                    showGritter('success',resp.title,resp.msg);
                }

            });
        });

        function DeleteRecord(statusId){

            $('#ids_'+statusId).prop('checked', true);
            $('#delete_status').click();
        }

        function EditRecord(lang_code){

            $('#ids_'+lang_code).prop('checked', true);
            $('#edit_status').click();
        }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                     Status Master
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Status', 'Add Status')))   {
                        ?>
                        <a id='add_status' href="status_add.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Status', 'Edit Status')))   {
                        ?>
                        <a id='edit_status' href="status_edit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Status', 'Delete Status')))   {
                        ?>
                        <a id='delete_status' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_status' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='dg_status_chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Activity Type</th>
                            <th>Sort Order</th>
                            <th>Is Default</th>
                            <th>Is Closed</th>
                            <th>Is Complete</th>
                            <th>Is Active</th>
                            <th>Is Callback</th>
                            <th>Is Show To Kc</th>
                            <th>Is Send SMS</th>
                            <th>Is Send Email</th>
                            <th>Total Lead</th>
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