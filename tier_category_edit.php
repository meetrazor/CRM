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

$middle_breadcrumb = array('title' => 'Tier Category Rate', 'link' => 'tier_category.php');
include_once 'header.php';
$table = 'tier_category';
$table_id = 'tier_category_id';
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

if($error != ''){
    echo Utility::ShowMessage('Error: ', $error);
    include_once 'footer.php';
    exit;
}
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$data['category_id'],array("category_name"=>"asc"));
$tierDd = $db->CreateOptions("html","tier_master",array("tier_id","tier_name"),$data['tier_id'],array("tier_name"=>"asc"),"is_active = 1");
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






                $("#form_add").validate({
                    rules:{
                        tier_id:{required:true },
                        'category_id':{required:true },
                        'commission':{required:true,number:true },
                        max_withdrawal:{required:true,number:true },
                        effective_date:{required:true},
                    },
                    messages:{
                        tier_id:{required:'Please select tier name'},
                        commission:{required:'Please enter commission',number:'Please enter number only'},
                        max_withdrawal:{required:'Please enter withdrawal',number:'Please enter number only'},
                        effective_date:{required:'Please select effective date'},
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
                            url: 'control/tier_category_edit.php?act=edit',
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
            //  $('#landline_no').mask('(?999)9999999?9');
            $('#pincode').mask('999999');
        });
    </script>
    <div class='row-fluid'>
        <form class="form-horizontal" id="form_add">

            <div class="control-group">
                <label class="control-label" for="user_type">Select Tier</label>
                <div class="controls">
                    <select id="tier_id" name="tier_id" data-placeholder="select tier" class="chzn-select">
                        <option></option>
                        <?php   echo $tierDd; ?>
                    </select>
                    <span for="tier_id" class="help-inline"></span>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="category_id">Select Category</label>
                <div class="controls">
                    <select id="category_id" name="category_id" data-placeholder="select tier" class="chzn-select">
                        <option></option>
                        <?php   echo $categoryDd; ?>
                    </select>
                    <span for="category_id" class="help-inline"></span>
                </div>
            </div>

            <div class='control-group'>
                <label class='control-label' for='effective_date'>
                    Effective Date<small class="text-error"> *</small>
                </label>
                <div class='controls'>
                    <div class='row-fluid input-append'>
                        <input class='input-small date-picker' data-placement='top' type='text' placeholder='effective date'
                               name='effective_date' id="effective_date" data-date-format='dd-mm-yyyy'
                               value="<?php echo ($data['effective_date'] != '0000-00-00' && $data['effective_date'] != '') ? Core::YMDToDMY($data['effective_date']) : '';?>"
                               readonly='readonly'>
                        <span class='add-on'><i class='icon-calendar'></i></span>
                        <span for='effective_date' class='help-inline'></span>
                    </div>
                </div>
            </div>

            <div class="control-group">
                <label for="commission" class="control-label">Commission<small class="text-error"> *</small></label>
                <div class="controls">
                    <input type="tel" name='commission' id='commission' placeholder="ex.100" value="<?php echo $data['commission']; ?>" class=""/>
                    <input type="hidden" name='tier_category_id' id='tier_category_id' placeholder="ex.100" value="<?php echo $id; ?>" class=""/>
                </div>
            </div>


            <div class="control-group">
                <label for="max_withdrawal" class="control-label">Max Withdrawal<small class="text-error"> *</small></label>
                <div class="controls">
                    <input type="tel" name='max_withdrawal' id='max_withdrawal' placeholder="ex.100" value="<?php echo $data['max_withdrawal']; ?>" class=""/>
                </div>
            </div>


            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-ok bigger-110"></i>Update
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
<?php
include_once 'footer.php';
?>