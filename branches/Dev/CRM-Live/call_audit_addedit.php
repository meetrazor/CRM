<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/1/2019
 * Time: 4:55 PM
 */

$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
     'css/chosen',
    'css/chosen.create-option',
    'css/bootstrap-timepicker',
    'css/bootstrap-datetimepicker.min',
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.create-option.jquery',
    'js/chosen.jquery.min',
    'js/jquery.autosize-min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/bootstrap-datetimepicker.min',
    'js/jquery.maskedinput.min',
);

$middle_breadcrumb = array('title' => 'Call Audit', 'link' => 'call_audit.php');
include_once 'header.php';
$table = 'call_audit';
$table_id = 'call_audit_id';
$action = 'add';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field) {
    $data[$field] = '';
}

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';

$mainTable = array("question_master as qm",array("qm.question_id,qm.question_for,qm.question_name,qm.agentcalltype,qm.reason,qm.option_type,qm.sort_order,qm.is_required,qm.is_active,qm.is_multiple"));
$joinTable = array(
    array("left","question_option_value as qov","qm.question_id = qov.question_id",
        array("qov.option_value","qov.sort_order","qov.weight","option_value_id"))
);

$questionQ = $db->JoinFetch($mainTable,$joinTable,"qm.is_active = 1",array("qm.sort_order" => "asc","qov.sort_order" => "asc"));
$questions = $db->FetchToArrayFromResultset($questionQ);

$questionOptionArray = array();
$questionAnswer = array();
if(is_array($questions) && count($questions)) {
    foreach ($questions as $key => $questionData) {
        $questionOptionArray[$questionData['question_id']]['question_data'] = $questionData;
        $questionOptionArray[$questionData['question_id']]['options'][$key]['option_value'] = $questionData['option_value'];
        $questionOptionArray[$questionData['question_id']]['options'][$key]['option_value_id'] = $questionData['option_value_id'];
        $questionOptionArray[$questionData['question_id']]['options'][$key]['question_id'] = $questionData['question_id'];
    }
}

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
        $questionAnswer = $db->FetchToArray("call_audit_answer",array("call_audit_answer_id","question_id","option_value",
            "option_value_id"),"call_audit_id = {$id}");
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
$questionAnswerQuestionWise = array();
if(is_array($questionAnswer) and count($questionAnswer) > 0){
    foreach ($questionAnswer as $key => $questionAnswerData) {
        $questionAnswerQuestionWise[$questionAnswerData['question_id']]['selected'][$key] = ($questionAnswerData['option_value_id'] != 0) ? $questionAnswerData['option_value_id'] : $questionAnswerData['option_value'];
    }
}
$userDd = $db->CreateOptions("html", "admin_user au", array("user_id", "CONCAT(au.first_name,' ',au.last_name) name "), $data['user_id'], array("user_id" => "asc"),"is_active = '1' and user_type = ".UT_TC."");
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function () {

            $(".chzn-select").chosen({
                allow_single_deselect: true,
            });

            $('.play_time').timepicker({
                minuteStep: 1,
                showSeconds: true,
                showMeridian: false,
            });

            $('.date-picker').datepicker({
                todayHighlight : false,
                endDate: '-1d',
                orientation: 'top',
                autoclose: true,
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

            $('#btn_cancel').click(function () {
                form = $(this).closest('form');
                form.find('div.control-group').removeClass("success error");
                form.find('span.help-inline').text("");
                form.clearForm();

            });


            if (jQuery().validate) {
                var e = function (e) {
                    $(e).closest(".control-group").removeClass("success");
                };

                $("#form_add").validate({
                    ignore: [],
                    rules: {
                        user_id: {
                            required: true,
                            // checkValidAudit: true,
                        },
//                        agentcalltype_for: {
//                            required: true,
//                            // checkValidAudit: true,
//                        },
                        audit_date: {
                            required: true,
                            validAuditDate: true,
                            // checkValidAudit: true,
                        },
                        audit_time: {
                            required: true,
                        },
                        mobile: {
                            required: true,
                            digits: true,
                            maxlength: 10,
                            // "remote": {
                            //     url: 'control/call_audit_addedit.php?act=check_audit',
                            //     data: { 'user_id': user_id, 'audit_date': audit_date,'mobile': mobile },
                            //     async: false,
                            //     type: "post"
                            // },
                            // checkValidAudit: true,
                        }
                    },

                    messages: {
                        user_id: {
                            required: 'Please select user',
                        },
//                        agentcalltype_for: {
//                            required: 'Please select agent call type',
//                        },
                        audit_date: {
                            required: 'Please enter audit date',
                        },
                        audit_time: {
                            required: 'Please enter audit time',
                        },
                        mobile: {
                            required: 'Please enter mobile number',
                            maxlength: 'Max leangth is 10 digits only',
                            // remote: 'Call audit is already done....'
                        }
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
                            url: 'control/call_audit_addedit.php?act=addedit',
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

            // $(document).click(function () {
            //     var user_id = $('#user_id').children("option:selected").val();
            //     var audit_date = $('#audit_date').val();
            //     var mobile = $('#mobile').val();
            //     $.ajax({
            //         url: 'control/call_audit_addedit.php?act=check_audit',
            //         type: 'post',
            //         dataType: 'json',
            //         data: { user_id: user_id, audit_date: audit_date,mobile: mobile },
            //         success: function (resp) {
            //             if(resp.success) {
            //                 $('#exist').text(resp.msg);
            //                 $('#exist').show();
            //             }else{
            //                 $('#exist').hide();
            //             }
            //         }
            //     });
            //
            // });


            jQuery.validator.addMethod("checkValidAudit", function () {
                var user_id = $('#user_id').children("option:selected").val();
                var audit_date = $('#audit_date').val();
                var mobile = $('#mobile').val();
                $.ajax({
                    url: 'control/call_audit_addedit.php?act=check_audit',
                    type: 'post',
                    dataType: 'json',
                    data: { user_id: user_id, audit_date: audit_date,mobile: mobile },
                    success: function (resp) {
                        if(resp.success) {
                            console.log(resp.msg);
                            // $('#exist').text(resp.msg);
                            // $('#exist').show();
                        }else{
                            console.log(resp.msg);
                            // $('#exist').hide();
                        }
                    }
                });
            },"Call audit is done for given details");


            jQuery.validator.addMethod("validAuditDate", function (value) {
                var currVal = value;
                if (currVal == '')
                    return false;

                var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/; //Declare Regex
                var dtArray = currVal.match(rxDatePattern); // is format OK?

                if (dtArray == null)
                    return false;

                //Checks for mm/dd/yyyy format.
                dtDay = dtArray[1];
                dtMonth = dtArray[3];
                dtYear = dtArray[5];

                if (dtMonth < 1 || dtMonth > 12)
                    return false;
                else if (dtDay < 1 || dtDay > 31)
                    return false;
                else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
                    return false;
                else if (dtMonth == 2) {
                    var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
                    if (dtDay > 29 || (dtDay == 29 && !isleap))
                        return false;
                }
                return true;
            }, 'Please enter a valid audit date');

            $('#mobile').mask('9999999999');
                
//                 $('select.chzn-select').trigger("liszt:updated");
//                $('select.chzn-select').trigger("chosen:updated");
                
        });


    </script>

    <div class='row-fluid'>
        <div class="span12">
            <form class="form-horizontal" id="form_add" enctype="multipart/form-data">

                <div class="control-group">
                    <label for="user_id" class="control-label">User <small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="hidden" value="<?php echo $id; ?>" id="call_audit_id" name="call_audit_id">
                        <select id="user_id" name="user_id" data-placeholder="Select User" class="chzn-select">
                            <option></option>
                            <?php echo $userDd; ?>
                        </select>
                        <i id='user_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="user_id" class="help-inline"></span>
                    </div>
                </div>

                <div class='control-group'>
                    <label class='control-label' for='audit_date'>Audit date
                        <small class="text-error"> *</small>
                    </label>
                    <div class='controls'>
                        <div class='row-fluid input-append'>
                            <input class='input-small date-picker' data-placement='top' type='text'
                                   placeholder='audit date'
                                   name='audit_date' id="audit_date" data-date-format='dd-mm-yyyy'
                                   readonly='readonly'
                                   value="<?php echo ($data['audit_date'] != '0000-00-00' && $data['audit_date'] != '') ? core::YMDToDMY($data['audit_date']) : ""; ?>">
                            <span class='add-on'><i class='icon-calendar'></i></span>
                            <span for='audit_date' class='help-inline'></span>
                        </div>
                    </div>
                </div>

                <div class='control-group'>
                    <label for="audit_time" class="control-label">Audit Time
                        <small class="text-error"> *</small>
                    </label>
                    <div class='controls bootstrap-timepicker'>
                        <div class="input-append">
                            <input id="audit_time"  name="audit_time" type="text" class="play_time span6" />
                            <span class="add-on"><i class="icon-time"></i></span>
                            <span for="audit_time" class="help-inline"></span>
                        </div>
                    </div>
                </div>


                <div class="control-group">
                    <label for="mobile" class="control-label">Mobile <small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" name="mobile" id="mobile" value="<?php echo $data['mobile']; ?>" placeholder="Mobile number">
                        <span for="mobile" class="help-inline"></span>
                    </div>
<!--                    <span id="exist" style="color:red"> </span>-->
                </div>

                <div class="control-group">
                    <label for="audio_file" class="control-label">Upload Audio</label>
                    <div class="controls">
<!--                        <input multiple accept=".mp3" type="file" id="audio_file" name="audio_file[]" placeholder="File" class="upload">-->
                        <input multiple accept=".mp3" type="file" id="audio_file" name="audio_file" placeholder="File" class="upload">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="reason_for">Reason
<!--                        <small class="text-error"> *</small>-->
                    </label>
                    <div class="controls">
                        <?php   $reason_masterVals = $db->FetchToArray("reason_master", "*", "is_active = '1'");
//                        print_r($data['agentcalltype_id']);
                       // $db->GetEnumvalues("reason_master", "reason_name");
//                            $agentcalltypeData = explode(",", $data['agentcalltype']);?>
                        <select class="chzn-select" name="reason_for" id="reason_for"
                                data-placeholder="Select reason" >
                            <option value=""></option>
                            <?php
                          
                            foreach ($reason_masterVals as $reason_masterVal) {
                                if ($reason_masterVal['reason_id']==$data['reasonid']){ ?>
                                    <option selected value="<?php echo $reason_masterVal['reason_id']; ?>"><?php echo $reason_masterVal['reason_name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $reason_masterVal['reason_id']; ?>"><?php echo $reason_masterVal['reason_name']; ?></option>
                                <?php }
                            }
                            ?>
                        </select>
                        <span for="reason_for" class="help-inline"></span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="reason_for">Agent Call Type
<!--                        <small class="text-error"> *</small>-->
                    </label>
                    <div class="controls">
                        <?php   $agentcalltypevals = $db->FetchToArray("agentcalltype_master", "*", "is_active = '1'");
//                        print_r($data['agentcalltype_id']);
                       // $db->GetEnumvalues("reason_master", "reason_name");
//                            $agentcalltypeData = explode(",", $data['agentcalltype']);?>
                        <select class="chzn-select" name="agentcalltype_for" id="agentcalltype_for"
                                data-placeholder="Select Agent Call Type For" >
                            <option value=""></option>
                            <?php
                          
                            foreach ($agentcalltypevals as $agentcalltypeval) {
                                if ($agentcalltypeval['agentcalltype_id']==$data['agentcalltype_id']){ ?>
                                    <option selected value="<?php echo $agentcalltypeval['agentcalltype_id']; ?>"><?php echo $agentcalltypeval['name']; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $agentcalltypeval['agentcalltype_id']; ?>"><?php echo $agentcalltypeval['name']; ?></option>
                                <?php }
                            }
                            ?>
                        </select>
                        <span for="agentcalltype_for" class="help-inline"></span>
                    </div>
                </div>
<?php // print_r($data);?>
                <div class="control-group questionDiv" <?php if(!isset($data['agentcalltype_id']) && $data['agentcalltype_id']=="" && $data['call_audit_id']==""){?>style="display: none;"<?php }else if($data['call_audit_id']!=""){?>style="display: block;"<?php }else{?> style="display: none;<?php }?>>
                    <label for="question" class="control-label">Question <small class="text-error"> *</small></label>
                    <div class="controls">
                        <?php
                        
                        $style = "";
//                        print_r($questionOptionArray);
                        if(count($questionOptionArray) > 0){
                            echo "<table class='table'>";
                            foreach ($questionOptionArray as $questionId => $questionOptionData){
                                $selectedOption = array_key_exists($questionId,$questionAnswerQuestionWise) ? $questionAnswerQuestionWise[$questionId]['selected'] : "";
                                $dataagentCall = ($questionOptionData['question_data']['agentcalltype']!="")?$questionOptionData['question_data']['agentcalltype']:"0";
                                $datareasonCall = ($questionOptionData['question_data']['reason']!="")?$questionOptionData['question_data']['reason']:"0";
                                
                                 if (isset($data['agentcalltype_id']) && $data['agentcalltype_id']!=0 && $data['call_audit_id']!="")
                                 { 
                                        if(strpos($dataagentCall, $data['agentcalltype_id']) !== false) {
                                       $style="style='display:block;'";
                                   }else{
                                       $style="style='display:none;'";
                                   }
                                 }else if($data['call_audit_id']=="")
                                 {
                                     $style="style='display:none;'";
                                 }
                                
                                echo "<tr data-agentcall=".$dataagentCall." data-reasoncall=".$datareasonCall."  ".$style."><th>".core::StripAllSlashes($questionOptionData['question_data']['question_name'])."</th></tr>";
                                echo "<tr data-agentcall=".$dataagentCall." data-reasoncall=".$datareasonCall." ".$style."><td>".Utility::getFieldHtml($questionOptionData['question_data']['option_type'],$questionOptionData['options'],$selectedOption)."</td></tr>";
                            }
                            echo "</table>";
                        }
                        ?>
                    </div>
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
<?php if(isset($data['agentcalltype_id']) && $data['agentcalltype_id']!="" && $data['agentcalltype_id']!=0){?>
<script type="text/javascript">
    
window.setInterval(function() {//alert('dfs');
  $('#agentcalltype_for').trigger('change');
}, 1000);
</script>
<?php }?>
<?php 
include_once 'footer.php';
?>
<script type="text/javascript">
//function filterque(val)
//{ console.log(val);
//    
//    // str.indexOf('apple') != -1
//}
 $("#agentcalltype_for").chosen().change(function(){
        var id = $(this).val();
        
//        alert(id);
        $('.table tr').each(function (i, row)
            {

                var $row = $(row);
                var agenttype ="";
                var reasonfor ="";
                var reasonforData ="";
               
                 agenttype =  String($row.data('agentcall')); //alert(agenttype);
                 reasonforData =  String($row.data('reasoncall')); //alert(agenttype);
                 reasonfor = $('#reason_for').val();
                 var nameArr = agenttype.split(',');
//                 alert(nameArr.length);
                  if(agenttype!=0){
//                      if(nameArr.length)
                if(agenttype.indexOf(id) != -1 && reasonfor!="" && reasonforData.indexOf(reasonfor) != -1)
                {
                    $row.show();
                    $('.questionDiv').show();
                }else
                {
                   $row.find('input').removeAttr('required');
                     $row.hide();  
                }
            }else
                {
                    $row.find('input').removeAttr('required');
                     $row.hide();
//                    $row.prop('required',false);
                }
            });
    });
 $("#reason_for").chosen().change(function(){
        var id = $(this).val();
        
//        alert(id);
        $('.table tr').each(function (i, row)
            {

                var $row = $(row);
                var agenttype ="";
                var reasonfor ="";
                var reasonforData ="";
                var agentcallfor ="";
               
                 agenttype =  String($row.data('agentcall')); //alert(agenttype);
                 reasonforData =  String($row.data('reasoncall')); //alert(agenttype);
                 agentcallfor = $('#agentcalltype_for').val();
                 var nameArr = agenttype.split(',');
//                 alert(nameArr.length);
                  if(agenttype!=0){
//                      if(nameArr.length)
                if(reasonforData.indexOf(id) != -1 && agenttype!="" && agenttype.indexOf(agentcallfor) != -1)
                {
                    $row.show();
                    $('.questionDiv').show();
                }else
                {
                   $row.find('input').removeAttr('required');
                     $row.hide();  
                }
            }else
                {
                    $row.find('input').removeAttr('required');
                     $row.hide();
//                    $row.prop('required',false);
                }
            });
    });

</script>
