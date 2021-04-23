function likePlus(element) {
    const $url = element.href;
    const $dataUrl = $(element).attr('data-url');

    if ($url){
        $.ajax({
            url: $url,
            method: 'POST',
            beforeSend: function () {
                $(element).removeAttr('href');

            }
        });
    }

    if ($dataUrl){
        $.ajax({
            url: $url,
            method: 'POST',
            beforeSend: function () {
                $(element).attr('disabled','disabled');
            }
        });
    }
}