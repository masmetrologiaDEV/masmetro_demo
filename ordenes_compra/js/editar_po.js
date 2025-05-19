var ID = 0;
var ID_PROV = 0;
var NOMBRE_PROV;
var CONTACTO = 0;
var NOMBRE_CONTACTO;
var PRS = [];

var Resico;

var SUBTOTAL = 0;
var DESCUENTO = 0;
var FACTOR_IMPUESTO = 0;
var IMPUESTO = 0;
var TOTAL = 0;
var TIPO_CAMBIO = 0;
var IvaRet = 0;

var METODOPAGO = 0;
var ENTREGA = '';
var retencion = 0;
var USA = 0;

function load(){
    sumarTotales();
    sumarRetencion();
    //calcularRetencion();
    iniciar_daterangepicker();

    eventos();
    cargarDatos();
    buscar();

    setInterval(buscarPRs, 3000);
    
}

function eventos(){
    $( '#mdlDescuento' ).on( 'keypress', function( e ) {
        if( e.keyCode === 13 ) {
            e.preventDefault();
            setDescuento();
        }
    });
}

function cargarDatos(){
    ID = id;
    var URL = base_url + "ordenes_compra/ajax_getPO";

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : ID },
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);

                if(rs.tipo == 'SERVICIO' && rs.rma_requerido == '1')
                {
                    $('#divRMA').show();
                }
                
                ID_PROV = rs.proveedor;
                ENTREGA = rs.entrega;
                DESCUENTO = rs.descuento;
                NOMBRE_PROV = rs.Prov;
                Resico = rs.valResico;
                TIPO_CAMBIO = rs.tipo_cambio;
                PRS = JSON.parse(rs.prs);
                //console.log(PRS);
                $('#lblProveedor').html("Proveedor: <small><u>" + NOMBRE_PROV + "</u></small>");
                $('#lblResico').html("Resico: <small><u>" + Resico + "</u></small>");

                cargarDirecciones();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function buscar(){
    var URL = base_url + "ordenes_compra/ajax_getConceptosPO";
    var costos = [];

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : ID },
        success: function(result) {
            if(result)
            {
                
                var rs = JSON.parse(result);
                tab = $('#tabla tbody.con')[0];
                $.each(rs, function(t, concep)
                {
                    //alert(JSON.stringify(concep.costos));
                    var costo = JSON.parse(concep.costos);
                    $.each(costo, function(i, elem)
                    {
                        var ren = tab.insertRow(tab.rows.length);
                        //var t= JSON.stringify(i);
                        ren.insertCell(0).innerHTML = tab.rows.length;
                        ren.insertCell(1).innerHTML = "<input name='cantidad' min='1' onchange='calcularImporte(this)' style='width: 90%; border: none;' type='number' value='" + concep.cantidad +"'/>";
                        
                        ren.insertCell(2).innerHTML = "<input name='concepto' style='width: 95%; border: none;' type='text' value='" + i +"'/>";
                        
                        
                        ren.insertCell(3).innerHTML = "<input name='precio' min='0' onchange='calcularImporte(this)' style='width: 90%; text-align: right; border: none;' type='number' value='" + parseFloat(elem).toFixed(2) + "'/>";
                        ren.insertCell(4).innerHTML = "<div id='importe' name='importe' data-monto='" + (parseFloat(concep.cantidad) * parseFloat(elem)).toFixed(2) + "' style='width: 90%; text-align: right;'>" + (parseFloat(concep.cantidad) * parseFloat(elem)).toFixed(2) +"</div>";
                        ren.insertCell(5).innerHTML = "<input name='ivaRet' required min='0' onchange='calcularImporte(this)' style='width: 90%; text-align: right; border: none;' type='number' value='0.0'/>";
                        ren.insertCell(6).innerHTML = "<div id='retencion' name='retencion' data-monto='' style='width: 90%; text-align: right;'>0.00</div>";
                        ren.insertCell(7).innerHTML = "<button style='width: 100px;' class='btn btn-default btn-xs pull-right' onclick='showComment(this)' type='button'><i class='fa fa-comments-o'></i> Comentarios</button><br><button style='width: 100px;' class='btn btn-danger btn-xs pull-right' onclick='eliminarConcepto(this)' type='button'><i class='fa fa-trash'></i> Eliminar</button>";

                        var div = "<td class='comments' style='display: none;' colspan=6><label>Comentarios</label><div><textarea style='width: 100%; border: solid 1px;'></textarea></div></td>";
                        $(tab).append(div);


                    });                    
                });
                    
                tab = $('#tabla tbody.sub')[0];
                var renS = tab.insertRow(tab.rows.length);
                renS.insertCell(0);
                renS.insertCell(1);
                renS.insertCell(2);
                renS.insertCell(3).outerHTML = "<th><div style='width: 90%; text-align: right;'>Sub-Total</div></th>";
                renS.insertCell(4).outerHTML = "<th><div id='lblSubtotal' style='width: 90%; text-align: right;'></div></th>";
                renS.insertCell(5);

                var renD = tab.insertRow(tab.rows.length);
                renD.insertCell(0).style.borderTop = "none";
                renD.insertCell(1).style.borderTop = "none";
                renD.insertCell(2).style.borderTop = "none";
                renD.insertCell(3).outerHTML = "<th><div style='width: 90%; text-align: right;'>Descuento</div></th>";
                renD.insertCell(4).outerHTML = "<th><div id='lblDescuento' style='width: 90%; text-align: right;'>" + DESCUENTO + "</div></th>";
                var cellD = renD.insertCell(5);
                cellD.style.borderTop  = "none";
                cellD.innerHTML = "<button class='btn btn-warning btn-xs pull-right' onclick='mdlDescuento()' type='button'><i class='fa fa-plus'></i> Descuento</button>";


                var renI = tab.insertRow(tab.rows.length);
                renI.insertCell(0).style.borderTop = "none";
                renI.insertCell(1).style.borderTop = "none";
                renI.insertCell(2).style.borderTop = "none";
                renI.insertCell(3).outerHTML = "<th><div id='lblNombreImpuesto' style='width: 90%; text-align: right;'>Exento (0.00%)</div></th>";
                renI.insertCell(4).outerHTML = "<th><div id='lblImpuesto' style='width: 90%; text-align: right;'></div></th>";
                var cellI = renI.insertCell(5);
                cellI.style.borderTop  = "none";
                cellI.innerHTML = "<button class='btn btn-warning btn-xs pull-right' onclick='mdlImpuestos()' type='button'><i class='fa fa-plus'></i> Impuesto</button>";

                var renIv = tab.insertRow(tab.rows.length);
                renIv.insertCell(0).style.borderTop = "none";
                renIv.insertCell(1).style.borderTop = "none";
                renIv.insertCell(2).style.borderTop = "none";
                renIv.insertCell(3).outerHTML = "<th><div id='lblivaret' style='width: 90%; text-align: right;'>IVA Retenido</div></th>";
                renIv.insertCell(4).outerHTML = "<th><div id='lblImpu' style='width: 90%; text-align: right;'>0.00</div></th>";
                var cellIv = renIv.insertCell(5);

                var renRe = tab.insertRow(tab.rows.length);
                renRe.insertCell(0).style.borderTop = "none";
                renRe.insertCell(1).style.borderTop = "none";
                renRe.insertCell(2).style.borderTop = "none";
                if (Resico) {
                    renRe.insertCell(3).outerHTML = "<th><div id='lblResico' style='width: 90%; text-align: right;'> Retención ISR: ("+Resico+"%)</div></th>";
                    renRe.insertCell(4).outerHTML = "<th><div id='lblValRes' style='width: 90%; text-align: right;'>0.00</div></th>";
                     var cellRe = renRe.insertCell(5);
                }else{
                   // renRe.insertCell(3).outerHTML = "<th><div id='lblValRes' style='width: 90%; text-align: right;'>0.00</div></th>";
                     var cellRe = renRe.insertCell(3);
                }
                
                
               
                
                
                var renT = tab.insertRow(tab.rows.length);
                renT.insertCell(0).style.borderTop = "none";
                renT.insertCell(1).style.borderTop = "none";
                renT.insertCell(2).style.borderTop = "none";
                renT.insertCell(3).outerHTML = "<th><div style='width: 90%; text-align: right;'>Total</div></th>";
                renT.insertCell(4).outerHTML = "<th><div id='lblTotal' style='width: 90%; text-align: right;'></div></th>";
                renT.insertCell(5).style.borderTop = "none";

                $("input[name='concepto'], input[name='cantidad'], input[name='precio']").hover(function(){
                    $(this).css("background-color", "#fffb19");
                    }, function(){
                    $(this).css("background-color", "#fff");
                });

                $("div[name='importe'], #lblDescuento").formatCurrency();

                sumarTotales();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function mdlContactos(){
    var URL = base_url + 'empresas/ajax_getContactos';
    $('#tblContactos tbody tr').remove();

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : ID_PROV },
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                var tab = $('#tblContactos tbody')[0];
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = elem.nombre;
                    ren.insertCell(1).innerHTML = elem.puesto;
                    ren.insertCell(2).innerHTML = "<button type='button' onclick='asignarContacto(this)' class='btn btn-primary btn-xs' data-nombre='" + elem.nombre + "' data-puesto='" + elem.puesto + "' data-correo='" + elem.correo + "' value=" + elem.id + "><i class='fa fa-check'></i> Seleccionar</button>";
                });


                $('#mdlContactos').modal();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function mdlMetodosPago(){
    var URL = base_url + 'recursos/ajax_getMetodosPago';

    $('#tblMetodosPago tbody tr').remove();

    $.ajax({
        type: "POST",
        url: URL,
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                var tab = $('#tblMetodosPago tbody')[0];
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell().innerHTML = elem.tipo;
                    ren.insertCell().innerHTML = elem.nombre;
                    ren.insertCell().innerHTML = elem.comentarios;
                    var cell = ren.insertCell()
                    cell.innerHTML = elem.Saldo;
                    cell.classList.add("saldo");
                    ren.insertCell().innerHTML = "<button type='button' onclick='asignarMetodoPago(this)' class='btn btn-primary btn-xs' data-nombre='" + elem.nombre + "' data-saldo=" + elem.Saldo + " value=" + elem.id + "><i class='fa fa-check'></i> Seleccionar</button>";
                });

                $('.saldo').formatCurrency();
                $('#mdlMetodosPago').modal();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function asignarContacto(btn){
    var id = $(btn).val();
    CONTACTO = id;

    NOMBRE_CONTACTO = btn.dataset.nombre;
    var puesto = btn.dataset.puesto;
    var correo = btn.dataset.correo;
    
    $('#lblConNombre').html('<u>' + NOMBRE_CONTACTO + '</u>');
    $('#lblConPuesto').html('<u>' + puesto + '</u>');
    $('#lblConCorreo').html('<u>' + correo + '</u>');

    $('#mdlContactos').modal('hide');

    $('#divContacto').fadeIn('slow');


}

function asignarMetodoPago(btn){
    var id = $(btn).val();
    METODOPAGO = id;

    var nombre = btn.dataset.nombre;
    var saldo = btn.dataset.saldo;
    
    $('#txtMetodo').html('<u>' + nombre + '</u>');
    $('#mdlMetodosPago').modal('hide');

    $("#opRecurso option").remove();
    $("#divRecurso").show();

    if(saldo < (TOTAL * TIPO_CAMBIO))
    {
        $('#opRecurso').append(new Option('PENDIENTE', 'PENDIENTE'));
    }
    else
    {
        $('#opRecurso').append(new Option('PENDIENTE', 'PENDIENTE'));
        $('#opRecurso').append(new Option('PROVISIONADO', 'PROVISIONADO'));
    }

}

function cargarDirecciones(){
    //$('#opShipping').append(new Option('', ''));
    $.ajax({
        type: "POST",
        url: base_url + 'ordenes_compra/ajax_getShippingAddress',
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var option = new Option(elem.nombre, elem.direccion);
                    if(elem.default == "1" && elem.pais == ENTREGA)
                    {
                        option.selected = true;
                        $('#txtShipping').text(elem.direccion);
                    }
                    option.dataset.pais = elem.pais;
                    $('#opShipping').append(option);
                });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });


      //$('#opBilling').append(new Option('', ''));
      $.ajax({
        type: "POST",
        url: base_url + 'ordenes_compra/ajax_getBillingAddress',
        success: function(result) {
            if(result)
            {
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var option = new Option(elem.nombre, elem.direccion);
                    
                    if(elem.default == "1")
                    {
                        option.selected = true;
                        $('#txtBilling').text(elem.direccion);
                    }
                    
                    $('#opBilling').append(option);
                    
                });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function selectShipp(){
    var dir = $('#opShipping').val();
    $('#txtShipping').text(dir);
    
    var p = $('#opShipping').find('option:selected').data('pais');
    if(p != ENTREGA)
    {
        $('#txtShippingWarning').html("<u><i class='fa fa-warning'></i> Dirección de envio no coincide con país de entrega</u>");
    }
    else
    {
        $('#txtShippingWarning').text('');
    }
}

function showComment(btn){
    var row = $(btn).closest('tr')[0];
    var box = $(row).next()[0];
    if(box.style.display == 'none')
    {
        $(box).show('slow');
    }
    else{
        $(box).hide('slow');
    }
}

function selectBill(){
    var dir = $('#opBilling').val();
    $('#txtBilling').text(dir);
}

function validacion(){
    var rows = $('#tabla tbody.con tr');
    if(rows.length <= 0)
    {
        alert('Tabla se encuentra vacia');
        return false;
    }
    if(CONTACTO == 0)
    {
        alert('Asigne contacto');
        return false;
    }
    if(METODOPAGO == 0)
    {
        alert('Seleccione metodo de pago');
        return false;
    }
    if($('#divRMA').is(':visible') && !$('#txtRMA').val().trim())
    {
        alert('Ingrese RMA');
        return false;
    }
    if(!confirm('¿Desea continuar?'))
    {
        return false;
    }

    return true;
}

function eliminarConcepto(btn){
    if(confirm('¿Desea eliminar concepto?'))
    {
        var row = $(btn).closest('tr')[0];
        var box = $(row).next()[0];

        row.remove();
        box.remove();
        
        numerarTabla();
    }
}

function numerarTabla(){
    var rows = $('#tabla tbody.con tr');
    $.each(rows, function(i, elem)
    {
        $(elem).find('td').eq(0).html(elem.rowIndex);
    });
}

function cancelar(){
    if(confirm('¿Desea cancelar PO?'))
    {
        var URL = base_url + 'ordenes_compra/ajax_cancelarPO';
        $.ajax({
            type: "POST",
            url: URL,
            data: { id : ID },
            success: function(result) {
                if(result)
                {
                    window.location.href = base_url + 'ordenes_compra/catalogo_po';
                }
            },
            error: function(data){
                new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
                console.log(data);
            },
        });
    }
}

function aprobacion(){
    if(validacion())
    {
        var URL = base_url + 'ordenes_compra/ajax_solicitarAprobacionPO';
        var billing_a = $('#opBilling').val();
        var shipping_a = $('#opShipping').val();
        var impuesto_nombre = $('#lblNombreImpuesto').html();
        var nombreProveedor = NOMBRE_PROV;
        var nombreContacto = NOMBRE_CONTACTO;
        var rma = $('#txtRMA').val().trim();
        var CONCEPTOS = [];

        var recurso = $("#opRecurso").val();
        var fecha_cobro = $('#single_cal').val();

        var rows = $('#tabla tbody.con tr'); var no = 1;
        $.each(rows, function(i, elem){
            var q = $(elem).find("input[name='cantidad']").val();
            var con = $(elem).find("input[name='concepto']").val();
            var imp = $(elem).find("div[name='importe']").data('monto');
            var ivaret = $(elem).find("input[name='ivaRet']").val()+"%";
            var ret = $(elem).find("div[name='retencion']").data('monto');
            //alert(ivaret);
            if(ivaret == '0.0%'){
                var concepto = [q, con, imp, $(elem).next().find('textarea').val()];
            }
            else{
            var concepto = [q, con, imp, ivaret, ret, $(elem).next().find('textarea').val()];    
            }
            

            CONCEPTOS.push(concepto);

            no++;
        });

        $.ajax({
            type: "POST",
            url: URL,
            data: { id : ID, billing_a : billing_a, shipping_a : shipping_a, contacto : CONTACTO,
                conceptos : JSON.stringify(CONCEPTOS), estatus : 'PENDIENTE AUTORIZACION', metodo_pago : METODOPAGO, rma : rma,
                subtotal : SUBTOTAL, descuento : DESCUENTO, impuesto : IMPUESTO, impuesto_nombre : impuesto_nombre,
                impuesto_factor : FACTOR_IMPUESTO, total : TOTAL, retencion : retencion, ivaRet : IvaRet, nombre_proveedor : nombreProveedor, nombre_contacto : nombreContacto,
                recurso : recurso, fecha_cobro : fecha_cobro
             },
            success: function(result) {
                if(result)
                {

                    window.location.href = base_url + 'ordenes_compra/ver_po/' + ID               
                     }
            },
            error: function(data){
                new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
                console.log(data);
            },
        });
       // alert(JSON.stringify(CONCEPTOS));
        
    }
}

function calcularImporte(inp){
    var rows = $('tbody.con tr');
    var row = $(inp).closest('tr');

    var cant = $(row).find("input[name='cantidad']").val();
    var pu = $(row).find("input[name='precio']").val();
    var importe = cant * pu;
    var ivaR=$(row).find("input[name='ivaRet']").val()/100;
    var ret= importe*ivaR;


    $(row).find("div[name='importe']").data('monto', importe);
    $(row).find("div[name='importe']").html(importe);
    $(row).find("div[name='importe']").formatCurrency();
    $(row).find("div[name='retencion']").data('monto', ret);
    $(row).find("div[name='retencion']").html(ret);
    $(row).find("div[name='retencion']").formatCurrency();
        sumarTotales();
        sumarRetencion();

    

}


function mdlDescuento(){
    $('#txtDescuento').val(DESCUENTO);
    $('#mdlDescuento').modal();
}

function mdlImpuestos(){
    $('#mdlImpuestos').modal();
}

function setDescuento(){
    DESCUENTO = $('#txtDescuento').val();
    $('#lblDescuento').html(DESCUENTO);
    $('#lblDescuento').formatCurrency();
    $('#mdlDescuento').modal('hide');

    sumarTotales();
}

function setImpuesto(btn){
    var impuesto = btn.dataset.imp;
    var factor = btn.dataset.factor;

    FACTOR_IMPUESTO = factor;

    $('#lblNombreImpuesto').html(impuesto);
    $('#mdlImpuestos').modal('hide');

    sumarTotales();
}

function sumarTotales(){
    var Res = parseFloat(Resico)/100;
    if (isNaN(Res)) {
        Res =0;
    }
    var rows = $('tbody.con tr');

    SUBTOTAL = 0;
    $.each(rows, function(i, elem){
        SUBTOTAL += parseFloat($(elem).find("div[name='importe']").data('monto'));
    });

    if(USA != 0){
        FACTOR_IMPUESTO=USA;

        //alert(FACTOR_IMPUESTO);

    }else{
        FACTOR_IMPUESTO=parseFloat(FACTOR_IMPUESTO).toFixed(2);
    }
//alert(FACTOR_IMPUESTO);

    //IMPUESTO = (SUBTOTAL - DESCUENTO) * FACTOR_IMPUESTO;
    IMPUESTO = (parseFloat(SUBTOTAL).toFixed(2) - parseFloat(DESCUENTO).toFixed(2)) * FACTOR_IMPUESTO;
    //alert((parseFloat(SUBTOTAL).toFixed(2) +'-'+ parseFloat(DESCUENTO).toFixed(2)) +'x'+ parseFloat(FACTOR_IMPUESTO).toFixed(2));

    //TOTAL = ((SUBTOTAL - DESCUENTO) + IMPUESTO) - retencion ;    
    TOTAL = ((parseFloat(SUBTOTAL).toFixed(2) - parseFloat(DESCUENTO).toFixed(2)) + parseFloat(IMPUESTO)) - parseFloat(retencion).toFixed(2);    

    
    //IvaRet = SUBTOTAL * Res;
    IvaRet = parseFloat(SUBTOTAL).toFixed(2) * parseFloat(Res);
   // alert(parseFloat(Res).toFixed(2));
    //TOTAL = TOTAL - IvaRet;
    TOTAL = parseFloat(TOTAL).toFixed(2) - parseFloat(IvaRet).toFixed(2);

    //alert('ads'+Res);

    $('#lblTotal').html(TOTAL);
    $('#lblSubtotal').html(SUBTOTAL);
    $('#lblImpuesto').html(IMPUESTO);
    
    $('#lblValRes').html(IvaRet);
   
    $('#lblSubtotal, #lblTotal, #lblImpuesto, #lblValRes').formatCurrency();
    
}

function sumarRetencion(){
    var Res = parseFloat(Resico)/100;
    if (isNaN(Res)) {
        Res =0;
    }
    var rows = $('tbody.con tr');

   
    retencion = 0;
    $.each(rows, function(i, elem){

        ret = parseFloat($(elem).find("div[name='retencion']").data('monto'));
        retencion  = retencion + ret;
        
    });
    
    if (isNaN(retencion) ) {
        retencion =0;
    }
     
    $('#lblImpu').html(retencion);
    
   
    $('#lblImpu').formatCurrency();
}

//DESARROLLE SU GENIALIDAD AQUI!

function buscarPRs(){
    //console.log(PRS);
    var URL = base_url + 'ordenes_compra/ajax_getPRsAgregadas';

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : ID },
        success: function(result) {
            if(result)
            {
                var noIncluidas = [];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    if(!PRS.includes(elem))
                    {
                        PRS.push(elem);
                        noIncluidas.push(elem);
                    }
                });

                if(noIncluidas.length > 0)
                {
                    alert("Se han agregado PR's a la PO");
                    agregarPRS(noIncluidas);
                }
            }
          },
    });
}

function agregarPRS(ar_prs){
    var URL = base_url + "ordenes_compra/ajax_getConceptosPO_fromPRS";
    var costos = [];

    $.ajax({
        type: "POST",
        url: URL,
        data: { id : ID, prs : JSON.stringify(ar_prs), prs_actuales : JSON.stringify(PRS) },
        success: function(result) {
            if(result)
            {                
                var rs = JSON.parse(result);
                tab = $('#tabla tbody.con')[0];
                $.each(rs, function(i, concep)
                {
                    var costo = JSON.parse(concep.costos);
                    
                    $.each(costo, function(i, elem)
                    {
                        var ren = tab.insertRow(tab.rows.length);
                        ren.insertCell(0).innerHTML = tab.rows.length;
                        ren.insertCell(1).innerHTML = "<input name='cantidad' min='1' onchange='calcularImporte(this)' style='width: 90%; border: none;' type='number' value='" + concep.cantidad +"'/>";
                        ren.insertCell(2).innerHTML = "<input name='concepto' style='width: 95%; border: none;' type='text' value='" + i +"'/>";
                        ren.insertCell(3).innerHTML = "<input name='precio' min='0' onchange='calcularImporte(this)' style='width: 90%; text-align: right; border: none;' type='number' value='" + parseFloat(elem).toFixed(2) + "'/>";
                        ren.insertCell(4).innerHTML = "<div name='importe' data-monto='" + (parseFloat(concep.cantidad) * parseFloat(elem)).toFixed(2) + "' style='width: 90%; text-align: right;'>" + (parseFloat(concep.cantidad) * parseFloat(elem)).toFixed(2) +"</div>";
                        ren.insertCell(5).innerHTML = "<button style='width: 100px;' class='btn btn-default btn-xs pull-right' onclick='showComment(this)' type='button'><i class='fa fa-comments-o'></i> Comentarios</button><br><button style='width: 100px;' class='btn btn-danger btn-xs pull-right' onclick='eliminarConcepto(this)' type='button'><i class='fa fa-trash'></i> Eliminar</button>";

                        var div = "<td class='comments' style='display: none;' colspan=6><label>Comentarios</label><div><textarea style='width: 100%; border: solid 1px;'></textarea></div></td>";
                        $(tab).append(div);
                    });                    
                });


                $("input[name='concepto'], input[name='cantidad']").hover(function(){
                    $(this).css("background-color", "#fffb19");
                    }, function(){
                    $(this).css("background-color", "#fff");
                });

                $("div[name='precio'], div[name='importe']").formatCurrency();

                sumarTotales();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}






//////////DATES
function iniciar_daterangepicker() {

    if( typeof ($.fn.daterangepicker) === 'undefined'){ return; }
    console.log('init_daterangepicker_single_call');

    $('#single_cal').daterangepicker({
      singleDatePicker: true,
      singleClasses: "picker_4"
    }, function(start, end, label) {
      console.log(start.toISOString(), end.toISOString(), label);
    });


}
function AgregarConcepto(){
var n =0;
  var table =$('#tabla tbody.con')[0];
  var row = table.insertRow(table.rows.length);
  var cell1 = row.insertCell(0);
  var cell2 = row.insertCell(1);
  var cell3 = row.insertCell(2);
  var cell4 = row.insertCell(3);
  var cell5 = row.insertCell(4);
  var cell6 = row.insertCell(5);
  var cell7 = row.insertCell(6);
  var cell8 = row.insertCell(7);
  cell1.innerHTML =table.rows.length;
  cell2.innerHTML = "<input name='cantidad' min='1' onchange='calcularImporte(this)' style='width: 90%; border: none;' type='number' value='" + 0 +"'/>";
  cell3.innerHTML= "<input name='concepto' style='width: 95%; border: none;' type='text' placeholder='Concepto'/>";
  cell4.innerHTML="<input name='precio' min='0' onchange='calcularImporte(this)' style='width: 90%; text-align: right; border: none;' type='number' value='" + n.toFixed(2) + "'/>";
  cell5.innerHTML= "<div id='importe' name='importe' data-monto='" + n + "' style='width: 90%; text-align: right;'>" + n.toFixed(2) +"</div>";
  cell6.innerHTML= "<input name='ivaRet' required min='0' onchange='calcularImporte(this)' style='width: 90%; text-align: right; border: none;' type='number' value='0.0'/>";
  cell7.innerHTML= "<div id='retencion' name='retencion' data-monto='' style='width: 90%; text-align: right;'>0.00</div>";
  cell8.innerHTML= "<button style='width: 100px;' class='btn btn-default btn-xs pull-right' onclick='showComment(this)' type='button'><i class='fa fa-comments-o'></i> Comentarios</button><br><button style='width: 100px;' class='btn btn-danger btn-xs pull-right' onclick='eliminarConcepto(this)' type='button'><i class='fa fa-trash'></i> Eliminar</button>";

$("input[name='concepto'], input[name='cantidad'], input[name='precio']").hover(function(){
    $(this).css("background-color", "#fffb19");
    }, function(){
    $(this).css("background-color", "#fff");
});

$("div[name='importe'], #lblDescuento").formatCurrency();

sumarTotales();
}

function editarPO(){
    if(validacion())
    {
        var URL = base_url + 'ordenes_compra/guardarPO';
        var billing_a = $('#opBilling').val();
        var shipping_a = $('#opShipping').val();
        var impuesto_nombre = $('#lblNombreImpuesto').html();
        var nombreProveedor = NOMBRE_PROV;
        var nombreContacto = NOMBRE_CONTACTO;
        var rma = $('#txtRMA').val().trim();
        var CONCEPTOS = [];
        var C=[];

        var recurso = $("#opRecurso").val();
        var fecha_cobro = $('#single_cal').val();

        var rows = $('#tabla tbody.con tr'); var no = 1;
        $.each(rows, function(i, elem){
            var q = $(elem).find("input[name='cantidad']").val();
            var con = $(elem).find("input[name='concepto']").val();
            var imp = $(elem).find("div[name='importe']").data('monto');
            var ivaret = $(elem).find("input[name='ivaRet']").val()+"%";
            var ret = $(elem).find("div[name='retencion']").data('monto');
            //alert(ivaret);
            if(ivaret == '0.0%'){
                var concepto = [q, con, imp, $(elem).next().find('textarea').val()];
            }
            else{
            var concepto = [q, con, imp, ivaret, ret, $(elem).next().find('textarea').val()];    
            }
            var concep={};
            concep=[concepto[1]+' \ " : \" '+concepto[2]];            
            C.push(concep);
            CONCEPTOS.push(concepto);

            no++;
        });

       $.ajax({
            type: "POST",
            url: URL,
            data: { id : ID, billing_a : billing_a, shipping_a : shipping_a, contacto : CONTACTO,
                conceptos : JSON.stringify(CONCEPTOS), concep :JSON.stringify(C), metodo_pago : METODOPAGO, rma : rma,
                subtotal : SUBTOTAL, descuento : DESCUENTO, impuesto : IMPUESTO, impuesto_nombre : impuesto_nombre,
                impuesto_factor : FACTOR_IMPUESTO, total : TOTAL, retencion : retencion, ivaRet : IvaRet, nombre_proveedor : nombreProveedor, nombre_contacto : nombreContacto,
                recurso : recurso, fecha_cobro : fecha_cobro
             },
            success: function(result) {
                if(result)
                {

                   window.location.href = base_url + 'ordenes_compra/ver_po/' + ID               
                }
            },
            error: function(data){
                new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
                console.log(data);
            },
        });
               
    }
}

function setImpuestoUSA(){
    USA = $('#txtUSA').val();
    $('#lblNombreImpuesto').html('USA ('+USA+'%)');
    USA=USA/100;
    $('#mdlImpuestos').modal('hide');
 

    sumarTotales();
}