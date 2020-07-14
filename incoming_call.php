<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    //'css/chosen',
    'css/chosen.create-option',
    'css/bootstrap-timepicker',
    'css/bootstrap-datetimepicker.min',
    'css/colorbox',
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
    'js/bootstrap-datetimepicker.min',
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
    'js/jquery.colorbox-min',
);
// http://52.66.187.187/crm/incoming_call.php?phonenumber=9227231501&agentname=MANISH&token=9654ads987415ada
$middle_breadcrumb = array('title' => 'Activities', 'link' => 'activity.php');
include_once 'header.php';
$table = 'activity_master';
$table_id = 'activity_id';
$action = 'add';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field) {
    $data[$field] = '';
}
$id = '';
$mobile= (isset($_GET['phonenumber']) && !empty($_GET['phonenumber'])) ? $db->FilterParameters($_GET['phonenumber']) : '';
$agentCode = (isset($_GET['agentname']) && !empty($_GET['agentname'])) ? $db->FilterParameters($_GET['agentname']) : '-999';
$mobile = strrev(substr(strrev($mobile),0,10));
$user_id = $db->FetchCellValue("admin_user","user_id","agent_code = '{$agentCode}'");
// check whether mobile exist or not
if($mobile != ''){
    $prospectCondition = "1=1";

    $mobileCheck = is_array($mobile) ? implode("','",$mobile)  : $mobile;
    $prospectCondition .= " and pc1.contact in ('$mobileCheck')";

    $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
    $joinTable = array(
        array("left","prospect_contact as pc1","pc1.prospect_id = pm.prospect_id")
    );
    $prospectRes = $db->JoinFetch($mainTable,$joinTable,$prospectCondition,null,array(0,1),"pm.prospect_id");
    $prospectCount = $db->CountResultRows($prospectRes);
            // if mobile exist that get prospect id other wise create new prospect and assign call to it
    if($prospectCount > 0) {
        $prospectRow = $db->FetchRowInAssocArray($prospectRes);
        $prospectId = $prospectRow['prospect_id'];
    } else {
        $prospect_data = array();
        $prospect_data['campaign_id'] = SIDBI_CAMPAIGN_ID;
        $prospect_data = array_merge($prospect_data,$db->TimeStampAtCreate(0));
        $prospectId = $db->Insert("prospect_master", $prospect_data, true);

        // insert call for prospect
        $db->Insert("prospect_queue",array(
                "prospect_id" => $prospectId,
                "campaign_id" => SIDBI_CAMPAIGN_ID,
                "created_at"=>DATE_TIME_DATABASE,
                "created_by"=>$user_id,
                "updated_at"=>DATE_TIME_DATABASE,
                "updated_by"=>$user_id,
                "date_queued"=>DATE_TIME_DATABASE,
            )

        );

        // insert mobile number for prospect
        $phoneNumber = is_array($mobile) ? $mobile  : array($mobile);
        if(count($phoneNumber) > 0){
            foreach($phoneNumber as $pnkey => $number) {
                $primary = ($pnkey == 0) ? 1 : 0;
                $phoneNumberData = array(
                    "prospect_id" => $prospectId,
                    "contact" => $number,
                    "contact_type" => "phone",
                    "is_wrong" =>0,
                    "is_primary" =>$primary
                );
                $db->Insert('prospect_contact', $phoneNumberData);
            }
        }
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
        $dispositionData = $db->FetchRowForForm("disposition_master","*","disposition_id = '{$data['disposition_id']}'");
        if($dispositionData['is_meeting'] == 1 && $dispositionData['is_callback'] == 1){
            echo Utility::ShowMessage('Error: ', "You can not edit records");
            include_once 'footer.php';
            exit;
        }
    } else {
        $error = 'Invalid Record Or Record Not Found';
    }
} else {
    $error = 'Invalid Record Or Record Not Found';
}
$activityOn = $data['activity_on'];
$prospectId = (isset($_GET['prospect_id']) && !empty($_GET['prospect_id'])) ? intval($db->FilterParameters($_GET['prospect_id'])) : $prospectId;
//$token = core::GenRandomStr(6);
$nextProspectData = Utility::getNextProspect($login_id);
$nextProspectId = (isset($nextProspectData['prospect_id']) && $nextProspectData['prospect_id'] != '') ? $nextProspectData['prospect_id'] : "";
$prospectQueueId = (isset($nextProspectData['prospect_queue_id']) && $nextProspectData['prospect_queue_id'] != '') ? $nextProspectData['prospect_queue_id'] : "";
//$callData = Utility::getUpcomingCall($login_id);
//echo "here".$nextProspectId;
//echo $getUpcomingCall['follow_up_date_time'];
if (isset($id) && $id != '') {
    if ($error != '') {
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
if ($prospectId == '') {
//    $message = "No New Call";
//    if(isset($callData['follow_up_date_time']) && $callData['follow_up_date_time'] != ''){
//        $message =  "Up Comping Call on ".core::YMDToDMY($callData['follow_up_date_time'],true)."&nbsp<b><a href='activity_addedit.php?prospect_id=".$callData['type_id']."&token=".$token."&type=call'>Call Now</a></b>";
//    }
    //echo Utility::ShowMessage('Info: ',$message ,"info");
    $prospectId = $nextProspectId;
}
$activityType = "call";
if($activityType == ''){
    echo Utility::ShowMessage('Error: ', "Activity Type missing");
    include_once 'footer.php';
    exit;
}
$dispositionDd = $db->CreateOptions("html","disposition_master",array("disposition_id","disposition_name"),$data['disposition_id'],array("disposition_name"=>"asc"));
//$prospectDd = $db->CreateOptions("html","prospect_master",array("prospect_id","concat(first_name,' ',last_name)"),$prospectId,array("prospect_name"=>"asc"),"prospect_id = '{$prospectId}'");
$prospectDd = $db->CreateOptions("html","prospect_master",array("prospect_id","concat(first_name,' ',last_name)"),"",array("prospect_name"=>"asc"),null,array(0,10));
$lastUser = $db->FetchCellValue("activity_users","type_id","activity_id = '{$id}' and is_latest = 1 and user_type = 'tc'");
//$userDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),$lastUser,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = ".UT_BD."");
$mainTable = array("activity_master as am",array("am.*"));
$joinTable = array(
    array("left","disposition_master as dm","dm.disposition_id = am.disposition_id",array("dm.disposition_name"))
);
$activityHistoryQ = $db->JoinFetch($mainTable,$joinTable,"am.type_id = '{$prospectId}' and am.activity_type = '{$type}'",array("am.created_at"=>"desc"));
$activityHistory = $db->FetchToArrayFromResultset($activityHistoryQ);
//$checkAgentLogin = Utility::viciErrorMessage(Utility::pauseCall($agentCode));
if($agentCode == ''){
   // echo Utility::ShowMessage('Error: ', "Kindly contact admin");
   // include_once 'footer.php';
   // exit;
}
//if($checkAgentLogin['success'] == false){
//    echo Utility::ShowMessage('Error: ', $checkAgentLogin['message']);
//    include_once 'footer.php';
//    exit;
//}
?>
<style>
    .help-inline{
        color: #D16E6C;
    }
</style>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        var Clock = '';

        function pad (str, max ) {
            if(typeof max === 'undefined'){
                max = 2;
            }
            str = str.toString();
            return str.length < max ? pad("0" + str, max) : str;
        }

        $(function () {


            var colorbox_params = {
                width:"90%",
                height:"100%",
                iframe: true,
                reposition:true,
                scalePhotos:true,
                scrolling:true,
                previous:'<i class="icon-arrow-left"></i>',
                next:'<i class="icon-arrow-right"></i>',
                close:'&times;',
                current:'{current} of {total}',
                maxWidth:'100%',
                maxHeight:'100%',
                onOpen:function(){
                    document.body.style.overflow = 'hidden';
                },
                onClosed:function(){
                    document.body.style.overflow = 'auto';
                    typeRefresh();
                    typeDetails();
                }
            };

            $('[data-rel="prospect_edit"]').colorbox(colorbox_params);
            $('[data-rel="prospect_add"]').colorbox(colorbox_params);

            $('[data-rel=tooltip]').tooltip();

            $(".form_datetime").datetimepicker({
                format: "yyyy-mm-dd hh:ii",
                autoclose: true,
                todayBtn: true,
                showMeridian: true,
                startDate: "<?php echo date("Y-m-d H:i") ?>",
            });


            Clock = {
                totalSeconds: 0,

                start: function () {
                    var self = this;

                    this.interval = setInterval(function () {
                        self.totalSeconds += 1;
                        $("#min").text(pad(Math.floor(self.totalSeconds / 60 % 60),2));
                        $("#sec").text(pad(parseInt(self.totalSeconds % 60)));
                    }, 1000);
                },

                pause: function () {
                    console.log(this);
                    $("#pauseCallTimer").hide();
                    $("#resumeCallTimer").show();
                    clearInterval(this.interval);
                    delete this.interval;
                },

                resume: function () {
                    $("#resumeCallTimer").hide();
                    $("#pauseCallTimer").show();
                    if (!this.interval) this.start();
                },

                stop: function () {
                    clearInterval(this.interval);
                    delete this.interval;
                    $("#stopButton").hide();
                    $("#duration_minute").val($("#min").text()).attr("readonly",true);
                    $("#duration_second").val($("#sec").text()).attr("readonly",true);
                    $("#resumeCallTimer").hide();
                    $("#pauseCallTimer").hide();
                    $("#resetCallTimer").fadeIn();
                },

                reset: function () {
                    $("#min").text("00");
                    $("#sec").text("00");
                    $("#duration_minute").val('');
                    $("#duration_second").val('');
                    clearInterval(this.interval);
                    this.totalSeconds = 0;
                    $("#stopButton").hide();
                    $("#pauseCallTimer").show();
                    $("#timerButton").hide();
                    $("#startButton").fadeIn();
                }
            };


            $('#startButton').click(function () {
                var number = $("#prospect_number").html();
                if(number == ''){
                    showGritter('error', "Phone number", "Phone number not available");
                } else {
                    $(this).hide();
                    $("#stopButton").fadeIn();
                    $("#timerButton").show();
                    pauseCall();
                    dialCall();
                    Clock.start();
                }
            });
            $('#stopButton').click(function () {
                hangCall();
            });
//            $('#stopButton').click(function () { Clock.pause(); });
//            $('#resumeButton').click(function () { Clock.resume(); });

            $(".chzn-select").chosen({
                allow_single_deselect: true,
            });

            $("#ap_dd").hide();

            $('.date-picker').datepicker({
                orientation: 'top',
                autoclose: true,
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

            $('#call_time').timepicker({
                minuteStep: 15,
                showSeconds: false,
                showMeridian: true,
            });


            $("#disposition_id").change(function(){
                var disposition_id = $(this).val();
                $.ajax({
                    url: 'control/disposition.php?act=checkdis',
                    data : { disposition_id : disposition_id,prospect_id: $("#type_id").val() },
                    type:'post',
                    dataType: 'json',
                    beforeSend: function(){
                        $('#disposition_loader').show();
                    },
                    complete: function(){
                        $('#city_loader').hide();
                    },
                    success: function(resp){
                        $("#ap_id").html(resp.ap_list);
                        $("#ap_id").trigger("chosen:updated");
                        if(resp.is_callback == 1){
                            $("#follow_up_dd").show();
                        } else {
                            $("#follow_up_dd").hide();
                        }
                        if(resp.is_meeting == 1){
                            $(".user_dd").show();
                        } else {
                            $(".user_dd").hide();
                        }

                    }
                });
            });

            $("input[name=activity_on]").click(function(){
                var activityOn = $(this).val();
                if(activityOn == 'ap'){
                    $("#ap_dd").show();
                } else {
                    $("#ap_dd").hide();
                }
            });

            <?php if($userType != UT_ADMIN) { ?>
            $("#type_id option:not(:selected)").attr('disabled','disabled');
            $("#type_id").trigger("chosen:updated");
            <?php } ?>



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


            var clkBtn = "";
            $('button[type="submit"]').click(function(evt) {
                clkBtn = evt.target.id;
                $("#button_click").val(clkBtn);
            });


            if (jQuery().validate) {
                var e = function (e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                jQuery.validator.addMethod("zipcode", function (value, element) {
                    return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
                }, "Please provide a valid pin code.");

                $("#frm_activity").validate({
                    rules: {
                        activity_name: { required: true },
                        remarks: { required: true },
                        start_date_time: { required: true},
                        type_id: { required: true},
                        follow_up_date_time: { required: "#follow_up_date_time:visible"},
                        ap_id: { required: "#ap_id:visible"},
                        duration_minute: { required: true,digits:true,max:60},
                        duration_second: { required: true,digits:true,max:60},
                        call_time: { required: true},
                        disposition_id: { required: true},
                        activity_on: { required: ".user_dd:visible"}
                    },

                    messages: {
                        activity_name: { required: "please enter activity name" },
                        remarks: { required: "please enter notes" },
                        start_date_time: { required: "please enter start date"},
                        type_id: { required: "please select prospect"},
                        disposition_id: { required: "please select disposition"},
                        activity_on: { required: "please select disposition"}
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
                            url: 'control/activity_addedit.php?act=addedit',
                            type: 'post',
                            beforeSubmit: function (formData, jqForm, options) {
                            //    $(e).find('button[type="submit"]').hide();
                                $('#loader').show();
                            },
                            complete: function () {
                                $('#loader').hide();
                              //  $(e).find('button[type="submit"]').show();
                            },
                            dataType: 'json',
                            clearForm: false,
                            success: function (resObj, statusText) {
                               // $(e).find('button').attr('disabled', false);

                                if (resObj.success) {
                                    showGritter('success', resObj.title, resObj.msg);
                                    if(clkBtn == 'save') {
                                        window.location = "tc_start_page.php?token=<?php echo $token; ?>";
                                    } else if(clkBtn == 'save_next'){
                                        setTimeout(function () {
                                            window.location.href=window.location.pathname+"?token=<?php echo $token; ?>&type=<?php echo $type; ?>";
                                            //window.location.href=window.location.pathname+"?prospect_id="+resObj.next_id+"&token=<?php echo $token; ?>&type=<?php echo $type; ?>";
                                        }, 3000);
                                    }
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

            $("input[name='call_type']").click(function(){
                var callType = $(this).val();
                if(callType == 'completed'){
                    $("#timer").hide();
                } else {
                    $("#timer").show();
                }
            });

            $("#disposition_id").change();
            $("#type_id").change(function(){
                typeDetails();
                $("#disposition_id").change();
            });

            $("#prospect_edit").click(function(){
                var prospectId = $("#type_id").val();
                $(this).attr('href','prospect_addedit_colorbox.php?id='+prospectId+'');
            });

           // var checkNext = window.setInterval('checkNextCall()', 40000); // 40sec

            var addTime = window.setInterval('addTime()',100000); // 100 sec

            typeDetails();


        });

    function typeEdit(){
        $("#prospect_edit").click();
    }

    function typeRefresh(){
        var prospectId = $("#type_id").val();
        $.ajax({
            url: 'control/prospect.php?act=prospectdd', data : { prospect_id : prospectId },type:'post',dataType: 'html',
            beforeSend: function(){
                $('#prospect_loader').show();
            },
            complete: function(){
                $('#prospect_loader').hide();
            },
            success: function(resp){
                //$('#type_id').html(resp);
                //$("#type_id").trigger("chosen:updated");
            }
        });
    }

    function checkNextCall(){
        var prospectId = $("#type_id").val();
        if(prospectId == ''){
            window.location.href=window.location.pathname+"?token=<?php echo $token; ?>&type=<?php echo $type; ?>";
<!--            $.ajax({-->
<!--                url: 'control/activity.php?act=checknextcall',type:'post',dataType: 'json',-->
<!--                success: function(resp){-->
<!--                    window.location.href=window.location.pathname+"?token=--><?php //echo $token; ?><!--&type=--><?php //echo $type; ?><!--";-->
                    //window.location.href=window.location.pathname+"?prospect_id="+resp.next_id+"&token=<?php echo $token; ?>&type=<?php echo $type; ?>";
<!--                }-->
<!--            });-->
        }

    }

    function typeDetails(){
        var prospectId = $("#type_id").val();
        $.ajax({
            url: 'control/prospect.php?act=prospectdetails', data : { prospect_id : prospectId },type:'post',dataType: 'html',
            beforeSend: function(){
                $('#type_detail_loader').show();
            },
            complete: function(){
                $('#type_detail_loader').hide();
            },
            success: function(resp){
                $('#type_details').html(resp);
            }
        });
    }

        function addTime() {
            var prospectId = $("#type_id").val();
            $.ajax({
                url: 'control/prospect.php?act=addtime',
                data : { prospect_id : prospectId },
                type:'post',
                dataType:'json'
            });
        }

        function pauseCall(){
            $.ajax({
               // url: 'control/activity.php?act=pausecall',
                type:'post',
                dataType:'json'
            });
        }

        function dialCall(){
            var number = $("#prospect_number").html();
            $.ajax({
             //   url: 'control/activity.php?act=dialcall',
                data : { number : number },
                type:'post',
                dataType:'json'
            });
        }

        function hangCall(){
            $.ajax({
              //  url: 'control/activity.php?act=hangcall',
                type:'post',
                dataType:'json'
            });
        }



    </script>

<div class="row-fluid">
    <div class="span12">
        <?php
        if(count($activityHistory) > 0){
        ?>
        <div class='row-fluid'>
            <div class="span11 table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Remarks</th>
                            <th>Disposition</th>
                            <th>Last Call On</th>
                            <th>Follow Up</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($activityHistory as $historyData){ ?>
                                <tr>
                                    <td><?php echo $historyData['remarks']; ?></td>
                                    <td><?php echo $historyData['disposition_name']; ?></td>
                                    <td><?php echo core::YMDToDMY($historyData['created_at'],true); ?></td>
                                    <td><?php echo core::YMDToDMY($historyData['follow_up_date_time'],true); ?></td>
                                </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
        <div class='row-fluid'>
            <div class="span9">
                <form class="form-horizontal" id="frm_activity">
                    <input value="<?php echo $id; ?>" type="hidden" name="activity_id" id="activity_id"/>
                    <input type="hidden" name="button_click" value="" class="" id="button_click">
                    <input value="<?php echo $activityType; ?>" type="hidden" name="activity_type" id="activity_type"/>
                    <input value="<?php echo $prospectQueueId; ?>" type="hidden" name="prospect_queue_id" id="prospect_queue_id"/>

                    <?php /*
                    <div class="control-group">
                        <label class="control-label" for="activity_name">Activity Name<small class="text-error"> *</small></label>
                        <div class="controls">
                            <div class="span12">
                                <input value="<?php echo $data['activity_name']; ?>" type="text" name="activity_name" id="activity_name"
                                       placeholder="ex. facebook"/>
                            </div>
                        </div>
                    </div>
                    */ ?>


                    <div class="control-group">
                        <label for="customer_id" class="control-label">Customer<small class="text-error"> *</small></label>
                        <div class="controls">
                            <select id="type_id" name="type_id" data-placeholder="Select Customer" class="chzn-select">
                                <option value=""></option>
                                <?php echo $prospectDd; ?>
                            </select>

                            <?php
                            if($acl->IsAllowed($login_id,'PROSPECT', 'PROSPECT', 'Edit PROSPECT')){
                            ?>
                            <a data-rel='prospect_edit' id="prospect_edit" class="btn btn-minier btn-warning" href='javascript:void(0);'><i class="icon-pencil icon-large" data-placement="bottom" data-rel="tooltip" data-original-title="Update Selected Prospect"></i></a>
                            <?php } ?>

                            <?php
                            if($acl->IsAllowed($login_id,'PROSPECT', 'PROSPECT', 'Add PROSPECT')){
                            ?>
                            <a  data-rel='prospect_add' class="btn btn-minier btn-success" href='prospect_addedit_colorbox.php'><i class="icon-plus icon-large" data-placement="bottom" data-rel="tooltip" data-original-title="Add New Prospect"></i></a>
                            <?php } ?>

                            <span for="type_id" class="help-inline"></span>
                        </div>
                    </div>


                    <div class="control-group">
                        <label for="customer_id" class="control-label">Disposition<small class="text-error"> *</small></label>
                        <div class="controls">
                            <select id="disposition_id" name="disposition_id" data-placeholder="Select disposition" class="chzn-select">
                                <option></option>
                                <?php echo $dispositionDd; ?>
                            </select>
                            <span for="disposition_id" class="help-inline"></span>
                        </div>
                    </div>


                    <div class="control-group">
                        <label class="control-label" for="remarks">Call Start Time</label>

                        <div class="controls">
                            <div class="span8 table-responsive">
                                <table class="table">
                                    <tr>
                                        <td colspan="2">
                                            <label>
                                                <input type="radio" name="call_type"  class="radio" checked value="current" <?php echo ($data['call_type'] == 'current') ? "checked" : ''; ?> />
                                                <span class="lbl">Current Call</span>
                                            </label>
                                        </td>
                                        <td class="hide">
                                            <label>
                                                <input type="radio" name="call_type"  class="radio" value="completed" <?php echo ($data['call_type'] == 'completed') ? "checked" : ''; ?>/>
                                                <span class="lbl">Completed Call</span>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="well well-small <?php echo ($data['call_type'] == 'completed') ? "hide" : ''; ?>" id="timer">
                                        <td colspan="2">
                                            <div class=""><h4>Call Timer</h4>
                                                <h5><span id="min">00</span>&nbsp;:&nbsp;<span id="sec">00</span></h5>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success" id="startButton">Start</button>
                                            <button type="button" class="btn btn-danger hide" id="stopButton" onclick="Clock.stop();">Stop</button>
                                            <div class="clearfix"></div>
                                            <div id="timerButton" class="hide">
                                                <span id="pauseCallTimer" style="display: inline;" onclick="Clock.pause();"><a
                                                        href="javascript:void(0);"
                                                        class="linkCol" style="cursor:pointer">Pause</a>&nbsp;|&nbsp;</span>
                                                <span id="resumeCallTimer" style="display: none;" onclick="Clock.resume();"><a
                                                        href="javascript:void(0);" style="cursor:pointer;">Resume</a>&nbsp;|&nbsp;</span>
                                                <span
                                                    id="resetCallTimer"><a href="javascript:void(0);"

                                                                           class="linkCol" style="cursor:pointer" onclick="Clock.reset()">Reset</a></span>
                                                &nbsp; </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Call Duration</td>
                                        <td>
                                            <input type="tel" class="input-mini" name="duration_minute" value="<?php echo $data['duration_minute']; ?>" id="duration_minute"/>minutes
                                            <span for="duration_minute" class="help-inline"></span>
                                        </td>
                                        <td>
                                            <input type="tel" class="input-mini" name="duration_second" value="<?php echo $data['duration_second']; ?>" id="duration_second"/>seconds
                                            <span for="duration_second" class="help-inline"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Call Start Time</td>
                                        <td>
                                            <div class='bootstrap-timepicker'>
                                                <div class="input-append">
                                                    <input id="call_time" name="call_time" type="text" class="input-mini"
                                                           value="<?php echo ($data['call_time'] != '00:00' && $data['call_time'] != '') ? $data['call_time'] : date("h:i A"); ?>">
                                                        <span class="add-on">
                                                            <i class="icon-time"></i>
                                                        </span>
                                                    <span for="call_time" class="help-inline"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class='row-fluid input-append'>
                                                <input class='input-small date-picker' data-placement='top' type='text' placeholder='start date'
                                                       name='start_date_time' data-date-format='dd-mm-yyyy'
                                                       readonly='readonly'
                                                       value="<?php echo ($data['start_date_time'] != '0000-00-00' && $data['start_date_time'] != '') ? core::YMDToDMY($data['start_date_time']) : ONLY_DATE; ?>">
                                                <span class='add-on'><i class='icon-calendar'></i></span>
                                                <span for="start_date_time" class="help-inline"></span>
                                            </div>
                                        </td>

                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>

                    <div class='control-group' id="follow_up_dd">
                        <label class='control-label' for='follow_up_date_time'>
                            Callback/Follow Up Date Time<small class="text-error"> *</small>
                        </label>
                        <div class='controls'>
                            <div class='row-fluid input-append'>
                                <input class='input-medium form_datetime' data-placement='top' type='text' placeholder='callback/follow up date'
                                       name='follow_up_date_time' data-date-format='yyyy-mm-dd' id="follow_up_date_time"
                                       readonly='readonly'
                                       value="<?php echo ($data['follow_up_date_time'] != '0000-00-00 00:00:00' && $data['follow_up_date_time'] != '') ? date('d-m-Y h:i:s',strtotime($data['follow_up_date_time'])) : ""; ?>">
                                <span class='add-on'><i class='icon-calendar'></i></span>
                                <span for='start_date' class='help-inline'></span>
                            </div>
                            <span for="follow_up_date_time" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group user_dd">
                        <label class="control-label" for="activity_on">Assign To<small class="text-error"> *</small></label>
                        <div class="controls">
                            <label class="inline">
                                <input name="activity_on" type="radio" value="bd" <?php echo ($activityOn == 'bd') ? "checked" : ""; ?>>
                                <span class="lbl">BD</span>
                            </label>
                            <label class="inline">
                                <input name="activity_on" type="radio" value="sp" <?php echo ($activityOn == 'sp') ? "checked" : ""; ?>>
                                <span class="lbl">AP</span>
                            </label>
                            <label class="inline">
                                <input name="activity_on" type="radio" value="ap" <?php echo ($activityOn == 'ap') ? "checked" : ""; ?>>
                                <span class="lbl">IA</span>
                            </label>
                            <span for="activity_on" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group" id="ap_dd">
                        <label for="customer_id" class="control-label">Assign User</label>
                        <div class="controls">
                            <select id="ap_id" name="ap_id" data-placeholder="Select AP" class="chzn-select">
                                <option></option>
                            </select>
                            <span for="ap_id" class="help-inline"></span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="remarks">Notes</label>

                        <div class="controls">
                            <textarea name="remarks" id="remarks" rows="3"><?php echo $data['remarks']; ?></textarea>
                        </div>
                    </div>



                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="save_next">
                            <i class="icon-step-forward bigger-110"></i> Save & next
                        </button>
                        <button type="submit" class="btn btn-info" id="save">
                            <i class="icon-ok bigger-110"></i> Save & stop
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
            <div class="span3 table-responsive" id="type_details">
            </div>
        </div>
    </div>
</div>
<?php
include_once 'footer.php';
?>