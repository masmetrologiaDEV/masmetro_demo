
function load(){
 
    buscar();
}
function buscar(){
    
    var URL = base_url + "autos/ajaxgetAutos";
    $('#tblAutos tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBuscar").val();
    var activo = $("#cbActivo").is(":checked") ? 1 : 0;

    $.ajax({
        type: "POST",
        url: URL,
        data: { parametro : parametro, texto : texto, activo : activo },
        success: function(result) {
            
            if(result)
            {
                var tab = $('#tblAutos tbody')[0];
                var rs = JSON.parse(result);
                                


                $.each(rs, function(i, elem){
                                    

                    var ren = tab.insertRow(tab.rows.length);
                    ren.dataset.id = elem.id;
                    
                    var style = "cursor: pointer;";
                    if(elem.activo != "1"){
                        style += " color: red;";
                    }
                    ren.style = style;

                    ren.insertCell(0).innerHTML = elem.id;
                    //ren.insertCell(1).innerHTML = elem.id;
                    ren.insertCell(1).innerHTML = '<a><img src="'+ base_url +'/autos/photo/'+elem.id +'" class="avatar" alt="Avatar"></a>';
                    //ren.insertCell(1).innerHTML = '<a href="' + base_url + '/autos/ver/' + elem.id + '"><img src="'+ base_url + elem.id +'" class="avatar" alt="Avatar"><a>';
                    ren.insertCell(2).innerHTML = elem.fabricante+' '+elem.marca +' '+ elem.modelo;
                    ren.insertCell(3).innerHTML = elem.serie;
                    ren.insertCell(4).innerHTML = elem.placas;
                    ren.insertCell(5).innerHTML = '<a href="'+base_url+'/usuarios/ver/' + elem.responsable+'">'+elem.Responsable +'</a>';
                    if(elem.Ultrev == null)
                        {
                        ren.insertCell(6).innerHTML='N/A';
                        }else
                        {
                        //var date = JSON.stringify(elem.Ultrev);
                        
                        const formateador = new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeStyle: 'medium' });
                        const fecha = new Date(elem.Ultrev);
                        const date = formateador.format(fecha);
                        

                    ren.insertCell(6).innerHTML ='<a target="_blank" href="'+base_url+'autos/hallazgos/'+elem.IdUltrev+'" class="btn btn-primary">"'+date+'"</a>';
                    }
                    ren.insertCell(7).innerHTML='<a href="'+base_url+'autos/editar/'+elem.id+'"><button type="button" class="btn btn-warning"><i class="fa fa-pencil"></i> Editar</button>';
                     

                    
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


