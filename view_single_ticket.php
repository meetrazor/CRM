<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    'css/chosen',
    'css/bootstrap-timepicker',
    'css/colorbox',
    'css/bootstrap-datetimepicker.min',
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'js/jquery.autosize-min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/bootstrap-datetimepicker.min',
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
    'js/jquery.colorbox-min',
    'js/ckeditor',
    'js/jquery.nicescroll.min',

);
$middle_breadcrumb = array('title' => 'CW Tickets', 'link' => 'ticket.php');
include_once 'header.php';
$table = 'tickets';
$table_id = 'ticket_id';
$action = 'add';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field){
    $data[$field] = '';
}

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';

// If edit type then reassign data array
if(isset($id) && $id!=''){
    $result = $db->FetchRow($table, $table_id, $id);
    $count = $db->CountResultRows($result);
    if($count > 0){
        $action = 'edit';
        $row_data = $db->MySqlFetchRow($result);
        foreach ($data_fields as $field){
            $data[$field] = $row_data[$field];
        }
    }else{
        $error = 'Invalid Record Or Record Not Found';
    }
} else {
    $error = 'Invalid Record Or Record Not Found';
}
if(isset($id) && $id!=''){
    if($error != ''){
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
if(is_array($data) && count($data) > 0 ){
    $mainTable = array("tickets as t",array("t.ticket_number,t.call_from,t.comment,t.resolve_date_time,t.created_at,t.updated_at"));
    $joinTable = array(
        array("left","customer_master as cm","cm.customer_id = t.customer_id",array("customer_name")),
        array("left","reason_master as rm","rm.reason_id = t.reason_id",array("reason_name")),
        array("left","loan_type_master as ltm","ltm.loan_type_id = t.loan_type_id",array("ltm.loan_type_name")),
        array("left","category_master as cms","cms.category_id = t.product_type_id",array("cms.category_name")),
        array("left","query_type_master as qtm","qtm.query_type_id = t.query_type_id",array("query_type_name")),
        array("left","query_stage_master as qsm","qsm.query_stage_id = t.query_stage_id",array("query_stage_name")),
        array("left","sub_query_stage_master as sqsm","sqsm.sub_query_stage_id = t.sub_query_stage_id",array("sub_query_stage_name")),
        array("left","bank_master as bm","bm.bank_id = t.bank_id",array("bank_name")),
        array("left","admin_user as au","au.user_id = t.assign_to",array("concat(au.first_name,' ',au.last_name) as assigned_to_user")),
        array("left","disposition_master as dm","dm.disposition_id = t.disposition_id",array("disposition_name")),
        array("left","status_master as sm","sm.status_id = t.status_id",array("status_name"))
    );
    $misDisRes = $db->JoinFetch($mainTable,$joinTable,"t.ticket_id =". $id,null,array(0,1),null);
    $ticketData = $db->MySqlFetchRow($misDisRes);

    $ticketHistoryRes = $db->Fetch('ticket_history',array('comment','created_at'),"ticket_id = ".$id,array('created_at' => 'desc'),array(0,5));
    $ticketHistoryData = $db->FetchToArrayFromResultset($ticketHistoryRes);
}
$statusClose = $db->FetchCellValue("status_master","status_id","is_close = 1 and status_id = '{$data['status_id']}'");
if($statusClose != ''){
    // $error = 'You Can\'t edit Close status data';
}
?>
    <style rel="stylesheet">
        .ck-editor__editable {
            min-height: 200px;
        }
    </style>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function() {
            ClassicEditor.create(document.querySelector('#comment'));
            $(".do-nicescrol").niceScroll();

            $('#btn_cancel').click(function () {
                form = $(this).closest('form');
                form.find('div.control-group').removeClass("success error");
                form.find('span.help-inline').text("");
                form.clearForm();
                $('select.chzn-select').trigger("liszt:updated");
            });

            if (jQuery().validate) {
                var e = function (e) {
                    $(e).closest(".control-group").removeClass("success");
                };

                $("#form_add").validate({

                    rules: {
                        comment: {required: true}
                    },
                    messages: {
                        comment: {required: 'Please enter comment'}
                    },
                    errorElement: "span",
                    errorClass: "help-inline",
                    focusInvalid: false,
                    ignore: "",
                    invalidHandler: function (e, t) {
                    },
                    highlight: function (e) {
                        $(e).closest(".control-group").removeClass("success").addClass("error");
                    },
                    unhighlight: function (t) {
                        $(t).closest(".control-group").removeClass("error");
                        setTimeout(function () {
                            e(t);
                        }, 3e3);
                    },
                    success: function (e) {
                        e.closest(".control-group").removeClass("error").addClass("success");
                    },
                    submitHandler: function (e) {

                        $(e).ajaxSubmit({
                            url: 'control/ticket_addedit.php?act=addedit',
                            type: 'post',
                            beforeSubmit: function (formData, jqForm, options) {
                                $(e).find('button').hide();
                                $('#loader').show();
                            },
                            complete: function () {
                                $('#loader').hide();
                                $(e).find('button').show();
                            },
                            dataType: 'json',
                            clearForm: false,
                            success: function (resObj, statusText) {
                                $(e).find('button').attr('disabled', false);

                                if (resObj.success) {
                                    showGritter('success', resObj.title, resObj.msg);
                                    setTimeout(function () {
                                        location.reload(true);
                                    }, 3000);
                                } else {
                                    if (resObj.hasOwnProperty("errors")) {
                                        var message = '';
                                        $.each(resObj.errors, function (key, value) {
                                            message += value + "<br>";
                                        });
                                        showGritter('error', "Error", message);
                                    } else {
                                        showGritter('error', resObj.title, resObj.msg);
                                    }
                                }
                            }
                        });
                    }
                });
            }
            $('#delete_record').click(function(){
                bootbox.confirm("Are you sure to delete selected ticket(s)? It will delete all ticket related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        delete_id.push($("#ticket_id").val());

                        $.ajax({
                            url: 'control/ticket.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id },
                            success: function(resp){
                                <?php $redirect_url = "".SITE_ROOT."ticket.php?token=$token"; ?>
                                showGritter('success',resp.title,resp.msg);
                                setTimeout(function () {
                                    window.location.href = "<?php echo $redirect_url; ?>";
                                }, 3000);
                            }
                        });
                    }
                });
            });
        });


    </script>

    <div class="row-fluid">
        <div class="span12">
            <div class="space-6"></div>

            <div class="row-fluid">
                <div class="span8">

                    <div class="row-fluid">
                        <div class="widget-box ">
                            <div class="widget-header">
                                <h4 class="lighter smaller">
                                    Last Comment
                                </h4>
                            </div>

                            <div class="widget-body">
                                <div class="widget-main no-padding">
                                    <div class="modal-body overflow-scrollable">
                                        <?php echo $ticketData['comment']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-6"></div>
                    <?php
                    if($statusClose == ''){
                    ?>
                    <div class="row-fluid">
                        <div class="widget-box ">
                            <div class="widget-header">
                                <h4 class="lighter smaller">
                                    Add Comment
                                </h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main no-padding">
                                    <form id="form_add">
                                        <div class="form-actions input-append">
                                            <input type="hidden" name="ticket_id" id="ticket_id" value="<?php echo $data['ticket_id']; ?>">
                                            <input type="hidden" name="status_id" id="status_id" value="<?php echo $data['status_id']; ?>">
                                            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $data['customer_id']; ?>">
                                            <div class="control-group">
                                                <label for="about_user" class="control-label">Comment</label>
                                                <div class="controls">
                                                    <textarea name="comment" id="comment" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class='control-group'>
                                                <label for='filename' class='control-label'>Upload Document</label>
                                                <div class='controls'>
                                                    <input type='file' id="filename" name='filename[]' placeholder='File' class='upload' multiple/>
                                                </div>
                                            </div>

                                                <div class="span10">
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="icon-ok bigger-110"></i>Save
                                                        </button>&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <button id='btn_cancel' type="button" class="btn">
                                                            <i class="icon-undo bigger-110"></i>Reset
                                                        </button>
                                                        <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                                                            wait...
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <div class="span4">

                    <div class="row-fluid">
                        <div class="widget-box ">
                            <div class="widget-header">
                                <h4 class="lighter smaller">
                                    Ticket Details
                                </h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main no-padding">
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td>Ticket</td>
                                            <td><?= $ticketData['ticket_number'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Customer</td>
                                            <td><?= $ticketData['customer_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Reason</td>
                                            <td><?= $ticketData['reason_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Loan Type</td>
                                            <td><?= $ticketData['loan_type_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Product Type</td>
                                            <td><?= $ticketData['category_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Query Received From</td>
                                            <td><?= $ticketData['call_from'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Query Type</td>
                                            <td><?= $ticketData['query_type_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Query Stage</td>
                                            <td><?= $ticketData['query_stage_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Sub Query Stage</td>
                                            <td><?= $ticketData['sub_query_stage_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Bank</td>
                                            <td><?= $ticketData['bank_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Assigned to</td>
                                            <td><?= $ticketData['assigned_to_user'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Expected time for resolution</td>
                                            <td><?= $ticketData['resolve_date_time'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Disposition</td>
                                            <td><?= $ticketData['disposition_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Status</td>
                                            <td><?= $ticketData['status_name'];?></td>
                                        </tr>
                                        <tr>
                                            <td>Created</td>
                                            <td><?= core::YMDToDMY($ticketData['created_at'],true); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Last Updated</td>
                                            <td><?= core::YMDToDMY($ticketData['updated_at'],true); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div>
                                        <p class='col-xs-12' align='center'>
                                            <?php
                                            if($statusClose == ''){
                                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Edit Ticket')){ ?>
                                                    <a class="btn btn-warning btn-mini" href="ticket_addedit.php?id=<?= $id; ?>&token=<?= $token; ?>"><i class="icon-edit bigger-125 icon-2x icon-only"></i></a>&nbsp
                                                <?php } ?>
                                                <?php
                                                if ($acl->IsAllowed($login_id, 'Ticket', 'Ticket', 'Merge Ticket')) {
                                                    ?>
                                                    <a class="btn btn-info btn-mini" href="ticket_merge.php?id=<?= $id; ?>&token=<?= $token; ?>"><i class="icon-exchange bigger-125 icon-2x icon-only"></i></a>&nbsp
                                                <?php } ?>
                                                <?php
                                                if($acl->IsAllowed($login_id,'Ticket', 'Ticket', 'Delete Ticket')){
                                                    ?>
                                                    <a id="delete_record" class="btn btn-danger btn-mini" href="javascript:void(0);"><i class="icon-trash bigger-125 icon-2x icon-only"></i></a>
                                                <?php }
                                            } ?>
                                        </p>
                                    </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-6"></div>

                    <div class="row-fluid">
                        <div class="widget-box ">
                            <div class="widget-header">
                                <h4 class="lighter smaller">
                                    Ticket History
                                </h4>
                            </div>
                            <div class="widget-body">
                            <div class="widget-main no-padding">
                                <div class="do-nicescrol" style="position: relative; overflow: hidden; width: auto; height: 450px;">
                                    <?php
                                    if(count($ticketHistoryData) > 0) { ?>
                                    <table class="table">
                                        <tbody>
                                        <?php foreach ($ticketHistoryData as $rowData) { ?>
                                            <tr>
                                                <td><?= $rowData['comment']; ?></td>
                                            </tr>
                                            <tr>
                                                <th><?= $rowData['created_at']; ?></th>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <div>
                                        <h3 class='col-xs-12' align='center'>
                                            <a href="ticket_history.php?id=<?= $id; ?>&token=<?php echo $token; ?>" ><button class="btn btn-small btn-info">View All</button></a>
                                        </h3>
                                    </div>
                                    <?php }
                                    else {
                                        echo "<div>
                                                <h3 class='col-xs-12' align='center'>
                                                    <span style='color:#438EB9;'>No History Found..</span>
                                                </h3>
                                              </div>";
                                    } ?>
                                    <div class="slimScrollBar ui-draggable" style="background: rgb(0, 0, 0); width: 7px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 287.593px;"></div>
                                    <div class="slimScrollRail" style="width: 7px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php
include_once 'footer.php';
?>