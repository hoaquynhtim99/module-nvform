/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Tue, 08 Apr 2014 15:13:43 GMT
 */

$(document).ready(function() {
    $('[data-click="nvform-picked-file"]').on('change', function() {
        var ipt = $(this);
        var iptFile = document.getElementById(ipt.attr('id'));
        var fileNames = [];
        if (iptFile.files && iptFile.files.length > 0) {
            if (iptFile.files.length > ipt.data('num')) {
                alert(ipt.data('errnum'));
                $(ipt.data('iptvalue')).val('');
                iptFile.value = '';
                return;
            }

            for (var i = 0; i < iptFile.files.length; i++) {
                if (iptFile.files[i].size > ipt.data('max')) {
                    alert(ipt.data('errsize'));
                    $(ipt.data('iptvalue')).val('');
                    iptFile.value = '';
                    return;
                }
                fileNames.push(iptFile.files[i].name);
            }
        } else {
            var m = ipt.val().match(/[-_\w]+[.][\w]+$/i);
            if (!!m) {
                fileNames.push(m[0]);
            }
        }
        $(ipt.data('iptvalue')).val(fileNames.join(', '));
    });

    // Chọn ảnh upload
    $('[data-click="nvform-pick-file"]').on('click', function() {
        $($(this).data('target')).trigger('click');
    });
});

var page = 1;
if(window.location.hash) {
    page = window.location.hash.substring(1);
    page = page.match( /^page\-([0-9]+)$/ );
    page = page[1];
}

if( page == 1 ){
    $('#btn-prev').attr( 'disabled', 'disabled' );
    $('#btn-prev').hide();
}
else{
    $('#btn-prev').removeAttr( 'disabled' );
    $('#btn-prev').show();
}

if( page == $('#max_page' ).val() )
{
    $('#btn-next').attr( 'disabled', 'disabled' );
    $('#btn-next').hide();
    $('#btn-submit').css( 'display', 'inline-block' );
}

$('#question .question_row').each( function( index, item ){
    if( $(item).data('page') != page )
    {
        $(item).hide();
    }
});

$('#btn-next').click(function(){
    var next_page = parseInt( $('#page').val() ) + 1;
    $('#question .question_row').each( function( index, item ){
        if( $(item).data('page') != next_page ){
            $(item).hide();
        }
        else{
            $(item).show();
        }
    });

    $('#page').val( next_page );

    window.history.pushState( window.location.href, '', '#page-' + next_page );

    if( next_page != 1 ){
        $('#btn-prev').removeAttr( 'disabled' );
        $('#btn-prev').show();
    }

    if( next_page == $('#max_page' ).val() )
    {
        $('#btn-next').attr( 'disabled', 'disabled' );
        $('#btn-next').hide();
        $('#btn-submit').css( 'display', 'inline-block' );
    }

    return false;
});

$('#btn-prev').click(function(){
    var prev_page = parseInt( $('#page').val() ) - 1;
    prev_page = prev_page < 0 ? 0 : prev_page;
    $('#question .question_row').each( function( index, item ){
        if( $(item).data('page') != prev_page ){
            $(item).hide();
        }
        else{
            $(item).show();
        }
    });

    $('#page').val( prev_page );

    window.history.pushState( window.location.href, '', '#page-' + prev_page );

    if( prev_page == 1 ){
        $('#btn-prev').attr( 'disabled', 'disabled' );
        $('#btn-prev').hide();
    }
    else{
        $('#btn-prev').removeAttr( 'disabled' );
        $('#btn-prev').show();
    }

    if( prev_page < $('#max_page' ).val() )
    {
        $('#btn-next').removeAttr( 'disabled' );
        $('#btn-next').show();
        $('#btn-submit').css( 'display', 'none' );
    }

    return false;
});
