function load(){
    reporteIT();
    reporteAutos();
    reporteEdificio();
    reporteCafeteria();
}

function reporteIT(){
    $.ajax({
        type: "POST",
        url: base_url + 'tickets/ajax_getTicktesIT',
        data: { },
        success: function(result) {
            var rs = JSON.parse(result);

            rs.Total; 
          //  alert(rs.Total);

            $('#pbAbiertos')[0].dataset.transitiongoal = (rs.Abiertos / rs.Total) * 100;
            //$('#pbCerrado')[0].dataset.transitiongoal = (rs.Cerrado / rs.Total) * 100;*/
            $('#pbCurso')[0].dataset.transitiongoal = (rs.Cotizando / rs.Total) * 100;
	    $('#pbRevision')[0].dataset.transitiongoal = (rs.Revision / rs.Total) * 100;
            $('#pbDetenido')[0].dataset.transitiongoal = (rs.Detenido / rs.Total) * 100;
            $('#pbSolucionado')[0].dataset.transitiongoal = (rs.Solucionado / rs.Total) * 100;

            $('#lblAbiertos').text(rs.Abiertos + " / " + rs.Total + (rs.ultAbiertos == null ? "" : " (" + moment(rs.ultAbiertos).format('DD-MMM-YYYY') + ")"));
/*            $('#lblCancelado').text(rs.Cancelado + " / " + rs.Total + (rs.ultCancelado == null ? "" : " (" + moment(rs.ultCancelado).format('DD-MMM-YYYY') + ")"));
            $('#lblCerrado').text(rs.Cerrado + " / " + rs.Total + (rs.ultCerrado == null ? "" : " (" + moment(rs.ultCerrado).format('DD-MMM-YYYY') + ")"));*/
            $('#lblCurso').text(rs.Curso + " / " + rs.Total + (rs.ultCurso == null ? "" : " (" + moment(rs.ultCurso).format('DD-MMM-YYYY') + ")"));
            $('#lblRevision').text(rs.Revision + " / " + rs.Total + (rs.ultRevision == null ? "" : " (" + moment(rs.ultRevision).format('DD-MMM-YYYY') + ")"));
            $('#lblDetenido').text(rs.Detenido + " / " + rs.Total + (rs.ultDetenido == null ? "" : " (" + moment(rs.ultDetenido).format('DD-MMM-YYYY') + ")"));
            $('#lblSolucionado').text(rs.Solucionado + " / " + rs.Total + (rs.ultSolucionado == null ? "" : " (" + moment(rs.ultSolucionado).format('DD-MMM-YYYY') + ")"));

            $('.progress .progress-bar').progressbar();
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
}

function reporteAutos(){
  $.ajax({
      type: "POST",
      url: base_url + 'tickets/ajax_getTicktesAutos',
      data: { },
      success: function(result) {
          var rs = JSON.parse(result);

          rs.Total; 
          $('#pbAbiertosA')[0].dataset.transitiongoal = (rs.Abiertos / rs.Total) * 100;
            $('#pbCursoA')[0].dataset.transitiongoal = (rs.Cotizando / rs.Total) * 100;
            $('#pbDetenidoA')[0].dataset.transitiongoal = (rs.Cancelado / rs.Total) * 100;
            $('#pbSolucionadoA')[0].dataset.transitiongoal = (rs.Detenido / rs.Total) * 100;

            $('#lblAbiertosA').text(rs.Abiertos + " / " + rs.Total + (rs.ultAbiertos == null ? "" : " (" + moment(rs.ultAbiertos).format('DD-MMM-YYYY') + ")"));
            $('#lblCursoA').text(rs.Curso + " / " + rs.Total + (rs.ultCurso == null ? "" : " (" + moment(rs.ultCurso).format('DD-MMM-YYYY') + ")"));
            $('#lblDetenidoA').text(rs.Detenido + " / " + rs.Total + (rs.ultDetenido == null ? "" : " (" + moment(rs.ultDetenido).format('DD-MMM-YYYY') + ")"));
            $('#lblSolucionadoA').text(rs.Solucionado + " / " + rs.Total + (rs.ultSolucionado == null ? "" : " (" + moment(rs.ultSolucionado).format('DD-MMM-YYYY') + ")"));

          $('.progress .progress-bar').progressbar();
        },
      error: function(data){
          new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
          console.log(data);
      },
    });
}

function reporteEdificio(){
  $.ajax({
      type: "POST",
      url: base_url + 'tickets/ajax_getTicktesEdificio',
      data: { },
      success: function(result) {
          var rs = JSON.parse(result);

           rs.Total; 
            $('#pbAbiertosE')[0].dataset.transitiongoal = (rs.Abiertos / rs.Total) * 100;
            $('#pbCursoE')[0].dataset.transitiongoal = (rs.Curso / rs.Total) * 100;
            $('#pbDetenidoE')[0].dataset.transitiongoal = (rs.Detenido / rs.Total) * 100;
            $('#pbSolucionadoE')[0].dataset.transitiongoal = (rs.Solucionado / rs.Total) * 100;

            $('#lblAbiertosE').text(rs.Abiertos + " / " + rs.Total + (rs.ultAbiertos == null ? "" : " (" + moment(rs.ultAbiertos).format('DD-MMM-YYYY') + ")"));
            $('#lblCursoE').text(rs.Curso + " / " + rs.Total + (rs.ultCurso == null ? "" : " (" + moment(rs.ultCurso).format('DD-MMM-YYYY') + ")"));
            $('#lblDetenidoE').text(rs.Detenido + " / " + rs.Total + (rs.ultDetenido == null ? "" : " (" + moment(rs.ultDetenido).format('DD-MMM-YYYY') + ")"));
            $('#lblSolucionadoE').text(rs.Solucionado + " / " + rs.Total + (rs.ultSolucionado == null ? "" : " (" + moment(rs.ultSolucionado).format('DD-MMM-YYYY') + ")"));

          $('.progress .progress-bar').progressbar();
        },
      error: function(data){
          new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
          console.log(data);
      },
    });
}
function reporteCafeteria(){
  $.ajax({
      type: "POST",
      url: base_url + 'tickets/ajax_getTicktesCafeteria',
      data: { },
      success: function(result) {
          var rs = JSON.parse(result);

           rs.Total; 
            $('#pbAbiertosC')[0].dataset.transitiongoal = (rs.Abiertos / rs.Total) * 100;
            $('#pbCursoC')[0].dataset.transitiongoal = (rs.Curso / rs.Total) * 100;
            $('#pbDetenidoC')[0].dataset.transitiongoal = (rs.Detenido / rs.Total) * 100;
            $('#pbSolucionadoC')[0].dataset.transitiongoal = (rs.Solucionado / rs.Total) * 100;

            $('#lblAbiertosC').text(rs.Abiertos + " / " + rs.Total + (rs.ultAbiertos == null ? "" : " (" + moment(rs.ultAbiertos).format('DD-MMM-YYYY') + ")"));
            $('#lblCursoC').text(rs.Curso + " / " + rs.Total + (rs.ultCurso == null ? "" : " (" + moment(rs.ultCurso).format('DD-MMM-YYYY') + ")"));
            $('#lblDetenidoC').text(rs.Detenido + " / " + rs.Total + (rs.ultDetenido == null ? "" : " (" + moment(rs.ultDetenido).format('DD-MMM-YYYY') + ")"));
            $('#lblSolucionadoC').text(rs.Solucionado + " / " + rs.Total + (rs.ultSolucionado == null ? "" : " (" + moment(rs.ultSolucionado).format('DD-MMM-YYYY') + ")"));

          $('.progress .progress-bar').progressbar();
        },
      error: function(data){
          new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
          console.log(data);
      },
    });
}
