
function load()
{

    $('#btnNotificaciones').html('<i class="fa fa-bell"></i> Usuarios (' + NOTIFICACIONES.length + ')');
    eventos();
    loadSubtipos();
    //$('#divRequisitosEspeciales').hide();
    $('#btnArchivoN').hide();
    $("#opIntervalo").hide();
    $("#tIntervalo").hide();
}
var QREXIST;

function checkFile(qr){
    var URL = base_url + "compras/checkFile";
    //QREXIST=0;
    $.ajax({
        type: "POST",
        url: URL,
        data: { qr : qr },
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                
                if (rs=='1') {
                    QREXIST=1;
                    console.log('existe '+QREXIST);
                    
                }else if(rs=='0'){
                    QREXIST=0;
                    console.log('no existe '+QREXIST);
                    
                }
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
return QREXIST;
}
function eventos(){
    $("input[name='rbTipo']").on('ifChanged', function(){
        loadSubtipos();
    });

    $("input[name='rbDestino']").on('ifChanged', function(){
        if($("#rbVenta").is(':checked'))
        {
            $('#divCliente').fadeIn();
        }
        else
        {
            $('#divCliente').fadeOut();
        }
        borrarNotificaciones();
    });

    $("#opSubtipo").on('change', function(){
        subtipo();
    });

    $('#mdlClientes').on('keypress', function( e ) {
        if( e.keyCode === 13 )
        {
            e.preventDefault();
            buscarClientes();
        }
    });
}

function editarQR(info, atributos){
    var URL = base_url + "compras/ajax_editarQR";
    $.ajax({
        type: "POST",
        url: URL,
        data: { info: info, atributos: atributos },
        success: function(result) {
            if(result)
            {
                if(_("userfile").value != "" && $('#lblArchivo').html() != "")
                {
                    subirArchivo(idqr);
                }
                else if($('#lblArchivo').html() == "")
                {
                    borrarArchivo(idqr);
                }
                window.location.href = base_url + 'compras/requisiciones';
            } 
            else 
            {
                new PNotify({ title: 'ERROR', text: 'Error al generar QR', type: 'error', styling: 'bootstrap3' });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error al generar QR', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function registrarQR(info, atributos, qr){
    
checkFile(qr);
    var URL = base_url + "compras/ajax_generarQR";

    $.ajax({
        type: "POST",
        url: URL,
        data: { info: info, atributos: atributos },
        success: function(result) {
            
            if(result){
                alert(result+" - "+1)

            if(_("userfileN").value != "" && $('#lblArchivo').html() != "")
                {

                    subirArchivoN(result);
                }else if (QREXIST == '1' && $('#lblArchivo').html() != "") {
                    
                    subirArchivoClon(qr);
                }else{
                    
                    if(_("userfile").value != "" && $('#lblArchivo').html() != "")
                {

                    subirArchivo(result);
                } 
                }
                
                new PNotify({ title: 'Requisición de Cotización', text: 'Se ha generado QR con éxito', type: 'success', styling: 'bootstrap3' });
                limpiar();
                window.location.href = base_url + 'compras/ver_qr/'+result;
            } 
            else 
            {
                new PNotify({ title: 'ERROR', text: 'Error al generar QR', type: 'error', styling: 'bootstrap3' });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error al generar QR', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
} 

function subirArchivoClon(id_qr){
    var URL = base_url + 'compras/ajax_subirArchivoQRClon';

    var formdata = new FormData();
   // formdata.append("file", file);
    formdata.append("qr", id_qr);

    var ajax = new XMLHttpRequest();
    //ajax.addEventListener("load", completeHandler2, false);
    ajax.open("POST", URL);
    ajax.send(formdata);
  }

  function subirArchivo(id_qr){
    
    var file = _("userfile").files[0];
    var URL = base_url + 'compras/ajax_subirArchivoQR';
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("qr", id_qr);

    var ajax = new XMLHttpRequest();
    //ajax.addEventListener("load", completeHandler2, false);
    ajax.open("POST", URL);
    ajax.send(formdata);
  }
  function subirArchivoN(id_qr){
    
    var file = _("userfileN").files[0];
    var URL = base_url + 'compras/ajax_subirArchivoQR';
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("qr", id_qr);

    var ajax = new XMLHttpRequest();
    //ajax.addEventListener("load", completeHandler2, false);
    ajax.open("POST", URL);
    ajax.send(formdata);
  }



  function borrarArchivo(id_qr){

    var URL = base_url + 'compras/ajax_borrarArchivoQR';

    var formdata = new FormData();
    formdata.append("qr", id_qr);

    var ajax = new XMLHttpRequest();
    ajax.open("POST", URL);
    ajax.send(formdata);
  }

function loadSubtipos(){
    
    var t = $("input[name='rbTipo']:checked").val();
    if(t == "PRODUCTO")
    {
        var opt = ''
        +'<option value="EQUIPO">EQUIPO</option>'
        +'<option value="REFACCION">REFACCIÓN</option>'
        +'<option value="ACCESORIO">ACCESORIO</option>'
        +'<option value="NORMA">NORMA</option>'
        +'<option value="MATERIAL">MATERIAL</option>'
        +'<option value="MEMBRESIA">MEMBRESIA</option>';
        $("#opSubtipo").html(opt);
    }
    else{
        var opt = ''
        +'<option value="CALIBRACION">CALIBRACIÓN</option>'
        +'<option value="REVISION">REVISIÓN</option>'
        +'<option value="REPARACION">REPARACIÓN</option>'
        +'<option value="CURSO">CURSO</option>'
        +'<option value="MANTENIMIENTO">MANTENIMIENTO</option>'
        +'<option value="OTROS">OTROS</option>';
        $("#opSubtipo").html(opt);
    }
    subtipo();
}

function subtipo(){
    $('#tabla').find('table').remove();

    var subt = $("#opSubtipo").val();
    switch (subt) {
        case "EQUIPO":
            //$('#divRequisitosEspeciales').hide();
            agregarEquipo();
            break;

        case "REFACCION":
            agregarRefaccion();
            break;

        case "ACCESORIO":
            //$('#divRequisitosEspeciales').hide();
            agregarRefaccion();
            break;
        
        case "NORMA":
            //$('#divRequisitosEspeciales').hide();
            agregarNorma();
            break;
        
        case "MATERIAL":
            //$('#divRequisitosEspeciales').hide();
            agregarMaterial();
            break;

        case "MEMBRESIA":
            //$('#divRequisitosEspeciales').hide();
            agregarNorma();
            break;

        case "CALIBRACION":
           // $('#divRequisitosEspeciales').show();
            agregarCalibracion();
            break;

        case "REVISION":
           // $('#divRequisitosEspeciales').hide();
            servicioRev();
            break;
        
        case "REPARACION":
            servicioRev();
            break;

        case "CURSO":
            //$('#divRequisitosEspeciales').hide();
            agregarNorma();
            break;

        case "MANTENIMIENTO":
            //$('#divRequisitosEspeciales').hide();
            agregarNorma();
            break;
        case "OTROS":
        //$('#divRequisitosEspeciales').hide();
            servicioCurso();
            break; 

            
        default:
            break;
    }

}

function agregarEquipo(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>UM</th>'
        +            '<th>Marca</th>'
        +            '<th>Modelo</th>'
        +            '<th>Descripción</th>'
        +            '<th>Calibración</th>'
        +            '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td style="width:10%;"><select data-campo="unidad" style="width:80%;" class="select2_single form-control">'
        +                   '<option selected value=""></option>'
        +                   '<option value="H87">Pieza</option>'
        +                   '<option value="E48">Servicio</option>'
        +                   '<option value="KGM">Kilogramo</option>'
        +                   '<option value="GRM">Gramo</option>'
        +                   '<option value="MTR">Metro</option>'
        +                   '<option value="INH">Pulgada</option>'
        +                   '<option value="FOT">Pie</option>'
        +                   '<option value="YRD">Yarda</option>'
        +                   '<option value="SMI">Milla</option>'
        +                   '<option value="MTK">Metro Cuadrado</option>'
        +                   '<option value="CMK">Centímetro cuadrado</option>'
        +                   '<option value="MTQ">Metro cúbico</option>'
        +                   '<option value="LTR">Litro</option>'
        +                   '<option value="GLL">Galón</option>'
        +                   '<option value="HUR">Hora</option>'
        +                   '<option value="DAY">Dia</option>'
        +                   '<option value="LO">Lote</option>'
        +               '</select></td>'
        +               '<td style="width:12%"><input id="txtMarca" data-campo="marca" style="width:100%" type="text" value=""/></td>'
        +               '<td style="width:12%"><input id="txtModelo" data-campo="modelo" style="width:100%" type="text" value=""/></td>'
        +               '<td><input id="txtDescripcion" maxlength="200" data-campo="descripcion" style="width:100%" type="text" value=""/></td>'
        +               '<td><select onclick="Cal();" id="cal" data-campo="calibracion" style="width:100%; height: 20%;" class="select2_single form-control">'
        +                   '<option selected value="N/A">N/A</option>'
        +                   '<option value="ACREDITADA">ACREDITADA</option>'
        +                   '<option value="NO ACREDITADA">NO ACREDITADA</option>'
        //+                   '<option value="TRAZABLE">TRAZABLE</option>'
        +               '</select></td>'
        +           '</tr>';
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}

function Cal(){
    tipo ='<label class="control-label col-md-3 col-sm-3 col-xs-12">Tipo Calibración:'
        +'<select id="tipoCalibracion" data-campo="tipo" style="width:80%; height: 20%;" class="select2_single form-control">'
        +                   '<option selected value="N/A">N/A</option>'
        +                   '<option value="CALIBRACION MASMETROLOGIA">CALIBRACION MASMETROLOGIA</option>'
        +                   '<option value="CALIBRACION EXTERNA">CALIBRACION EXTERNA</option>'
        +               '</select></label>';
       
    var t = $("input[name='rbTipo']:checked").val();
    if(t=='PRODUCTO'){
    if($('#cal').val() != 'N/A'){
        $("#tipo").html(tipo);
        $("#opIntervalo").show();
        $("#tIntervalo").show();
    }else{
        $("#tipo").empty();
        $("#opIntervalo").hide();
        $("#tIntervalo").hide();
    }
    }else{
        if($('#cal').val() != 'N/A'){
        $("#opIntervalo").show();
        $("#tIntervalo").show();
    }else{
        $("#opIntervalo").hide();
        $("#tIntervalo").hide();
    }
    }
}

function agregarRefaccion(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>UM</th>'
        +            '<th>Marca</th>'
        +            '<th>Modelo</th>'
        +            '<th>Descripción</th>'
        +            '<th>Serie</th>'
        +            '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td style="width:10%;"><select data-campo="unidad" style="width:80%;" class="select2_single form-control">'
        +                   '<option selected value=""></option>'
        +                   '<option value="H87">Pieza</option>'
        +                   '<option value="E48">Servicio</option>'
        +                   '<option value="KGM">Kilogramo</option>'
        +                   '<option value="GRM">Gramo</option>'
        +                   '<option value="MTR">Metro</option>'
        +                   '<option value="INH">Pulgada</option>'
        +                   '<option value="FOT">Pie</option>'
        +                   '<option value="YRD">Yarda</option>'
        +                   '<option value="SMI">Milla</option>'
        +                   '<option value="MTK">Metro Cuadrado</option>'
        +                   '<option value="CMK">Centímetro cuadrado</option>'
        +                   '<option value="MTQ">Metro cúbico</option>'
        +                   '<option value="LTR">Litro</option>'
        +                   '<option value="GLL">Galón</option>'
        +                   '<option value="HUR">Hora</option>'
        +                   '<option value="DAY">Dia</option>'
        +                   '<option value="LO">Lote</option>'
        +               '</select></td>'
        +               '<td style="width:12%"><input id="txtMarca" data-campo="marca" style="width:100%" type="text" value=""/></td>'
        +               '<td style="width:12%"><input id="txtModelo" data-campo="modelo" style="width:100%" type="text" value=""/></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:100%" type="text" value=""/></td>'
        +               '<td><input data-campo="serie" style="width:100%" type="text" value=""/></td>'
        +           '</tr>';
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}

function agregarNorma(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>UM</th>'
        +            '<th>Descripción</th>'
        +        '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td style="width:10%;"><select data-campo="unidad" style="width:80%;" class="select2_single form-control">'
        +                   '<option selected value=""></option>'
        +                   '<option value="H87">Pieza</option>'
        +                   '<option value="E48">Servicio</option>'
        +                   '<option value="KGM">Kilogramo</option>'
        +                   '<option value="GRM">Gramo</option>'
        +                   '<option value="MTR">Metro</option>'
        +                   '<option value="INH">Pulgada</option>'
        +                   '<option value="FOT">Pie</option>'
        +                   '<option value="YRD">Yarda</option>'
        +                   '<option value="SMI">Milla</option>'
        +                   '<option value="MTK">Metro Cuadrado</option>'
        +                   '<option value="CMK">Centímetro cuadrado</option>'
        +                   '<option value="MTQ">Metro cúbico</option>'
        +                   '<option value="LTR">Litro</option>'
        +                   '<option value="GLL">Galón</option>'
        +                   '<option value="HUR">Hora</option>'
        +                   '<option value="DAY">Dia</option>'
        +                   '<option value="LO">Lote</option>'
        +               '</select></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:50%" type="text" value=""/></td>'
        +           '</tr>'
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}

function agregarMaterial(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>UM</th>'
        +            '<th>Descripción</th>'
        +        '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td style="width:10%;"><select data-campo="unidad" style="width:80%;" class="select2_single form-control">'
        +                   '<option selected value=""></option>'
        +                   '<option value="H87">Pieza</option>'
        +                   '<option value="E48">Servicio</option>'
        +                   '<option value="KGM">Kilogramo</option>'
        +                   '<option value="GRM">Gramo</option>'
        +                   '<option value="MTR">Metro</option>'
        +                   '<option value="INH">Pulgada</option>'
        +                   '<option value="FOT">Pie</option>'
        +                   '<option value="YRD">Yarda</option>'
        +                   '<option value="SMI">Milla</option>'
        +                   '<option value="MTK">Metro Cuadrado</option>'
        +                   '<option value="CMK">Centímetro cuadrado</option>'
        +                   '<option value="MTQ">Metro cúbico</option>'
        +                   '<option value="LTR">Litro</option>'
        +                   '<option value="GLL">Galón</option>'
        +                   '<option value="HUR">Hora</option>'
        +                   '<option value="DAY">Dia</option>'
        +                   '<option value="LO">Lote</option>'
        +               '</select></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:50%" type="text" value=""/></td>'
        +           '</tr>';
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}

function agregarCalibracion(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        //+            '<th>UM</th>'
        +            '<th>Marca</th>'
        +            '<th>Modelo</th>'
        +            '<th>Descripción</th>'
        +            '<th>Serie</th>'
        +            '<th>Calibración</th>'
        +            '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
       /* +               '<td style="width:10%;"><select data-campo="unidad" style="width:80%;" class="select2_single form-control">'
        +                   '<option selected value=""></option>'
        +                   '<option value="H87">Pieza</option>'
        +                   '<option value="E48">Servicio</option>'
        +                   '<option value="KGM">Kilogramo</option>'
        +                   '<option value="GRM">Gramo</option>'
        +                   '<option value="MTR">Metro</option>'
        +                   '<option value="INH">Pulgada</option>'
        +                   '<option value="FOT">Pie</option>'
        +                   '<option value="YRD">Yarda</option>'
        +                   '<option value="SMI">Milla</option>'
        +                   '<option value="MTK">Metro Cuadrado</option>'
        +                   '<option value="CMK">Centímetro cuadrado</option>'
        +                   '<option value="MTQ">Metro cúbico</option>'
        +                   '<option value="LTR">Litro</option>'
        +                   '<option value="GLL">Galón</option>'
        +                   '<option value="HUR">Hora</option>'
        +                   '<option value="DAY">Dia</option>'
        +                   '<option value="LO">Lote</option>'
        +               '</select></td>'*/
        +               '<td style="width:12%"><input id="txtMarca" data-campo="marca" style="width:100%" type="text" value=""/></td>'
        +               '<td style="width:12%"><input id="txtModelo" data-campo="modelo" style="width:100%" type="text" value=""/></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:100%" type="text" value=""/></td>'
        +               '<td><input data-campo="serie" style="width:100%" type="text" value=""/></td>'
        +               '<td><select onclick="Cal();" data-campo="calibracion" id="cal" "style="width:100%; height: 20%;" class="select2_single form-control">'
        +                   '<option selected value="N/A">N/A</option>'
        +                   '<option value="ACREDITADA" >ACREDITADA</option>'
        +                   '<option value="NO ACREDITADA">NO ACREDITADA</option>'
        //+                   '<option value="TRAZABLE">TRAZABLE</option>'
        +               '</select></td>'
        +           '</tr>';
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}
function servicioRev(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>Marca</th>'
        +            '<th>Modelo</th>'
        +            '<th>Descripción</th>'
        +            '<th>Serie</th>'
        +            '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td style="width:12%"><input id="txtMarca" data-campo="marca" style="width:100%" type="text" value=""/></td>'
        +               '<td style="width:12%"><input id="txtModelo" data-campo="modelo" style="width:100%" type="text" value=""/></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:100%" type="text" value=""/></td>'
        +               '<td><input data-campo="serie" style="width:100%" type="text" value=""/></td>'
        +           '</tr>';
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}
function servicioCurso(){
    var ren = ''
        + '<table class="table table-striped">'
        +    '<thead>'
        +        '<tr>'
        +            '<th>Qty</th>'
        +            '<th>Descripción</th>'
        +        '</tr>'
        +        '</thead>'
        +        '<tbody>'
        +           '<tr>'
        +               '<td style="width:7%;"><input data-campo="cantidad" style="width:80%; text-align: center;" type="number" value="1"/></td>'
        +               '<td><input id="txtDescripcion" data-campo="descripcion" maxlength="200" style="width:50%" type="text" value=""/></td>'
        +           '</tr>'
        +        '</tbody>'
        +   '</table>';
        
    $('#tabla').append(ren);

    $('input:checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-green'
    });
}


function limpiar()
{   
    subtipo();
    $('#txtComentarios').val("");
    eliminarArchivo();

    NOTIFICACIONES = [];
    $('#btnNotificaciones').html('<i class="fa fa-bell"></i> Usuarios');
}

function eliminarRenglon(btn){
    var rs = confirm('¿Desea eliminar concepto?');
    if(rs)
    {
        $(btn).parent().parent().remove();
    }
}

function buscarUsuarios(){
    var URL = base_url + "compras/ajax_getUsuariosQRNotificaciones";
    $('#tblUsuarios tbody tr').remove();

    var privilegio = $("input[name='rbDestino']:checked").val() == "VENTA" ? 'crear_qr_venta' : 'crear_qr_interno';
    
    
    $.ajax({
        type: "POST",
        url: URL,
        data: { privilegio : privilegio },
        success: function(result) {
            if(result)
            {
                var tab = $('#tblUsuarios tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem)
                {
                    var ren = tab.insertRow(tab.rows.length);
                    ren.dataset.id = elem.id;

                    ren.insertCell().innerHTML = '<input type="checkbox" class="flat" ' + (NOTIFICACIONES.includes(parseInt(elem.id)) ? 'checked' : '') +'>';
                    ren.insertCell().innerHTML = elem.Nombre;
                    ren.insertCell().innerHTML = elem.Puesto;
                });

                $('#tblUsuarios input.flat').iCheck({
                    checkboxClass: 'icheckbox_flat-green',
                    radioClass: 'iradio_flat-green'
                });

                $('#mdlUsuarios').modal();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function guardarUsuariosNotificaciones(){
    NOTIFICACIONES = [];
    var tbl = $('#tblUsuarios tbody')[0];

    $.each(tbl.rows, function(i, row){
        var ipt = $(row).find('input[type="checkbox"]');

        if($(ipt).is(':checked'))
        {
            NOTIFICACIONES.push(parseInt(row.dataset.id));
        }
    });

    $('#btnNotificaciones').html('<i class="fa fa-bell"></i> Usuarios (' + NOTIFICACIONES.length + ')');
    $('#mdlUsuarios').modal('hide');

}

function borrarNotificaciones(){
    if(NOTIFICACIONES.length > 0)
    {
        alert('Los usuarios seleccionados para notificaciones seran eliminados por cambio de DESTINO');
        NOTIFICACIONES = [];
        $('#btnNotificaciones').html('<i class="fa fa-bell"></i> Usuarios');
    }
}




function _(el){
    return document.getElementById(el);
}

function uploadFile(){
    //alert(file.name+" | "+file.size+" | "+file.type);
    var file = _("userfile").files[0];
    
    /*$("#lblArchivo").html("<u>" + file.name + "</u>");

    $("#btnArchivo").fadeOut('slow', function(){
        $("#btnEliminarArchivo").fadeIn();
    });*/
    if (file.type === 'application/pdf' ) {
        $("#lblArchivo").html("<u>" + file.name + "</u>");

    $("#btnArchivo").fadeOut('slow', function(){
        $("#btnEliminarArchivo").fadeIn();
    });
    }else{
        alert("Solo se permiten archivos PDF");
        eliminarArchivo();
    }
  }
function uploadFileN(){
    //alert(file.name+" | "+file.size+" | "+file.type);
    var file = _("userfileN").files[0];
    
    $("#lblArchivo").html("<u>" + file.name + "</u>");
    $("#btnArchivoN").fadeOut('slow', function(){
        $("#btnEliminarArchivo").fadeIn()
;    });
  }
function eliminarArchivo()
{
    $("#lblArchivo").html("");
    $("#btnEliminarArchivo").fadeOut('slow', function(){
        $("#btnArchivoN").fadeIn();
    });
    _("userfile").value="";
    $('#btnArchivo').hide();
    
}
