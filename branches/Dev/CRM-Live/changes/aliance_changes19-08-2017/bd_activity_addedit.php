<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    'css/chosen',
    'css/bootstrap-timepicker',
    'css/bootstrap-datetimepicker.min',
    'css/jquery.ui.core',
    'css/jquery.ui.theme',
    'css/jquery.ui.menu',
    'css/jquery.ui.accordion',
    'css/jquery.ui.autocomplete',
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.jquery.min',
    'js/jquery.autosize-min',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/bootstrap-datetimepicker.min',
    'js/jquery.ui.core',
    'js/jquery.ui.widget',
    'js/jquery.ui.menu',
    'js/jquery.ui.position',
    'js/jquery.ui.autocomplete'
);
$middle_breadcrumb = array('title' => 'Activities', 'link' => 'bd_activity.php');
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

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';
$type = (isset($_GET['type']) && !empty($_GET['type'])) ? $db->FilterParameters($_GET['type']) : '';
$lead_id = (isset($_GET['lead_id']) && !empty($_GET['lead_id'])) ? $db->FilterParameters($_GET['lead_id']) : '';
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
$error = '';
$typeId = isset($_GET['type_id']) ? $_GET['type_id'] : "";
$leadId = (isset($data['type_id']) && $data['type_id']!= '') ? $data['type_id'] : $lead_id;
$activityOn = isset($_GET['activity_on']) ? $_GET['activity_on'] : "";
if($typeId == ''){
    //   $error = 'Invalid Record Or Record Not Found';
}
//$leadId = isset($_GET['lead_id']) ? $_GET['lead_id'] : "";
$clientName = ($leadId != '') ?  $db->FetchCellValue("lead_master","lead_name","lead_id = ".$leadId."") : "";
$activityName = ($typeId != '') ? ucwords($typeId) : "Acitivity";
$data['activity_type'] = ($typeId != '') ? $typeId : $data['activity_type'];
$activityType = $db->GetEnumvalues("activity_master","activity_type");
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$data['status_id'],array("status_name"=>"ASC"),"status_type = 'activity'");
$isShow =array();
if($error != ''){
    echo Utility::ShowMessage('Error: ', $error);
    include_once 'footer.php';
    exit;
}
?>
<script>

    var e;
    var validation_form;

    var Clock = '';

    function pad (str, max ) {
        if(typeof max === 'undefined'){
            max = 2;
        }
        str = str.toString();
        return str.length < max ? pad("0" + str, max) : str;
    }

    $(document).ready(function () {

        // call start
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
            $(this).hide();
            $("#stopButton").fadeIn();
            $("#timerButton").show();
            pauseCall();
            dialCall();
            Clock.start();
        });
        $('#stopButton').click(function () {
            hangCall();
        });

        $(".form_datetime").datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            showMeridian: true,
            startDate: "<?php echo date("Y-m-d H:i") ?>",
        });


        $(".type_name").blur(function(){
            if($(this).val().length == 0) {
                $("#create-client").html('');
            }
        });

        <?php if($leadId != ''){ ?>
        $("#type_id").prop("readonly",true);
        <?php
        }
        ?>
        $(".type_name").autocomplete({
            autoFocus: true,
            source:function(request,response){

                var type = $("select#type option:selected").text();

                $.ajax({
                    url:"control/activity.php?act=getleads",
                    data: {
                        term : request.term,
                    },
                    beforeSend: function(){
                        var id_str = $(".type_name").attr('id');
                        var id_arr = id_str.split('_');
                        $('#type_id_'+ id_arr[id_arr.length - 1]).val('');
                    },
                    dataType: 'json',
                    success: function(data){

                        response( $.map( data, function( item,key ) {
                            var mobile = (item.mobile_no) ? ", " + item.mobile_no : "";
                            var name = (item.name) ? item.name : "no result found";
                            return {
                                // value:[item]
                                id: item.id,
                                label: name+""+mobile,
                                value: item.name
                            };
                        }));
                    }
                });
            },
            select: function (event, ui) {

                var id_str = $(this).attr('id');
                var id_arr = id_str.split('_');
                $("#lead_id").val(ui.item.id);
                $('#type_id_'+ id_arr[id_arr.length - 1]).val(ui.item.id);
            }
        }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {

            var id_str = $(".type_name").attr('id');
            var id_arr = id_str.split('_');
            var type = $('#type_'+ id_arr[id_arr.length - 1]).val();

            var inner_html = '<a><div class="list_item_container"><div class="label">' + item.label + '</div></div></a>';


            return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append(inner_html)
                .appendTo( ul );
        };

        $('.play_time').timepicker({
            minuteStep: 15,
            showSeconds: true,
            showMeridian: false,
            defaultTime: "00:00:00",
        });
        $(".chzn-select").chosen({
            allow_single_deselect:true
        });
        <?php if($typeId != ''){ ?>
        $("#activity_id option:not(:selected)").attr('disabled','disabled');
        $("#activity_id").trigger("liszt:updated");
        <?php } ?>
        $('.date-picker').datepicker({
            orientation: 'top',
            autoclose: true,
            <!--            startDate: '--><?php // echo date('d-m-Y', strtotime("0 days", strtotime(date('d-m-Y')))); ?><!--',-->
        }).next().on(ace.click_event, function () {
            $(this).prev().focus();
        });
        jQuery.validator.addMethod("isString", function(value, element) {
            return /^[a-zA-Z() ]+$/.test(value);
        }, "please enter text only");

        jQuery.validator.addMethod("zipcode", function(value, element) {
            return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
        }, "Please provide a valid pin code.");

        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };

            // Company type validateion code
            $("#frm_activity").validate({
                rules:{
                    lead_id:{required: true},
                    start_date:{required:true},
                    follow_up_date_time: { required: "#follow_up_date_time:visible"},
                    status_id:{required: true},
                    assign_to: { required : true },
                    activity_id:{required: true},
                    remarks:{required: true},
                    duration_minute: { required: "#call_dd:visible",digits:true,max:60},
                    duration_second: { required: "#call_dd:visible",digits:true,max:60},
                    call_time: { required: true},
                },
                messages:{
                    lead_id:{required: 'Please select client name'},
                    start_date:{required: 'please select start Date'},
                    end_date: { required: 'Please select followup/end date', dateGreaterThan: 'Followup/end date must be greater or equal to start date'},
                    status_id:{required: 'Please select status'},
                    activity_id:{required: 'Please select activity type'},
                    remarks:{required: 'Please enter some remarks about activity'},
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
                        url: 'control/bd_activity_addedit.php?act=addedit',
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
                                $(e).clearForm();
                                $('.chzn-select').trigger('chosen:updated');
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

        $('#activity_type').change(function(){
            var activity_type = $(this).val();
            $.ajax({
                url: 'control/status.php?act=getstatus', data : { activity_type : activity_type,status_id : "<?php echo $data['status_id']; ?>" },type:'post',dataType: 'html',
                beforeSend: function(){
                    $('#city_loader').show();
                },
                complete: function(){
                    $('#city_loader').hide();
                },
                success: function(resp){
                    $('#status_id').html(resp);
                    $("#status_id").trigger("liszt:updated");
                }
            });
        });

        $("#status_id").change(function(){
            var status_id = $(this).val();
            $.ajax({
                url: 'control/status.php?act=checkstatus',
                data : { status_id : status_id},
                type:'post',
                dataType: 'json',
                beforeSend: function(){
                    $('#disposition_loader').show();
                },
                complete: function(){
                    $('#city_loader').hide();
                },
                success: function(resp){
                    if(resp.is_callback == 1){
                        $("#follow_up_dd").show();
                    } else {
                        $("#follow_up_dd").hide();
                    }


                }
            });
        });

        $("#activity_type").change(function(){
            var activityType = $(this).val();
            if(activityType == 'call'){
                $("#call_dd").show();
            } else {
                $("#call_dd").hide();
            }
        });


        <?php
         if($activityOn != ''){ ?>
        $("input[name=activity_on]").each(function(){
            if (!$(this).is(':checked')) {
                $(this).attr('disabled', true);
            }
        });
        <?php } ?>

        $("#status_id").change();
        $("#activity_type").change();

    });


    $(document).on("click",".create-class",function(){
        $('form#frm_lead input,textarea').val('');
        $('form#frm_lead').find('div.control-group').removeClass("success error");
        $('form#frm_lead').find('div.control-group span.help-inline').text('');
        $('#act_lead').text('Add');
        $('#lead_name').val($.trim($("#create-client").contents().get(1).nodeValue));
        $('#modal_add_lead').modal('show');
    });

    function pauseCall(){
        $.ajax({
            url: 'control/activity.php?act=pausecall',
            type:'post',
            dataType:'json'
        });
    }

    function dialCall(){
        var number = $("#prospect_number").html();
        $.ajax({
            url: 'control/activity.php?act=dialcall',
            data : { number : number },
            type:'post',
            dataType:'json'
        });
    }

    function hangCall(){
        $.ajax({
            url: 'control/activity.php?act=hangcall',
            type:'post',
            dataType:'json'
        });
    }


</script>
<h4><?php echo ucwords($action)." ".$activityName; ?></h4>
<hr/>
<div class='row-fluid'>
    <div class="span12">
        <form class='form-horizontal' id='frm_activity'>


            <div class="control-group">
                <label class="control-label" for="type_id"><span id="activity_on_title">Lead</span> name<small class="text-error"> *</small></label>
                <div class="controls">
                    <input type="text" class="type_name" id="type_id" name="type_id" placeholder="ex. joeseph" value="<?php echo $clientName; ?>">
                    <span class="help-block" style="font-size: 10px">(search by lead name or mobile)</span>
                    <input type="hidden" name='lead_id' value="<?php echo $leadId; ?>" id="lead_id"/>
                    <input type="hidden" name='lead_activity_id' value="<?php echo $id; ?>" id="lead_activity_id"/>
                    <span class="" style="font-size: 12px;margin-top: 0px !important;" id="create-client"></span>
                </div>
            </div>

            <div class="control-group">
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
                    <span for="activity_id" class="help-inline"></span>
                    <input type="hidden" name='action' value="<?php echo $action; ?>"/>
                </div>
            </div>


            <div class="control-group hide" id="call_dd">
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

            <div class="control-group">
                <label class="control-label" for="country_name">Status<small class="text-error"> *</small></label>
                <div class="controls">
                    <select id="status_id" name="status_id" class="chzn-select" data-placeholder="Select Status">
                        <option></option>
                        <?php echo $statusDd; ?>
                    </select>
                    <span for="status_id" class="help-inline"></span>
                </div>
            </div>


            <div class='control-group'>
                <label class='control-label' for='start_date'>
                    Activity  Date<small class="text-error"> *</small>
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='input-small date-picker' data-placement='top' type='text' placeholder='Start Date'
                               name='start_date_time' id="start_date_time" data-date-format='dd-mm-yyyy'
                               readonly='readonly' value="<?php echo ONLY_DATE; ?>">
                        <span class='add-on'><i class='icon-calendar'></i></span>
                        <span for='start_date_time' class='help-inline'></span>
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
                               value="<?php echo ($data['follow_up_date_time'] != '0000-00-00 00:00:00' && $data['follow_up_date_time'] != '') ? $data['follow_up_date_time'] : ""; ?>">
                        <span class='add-on'><i class='icon-calendar'></i></span>
                        <span for='start_date' class='help-inline'></span>
                    </div>
                    <span for="follow_up_date_time" class="help-inline"></span>
                </div>
            </div>
            <div class='control-group'>
                <label for="call_time" class="control-label">Time</label>
                <div class='controls bootstrap-timepicker'>
                    <div class="input-append">
                        <input id="call_time"  name="call_time" value="<?php echo $data['call_time']; ?>" type="text" class="play_time span6" />
                                <span class="add-on">
                                    <i class="icon-time"></i>
                                </span>
                        <span for="call_time" class="help-inline"></span>
                    </div>
                </div>
            </div>


            <?php /*
            <div class='control-group upload_doc'>
                <label for='filename' class='control-label'>Upload Document</label>
                <div class='controls'>
                    <input type='file' id="filename" name='filename[]' placeholder='File' class='upload' multiple/>
                </div>
            </div>
            */ ?>

            <div class="control-group">
                <label for="remarks" class="control-label">Remarks<small class="text-error"> *</small></label>
                <div class="controls">
                    <textarea name='remarks' id='remarks' class="input-xlarge"><?php echo $data['remarks']; ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-small btn-info">
                    <i class="icon-ok bigger-110"></i> Save
                </button>
                <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                    wait...
                </div>
            </div>
        </form>
    </div>
    <div class="span5 hide">
        <div class="widget-box">
            <div class="widget-header">
                <h4 class="smaller">Attendees</h4>
            </div>

            <div class="widget-body">
                <div class="widget-main">

                </div>
            </div>
        </div>

    </div>
</div>

