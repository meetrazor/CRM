<?php
/**
 * Created by PhpStorm.
 * User: dt-server1
 * Date: 3/11/2019
 * Time: 1:27 PM
 */

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
    //'ckeditor/ckeditor',
    //'ckeditor/adapters/jquery',
);
//include_once 'core/CkeditorConfig.php';
//$ckeditor_config = new CkeditorConfig();
$middle_breadcrumb = array('title' => 'Sub Query Stage', 'link' => 'sub_query_stage.php');
include_once 'header.php';
$table = 'sub_query_stage_master';
$table_id = 'sub_query_stage_id';
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
        $data['loan_type_id'] = intval($db->FilterParameters($_GET['loan_type_id']));
        $data['category_id'] = intval($db->FilterParameters($_GET['category_id']));
        $data['reason_id'] = intval($db->FilterParameters($_GET['reason_id']));
    }else{
        $error = 'Invalid Record Or Record Not Found';
    }
} else {
    $error = 'Invalid Record Or Record Not Found';
}

//Core::PrintArray($data); die();

if(isset($id) && $id!=''){
    if($error != ''){
        echo Utility::ShowMessage('Error: ', $error);
        include_once 'footer.php';
        exit;
    }
}
if (isset($id) && $id != '') {
    $loanDd = $db->CreateOptions("html", "loan_type_master", array("loan_type_id", "loan_type_name"), $data['loan_type_id'], array("loan_type_name" => "asc"), "is_active = '1'");
//    $queryStageDd = $db->CreateOptions("html","query_stage_master",array("query_stage_id","query_stage_name"),$data['query_stage_id'],array("query_stage_name"=>"asc"),"is_active = '1' or query_stage_id = '{$data['query_stage_id']}' ");
} else {
    $loanDd = $db->CreateOptions("html", "loan_type_master", array("loan_type_id", "loan_type_name"), null, array("loan_type_name" => "asc"), "is_active = '1'");
//    $queryStageId = $db->FetchCellValue("query_stage_master", "query_stage_id", "is_default='1'");
//    $queryStageDd = $db->CreateOptions("html", "query_stage_master", array("query_stage_id","query_stage_name"), $queryStageId, array("query_stage_name" => "asc"),"is_active = '1'");
}

?>
    <script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script>
    <script>
    </script>
    <script type="text/javascript" xmlns="http://www.w3.org/1999/html">
        $(function() {

            ClassicEditor.create( document.querySelector( '#sub_query_stage_description' ));
            $(".chzn-select").chosen({
                allow_single_deselect:true
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

                $("#form_add").validate({

                   rules: {
                        loan_type_id: { required : true },
                        category_id: { required : true },
                        reason_id: { required : true },
                        query_stage_id: { required : true },
                        sub_query_stage_name: {
                            required : true,
                            maxlength : 100,
                        },
                        // sub_query_stage_description: { required : true },
                    },

                    messages: {
                        loan_type_id: { required : 'Please select loan type'},
                        category_id: { required : 'Please select product type'},
                        reason_id: { required : 'Please select reason'},
                        query_stage_id: { required : 'Please select query stage'},
                        sub_query_stage_name: {
                            required : 'Please enter sub query stage name',
                            maxlength : 'Max length is 100 character',
                        },
                        // sub_query_stage_description: { required : 'Please enter description'},
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
                            url: 'control/sub_query_stage_addedit.php?act=addedit',
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

            // loan type change event
            $('#loan_type_id').change(function() {
               <?php
                    if(isset($data['category_id']) && $data['category_id'] != '' ) { ?>
                    $('#category_id').change();
                    var category_id = <?= $data['category_id'] ?>;
                    var loan_type_id = <?= $data['loan_type_id'] ?>;
                <?php }else{ ?>
                    var category_id = 0;
                    var loan_type_id = $(this).val();
                <?php } ?>
                $.ajax({
                    url: 'control/sub_query_stage_addedit.php?act=get_product_type_dd',
                    data: {id: loan_type_id, category_id: category_id },
                    type: 'post',
                    dataType: 'html',
                    beforeSend: function () {
                        $('#product_type_loader').show();
                    },
                    complete: function () {
                        $('#product_type_loader').hide();
                    },
                    success: function (resp) {
                        $('#category_id').html(resp);
                        $("#category_id").trigger("liszt:updated");
                    }
                });
            });

            $('#category_id').change(function(){
                <?php
                if(isset($data['reason_id']) && $data['reason_id'] != '' ) { ?>
                    $('#reason_id').change();
                    var product_type_id = <?= $data['category_id']; ?>;
                    var reason_id = <?= $data['reason_id']; ?>;
                <?php }else{ ?>
                    var product_type_id = $(this).val();
                    var reason_id = 0;
                <?php } ?>
                $.ajax({
                    url: 'control/sub_query_stage_addedit.php?act=get_reason_dd', data : { id : product_type_id,reason_id: reason_id },type:'post',dataType: 'html',
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
            $('#reason_id').change(function() {
                <?php if(isset($data['query_stage_id']) && $data['query_stage_id'] != '' ){ ?>
                var reason_id = <?= $data['reason_id']; ?>;
                var query_stage_id = <?php echo $data['query_stage_id'] ?>;
                <?php }else{ ?>
                var reason_id = $(this).val();
                var query_stage_id = 0;
                <?php } ?>
                $.ajax({
                    url: 'control/sub_query_stage_addedit.php?act=get_query_stage_dd',
                    data: {id: reason_id, query_stage_id: query_stage_id},
                    type: 'post',
                    dataType: 'html',
                    beforeSend: function () {
                        $('#query_stage_loader').show();
                    },
                    complete: function () {
                        $('#query_stage_loader').hide();
                    },
                    success: function (resp) {
                        $('#query_stage_id').html(resp);
                        $("#query_stage_id").trigger("liszt:updated");
                    }
                });
            });

            <?php
                if(isset($data['loan_type_id']) && $data['loan_type_id'] != '' ) { ?>
                    $('#loan_type_id').change();
            <?php }
                if(isset($data['product_type_id']) && $data['product_type_id'] != '' ) { ?>
                    $('#product_type_id').change();
            <?php } ?>
        });

    </script>
    <div class='row-fluid'>
        <form class="form-horizontal" id="form_add">
            <div class="span12">

                <div class="control-group">
                    <label for="loan" class="control-label">Loan Type
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <select id="loan_type_id" name="loan_type_id" data-placeholder="Select Loan Type"
                                class="chzn-select">
                            <option></option>
                            <?php echo $loanDd; ?>
                        </select>
                        <span for="loan_type_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="product_type" class="control-label">Product Type
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <select id="category_id" name="category_id" data-placeholder="Select Product Type"
                                class="chzn-select">
                            <option></option>
                            <?php //echo $productTypeDd; ?>
                        </select>
                        <i id='product_type_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="reason" class="control-label">Reason
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <select id="reason_id" name="reason_id" data-placeholder="Select Reason"
                                class="chzn-select">
                            <option></option>
                            <?php //echo $reasonDd; ?>
                        </select>
                        <i id='reason_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="reason_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="query_stage_id" class="control-label">Query Stage<small class="text-error"> *</small></label>
                    <div class="controls">

                        <select id="query_stage_id" name="query_stage_id" data-placeholder="Select Query Stage" class="chzn-select">
                            <option value=""></option>
                            <?php // echo $queryStageDd; ?>
                        </select>
                        <i id=query_stage_loader' class="icon-spinner icon-spin orange bigger-150 hide"></i>
                        <span for="query_stage_id" class="help-inline"></span>
                        <input type="hidden" name="sub_query_stage_id" id="sub_query_stage_id" value="<?php echo ($data['sub_query_stage_id']); ?>"/>
                        <input type="hidden" id='action' name='action' value="add"/>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="sub_query_stage_name">Sub Query Stage Name
                        <small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <input type="text" name="sub_query_stage_name" id="sub_query_stage_name" value="<?php echo ($data['sub_query_stage_name']); ?>" placeholder="ex. GST"/>

                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="about_user">Description</label>
                    <div class="controls">
                        <textarea name="sub_query_stage_description" id="sub_query_stage_description"  rows="3" class="sub_query_stage_description"><?php echo ($data['sub_query_stage_description']); ?></textarea>
                    </div>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_active" id="is_active" <?php echo ($data['is_active'] == 1) ? "checked" : ""; ?> >
                        <span class="lbl"> Active</span>
                    </label>
                </div>

                <div class="controls">
                    <label>
                        <input type="checkbox" name="is_default" id="is_default" <?php echo ($data['is_default'] == 1) ? "checked" : ""; ?> >
                        <span class="lbl"> Default</span>
                    </label>
                </div>

            </div>

            <div class="span5 table-responsive" id="type_details">
            </div>

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

        </form>
    </div>
<?php
include_once 'footer.php';
?>