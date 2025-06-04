<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Tool Crib Pedidos</h2>

                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                            
                        
                        <a href=<?= base_url("toolcrib/reporte"); ?>><button id="send" type="submit" class="btn btn-primary">Reporte</button></a>
                        </div>



                        <div class="x_content" >





                            <div class="table-responsive" >
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            
                                            <!--<th class="column-title">Responsable</th>-->
                                            <th >No. Pedido</th>
                                            <th >No. Empleado</th>
                                            <th >Usuario</th>
                                            <th >Fecha</th>
                                            <th >Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if ($pedido) {
                                        foreach ($pedido->result() as $elem) {
                                            ?>
                                                <tr class="even pointer">
                                                    
                                                    <td><?= $elem->idToolCrib ?></td>
                                                    <td><?= $elem->no_empleado ?></td>
                                                    <td><?= $elem->nombre ?></td>
                                                    <td><?= $elem->fecha ?></td>
                                                    <?php
                                                    if($elem->estatus =="APROBADO"){
                                                        ?>
                                                    <td><a href="<?= base_url('toolcrib/verPedido/' . $elem->idToolCrib) ?>" class="btn btn-success"><?= $elem->estatus ?></a></td>
                                                <?php }
                                                else{
                                                    ?>
                                                    <td><button class="btn btn-warning"><?= $elem->estatus ?></button></td>

                                              <?php  }
                                            ?>
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
