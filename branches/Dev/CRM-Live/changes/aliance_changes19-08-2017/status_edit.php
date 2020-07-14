<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/chosen',
    'css/chosen.create-option'
);

$asset_js = array(

    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-form/jquery.form',
    'js/jquery.autosize-min',
    'js/chosen.jquery.min',
    'js/chosen.create-option.jquery'
);
$middle_breadcrumb = array('title' => 'Statuses', 'link' => 'status.php');
include "header.php";
$table = 'status_master';//change here
$table_id = 'status_id';//change here
$error = '';
$data = array();
$data_fields = $db->FetchTableField($table);
$statusType = $db->GetEnumvalues("status_master","status_type");
$statusId = (isset($_GET['status_id']) && !empty($_GET['status_id'])) ? intval($db->FilterParameters($_GET['status_id'])) : '';
if(isset($statusId) && $statusId!=''){
    $result = $db->FetchRow($table, $table_id, $statusId);
    $count = $db->CountResultRows($result);
    if($count > 0){
        $row_data = $db->MySqlFetchRow($result);
        foreach ($data_fields as $field){
            $data[$field] = $row_data[$field];
        }
    }else{
        $error = 'Invalid Record Or Record Not Found';
    }
}
if($error != ''){
    echo Utility::ShowMessage('Error: ', $error);
    include_once 'footer.php';
    exit;
}
$statusType = $db->GetEnumvalues("status_master","status_type");
?>
    <div class="page-header position-relative">
        <h4>Edit Status</h4>
    </div>
    <div class='row-fluid'>
        <div class="span12">
            <form class='form-horizontal' id='frm_status'>
                <div class="control-group">
                    <label for="status_name" class="control-label">Type</label>
                    <div class="controls">
                        <select name='status_type' id='status_type' data-placeholder="Select Type" class="chzn-select">
                            <option></option>
                            <?php
                            if(count($statusType) > 0){
                                foreach($statusType as $statusData) {
                                    $selected = ($statusData == $data['status_type']) ? "selected" : "";
                                    ?>

                                    <option value="<?php echo $statusData; ?>" <?php echo $selected; ?>><?php echo ucwords($statusData); ?></option>
                                <?php
                                }
                            }
                            ?>
                        </select>
                        <span for="status_type" class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group" id="activity_type_id">
                    <label class="control-label" for="activity_type">Activity Type<small class="text-error"> *</small></label>
                    <div class="controls">

                        <select id="activity_type" name="activity_type" class="chzn-select" data-placeholder="Select Activity Type">
                            <option></option>
                            <?php
                            if(count($activityType) > 0){
                                foreach($activityType as $activityData) { ?>
                                    <option <?php echo ($activityData == $data['activity_type']) ? "selected" : ""; ?> value="<?php echo $activityData; ?>"><?php echo ucwords($activityData); ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <span for="activity_type" class="help-inline"></span>
                        <input type="hidden" name='action' value="<?php echo $action; ?>"/>
                    </div>
                </div>
                <div class="control-group">
                    <label for="status_name" class="control-label">Status Name</label>
                    <div class="controls">
                        <input type="text" name='status_name' id='status_name' placeholder="Status Name" value="<?php echo $data['status_name']; ?>"/>
                        <input type="hidden" name='status_id' id='status_id' value="<?php echo $statusId; ?>"/>
                    </div>
                </div>


                <div class="control-group">
                    <label for="sort_order" class="control-label">Sort Order</label>
                    <div class="controls">
                        <input type="text" name='sort_order' value="<?php echo $data['sort_order']; ?>" id='sort_order' placeholder="ex.1"/>
                    </div>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active" id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Active</span>
                    </label>
                </div>
                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_default" id="is_default" <?php echo ($data['is_default'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Is Default</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_close" id="is_close" <?php echo ($data['is_close'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Is Closed</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="cal_price" id="cal_price" <?php echo ($data['cal_price'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Complete</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_callback" id="is_callback" <?php echo ($data['is_callback'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Is Callback</span>
                    </label>
                </div>


                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_show_to_kc" id="is_show_to_kc" <?php echo ($data['is_show_to_kc'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Forward to KC</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_email_send" id="is_email_send" <?php echo ($data['is_email_send'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Send Email</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_sms_send" id="is_sms_send" <?php echo ($data['is_sms_send'] == 1) ? "checked" : "";?>>
                        <span class="lbl"> Send SMS</span>
                    </label>
                </div>


                <div class="form-actions">
                    <button type="submit" class="btn btn-small btn-info">
                        <i class="icon-ok bigger-110"></i> Save
                    </button>
                    <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please wait...</div>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function(){
            $(".chzn-select").chosen({
                allow_single_deselect: true,
                no_results_text: "Oops, nothing found!",
            });
            jQuery.validator.addMethod("isString", function(value, element) {
                return /^[a-zA-Z() ]+$/.test(value);
            }, "please enter text only");



            $("#status_type").change(function(){
                var activityType = $(this).val();
                if(activityType == 'Activity'){
                    $("#activity_type_id").show();
                } else {
                    $("#activity_type_id").hide();
                }
            });
            if (jQuery().validate) {
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };

                // Company type validateion code
                $("#frm_status").validate({
                    rules:{
                        status_name:{required: true},
                        status_type:{required: true},
                        activity_type:{required: "#activity_type:visible"}
                    },
                    messages:{
                        status_name:{required: 'Please enter status name'},
                        status_type:{required: 'Please select type'},
                        activity_type:{required: 'Please select type'}
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
                            url: 'control/status_edit.php?act=edit',
                            type:'post',
                            beforeSubmit: function (formData, jqForm, options) {
                                $(e).find('button').hide();
                                $('#loader').show();
                            },
                            complete: function(){
                                $('#loader').hide();
                                $(e).find('button').show();
                            },
                            dataType: 'json',
                            clearForm: false,
                            success: function (resObj, statusText) {

                                if(resObj.success){
                                    $('.chzn-select').trigger('liszt:updated');
                                    $('.chzn-select').trigger('choosen:updated');
                                    $(e).clearForm();
                                    showGritter('success',resObj.title,resObj.msg);
                                    setTimeout(function(){location.reload(true);},3000);
                                } else{
                                    showGritter('error',resObj.title,resObj.msg);
                                }
                            }
                        });
                    }
                });


            }
            $("#status_type").change();
        });
    </script>
<?php
include_once 'footer.php';
?>