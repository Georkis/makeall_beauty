function notifyAlert(tipo, mensaje){
    $.notify({
        icon: 'flaticon-alarm-1',
        title: 'Mensaje',
        message: mensaje,
    },{
        type: tipo,
        placement: {
            from: "top",
            align: "right"
        },
        time: 5000,
    });
}

function ejecutar(element, mensaje, string) {
    let url = element.href;
    $(element).attr('data-target','modal-ejecutar').attr('data-toggle','modal');
    $('#modal-ejecutar').remove();

    $('#basic-datatables').append(
        '<div id="modal-ejecutar" class="modal fade" role="dialog">\n' +
        '    <div class="modal-dialog">\n' +
        '       <div class="modal-content">\n' +
        '           <div class="modal-header">\n' +
        '               <h5 class="modal-title">Mensaje de confirmacion</h5>\n' +
        '               <button class="close" data-dismiss="modal">&times;</button>' +
        '           </div>\n' +
        '           <div class="modal-body">' +
        '               <p class="text-danger">'+mensaje+' '+string+'?</p>' +
        '               <h4 class="text-center d-none" id="cargando-ejecutar"><i class="fa fa-spinner fa-pulse"></i> Cargando...</h4>' +
        '           </div>' +
        '           <div class="modal-footer">' +
        '               <button class="btn btn-dark" data-dismiss="modal">Cerrar</button>' +
        '               <button class="btn btn-secondary" id="btn-ejecutar">Procesar tarea</button>' +
        '           </div>' +
        '       </div>\n' +
        '    </div>\n' +
        '</div>'
    );
    $('#modal-ejecutar').modal('show');

    $('#btn-ejecutar').click(function (event) {
        event.preventDefault();
        $.ajax({
            url: url,
            beforeSend: function () {
                $('#cargando-ejecutar').removeClass('d-none');
                $('#btn-ejecutar').attr('disabled','disabled');
            },
            statusCode: {
                200: function (response) {
                    notifyAlert('success', response);
                    $('#modal-ejecutar').modal('hide');
                    datatable.row($(element).parent().parent()).remove().draw();
                    modificarDOMEjecutar();
                },
                400: function (response) {
                    notifyAlert('danger', response.responseJSON);
                    modificarDOMEjecutar();
                },
                500: function (response) {
                    notifyAlert('danger', response.responseJSON);
                    modificarDOMEjecutar();
                }
            }
        })
    })
}
function modificarDOMEjecutar() {
    $('#cargando-ejecutar').addClass('d-none');
    $('#btn-ejecutar').removeAttr('disabled','disabled');
    $('#modal-ejecutar').modal('hide');
}