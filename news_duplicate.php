<?php
$asset_css = array(
    'css/jquery.gritter',
    'css/datepicker',
    'css/jquery-ui-1.10.3.custom.min',
    'css/chosen.create-option'
);

$asset_js = array(
    'js/date-time/bootstrap-datepicker.min',
    'js/jquery.gritter.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/ace-elements.min',
    'ckeditor/ckeditor',
    'ckeditor/adapters/jquery',
    'js/chosen.jquery.min',
    'js/chosen.create-option.jquery',
    'js/bootbox.min'
);
$middle_breadcrumb = array('title' => 'Rss Feed','link'=>'rss_feed.php' );

include_once 'core/CkeditorConfig.php';
$ckeditor_config = new CkeditorConfig();

include_once "header.php";
$table = 'rss_feed';
$table_id = 'rss_feed_id';
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
$childRssId = $db->FetchCellValue($table,"rss_feed_id","parent_rss_feed_id = '{$id}'");
$selected_tags = $db->FetchToArray('rss_feed_tags',array('tag_id'),"$table_id = '{$id}'");
$tagsDd = $db->CreateOptions('html', 'tag_master', array('tag_id','tag_name'), $selected_tags, array('tag_name' => 'asc'));
$categoryDd =$db->CreateOptions('html', 'category_master', array('category_id','category_name'), $data['category_id'], array('category_name' => 'asc'),"category_type = 'rss'");
$companyDd =$db->CreateOptions('html', 'company_master', array('company_id','company_name'), $data['company_id'], array('company_name' => 'asc'));
?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.date-picker').datepicker({
                orientation: 'top',
                autoclose:true
            }).next().on(ace.click_event, function(){
                $(this).prev().focus();
            });

            $("#tag_ids").chosen({
                create_option_text: 'Create Tags',
                create_option: function(term){
                    var chosen = this;
                    $.post('control/tags.php?act=add', {term: term}, function(data){
                        chosen.append_option({
                            value: data.value,
                            text: data.text,
                        });
                    },"json");
                },
                persistent_create_option: true,
                skip_no_results: true
            });

            $(".chzn-select").chosen({
                allow_single_deselect:true
            });


            $('textarea.editor').ckeditor(<?php echo $ckeditor_config->getJSONConfig1();?>);

            if (jQuery().validate) {
                var e = function(e) {
                    $(e).closest(".control-group").removeClass("success");
                };
                $("#news").validate({
                    rules:{
                        company_id:{required:true},
                        title:{required:true},
                        short_description:{required:true},
                        description:{required:<?php echo $ckeditor_config->ValidateCKEditor();?>},
                        start_date:{required:true, dateNL: true},
                        category_id:{required:true},
                        'image':{
                            required:true,
                            extension: "png|jpe?g|gif|bmp"
                        }
                    },
                    messages:{
                        company_id:{required:"Please select company"},
                        title:{required: 'Please enter news title'},
                        short_description:{required: 'Please short description'},
                        description:{required: 'Please Enter description'},
                        start_date:{required: 'Please select publish Date'},
                        category_id:{required:'Please select category'},
                        image:{
                            required:"please upload user photo",
                            extension:"please upload proper either of this format png,jpeg,gif or bmp"
                        },
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
                        $(e).closest(".control-group").removeClass("error").addClass("success");
                    },
                    submitHandler: function(form) {
                        $(form).ajaxSubmit({
                            url: 'control/news_addedit.php?act=addedit',
                            type:"post",
                            beforeSubmit: function (formData, jqForm, options) {

                                $(form).find('button').hide();
                                $('#loader').show();
                            },
                            complete:function(){
                                $('#loader').hide();
                                $(form).find('button').show();
                            },
                            dataType: 'json',
                            clearForm: false,
                            success: function (resObj) {

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

        });


        function DeleteImage(docid){
            bootbox.confirm("Are you sure to delete selected image(s)?", function(result) {
                if(result) {
                    $.ajax({
                        url:'control/rss_feed.php?act=deleteimage',
                        type:'post',
                        dataType: 'json',
                        data:{ id : docid},
                        success: function(resObj){
                            if(resObj.success){
                                var element = $('#new_image_'+docid+'');
                                element.fadeOut(500, function() { element.remove(); });
                                showGritter('success',resObj.title,resObj.msg);
                            }else{
                                showGritter('success',resObj.title,resObj.msg);
                            }

                        }
                    });
                }
            });
        }
    </script>
    <div class="row-fluid">
        <div class="span12">
            <form class='form-horizontal' id='news' method="post" enctype="multipart/form-data">

                <div class="control-group">
                    <label for="category_id" class="control-label">Company name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select name="company_id" id='company_id' class='chzn-select' data-placeholder="Select Company">
                            <option value=""></option>
                            <?php echo $companyDd;?>
                        </select>
                        <span for="company_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="category_id" class="control-label">Category name<small class="text-error"> *</small></label>
                    <div class="controls">
                        <select name="category_id" id='category_id' class='chzn-select' data-placeholder="Select Category Name">
                            <option value="">--Select--</option>
                            <?php echo $categoryDd;?>
                        </select>
                        <span for="category_id" class="help-inline"></span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="news_title" class="control-label">News Title<small class="text-error"> *</small></label>
                    <div class="controls">
                        <input type="text" name="title"  value="<?php echo $data['title']; ?>" id="title"><span class="help-inline"></span>
                        <input type="hidden" name="parent_rss_feed_id"  id="parent_rss_feed_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="rss_feed_id"  id="rss_feed_id" value="<?php echo $childRssId; ?>">
                    </div>
                </div>

                <div class="control-group">
                    <label for="short_description" class="control-label">Short Description<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="row-fluid input-append">
                            <label>
                                <textarea id="short_description" class="" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 67px;" name="short_description"><?php echo $data['short_description']; ?></textarea>
                                <span class="help-inline"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label for="long_description" class="control-label">Long Description<small class="text-error"> *</small></label>
                    <div class="controls">
                        <div class="row-fluid input-append">
                            <label>
                                <textarea id="description" class="span6 editor" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 67px;" name="description"><?php echo $data['description']; ?></textarea>
                                <span for="description" class="help-inline"></span>
                            </label>
                        </div>
                    </div>
                </div>



                <div class="control-group">
                    <label for="news_title" class="control-label">Image</label>
                    <div class="controls">
                        <input type="file" id='image' name='image[]' accept="image/jpeg,image/png,image/gif" multiple/>
                        <ul class="ace-thumbnails">
                            <?php
                            $docFiles = $db->FetchToArray("rss_feed_image",array("*"),"$table_id = '{$id}'");
                            if(is_array($docFiles) and count($docFiles) > 0){
                                ?>
                                <?php
                                $cnt = 1;
                                foreach($docFiles as $file){
                                    $file_path = NEWS_IMAGE_PATH_ABS;
                                    if($file['filename'] != '' && file_exists($file_path)){

                                        $fileExt = pathinfo($file['filename'],PATHINFO_EXTENSION);
                                        $fileName = pathinfo($file['real_filename'],PATHINFO_FILENAME);
                                        $fileabsPath =  NEWS_IMAGE_PATH_REL.$file['filename'];
                                        ?>
                                        <li id="new_image_<?php echo $file['rss_feed_image_id']; ?>">
                                            <a href="<?php echo NEWS_IMAGE_PATH_ABS.$file['filename'];?>" class="cboxElement">
                                                <img src="<?php echo $fileabsPath;?>" alt="<?php echo $file['real_filename'];?>" title="<?php echo $file['real_filename'];?>" width="100" height="75">
                                            </a>
                                            <div class="tools tools-bottom">
                                                <a href="javascript:void(0);" onclick="DeleteImage(<?php echo $file['rss_feed_image_id'];?>)">
                                                    <i class="icon-remove red"></i>
                                                </a>
                                                <a target="_blank" href="<?php echo NEWS_IMAGE_PATH_REL.$file['filename'];  ?>">
                                                    <i class="icon-download"></i>
                                                </a>
                                            </div>
                                        </li>

                                    <?php }
                                    $cnt = $cnt + 1;
                                } ?>

                            <?php
                            } else {
                                echo "no image available";
                            }
                            ?>
                        </ul>
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="start_date" >
                        Publish Date<small class="text-error"> *</small>
                    </label>
                    <div class="controls">
                        <div class="row-fluid input-append">
                            <input id="pub_date" class="input-small date-picker" data-placement="top" type="text" name="start_date"
                                   data-date-format="dd-mm-yyyy"
                                   value="<?php echo ($data['pub_date'] != '0000-00-00' && $data['pub_date'] != '') ? core::YMDToDMY($data['pub_date']) : ""; ?>"
                                   readonly="readonly">
                            <span class="add-on">
                                <i class="icon-calendar"></i>
                            </span>
                            <span for="pub_date" class="help-inline"></span>
                        </div>
                    </div>
                </div>


                <div class="control-group">
                    <label for="tag_ids" class="control-label">Tags</label>
                    <div class="controls">
                        <select name="tag_ids[]" id='tag_ids' multiple="multiple" data-placeholder="Choose Tags">
                            <option value=""></option>
                            <?php echo $tagsDd;?>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label for="is_active" class="control-label">Active:</label>
                    <div class="controls">
                        <div class="span3">
                            <label>
                                <input type="checkbox" <?php echo ($data['is_active'] == 1)  ? "checked" : "";?> class="ace-switch ace-switch-2" name="is_active">
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-small btn-info">
                        <i class="icon-ok bigger-110"></i> Save
                    </button>
                    <div id='loader' class="span2 hide"><i class="icon-spinner icon-spin orange bigger-150 "></i> Please wait...</div>
                </div>
            </form>
        </div>
    </div>
<?php
include_once 'footer.php';
?>