<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    //'css/chosen',
    'css/chosen.create-option',
    'css/bootstrap-timepicker',
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.create-option.jquery',
    'js/jquery.autosize-min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
);

$middle_breadcrumb = array('title' => 'Campaigns', 'link' => 'campaign.php');
include_once 'header.php';
$table = 'campaign_master';
$table_id = 'campaign_id';
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
        $action = 'edit';
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
$activeCondition = ($action != "edit") ? "is_active = 1" : "1=1";
$campaignDd = $db->CreateOptions("html", "campaign_type_master", array("campaign_type_id", "campaign_type_name"), $data['campaign_type_id'], array("campaign_type_name" => "asc"),$activeCondition);
$vendorDd = $db->CreateOptions("html", "vendor_master", array("vendor_id", "vendor_name"), $data['vendor_id'], array("vendor_name" => "asc"),$activeCondition);
$selectedCategory = $db->FetchToArray("campaign_category","category_id","campaign_id = '{$id}'");
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$selectedCategory,array("category_name"=>"asc"),$activeCondition);
$selectedTeleCaller = $db->FetchToArray("campaign_telecaller","user_id","campaign_id = '{$id}'");
$tellecallerDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),$selectedTeleCaller,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = ".UT_TC."");
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function () {

            $(".chzn-select").chosen({
                allow_single_deselect: true,
            });

            $('.date-picker').datepicker({
                orientation: 'top',
                autoclose: true,
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });


            $('#btn_cancel').click(function () {
                form = $(this).closest('form');
                form.find('div.control-group').removeClass("success error");
                form.find('span.help-inline').text("");
                var email = $('#email').val();
                form.clearForm();
                $('#email').val(email);
                $('select.chzn-select').trigger("liszt:updated");
                $('select.chzn-select').trigger("chosen:updated");

            });


            if (jQuery().validate) {
                var e = function (e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                jQuery.validator.addMethod("zipcode", function (value, element) {
                    return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
                }, "Please provide a valid pin code.");

                $("#frm_campaign").validate({
                    rules: {
                        campaign_name: { required: true },
                        phone_number: { required: true },
                        start_date: { required: true},
                        end_date: { required: true, dateGreaterThan: '#frm_campaign' + " input[name='start_date']" },
                        description: { required: true},
                        campaign_type_id: { required: true},
                        vendor_id: { required: true},
                        amount: { required: true,number:true},
                        'category_id[]': { required : true }
                    },

                    messages: {
                        campaign_name: { required: "please enter campaign name" },
                        phone_number: { required: "please enter campaign ID", number:"please enter only number" },
                        start_date: { required: "please enter start date"},
                        end_date:{required: 'please select end Date',dateGreaterThan:"end must be greater and equal to start date"},
                        campaign_type_id: { required: "please select campaign type"},
                        vendor_id: { required: "please select vendor"},
                        amount: { required: "please enter amount",number:"please enter number only"},
                        category_id: { required : 'Please select category'},
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
                            url: 'control/campaign_addedit.php?act=addedit',
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
            <form class="form-horizontal" id="frm_campaign">

                <div class="control-group">
                    <label for="campaign_type_id" class="control-label">Campaign Type
                        <small class="text-error"> *</small>
                    </label>

                    <div class="controls">
                        <select id="campaign_type_id" name="campaign_type_id" data-placeholder="Select campaign type" class="chzn-select">
                            <option></option>
                            <?php echo $campaignDd; ?>
                        </select>
                        <span for="campaign_type_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="campaign_name">Campaign Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['campaign_name']; ?>" type="text" name="campaign_name" id="campaign_name"
                                   placeholder="ex. facebook"/>
                            <input value="<?php echo $id; ?>" type="hidden" name="campaign_id" id="campaign_id"/>
                        </div>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="campaign_name">Campaign ID<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['phone_number']; ?>" type="text" name="phone_number" id="phone_number" placeholder="ex. 274277772"/>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="category_id" class="control-label">Loan/Product Type<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="category_id" name="category_id[]" data-placeholder="Select Loan/Product Type" class="chzn-select" multiple>
                            <option></option>
                            <?php echo $categoryDd; ?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>



                <div class='control-group'>
                    <label class='control-label' for='start_date'>Start Date<small class="text-error"> *</small></label>
                    <div class='controls'>
                        <div class='row-fluid input-append'>
                            <input class='input-small date-picker' data-placement='top' type='text' placeholder='start date'
                                   name='start_date' data-date-format='dd-mm-yyyy'
                                   readonly='readonly' id="start_date"
                                   value="<?php echo ($data['start_date'] != '0000-00-00' && $data['start_date'] != '') ? core::YMDToDMY($data['start_date']) : ""; ?>">
                            <span class='add-on'><i class='icon-calendar'></i></span>
                            <span for='start_date' class='help-inline'></span>
                        </div>
                    </div>
                </div>

                <div class='control-group'>
                    <label class='control-label' for='end_date'>End Date<small class="text-error"> *</small></label>
                    <div class='controls'>
                        <div class='row-fluid input-append'>
                            <input class='input-small date-picker' data-placement='top' type='text' placeholder='end date'
                                   name='end_date' data-date-format='dd-mm-yyyy'
                                   readonly='readonly' id="end_date"
                                   value="<?php echo ($data['end_date'] != '0000-00-00' && $data['end_date'] != '') ? core::YMDToDMY($data['end_date']) : ""; ?>">
                            <span class='add-on'><i class='icon-calendar'></i></span>
                            <span for='end_date' class='help-inline'></span>
                        </div>
                    </div>
                </div>



                <div class="control-group">
                    <label class="control-label" for="description">Description</label>

                    <div class="controls">
                        <div class="span12">
                            <textarea name="description" id="description" rows="3"><?php echo $data['description']; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="city_id" class="control-label">Tellecaller</label>
                    <div class="controls">
                        <select id="user_id" name="user_id[]" data-placeholder="Select tellecaller" class="chzn-select" multiple>
                            <option></option>
                            <?php echo $tellecallerDd; ?>
                        </select>
                        <span for="user_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="vendor_id" class="control-label">Vendor
                        <small class="text-error"> *</small>
                    </label>

                    <div class="controls">
                        <select id="vendor_id" name="vendor_id" data-placeholder="Select vendor type" class="chzn-select">
                            <option></option>
                            <?php echo $vendorDd; ?>
                        </select>
                        <span for="vendor_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="amount">Amount<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input value="<?php echo $data['amount']; ?>" type="tel" name="amount" id="amount" placeholder="ex. 50000"/>
                    </div>
                </div>



                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active"
                               id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : ""; ?>>
                        <span class="lbl">Is Active</span>
                    </label>
                </div>


                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-ok bigger-110"></i>Submit
                    </button>
                    <button id='btn_cancel' type="button" class="btn">
                        <i class="icon-undo bigger-110"></i>Reset
                    </button>
                    <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                        wait...
                    </div>
                </div>
            </form>
      </div>
    </div>
<?php
include_once 'footer.php';
?>