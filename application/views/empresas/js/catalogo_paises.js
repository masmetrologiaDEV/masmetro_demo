var PAIS;
function load()
{
    buscar();
}
function buscar(){
    var URL = base_url + "empresas/ajax_paises";
    $('#tabla tbody tr').remove();
    
    var texto = $("#txtBuscar").val();
    var activo = $('#activo').is(':checked') ? "1" : "0";
    var check;

    $.ajax({
        type: "POST",
        url: URL,
        data: { texto : texto, activo : activo },
        success: function(result) {
            //alert(result);
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    if(elem.activo==1){
                        check ='<input type="checkbox" class="flat cbTipo" checked disabled />';
                    }
                    else{
                     check ='<input type="checkbox" class="flat cbTipo"  disabled/>';   
                    }
                    ren.insertCell(0).innerHTML = elem.paisnombre;
                    ren.insertCell(1).innerHTML = check;
                    if (elem.activo == '1') {
                    ren.insertCell(2).innerHTML = '<button type="button"class="btn btn-danger btn-xs" onclick="desactivar('+elem.id+')"><i class="fa fa-trash"></i> Desctivar </button><button type="button"class="btn btn-success btn-xs" onclick="estados('+elem.id+')"><i class="fa fa-eye"></i> Ciudad por defecto </button>';
                    }
                    else{
                    ren.insertCell(2).innerHTML = '<button type="button"class="btn btn-warning btn-xs" onclick="activar('+elem.id+')"><i class="fa fa-pencil" ></i> Activar </button><button type="button"class="btn btn-success btn-xs" onclick="estados('+elem.id+')"><i class="fa fa-eye"></i> Ciudad por defecto </button>';    
                    }
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
function activar(btn){
     var URL = base_url + "empresas/ajax_Activarpaises";
     //alert(btn);
        $.ajax({
        type: "POST",
        url: URL,
        data: { id : btn },
        success: function(result) {
            //alert(result);
            if(result)
            {
                buscar();
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

function desactivar(id){
     var URL = base_url + "empresas/ajax_Desactivarpaises";
     //alert(btn);
        $.ajax({
        type: "POST",
        url: URL,
        data: { id : id },
        success: function(result) {
            //alert(result);
            if(result)
            {
                buscar();
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

function estados(id){
    PAIS = id;
    setEstado(id);
    $("#estados").empty();
    var URL = base_url + "empresas/ajax_Estados";
    const select = $("#estados");
    $.ajax({
        type: "POST",
        url: URL,
        data: { id : id },
        success: function (response) {
            if(response){
            var rs = JSON.parse(response);
              $.each(rs, function(i, elem){                    
                $("#estados").append("<option value='"+elem.id+"'>"+elem.estadonombre+"</option>");
                });
                $('#mdlEstados').modal();
            }
        }
    }); 
}
function asignar(){
    var id = document.getElementById("estados").value;
    var URL = base_url + "empresas/ajax_AsignarEstados";
    $.ajax({
        type: "POST",
        url: URL,
        data: { id : id, idp : PAIS },
        success: function (response) {

            //alert(response);
            if(response){
                var rs = JSON.parse(response);

                new PNotify({ title: 'Se asigno: ', text: rs.estadonombre, type: 'info', styling: 'bootstrap3' });
            }
        }
    });
}
function setEstado(id) {
   $("#estadoAsignado").empty();
    var URL = base_url + "empresas/ajax_setEstados";
    $.ajax({
        type: "POST",
        url: URL,
        data: { id : id },
        success: function (response) {
            if(response){
            var rs = JSON.parse(response);
            var span = document.getElementById("estadoAsignado");
                span.textContent = 'Estado Asignado: '+rs.estadonombre;
            }
        }
    });
}