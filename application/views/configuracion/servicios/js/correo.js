function load(){
    buscar();
}
function buscar(){
    var URL = base_url + "configuracion/ajax_getTexto_correo";
    $('#tabla tbody tr').remove();
    
    $.ajax({
        type: "POST",
        url: URL,
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = tab.rows.length;
                    ren.insertCell(1).innerHTML = elem.texto;
                    ren.insertCell(2).innerHTML = elem.fecha;
                    ren.insertCell(3).innerHTML = elem.us;
                    ren.insertCell(4).innerHTML = elem.activo;
                    ren.insertCell(5).innerHTML = "<button type='button' onclick='modalModificar(this)' value='" + elem.id +"' class='btn btn-warning btn-xs'><i class='fa fa-pencil'></i> Modificar</button><button type='button' onclick='eliminar(this)' value='" + elem.id +"' class='btn btn-danger btn-xs'><i class='fa fa-trash'></i> Eliminar</button>";
                });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function modalAgregar(){
    $('#btnEditar').hide();
    $('#btnAgregar').show();
    $('#mdl').modal();

    PREFIJO = "";
    $('#txtTexto').val("");
}
function agregar(){

        var URL = base_url + "configuracion/ajax_setTexto_correo";
        var texto = $('#txtTexto').val().trim();
        var activo = $('#cbActivo').val();

        $.ajax({
            type: "POST",
            url: URL,
            data: { texto : texto, activo : activo },
            success: function(result) {
                if(result)
                {
                    new PNotify({ title: 'Nuevo Texto', text: 'Se ha agregado nuevo Texto', type: 'success', styling: 'bootstrap3' });
                    $('#mdl').modal('hide');
                    buscar();
                }
            },
        });
    
}
function modalModificar(btn){

    var URL = base_url + "configuracion/ajax_getTexto_correo";
    var id = $(btn).val();
    $('#btnAgregar').hide();
    $('#btnEditar').show();
    $('#btnEditar').val(id);

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : id },
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
             
                $('#txtTexto').val(rs.texto);

                $('#mdl').modal();
                $('#btnEditar').show();
            }
          },
      });

}

function modificar(btn){
    
        var URL = base_url + "configuracion/ajax_setTexto_correo";

        var id = $(btn).val();
        var texto = $('#txtTexto').val().trim();
        var activo = $('#cbActivo').val();

        $.ajax({
            type: "POST",
            url: URL,
            data: { id : id, texto : texto, activo : activo },
            success: function(result) {
                if(result)
                {
                    new PNotify({ title: 'Edición de Texto', text: 'Se ha editado texto', type: 'success', styling: 'bootstrap3' });
                    $('#mdl').modal('hide');
                    buscar();
                }
            },
        });
    
}

function eliminar(btn){
    if(confirm('¿Desea eliminar Texto?'))
    {
        var URL = base_url + "configuracion/ajax_deleteTexto";
        var id = $(btn).val();

        $.ajax({
            type: "POST",
            url: URL,
            data: { id : id },
            success: function(result) {
                if(result)
                {
                    new PNotify({ title: 'Eliminar Texto', text: 'Se ha eliminado Texto', type: 'success', styling: 'bootstrap3' });
                    buscar();
                }
            },
        });   
    }
}