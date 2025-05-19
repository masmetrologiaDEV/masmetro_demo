
function load(){
 
    buscar();
}
function buscar(){
    $('#lblCount').text("");
    var URL = base_url + "tickets/ajax_getCamaras";
    $('#camaras tbody tr').remove();
    

    $.ajax({
        type: "POST",
        url: URL,
       // data: { estatus: estatus, texto : texto, parametro : parametro, fecha1 : fecha1, fecha2 : fecha2},
        success: function(result) {
            if(result)
            {
                var tab = $('#camaras tbody')[0];
                var rs = JSON.parse(result);
              //  $('#lblCount').text(rs.length + (rs.length == 1 ? " Ticket" : " Tickets's"));
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.dataset.id = elem.id;
                    ren.insertCell().innerHTML = elem.id;
                    ren.insertCell().innerHTML = elem.grabadora;
                    ren.insertCell().innerHTML = elem.ubicacion;
                    ren.insertCell().innerHTML = elem.marca;
                    ren.insertCell().innerHTML = elem.modelo;
                    ren.insertCell().innerHTML = elem.serie;    
                    ren.insertCell().innerHTML = elem.codigo;    
                    
                });
            }
            else
            {
                new PNotify({ title: '¡Nada por aquí!', text: 'No se encontraron resultados', type: 'info', styling: 'bootstrap3' });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function agregar() {
      $('#mdlAgregar').modal();
    }

    function agregarCamara(){
    if(validar()){
        var URL = base_url + "tickets/registrar_camara";
        var data = {};
        data.grabadora = $('#grabadora').val().trim();
        data.ubicacion = $('#ubicacion').val().trim();
        data.marca = $('#marca').val().trim();
        data.modelo = $('#modelo').val().trim();
        data.serie = $('#serie').val().trim();
        data.codigo = $('#codigo').val().trim();

        $.ajax({
            type: "POST",
            url: URL,
            data: { data : JSON.stringify(data) },
            success: function (response) {
                $('#mdlAgregar').modal('hide');
                buscar();
            }
        });
    }
}

function validar(){
    if(!$('#grabadora').val()){
        alert('Ingrese Grabadora');
        return false;
    }
    if(!$('#ubicacion').val()){
        alert('Ingrese Ubicacion de Camara');
        return false;
    }

    return true;
}
