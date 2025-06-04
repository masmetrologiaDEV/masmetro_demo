<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Mantenimineto Equipos de TI</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">


                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <p style="display: inline;">
                                        Tipo
                                    </p>
                                    <select style="width: 100px;" id="selTipo" onclick="buscar()">
                                        <option value=""></option>
                                        <option value="Laptop">Laptop</option>
                                        <option value="Desktop">Desktop</option>
                                        <option value="Monitor">Monitor</option>
                                        <option value="Impresora">Impresora</option>
                                        <option value="Bateria">Bateria</option>
                                        <option value="Router">Router</option>
                                        <option value="Switch">Switch</option>
                                        <option value="Celular">Celular</option>
                                    </select>

	   			    <label id="tipo">No Inventario</label>
                                    <input id="txtNoInv" style="display: inline;" type="text">

                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>

                            </div>
                             <table style="margin-bottom:60px;" id="tabla" class="table table-striped">
                                <thead>
                                    <tr class="headings">
                                        <th class="column-title" style="width: 7%;">Foto</th>
                                        <th class="column-title">ID</th>
                                        <th class="column-title">Tipo</th>
                                        <th class="column-title">Asignado a</th>
                                        <th class="column-title">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($equipo) {
                                        foreach ($equipo->result() as $elem) {
                                            ?>
                                                <tr class="even pointer">
                                                    <td><img src="<?= base_url("data/equipos/ti/fotos/".$elem->foto);?>" class='avatar' alt='Avatar'></td>
                                                    
                                                    <td><?= $elem->id ?></td>
                                                    <td><?= $elem->tipo ?></td>
                                                    <td><?= $elem->Asignado ?></td>
                                                    <td><a href="<?= base_url("equipos/mantenimiento/".$elem->id);?>" class="btn btn-info">Realizar Mantenimiento</a></td>
                                                </tr>

                                        <?php }
                                    }
                                    ?>
                                </tbody>
                            </table>


                        </div>
                    </div>
                </div>

            </div>
        </div>

        


    </div>
</div>
<!-- /page content -->







<!-- footer content -->
<footer>
    <div class="pull-right">
        Equipo de Desarrollo | MAS Metrología
    </div>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- jQuery Tags Input -->
<script src=<?= base_url("template/vendors/jquery.tagsinput/src/jquery.tagsinput.js") ?>></script>
<!-- formatCurrency -->
<script src=<?= base_url("template/vendors/formatCurrency/jquery.formatCurrency-1.4.0.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<!-- jquery.redirect -->
<script src=<?= base_url("template/vendors/jquery.redirect/jquery.redirect.js"); ?>></script>
<!-- CUSTOM JS FILE -->
<script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>
<!-- JS FILE -->
<script src=<?= base_url("application/views/equipos/ti/js/catalogo.js"); ?>></script>

<script>
  function load(){
    buscar();
  }
    function buscar(){
    var URL = base_url + "equipos/getEquiposTI";
    $('#tabla tbody tr').remove();
    
    var tipo =$('#selTipo').val();
    var texto = $('#txtNoInv').val();
    if (tipo=="Celular") {
        $('#tipo').html("Serie");
    }else{
        $('#tipo').html("No Inventario");
    }

    $.ajax({
        type: "POST",
        url: URL,
        data: { tipo : tipo, texto : texto},
        success: function(result) {
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                  
                    ren.insertCell(0).innerHTML = "<img src='" + base_url + "data/equipos/ti/fotos/" + elem.foto + "' class='avatar' alt='Avatar'>";
                    ren.insertCell(1).innerHTML = paddy(elem.id, 4);
                    ren.insertCell(2).innerHTML = elem.tipo;
                    ren.insertCell(3).innerHTML = elem.Asignado;
                    ren.insertCell(4).innerHTML = "<a href='"+ base_url+ "equipos/mantenimiento/" +elem.id+"' class='btn btn-info'>Realizar Mantenimiento</a>";
                    //ren.insertCell().innerHTML = botonhis(elem.id);
                });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
    });
}
</script>


</body>
</html>
