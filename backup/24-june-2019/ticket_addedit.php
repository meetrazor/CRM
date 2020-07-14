<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    'css/chosen',
    'css/bootstrap-timepicker',
    'css/colorbox',
    'css/bootstrap-datetimepicker.min',
);

$asset_js = array(
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/chosen.jquery.min',
    'js/ajax-chosen.min',
    'js/jquery.autosize-min',
    'js/date-time/bootstrap-datepicker.min',
    'js/date-time/bootstrap-timepicker.min',
    'js/bootstrap-datetimepicker.min',
    'js/jquery.maskedinput.min',
    'js/bootbox.min',
    'js/jquery.colorbox-min',
    'js/ckeditor',    
    //'ckeditor/adapters/jquery',
);
//include_once 'core/CkeditorConfig.php';
//$ckeditor_config = new CkeditorConfig();
$middle_breadcrumb = array('title' => 'CW Tickets', 'link' => 'ticket.php');
include_once 'header.php';
$table = 'tickets';
$table_id = 'ticket_id';
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
    if(!$acl->IsAllowed($login_id,'ticket', 'Ticket', 'Update Ticket After Close')){
        $error = 'You Can\'t edit Close status data';
    }
}
if(isset($id) && $id!=''){
    if($error != ''){
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
if(isset($id) && $id!=''){
    $queryStageDd = $db->CreateOptions("html","query_stage_master",array("query_stage_id","query_stage_name"),$data['query_stage_id'],array("query_stage_name"=>"asc"),"is_active = '1' or query_stage_id = '{$data['query_stage_id']}' ");
    $productTypeDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),$data['product_type_id'],array("category_name"=>"asc"),"is_active = '1' or category_id = '{$data['product_type_id']}' ");
    $subQueryStageDd = $db->CreateOptions("html","sub_query_stage_master",array("sub_query_stage_id","sub_query_stage_name"),$data['sub_query_stage_id'],array("sub_query_stage_name"=>"asc"),"is_active = '1' or sub_query_stage_id = '{$data['sub_query_stage_id']}' ");
    $statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$data['status_id'],array("status_name"=>"asc"),"status_type = 'support' and (is_active = '1' or status_id = '{$data['status_id']}')");
//    $reasonDd = $db->CreateOptions("html","reason_master",array("reason_id","reason_name"),$data['reason_id'],array("reason_name"=>"asc"),"is_active = '1' or reason_id = '{$data['reason_id']}' ");
    $loanDd = $db->CreateOptions("html","loan_type_master",array("loan_type_id","loan_type_name"),$data['loan_type_id'],array("loan_type_name"=>"asc"),"is_active = '1' or loan_type_id = '{$data['loan_type_id']}' ");

}else{
    $statusId = $db->FetchCellValue("status_master","status_id","status_type = 'support' and is_default = '1' and is_active = '1'");
    $statusDd = $db->CreateOptions("html","status_master",array("status_id","status_name"),$statusId,array("status_name"=>"asc"),"status_type = 'support' and is_active = '1'");
    $reasonId = $db->FetchCellValue("reason_master","reason_id","is_default = '1'");
//    $reasonDd = $db->CreateOptions("html","reason_master",array("reason_id","reason_name"),$reasonId,array("reason_name"=>"asc"),"is_active = '1'");
    $loanId = $db->FetchCellValue("loan_type_master","loan_type_id","is_default = '1'");
    $loanDd = $db->CreateOptions("html","loan_type_master",array("loan_type_id","loan_type_name"),$loanId,array("loan_type_name"=>"asc"),"is_active = '1'");
    $productTypeDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"),"is_active = '1'");
}
$selectedCondition = $data['customer_id'] != '' ? "customer_id = '{$data['customer_id']}'" : null;
$customerDd = $db->CreateOptions("html","customer_master",array("customer_id","concat(customer_name,' ',COALESCE(mobile_no,''))"),$data['customer_id'],array("customer_name"=>"asc"),$selectedCondition,array(0,10));
$dispositionDd = $db->CreateOptions("html","disposition_master",array("disposition_id","disposition_name"),$data['disposition_id'],array("disposition_name"=>"asc"),"disposition_type = 'support'");
$queryTypeDd = $db->CreateOptions("html","query_type_master",array("query_type_id","query_type_name"),$data['query_type_id'],array("query_type_name"=>"asc"));
$bankDd = $db->CreateOptions("html","bank_master",array("bank_id","bank_name"),$data['bank_id'],array("bank_name"=>"asc"));
$userAssignTo = $db->CreateOptions("html","admin_user",array("user_id","concat(first_name,' ',last_name)"),$data['assign_to'],array("concat(first_name,' ',last_name)"=>"asc"),"user_type = '".UT_ST."' and user_level = 'level1'");
$callFrom = $db->GetEnumvalues("tickets","call_from");
?>
       <script>
           </script>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
    $(function() {
        ClassicEditor.create( document.querySelector( '#comment' ));
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

        $('#category_id').change(function(){
            var city_id = $("#city_id").val();
            var category_id = $(this).val();
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
                    $('#assistance_fees').val(resp.assistance_fees);
                }
            });
        });

        $('#sub_locality_id').change(function(){
            var city_id = $(this).val();
            $.ajax({
                url: 'control/sub_locality.php?act=get_cus_par',
                data : { id : city_id },
                type:'post',
                dataType: 'json',
                beforeSend: function(){
                    $('#sub_locality_loader').show();
                },
                complete: function(){
                    $('#sub_locality_loader').hide();
                },
                success: function(resp){
                    $('#partner_id').html(resp.partner_dd);
                    $('#customer_id').html(resp.customer_dd);
                    $("#partner_id").trigger("liszt:updated");
                    $("#customer_id").trigger("liszt:updated");
                }
            });
        });

        $('#product_type_id').change(function(){

            var product_type_id = $(this).val();
            var reason_id = '<?php  echo (isset($data['reason_id'])) ? $data['reason_id'] : ""; ?>';
            $.ajax({
                url: 'control/ticket_addedit.php?act=get_reason_dd', data :
                { id : product_type_id ,reason_id: reason_id },type:'post',dataType: 'html',
                beforeSend: function(){
                    $('#reason_loader').show();
                },
                complete: function(){
                    $('#reason_loader').hide();
                },
                success: function(resp){
                    $('#reason_id').html(resp);
                    $("#reason_id").trigger("liszt:updated");
                }
            });
        });

        // reason drop down change event
        $('#reason_id').change(function(){

            // query stage drop down load event (reason wise)
            <?php if(isset($data['query_stage_id']) && $data['query_stage_id'] != '' ){ ?>
            var reason_id = <?php echo $data['reason_id'] ?>;
            var query_stage_id = <?php echo $data['query_stage_id'] ?>;
            <?php }else{ ?>
            var reason_id = $('#reason_id').val();
            var query_stage_id = 0;
            <?php } ?>
            $.ajax({
                url: 'control/ticket_addedit.php?act=get_query_stage_dd', data : { id : reason_id, query_stage_id : query_stage_id },type:'post',dataType: 'html',
                beforeSend: function(){
                    $('#query_stage_loader').show();
                },
                complete: function(){
                    $('#query_stage_loader').hide();
                },
                success: function(resp){
                    $('#query_stage_id').html(resp);
                    $("#query_stage_id").trigger("liszt:updated");
                }
            });
        });

        $('#loan_type_id').change(function(){
            var loan_type_id = $(this).val();
            $.ajax({
                url: 'control/ticket_addedit.php?act=get_product_type_dd',
                data : {
                    id : loan_type_id,
                    product_type_id : '<?php  echo (isset($data['product_type_id'])) ? $data['product_type_id'] : ""; ?>'
                },
                type:'post',
                dataType: 'html',
                beforeSend: function(){
                    $('#product_type_loader').show();
                },
                complete: function(){
                    $('#product_type_loader').hide();
                },
                success: function(resp){
                    $('#product_type_id').html(resp);
                    $("#product_type_id").trigger("liszt:updated");
                }
            });
        });

        // query stage drop down chnage event
        $('#query_stage_id').change(function(){

            var query_stage_id = $('#query_stage_id').val();
            var sub_query_stage_id = '<?php  echo (isset($data['reason_id'])) ? $data['sub_query_stage_id'] : ""; ?>';

            $.ajax({
                url: 'control/ticket_addedit.php?act=get_sub_query_stage_dd', data : { id : query_stage_id, sub_query_stage_id: sub_query_stage_id },type:'post',dataType: 'html',
                beforeSend: function(){
                    $('#sub_query_stage_loader').show();
                },
                complete: function(){
                    $('#sub_query_stage_loader').hide();
                },
                success: function(resp){
                    $('#sub_query_stage_id').html(resp);
                    $("#sub_query_stage_id").trigger("liszt:updated");
                }
            });
        });

        <?php if(isset($data['reason_id']) && $data['reason_id'] != '' ){ ?>
            $('#product_type_id').change();
        <?php } ?>

        <?php if(isset($data['query_stage_id']) && $data['query_stage_id'] != '' ){ ?>
            $('#reason_id').change();
        <?php } ?>

        <?php if(isset($data['sub_query_stage_id']) && $data['sub_query_stage_id'] != '' ){ ?>
            $('#query_stage_id').change();
        <?php } ?>

        $('#loan_type_id').change();

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
                    email: { email: true},
                    customer_id: { required : true },
                    status_id: { required : true },
                    //disposition_id: { required : true },
                    reason_id: { required : true },
                    loan_type_id: { required : true },
                    product_type_id: { required : true },
                    query_stage_id: { required : true },
                    sub_query_stage_id: { required : true },
                    query_type_id: { required : true },
                    call_from: { required : true },
                    mobile_no: {
                        required : true,
                        regex: ['mobile','mobile']
                    },
                },

                messages: {
                    email: { required : 'Please enter email address', email: 'Please enter valid email address',"remote" :"email address is duplicate" },
                    customer_id: { required : 'Please select customer'},
                    loan_type_id: { required : 'Please select loan type'},
                    product_type_id: { required : 'Please select product type'},
                    sub_query_stage_id: { required : 'Please select sub query stage' }
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
                        url: 'control/ticket_addedit.php?act=addedit',
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
                var myvalue = $("#iframe").contents().find("#customer_name").val();
//                alert(myvalue);
                typeRefresh();
                typeDetails();
            }
        };


        $('[data-rel="customer_edit"]').colorbox(colorbox_params);
        $('[data-rel="customer_add"]').colorbox(colorbox_params);

        $("#customer_id").change(function(){
            typeDetails();
        });

        $("#customer_edit").click(function(){
            var customerId = $("#customer_id").val();
            $(this).attr('href','customer_addedit_colorbox.php?id='+customerId+'');
        });

        $('#mobile_no').mask('9999999999');
        //  $('#landline_no').mask('(?999)9999999?9');
        $('#pincode').mask('999999');


        $(".form_datetime").datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            showMeridian: true,
            startDate: "<?php echo date("Y-m-d H:i") ?>",
        });


        $("#customer_id").ajaxChosen({
            minTermLength:3,
            type: 'post',
            url: 'control/customer.php?act=getcustomer',
            dataType: 'json'
        }, function (data) {
            var results = [];

            $.each(data, function (i, val) {
                results.push({ value: val.value, text: val.text });
            });
            return results;
        });

        $("#customer_id").change();

    });

    function typeEdit(){
        $("#customer_edit").click();
    }

    function typeRefresh(){
        var customerId = $("#customer_id").val();
        $.ajax({
            url: 'control/customer.php?act=customerdd', data : { customer_id : customerId,customer_limit : 1 },type:'post',dataType: 'html',
            beforeSend: function(){
                $('#customer_loader').show();
            },
            complete: function(){
                $('#customer_loader').hide();
            },
            success: function(resp){
                $('#customer_id').html(resp);
                $("#customer_id").trigger("liszt:updated");
            }
        });
    }


    function typeDetails(event = 'hide'){
        var customerId = $("#customer_id").val();
        $.ajax({
            url: 'control/customer.php?act=customerhistory', data : { customer_id : customerId, event : event },type:'post',dataType: 'html',
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

    function DeleteImage(id){
        bootbox.confirm("Are you sure to delete image", function(result) {
            if(result) {
                $.ajax({
                    url:'control/ticket.php?act=deleteimage',
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

    $(document).on('click','.sub_query_stage_info', function(){

        var sqsId = $('#sub_query_stage_id').val();

        if(sqsId){
            $.ajax({
                url: 'control/sub_query_stage.php?act=get_sub_query_stage_description',
                type:'post',
                dataType:'html',
                beforeSend: function(){
                    $("#modal_sub_q_description").modal('show');
                    $("#description_div").html(wait);
                },
                data:{id : sqsId },
                success: function(resp){
                    $("#description_div").html(resp);
                    $("#modal_sub_q_description").modal('show');
                }
            });
        }
    });


    </script>
    <div class='row-fluid'>
    <form class="form-horizontal" id="form_add">
    <div class="span5">

        <input type="hidden" name="ticket_id" id="ticket_id" value="<?php echo $data['ticket_id']; ?>">
        <input type="hidden" name="ticket_number" id="ticket_number" value="<?php echo $data['ticket_number']; ?>">
        <div class="control-group">
            <label for="customer_id" class="control-label">Customer<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="customer_id" name="customer_id" data-placeholder="Select Customer" class="chzn-select">
                    <option value=""></option>
                    <?php echo $customerDd; ?>
                </select>

                <?php
                if($acl->IsAllowed($login_id,'customer', 'customer', 'Edit customer')){
                    ?>
                    <a data-rel='customer_edit' id="customer_edit" class="btn btn-minier btn-warning" href='javascript:void(0);'><i class="icon-pencil icon-large" data-placement="bottom" data-rel="tooltip" data-original-title="Update Selected Customer"></i></a>
                <?php } ?>

                <?php
                if($acl->IsAllowed($login_id,'CUSTOMER', 'CUSTOMER', 'Add CUSTOMER')){
                    ?>
                    <a  data-rel='customer_add' class="btn btn-minier btn-success" href='customer_addedit_colorbox.php'><i class="icon-plus icon-large" data-placement="bottom" data-rel="tooltip" data-original-title="Add New Customer"></i></a>
                <?php } ?>

                <span for="customer_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="loan_type_id" class="control-label">Loan Type<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="loan_type_id" name="loan_type_id" data-placeholder="Select Loan Type" class="chzn-select">
                    <option></option>
                    <?php echo $loanDd; ?>
                </select>
                <span for="loan_type_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="product_type_id" class="control-label">Product Type<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="product_type_id" name="product_type_id" data-placeholder="Select Product Type" class="chzn-select">
                    <option></option>
                    <?php echo $productTypeDd; ?>
                </select>
                <i id='product_type_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                <span for="product_type_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="reason_id" class="control-label">Reason<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="reason_id" name="reason_id" data-placeholder="Select Reason" class="chzn-select">
                    <option></option>
                    <?php echo $reasonDd; ?>
                </select>
                <i id=reason_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                <span for="reason_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group inline">
            <label class="control-label" for="call_from">Query Received From
                <small class="text-error"> *</small>
            </label>
            <div class="controls">
                <select class="chzn-select" data-placeholder="Select Call From" id="call_from" name="call_from">
                    <option></option>
                    <?php
                    if(count($callFrom) > 0){
                        foreach($callFrom as $callFromData) {
                            $selected = ($callFromData == $data['call_from']) ? "selected" : "";
                            ?>

                            <option value="<?php echo $callFromData; ?>" <?php echo $selected; ?>><?php echo ucwords($callFromData); ?></option>
                        <?php
                        }
                    }
                    ?>
                </select>
                <span for="call_from" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="query_type_id" class="control-label">Query Type<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="query_type_id" name="query_type_id" data-placeholder="Select Query Type" class="chzn-select">
                    <option></option>
                    <?php echo $queryTypeDd; ?>
                </select>
                <span for="query_type_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="query_stage_id" class="control-label">Query Stage<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="query_stage_id" name="query_stage_id" data-placeholder="Select Query Stage" class="chzn-select">
                    <option></option>
                    <?php echo $queryStageDd; ?>
                </select>
                <i id=query_stage_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                <span for="query_stage_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="sub_query_stage_id" class="control-label">Sub Query Stage<small class="text-error"> *</small></label>
            <div class="controls">
                <select id="sub_query_stage_id" name="sub_query_stage_id" data-placeholder="Select Sub Query Stage" class="chzn-select">
                    <option></option>
                    <?php echo $subQueryStageDd; ?>
                </select>
                    <a id="v_sub_query_stage_description" href='javascript:void(0)' class="btn btn-minier btn-success sub_query_stage_info">
                        <i class="icon-eye-open icon-large" data-placement="bottom" data-rel="tooltip" data-original-title="View description"></i>
                    </a>
                <i id=sub_query_stage_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                <span for="sub_query_stage_id" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="query_stage_id" class="control-label">Bank</label>
            <div class="controls">
                <select id="bank_id" name="bank_id" data-placeholder="Select Bank" class="chzn-select">
                    <option></option>
                    <?php echo $bankDd; ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="query_stage_id" class="control-label">Assign To</label>
            <div class="controls">
                <select id="assign_to" name="assign_to" data-placeholder="Select User" class="chzn-select">
                    <option></option>
                    <?php echo $userAssignTo; ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="about_user">Comment<small class="text-error"> *</small></label>
            <div class="controls">
                <textarea name="comment" id="comment"  rows="3" class="comment"><?php echo ($data['comment']); ?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="assistance_fees">Exp. Time For Resolution</label>
            <div class='controls'>
                <div class='row-fluid input-append'>
                    <input class='input-medium form_datetime' data-placement='top' type='text' placeholder='Exp. Time For Resolution'
                           name='resolve_date_time' data-date-format='yyyy-mm-dd' id="follow_up_date_time"
                           readonly='readonly'
                           value="<?php echo ($data['resolve_date_time'] != '0000-00-00 00:00:00' && $data['resolve_date_time'] != '') ? date('d-m-Y h:i:s',strtotime($data['resolve_date_time'])) : ""; ?>">
                    <span class='add-on'><i class='icon-calendar'></i></span>
                    <span for='resolve_date_time' class='help-inline'></span>
                </div>
                <span for="resolve_date_time" class="help-inline"></span>
            </div>
        </div>

        <div class="control-group">
            <label for="customer_id" class="control-label">Disposition</label>
            <div class="controls">
                <select id="disposition_id" name="disposition_id" data-placeholder="Select disposition" class="chzn-select">
                    <option></option>
                    <?php echo $dispositionDd; ?>
                </select>
                <span for="disposition_id" class="help-inline"></span>
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

        <div class='control-group'>
            <label for='filename' class='control-label'>Upload Document</label>
            <div class='controls'>
                <input type='file' id="filename" name='filename[]' placeholder='File' class='upload' multiple/>
            </div>
        </div>

        <?php

            $ticketDocument = $db->FetchToArray("ticket_documents","*","ticket_id = '{$id}' order by ticket_document_id desc");
            foreach($ticketDocument as $documentData){
                ?>

                <div class='control-group'>

                    <div class='controls'>
                        <?php

                            $fileExt = pathinfo($documentData['filename'],PATHINFO_EXTENSION);
                            $fileabsPath = (!in_array($fileExt,Utility::imageExtensions())) ? "uploads/docimage.png" : TICKET_IMAGE_PATH.$documentData['filename'];

                            if($documentData['filename'] != '' && file_exists(TICKET_IMAGE_PATH_ABS.$documentData['filename'])){
                                ?>
                                <div class='row-fluid inline' id='new_image_<?php echo $documentData['ticket_document_id']; ?>'>
                                    <ul class="ace-thumbnails">
                                        <li>
                                            <a href="javascript:void(0);" class="cboxElement">
                                                <img src="<?php echo $fileabsPath;?>" alt="<?php echo $documentData['real_filename'];?>" title="<?php echo $documentData['real_filename'];?>" style="width: 150px;height: 150px">
                                            </a>
                                            <div class="tools tools-bottom">
                                                <?php if($login_id == $documentData['created_by']) { ?>
                                                <a href="javascript:void(0);" onclick="DeleteImage(<?php echo $documentData['ticket_document_id'];?>)">
                                                    <i class="icon-remove red"></i>
                                                </a>
                                                <?php } ?>
                                                <a href="<?php echo TICKET_IMAGE_PATH.$documentData['filename'];?>"  data-placement="top" data-rel="tooltip" data-original-title="Download">
                                                    <i class="icon-download"></i>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>

                                </div>
                            <?php
                            }
                        ?>

                    </div>

                </div>

            <?php } ?>

        <?php
        if($statusClose == ''){
            ?>
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
        <?php } ?>

    </div>

    <div class="span5 table-responsive" id="type_details">
        <!--<a data-rel='show_customer_history' id="show_customer_history" class="btn btn-minier btn-primary" href='javascript:void(0);' onclick="typeDetails();"><i class="icon icon-eye-open bigger-150" data-placement="bottom" data-rel="tooltip" data-original-title="View Customer History"></i> View Customer History</a>-->
    </div>

    </form>
    </div>

<!--    show sub query description-->

    <div id="modal_sub_q_description" class="modal hide" tabindex="-1">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="blue bigger">Sub Query Stage Description</h4>
        </div>
        <div class="modal-body overflow-scrollable">
            <div class="row-fluid">
                <div class="span12">
                    <div class="control-group" id="description_div">

                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-small" data-dismiss="modal">
                <i class="icon-remove"></i>
                Cancel
            </button>
        </div>
    </div>

<?php
include_once 'footer.php';
?>
