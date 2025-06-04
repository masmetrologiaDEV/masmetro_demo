<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Historial de asignaciones</h2>

                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                                <button id="send" type="submit" class="btn btn-success"><a href=<?= base_url("equipos/ti"); ?>>Volver</a></button>
                                
                        </div>

                        <div class="x_content">

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <th class="column-title">ID</th>
                                            <th class="column-title text-center">Fecha</th>
                                            <th class="column-title text-center">Asignado</th>
                                            <th class="column-title text-center">Equipod</th>
                                       
                                    </thead>
                                    <tbody>
                                            <?php
                                    if ($equipo) {
                                        foreach ($equipo->result() as $elem) {
                                            ?>
                                            <tr class="headings">
                                                <td><?= $elem->idequipo?></td>
                                                <td class="column-title text-center"><?= $elem->fecha ?></td>
                                                <td class="column-title text-center"><?= $elem->usuario ?></td>
                                                <td class="column-title text-center"><?= $elem->tipo ?></td>
                                            </tr>   
                                          <?php }
                                    }else{
                                    ?> 

                                   
                                    </tbody>
                                </table>
<section id="error">
    <div class="content">
        <p class=" text-center"><i class="fa fa-warning" style="color:Khaki; font-size: 100px;"></i></p>
        <h1 class="text-center">404</h1>
        <p class="text-center">Error occurred! - Datos no encontrados </p>
        <p  class="text-center"><a href=<?= base_url("equipos/ti"); ?>>Volver</a></p>
    </div>
</section>





                                  <?php }
                                    
                                    ?>
                            </div>


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
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.min.js"); ?>></script>


</body>
</html>
