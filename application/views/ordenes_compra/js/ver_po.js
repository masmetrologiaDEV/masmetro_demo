var ID = 0;
var PO;
var PRS = [];
var provCorreo;

function load() {
    eventos();
    cargarDatos();
    cargarAcciones();
    iniciar_daterangepicker();

}

function eventos() {
    $('#txtTags').tagsInput({
        width: 'auto',
        defaultText: 'correos',
    });

    $('#txtNoConfirmacion').on('keypress', function(){
        $('#btnGuardarNoConfirmacion').fadeIn();
    });
}

function cargarDatos(revision) {
    ID = id;
    var P=[];
    var URL = base_url + "ordenes_compra/ajax_getPO";
    //$('#tablaPRS p').remove();

   // alert(revision);

    $.ajax({
        type: "POST",
        url: URL,
        data: {
            id: ID, revision : revision
        },
        success: function (result) {
            if (result) {

                PO = JSON.parse(result);
                PRS = JSON.parse(PO.prs);

                $('#lblReq').html('<u>' + PO.User + '</u>');
                $('#lblProveedor').html('<u>' + PO.Prov + '</u>');
                $('#lblConNombre').html('<u>' + PO.Contact + '</u>');
                $('#divConNombre').text('Buen día ' + PO.Contact + ':');
                $('#lblPRS').html('<u>' + PRS + '</u>');
                  var concep={};
            concep=PRS;            
            P.push(concep);
            /*alert(P);

                $.ajax({
            type: "POST",
            url: base_url + "ordenes_compra/getArchivosPrs",
            data: {prs :JSON.stringify(P)},
            success: function(result) {
               if(result)
                {

                $('#tablaPRS tbody tr').remove();
                tab = $('#tablaPRS tbody')[0];
                var rs = JSON.parse(result);
                 $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                  //  alert(elem);
                   ren.insertCell(0).innerHTML ='<button class="btn btn-success"><a target="_blank" href='+base_url+'compras/ver_pr/'+elem.id+'>'+elem.id+'</a></button>';
                    
                });

                   
                }
            },
            error: function(data){
                new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
                console.log(data);
            },
        });*/


               //$('#tablaPRS').remove();
                //tab = $('#tablaPRS')[0];
                 
                 $.each(PRS, function(i, elem){
                    //var ren = tab.insertRow(tab.rows.length);
                  //  alert(elem);
                  
                  $("#tablaPRS p").append('<button class="btn btn-success btn-xs"><a target="_blank" href='+base_url+'compras/ver_pr/'+elem+'>'+elem+'</a></button>');
                    //ren.insertCell(0).innerHTML ='<button class="btn btn-success btn-xs"><a target="_blank" href='+base_url+'compras/ver_pr/'+elem+'>'+elem+'</a></button>';
                    
                });
                

                if (PO.contacto > 0) {
                    $('#divContacto').show();
                }

                provCorreo = PO.correo;
                $('#lblConPuesto').html('<u>' + PO.puesto + '</u>');
                
                
                $('#lblConCorreo').html('<u>' + provCorreo + '</u>');
                $('#lblPara').html('Para: ' + provCorreo);

                $('#txtShipping').html(PO.shipping_address);
                $('#txtBilling').html(PO.billing_address);
                
                if(PO.rma)
                {
                    $('#lblRMA').text(PO.rma);
                }
                else{
                    $('#divRMA').hide();
                }
                $('#txtNoConfirmacion').val(PO.numero_confirmacion);


                $('#txtMetodoPago').html('<u>' + PO.MetodoPago + '</u>');

                if (PO.aprobador > 0) 
                {
                    $('#divAprobador').show();
                    $('#lblAprobador').html(PO.UserA + ' @ ' + PO.fecha_aprobacion)
                }



                
                var conceptos = JSON.parse(PO.concepto);
                //alert(conceptos);
                $('#tabla tbody tr').remove();
                //alert(PO.UltRev);
                if(revision === undefined){
                    revision=parseInt(PO.UltRev);
                }
                var select = '<select style="width: 70px; display: inline;" onchange="cambiarRevision(this)" id="optRevision" required="required" class="select2_single form-control">'
                for (let index = 0; index <= parseInt(PO.UltRev); index++) {

                        var s = index == revision ? "selected" : "";

                        select += '<option value="' + index + '" ' + s + '>'+ index +'</option>';
                    }
                 

                
              
                select += '</select>';
                //alert(PO.UltRev);
                $('#lblPnlConceptos').html("Conceptos (Rev: " + select + ")");

                tab = $('#tabla tbody')[0];
                $.each(conceptos, function (i, concept) {
                    //alert(JSON.stringify(concept));
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = tab.rows.length;
                    ren.insertCell(1).innerHTML = concept[0];
                    ren.insertCell(2).innerHTML = concept[1];
//alert(JSON.stringify(concept[4]));
                    if(concept[3]==""){

                    ren.insertCell(3).innerHTML = 'N/A';
                    ren.insertCell(4).innerHTML = 'N/A';
                    //ren.insertCell(4).innerHTML = 'N/A';
                    }else{
                    ren.insertCell(3).innerHTML = concept[3];    
                    ren.insertCell(4).innerHTML = "$"+parseFloat(concept[4]).toFixed(2);
                    }
                    ren.insertCell(5).innerHTML = "$"+parseFloat(concept[2]).toFixed(2);


                    
                    //ren.insertCell(4).innerHTML = concept[4];
                    //ren.insertCell(5).innerHTML = concept[5];
                   // 
                   /* var cost = ren.insertCell(4);
                    cost.innerHTML = parseFloat(concept[2]).toFixed(2);
                    cost.style.align = 'right';
                    $(cost, ret).formatCurrency();
                    
                    var ret = ren.insertCell(4);
                    ret.innerHTML = parseFloat(concept[4]).toFixed(2);
                    ret.style.align = 'right';
                    $(ret).formatCurrency();*/
                });

                leerArchivos();
                loadEstatus(PO.estatus);
            }
        },
        error: function (data) {
            new PNotify({
                title: 'ERROR',
                text: 'Error',
                type: 'error',
                styling: 'bootstrap3'
            });
            console.log(data);
        },
    });

}

function loadEstatus(estatus) {
    var btnClass = '';
    $('#btnEstatus').removeClass();
    $('#btnEstatus').html(estatus);

    $('#divPendiente').hide();
    $('#divAutorizada').hide();
    $('#divOrdenada').hide();

    PO.estatus = estatus;

    if (estatus == 'PENDIENTE AUTORIZACION') {
        $('#divPendiente').show();
        btnClass = 'btn btn-warning btn-md';
        $('#divPDF').hide();
    }
    if (estatus == 'AUTORIZADA') {
        $('#divAutorizada').show();
        btnClass = 'btn btn-success btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'ORDENADA') {
        $('#divOrdenada').show();
        btnClass = 'btn btn-success btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'RECIBIDA PARCIAL') {
        $('#divOrdenada').show();
        btnClass = 'btn btn-warning btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'RECIBIDA') {
        btnClass = 'btn btn-primary btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'RECIBIDA TOTAL') {
        btnClass = 'btn btn-primary btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'CERRADA') {
        leerArchivos();
        btnClass = 'btn btn-primary btn-md';
        $('#divPDF').show();
    }
    if (estatus == 'RECHAZADA') {
        btnClass = 'btn btn-danger btn-md';
        $('#divPDF').hide();
    }
    if (estatus == 'CANCELADA') {
        btnClass = 'btn btn-default btn-md';
        $('#divPDF').hide();
    }
    if (estatus == 'LISTA PARA CERRAR') {
        btnClass = 'btn btn-primary btn-md';
    }



    $('#btnEstatus').addClass(btnClass);
}

function guardarNumero(){
    var URL = base_url + "ordenes_compra/ajax_setNoConfirmacion";

    var numero = $('#txtNoConfirmacion').val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { po : ID, numero : numero },
        success: function (response) {
            $('#btnGuardarNoConfirmacion').fadeOut();
        }
    });
}

function setEstatus(btn) {

    if (confirm('¿Desea continuar?')) {
        var stat = $(btn).val();
        var URL = base_url + 'ordenes_compra/ajax_poSetEstatus';
        $.ajax({
            type: "POST",
            url: URL,
            data: { id : ID, estatus : stat, prs : JSON.stringify(PRS), PO : JSON.stringify(PO) },
            success: function (result) {
                if (result) {
                    loadEstatus(stat);
                }
            },
        });
    }
}

function mdlRechazar(btn) {
    var estatus = $(btn).val();
    $('#btnConfirmarRechazo').val(estatus);

    $('#btnConfirmarRechazo').show();
    $('#btnAgregarComentario').hide();
    $('#mdlEstatus').modal('hide');
    $('#mdlComentarios').modal();
}

function estatus_msj(btn) {

    var estatus = $(btn).val();
    var comentario = $('#txtComentarios').val();
    var txtTags = $('#txtTags').val();

    if (comentario.length < 10) {
        alert('Minimo 10 caracteres');
        return;
    }

    var URL = base_url + 'ordenes_compra/ajax_setEstatusMsjPO';
if(confirm('Desea continuar?')){


    $.ajax({
        type: "POST",
        url: URL,
        data: {
            id: ID,
            PO : JSON.stringify(PO),
            estatus: estatus,
            comentario: comentario,
            txtTags: txtTags
        },
        success: function (result) {
            if (result) {
                $('#mdlComentarios').modal('hide');
                //loadEstatus(estatus);
                window.location.href = base_url + 'ordenes_compra/ver_po/' + ID;
            }
        }
    });
}else{
    location.reload();
}

}

function mdlComentarios() {
    $('#btnAgregarComentario').show();
    $('#btnConfirmarRechazo').hide();
    $('#mdlComentarios').modal();
}

function mdlCorreo(){
    $('#mdlCorreo').modal();
}

function mdlRecibir(){
    var URL = base_url + 'ordenes_compra/ajax_getPRsPO';
    $('#tblRecibir tbody tr').remove();
    $.ajax({
        type: "POST",
        url: URL,
        data: { id: ID },
        success: function (result) {
            if (result) 
            {
                tab = $('#tblRecibir tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem)
                {
                    $('#btnRecibir').hide();  
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = elem.pr;
                    ren.insertCell(1).innerHTML = elem.User
                    ren.insertCell(2).innerHTML = elem.tipo
                    ren.insertCell(3).innerHTML = elem.subtipo;
                    ren.insertCell(4).innerHTML = elem.cantidad;
                    if (elem.cantidad!=elem.recibida) {
                     ren.insertCell(5).innerHTML = "<input type='number' id='"+elem.pr+"' style='width:58px'/>";   
                     ren.insertCell(6).innerHTML = "<button data-dismiss='modal' onclick='entregaParcial("+elem.pr+")' class='btn btn-success btn-xs'>Entregar</button>";   
                    }else{
                        ren.insertCell(5).innerHTML='';
                        ren.insertCell(6).innerHTML='';
                    }
                    ren.insertCell(7).innerHTML = elem.cantidad-elem.recibida;
                    ren.insertCell(8).innerHTML = elem.descripcion; 
                    
                    var check = "data-change='1'";
                    if(elem.estatus == 'POR RECIBIR'){
                        check = "checked data-change='0'";
                    }
                    else if (elem.estatus == 'CERRADO'){
                        check = "checked disabled data-change='0'";
                    }
                    if (elem.cantidad==elem.recibida) {
                        ren.insertCell(9).innerHTML = "<input type='checkbox' class='flat' value='" + elem.pr + "' "+ check +"/>";  
                        $('#btnRecibir').show();    
                    }
                    
                });

                $('input.flat').iCheck({
                    checkboxClass: 'icheckbox_flat-green',
                    radioClass: 'iradio_flat-green'
                });

                $('#mdlRecibir').modal();
            }
        }
    });
}

function recibir(){
    var prs = [];
    var todos = 1;
    var rows = $('#tblRecibir tbody tr');
    $.each(rows, function(i, tr)
    {
        cb = $(tr).find("input[type='checkbox']");
        if($(cb).is(':checked') && $(cb).data('change') == "1")
        {
            prs.push($(cb).val());
        }
        if(!$(cb).is(':checked'))
        {
            todos = 0;
        }
    });
    
    
    if(prs.length > 0)
    {
     
        var URL = base_url + 'ordenes_compra/ajax_recibirPO';
        $.ajax({
            type: "POST",
            url: URL,
            data: { id : ID, prs : JSON.stringify(prs), todos : todos },
            success: function (result) {
                if (result) 
                {
      //              alert('dasdsa');
                    cargarDatos();
                }
            }
        });
    }

    $('#mdlRecibir').modal('hide');
    //alert("das");
    //location.reload();
}

//FILES
function leerArchivos() {
    var URL = base_url + 'ordenes_compra/ajax_getEvidenciaInfo';
    
    $.ajax({
        type: "POST",
        url: URL,
        data: { po: ID },
        success: function (result) {
            $('#tablaArchivos tbody tr').remove();


            if (result) {

                var rs = JSON.parse(result);
                var arc1 = rs.nombre1;
                var arc2 = rs.nombre2;


                tab = $('#tablaArchivos tbody')[0];
                
                if(arc1){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = rs.fecha1;
                    ren.insertCell(1).innerHTML = "<i class='fa fa-file-pdf-o'></i> " + rs.nombre1;
                    ren.insertCell(2).innerHTML = rs.User1;
                    ren.insertCell(3).innerHTML = arc1.includes("comp_fact") ? 'COMPROBANTE / FACTURA' : 'COMPROBANTE';
                    var op = ren.insertCell(4);
                    op.innerHTML = "<button type='button' value='1' onclick='verArchivo(this)' class='btn btn-primary btn-xs'><i class='fa fa-file-pdf-o'></i> Ver PDF</button>"
                    if(rs.usuario1 == UID && PO.estatus != "CERRADA")
                    {
                        op.innerHTML += " <button type='button' onclick='eliminarArchivo(this)' data-campo='1' class='btn btn-danger btn-xs'><i class='fa fa-trash'></i> Eliminar</button>"
                    }
                    
                }
                if(arc2){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = rs.fecha2;
                    ren.insertCell(1).innerHTML = "<i class='fa fa-file-pdf-o'></i> " + rs.nombre2;
                    ren.insertCell(2).innerHTML = rs.User2;
                    ren.insertCell(3).innerHTML = "FACTURA";
                    var op = ren.insertCell(4);
                    op.innerHTML = "<button type='button' value='2' onclick='verArchivo(this)' class='btn btn-primary btn-xs'><i class='fa fa-file-pdf-o'></i> Ver PDF</button>"
                    if(rs.usuario2 == UID)
                    {
                        op.innerHTML += " <button type='button' onclick='eliminarArchivo(this)' data-campo='2' class='btn btn-danger btn-xs'><i class='fa fa-trash'></i> Eliminar</button>"
                    }
                }


            
  $('#divRecibida').hide();
                
                /*if(PO.estatus == "RECIBIDA")
                {
                    if (PO.TipoMetodoPago == "TRANSFERENCIA")
                    {
                        $('#divRecibida').show();
                    }
                    else{
                        if (arc1.includes("comp_fact"))
                        {
                            $('#divRecibida').show();
                        } 
                        else if (arc1.includes("comp") && arc2.includes("fact"))
                        {
                            $('#divRecibida').show();
                        }
                    }
                }*/
                if(PO.estatus == "RECIBIDA" || PO.estatus == "LISTA PARA CERRAR")
                {
                    
                    if (arc1.includes("comp_fact"))
                        {
                            

                            $('#divRecibida').show();
                        } 
                        else if (arc1.includes("comp") && arc2.includes("fact") && PO.estatus == "LISTA PARA CERRAR")
                        {
                            
                            $('#divRecibida').show();
                        }
                    
                }                


            }
        }
    });
//window.location.reload();
}

function _(el) {
    return document.getElementById(el);
}

function uploadFile() {
    //var file = _("userfile").files[0];
    //alert(file.name+" | "+file.size+" | "+file.type);
    $('#mdlArchivos').modal();
}

function progressHandler(event) {
    var percent = (event.loaded / event.total) * 100;
    $(".progress-bar").attr('aria-valuenow', Math.round(percent)).css('width', Math.round(percent) + '%');
}

function completeHandler(event) {
    /*var res = JSON.parse(event.target.responseText);*/
    _("userfile").value = "";

    $("#divArchivo").show();
    $("#lblArchivo").html("<u>" + event.target.responseText + "</u>");
    $("#imgArchivo").attr("src", file_image(event.target.responseText));
    $("#divArchivo").show();

    $("#btnArchivo").fadeOut('slow', function () {
        $("#btnBorrarArchivo").fadeIn('slow');
    });
}

function errorArchivo(event) {
    alert('ERROR');
}

function loadArchivo(event) {
    _("userfile").value = "";
    leerArchivos();
}

function eliminarArchivo(btn) {
    if(confirm('¿Desea eliminar evidencia?'))
    {
        var campo = $(btn).data('campo');
        var URL = base_url + 'ordenes_compra/ajax_eliminarEvidencia';

        $.ajax({
            type: "POST",
            url: URL,
            data: { po: ID, campo : campo },
            success: function (result) {
                if (result) {
                    leerArchivos();
                }
            }
        });
    }
}

function subirEvidencia(tipo) {
    var campo = '';
    var nombre = '';

    if (tipo == 1) {
        campo = '1';
        nombre = 'comp_' + paddy(ID, 6);
    } else if (tipo == 2) {
        campo = '2';
        nombre = 'fact_' + paddy(ID, 6);
    } else if (tipo == 3) {
        campo = '1';
        nombre = 'comp_fact_' + paddy(ID, 6);
    }


    var file = _("userfile").files[0];
    var URL = base_url + 'ordenes_compra/ajax_subirEvidencia';

    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("po", ID);
    formdata.append("campo", campo);
    formdata.append("nombre", nombre);

    var ajax = new XMLHttpRequest();
    ajax.addEventListener("load", loadArchivo, false);
    ajax.addEventListener("error", errorArchivo, false);
    ajax.open("POST", URL);
    ajax.send(formdata);

    $('#mdlArchivos').modal('hide');   
    //window.location.reload();
}

function enviarCorreo(){
    $('#mdlCorreo').modal('hide');
    var URL = base_url + 'ordenes_compra/ajax_enviarPO';
    var text = $('#editor-one').html();


    $.ajax({
        type: "POST",
        url: URL,
        data: { body : text, para : provCorreo, po : ID},
        success: function (result) 
        {
            if (result) {
                //var rs = JSON.parse(result);
            }
        },
    });
}

function verArchivo(btn){
    var file = $(btn).val();
    
    var URL = base_url + 'ordenes_compra/evidencia';
    var frm = document.createElement("form");
    frm.setAttribute('method',"post");
    frm.setAttribute('target',"_blank");
    frm.setAttribute('action', URL);
    

    var po = document.createElement("input");
    po.setAttribute('type',"hidden");
    po.setAttribute('name',"po");
    po.setAttribute('value', ID);
    frm.appendChild(po);

    var f = document.createElement("input");
    f.setAttribute('type',"hidden");
    f.setAttribute('name',"file");
    f.setAttribute('value', file);
    frm.appendChild(f);

    $(document.body).append(frm);
    frm.submit();
}

//////////// A C C I O N E S ////////////
function cargarAcciones(){

    $("#tblAcciones tbody tr").remove();
    var URL = base_url + 'ordenes_compra/ajax_getAcciones';

    $.ajax({
        type: "POST",
        url: URL,
        data: { po : ID },
        success: function (response) {
            if(response){
                var tbl = $("#tblAcciones tbody")[0];
                var rs = JSON.parse(response);

                $.each(rs, function (i, elem) { 
                    var ren = tbl.insertRow();
                    ren.dataset.id = elem.id;
                    ren.dataset.accion = elem.accion;
                    ren.dataset.estatus = elem.estatus;
                    ren.dataset.usuario = elem.usuario;
                    ren.dataset.limite = moment(elem.fecha_limite).format('DD/MM/YYYY h:mm A');
                    ren.dataset.realizada = moment(elem.fecha_realizada).format('DD/MM/YYYY h:mm A');

                    ren.insertCell().innerHTML = moment(elem.fecha_creacion).format('DD/MM/YYYY h:mm A');
                    ren.insertCell().innerHTML = elem.User;
                    ren.insertCell().innerHTML = elem.accion;
                    ren.insertCell().innerHTML = moment(elem.fecha_limite).format('DD/MM/YYYY h:mm A');
                    ren.insertCell().innerHTML = '<button onclick="mdlAccionFeedback(this)" type="button" class="btn btn-xs ' + btnAccionColor(elem.estatus, moment(elem.fecha_limite), moment(elem.fecha_realizada))[0] + '"><i class="' + btnAccionColor(elem.estatus, moment(elem.fecha_limite), moment(elem.fecha_realizada))[1] + '"></i> ' + btnAccionColor(elem.estatus, moment(elem.fecha_limite), moment(elem.fecha_realizada))[2] + '</button>';
                });
                
            }
        }
    });
}

function btnAccionColor(estatus, fecha_limite, fecha_realizada){

    var valor = ['btn-default', 'fa', estatus];

    if(estatus == "CANCELADA")
    {
        valor = ['btn-default', 'fa fa-close', 'CANCELADA'];
    }

    if(estatus == "PENDIENTE")
    {
        if(moment() > fecha_limite) //SE VENCIO
        {
            valor = ['btn-danger', 'fa fa-clock-o', 'VENCIDA'];
        }
        else{
            valor = ['btn-warning', 'fa fa-clock-o', 'PENDIENTE'];
        }
    }

    if(estatus == "REALIZADA")
    {
        if(fecha_realizada > fecha_limite) //NO SE CUMPLIO A TIEMPO
        {
            valor = ['btn-danger', 'fa fa-check', 'REALIZADA FUERA DE TIEMPO'];
        }
        else{
            valor = ['btn-success', 'fa fa-check', 'REALIZADA'];
        }
    }




    return valor;
}

function mdlAccion(){
    $('#mdlAccion').modal();
}

function mdlAccionFeedback(btn){

    var ren = $(btn).closest('tr');

    var id_accion = $(ren).data('id');
    var accion = $(ren).data('accion');
    var estatus = $(ren).data('estatus');
    var usuario = $(ren).data('usuario');
    var limite = $(ren).data('limite');
    var realizada = $(ren).data('realizada');

    if(realizada == "Invalid date")
    {
        $('#divFechaRealizada').hide();
    }
    else{
        $('#divFechaRealizada').show();
    }

    if(estatus == "PENDIENTE" && usuario == UID){
        $('#divBtnAccionFeedback').show();
        $('#divCommentAccionFeedback').show();
    }
    else{
        $('#divBtnAccionFeedback').hide();
        $('#divCommentAccionFeedback').hide();
    }

    $('#mdlAccionFeedback').data('id', id_accion);

    $('#txtAccionFeed').text(accion);
    $('#lblFechaLimite').text(limite);
    $('#lblFechaRealizada').text(realizada);
    

    $('#btnAgregarAccionComentario').val(id_accion);
    cargarComentariosAccion(id_accion);
    $('#mdlAccionFeedback').modal();
}

function cargarComentariosAccion(id_accion){
    var URL = base_url + "ordenes_compra/ajax_getAccionComentarios";
    
    $('#ulComments').html("");
    
    $.ajax({
        type: "POST",
        url: URL,
        data: { accion : id_accion },
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var c = '<li>'
                    +    '<a>'
                    +        '<span>'
                    +            '<b>' + elem.User + '</b> ' + moment(elem.fecha).format('DD/MM/YYYY h:mm A') + '</span>'
                    +        '</span>'
                    +        '<span class="message">' + elem.comentario + '</span>'
                    +    '</a>'
                    +'</li>';
                    $('#ulComments').append(c);
                });
            }
        },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function agregarComentarioAccion(btn){
    var id_accion = $(btn).val();


    if(!$('#txtAccionComentario').val().trim())
    {
        alert('Ingrese comentario');
        return;
    }

    var URL = base_url + "ordenes_compra/ajax_setAccionComentario";
    var data = {};
    data.accion = id_accion;
    data.comentario = $('#txtAccionComentario').val();
    $('#txtAccionComentario').val("");

    $.ajax({
        type: "POST",
        url: URL,
        data: { data : JSON.stringify(data) },
        success: function (response) {
            if(response){
                alert(id_accion);
                cargarComentariosAccion(id_accion);
            }
        }
    });


}



function crearAccion(){
document.getElementById('btnSeguimiento').disabled = true;
    if(!$('#txtAccion').val().trim())
    {
        alert('Ingrese Acción');
        return;
    }

    var URL = base_url + "ordenes_compra/ajax_setAccion";
    var data = {};

    var enviar_contacto = $('#cbEnviarContacto').is(':checked') ? 1 : 0;
    var correo;
    if (enviar_contacto ==1) {
        correo=provCorreo;
    }
    data.po = ID;
    data.accion = $('#txtAccion').val().trim();
    data.fecha_limite = $('#txtFechaAccion').val();
    //alert(JSON.stringify(data));

    $.ajax({
        type: "POST",
        url: URL,
        data: { data : JSON.stringify(data),  correo:correo},
        success: function (response) {
            if(response){
document.getElementById('btnSeguimiento').disabled = false;
                $('#mdlAccion').modal('hide');
                $('#txtAccion').val("");
                $('#txtFechaAccion').val(0);
               // window.location.href = base_url + 'ordenes_compra/eventoICS/' + response;
                cargarAcciones();
            }
        }
    });
}

function realizarAccion(){
    if(confirm("¿Desea marcar acción como realizada?"))
    {
        var id = $('#mdlAccionFeedback').data('id');

        var URL = base_url + "ordenes_compra/ajax_setAccionRealizada";
        var data = {};
        data.id = id;
        data.estatus = "REALIZADA";

        $.ajax({
            type: "POST",
            url: URL,
            data: { data : JSON.stringify(data) },
            success: function (response) {
                $('#mdlAccionFeedback').modal('hide');
                cargarAcciones();
            }
        });
    }    
}

function cancelarAccion(){
    if(confirm("¿Desea cancelar acción?"))
    {
        var id = $('#mdlAccionFeedback').data('id');

        var URL = base_url + "ordenes_compra/ajax_updateAccion";
        var data = {};
        data.id = id;
        data.estatus = "CANCELADA";

        $.ajax({
            type: "POST",
            url: URL,
            data: { data : JSON.stringify(data) },
            success: function (response) {
                $('#mdlAccionFeedback').modal('hide');
                cargarAcciones();
            }
        });
    }  
}

function bitacoraEstatus(){
    $('#tblBitacora tbody tr').remove();
    var URL = base_url + "ordenes_compra/ajax_getNombresUsuarios";
    var dic = {}
    POAc = JSON.parse(PO.id);
    //alert(POAc);
    $.ajax({
        type: "POST",
        url: URL,
        data: { POAc : POAc },
        success: function (response) {
            if(response){
                var rs = JSON.parse(response);
                var tbl = $('#tblBitacora tbody')[0];
                $.each(rs, function(i, elem){
                    var ren = tbl.insertRow(tbl.rows.length);
                    
                    ren.insertCell(0).innerHTML = elem.estatus;
                    ren.insertCell(1).innerHTML = elem.fecha;
                    ren.insertCell(2).innerHTML = elem.user;
                    
                    
                });

                $('#mdlBitacora').modal();
            }
        }
    });   
}

function mdlRastreo() {
    $('#btnAgregarComentario').show();
    //$('#btnConfirmarRechazo').hide();
    $('#mdlRastreo').modal();
}

function iniciar_daterangepicker() {
    $("#txtFechaAccion").daterangepicker({
        timePicker: true,
        singleDatePicker: true,
        timePickerIncrement: 15,
        locale: {
            format: 'YYYY-MM-DD H:mm'
        }
    });
}

function cambiarRevision(opt){
    $('#tablaPRS p').html('');  
    cargarDatos($(opt).val());

}

function entregaParcial(pr){
    
    qty=$('#'+pr).val();
    //alert(qty);
    var URL = base_url + 'ordenes_compra/entregaParcial';
    if (qty == '') {
        alert('Ingrese la cantidad a entegar');
        mdlRecibir();
    }else{

    $('#tblRecibir tbody tr').remove();
        $.ajax({
            type: "POST",
            url: URL,
            data: { pr:pr, qty:qty, po : ID },
            success: function (result) {
                if (result) 
                {
                    window.location.reload();
                    mdlRecibir();
                }else{
                    alert('No puedes entregar mas de la cantidad requerida.');
                    mdlRecibir();
                }
            }
        });
}
}

