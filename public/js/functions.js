function likePlus(element) {
    const $url = element.href;
    if (!$url){
        return;
    }
    $.ajax({
        url: $url,
        method: 'POST',
        beforeSend: function () {
            $(element).attr('disabled','disabled');
            $(element).removeAttr('href');
        }
    });
}