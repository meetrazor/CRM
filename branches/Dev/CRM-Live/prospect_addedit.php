<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    //'css/chosen',
    'css/chosen.create-option'
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.create-option.jquery',
    'js/jquery.autosize-min',
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
);

$middle_breadcrumb = array('title' => 'Prospects', 'link' => 'prospect.php');
include_once 'header.php';
$table = 'prospect_master';
$table_id = 'prospect_id';
$action = 'add';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field){
    $data[$field] = '';
}

$id = (isset($_GET['id']) && !empty($_GET['id'])) ? intval($db->FilterParameters($_GET['id'])) : '';
$campaignId = (isset($_GET['campaign_id']) && !empty($_GET['campaign_id'])) ? intval($db->FilterParameters($_GET['campaign_id'])) : '';
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
$activeCondition = ($action != "edit") ? "is_active = 1" : "1=1";
$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),$data['state_id'],array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),$data['city_id'],array("city_name"=>"asc"));
$salaryModeDd = $db->CreateOptions("html","salary_mode_master",array("salary_mode_id","salary_mode_name"),$data['salary_mode_id'],array("salary_mode_name"=>"asc"));
$data['campaign_id'] = ($campaignId != '') ? $campaignId : $data['campaign_id'];
$campaignDd = $db->CreateOptions("html","campaign_master",array("campaign_id","campaign_name"),$data['campaign_id'],array("campaign_name"=>"asc"),$activeCondition);
$lastTellecallerId = $db->FetchCellValue("prospect_users","type_id","prospect_id = '{$id}' and is_latest = 1 and user_type = 'tc'");
$tellecallerDd = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),$lastTellecallerId,array("concat(first_name,' ',last_name)"=>"asc"),"user_type = ".UT_TC."");
$contacts = $db->FetchToArray("prospect_contact","*","$table_id = '{$id}'");
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$data['category_id'],array("category_name"=>"asc"),$activeCondition);
$email = array();
$number = array();
foreach($contacts as $contact){
    if($contact['contact_type'] == 'phone'){
        $number[] = $contact;
    } else {
        $email[] = $contact;
    }
}
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
    $(function() {

        $(".chzn-select").chosen({
            allow_single_deselect:true,
        });


        $("#education_id").chosen({
            create_option_text: 'Create Education',
            create_option: function(term){
                var chosen = this;
                $.post('control/education.php?act=addchosen', {term: term}, function(data){
                    chosen.append_option({
                        value: data.value,
                        text: data.text,
                    });
                },"json");
            },
            persistent_create_option: true,
            skip_no_results: true
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
                    $("#city_id").trigger("chosen:updated");
                }
            });
        });

        $('#category_id').change(function(){
            var category_id = $(this).val();
            $.ajax({
                url: 'control/category.php?act=checkbus', data : { category_id : category_id },type:'post',dataType: 'json',
                success: function(resp){
                    if(resp.is_business == 1){
                         $("#first_name_title").html("Business Name");
                        $("#last_name_dd").hide();
                    } else {
                        $("#first_name_title").html("First Name");
                        $("#last_name_dd").show();
                    }
                }
            });
        });


        $('#city_id').change(function(){
            var city_id = $(this).val();
            $.ajax({
                url: 'control/country_state_city.php?act=get_city_locality',
                data : { id : city_id },
                type:'post',
                dataType: 'html',
                beforeSend: function(){
                    $('#sub_locality_loader').show();
                },
                complete: function(){
                    $('#sub_locality_loader').hide();
                },
                success: function(resp){
                    $('#sub_locality_id').html(resp);
                    $("#sub_locality_id").trigger("chosen:updated");
                }
            });
        });

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

            $("#frm_prospect").validate({
                rules: {
                    first_name: { required : true,},
                    last_name: { required : "#last_name:visible", regex: ['name','Name']},
                    pincode:{zipcode:true},
                    amount:{required:true},
                    actual_amount:{required:true,digits:true},
                    campaign_id: { required : true },
                    category_id: { required : true },
                    state_id: { required : true },
                    city_id: { required : true },
                    user_id: { required : true },
                    salary_amount: { digits : true },
                    work_experience: { digits : true },
                    profit: { digits : true },
                    business_vintage: { digits : true },
                    loan_obligation: { digits : true },
                },

                messages: {
                    prospect_name: { required : 'Please enter customer name'},
                    first_name: { required : 'Please enter first name'},
                    last_name: { required : 'Please enter last name'},
                    campaign_id: { required : 'Please select campaign'},
                    amount: { required : 'Please enter amount'},
                    actual_amount: { required : 'Please enter actual amount',digits:"Please enter digits only"},
                    category_id: { required : 'Please select category'},
                    state_id: { required : 'Please select state'},
                    city_id: { required : 'Please select city'},
                    user_id: { required : 'Please select telecaller'},
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
                        url: 'control/prospect_addedit.php?act=addedit',
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

            $("#employment_type").change(function(){
                $(".salary,.business").hide();
                var employmentType = $(this).val();
                if(employmentType == 'salaried'){
                    $(".salary").show();
                    $(".business input,select").val('');
                    $(".business").hide();
                } else if(employmentType == 'businessman') {
                    $(".salary").hide();
                    $(".salary input,select").val('');
                    $(".business").show();
                }
            });

            $("#employment_type").change();
        }

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
                        $("#state_id").trigger("chosen:updated");
                        $("#city_id option").filter(function() {
                            return this.text == city;
                        }).attr('selected', true);
                        $("#city_id").trigger("chosen:updated");
                    } else {

                        $("#state_id option").filter(function() {
                            return this.value == '';
                        }).attr('selected', true);
                        $("#state_id").trigger("chosen:updated");
                        $("#city_id option").filter(function() {
                            return this.value == '';
                        }).attr('selected', true);
                        $("#city_id").trigger("chosen:updated");

                    }
                }

            });
            setTimeout(function(){
                $('#city_id').change();
            },1000);

        });


        addValidation("input","#phone_number0",{
            required: true,
            regex: ['mobile','mobile']
        });

        addValidation("input","#email0",{
            required: true,
            email:true
        });

    });

    function addValidation(type,selecter,rules){
        $(''+type+''+selecter+'').each(function () {
            $(this).rules('add', rules);
        });

    }


    var doc_index = <?php echo count($number) + 1; ?>;
    function AddNumberRow(){
        doc_index ++;
        index = doc_index;
//        alert(index);
        doc_html = "<tr id='number_" + index + "'>" +
            // "<td>"+ (index+1) +"</td>" +
            "<td>" +
            "<input type='tel'  class='span12'  id='phone_number" + index + "' name='phone_number[" + index + "]' placeholder='' />" +
            "</td>" +

            "<td>"+
            "<label>"+
            "<input type='radio'  name='phone_number_primary[]' class='radio' value='"+index+"' ><span class='lbl'></span>"+
            "</label>"+
            "</td>"+
            "<td>"+
            "<label>"+
            "<input type='checkbox'  name='phone_number_wrong[" + index + "]' value='"+index+"' class='checkbox'  /><span class='lbl'></span>"+
            "</label>"+
            "</td>"+
            "<td><a href='javascript:void(0);' onclick='RemoveNumberRow(" + index + ")'><i class='icon-remove red'></i></a></td>"+
            "</tr>";


        $('table#pn_rows tbody').append(doc_html);

        addValidation("input","#phone_number"+index+"",{
            required: true,
            regex: ['mobile','mobile']
        });
    }

    function RemoveNumberRow(index){
//        alert(index);
        $('table#pn_rows tr#number_'+index).slideUp().hide().remove();
    }

    var email_index = <?php echo count($email) + 1; ?>;
    function AddEmailRow(){
        email_index ++;
        index = email_index;
//        alert(index);
        doc_html = "<tr id='email_" + index + "'>" +
            // "<td>"+ (index+1) +"</td>" +
            "<td>" +
            "<input type='tel'  class='span12'  id='email" + index + "' name='email[" + index + "]' placeholder='' />" +
            "</td>" +

            "<td>"+
            "<label>"+
            "<input type='radio'  name='email_primary[]' class='radio' value='"+index+"' ><span class='lbl'></span>"+
            "</label>"+
            "</td>"+
            "<td>"+
            "<label>"+
            "<input type='checkbox'  name='email_wrong[" + index + "]'  class='checkbox'  /><span class='lbl'></span>"+
            "</label>"+
            "</td>"+
            "<td><a href='javascript:void(0);' onclick='RemoveEmailRow(" + index + ")'><i class='icon-remove red'></i></a></td>"+
            "</tr>";


        $('table#email_rows tbody').append(doc_html);

        addValidation("input","#email"+index+"",{
            required: true,
            email:true,
        });
    }

    function RemoveEmailRow(index){
//        alert(index);
        $('table#email_rows tr#email_'+index).slideUp().hide().remove();
    }





    </script>

    <div class='row-fluid'>
        <div class="span12">
            <form class="form-horizontal" id="frm_prospect">

                <div class="control-group">
                    <label for="category_id" class="control-label">Loan/Product Type<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="category_id" name="category_id" data-placeholder="Select Load/Product Type" class="chzn-select">
                            <option></option>
                            <?php echo $categoryDd; ?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group" id="first_name_dd">
                    <label class="control-label" for="first_name"><span id="first_name_title">First Name</span><small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['first_name']; ?>" type="text" name="first_name" id="first_name" placeholder="" />
                            <input value="<?php echo $id; ?>" type="hidden" name="prospect_id" id="prospect_id" />
                            <input value="<?php echo $lastTellecallerId; ?>" type="hidden" name="last_user_id" id="last_user_id" />
                        </div>
                    </div>
                </div>

                <div class="control-group" id="last_name_dd">
                    <label class="control-label" for="last_name">Last Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['last_name']; ?>" type="text" name="last_name" id="last_name" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="age">Age</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['age']; ?>" type="tel" name="age" id="age" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="employment_type">Employment Type</label>
                    <div class="controls">
                        <div class="span12">
                            <select id="employment_type" name="employment_type" class="chzn-select">
                                <option></option>
                                <option value="salaried" <?php echo ($data['employment_type'] == 'salaried') ? "selected" : ""?>>Salaried</option>
                                <option value="businessman" <?php echo ($data['employment_type'] == 'businessman') ? "selected" : ""?>>Businessman</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="control-group salary">
                    <label class="control-label" for="salary_amount">Salary Amount</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['salary_amount']; ?>" type="tel" name="salary_amount" id="salary_amount" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group salary">
                    <label class="control-label" for="salary_mode_id">Salary Mode</label>
                    <div class="controls">
                        <div class="span12">
                            <select id="salary_mode_id" name="salary_mode_id" class="chzn-select">
                                <option></option>
                                <?php echo $salaryModeDd; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="control-group salary">
                    <label class="control-label" for="work_experience">Work Experience</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['work_experience']; ?>" type="tel" name="work_experience" id="work_experience" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group business">
                    <label class="control-label" for="profit">Profit</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['profit']; ?>" type="tel" name="profit" id="profit" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group business">
                    <label class="control-label" for="business_vintage">Business Vintage</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['business_vintage']; ?>" type="tel" name="business_vintage" id="business_vintage" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group business">
                    <label class="control-label" for="loan_obligation">Loan Obligation</label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['loan_obligation']; ?>" type="tel" name="loan_obligation" id="loan_obligation" placeholder="" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="campaign_id" class="control-label">Campaign<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="campaign_id" name="campaign_id" data-placeholder="Select Campaign" class="chzn-select">
                            <option></option>
                            <?php echo $campaignDd; ?>
                        </select>
                        <span for="campaign_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group">
                    <label for="amount" class="control-label">Loan/Product Amt.<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" value="<?php echo $data['amount']; ?>" name='amount' id='amount' placeholder="ex.5 lacs"/>
                    </div>
                </div>

                <div class="control-group">
                    <label for="actual_amount" class="control-label">Actual Loan/Product Amt.<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" value="<?php echo $data['actual_amount']; ?>" name='actual_amount' id='actual_amount' placeholder="ex.50000"/>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="doc_titles">Email</label>
                    <div class="controls">
                        <table id='email_rows' class="table table-bordered span6">
                            <thead>
                                <tr>
                                    <th width="65%">Email</th>
                                    <th width="15%">Is Primary?</th>
                                    <th width="15%">Is Wrong</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(count($email) > 0){
                                foreach($email as $ekey => $emailData){
                                ?>
                                    <tr id='email_<?php echo $ekey; ?>'>
                                        <!-- <td>1</td> -->
                                        <td>
                                            <label for="option_1" class="control-label"></label>
                                            <input type="text" class="span12" placeholder="" value="<?php echo $emailData['contact']; ?>" name="email[<?php echo $ekey; ?>]" id="email<?php echo $ekey; ?>">
                                        </td>
                                        <td>
                                            <label>
                                                <input type="radio" name="email_primary[]" value="<?php echo $ekey; ?>" class="radio" <?php echo ($emailData['is_primary'] == 1) ? "checked" : ""; ?>/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="email_wrong[<?php echo $ekey; ?>]" <?php echo ($emailData['is_wrong'] == 1) ? "checked" : ""; ?> value="1" class="checkbox"/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <?php
                                            if($ekey != 0) {
                                                ?>
                                                <a href='javascript:void(0);' onclick='RemoveEmailRow(<?php echo $ekey; ?>)'><i class='icon-remove red'></i></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php } else { ?>
                                    <tr id='email_0'>
                                        <!-- <td>1</td> -->
                                        <td>
                                            <label for="option_1" class="control-label"></label>
                                            <input type="text" class="span12" placeholder="" name="email[0]" id="email0">
                                        </td>
                                        <td>
                                            <label>
                                                <input type="radio" name="email_primary[]" value="0" class="radio" checked="checked"/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="email_wrong[0]"  class="checkbox"/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td></td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="span12" id='email_add_remove' style="margin-left: 0px;">
                            <button type="button" class="btn btn-mini btn-success" onclick="AddEmailRow();"><i class="icon-plus"></i></button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="doc_titles">Phone Numbers</label>
                    <div class="controls">
                        <table id='pn_rows' class="table table-bordered span6">
                            <thead>
                                <tr>
                                    <th width="65%">Number</th>
                                    <th width="15%">Is Primary?</th>
                                    <th width="15%">Is Wrong</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(count($number) > 0){
                                foreach($number as $phkey => $numberData){
                                    ?>
                                    <tr id='number_<?php echo $phkey; ?>'>
                                        <!-- <td>1</td> -->
                                        <td>
                                            <label for="option_1" class="control-label"></label>
                                            <input type="text" class="span12" placeholder="" value="<?php echo $numberData['contact']; ?>" name="phone_number[<?php echo $phkey; ?>]" id="phone_number<?php echo $phkey; ?>">
                                        </td>
                                        <td>
                                            <label>
                                                <input type="radio" name="phone_number_primary[]" value="<?php echo $phkey; ?>" class="radio" <?php echo ($numberData['is_primary'] == 1) ? "checked" : ""; ?>/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="phone_number_wrong[<?php echo $phkey; ?>]" value="1" <?php echo ($numberData['is_wrong'] == 1) ? "checked" : ""; ?>/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <?php
                                            if($phkey != 0) {
                                            ?>
                                            <a href='javascript:void(0);' onclick='RemoveNumberRow(<?php echo $phkey; ?>)'><i class='icon-remove red'></i></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                    <tr id='number_0'>
                                        <!-- <td>1</td> -->
                                        <td>
                                            <label for="option_1" class="control-label"></label>
                                            <input type="text" class="span12" placeholder="" name="phone_number[0]" id="phone_number0">
                                        </td>
                                        <td>
                                            <label>
                                                <input type="radio" name="phone_number_primary[]" value="0" class="radio" checked="checked"/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="phone_number_wrong[0]" value="1" class="checkbox"/><span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td></td>
                                    </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <div class="span12" id='number_add_remove' style="margin-left: 0px;">
                            <button type="button" class="btn btn-mini btn-success" onclick="AddNumberRow();"><i class="icon-plus"></i></button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="address">Address</label>
                    <div class="controls">
                        <div class="span12">
                            <textarea name="address" id="address" rows="3" ><?php echo $data['address']; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="pincode" class="control-label">Pincode<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" value="<?php echo $data['pincode']; ?>" name='pincode' id='pincode' placeholder="ex.395001"/>
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

                <?php /*
                <div class="control-group">
                    <label for="city_id" class="control-label">Tellecaller<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="user_id" name="user_id" data-placeholder="Select tellecaller" class="chzn-select">
                            <option></option>
                            <?php echo $tellecallerDd; ?>
                        </select>
                        <i id='city_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="user_id" class="help-inline"></span>
                    </div>
                </div>
 */ ?>



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