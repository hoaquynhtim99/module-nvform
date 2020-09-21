<!-- BEGIN: main -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet">
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<div class="row">
    <div class="col-xs-24 col-sm-24 col-md-16">
        <form class="form-inline form-search-report" action="{FORM_ACTION}" method="get">
            <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
            <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
            <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
            <input type="hidden" name="fid" value="{FID}">
            <!--
            <div class="form-group">
                <select class="form-control" name="field">
                    <option value="">{LANG.report_all_field}</option>
                    <!-- BEGIN: field -->
                    <option value="{FIELD.title}"{FIELD.selected}>{FIELD.title}</option>
                    <!-- END: field -->
                </select>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="q" value="{SEARCH.q}" placeholder="{GLANG.search}">
            </div>
            -->
            <div class="form-group">
                <label>{LANG.form_start_time}:</label>
                <input type="text" class="form-control w100 dtpicket" name="f" value="{SEARCH.from}" placeholder="dd/mm/yyyy" autocomplete="off">
            </div>
            <div class="form-group">
                <label>{LANG.form_end_time}:</label>
                <input type="text" class="form-control w100 dtpicket" name="t" value="{SEARCH.to}" placeholder="dd/mm/yyyy" autocomplete="off">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">{GLANG.search}</button>
            </div>
        </form>
    </div>
    <div class="col-xs-24 col-sm-24 col-md-8">
        <div class="form-group text-right">
            <a href="{URL_ANALYTICS}" target="_blank" class="btn btn-danger"><i class="fa fa-area-chart"></i> {LANG.report_chart}</a>
            <div class="btn-group">
                <button id="open_modal" data-fid="{FID}" data-sql="{SQL}" class="btn btn-primary" data-lang_ex="{LANG.report_ex}">
                    <em class="fa fa-floppy-o">&nbsp;</em>{LANG.report_ex}
                </button>
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span> <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu pull-right">
                    <li><a href="javascript:void(0)" data-fid="{FID}" id="ex_onine"><em class="fa fa-file-excel-o">&nbsp;&nbsp;</em>{LANG.report_ex_online}</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="table-responsive" style="width: 100%; height: 100%; overflow: scroll">
    <table class="table table-striped table-bordered table-hover" id="table_report">
        <colgroup>
            <col width="60" />
            <col width="20" />
            <col class="w150" />
            <col width="120" />
            <col width="120" />
        </colgroup>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>STT</th>
                <th>{LANG.report_who_answer}</th>
                <th>{LANG.report_answer_time}</th>
                <th>{LANG.report_answer_edit_time}</th>
                <!-- BEGIN: thead -->
                <th><span href="#" style="cursor: pointer" rel='tooltip' data-html="true" data-toggle="tooltip" data-placement="bottom" title="<p class='text-justify'>{QUESTION.title}</p>">{QUESTION.title_cut}</span></th>
                <!-- END: thead -->
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: tr -->
            <tr>
                <td class="danger text-center">
                    <a href="javascript:void(0);" rel='tooltip' data-html="true" data-toggle="tooltip" data-placement="top" title="{GLANG.delete}" onclick="nv_del_answer({ANSWER.id});"><i class="fa fa-trash-o fa-lg"></i></a>
                    <a href="#" rel='tooltip' data-html="true" data-toggle="tooltip" data-placement="top" title="{LANG.report_viewpage}" onclick="nv_open_windown('{ANSWER.answer_view_url}');"><i class="fa fa-search fa-lg"></i></a>
                    <!-- BEGIN: export -->
                    <a href="{ANSWER.answer_export}" data-toggle="tooltip" data-placement="top" title="{LANG.report_export}"><i class="fa fa-download" aria-hidden="true"></i></a>
                    <!-- END: export -->
                </td>
                <td class="success text-center">{ANSWER.no}</td>
                <td class="success">{ANSWER.username}</td>
                <td class="success">{ANSWER.answer_time}</td>
                <td class="success">{ANSWER.answer_edit_time}</td>
                <!-- BEGIN: td -->
                <td>
                    <!-- BEGIN: table --> <a href="#" title="" onclick="modalShow('Chức năng đang hoàn thiện', 'Chức năng đang hoàn thiện'); return false;">{LANG.report_viewtable}</a> <!-- END: table --> <!-- BEGIN: files --> <a href="{FILES}" title="">{LANG.question_options_file_dowload}</a> <!-- END: files --> <!-- BEGIN: other --> {ANSWER} <!-- END: other -->
                </td>
                <!-- END: td -->
            </tr>
            <!-- END: tr -->
        </tbody>
    </table>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.dtpicket').datepicker({
        showOn : "both",
        dateFormat : "dd-mm-yy",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        buttonImage : null,
        buttonText : null,
        buttonImageOnly : false
    });
});
</script>
<!-- END: main -->
