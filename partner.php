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

include_once 'header.php';
?>
    <style type="text/css">
        table#dg_partner tfoot {
            display: table-header-group;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){

            $('[data-rel=tooltip]').tooltip();
            $(".partner-count").html(0);

            var breakpointDefinition = {
                pc: 1280,
                tablet: 1024,
                phone : 480
            };
            var responsiveHelper2 = undefined;
            dg_partner = $('#dg_partner').dataTable({
                "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
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
                "bScrollCollapse" : true,
                "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
                "sAjaxSource": "control/partner.php",
                "fnServerParams": function ( aoData ) {
                    aoData.push({ "name": "act", "value": "fetch" });
                    server_params = aoData;
                },
                "aaSorting": [[ 6, "desc" ]],
                "aoColumns": [
                    {
                        mData: "partner_id",
                        bSortable : false,
                        mRender: function (v, t, o) {
                            return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                        },
                        sClass: 'center'
                    },
                    {"mData": "partner_code" },
                    {
                        "mData": "full_name",
                        mRender: function (v, t, o) {
                            return v;
                        }
                    },
                    {"mData": "parent_partner_name" },
                    {"mData": "email" },
                    {"mData": "mobile_no" },
                    {"mData": "partner_type" },
                    {"mData": "city_name"},
                    {"mData": "created_at"},
                    {"mData": "created_by"},
                    {
                        "mData": "is_active",
                        mRender: function (v,t,o){
                            return (v == 1) ? 'Yes' : 'No';
                        }
                    },
                    {
                        bSortable :false,
                        mData: null,
                        mRender: function(v,t,o){
                            var act_html = '';
                            <?php
                            if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'Edit Partner')){
                            ?>
                            act_html = act_html + "<a href='partner_addedit.php?id="+ o['partner_id'] +"&token=<?php echo $token; ?>' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Edit "+o['full_name']+"'><i class='icon-edit bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'Delete Partner')){
                            ?>
                            act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['partner_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'Lead', 'Lead', 'View Lead')){
                            ?>
                            //act_html = act_html + "<a target='_blank' href='lead.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' class='btn btn-minier btn-primary' data-placement='bottom' data-rel='tooltip' data-original-title='View Lead' title='View Lead'><i class='icon-book bigger-120'></i></a>&nbsp";
                            <?php } ?>

                            act_html = act_html + "<div class='inline position-relative'>" +
                                "<button title='Partner&nbsp;Info' data-placement='left' data-rel='tooltip' data-toggle='dropdown' class='btn btn-minier bigger btn-primary dropdown-toggle'>" +
                                "<i class='icon-info-sign icon-only bigger-120'></i>" +
                                "</button>" +
                                "<ul class='dropdown-menu dropdown-icon-only dropdown-yellow pull-right dropdown-caret dropdown-close'>" +

                                <?php
                                if($acl->IsAllowed($login_id,'Lead', 'Lead', 'View Lead')){
                                ?>
                                "<li>" +
                                "<a title='CW Lead&nbsp;View' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='lead.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' data-original-title='CW Lead&nbsp;View'>" +
                                "<span class='blue'>" +
                                "<i class='icon-book bigger-110'></i>" +
                                "</span>" +
                                "</a>" +
                                "</li>" +
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'View Partner Withdrawal')){
                                ?>
                                "<li>" +
                                "<a title='Withdrawal&nbsp;Request' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='partner_withdrawal.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' data-original-title='Withdrawal&nbsp;Request'>" +
                                "<span class='blue'>" +
                                "<i class='icon-hand-up bigger-110'></i>" +
                                "</span>" +
                                "</a>" +
                                "</li>" +
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'View Partner Commission')){
                                ?>
                                "<li>" +
                                "<a title='Commission&nbsp;View' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='partner_commission.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' data-original-title='Commission&nbsp;View'>" +
                                "<span class='blue'>" +
                                "<i class='icon-gift bigger-110'></i>" +
                                "</span>" +
                                "</a>" +
                                "</li>" +
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'PARTNER', 'Payout', 'View Partner Payout')){
                                ?>
                                "<li>" +
                                "<a title='Payout&nbsp;View' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='partner_payout.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' data-original-title='Payout&nbsp;View'>" +
                                "<span class='blue'>" +
                                "<i class='icon-exchange bigger-110'></i>" +
                                "</span>" +
                                "</a>" +
                                "</li>" +
                                <?php } ?>

                                <?php
                                if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'View Partner Ledger')){
                                ?>
                                "<li>" +
                                "<a title='Ledger&nbsp;View' target='_blank' data-placement='left' data-rel='tooltip' class='tooltip-info' href='partner_ledger.php?partner_id="+o['partner_id']+"&token=<?php echo $token; ?>' data-original-title='Ledger&nbsp;View'>" +
                                "<span class='blue'>" +
                                "<i class='icon-inr bigger-110'></i>" +
                                "</span>" +
                                "</a>" +
                                "</li>" +
                                <?php } ?>

                                "</ul>" +
                                "</div>";
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
                    return nRow;
                },
                fnDrawCallback : function (oSettings) {
                    responsiveHelper2.respond();
                    $(this).removeAttr('style');
                    $('[data-rel=tooltip]').tooltip();
                }
            });

            $("tfoot input").keyup( function () {
                dg_partner.fnFilter( this.value, $(this).attr("colPos") );
            });


            $('#edit_record').click( function (e) {
                var selected_list = $('#dg_partner tbody input[type=checkbox]:checked');
                var selected_length = selected_list.size();

                if(0 == selected_length){

                    showGritter('info','Alert!','Please select a partner to edit.');
                    return false;
                }else if(selected_length > 1){
                    showGritter('info','Alert!','Only single record can be edited at a time.');
                    return false;
                }

                href = $('#edit_record').attr('href');
                href += '&id=' + selected_list.val();
                $('#edit_record').attr('href',href);
                return true;
            });

            $('#delete_record').click(function(){

                var delete_ele = $('#dg_partner tbody input[type=checkbox]:checked');
                var selected_length = delete_ele.size();

                if(0 == selected_length){
                    showGritter('info','Alert!','Please select partner to delete.');
                    return false;
                }else{
                    bootbox.confirm("Are you sure to delete selected partner(s)? It will delete all partner related data and can not be reverted", function(result) {
                        if(result) {

                            var delete_id = [];
                            $.each(delete_ele, function(i, ele){
                                delete_id.push($(ele).val());
                            });

                            $.ajax({
                                url: 'control/partner.php?act=delete',
                                type:'post',
                                dataType:'json',
                                data:{ id : delete_id, },
                                success: function(resp){
                                    dg_partner.fnDraw();
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

        function ExportToExcel(ele){

            var query_string = decodeURIComponent($.param(server_params));
            $(ele).attr('href','export_partners.php?='+query_string);
            return true;
        }


        function DeleteRecord(rid){

            $('#chk_'+rid).prop('checked', true);
            $('#delete_record').click();
        }

    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Partner List
					<span class="widget-toolbar pull-right">
                        <?php
                        if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'Add Partner')){
                            ?>
                            <a id='add_record' href="partner_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'Edit Partner')){
                            ?>
                            <a id='edit_record' href="partner_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp|
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'Delete Partner')){
                            ?>
                            <a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'PARTNER', 'Partner', 'dExport Partner Details')){
                            ?>
                            <a target="_blank" id="export_excel" href="javascript:void(0);" class="btn btn-mini btn-primary show-tooltip" data-placement="top" data-rel="tooltip" data-original-title="Excel Export" onclick="return ExportToExcel(this);"><i class="icon-save icon-large white">Export</i></a>
                        <?php } ?>

					</span>
                    </div>
                    <table id='dg_partner' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox"/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Email</th>
                            <th>Mobile No.</th>
                            <th>Type</th>
                            <th>City</th>
                            <th>Created On</th>
                            <th>Created By</th>
                            <th>Is Active</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th class="center">
                            </th>
                            <th>
                                <input type="text"  placeholder="code" name="code" class="span7" colPos="7">
                            </th>
                            <th>
                                <input type="text"  placeholder="name" name="name" class="span7" colPos="1">
                            </th>
                            <th></th>
                            <th>
                                <input type="text"  placeholder="email" name="filter_email" class="span7" colPos="4">
                            </th>
                            <th>
                                <input type="text"  placeholder="mobile" name="filter_mobile" class="span7" colPos="5">
                            </th>
                            <th></th>
                            <th>
                                <input type="text"  placeholder="city" name="filter_city" class="span7" colPos="6">
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
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
<?php
include_once 'footer.php';
?>