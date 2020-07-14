<?php
$asset_css = array(
    'css/jquery.gritter',
    'data-tables/responsive/css/datatables.responsive',
);

$asset_js = array(
    'js/lodash/lodash.min',
    'data-tables/js/jquery.dataTables.min',
    'data-tables/js/DT_bootstrap',
    'data-tables/responsive/js/datatables.responsive',
    'data-tables/js/fnStandingRedraw',
    'js/jquery.gritter.min',
    'js/bootbox.min',
    'js/jquery-validation/dist/jquery.validate.min',
    'js/jquery-validation/dist/jquery.validate.extension',
    'js/jquery-form/jquery.form'
);

include_once 'header.php';
//echo date("Y-m-d H:i:s",Utility::rssToTime("Tue, 17 Jan 2017 14:32:56 +05:30"));
//echo Utility::rssToTime("Tue, 17 Jan 2017 12:26:47 +0530");
?>
    <style type="text/css">
        table#dg_rss_feed tfoot {
            display: table-header-group;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function(){

        $('[data-rel=tooltip]').tooltip();
        $(".rss_feed-count").html(0);

        var breakpointDefinition = {
            pc: 1280,
            tablet: 1024,
            phone : 480
        };

        $("#syn_rss").click(function(){

            $.ajax({
                url: 'rss_feed_syn.php?token=<?php echo $token; ?>',
                type:'post',
                dataType:'json',
                beforeSend: function (formData, jqForm, options) {
                    bootbox.dialog('<i class="icon-spinner icon-spin orange bigger-125"></i> Please have patience, data is being syncronize');
                },
                success: function(resp){
                    bootbox.hideAll();
                    if(resp.success) {
                        dg_rss_feed.fnStandingRedraw();
                        showGritter('success',resp.title,resp.msg);
                    } else {
                        showGritter('error',resp.title,resp.msg);
                    }

                }
            });

        });
        var responsiveHelper2 = undefined;
        dg_rss_feed = $('#dg_rss_feed').dataTable({
            "sDom": "<'row-fluid'<'span6'li>r><'table-responsive't><'row-fluid'p>",
            oLanguage : {
                sSearch : "Search _INPUT_",
                sLengthMenu : " _MENU_ ",
                sInfo : "_START_ to _END_ of _TOTAL_",
                sInfoEmpty : "0 - 0 of 0",
                oPaginate : {
                    sFirst : '<i class="icon-double-angle-left"></i>',
                    sLast : '<i class="icon-double-angle-right"></i>',
                    sPrevious: '<i class="icon-angle-left"></i>',
                    sNext: '<i class="icon-angle-right"></i>',
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "bScrollCollapse" : true,
            "aLengthMenu": [[10,25,50,100], [10,25,50,100]],
            "sAjaxSource": "control/rss_feed.php",
            "fnServerParams": function ( aoData ) {
                aoData.push({ "name": "act", "value": "fetch" });
                server_params = aoData;
            },
            "aaSorting": [[ 7, "desc" ]],
            "aoColumns": [
                {
                    mData: "rss_feed_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                {"mData": "company_name"},
                {"mData": "category_name" },
                {
                    "mData": "title",
                    mRender: function(v,t,o){
                        return '<a target="_blank" href='+o['link']+' >'+v+'</a>';
                    }
                },
                {
                    "mData": "description",
                    "bSortable":false
                },
                {
                    "mData": "short_description",
                    "bSortable":false
                },
                {
                    "mData": "tags",
                    "bSortable":false
                },
                {"mData": "pub_date"},
                {"mData": "created_at"},
                {"mData": "created_by"},
                {
                    bSortable :false,
                    mData: null,
                    mRender: function(v,t,o){
                        var act_html = '';
                        <?php
                        if($acl->IsAllowed($login_id,'Rss feed', 'Rss feed', 'Edit Rss feed')){
                        ?>
                        var editUrl = (o['is_direct'] == 1) ? "news_duplicate.php" : "news_addedit.php";
                        act_html = act_html + "<a href='"+editUrl+"?token=<?php echo $token; ?>&id="+o['rss_feed_id']+"' id='rss_feed_"+o['rss_feed_id']+"' class='btn btn-minier btn-warning' data-placement='bottom' data-rel='tooltip' data-original-title='Add/Edit Record' title='Edit Record'><i class='icon-pencil bigger-120'></i></a>&nbsp";
                        <?php } ?>

                        <?php
                        if($acl->IsAllowed($login_id,'Rss Feed', 'Rss Feed', 'Delete Rss Feed')){
                        ?>
                        act_html = act_html + "<a href='javascript:void(0);' onclick=\"DeleteRecord('"+ o['rss_feed_id'] +"')\" class='btn btn-minier btn-danger' data-placement='bottom' data-rel='tooltip' data-original-title='Delete' title='Delete'><i class='icon-trash bigger-120'></i></a>&nbsp";
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
            "fnRowCallback" : function(nRow, aData, iDisplayIndex){
                responsiveHelper2.createExpandIcon(nRow);
                if(aData.is_changed == 1){
                    nRow.className = "success";
                }
                return nRow;

            },
            fnDrawCallback : function (oSettings) {
                responsiveHelper2.respond();
                $(this).removeAttr('style');
                $('[data-rel=tooltip]').tooltip();
            }
        });

        $("tfoot input").keyup( function () {
            dg_rss_feed.fnFilter( this.value, $(this).attr("colPos") );
        });


        if (jQuery().validate) {
            var e = function(e) {
                $(e).closest(".control-group").removeClass("success");
            };         

            $("#frm_update").validate({

                rules:{
                    short_description:{required:true }
                },
                messages:{
                    short_description:{required:'Please enter description'}
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
                        url: 'control/rss_feed.php?act=update',
                        type:'post',
                        beforeSubmit: function (formData, jqForm, options) {
                            $(e).find('button').hide();
                        },
                        dataType: 'json',
                        clearForm: false,
                        success: function (resObj, statusText) {
                            $(e).find('button').show();
                            if(resObj.success){
                                $(e).clearForm();
                                $('#modal_update').modal('hide');
                                dg_rss_feed.fnStandingRedraw();
                                showGritter('success',resObj.title,resObj.msg);
                            }else{
                                showGritter('error', resObj.title, resObj.msg);
                            }
                        }
                    });
                }
            });
        }


        $('#edit_record').click( function (e) {
            var selected_list = $('#dg_rss_feed tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select a record to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            href = $('#edit_record').attr('href');
            href += '&id=' + selected_list.val();
            $('#edit_record').attr('href',href);
            return true;
        });

        $('#delete_record').click(function(){

            var delete_ele = $('#dg_rss_feed tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select record to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected record(s)? It will delete all record related data and can not be reverted", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/rss_feed.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_rss_feed.fnDraw();
                                showGritter('success',resp.title,resp.msg);
                            }
                        });
                    }
                });
            }
        });




    });

    function ExportToExcel(ele){

        var query_string = decodeURIComponent($.param(server_params));
        $(ele).attr('href','export_rss_feeds.php?='+query_string);
        return true;
    }


    function DeleteRecord(rid){

        $('#chk_'+rid).prop('checked', true);
        $('#delete_record').click();
    }

    $(document).on("click",".syn_rss",function(){
        var rowData = dg_rss_feed.fnGetData($(this).parents('tr'));
        var rss_feed_id = rowData.rss_feed_id;
        var shortDescription = rowData.short_description;
        $('form#frm_update input[type=text]').val('');
        $('form#frm_update').find('div.control-group').removeClass("success error");
        $('form#frm_update').find('div.control-group span').text('');
        $("#short_description").val(shortDescription);
        $("#rss_feed_id").val(rss_feed_id);
        $('#modal_update').modal('show');
    });

    </script>
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class='span12'>
                    <div class="table-header">
                        Rss feed List
					<span class="widget-toolbar pull-right">

                        <a id='add_news' href="news_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Add" class='btn'><i class="icon-plus icon-large white"></i>Add</a>
						<a id='edit_record' href="news_addedit.php?token=<?php echo $token; ?>" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="btn"><i class="icon-pencil icon-large white"></i>Edit</a>
						<a id='delete_record' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="btn"><i class="icon-trash icon-large white"></i>Delete</a>

                        <?php
                        if($acl->IsAllowed($login_id,'Rss feed', 'Rss feed', 'Syn Rss feed')){
                            ?>
                            <a id='syn_rss' href="javascript:void(0)" data-placement="top" data-rel="tooltip" data-original-title="Synchronize Rss Feed" class="white"><i class="icon-refresh icon-large white"></i>Syn</a>
                        <?php } ?>


					</span>
                    </div>
                    <table id='dg_rss_feed' class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th class="center" width="5%">
                                <label>
                                    <input type="checkbox"/>
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Short Description</th>
                            <th>Tags</th>
                            <th>Pub Date</th>
                            <th>Created On</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th class="center">
                            </th>
                            <th>
                                <input type="text"  placeholder="company" name="filter_company" class="span7" colPos="1">
                            </th>
                            <th>
                                <input type="text"  placeholder="category" name="filter_category" class="span7" colPos="2">
                            </th>
                            <th>
                                <input type="text"  placeholder="title" name="filter_title" class="span7" colPos="3">
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <input type="text"  placeholder="user" name="filter_user" class="span7" colPos="4">
                            </th>
                            <th></th>
                        </tr>
                        </tfoot>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Short description model box start
    <div id="modal_update" class="modal hide" tabindex="-1">
        <form id='frm_update'>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">Short Description Add/Update</h4>
            </div>
            <div class="modal-body overflow-visible">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="control-group">
                            <label for="short_description" class="control-label">Short Description</label>
                            <div class="controls">
                                <textarea name='short_description' id='short_description' class="span6"></textarea>
                                <input type="hidden" name="rss_feed_id" id="rss_feed_id"/>
                            </div>
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
            </div>
        </form>
    </div>
    <!-- Short description model box end-->
<?php
include_once 'footer.php';
?>