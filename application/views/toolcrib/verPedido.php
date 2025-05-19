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
                        </div>



                        <div class="x_content" >

<form method="POST" action=<?= base_url('toolcrib/entregarPedido') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
    <input type="hidden" name="idVenta" value=<?= $idVenta ?>>

                            <div class="table-responsive" >
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th >Usuario</th>
                                            <th >Producto</th>
                                            <th >Cantidad</th>
                                            
                                            <th >Opciones</th>
                                                                                        
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if ($pedido) {
                                        ?>
                                        <?php 
                                        foreach ($pedido->result() as $elem) {
                                            ?>    

                                                <tr class="even pointer">
                                                    <td><?= $elem->nombre ?></td>
                                                    <td><?= $elem->producto ?></td>
                                                    <td><?= $elem->cantidad ?></td> 
                                                    <?php if ($elem->estatus!='ENTREGADO') { ?>
                                                    <td>
                                                        <a href=<?= base_url("toolcrib/EditarPedido/".$elem->idDVTC); ?>><button type="button"class="btn btn-warning btn-xs"><i class="fa fa-cogs"></i> Entrega </button></a>
                                                    </td>   
                                                    <?php } ?>                                                
                                                </tr>

                                        <?php }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                
                                <?php if ($elem->estatus=='ENTREGADO') { ?>
                                <button id="send" type="submit" class="btn btn-primary">Entregar pedido</button></
                                <?php } ?>   
                            </div>

</form>
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
