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
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
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
        dg_tier_category = $('#dg_tier_category').dataTable({
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
            "sAjaxSource": "control/tier_category.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'tier_category_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="tier_category_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"tier_name" },
                {"mData":"category_name"},
                {"mData":"commission"},
                {"mData":"effective_date"},
                {"mData":"max_withdrawal"},

                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier category rate', 'Edit Tier Category Rate')))   {
                        ?>
                        act_html = act_html+"<a href='tier_category_edit.php?id="+ o['tier_category_id'] +"&token=<?php echo $token; ?>'  class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier category rate', 'Delete Tier Category Rate')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['tier_category_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
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



        $('#edit_tier_category').click( function (e) {
            var selected_list = $('#dg_tier_category tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();
            if(0 == selected_length){

                showGritter('info','Alert!','Please select a record to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            href = $('#edit_tier_category').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_tier_category').attr('href',href);
            return true;
        });

        $("#modal_add_tier_category").on("hidden", function () {
            $('#dg_tier_category tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_tier_category').click(function(){

            var delete_ele = $('#dg_tier_category tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select tier_category to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected tier_category(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/tier_category.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_tier_category.fnStandingRedraw();
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

    function DeleteRecord(tier_categoryId){

        $('#ids_'+tier_categoryId).prop('checked', true);
        $('#delete_tier_category').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_tier_category').click();
    }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Tier Categories Rate
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier Category Rate', 'Add Tier Category Rate')))   {
                            ?>
                            <a id='add_tier_category' href="tier_category_add.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier Category Rate', 'Edit Tier Category Rate')))   {
                            ?>
                            <a id='edit_tier_category' href="tier_category_edit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Tier Category Rate', 'Delete Tier Category Rate')))   {
                            ?>
                            <a id='delete_tier_category' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_tier_category' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Tier</th>
                            <th>Category</th>
                            <th>Commission</th>
                            <th>Effective Date</th>
                            <th>Max Withdrawal</th>
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