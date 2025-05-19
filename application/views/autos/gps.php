<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2><?= $titulo ?></h2>

                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>

                        <div class="x_content">

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">Foto</th>
                                            <th class="column-title">Automóvil</th>
                                            <th class="column-title">Placas</th>
                                            <th class="column-title">Serie</th>
                                            <th class="column-title">Marca GPS</th>
                                            <th class="column-title">IME GPS</th>
                                            <th class="column-title">Modelo GPS</th>
                                            <th class="column-title">Chip GPS</th>
                                            <th class="column-title">Numero GPS</th>
                                            <th class="column-title">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if ($autos) {
                                        foreach ($autos->result() as $elem) {
                                            ?>
                                                <tr class="even pointer">
                                                    <td><a href='<?= base_url('autos/ver/'.$elem->id) ?>'><img width="100" src=<?= 'data:image/bmp;base64,' . base64_encode($elem->foto); ?>><a></td>
                                                    <td><?= $elem->auto ?></td>
                                                    <td><?= $elem->placas ?></td>
                                                    <td><?= $elem->serie ?></td>
                                                    <td><?= $elem->placas ?></td>
                                                    <td><?= $elem->imei ?></td>
                                                    <td><?= $elem->modelo ?></td>
                                                    <td><?= $elem->chip ?></td>
                                                    <td><?= $elem->telefono ?></td>
                                                    <td>
                                                        <a href=<?= base_url("autos/alta_gps/".$elem->idAu); ?>><button type="button"class="btn btn-warning btn-xs"><i class="fa fa-cogs"></i> Agregar GPS </button></a>
                                                    </td> 

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
