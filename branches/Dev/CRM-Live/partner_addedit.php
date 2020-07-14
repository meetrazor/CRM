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

$middle_breadcrumb = array('title' => 'Partners', 'link' => 'partner.php');
include_once 'header.php';
$table = 'partner_master';
$table_id = 'partner_id';
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
$selectedEducation = $db->FetchToArray("partner_education","*","partner_id = '{$id}'");
$selectedCategory = $db->FetchToArray("partner_category","category_id","partner_id = '{$id}'");
$selectedSubLocality = $db->FetchToArray("partner_sub_locality","sub_locality_id","partner_id = '{$id}'");
$educationDd = $db->CreateOptions("html","education_master",array("education_id","education_name"),null,array("education_name"=>"asc"));
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$selectedCategory,array("category_name"=>"asc"));
$subLocalityDd = $db->CreateOptions("html","sub_locality",array("sub_locality_id","sub_locality_name"),$selectedSubLocality,array("sub_locality_name"=>"asc"));
$partnerDd = $db->CreateOptions("html","partner_master",array("partner_id","concat(first_name,' ',last_name) as partner_name"),$data['parent_partner_id'],array("concat(first_name,' ',last_name)"=>"asc"),"is_active = 1 and partner_type = 'ia'");
$partnerType = explode(",",$data['partner_type']);
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

                $("#form_add").validate({
                    rules: {
                        email: {
                            required : true,
                            email: true,
                            "remote" :
                            {
                                url: 'control/partner.php?act=checkemail',
                                data:{"partner_id":"<?php echo $id;?>"},
                                async:false,
                                type: "post"
                            }
                        },
                        state_id: { required : true },
                        city_id: { required : true },
                        'category_ids[]': { required : true },
                        'education_ids[]': { required : true },
                        'sub_locality_ids[]': { required : true },
                        first_name: { required : true, regex: ['name','Name']},
                        last_name: { required : true, regex: ['name','Name']},
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
                        'category_ids[]': { required : "Please select product/Loan" },
                        'education_ids[]': { required : "Please select education"},
                        'sub_locality_ids[]': { required : "Please select sub locality"},
                        first_name: { required : 'Please enter first name'},
                        last_name: { required : 'Please enter last name'},
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
                            url: 'control/partner_addedit.php?act=addedit',
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

            })


            $('#mobile_no').mask('9999999999');
            $('#pincode').mask('999999');
        })  ;


        function addValidation(type,selecter,rules){
            $(''+type+''+selecter+'').each(function () {
                $(this).rules('add', rules);
            });

        }

        var number_index = '<?php echo count($selectedEducation) + 1; ?>';
        function AddNumberRow(){
            number_index ++;
            index = number_index;
            numberHtml = "<tr id='number_" + index + "'>" +
                "<td>"+
                "<select name='education["+index+"][education_id]' id='education_tye_"+index+"' class='chzn-select input-medium insurer' data-placeholder='Select Education'>"+
                "<option></option>"+
                "<?php echo $educationDd; ?>"+
                "</select>"+
                "<span for='education_tye_"+index+"' class='help-inline'></span>" +
                "</td>" +
                "<td>"+
                "<input type='file'  name='education"+index+"[]' id='education"+index+"' accept='application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf'  class='' multiple/>"+
                "</td>"+
                "<td><a href='javascript:void(0);' onclick='RemoveNumberRow(" + index + ")'><i class='icon-remove red icon-2x'></i></a></td>"+
                "</tr>";
            $('table#education_rows tbody').append(numberHtml);
            $(".chzn-select").chosen({
                allow_single_deselect:true,
                width:"100%"
            });

            addValidation("select","#education_tye_"+index+"",{
                required: true
            });

            addValidation("input","#education"+index+"",{
                required: true,
                extension: "xls|xlsx|Pdf"
            });

        }

        function RemoveNumberRow(index){
            $('table#education_rows tr#number_'+index).slideUp().hide().remove();
        }

        function DeleteImage(id){
            bootbox.confirm("Are you sure to delete image", function(result) {
                if(result) {
                    $.ajax({
                        url:'control/partner_addedit.php?act=deleteimage',
                        type:'post',
                        dataType: 'json',
                        data:{ id : id},
                        success: function(resObj){
                            var element = $('#new_image_'+id+'');
                            element.fadeOut(500, function() { element.remove(); });
                            showGritter('success',resObj.title,resObj.msg);
                        }
                    });
                }
            });
        }
    </script>

    <div class='row-fluid'>
        <div class="span12">
            <form class="form-horizontal" id="form_add">


                <div class="control-group">
                    <label class="control-label" for="first_name">First Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['first_name']; ?>" type="text" name="first_name" id="first_name" placeholder="ex. Stephan" />
                            <input value="<?php echo $id; ?>" type="hidden" name="partner_id" id="partner_id" />
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="last_name">Last Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $data['last_name']; ?>" type="text" name="last_name" id="last_name" class="" placeholder="ex. Joe" />
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

                <div class="control-group user_dd">
                    <label class="control-label" for="partner_ty[e">Partner Type<small class="text-error"> *</small></label>
                    <div class="controls">
                        <label class="inline">
                            <input name="partner_type[]" type="checkbox" value="ia" <?php echo (in_array("ia",$partnerType)) ? "checked" : ""; ?>>
                            <span class="lbl">IA</span>
                        </label>
                        <label class="inline">
                            <input name="partner_type[]" type="checkbox" value="lp" <?php echo (in_array("lp",$partnerType)) ? "checked" : ""; ?>>
                            <span class="lbl">LP</span>
                        </label>
                        <label class="inline">
                            <input name="partner_type[]" type="checkbox" value="dp" <?php echo (in_array("dp",$partnerType)) ? "checked" : ""; ?>>
                            <span class="lbl">DP</span>
                        </label>
                        <span for="activity_on" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="partner_id" class="control-label">Parent Partner</label>
                    <div class="controls">
                        <select id="parent_partner_id" name="parent_partner_id" data-placeholder="Select Parent Partner" class="chzn-select">
                            <option></option>
                            <?php echo $partnerDd; ?>
                        </select>
                        <span for="parent_partner_id" class="help-inline"></span>
                    </div>
                </div>


                <div class="control-group">
                    <label for="category_id" class="control-label">Product/Loan Type<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="category_id" name="category_ids[]" data-placeholder="Select Product/Loan" class="chzn-select" multiple>
                            <option></option>
                            <?php echo $categoryDd; ?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>
                <?php
                    /*
                <div class="control-group">
                    <label for="education_id" class="control-label">Education<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="education_id" name="education_ids[]" data-placeholder="Select Education" class="" multiple>
                            <option></option>
                            <?php echo $educationDd; ?>
                        </select>
                        <span for="education_id" class="help-inline"></span>
                    </div>
                </div>
                    */ ?>


                <div class="control-group">
                    <label for="education_id" class="control-label">Education<small class="text-error"> *</small></label>
                    <div class="controls table-responsive">
                        <table id='education_rows' class="table">
                            <thead style="border-bottom: hidden">
                            <tr>
                                <th width="">
                                    Education Company<small class="text-error"> *</small>
                                </th>
                                <th width="15%">
                                    Upload Doc<small class="text-error"> *</small>
                                </th>
                                <th>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(count($selectedEducation) > 0) {
                                $last_key = key( array_slice( $selectedEducation, -1, 1, TRUE ) );
                                foreach($selectedEducation as  $key => $educationData) {

                                    $educationDd = $db->CreateOptions("html","education_master",array("education_id","education_name"),$educationData['education_id'],array("education_name"=>"asc"));
                                    ?>
                                    <tr id='number_<?php echo $key; ?>'>
                                        <td>
                                            <select name='education[<?php echo $key; ?>][education_id]' id='education_type_<?php echo $key; ?>' class="chzn-select insurer" data-placeholder="Select Education">
                                                <option></option>
                                                <?php echo $educationDd; ?>
                                            </select>
                                            <span for="education_type_<?php echo $key; ?>" class="help-inline"></span>
                                        </td>
                                        <td>
                                            <ul class="ace-thumbnails">
                                                <?php
                                                $educationDoc = $db->FetchToArray("partner_education_document","*","partner_education_id = '{$educationData['partner_education_id']}'");
                                                if(count($educationDoc) > 0) {

                                                    foreach($educationDoc as $docData) {
                                                        $file_path = PARTNER_EDUCATION_IMAGE_PATH_ABS.$docData['filename'];
                                                        $fileExt = pathinfo($docData['filename'],PATHINFO_EXTENSION);
                                                        $fileabsPath = (Utility::docExtensions($fileExt)) ? "uploads/docimage.png" : PARTNER_EDUCATION_IMAGE_PATH_ABS.$docData['filename'];
                                                        if($docData['filename'] != '' && file_exists($file_path)){
                                                            ?>

                                                            <li id="new_image_<?php echo $docData['partner_education_document_id'];?>">
                                                                <a href="<?php echo PARTNER_EDUCATION_IMAGE_PATH_ABS.$docData['filename'];?>" class="cboxElement">
                                                                    <img src="<?php echo $fileabsPath;?>" alt="<?php echo $docData['real_filename'];?>" title="<?php echo $docData['real_filename'];?>" width="100" height="75">
                                                                    <div class="text">
                                                                        <div class="inner"><?php echo $docData['real_filename'];?></div>
                                                                    </div>
                                                                </a>
                                                                <div class="tools tools-bottom">
                                                                    <a href="javascript:void(0);" onclick="DeleteImage(<?php echo $docData['partner_education_document_id'];?>)">
                                                                        <i class="icon-remove red"></i>
                                                                    </a>
                                                                    <a href="<?php echo PARTNER_EDUCATION_IMAGE_PATH.$docData['filename'];  ?>">
                                                                        <i class="icon-download"></i>
                                                                    </a>
                                                                </div>
                                                            </li>
                                                        <?php
                                                        }
                                                    }
                                                    echo "</ul>";
                                                }
                                                ?>
                                                <input type="file"  class="upload-doc" id="education<?php echo $key; ?>" name="education<?php echo $key; ?>[]" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf" multiple>
                                        </td>
                                        <td>                                            
                                            <input type="hidden" class="" value="<?php echo $educationData['partner_education_id']; ?>"  name="education[<?php echo $key; ?>][partner_education_id]" id="partner_education_id<?php echo $key; ?>" />
                                        </td>                                        
                                        <td>
                                            <?php
                                            if($last_key == $key) {
                                                ?>
                                                <div class="span12" id='number_add_remove' style="margin-left: 0px;">
                                                    <button type="button" id="add_row" class="btn btn-mini btn-success" onclick="AddNumberRow();"><i class="icon-plus"></i></button>
                                                </div>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } } else { ?>                                                            
                                <tr id='number_0'>
                                    <td>
                                        <select name='education[0][education_id]' id='education_type_0' class="chzn-select input-medium insurer" data-placeholder="Insurance Company">
                                            <option></option>
                                            <?php echo $educationDd; ?>
                                        </select>
                                        <span for="education_type_0" class="help-inline"></span>
                                    </td>
                                    <td>
                                        <input type="file"  class="" name="education0[]" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf" multiple>
                                    </td>
                                    <td>
                                        <div class="span12" id='number_add_remove' style="margin-left: 0px;">
                                            <button type="button" id="add_row" class="btn btn-mini btn-success" onclick="AddNumberRow();"><i class="icon-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>

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


                <?php
                /*
                <div class="control-group">
                    <label for="sub_locality_id" class="control-label">Sub Locality<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="sub_locality_id" name="sub_locality_ids[]" data-placeholder="Select Sub Locality" class="chzn-select" multiple>
                            <option></option>
                            <?php echo $subLocalityDd; ?>
                        </select>
                        <i id='sub_locality_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="sub_locality_id" class="help-inline"></span>
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