function load(){
    reporte();
//  cotizaciones();
 /* creadas();
  Rechazadas();
  Pendiente();
  Revision();
  Autorizadas();
  Confirmadas();
  Aprobacion();
  AprobacionTotal();
  AprobacionParcial();*/
  
  
}

var r="";
var f;
  function cotizaciones(){
    var idSub = $('#sub').val();    
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboard',
        async:false,
        data: {'idSub':idSub},
        success: function(result) {
            var total = JSON.parse(result);
           //  alert(total);
            // return total;
            if(result){
                r=total;


            }else{
                r=0;
            }
            
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
                return r;

   //alert(r);

}

function creadas(){
    cotizaciones();

    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardCreadas',
        data: {'idSub':idSub},
        success: function(result){

            if(result){
		var creada = JSON.parse(result);            
	        $.each(creada, function(i, elem){
                f=moment(elem.fecha).format('MM-DD-YYYY');
	          });
                
                $('#pbCreadas')[0].dataset.transitiongoal = (creada.length / r) * 100;
                $('#lblCreadas').text(creada.length + " / " + r+" ("+f+")");
            }else{
                $('#lblCreadas').text("0" + " / " + r);
            }
            
           
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function Rechazadas(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardRechazadas',
        data: {'idSub':idSub},
        success: function(result) { 
           
           if(result){
	var rechazada = JSON.parse(result);
             $.each(rechazada, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });

           $('#pbRechazadas')[0].dataset.transitiongoal = (rechazada.length / r) * 100;
            $('#lblRechazadas').text(rechazada.length + " / " + r+" ("+f+")");

           }else{
            $('#lblRechazadas').text("0" + " / " + r);

           }
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function Pendiente(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardPendientes',
        data: {'idSub':idSub},
        success: function(result) {
	
	if(result){
	var pendiente = JSON.parse(result);
            $.each(pendiente, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });           
           $('#pbPendiente')[0].dataset.transitiongoal = (pendiente.length / r) * 100;
           $('#lblPendiente').text(pendiente.length + " / " + r+" ("+f+")");


            }else{
           $('#lblPendiente').text("0" + " / " + r);
           }
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function Revision(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardRevision',
        data: {'idSub':idSub},
        success: function(result) {

if(result){
	var rev = JSON.parse(result);

            $.each(rev, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
           
           $('#pbEnRevision')[0].dataset.transitiongoal = (rev.length / r) * 100;
           $('#lblEnRevision').text(rev.length + " / " + r +" ("+f+")");    
        }else{
            $('#lblEnRevision').text("0" + " / " + r); 
        }
            
           
           $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function Autorizadas(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardAutorizadas',
        data: {'idSub':idSub},
        success: function(result) {

if(result){
	var aut = JSON.parse(result);
            $.each(aut, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
           

           $('#pbAprobadas')[0].dataset.transitiongoal = (aut.length / r) * 100;
           $('#lblAprobadas').text(aut.length + " / " + r+" ("+f+")");   
          } else{
            $('#lblAprobadas').text("0" + " / " + r);  
          } 
           
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function Confirmadas(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardConfirmadas',
        data: {'idSub':idSub},
        success: function(result) {

if(result){
	var con = JSON.parse(result);

            $.each(con, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
          
           $('#pbConfirmadas')[0].dataset.transitiongoal = (con.length / r) * 100;
           $('#lblConfirmadas').text(con.length + " / " + r+" ("+f+")"); 
           }else{
             $('#lblConfirmadas').text("0" + " / " + r);

           }
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function Aprobacion(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardAprobacion',
        data: {'idSub':idSub},
        success: function(result) {

if(result){
	var apr = JSON.parse(result);
              $.each(apr, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
           
           $('#pbEnAutorizacion')[0].dataset.transitiongoal = (apr.length / r) * 100;
           $('#lblEnAutorizacion').text(apr.length + " / " + r+" ("+f+")");
         }  else{
            $('#lblEnAutorizacion').text("0"+ " / " + r);
         }
           
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function AprobacionParcial(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardApParcial',
        data: {'idSub':idSub},
        success: function(result) {

if(result){
            var apr = JSON.parse(result);

             $.each(apr, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
           
           $('#pbEnAutorizacionAP')[0].dataset.transitiongoal = (apr.length / r) * 100;
           $('#lblEnAutorizacionAP').text(apr.length + " / " + r+" ("+f+")");
         }  else{
            $('#lblEnAutorizacionAP').text("0"+ " / " + r);
         }
           
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}
function AprobacionTotal(){
    cotizaciones();
    var idSub = $('#sub').val();
    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getDashboardApTotal',
        data: {'idSub':idSub},
        success: function(result) {
if(result){         
   var apr = JSON.parse(result);
            $.each(apr, function(i, elem){
           f=moment(elem.fecha).format('MM-DD-YYYY');
             });
           
         
           
           $('#pbEnAutorizacionAT')[0].dataset.transitiongoal = (apr.length / r) * 100;
           $('#lblEnAutorizacionAT').text(apr.length + " / " + r+" ("+f+")");
         }  else{
            $('#lblEnAutorizacionAT').text("0"+ " / " + r);
         }
           
           
  
            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function cargar() {
    $('#pbCreadas')[0].dataset.transitiongoal = 0;
    $('#lblCreadas').text("0" + " / " + "0");

    $('#pbRechazadas')[0].dataset.transitiongoal = 0;
    $('#lblRechazadas').text("0" + " / " + "0");

    $('#pbPendiente')[0].dataset.transitiongoal = 0;
    $('#lblPendiente').text("0" + " / " + "0");

    $('#pbEnRevision')[0].dataset.transitiongoal = 0;
    $('#lblEnRevision').text("0" + " / " + "0");

    $('#pbAprobadas')[0].dataset.transitiongoal = 0;
    $('#lblAprobadas').text("0" + " / " + "0");

    $('#pbConfirmadas')[0].dataset.transitiongoal = 0;
    $('#lblConfirmadas').text("0" + " / " + "0");

    $('#pbEnAutorizacion')[0].dataset.transitiongoal = 0;
    $('#lblEnAutorizacion').text("0" + " / " + "0");    

    $('#pbEnAutorizacionAT')[0].dataset.transitiongoal = 0;
    $('#lblEnAutorizacionAT').text("0" + " / " + "0");

    $('#pbEnAutorizacionAP')[0].dataset.transitiongoal = 0;
    $('#lblEnAutorizacionAP').text("0" + " / " + "0");

  /*cotizaciones();
  creadas();
  Rechazadas();
  Pendiente();
  Revision();
  Autorizadas();
  Confirmadas();
  Aprobacion();
  AprobacionTotal();
  AprobacionParcial();*/
    
}
function reporte(){
var asignado = $('#opAsignado').val();
var asignadoTexto = $('#opAsignado option:selected').text();

var creadas = document.getElementById('link_creadas'); 
    creadas.href = "index/creada/" + encodeURIComponent(asignadoTexto);

var rechazadas = document.getElementById('link_rechazadas'); 
    rechazadas.href = "index/rechazada/" + encodeURIComponent(asignadoTexto);

var pendiente = document.getElementById('link_pendiente'); 
    pendiente.href = "index/pendiente/" + encodeURIComponent(asignadoTexto);

var revision = document.getElementById('link_revision'); 
    revision.href = "index/revision/" + encodeURIComponent(asignadoTexto);

var autorizadas = document.getElementById('link_autorizadas'); 
    autorizadas.href = "index/autorizada/" + encodeURIComponent(asignadoTexto);

var confirmada = document.getElementById('link_confirmada'); 
    confirmada.href = "index/confirmada/" + encodeURIComponent(asignadoTexto);

var aprobacion = document.getElementById('link_aprobacion'); 
    aprobacion.href = "index/aprobacion/" + encodeURIComponent(asignadoTexto);

var aptotal = document.getElementById('link_aptotal'); 
    aptotal.href = "index/aprobaciont/" + encodeURIComponent(asignadoTexto);

var parcial = document.getElementById('link_parcial'); 
    parcial.href = "index/aprobacionp/" + encodeURIComponent(asignadoTexto);


    $.ajax({
        type: "POST",
        url: base_url + 'cotizaciones/ajax_getReporteCotizaciones',
        data: { asignado : asignado },
        success: function(result) {
            var rs = JSON.parse(result);

            //rs.Total = parseInt(rs.Abiertos) + parseInt(rs.Rechazados) + parseInt(rs.Cotizando);

            $('#pbCreadas')[0].dataset.transitiongoal = (rs.TotalCreadas / rs.Total) * 100;
            $('#pbRechazadas')[0].dataset.transitiongoal = (rs.TotalRechazadas / rs.Total) * 100;
            $('#pbPendiente')[0].dataset.transitiongoal = (rs.TotalPendienteAutorizacion / rs.Total) * 100;
            $('#pbEnRevision')[0].dataset.transitiongoal = (rs.TotalEnRevision / rs.Total) * 100;
            $('#pbAprobadas')[0].dataset.transitiongoal = (rs.TotalAutorizadas / rs.Total) * 100;
            $('#pbConfirmadas')[0].dataset.transitiongoal = (rs.TotalConfirmada / rs.Total) * 100;
            $('#pbEnAutorizacion')[0].dataset.transitiongoal = (rs.TotalEnAprobacion / rs.Total) * 100;
            $('#pbEnAutorizacionAT')[0].dataset.transitiongoal = (rs.TotalAprobadoTotal / rs.Total) * 100;
            $('#pbEnAutorizacionAP')[0].dataset.transitiongoal = (rs.TotalAprobadoParcial / rs.Total) * 100;

            $('#lblCreadas').text(rs.TotalCreadas + " / " + rs.Total + (rs.UltFechaCreadas == null ? "" : " (" + moment(rs.UltFechaCreadas).format('DD-MMM-YYYY') + ")"));
            $('#lblRechazadas').text(rs.TotalRechazadas + " / " + rs.Total + (rs.UltFechaRechazadas == null ? "" : " (" + moment(rs.UltFechaRechazadas).format('DD-MMM-YYYY') + ")"));
            $('#lblPendiente').text(rs.TotalPendienteAutorizacion + " / " + rs.Total + (rs.UltFechaPendienteAutorizacion == null ? "" : " (" + moment(rs.UltFechaPendienteAutorizacion).format('DD-MMM-YYYY') + ")"));
            $('#lblEnRevision').text(rs.TotalEnRevision + " / " + rs.Total + (rs.UltFechaEnRevision == null ? "" : " (" + moment(rs.UltFechaEnRevision).format('DD-MMM-YYYY') + ")"));
            $('#lblAprobadas').text(rs.TotalAutorizadas + " / " + rs.Total + (rs.UltFechaAutorizadas == null ? "" : " (" + moment(rs.UltFechaAutorizadas).format('DD-MMM-YYYY') + ")"));
            $('#lblConfirmadas').text(rs.TotalConfirmada + " / " + rs.Total + (rs.TotalConfirmada == null ? "" : " (" + moment(rs.TotalConfirmada).format('DD-MMM-YYYY') + ")"));
            $('#lblEnAutorizacion').text(rs.TotalEnAprobacion + " / " + rs.Total + (rs.UltFechaEnAprobacion == null ? "" : " (" + moment(rs.UltFechaConfirmada).format('DD-MMM-YYYY') + ")"));
            $('#lblEnAutorizacionAT').text(rs.TotalAprobadoTotal + " / " + rs.Total + (rs.UltFechaAprobadoTotal == null ? "" : " (" + moment(rs.UltFechaAprobadoTotal).format('DD-MMM-YYYY') + ")"));
            $('#lblEnAutorizacionAP').text(rs.TotalAprobadoParcial + " / " + rs.Total + (rs.UltFechaAprobadoParcial == null ? "" : " (" + moment(rs.UltFechaAprobadoParcial).format('DD-MMM-YYYY') + ")"));

            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}