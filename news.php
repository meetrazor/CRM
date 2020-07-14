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
    // 'js/jquery-form/jquery.form',
    'js/jquery.gritter.min',
    'js/bootbox.min',
);

include_once 'header.php';
?>
<style type="text/css">
    table#dg_news tfoot {
        display: table-header-group;
    }
</style>
<script type="text/javascript">

    $(function() {

        $('[data-rel=tooltip]').tooltip();

        var breakpointDefinition = {
            tablet: 1024,
            phone : 480
        };
        var responsiveHelper2 = undefined;
        dg_news = $('#dg_news').dataTable({
            "sDom": "<'row-fluid'<'span6'l>r>t<'row-fluid'pi>",
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
            "bScrollCollapse" : true,
            "sAjaxSource": "control/news.php",//change here
            "fnServerParams": function ( aoData ) { //send other data to server side
                aoData.push({ "name": "act", "value": "fetch" });
            },
            "aaSorting": [[ 2, "desc" ]],
            "aoColumns": [
                {
                    mData: "news_id",
                    bSortable : false,
                    mRender: function (v, t, o) {
                        return '<label><input type="checkbox" id="chk_'+v+'" name="news_id[]" value="'+v+'"/><span class="lbl"></span></label>';
                    },
                    sClass: 'center'
                },
                { "mData": "news_title" },
                { "mData": "start_date" },
                { "mData": "category_name" },
                { "mData": "sub_category_name" },

                {
                    mData: null,
                    bSortable : false,
                    mRender: function(v,t,o){

                        var act_html = "<a href='news_edit.php?id="+ o['news_id'] +"' class='btn btn-minier btn-warning' title='Edit'><i class='icon-edit bigger-120'></i></a> "
                            + "<a href='javascript:void(0);' onclick=\"DeleteRecord("+ o['news_id'] +")\" class='btn btn-minier btn-danger' title='Delete'><i class='icon-trash bigger-120'></i></a>";
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


        $("tfoot input").keyup( function () {
            dg_news.fnFilter( this.value, $("tfoot input").index(this) );
        });


        $('#edit_news').click( function (e) {
            var selected_list = $('#dg_news tbody input[type=checkbox]:checked');
            var selected_length = selected_list.size();

            if(0 == selected_length){

                showGritter('info','Alert!','Please select a record to edit.');
                return false;
            }else if(selected_length > 1){
                showGritter('info','Alert!','Only single record can be edited at a time.');
                return false;
            }

            href = $('#edit_news').attr('href');
            href += '?id=' + selected_list.val();
            $('#edit_news').attr('href',href);
            return true;
        });


        $('#delete_news').click(function(){

            var delete_ele = $('#dg_news tbody input[type=checkbox]:checked');
            var selected_length = delete_ele.size();

            if(0 == selected_length){
                showGritter('info','Alert!','Please select record to delete.');
                return false;
            }else{
                bootbox.confirm("Are you sure to delete selected record(s)?", function(result) {
                    if(result) {

                        var delete_id = [];
                        $.each(delete_ele, function(i, ele){
                            delete_id.push($(ele).val());
                        });

                        $.ajax({
                            url: 'control/news.php?act=delete',
                            type:'post',
                            dataType:'json',
                            data:{ id : delete_id, },
                            success: function(resp){
                                dg_news.fnDraw();
                                showGritter('success',resp.title,resp.msg);
                            }
                        });
                    }
                });
            }
        });

    });

    function DeleteRecord(news_id){

        $('#chk_'+news_id).prop('checked', true);
        $('#delete_news').click();
    }
</script>

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class='span12'>
                <div class="table-header">
                    News
					<span class="widget-toolbar pull-right">
						<a id='add_news' href="news_add.php" data-placement="top" data-rel="tooltip" data-original-title="Add" class='btn'><i class="icon-plus icon-large white"></i>Add</a>
						<a id='edit_news' href="news_edit.php" data-placement="top" data-rel="tooltip" data-original-title="Edit" class="btn"><i class="icon-pencil icon-large white"></i>Edit</a>
						<a id='delete_news' href="javascript:void(0);" data-placement="top" data-rel="tooltip" data-original-title="Delete" class="btn"><i class="icon-trash icon-large white"></i>Delete</a>
					</span>
                </div>
                <table id='dg_news' class="table table-condensed table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="center" width="5%">
                            <label>
                                <input type="checkbox" id='chk_master'/>
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th>News Title</th>
                        <th>Start Date</th>
                        <th>Category name</th>
                        <th>Sub Category name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th class="center" width="5%">
                        </th>
                        <th><input type="text"  placeholder="News Title" name="news_title"></th>
                        <th></th>
                        <th><input type="text"  placeholder="Category name" name="category_name"></th>
                        <th><input type="text"  placeholder="Sub Category name" name="sub_category_name"></th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
include_once 'footer.php';
?>
