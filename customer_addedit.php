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

$middle_breadcrumb = array('title' => 'Customers', 'link' => 'customer.php');
include_once 'header.php';
$table = 'customer_master';
$table_id = 'customer_id';
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
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),$data['state_id'],array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$data['city_id'],array("city_name"=>"asc"));
$selectedSubLocality = $db->FetchToArray("customer_sub_locality","sub_locality_id","customer_id = '{$id}'");
$subLocalityDd = $db->CreateOptions("html","sub_locality",array("sub_locality_id","sub_locality_name"),$selectedSubLocality,array("sub_locality_name"=>"asc"));
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function() {

            $(".chzn-select").chosen({
                allow_single_deselect:true,
            });

            $('.date-picker').datepicker({
                orientation: 'top',
                autoclose: true,
            }).next().on(ace.click_event, function () {
                $(this).prev().focus();
            });



            $('#state_id').change(function(){
                var state_id = $(this).val();
                $.ajax({
                    url: 'control/users.php?act=getcities', data : { state_id : state_id },type:'post',dataType: 'html',
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
            });

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
                            city = value[count - 3];
                            $("#state_id option").filter(function() {
                                return this.text == stateonly;
                            }).attr('selected', true);
                            $("#state_id").trigger("liszt:updated");
                            $("#city_id option").filter(function() {
                                return this.text == city;
                            }).attr('selected', true);
                            $("#city_id").trigger("liszt:updated");
                        } else {

                            $("#state_id option").filter(function() {
                                return this.value == '';
                            }).attr('selected', true);
                            $("#state_id").trigger("liszt:updated");
                            $("#city_id option").filter(function() {
                                return this.value == '';
                            }).attr('selected', true);
                            $("#city_id").trigger("liszt:updated");

                        }
                    }

                });
            })


            $('#btn_cancel').click(function(){
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
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                jQuery.validator.addMethod("zipcode", function(value, element) {
                    return this.optional(element) || /^\d{6}(?:-\d{4})?$/.test(value);
                }, "Please provide a valid pin code.");

                $("#form_add").validate({
                    rules: {
                        email: {
                            required : true,
                            email: true,
                            "remote" :
                            {
                                url: 'control/customer.php?act=checkemail',
                                data:{"customer_id":"<?php echo $id;?>"},
                                async:false,
                                type: "post"
                            }
                        },
                        state_id: { required : true },
                        city_id: { required : true },
                        'sub_locality_ids[]': { required : true },
                        customer_name: { required : true, regex: ['name','Name']},
                        mobile_no: { required : true, regex: ['mobile','mobile']},
                        //landline_no: { regex: ['landline','landline']},
                        pincode:{zipcode:true},
                        filepath:{extension: "png|jpg|jpeg|gif" }
                    },

                    messages: {
                        email: {
                            required : 'Please enter email address',
                            email: 'Please enter valid email address',
                            remote:"email is duplicate"
                        },
                        'category_ids[]': { required : "Please select category" },
                        'sub_locality_ids[]': { required : "Please select sub locality"},
                        customer_name: { required : 'Please enter customer name'},
                        mobile_no: { required : 'Please enter mobile no'},
                        state_id: { required : 'Please select state'},
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
                            url: 'control/customer_addedit.php?act=addedit',
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
                                            showGritter('error',"Error",message);
                                        });
                                    } else {
                                        showGritter('error',resObj.title,resObj.msg);
                                    }
                                }
                            }
                        });
                    }
                });
            }


            $('#mobile_no').mask('9999999999');
            $('#pincode').mask('999999');
        });
    </script>

    <div class='row-fluid'>
        <div class="span12">
            <form class="form-horizontal" id="form_add">


                <div class="control-group">
                    <label class="control-label" for="customer_name">Customer Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['customer_name']; ?>" type="text" name="customer_name" id="customer_name" placeholder="ex. Naitik" />
                            <input value="<?php echo $id; ?>" type="hidden" name="customer_id" id="customer_id" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="email">Email<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['email']; ?>" type="email" name="email" id="email" class="" placeholder="name@company.com" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="mobile_no">Mobile<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input value="<?php echo $data['mobile_no']; ?>" type="tel" name="mobile_no" id="mobile_no"  placeholder="ex 8976677XXX" />
                    </div>
                </div>

                <?php
                /*
                <div class="control-group">
                    <label for="sub_locality_id" class="control-label">Sub Locality<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="sub_locality_id" name="sub_locality_ids[]" data-placeholder="Select Sub Locality" class="chzn-select" multiple>
                            <option></option>
                            <?php echo $subLocalityDd; ?>
                        </select>
                        <span for="sub_locality_id" class="help-inline"></span>
                    </div>
                </div>
                */
                ?>

                <div class="control-group">
                    <label class="control-label" for="address">Address</label>
                    <div class="controls">
                        <div class="span12">
                            <textarea name="address" id="address" rows="3"><?php echo $data['address']; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="pincode" class="control-label">Pincode<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" value="<?php echo $data['pincode']; ?>" name='pincode' id='pincode' placeholder="ex.395001" />
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




                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active" id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : ""; ?>>
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