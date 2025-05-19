var CURRENT_PR;


function buscar(){
    $('#lblCount').text("");
    var URL = base_url + "compras/ajax_getPRs";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBusqueda").val();
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();

    var prioridad = [];

    if($('#cbNormal').is(':checked'))
    {
        prioridad.push('NORMAL');
    }
    if($('#cbUrgente').is(':checked'))
    {
        prioridad.push('URGENTE');
    }
    if($('#cbInfoUrgente').is(':checked'))
    {
        prioridad.push('INFO URGENTE');
    }
    var tipo = [];
    if($('#cbProducto').is(':checked'))
    {
        tipo.push('PRODUCTO');
    }
    if($('#cbServicio').is(':checked'))
    {
        tipo.push('SERVICIO');
    }

    var estatus = $('#opEstatus').val();
    
    var misPRs = $('#cbMisPRs').is(':checked') ? "0" : "1";
    var stock = $('#cbStock').is(':checked') ? "1" : "0"
    var archivo = $('#cbArchivo').is(':checked') ? "1" : "0";
    //alert(stock);

    $.ajax({
        type: "POST",
        url: URL,
        data: { prioridad: JSON.stringify(prioridad),tipo: JSON.stringify(tipo), estatus: estatus, texto : texto, parametro : parametro, misprs : misPRs, fecha1 : fecha1, fecha2 : fecha2, stock : stock, archivo : archivo },
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);

                $('#lblCount').text(rs.length + (rs.length == 1 ? " Pr" : " Pr's"));
                $.each(rs, function(i, elem){

                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell().innerHTML = "<button type='button' onclick='getPR(this)' value='" + elem.id +"' class='btn btn-default btn-xs'>" + elem.id + "</button>";
                    ren.insertCell().innerHTML = moment(elem.fecha).format('YYYY-MM-DD');
                    ren.insertCell().innerHTML = elem.User;
                    ren.insertCell().innerHTML = elem.prioridad;
                    ren.insertCell().innerHTML = elem.tipo;
                    ren.insertCell().innerHTML = elem.subtipo;
                    ren.insertCell().innerHTML = elem.cantidad;
                    ren.insertCell().innerHTML = elem.descripcion;
                    
                    var btn = "";
                    switch (elem.estatus)
                    {
                        case 'PENDIENTE':
                        case 'EN SELECCION':
                        case 'POR RECIBIR':
                        btn = 'btn btn-warning btn-xs';
                        break;

                        case 'APROBADO':
                        case 'PO AUTORIZADA':
                        btn = 'btn btn-success btn-xs';
                        break;

                        case 'CANCELADO':
                        btn = 'btn btn-default btn-xs';
                        break;

                        case 'RECHAZADO':
                        btn = 'btn btn-danger btn-xs';
                        break;

                        case 'EN PO':
                        btn = 'btn btn-primary btn-xs';
                        break;

                        case 'PROCESADO':
                        case 'CERRADO':
                        btn = 'btn btn-default btn-xs';
                        break;
                    }

                    if(elem.stock==1){
                        //alert(elem.stock);
                        var link = base_url + "compras/ver_pr/"+ elem.id;
                        ren.insertCell().innerHTML = "<a target='_blank' href='" + link +"'><button class='btn btn-default btn-xs'><i class='fa fa-shopping-cart'></i> STOCK</a>";

                    }else if(elem.estatus == 'EN PO' | elem.estatus == 'PO AUTORIZADA' | elem.estatus == 'CERRADO' | elem.estatus == 'POR RECIBIR')
                    {
                        var link = base_url + "ordenes_compra/ver_po/"+ elem.POActual;
                        ren.insertCell().innerHTML = "<a target='_blank' href='" + link +"'><button class='btn btn-default btn-xs'><i class='fa fa-shopping-cart'></i> PO: "+ elem.POActual +"</a>";
                    }
                    else{
                        ren.insertCell().innerHTML = "N/A";
                    }

                    var cell8 = ren.insertCell();
                    cell8.innerHTML = "<a target='_blank' href='" + base_url + "compras/ver_pr/"+ elem.id +"' class='" + btn + "'>" + elem.estatus + "</a>";

                    
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

function getPR(btn){
    var URL = base_url + 'compras/ajax_getPR';
    var idPR = $(btn).val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { id: idPR },
        success: function(result) {
            if(result)
            {
                $('#tblDetalle tr').remove();
                var tab = $('#tblDetalle tbody')[0];

                var rs = JSON.parse(result);
                var att = JSON.parse(rs['atributos']);
                
                
                $(".mdlTitulo").text("PR # " + rs.id);
                $("#lblQR").html('QR: # ' + rs.qr);
                $("#lblDestino").html('Destino: <small>' + rs.destino + '</small>');
                $("#lblRequisitor").text(rs.User);
                $("#lblFecha").text(rs.fecha);
                $("#lblCantidad").text(rs.cantidad);
                $("#lblDescripcion").text(rs.descripcion);

                var ren = tab.insertRow(tab.rows.length);
                var cell0 = ren.insertCell(0);
                cell0.innerHTML = "Tipo";
                cell0.style.width = "40%";
                ren.insertCell(1).innerHTML = rs.tipo;

                var ren = tab.insertRow(tab.rows.length);
                ren.insertCell(0).innerHTML = "Subtipo";
                ren.insertCell(1).innerHTML = rs.subtipo;

                var ren = tab.insertRow(tab.rows.length);
                ren.insertCell(0).innerHTML = "Cantidad";
                ren.insertCell(1).innerHTML = rs.cantidad;

                var ren = tab.insertRow(tab.rows.length);
                ren.insertCell(0).innerHTML = "Unidad de Medida";
                ren.insertCell(1).innerHTML = rs.unidad;

                $.each(att, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = i[0].toUpperCase() + i.slice(1);
                    ren.insertCell(1).innerHTML = elem;
                });

                if(rs.comentarios)
                {
                    $("#divCommentQR").show();
                    $("#lblCommentQR").text(rs.comentarios);
                }
                else{
                    $("#divCommentQR").hide();
                    $("#lblCommentQR").text("");
                }
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
      $("#mdlDetalle").modal();
}

function selectPropuesta(btn){
    CURRENT_QR = $(btn).val();
    $('#txtQtySolicitud').val("0");

    var URL = base_url + "compras/ajax_getPR";
    $('#tblProveedores tbody tr').remove();

    $.ajax({
        type: "POST",
        url: URL,
        data: { idQR: CURRENT_QR },
        success: function(result) {
            if(result)
            {
                
                var tab = $('#tblProveedores tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem) {
                    if(elem.nominado == 1)
                    {
                        var ren = tab.insertRow(tab.rows.length);
                        var check = elem.seleccionado == '1' ? 'checked' : '';

                        ren.insertCell(0).innerHTML = "<center><input type='radio' name='rbSelect' class='flat' value='" + elem.idQP + "' " + check + " ></center>";

                        var cell2 = ren.insertCell(1);
                        cell2.innerHTML = elem.monto;
                        $(cell2).formatCurrency();
                        cell2.innerHTML += " " + elem.moneda;

                        var diasH = elem.dias_habiles == 1 ? " Habiles" : " Naturales";
                        var cell3 = ren.insertCell(2);
                        cell3.innerHTML = elem.tiempo_entrega + " Dias" + diasH;

                        ren.insertCell(3).innerHTML = moment(elem.vencimiento).format("DD-MM-YYYY");

                        ren.insertCell(4).innerHTML = elem.nombre;

                        //var arc = "<a target='_blank' href='" + base_url + 'compras/getEvidencia/' + elem.idQP + "'><img height='25px' src='" + file_image(elem.nombre_archivo) + "'></a>";
                        ren.insertCell(5).innerHTML = "<a target='_blank' href='" + base_url + 'compras/getEvidencia/' + elem.idQP + "'><img height='25px' src='" + file_image(elem.nombre_archivo) + "'></a>";
                    }
                    
                    $('#txtQtySolicitud').val(elem.cantidad);
                });

                $('input.flat').iCheck({
                    checkboxClass: 'icheckbox_flat-green',
                    radioClass: 'iradio_flat-green'
                });
            }
        },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });

    $('#mdlCotizaciones').modal();

}

function generarPR(){
    var URL = base_url + "compras/ajax_generarPR";
    
    var qty = $('#txtQtySolicitud').val();
    var qr_prov = $("input[name='rbSelect']:checked").val();

    if(qr_prov == undefined)
    {
        alert('Seleccione un proveedor');
        return;
    }
    if(qty <= 0)
    {
        alert('La cantidad debe ser mayor a 0');
        return;
    }

    $.ajax({
        type: "POST",
        url: URL,
        data: { qr : CURRENT_QR, qr_prov : qr_prov, qty : qty },
        success: function(result) {
            if(result)
            {
                new PNotify({ title: 'Nueva PR', text: 'Se ha generado PR #' + result, type: 'success', styling: 'bootstrap3' });
                $('#mdlCotizaciones').modal('hide');
            }
        },
    });
}

function file_image(archivo) {
    if(archivo)
    {
        var ext = archivo.split('.');
        ext = ext[ext.length - 1];
        ext = ext.toLowerCase();

        switch (ext)
        {
            case "avi":
                return base_url + "template/images/files/avi.png";
            case "css":
                return base_url + "template/images/files/css.png";
            case "csv":
                return base_url + "template/images/files/csv.png";
            case "dbf":
                return base_url + "template/images/files/dbf.png";
            case "doc":
                return base_url + "template/images/files/doc.png";
            case "docx":
                return base_url + "template/images/files/doc.png";
            case "dwg":
                return base_url + "template/images/files/dwg.png";
            case "exe":
                return base_url + "template/images/files/exe.png";
            case "html":
                return base_url + "template/images/files/html.png";
            case "iso":
                return base_url + "template/images/files/iso.png";
            case "js":
                return base_url + "template/images/files/js.png";
            case "jpeg":
                return base_url + "template/images/files/jpg.png";
            case "jpg":
                return base_url + "template/images/files/jpg.png";
            case "json":
                return base_url + "template/images/files/json.png";
            case "mp3":
                return base_url + "template/images/files/mp3.png";
            case "mp4":
                return base_url + "template/images/files/mp4.png";
            case "pdf":
                return base_url + "template/images/files/pdf.png";
            case "png":
                return base_url + "template/images/files/png.png";
            case "ppt":
                return base_url + "template/images/files/ppt.png";
            case "pptx":
                return base_url + "template/images/files/ppt.png";
            case "ppsx":
                return base_url + "template/images/files/ppt.png";
            case "psd":
                return base_url + "template/images/files/psd.png";
            case "rtf":
                return base_url + "template/images/files/rtf.png";
            case "search":
                return base_url + "template/images/files/search.png";
            case "rar":
                return base_url + "template/images/files/rar.png";
            case "svg":
                return base_url + "template/images/files/svg.png";
            case "txt":
                return base_url + "template/images/files/txt.png";
            case "xls":
                return base_url + "template/images/files/xls.png";
            case "xlsx":
                return base_url + "template/images/files/xls.png";
            case "xml":
                return base_url + "template/images/files/xml.png";
            case "zip":
                return base_url + "template/images/files/zip.png";
            default:
                return base_url + "template/images/files/file.png";
        }
    }
}

function procesarPR(btn)
{
    var URL = base_url + "compras/ajax_setEstatusPR";
    var id = $(btn).val();
    if(confirm("¿Desea Procesar PR#" + id))
    {
        $.ajax({
            type: "POST",
            url: URL,
            data: { id : id, estatus : 'PROCESADO' },
            success: function(result) {
                if(result)
                {
                    new PNotify({ title: 'PR Procesada', text: 'Se ha procesado PR #' + id, type: 'success', styling: 'bootstrap3' });
                    buscar();
                }
            },
        });
    }
}