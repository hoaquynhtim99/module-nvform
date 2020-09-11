<!-- BEGIN: main -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover table-middle">
        <thead>
            <tr class="center">
                <th width="100">{LANG.order}</th>
                <th>{LANG.form_title}</th>
                <th class="w200">{LANG.link}</th>
                <th class="w200">{LANG.embed}</th>
                <th width="150" class="text-center">{LANG.status}</th>
                <th width="140">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: row -->
            <tr>
                <td class="center">
                    <select id="change_weight_{ROW.id}" onchange="nv_chang_weight('{ROW.id}', 0, 'form');" class="form-control">
                        <!-- BEGIN: weight -->
                        <option value="{WEIGHT.w}"{WEIGHT.selected}>{WEIGHT.w}</option>
                        <!-- END: weight -->
                    </select>
                </td>
                <td>
                    <!-- BEGIN: link --> <a href="{ROW.url_view}" title="{ROW.title}" target="_blank">{ROW.title}</a> <!-- END: link --> <!-- BEGIN: label --> {ROW.title} <!-- END: label -->
                </td>
                <td>
                    <div class="input-group">
                        <input type="text" value="{ROW.url_copy}" id="url_copy_{ROW.id}" class="form-control" readonly="readonly" onfocus="this.select();" onmouseup="return false;">
                        <div class="input-group-btn">
                            <button class="btn btn-default copy" data-clipboard-target="#url_copy_{ROW.id}" data-toggle="tooltip" data-placement="right" type="button" title="{LANG.copy}">
                                <i class="fa fa-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td><input class="form-control" readonly="readonly" onfocus="this.select();" onmouseup="return false;" value="{ROW.embed_copy}"></td>
                <td class="center">
                    <select id="change_status_{ROW.id}" onchange="nv_chang_status('{ROW.id}', 'form');" class="form-control">
                        <!-- BEGIN: status -->
                        <option value="{STATUS.key}"{STATUS.selected}>{STATUS.val}</option>
                        <!-- END: status -->
                    </select>
                </td>
                <td class="center"><a href="{ROW.qlist}" data-toggle="tooltip" data-placement="top" title="" data-original-title="{LANG.question_list}"><em class="fa fa-tasks fa-lg">&nbsp;</em></a> &nbsp; <a href="{ROW.url_report}" data-toggle="tooltip" data-placement="top" title="" data-original-title="{LANG.form_report}"><em class="fa fa-bar-chart-o fa-lg">&nbsp;</em></a> &nbsp; <a href="{ROW.url_edit}" data-toggle="tooltip" data-placement="top" title="" data-original-title="{GLANG.edit}"><em class="fa fa-edit fa-lg">&nbsp;</em></a> &nbsp; <a href="javascript:void(0);" onclick="nv_del_form({ROW.id});" data-toggle="tooltip" data-placement="top" title="" data-original-title="{GLANG.delete}"><em class="fa fa-trash-o fa-lg">&nbsp;</em></a></td>
            </tr>
            <!-- END: row -->
        </tbody>
    </table>
</div>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/clipboard/clipboard.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('.copy').tooltip({
        trigger: 'click',
        placement: 'right'
    });

    function setTooltip(btn, message) {
        $(btn).tooltip('hide')
        .attr('data-original-title', message)
        .tooltip('show');
    }

    function hideTooltip(btn) {
        setTimeout(function() {
            $(btn).tooltip('hide');
        }, 3000);
    }

    var clipboard = new ClipboardJS('.copy');

    clipboard.on('success', function(e) {
        setTooltip(e.trigger, '{LANG.copied}');
        hideTooltip(e.trigger);
    });

    clipboard.on('error', function(e) {
        setTooltip(e.trigger, 'Failed!');
        hideTooltip(e.trigger);
    });
});
</script>
<!-- END: main -->
