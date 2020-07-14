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
$statusType = $db->GetEnumvalues("status_master","status_type");
$activityType = $db->GetEnumvalues("activity_master","activity_type");
?>
    <div class="page-header position-relative">
        <h4>Add Status</h4>
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
                                foreach($statusType as $statusData) { ?>
                                    <option selected value="<?php echo $statusData; ?>"><?php echo ucwords($statusData); ?></option>
                                 <?php
                                }
                            }
                            ?>
                        </select>
                        <span for="status_type" class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group" id="activity_type_id">
                    <label class="control-label" for="activity_type">Activity Type</label>
                    <div class="controls">

                        <select  name="activity_type" id="activity_type" class="chzn-select" data-placeholder="Select Activity Type">
                            <option></option>
                            <?php
                            if(count($activityType) > 0){
                                foreach($activityType as $activityData) { ?>
                                    <option value="<?php echo $activityData; ?>"><?php echo ucwords($activityData); ?></option>
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
                        <input type="text" name='status_name' id='status_name' placeholder="Status Name"/>
                    </div>
                </div>
                <div class="control-group">
                    <label for="sort_order" class="control-label">Sort Order</label>
                    <div class="controls">
                        <input type="text" name='sort_order' id='sort_order' placeholder="ex.1" value=""/>
                    </div>
                </div>


                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <span class="lbl"> Active</span>
                    </label>
                </div>
                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_default" id="is_default">
                        <span class="lbl"> Is Default</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="cal_price" id="cal_price">
                        <span class="lbl"> Complete</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_close" id="is_close">
                        <span class="lbl"> Is Closed</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_callback" id="is_callback">
                        <span class="lbl"> Is Callback</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_show_to_kc" id="is_show_to_kc">
                        <span class="lbl"> Forward to KC</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_email_send" id="is_email_send">
                        <span class="lbl"> Send Email</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_sms_send" id="is_sms_send">
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
            });
            $("#status_type").change(function(){
                var activityType = $(this).val();
                if(activityType == 'Activity'){
                    $("#activity_type_id").show();
                } else {
                    $("#activity_type_id").hide();
                }
            });

            $("#type").change(function(){
                var statusTypeId = $(this).val();
                $.ajax({
                    url: 'control/status.php?act=accountcheck',
                    data : { status_type_id : statusTypeId },
                    type:'post',
                    dataType: 'json',
                    beforeSend: function(){
                        $('#status_type_loader').show();
                    },
                    complete: function(){
                        $('#status_type_loader').hide();
                    },
                    success: function(resp){
                        if(resp.success == true){
                            $("#account_status_id").closest("div.control-group").removeClass("hide");
                            $('#account_status_id').html(resp.data);
                            doChosen();
                        } else {
                            $("#account_status_id").closest("div.control-group").addClass("hide");
                        }

                    }
                });
            });
            function doChosen(){
                $("#account_status_id").chosen({
                    allow_single_deselect: true,
                    create_option_text: 'Create account status',
                    create_option: function(term){
                        var chosen = this;
                        $.post('control/status_add.php?act=add', {status_name: term,type:"account"}, function(data){
                            if(data.success == true){
                                chosen.append_option({
                                    value: data.value,
                                    text: data.text,
                                });
                             } else {
                                showGritter('error',data.title,data.msg);
                            }
                        },"json");
                    },
                    persistent_create_option: true,
                    skip_no_results: true
                });
            }

            jQuery.validator.addMethod("isString", function(value, element) {
                return /^[a-zA-Z() ]+$/.test(value);
            }, "please enter text only");
            if (jQuery().validate) {
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };

                // Company type validateion code
                $("#frm_status").validate({
                    rules:{
                        status_name:{required: true},
                        account_status_id:{required: "#account_status_id:visible"},
                        status_type:{required: true},
                        status_order:{required: true,digits:true},
                        activity_type:{required: "#activity_type:visible"}
                    },
                    messages:{
                        status_name:{required: 'Please enter status name'},
                        account_status_id:{required: 'Please select related account status name'},
                        status_type:{required: 'Please select type'},
                        status_order:{required: "Please enter status order"},
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
                            url: 'control/status_add.php?act=add',
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
            $("#activity_type").change();
        });
    </script>
<?php
include_once 'footer.php';
?>