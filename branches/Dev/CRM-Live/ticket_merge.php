<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/13/2019
 * Time: 7:08 PM
 */

$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    'css/chosen',
    'css/bootstrap-timepicker',
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
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
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
foreach ($data_fields as $field) {
    $data[$field] = '';
}

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';

// If edit type then reassign data array
if (isset($id) && $id != '') {
    $result = $db->FetchRow($table, $table_id, $id);
    $count = $db->CountResultRows($result);
    if ($count > 0) {
        $action = 'merge';
        $row_data = $db->MySqlFetchRow($result);
        foreach ($data_fields as $field) {
            $data[$field] = $row_data[$field];
        }
    } else {
        $error = 'Invalid Record Or Record Not Found';
    }
} else {
    $error = 'Invalid Record Or Record Not Found';
}
if (isset($id) && $id != '') {
    if ($error != '') {
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
$closedTicketStatusId = $db->FetchCellValue("status_master","status_id","status_type = 'support' and status_name = 'closed'");
$primaryTicketDd = $db->CreateOptions("html", "tickets", array("ticket_id", "ticket_number"), array("ticket_id" => "asc"), null, "is_merged != '1' and ticket_id != '" . $id . "' and status_id != '". $closedTicketStatusId ."'",array(0,10));
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function () {

            $(".chzn-select").chosen({
                allow_single_deselect: true,
            });

            $('#btn_cancel').click(function () {
                form = $(this).closest('form');
                form.find('div.control-group').removeClass("success error");
                form.find('span.help-inline').text("");
                form.clearForm();

            });


            $("#primary_ticket_id").ajaxChosen({
                minTermLength:3,
                type: 'post',
                data: {id : <?php echo $id; ?>},
                url: 'control/ticket.php?act=getticket',
                dataType: 'json'
            }, function (data) {
                var results = [];

                $.each(data, function (i, val) {
                    results.push({ value: val.value, text: val.text });
                });
                return results;
            });



            if (jQuery().validate) {
                var e = function (e) {
                    $(e).closest(".control-group").removeClass("success");
                };


                $("#form_add").validate({
                    rules: {
                        primary_ticket_id: {required: true},
                        merge_action: {required: true},
                    },

                    messages: {
                        primary_ticket_id: {required: 'Please select primary ticket'},
                        merge_action: {required: 'Please select an action'},
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
                            url: 'control/ticket_merge.php?act=merge_ticket',
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
                                            showGritter('error', "Error", message);
                                        });
                                    } else {
                                        showGritter('error', resObj.title, resObj.msg);
                                    }
                                }
                            }
                        });
                    }
                });
            }
        });

    </script>


    <div class='row-fluid'>
        <div class="span12">
            <h4>Merge Ticket / <?php echo $data['ticket_number']; ?></h4>
            <hr>
            <form class="form-horizontal" id="form_add">

                <div class="control-group">
                    <label for="primary_ticket_id" class="control-label">Primary Ticket
                        <small class="text-error"> *</small>
                    </label>
                    <input type="hidden" required value="<?php echo $id; ?>" id="ticket_id" name="ticket_id">
                    <input type="hidden" required value="<?php echo $data['ticket_number']; ?>" id="ticket_number" name="ticket_number">
                    <input type="hidden" required value="<?php echo $closedTicketStatusId; ?>" id="closed_ticket_status_id" name="closed_ticket_status_id">
                    <div class="controls">
                        <select id="primary_ticket_id" name="primary_ticket_id" data-placeholder="Select Primary Ticket" class="chzn-select">
                            <option></option>
                            <?php echo $primaryTicketDd; ?>
                        </select>
                        <i id='primary_ticket_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="primary_ticket_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Select Action
                        <small class="text-error"> *</small>
                    </label>

                    <div class="controls">
                        <label>
                            <input name="merge_action" type="radio" value="merge_all_replies">
                            <span class="lbl"> Merge All Replies</span>
                            <span class="help-block" style="font-size: 12px">(Copies all replies from this ticket to the Primary Ticket)</span>
                        </label>

                        <label>
                            <input name="merge_action" type="radio" value="replace_ticket_data">
                            <span class="lbl"> Replace Ticket Data</span>
                            <span class="help-block" style="font-size: 12px">(Replaces ticket data, including ticket replies,
                                date created, assigned user of the merged ticket with the Primary Ticket)</span>
                        </label>

                        <label>
                            <input name="merge_action" type="radio" value="merge_ticket_files">
                            <span class="lbl"> Merge Ticket Files</span>
                            <span class="help-block" style="font-size: 12px">(Copies all attached ticket files from this ticket to the Primary Ticket)</span>
                        </label>
                        <span for="merge_action" class="help-inline"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-ok bigger-110"></i>Submit
                    </button>
                    <button id='btn_cancel' type="button" class="btn">
                        <i class="icon-undo bigger-110"></i>Reset
                    </button>
                    <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i>
                        Please
                        wait...
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
include_once 'footer.php';
?>
