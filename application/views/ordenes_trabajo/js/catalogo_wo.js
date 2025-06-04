function load(){
    buscar();
}

function buscar(){
    var URL = base_url + "ordenes_trabajo/ajax_getWo";
    $('#tabla tbody tr').remove();
   var texto = $("#txtBusqueda").val();
    var cliente = $("#txtCliente").data("id");
    var estatus = $("#opEstatus").val();
    var cerradas = $("#cbCerradasCanceladas").is(':checked') ? "1" : "0";
    var  fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    var tecnico = $("#opTec").val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { texto : texto, estatus : estatus, cliente : cliente, tecnico : tecnico, cerradas : cerradas, fecha1 : fecha1, fecha2 : fecha2 },
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell().innerHTML = elem.WorkOrder_ID;
                    ren.insertCell().innerHTML = elem.Empresa;
                    ren.insertCell().innerHTML = elem.rs;
                    ren.insertCell().innerHTML = elem.Nombre;
                    ren.insertCell().innerHTML = elem.FCreacion;
                    ren.insertCell().innerHTML = elem.FProgramado;
                    ren.insertCell().innerHTML = elem.Status_Descripcion ;
                    ren.insertCell().innerHTML = boton(elem.WorkOrder_ID , elem.Status_Descripcion, elem.WorkOrder_ID );
                });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function boton(id, estatus, user){
    var clase = "btn dropdown-toggle btn-sm";
    opcs = "<ul role='menu' class='dropdown-menu'>"
    opcs += "<li><a onclick=verRequerimiento(this)><i class='fa fa-eye'></i> Ver</a></li>"
    

     switch(estatus){
        case "PROGRAMADA":
        case "REPROGRAMADA":
            clase += " btn-primary";
            if(user == uid)
            {
                opcs += "<li><a onclick=cancelar(this)><i class='fa fa-close'></i> Cancelar</a></li></ul></div>";
            }
            break;

        
        
        case "CONCLUIDA":
            clase += " btn-success";
            break;

        case "CANCELADA":
            clase += " btn-danger";
            break;

        case "CERRADA":
            clase += " btn-dark";
            break;

      
    }
    
    
    
    var btn = "<div class='btn-group'><button type='button' data-toggle='dropdown' value=" + id + " class='" + clase + "'>" + estatus + "  <span class='caret'></span></button>";
    btn += opcs;
    return btn;


}

function verRequerimiento(a){
    var ren = $(a).closest('tr');
    var btn = $(ren).find('button');
    var id = $(btn).val();

    $.redirect( base_url + "ordenes_trabajo/ver_wo", { 'id': id },'', '_blank');
}

function editar(a){
    var ren = $(a).closest('tr');
    var btn = $(ren).find('button');
    var id = $(btn).val();

    $.redirect( base_url + "facturas/editar_solicitud", { 'id': id });
}

function cancelar(a){
    var ren = $(a).closest('tr');
    var btn = $(ren).find('button');
    var id = $(btn).val();

    if(confirm("¿Desea cancelar Solicitud?"))
    {
        var URL = base_url + 'facturas/ajax_editSolicitud';
        
        var solicitud = { 
            id : id,
            estatus : 'CANCELADO'
        };

        $.ajax({
            type: "POST",
            url: URL,
            data: { solicitud : JSON.stringify(solicitud) },
            success: function(result) {
                if(result)
                {
                    buscar();
                }
            },
            error: function(data){
                new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
                console.log(data);
            },
        });
    }
}
function buscarClientes(){
    var URL = base_url + "ordenes_trabajo/ajax_getClientesCotizaciones";
    $('#tblClientes tbody tr').remove();
    
    var texto = $("#txtBuscarCliente").val();

    $.ajax({
        type: "POST",
        url: URL,
        data: { texto : texto },
        success: function(result) {
            if(result)
            {
                var tab = $('#tblClientes tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.dataset.id = elem.Cust_ID;
                    ren.dataset.nombre = elem.nombre;
                    ren.insertCell().innerHTML = elem.nombre;
                    ren.insertCell().innerHTML = "<button onclick='seleccionarCliente(this)' type='button' class='btn btn-primary btn-xs'><i class='fa fa-check'></i> Seleccionar</button> ";
                });

                $('#mdlClientes').modal();
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}

function seleccionarCliente(btn){
    var row = $(btn).closest('tr')[0];

    $('#txtCliente').val(row.dataset.nombre);
        $('#txtCliente').data('id', row.dataset.id);
        $('#txtClienteId').val(row.dataset.id);
    $('#btnRemoverCliente').show();
    $('#mdlClientes').modal('hide');

    buscar();
}

function removerCliente(){
    $('#btnRemoverCliente').hide();
    $('#txtCliente').val("TODOS");
    $('#txtClienteId').val("");
    $('#txtCliente').data('id', 0);

    buscar();
}