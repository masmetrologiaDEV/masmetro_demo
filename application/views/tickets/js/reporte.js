function buscar_IT(){
    $('#lblCount').text("");
    var URL = base_url + "tickets_IT/reporte_it";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBusqueda").val();
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    var estatus = $('#opEstatus').val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { estatus: estatus, texto : texto, parametro : parametro, fecha1 : fecha1, fecha2 : fecha2},
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $('#lblCount').text(rs.length + (rs.length == 1 ? " Ticket" : " Tickets's"));
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = "<a href="+base_url+"tickets_IT/ver/"+ elem.id + " target='_blank'><button type='button'class='btn btn-default btn-xs'>"+elem.id+"</button></a>";
                    ren.insertCell(1).innerHTML = moment(elem.fecha).format('YYYY-MM-DD');
                    ren.insertCell(2).innerHTML = elem.User;
                    ren.insertCell(3).innerHTML = elem.tipo;
                    ren.insertCell(4).innerHTML = elem.titulo;
                    //ren.insertCell(5).innerHTML = elem.estatus;
                    var btn = "";
                    switch (elem.estatus)
                    {
                        case 'ABIERTO':
                        btn = 'btn btn-primary btn-xs';
                        break;

                        case 'EN CURSO':
                        case 'COMPRA APROBADA':
                        btn = 'btn btn-success btn-xs';
                        break;

                        case 'DETENIDO':
  			case 'EN REVISION':
                        btn = 'btn btn-warning btn-xs';
                        break;

                        case 'CANCELADO':
                        btn = 'btn btn-default btn-xs';
                        break;

                        case 'CERRADO':
                        btn = 'btn btn-dark btn-xs';
                        break;

                        case 'SOLUCIONADO':
                       
                        btn = 'btn btn-danger btn-xs';
                        break;
                    }
                    var cell9 = ren.insertCell(5);
                    //cell9.innerHTML = "<a href='" + base_url + "compras/ver_qr/"+ elem.id +"' class='" + btn + "'>" + elem.estatus + "</a>";
                    cell9.innerHTML = "<button type='button' class='" + btn + "'>" + elem.estatus + "</button";
                    /*ren.insertCell(6).innerHTML = elem.cantidad;
                    ren.insertCell(7).innerHTML = elem.descripcion;

                    if(elem.estatus == "LIBERADO" && ((elem.destino == "VENTA" && V == "1") | (elem.destino == "CONSUMO INTERNO" && I == "1") | RV == "1") ){
                        ren.insertCell(8).innerHTML = "<button type='button' onclick='selectPropuesta(this)' value='" + elem.id + "' class='btn btn-default btn-xs'><i class='fa fa-file-text-o'></i></button>";
                    } else {
                        ren.insertCell(8).innerHTML = "N/A";
                    }
                    
                    
                    var cell9 = ren.insertCell(9);
                    //cell9.innerHTML = "<a href='" + base_url + "compras/ver_qr/"+ elem.id +"' class='" + btn + "'>" + elem.estatus + "</a>";
                    cell9.innerHTML = boton(elem.estatus, elem.id, btn, elem.usuario == usuario);
                    
                    if(btn == "btn btn-danger btn-xs" && elem.usuario == usuario)
                    {
                        //cell9.innerHTML += "<a href='" + base_url + "compras/editar_qr/" + elem.id + "' class='btn btn-primary btn-xs'><i class='fa fa-pencil'></i> Editar</a>";
                    }*/
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
function buscar_AT(){
    $('#lblCount').text("");
    var URL = base_url + "tickets_at/reporte_at";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBusqueda").val();
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    var estatus = $('#opEstatus').val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { estatus: estatus, texto : texto, parametro : parametro, fecha1 : fecha1, fecha2 : fecha2},
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $('#lblCount').text(rs.length + (rs.length == 1 ? " Ticket" : " Tickets's"));
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = "<a href="+base_url+"tickets_AT/ver/"+ elem.id + " target='_blank'><button type='button'class='btn btn-default btn-xs'>"+elem.id+"</button></a>";
                    ren.insertCell(1).innerHTML = moment(elem.fecha).format('YYYY-MM-DD');
                    ren.insertCell(2).innerHTML = elem.User;
                    ren.insertCell(3).innerHTML = elem.tipo;
                    ren.insertCell(4).innerHTML = elem.titulo;
                    //ren.insertCell(5).innerHTML = elem.estatus;
                    var btn = "";
                    switch (elem.estatus)
                    {
                        case 'ABIERTO':
                        btn = 'btn btn-primary btn-xs';
                        break;

                        case 'EN CURSO':
                        case 'COMPRA APROBADA':
                        btn = 'btn btn-success btn-xs';
                        break;

                        case 'DETENIDO':
                        btn = 'btn btn-warning btn-xs';
                        break;

                        case 'CANCELADO':
                        btn = 'btn btn-default btn-xs';
                        break;

                        case 'CERRADO':
                        btn = 'btn btn-dark btn-xs';
                        break;

                        case 'SOLUCIONADO':
                       
                        btn = 'btn btn-danger btn-xs';
                        break;
                    }
                    var cell9 = ren.insertCell(5);
                    cell9.innerHTML = "<button type='button' class='" + btn + "'>" + elem.estatus + "</button";
                    
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

function buscar_ED(){
    $('#lblCount').text("");
    var URL = base_url + "tickets_ed/reporte_ed";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBusqueda").val();
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    var estatus = $('#opEstatus').val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { estatus: estatus, texto : texto, parametro : parametro, fecha1 : fecha1, fecha2 : fecha2},
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $('#lblCount').text(rs.length + (rs.length == 1 ? " Ticket" : " Tickets's"));
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = "<a href="+base_url+"tickets_ED/ver/"+ elem.id + " target='_blank'><button type='button'class='btn btn-default btn-xs'>"+elem.id+"</button></a>";
                    ren.insertCell(1).innerHTML = moment(elem.fecha).format('YYYY-MM-DD');
                    ren.insertCell(2).innerHTML = elem.User;
                    ren.insertCell(3).innerHTML = elem.tipo;
                    ren.insertCell(4).innerHTML = elem.titulo;
                    //ren.insertCell(5).innerHTML = elem.estatus;
                    var btn = "";
                    switch (elem.estatus)
                    {
                        case 'ABIERTO':
                        btn = 'btn btn-primary btn-xs';
                        break;

                        case 'EN CURSO':
                        case 'COMPRA APROBADA':
                        btn = 'btn btn-success btn-xs';
                        break;

                        case 'DETENIDO':
                        btn = 'btn btn-warning btn-xs';
                        break;

                        case 'CANCELADO':
                        btn = 'btn btn-default btn-xs';
                        break;

                        case 'CERRADO':
                        btn = 'btn btn-dark btn-xs';
                        break;

                        case 'SOLUCIONADO':
                       
                        btn = 'btn btn-danger btn-xs';
                        break;
                    }
                    var cell9 = ren.insertCell(5);
                    cell9.innerHTML = "<button type='button' class='" + btn + "'>" + elem.estatus + "</button";
                    
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
