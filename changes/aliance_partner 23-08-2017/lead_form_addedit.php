<?php
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
    'js/jquery.autosize-min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/jquery.maskedinput.min',
);

$middle_breadcrumb = array('title' => 'CW Leads', 'link' => 'lead.php');
include_once 'header.php';
$table = 'lead_master';
$table_id = 'lead_id';
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
$statusClose = $db->FetchCellValue("status_master","status_id","is_close = 1 and status_id = '{$data['status_id']}'");
if($statusClose != ''){
   // $error = 'You Can\'t edit Close status data';
}
if(isset($id) && $id!=''){
    if($error != ''){
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),$data['state_id'],array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$data['city_id'],array("city_name"=>"asc"));
$data['status_id'] = ($data['status_id'] != '') ?  $data['status_id'] : $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1");
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$data['status_id'],array("status_name"=>"asc"),"status_type = 'lead'");
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$data['category_id'],array("category_name"=>"asc"),"category_type = 'lead'");

?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function() {

            $(".chzn-select").chosen({
                allow_single_deselect:true
            });

            $('.date-picker').datepicker({
                orientation: 'top',
                autoclose: true
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

            $('#state_id').change(function(){
                var state_id = $(this).val();
                $.ajax({
                    url: 'control/country_state_city.php?act=get_state_cities', data : { id : state_id },type:'post',dataType: 'html',
                    beforeSend: function(){
                        $('#city_loader').show();
                    },
                    complete: function(){
                        $('#city_loader').hide();
                    },
                    success: function(resp){
                        $('#city_id').html(resp);
                        $("#city_id").trigger("liszt:updated");
                    }
                });
            });//

            $('#btn_cancel').click(function(){
                form = $(this).closest('form');
                form.find('div.control-group').removeClass("success error");
                form.find('span.help-inline').text("");
                var email = $('#email').val();
                form.clearForm();
                $('#email').val(email);
                $('select.chzn-select').trigger("liszt:updated");
            });


            if (jQuery().validate) {
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                jQuery.validator.addMethod("zipcode", function(value, element) {
                    return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
                }, "Please provide a valid pin code.");

                jQuery.validator.addMethod("pan_card", function(value, element)
                {
                    return this.optional(element) || /^[A-Za-z]{5}\d{4}[A-Za-z]{1}$/.test(value);
                }, "Invalid Pan Number");

                $("#pincode").focusout(function(){
                    var pincode = $(this).val();
                    $.getJSON( "http://maps.googleapis.com/maps/api/geocode/json?address="+pincode+"&sensor=false&components=country:IN", function( data ) {
                        if(data.status == 'OK'){
                            var items = [];
                            var result = data.results[0].formatted_address;
                            var value = result.split(",");
                            if((result.match(/,/g) || []).length == 2){
                                count = value.length;
                                state = value[count - 2];
                                stateonly = $.trim(state.replace(/[0-9$.]/g, ""));
                                console.log(stateonly);
                                city = value[count - 3];
                                $("#state_id option").prop('selected', false);
                                $("#state_id option").each(function() {
                                    if($(this).text() == stateonly) {
                                        $(this).prop('selected', 'selected');
                                    }
                                });
                                $("#state_id").trigger("liszt:updated");
                                $("#city_id option").prop('selected', false);
                                $("#city_id option").filter(function() {
                                    return this.text == city;
                                }).prop('selected', true);
                                $("#city_id").trigger("liszt:updated");
                            } else {

                                $("#state_id option").filter(function() {
                                    return this.value == '';
                                }).prop('selected', true);
                                $("#state_id").trigger("liszt:updated");
                                $("#city_id option").filter(function() {
                                    return this.value == '';
                                }).prop('selected', true);
                                $("#city_id").trigger("liszt:updated");

                            }
                        } else {
                            $("#state_id option").filter(function() {
                                return this.value == '';
                            }).prop('selected', true);
                            $("#state_id").trigger("liszt:updated");
                            $("#city_id option").filter(function() {
                                return this.value == '';
                            }).prop('selected', true);
                            $("#city_id").trigger("liszt:updated");
                        }

                    });
                    setTimeout(function(){$('#city_id').change();},1000);
                })

                $("#form_add").validate({

                    rules: {
                        email: {
                            required:true,
                            email: true,
                            "remote" :
                            {
                                url: 'control/lead.php?act=checkemail',
                                data:{"lead_id":'<?php  echo $id;?>'},
                                async:false,
                                type: "post"
                            }
                        },
                        address:  { required:true},
                        state_id: { required : true },
                        city_id: { required : true },
                        status_id: { required : true },
                        category_id: { required : true },
                        lead_name: {
                            required : true,
                        },
                        mobile_no: {
                            required : true,
                            regex: ['mobile','mobile'],

                        },
                        pincode:{zipcode:true,required:true}
                    },

                    messages: {
                        email: { required : 'Please enter email address', email: 'Please enter valid email address',"remote" :"email address is duplicate" },
                        lead_name: { required : 'Please enter lead name'},
                        address: { required : 'Please enter address'},
                        mobile: { required : 'Please enter mobile no',"remote" :"mobile number is duplicate"},
                        state_id: { required : 'Please select state'},
                        status_id: { required : 'Please select status'},
                        category_id: { required : 'Please select Product/Lead'},
                        city_id: { required : 'Please select city'},

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
                            url: 'control/lead_form_addedit.php?act=addedit',
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
                                $(e).find('button').attr('disabled', false);

                                if(resObj.success){
                                    showGritter('success',resObj.title,resObj.msg);
                                    setTimeout(function(){location.reload(true);},3000);
                                }else{
                                    if(resObj.hasOwnProperty("errors")){
                                        var message = '';
                                        $.each(resObj.errors,function(key,value){
                                            message += value + "<br>";
                                        });
                                        showGritter('error',"Error",message);
                                    } else {
                                        showGritter('error',resObj.title,resObj.msg);
                                    }
                                }
                            }
                        });
                    }
                });
            }

            $('.date-picker-bday').datepicker({
                autoclose: true,
                endDate:'<?php echo date("d-m-Y",strtotime("-16 years")); ?>'
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });

            $('#mobile_no').mask('9999999999');
            $('#pincode').mask('999999');
        });
    </script>
    <div class='row-fluid'>
        <form class="form-horizontal" id="form_add">
            <div class="span5">


                <div class="control-group">
                    <label class="control-label" for="lead_name">Lead Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['lead_name']; ?>" type="text" name="lead_name" id="lead_name" placeholder="ex. Stephan Joe" />
                            <input value="<?php echo $data['lead_id']; ?>" type="hidden" name="lead_id" id="lead_id"/>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="email">Email<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['email']; ?>" type="text" name="email" id="email" placeholder="ex. StephanJoe@gmail.com" />
                        </div>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="address">Address<small class="text-error"> *</small></label>
                    <div class="controls">
                        <textarea name="address" id="address" rows="3" class=""><?php echo $data['address']; ?></textarea>
                    </div>
                </div>


                <div class="control-group">
                    <label for="pincode" class="control-label">Pincode<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" name='pincode' id='pincode' placeholder="ex.395001" value="<?php echo $data['pincode']; ?>" class=""/>
                    </div>
                </div>


                <div class="control-group">
                    <label for="state_id" class="control-label">State<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="state_id" name="state_id" data-placeholder="Select State" class="chzn-select">
                            <option></option>
                            <?php echo $stateDd; ?>
                        </select>
                        <span for="state_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="city_id" class="control-label">City<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="city_id" name="city_id" data-placeholder="Select City" class="chzn-select">
                            <option></option>
                            <?php echo $cityDd; ?>
                        </select>
                        <i id='city_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="city_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group">
                    <label for="status_id" class="control-label">Status<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="status_id" name="status_id" data-placeholder="Select Status" class="chzn-select">
                            <option></option>
                            <?php echo $statusDd; ?>
                        </select>
                        <span for="status_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="mobile_no">Mobile<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input value="<?php echo $data['mobile_no']; ?>" type="tel" name="mobile_no" placeholder="ex.897668XXXX" id="mobile_no" class="" />
                    </div>
                </div>

            </div>


            <div class="span5">
                <div class="control-group">
                    <label for="category_id" class="control-label">Product/Lead<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="category_id" name="category_id" data-placeholder="Select Product/Lead" class="chzn-select">
                            <option></option>
                            <?php echo $categoryDd; ?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="about_user">Remarks</label>
                    <div class="controls">
                        <textarea name="remarks" id="remarks" rows="3" class=""><?php echo $data['remarks']; ?></textarea>
                    </div>
                </div>

            </div>
            <?php
            if($statusClose == ''){
            ?>
            <div class="span10">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-ok bigger-110"></i>Save
                    </button>
                    <button id='btn_cancel' type="button" class="btn">
                        <i class="icon-undo bigger-110"></i>Reset
                    </button>
                    <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please
                        wait...
                    </div>
                </div>
            </div>
            <?php } ?>
        </form>
    </div>
<?php
include_once 'footer.php';
?>