$(function () {
        $("#cbreunion").click(function () {
            if ($(this).is(":checked")) {
                $("#correos").show();
            } else {
              document.getElementById('tags_1').value = '';
                $("#correos").hide();
            }
        });

        $('#tags_1').tagsInput({
        width: 'auto',
        defaultText: 'Correos',
      });
    });

var dia;
var cal;
var idEvento;

$(function () {
  cal = $("#calendar").fullCalendar({
    header: {
      left: "prev,next, today",
      center: "title",
      right: "month,agendaWeek,agendaDay,listMonth",
    },
    locale: "es",
    timeFormat: "h(:mm)",
    events: base_url + "agenda/getEventos", // ← base_url viene de PHP

    dayClick: function (date) {
      $("#modalNewTitle").html("Reservar sala de Juntas: " + date.format("D-MMM-Y"));
      $("#inicia").val(date.format("h:mm A"));
      $("#termina").val(date.format("h:mm A"));
      dia = date.format();
      $("#modalNew").modal();
    },

    eventClick: function (evento) {
      $("#modalViewTitle").html(evento.title + ": " + evento.start.format("D-MMM-Y"));
      $("#modalViewUsuario").html("Usuario: " + evento.User);
      $("#modalViewInicia").html("Inicia: " + evento.start.format("hh:mm A"));
      $("#modalViewTermina").html("Termina: " + evento.end.format("hh:mm A"));
      $("#modalViewDate").html("Creado: " + moment(evento.fecha).format("YYYY-MM-D h:mm:ss a"));
      $("#modalViewSala").html("Sala: " + evento.sala);

      idEvento = evento.id;

      if (evento.usuario == session_id) {
        $("#modalCancel").show();
      } else {
        $("#modalCancel").hide();
      }

      if (!evento.descripcion) {
        $("#modalViewNotes").html("");
      } else {
        $("#modalViewNotes").html("Notas: " + evento.descripcion);
      }

      $("#modalView").modal();
    },
  });
});



function validar() {
  var inicia = $('#inicia').val();
  var termina = $('#termina').val();
  var sala = $('#sala').val();

  var i = new Date(dia + " " + inicia);
  var t = new Date(dia + " " + termina);

  inicia = i.getFullYear() + "-" + (i.getMonth() + 1) + "-" + paddy(i.getDate(), 2) + " " + i.getHours() + ":" + paddy(i.getMinutes(), 2);
  termina = t.getFullYear() + "-" + (t.getMonth() + 1) + "-" + paddy(t.getDate(), 2) + " " + t.getHours() + ":" + paddy(t.getMinutes(), 2);

  if ($('#cbreunion').is(":checked")) {
    if (document.getElementById("tags_1").value.length == 0) {
      alert('El campo correos está vacío.');
      return;
    }
  }

  if (document.getElementById("asunto").value.length == 0) {
    alert('El asunto está vacío.');
  } else if (document.getElementById("notas").value.length == 0) {
    alert('Ingresar descripción del evento.');
  } else {
    $.ajax({
      type: "POST",
      url: base_url + 'agenda/validacion',
      data: { inicia: inicia, termina: termina, sala: sala },
      success: function (result) {
        if (result) {
          alert("Sala Ocupada");
        } else {
          crearEvento(); // asegúrate de que esta función esté definida también
        }
      },
      error: function (data) {
        alert("Error");
        console.log(data);
      },
    });
  }
}



function crearEvento() {
  var asunto = $('#asunto').val();
  var inicia = $('#inicia').val();
  var termina = $('#termina').val();
  var descripcion = $('#notas').val();
  var reunion = $('#cbreunion').val();
  var tags_1 = $('#tags_1').val();
  var sala = $('#sala').val();

  var i = new Date(dia + " " + inicia);
  var t = new Date(dia + " " + termina);

  inicia = i.getFullYear() + "-" + (i.getMonth() + 1) + "-" + paddy(i.getDate(), 2) + " " + i.getHours() + ":" + paddy(i.getMinutes(), 2);
  termina = t.getFullYear() + "-" + (t.getMonth() + 1) + "-" + paddy(t.getDate(), 2) + " " + t.getHours() + ":" + paddy(t.getMinutes(), 2);

  console.log(inicia);
  console.log(termina);

  if (moment(inicia) < moment(termina)) {
    $.ajax({
      type: "POST",
      url: base_url + 'agenda/crearEvento',
      data: {
        'titulo': asunto,
        'inicia': inicia,
        'termina': termina,
        'descripcion': descripcion,
        'reunion': reunion,
        'tags_1': tags_1,
        'sala': sala
      },
      success: function (result) {
        cal.fullCalendar('renderEvent', {
          id: result,
          title: 'SALA DE JUNTAS',
          usuario: session_id,
          User: session_name,
          fecha: moment().format('YYYY-MM-D h:mm:ss a'),
          descripcion: descripcion,
          start: i,
          end: t,
          allDay: false
        });
      },
      error: function (data) {
        alert("Error");
        console.log(data);
      }
    });
  } else {
    alert("Fecha de inicio debe ser menor a la fecha de terminación");
  }
}



function borrarEvento() {
  $.ajax({
    type: "POST",
    url: base_url + 'agenda/borrarEvento',
    data: { 'id': idEvento },
    success: function (result) {
      if (result == "1") {
        cal.fullCalendar('removeEvents', idEvento);
      }
    },
    error: function (data) {
      alert("Error");
      console.log(data);
    }
  });
}

    $('.date').datetimepicker({
        format: 'hh:mm A'
    });
    