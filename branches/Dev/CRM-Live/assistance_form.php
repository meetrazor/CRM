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

$middle_breadcrumb = array('title' => 'Leads', 'link' => 'lead.php');
include_once 'core/session.php';
$ses = new Session();
$ses->init();

include_once 'core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once 'core/Db.php';
include_once 'core/sMail.php';
$db = new Db();
$db->ConnectionOpen();
$db->CharactersetUTF8();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();




// Creating Permission Object
include_once 'core/Permission.php';
$acl = new Permission();

include_once 'core/SiteSettings.php';



// Core::PrintArray($acl->AllowedPages());exit;
//$postData = parse_str(file_get_contents("php://input"));
//$postData = $db->FilterParameters($_POST);
$postData = Array
(
    "name_" =>  "Retail Special",
    "Associates"=>"",
    "profileID"=> "71",
    "mobile"=> 8976677224,
    "address"=> "aaaaaaaa, bbbbbbbhhhhgh",
    "pincode"=> 395001,
    "state"=> "Austurland",
    "stateID"=> 1657,
    "city"=> "Bakkafjor ur",
    "cityID"=> 21435,
    "landmark"=> "nanpura",
    "category"=> "Corporate Debt"
)


?>
    <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Alliance Partner Admin - Dashboard</title>

        <meta name="description" content="overview &amp; stats" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!--basic styles-->

        <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
        <link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="assets/css/font-awesome.min.css" />

        <!--[if IE 7]>
        <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
        <![endif]-->

        <!--page specific plugin styles-->
        <?php
        if(isset($asset_css) && !empty($asset_css)){
            foreach ($asset_css as $css_file){
                ?>
                <link rel="stylesheet" type="text/css" href="<?php echo ASSETS.'/'.$css_file.'.css';?>">
            <?php
            }
        }
        ?>
        <!--fonts-->

        <!-- 		<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" /> -->

        <!--ace styles-->

        <link rel="stylesheet" href="assets/css/ace.min.css" />
        <link rel="stylesheet" href="assets/css/ace-responsive.min.css" />
        <link rel="stylesheet" href="assets/css/ace-skins.min.css" />

        <!--[if lte IE 8]>
        <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
        <![endif]-->

        <!--inline styles related to this page-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--basic scripts-->

        <!--[if !IE]>-->

        <script type="text/javascript">
            window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
        </script>

        <!--<![endif]-->

        <!--[if IE]>
        <script type="text/javascript">
            window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
        </script>
        <![endif]-->

        <script type="text/javascript">
            if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
        </script>
        <script src="assets/js/bootstrap.min.js"></script>
        <?php
        if(isset($asset_js) && !empty($asset_js)){
            foreach ($asset_js as $js_file){
                ?>
                <script type="text/javascript" src="<?php echo ASSETS.'/'.$js_file.'.js';?>"></script>
            <?php
            }
        }
        ?>
        <!--ace scripts-->

        <script src="assets/js/ace-elements.min.js"></script>
        <script src="assets/js/ace.min.js"></script>

        <script type="text/javascript">
            function showGritter(gclass,gtitle,gmessage){
                $.gritter.add({
                    time: '1000',
                    title: gtitle,
                    text: gmessage,
                    class_name: 'gritter-'+ gclass +' gritter-center  gritter-light'
                });
            }


        </script>
    </head>
<body>

<div class="main-container container-fluid">



<div class="main-content">



<div class='page-content'>
<?php
$data = $db->FilterParameters($_POST);
$table = 'lead_master';
$table_id = 'lead_id';
$error = '';
// Setting empty data array
$data = array();
$data_fields = $db->FetchTableField($table);
foreach ($data_fields as $field){
    $data[$field] = '';
}

$stateDd = $db->CreateOptions("html","state",array("state_id","state_name"),null,array("state_name"=>"asc"));
$cityDd = $db->CreateOptions("html","city",array("city_id","city_name"),null,array("city_name"=>"asc"));
$data['status_id'] =  $db->FetchCellValue("status_master","status_id","status_type = 'Lead' and is_default = 1");
$statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$data['status_id'],array("status_name"=>"asc"),"status_type = 'lead'");
$categoryId = Utility::addOrFetchFromTable("category_master",array("category_name"=>$postData['category']),"category_id","category_name = '{$postData['category']}'");
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$categoryId,array("category_name"=>"asc"));
$postData['landmark'] = strtolower($postData['landmark']);
//$subLocalityId = Utility::addOrFetchFromTable("sub_locality",array("sub_locality_name"=>$postData['landmark']),"sub_locality_id","lower(sub_locality_name) = '{$postData['landmark']}'");
//$subLocalityDd = $db->CreateOptions("html","sub_locality",array("sub_locality_id","sub_locality_name"),$subLocalityId,array("sub_locality_name"=>"asc"));
$emergencyId = $db->FetchCellValue("emergency_master","emergency_id","is_default = 1");
$defaultTimeSlot = $db->FetchCellValue("time_slot_master","time_slot_id","is_default = 1");
$emergencyDd = $db->CreateOptions("html","emergency_master",array("emergency_id","emergency_name"),$emergencyId,array("emergency_name"=>"asc"));
$timeSlotDd = $db->CreateOptions("html","time_slot_master",array("time_slot_id","time_slot_name"),$defaultTimeSlot,array("time_slot_name"=>"asc"));
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
        });

        $('#city_id').change(function(){
            var city_id = $(this).val();
            var category_id = $("#category_id").val();
            $.ajax({
                url: 'control/sub_locality.php?act=get_cus_par',
                data : { city_id : city_id,category_id:category_id },
                type:'post',
                dataType: 'json',
                beforeSend: function(){
                    $('#sub_locality_loader').show();
                },
                complete: function(){
                    $('#sub_locality_loader').hide();
                },
                success: function(resp){
//                        $('#sub_locality_id').html(resp);
//                        $("#sub_locality_id").trigger("liszt:updated");
                    $('#partner_id').html(resp.partner_dd);
                    $('#customer_id').html(resp.customer_dd);
                    $('#assistance_fees').val(resp.assistance_fees);
                    $("#partner_id").trigger("liszt:updated");
                    $("#customer_id").trigger("liszt:updated");
                }
            });
        });

//        $('#sub_locality_id').change(function(){
//            var city_id = $(this).val();
//            $.ajax({
//                url: 'control/sub_locality.php?act=get_cus_par',
//                data : { id : city_id },
//                type:'post',
//                dataType: 'json',
//                beforeSend: function(){
//                    $('#sub_locality_loader').show();
//                },
//                complete: function(){
//                    $('#sub_locality_loader').hide();
//                },
//                success: function(resp){
//                    $('#partner_id').html(resp.partner_dd);
//                    $('#customer_id').html(resp.customer_dd);
//                    $("#partner_id").trigger("liszt:updated");
//                    $("#customer_id").trigger("liszt:updated");
//                }
//            });
//        });

        $('#category_id').change(function(){
            var city_id = $("#city_id").val();
            var category_id = $(this).val();
            $.ajax({
                url: 'control/sub_locality.php?act=get_cus_par',
                data : { city_id : city_id,category_id:category_id },
                type:'post',
                dataType: 'json',
                beforeSend: function(){
                    $('#assistance_loader').show();
                },
                complete: function(){
                    $('#assistance_loader').hide();
                },
                success: function(resp){
                    $('#assistance_fees').val(resp.assistance_fees);
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
                            city = value[count - 3];
                            $("#state_id option").filter(function() {
                                return this.text == stateonly;
                            }).prop('selected', true);
                            $("#state_id").trigger("liszt:updated");
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
                            async:false,
                            type: "post"
                        }
                    },
                    state_id: { required : true },
                    city_id: { required : true },
                    status_id: { required : true },
                    category_id: { required : true },
                    customer_id: { required : true },
                    partner_id: { required : true },
                    sub_locality_id: { required : true },
                    time_slot: { required : true },
                    emergency_id: { required : true },
                    assistance_fees: { required : true,number:true },
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
                    mobile: { required : 'Please enter mobile no',"remote" :"mobile number is duplicate"},
                    state_id: { required : 'Please select state'},
                    status_id: { required : 'Please select status'},
                    category_id: { required : 'Please select category'},
                    customer_id: { required : 'Please select customer'},
                    partner_id: { required : 'Please select partner'},
                    sub_locality_id: { required : 'Please select sub locality'},
                    city_id: { required : 'Please select city'}
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
                        url: 'control/assistance_form.php?act=addedit',
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
                                //window.location = "http://67.23.248.106/~aliancepartner/admin/PayUMoney/PayUMoney.php";
                                postAndRedirect("http://67.23.248.106/~aliancepartner/admin/PayUMoney/PayUMoney.php",resObj.data);
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

        $("#pincode").focusout();
        $("#category_id").change();

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

    function postAndRedirect(url, postData)
    {
        var postFormStr = "<form method='POST' action='" + url + "'>\n";

        for (var key in postData)
        {
            if (postData.hasOwnProperty(key))
            {
                postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
            }
        }

        postFormStr += "</form>";

        var formElement = $(postFormStr);

        $('body').append(formElement);
        $(formElement).submit();
    }
    </script>
    <div class='row-fluid'>
        <form class="form-horizontal" id="form_add">
                <div class="control-group">
                    <label class="control-label" for="lead_name">Lead Name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="span12">
                            <input value="<?php echo $postData['name_']; ?>" type="text" name="lead_name" id="lead_name" placeholder="ex. Stephan Joe" />
                            <input value="<?php echo $postData['profileID']; ?>" type="hidden" name="app_user_id" id="app_user_id"/>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="mobile_no">Mobile<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input value="<?php echo $postData['mobile']; ?>" type="tel" name="mobile_no" placeholder="ex.897668XXXX" id="mobile_no" class="" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="address">Address<small class="text-error"> *</small></label>
                    <div class="controls">
                        <textarea name="address" id="address" rows="3" class=""><?php echo $postData['address']; ?></textarea>
                    </div>
                </div>


                <div class="control-group">
                    <label for="landmark" class="control-label">Landmark<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="text" name='landmark' id='landmark' placeholder="ex. nanpura" value="<?php echo $postData['landmark']; ?>" class=""/>
                    </div>
                </div>

                <div class="control-group">
                    <label for="pincode" class="control-label">Pincode<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="tel" name='pincode' id='pincode' placeholder="ex.395001" value="<?php echo $postData['pincode']; ?>" class=""/>
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
                    <label for="emergency_id" class="control-label">Emergency<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="emergency_id" name="emergency_id" data-placeholder="Select Emergency" class="chzn-select">
                            <option></option>
                            <?php echo $emergencyDd; ?>
                        </select>
                        <span for="emergency_id" class="help-inline"></span>
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
                    <label for="category_id" class="control-label">Category<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="category_id" name="category_id" data-placeholder="Select Category" class="chzn-select">
                            <option></option>
                            <?php echo $categoryDd; ?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="time_slot" class="control-label">Time Slot<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select id="time_slot" name="time_slot" data-placeholder="Select Category" class="chzn-select">
                            <option></option>
                            <?php echo $timeSlotDd; ?>
                        </select>
                        <span for="time_slot" class="help-inline"></span>
                    </div>
                </div>



                <div class="control-group">
                    <label class="control-label" for="assistance_fees">Assistance Fees</label>
                    <div class="controls">
                        <input type="text" readonly name="assistance_fees" id="assistance_fees">
                        <i id='assistance_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="about_user">Remarks</label>
                    <div class="controls">
                        <textarea name="remarks" id="remarks" rows="3" class=""></textarea>
                    </div>
                </div>


                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-ok bigger-110"></i>Proceed and Make Payment
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