<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
    'css/chosen.create-option'
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/js/fnStandingRedraw',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min',
    'js/chosen.create-option.jquery',
);
include_once 'header.php';
$categoryDd = $db->CreateOptions("html","category_master",array("category_id","category_name"),null,array("category_name"=>"asc"),"category_type = 'rss'");
?>

    <script type="text/javascript">
    $(function() {

        $('.modal').on('shown.bs.modal', function () {
            $('.chzn-select', this).chosen({
                allow_single_deselect:true
            });

        });


        $('[data-rel=tooltip]').tooltip();

        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_company = $('#dg_company').dataTable({
            "sDom": "<'row-fluid'<'span6'li>rf><'table-responsive't><'row-fluid'p>",
            "bPaginate":true,
            oLanguage : {
                sSearch : "Search _INPUT_",
                sLengthMenu : " _MENU_ ",
                sInfo : "_START_ to _END_ of _TOTAL_",
                sInfoEmpty : "0 - 0 of 0",
                oPaginate : {
                    sFirst : '<i class="icon-double-angle-left"></i>',
                    sLast : '<i class="icon-double-angle-right"></i>',
                    sPrevious: '<i class="icon-angle-left"></i>',
                    sNext: '<i class="icon-angle-right"></i>'
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "lengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/company.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                {
                    mData:'company_id',
                    bSortable: false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="ids_'+v+'" name="company_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData":"company_name" },
                {
                    "mData": "is_active",
                    bSortable:true,
                    mRender: function(v,t,o){
                        return (v == 1) ? "Active" : "Inactive";
                    }
                },
                {
                    mData: null,
                    bSortable: false,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'Edit Company')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"EditRecord('"+ o['company_id'] +"')\" class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i> </a>&nbsp";
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'Delete Company')))   {
                        ?>
                        act_html = act_html+"<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['company_id'] +"')\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a> ";
                        <?php } ?>
                        return act_html;
                    }
                }
            ],
            fnPreDrawCallback: function () {
                if (!responsiveHelper2) {
                    responsiveHelper2 = new ResponsiveDatatablesHelper(this, breakpointDefinition);
                }
            },
            fnRowCallback  : function (nRow) {
                responsiveHelper2.createExpandIcon(nRow);
            },
            fnDrawCallback : function (oSettings) {
                responsiveHelper2.respond();
                $(this).removeAttr('style');
            }
        });

        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };
            // Company type validateion code  
            $("#frm_company").validate({
                rules:{
                    company_name:{required:true }
                },
                messages:{
                    company_name:{required:'Please enter company name'}
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
                        url: 'control/company.php?act=addedit',/*i have changed here*/
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
                            $(e).find('button').show();
                            if(resObj.success){
                                $(e).clearForm();
                                $('#modal_add_company').modal('hide');
                                dg_company.fnDraw();
                                showGritter('success',resObj.title,resObj.msg);
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
        $('#add_company').click(function(){
            $('form#frm_company input,select#company_type').val('');
            $('form#frm_company').find('div.control-group').removeClass("success error");
            $('form#frm_company').find('div.control-group span.help-inline').text('');
            $('#act_company').text('Add');
            $('#action').val('add');
            $('#company_code').prop('readonly', false);
            $('#modal_add_company').modal('show');
        });

        $('#edit_company').click( function (e) {

            var selected_list = $('#dg_company tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select company to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            var selected_tr = selected_list[0];
            var ele = $(selected_tr).closest('tr').get(0);
            //console.log(ele);
            var aData = dg_company.fnGetData( ele );


            $.each(aData, function(key,val){
                var inputType = $('form#frm_company #'+key).prop("type");
                if(inputType == 'checkbox'){
                    if(val == 1){
                        $('form#frm_company #'+key).prop("checked",true);
                    } else {
                        $('form#frm_company #'+key).prop("checked",false);
                    }
                }else {
                    if($('form#frm_company #'+key).length){
                        $('form#frm_company #'+key).val(val);
                    }
                }
            });

            $.ajax({
                url: 'control/company.php?act=companyCat',
                type:'post',
                dataType:'html',
                data:{ company_id : aData.company_id },
                success: function(resp){
                    $("#category_body").html(resp);
                }
            });

            $('#act_company').text('Edit');
            $('form#frm_company').find('div.control-group').removeClass("success error");
            $('form#frm_company').find('div.control-group span.help-inline').text('');
            $('#action').val('edit');
            $('#modal_add_company').modal('show');
        });

        $("#modal_add_company").on("hidden", function () {
            $('#dg_company tbody input[type=checkbox]:checked').prop("checked",false);
        });

        $('#delete_company').click(function(){

            var delete_ele = $('#dg_company tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select company to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected company(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/company.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id },
                            success: function(resp){
                                dg_company.fnStandingRedraw();
                                if(resp.success) {
                                    showGritter('success',resp.title,resp.msg);
                                } else {
                                    showGritter('error',resp.title,resp.msg);
                                }
                            }
                        });
                    }
                });
            }
        });
    });

    function DeleteRecord(companyId){

        $('#ids_'+companyId).prop('checked', true);
        $('#delete_company').click();
    }

    function EditRecord(sid){

        $('#ids_'+sid).prop('checked', true);
        $('#edit_company').click();
    }

    function addValidation(type,selecter,rules){
        $(''+type+''+selecter+'').each(function () {
            $(this).rules('add', rules);
        });

    }

    function AddCategoryRow(){
        var lastIndex = $("#category_rows tbody tr:last").attr("id").split("_").pop();
        index = parseInt(lastIndex) + 1;

        category_html = "<tr id='category_" + index + "'>" +
            // "<td>"+ (index+1) +"</td>" +
            "<td>" +
            "<select id='category_id"+index+"' name='category_id[]' data-placeholder='Select category' class=''>" +
            "</select>" +
            "<span for='category_id"+index+"' class='help-inline'></span>" +
            "</td>" +

            "<td>"+
            "<label>"+
            "<input type='url'  name='rss_feed_link[]' id='rss_feed_link"+index+"'>"+
            "</label>"+
            "</td>"+
            "<td><a href='javascript:void(0);' onclick='RemoveCategoryRow(" + index + ")'><i class='icon-remove red'></i></a></td>"+
            "</tr>";

        $('table#category_rows tbody').append(category_html);

        $('#category_id'+lastIndex+'').find("option:not(:selected)").clone().appendTo("#category_id"+index+"");

        addValidation("input","#rss_feed_link"+index+"",{
            required: true,
            url:true
        });

        addValidation("select","#category_id"+index+"",{
            required: true
        });
    }

    function RemoveCategoryRow(index){
        $('table#category_rows tr#category_'+index).slideUp().hide().remove();
    }

    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Company
					<span class="widget-toolbar pull-right">
                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'Add Company')))   {
                            ?>
                            <a id='add_company' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Add" class="white"><i class="icon-plus icon-large white"></i>Add</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'Edit Company')))   {
                            ?>
                            <a id='edit_company' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="white"><i class="icon-pencil icon-large white"></i>Edit</a>&nbsp;|
                        <?php } ?>

                        <?php
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'Delete Company')))   {
                            ?>
                            <a id='delete_company' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="white"><i class="icon-trash icon-large white"></i>Delete</a>
                        <?php } ?>
					</span>
                    </div>
                    <table id='dg_company' class="table table-condensed table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox" id='chk_master'/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="modal_add_company" class="modal hide" tabindex="-1">
        <form id='frm_company' class="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger"><span id='act_company'>Add</span>&nbsp;Company</h4>
            </div>
            <div class="modal-body overflow-scroll">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label class="control-label" for="company_name">Company Name</label>
                            <div class="controls">
                                <input type="text" name="company_name" id="company_name" placeholder="ex. Business Line"/>
                                <input type="hidden" name="company_id" id="company_id"/>
                                <input type="hidden" id='action' name='action' value="add"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="category">Category</label>
                            <div class="controls table-responsive">
                                <table id='category_rows' class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>RSS Feed Link</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody id="category_body">
                                        <tr id='category_0'>
                                            <!-- <td>1</td> -->
                                            <td>
                                                <select id="category_id0" name="category_id[]" data-placeholder="Select category" class="chzn-select">
                                                    <option></option>
                                                    <?php echo $categoryDd; ?>
                                                </select>
                                                <span for='category_id0' class='help-inline'></span>
                                            </td>
                                            <td>
                                                <input type="url" name="rss_feed_link[]" id="rss_feed_link0"/>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="span12" id='number_add_remove' style="margin-left: 0px;">
                                    <button type="button" class="btn btn-mini btn-success" onclick="AddCategoryRow();"><i class="icon-plus"></i></button>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" checked>
                                <span class="lbl"> Active</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-small btn-primary">
                    <i class="icon-ok"></i>
                    Save
                </button>
                <button class="btn btn-small" data-dismiss="modal">
                    <i class="icon-remove"></i>
                    Cancel
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
