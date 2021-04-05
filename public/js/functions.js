function likePlus(element) {
    $.ajax({
        url: element.href,
        method: 'POST',
        beforeSend: function () {
            $(element).attr('disabled','disabled');
        }
    });
}