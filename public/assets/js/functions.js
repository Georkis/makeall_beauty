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