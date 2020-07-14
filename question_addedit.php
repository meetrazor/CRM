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

$middle_breadcrumb = array('title' => 'Question', 'link' => 'question.php');
include_once 'header.php';
$table = 'question_master';
$table_id = 'question_id';
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
        $optionType = $data['option_type'];
        $optionValue = $db->FetchToArray("question_option_value", "*", "question_id = '{$id}'");
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
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function () {

            <?php if(isset($id) && $id != ''){ ?>
            var optionType = '<?php echo $optionType; ?>';
            $("#option_type option:not([value ='" + optionType + "'])").prop("disabled", true);
            <?php } ?>

            $(".chzn-select").chosen({
                allow_single_deselect: true,
            });

            // Select2Init();

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


                $("#form_add").validate({
                    rules: {
                        question_for: {required: true},
                        question_name: {required: true},
                        question_short_name: {required: true, maxlength : 5},
                        option_type: {required: true},
                        'option_value[0][option_value]': {
                            required: "#option_value0:visible",
                            maxlength: 50,
                        },
                        'option_value[0][sort_order]': {
                            required: "#sort_order0:visible",
                            number: true
                        },
                        'option_value[0][weight]': {
                            required: "#weight0:visible",
                            number: true
                        },
                        sort_order: {number: true}
                    },

                    messages: {
                        question_for: {required: 'Please select question for'},
                        question_name: {required: 'Please enter a question'},
                        question_short_name: {
                            required: 'Please enter a short name of question',
                            maxlength: 'Max length is five characters for short name'
                        },
                        option_type: {required: 'Please select option type'},
                        'option_value[0][option_value]': {
                            required: 'Please enter option value',
                            maxlength: 'Max length is 50 character',
                        },
                        'option_value[0][sort_order]': {
                            required: 'Please enter sort order',
                            number: 'Please enter only number'
                        },
                        'option_value[0][weight]': {
                            required: 'Please enter weight',
                            number: 'Please enter only number'
                        },
                        sort_order: {number: 'Please enter only number'}
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
                            url: 'control/question_addedit.php?act=addedit',
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


            $("#option_type").change(function () {
                var option = $(this).find(':selected').data("option");
                if (option == 1) {
                    $("#rowListing").show();
                } else {
                    $("#post_rows tbody tr:not(:first)").remove();
                    $("#post_rows input").val(' ');
                    $("#rowListing").hide();
                }
            });

            $("#rowListing").hide();
            <?php if(isset($data)) {?>
            $("#option_type").change();
            <?php }?>
            <?php if(isset($data['option_type']) && $data['option_type'] != '') {?>
            $("#option_type").chosen({
                allow_single_deselect: true
            });
            $('#option_type option', this).not(':eq(0), :selected').attr('disabled', 'disabled');
            <?php } ?>
        });


        function addValidation(type, selecter, rules) {
            $('' + type + '' + selecter + '').each(function () {
                $(this).rules('add', rules);
            });

        }

        var option_index = <?= isset($data['option_type']) ? count($data['option_type']) : 0 + 1;?>;

        function AddSizeRow() {
            //remove = option_index;
            option_index++;
            iner_option_index = option_index;

            emailHtml = "<tr id='option_value_" + iner_option_index + "'>" +

                "<td>" +
                "<input type='text' id='option_value" + iner_option_index + "' name='option_value[" + iner_option_index + "][option_value]'>" +
                "</td>" +

                "<td>" +
                "<input type='tel' id='weight" + iner_option_index + "' name='option_value[" + iner_option_index + "][weight]'>" +
                "</td>" +

                "<td>" +
                "<input type='tel' id='sort_order" + iner_option_index + "' name='option_value[" + iner_option_index + "][sort_order]'>" +
                "</td>" +

                "<td>" +
                "<a href='javascript:void(0);' data-popup='custom-tooltip' data-original-title='Add' title='Add' class='btn btn-mini btn-danger' onclick='deleteRow(" + iner_option_index + ")'><i class='icon-trash'></i></a>" +
                "</td>" +
                "</tr>";

            $('table#option_rows tbody').append(emailHtml);


            addValidation("input", "#option_value" + iner_option_index + "", {
                required: true
            });

            addValidation("input", "#sort_order" + iner_option_index + "", {
                required: true,
                number: true
            });

            addValidation("input", "#weight" + iner_option_index + "", {
                required: true,
                number: true
            });
        }


    </script>
    <script>
        function deleteRow(index, rowId, qid) {

            bootbox.confirm("Are you sure to delete this row? data related to this option value will be removed permanently!", function (result) {
                if (result) {
                    $.ajax({
                        url: 'control/question_addedit.php?act=delete_row',
                        type: 'post',
                        dataType: 'json',
                        data: {id: rowId, question_id: qid },
                        success: function (resp) {
                            if (resp.success) {
                                showGritter('success', resp.title, resp.msg);
                                $('table#option_rows tr#option_value_' + index).slideUp().hide().remove();
                                //setTimeout(function () {  location.reload(true);}, 2000);
                            } else {
                                showGritter('error', resp.title, resp.msg);
                            }
                        }
                    });
                }
            });
        }
    </script>

    <div class='row-fluid'>
        <div class="span12">
            <form class="form-horizontal" id="form_add">
                <input type="hidden" value="<?php echo $id; ?>" id="question_id" name="question_id">
                <div class="control-group">
                    <label class="control-label" for="question_for">Question For
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <select class="chzn-select" name="question_for" id="question_for"
                                data-placeholder="Select Question For">
                            <option value=""></option>
                            <?php
                            $enumvals = $db->GetEnumvalues("question_master", "question_for");
                            foreach ($enumvals as $enumval) {
                                if ($data['question_for'] == $enumval) { ?>
                                    <option selected value="<?php echo $enumval; ?>"><?php echo $enumval; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $enumval; ?>"><?php echo $enumval; ?></option>
                                <?php }
                            }
                            ?>
                        </select>
                        <span for="question_for" class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="reason_for">Reason
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <?php   $reasonvals = $db->FetchToArray("reason_master", "*", "");
                       // $db->GetEnumvalues("reason_master", "reason_name");
                            $reasonData = explode(",", $data['reason']);?>
                        <select class="chzn-select" name="reason_for[]" id="reason_for"
                                data-placeholder="Select Reason For" multiple="">
                            <option value=""></option>
                            <?php
                          
                            foreach ($reasonvals as $reasonval) {
                                if (in_array($reasonval['reason_id'], $reasonData)){ ?>
                                    <option selected value="<?php echo $reasonval['reason_id']; ?>"><?php echo $reasonval['reason_name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $reasonval['reason_id']; ?>"><?php echo $reasonval['reason_name']; ?></option>
                                <?php }
                            }
                            ?>
                        </select>
                        <span for="question_for" class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="reason_for">Agent Call Type
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <?php   $agentcalltypevals = $db->FetchToArray("agentcalltype_master", "*", "is_active = '1'");
                       // $db->GetEnumvalues("reason_master", "reason_name");
                            $agentcalltypeData = explode(",", $data['agentcalltype']);?>
                        <select class="chzn-select" name="agentcalltype_for[]" id="agentcalltype_for"
                                data-placeholder="Select Agent Call Type For" multiple="">
                            <option value=""></option>
                            <?php
                          
                            foreach ($agentcalltypevals as $agentcalltypeval) {
                                if (in_array($agentcalltypeval['agentcalltype_id'], $agentcalltypeData)){ ?>
                                    <option selected value="<?php echo $agentcalltypeval['agentcalltype_id']; ?>"><?php echo $agentcalltypeval['name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $agentcalltypeval['agentcalltype_id']; ?>"><?php echo $agentcalltypeval['name']; ?></option>
                                <?php }
                            }
                            ?>
                        </select>
                        <span for="question_for" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="question_name">Question
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['question_name']; ?>" type="text" name="question_name"
                                   id="question_name"
                                   class="" placeholder="ex. How was the call quality?"/>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="question_short_name">Question Short Name
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['question_short_name']; ?>" type="text" name="question_short_name"
                                   id="question_short_name"
                                   class="" placeholder="ex. HWTCQ"/>
                        </div>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label">Question Type
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <select class="chzn-select" data-placeholder="Select Question Type" name="option_type"
                                id="option_type">
                            <option value=""></option>
                            <optgroup label="Choose">
                                <option data-option=1
                                        value="select" <?= (isset($data['option_type']) && $data['option_type'] == 'select') ? "selected" : ""; ?>>
                                    Select
                                </option>
                                <option data-option=1
                                        value="radio" <?= (isset($data['option_type']) && $data['option_type'] == 'radio') ? "selected" : ""; ?>>
                                    Radio
                                </option>
                                <option data-option=1
                                        value="checkbox" <?= (isset($data['option_type']) && $data['option_type'] == 'checkbox') ? "selected" : ""; ?>>
                                    Checkbox
                                </option>
                            </optgroup>
                            <optgroup label="Input">
                                <option value="text" <?= (isset($data['option_type']) && $data['option_type'] == 'text') ? "selected" : ""; ?>>
                                    Text
                                </option>
                                <option value="textarea" <?= (isset($data['option_type']) && $data['option_type'] == 'textarea') ? "selected" : ""; ?>>
                                    Textarea
                                </option>
                            </optgroup>
                        </select>
                        <span for="option_type" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group" id="rowListing">
                    <label class="control-label">Question Type Value
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <div class="span12  table-responsive">
                            <table id='option_rows' class="table">
                                <thead>
                                <tr>
                                    <th>
                                        Option Value
                                    </th>
                                    <th>
                                        Weight
                                    </th>
                                    <th>
                                        Sort Order
                                    </th>

                                    <th>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (isset($optionValue) && count($optionValue) > 0) {
                                    foreach ($optionValue as $key => $valueData) { ?>
                                        <tr id="option_value_<?php echo $key; ?>">
                                            <input type="hidden" class="form-control"
                                                   value="<?= $valueData['option_value_id']; ?>"
                                                   name="option_value[<?= $key; ?>][option_value_id]"
                                                   id="option_value<?= $key; ?>">
                                            <td>
                                                <input type="text" class="form-control"
                                                       value="<?= $valueData['option_value']; ?>"
                                                       name="option_value[<?= $key; ?>][option_value]"
                                                       id="option_value<?= $key; ?>">
                                            </td>
                                            <td>
                                                <input type="tel" class="form-control"
                                                       value="<?= $valueData['weight']; ?>"
                                                       name="option_value[<?= $key; ?>][weight]"
                                                       id="weight<?= $key; ?>">
                                            </td>
                                            <td>
                                                <input type="tel" class="form-control"
                                                       value="<?= $valueData['sort_order']; ?>"
                                                       name="option_value[<?= $key; ?>][sort_order]"
                                                       id="sort_order<?= $key; ?>">
                                            </td>

                                            <td>
                                                <?php
                                                if ($key != 0) {
                                                    ?>
                                                    <a href='javascript:void(0);'
                                                       data-popup='custom-tooltip'
                                                       data-original-title="delete"
                                                       title="delete"
                                                       class='btn btn-mini btn-danger'
                                                       onclick='deleteRow(<?= $key; ?>,<?= $valueData['option_value_id']; ?>,<?= $id; ?>)'><i
                                                                class="icon-trash"></i></a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr id="option_value_0">
                                        <td>
                                            <input type="text" class="form-control" placeholder=""
                                                   name="option_value[0][option_value]" id="option_value0">
                                        </td>
                                        <td>
                                            <input type="tel" class="form-control" placeholder=""
                                                   name="option_value[0][weight]" id="weight0">

                                        </td>
                                        <td>
                                            <input type="tel" class="form-control" placeholder=""
                                                   name="option_value[0][sort_order]" id="sort_order0">
                                        </td>
                                        <td></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <div id='post_add_remove'>
                                <button type="button"
                                        class="btn btn-mini btn-success"
                                        onclick="AddSizeRow();"
                                        data-popup='custom-tooltip' data-original-title="add"
                                        title="add">
                                    <i class="icon-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="sort_order">Sort Order
                    </label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['sort_order']; ?>" type="tel" name="sort_order"
                                   id="sort_order"
                                   placeholder="ex. 1"/>
                        </div>
                    </div>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active"
                               id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : ""; ?>>
                        <span class="lbl"> Is Active</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_required"
                               id="is_required" <?php echo ($data['is_required'] == 1) ? "checked" : ""; ?>>
                        <span class="lbl"> Is Required</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_multiple"
                               id="is_multiple" <?php echo ($data['is_multiple'] == 1) ? "checked" : ""; ?>>
                        <span class="lbl"> Is Multiple</span>
                    </label>
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