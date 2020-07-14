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

$middle_breadcrumb = array('title' => 'Banks', 'link' => 'bank.php');
include_once 'header.php';
$table = 'bank_master';
$table_id = 'bank_id';
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

$contacts = $db->FetchToArray("bank_domain","*","$table_id = '{$id}'");
$domain = array();
foreach($contacts as $contact){
    $domain[] = $contact;
}
?>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
    $(function() {

        $(".chzn-select").chosen({
            allow_single_deselect:true,
        });


        $('#btn_cancel').click(function(){
            form = $(this).closest('form');
            form.find('div.control-group').removeClass("success error");
            form.find('span.help-inline').text("");
            var domain = $('#domain').val();
            form.clearForm();
            $('#domain').val(domain);
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

            $("#frm_bank").validate({
                rules: {
                    bank_name: { required : true},
                },

                messages: {
                    bank_name: { required : 'Please enter bank name'},
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
                        url: 'control/bank_addedit.php?act=addedit',
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


        addValidation("input","#domain0",{
            required: true,
        });

    });

    function addValidation(type,selecter,rules){
        $(''+type+''+selecter+'').each(function () {
            $(this).rules('add', rules);
        });

    }

    var domain_index = <?php echo count($domain) + 1; ?>;
    function AdddomainRow(){
        domain_index ++;
        index = domain_index;
//        alert(index);
        doc_html = "<tr id='domain_" + index + "'>" +
            // "<td>"+ (index+1) +"</td>" +
            "<td>" +
            "<input type='tel'  class='span12'  id='domain" + index + "' name='domain[" + index + "]' placeholder='' />" +
            "</td>" +
            "<td><a href='javascript:void(0);' onclick='RemovedomainRow(" + index + ")'><i class='icon-remove red'></i></a></td>"+
            "</tr>";


        $('table#domain_rows tbody').append(doc_html);

        addValidation("input","#domain"+index+"",{
            required: true,
        });
    }

    function RemoveDomainRow(index){
//        alert(index);
        $('table#domain_rows tr#domain_'+index).slideUp().hide().remove();
    }





    </script>

    <div class='row-fluid'>
    <div class="span12">
    <form class="form-horizontal" id="frm_bank">

    <div class="control-group" id="first_name_dd">
        <label class="control-label" for="first_name"><span id="first_name_title">Bank Name</span><small class="text-error"> *</small></label>
        <div class="controls">
            <div class="span12">
                <input value="<?php echo $data['bank_name']; ?>" type="text" name="bank_name" id="bank_name" placeholder="" />
                <input value="<?php echo $id; ?>" type="hidden" name="bank_id" id="bank_id" />
            </div>
        </div>
    </div>



    <div class="control-group">
        <label class="control-label" for="doc_titles">Domain</label>
        <div class="controls">
            <table id='domain_rows' class="table table-bordered">
                <thead>
                <tr>
                    <th>Domain</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($domain) > 0){
                    foreach($domain as $ekey => $domainData){
                        ?>
                        <tr id='domain_<?php echo $ekey; ?>'>
                            <!-- <td>1</td> -->
                            <td>
                                <label for="option_1" class="control-label"></label>
                                <input type="text" class="span12" placeholder="" value="<?php echo $domainData['domain']; ?>" name="domain[<?php echo $ekey; ?>]" id="domain<?php echo $ekey; ?>">
                            </td>                           
                            <td>
                                <?php
                                if($ekey != 0) {
                                    ?>
                                    <a href='javascript:void(0);' onclick='RemoveDomainRow(<?php echo $ekey; ?>)'><i class='icon-remove red'></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr id='domain_0'>
                        <!-- <td>1</td> -->
                        <td>
                            <label for="option_1" class="control-label"></label>
                            <input type="text" class="span12" placeholder="" name="domain[0]" id="domain0">
                        </td>                       
                        <td></td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>
            <div class="span12" id='domain_add_remove' style="margin-left: 0px;">
                <button type="button" class="btn btn-mini btn-success" onclick="AdddomainRow();"><i class="icon-plus"></i></button>
            </div>
            <div class="clearfix"></div>
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