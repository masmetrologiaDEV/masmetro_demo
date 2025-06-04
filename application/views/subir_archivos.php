<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Adjuntar archivos a Ticket de Servicio</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="x_content">
                            <form method="POST" action=<?= base_url($controlador.'/subir_archivos') ?> class="dropzone" id="myDrop" enctype="multipart/form-data">
                                <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                                <div class="fallback">
                                    <input type="file" name="file" id="archivos">
                                </div>
                            </form>
                        </div>
                        <a class="btn btn-primary" href="<?= base_url($controlador . '/ver/' . $id_ticket) ?>">Aceptar</a>
                        <button class="btn btn-primary"  id="btn-paste">Pegar del Portapapeles</button>
                        <button class="btn btn-danger" onclick="limpiar()">Limpiar</button>
                    </div>
                </div>
            </div>
             <h3 id="text"></h3>
            <div id="paste-here"></div> 
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
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js") ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js") ?>></script>
<!-- Dropzone.js -->
<script src=<?= base_url("template/vendors/dropzone/dist/dropzone.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.min.js"); ?>></script>

<script type="text/javascript">

    jQuery(document).ready(function ($) {

        Dropzone.options.myDrop = {
            uploadMultiple: true,
            dictDefaultMessage: "Haz click aqui o arrastra los archivos a subir",

            init: function init() {
                this.on('error', function () {
                    alert('Error');
                });
            }
        }

    });



</script>
<script>
    const pasteButton = document.getElementById('btn-paste');
    pasteButton.addEventListener('click', async (event) => {
        $('#paste-here').empty();
        document.getElementById('text').innerHTML = "";
        const data = await navigator.clipboard.read();
        const clipboardContent = data[0];
            const blob = await clipboardContent.getType('image/png');
            const url = window.URL.createObjectURL(blob);
            const img = document.createElement('img');
            img.src = url;
            img.style.maxHeight = '200px';
            document.getElementById('text').innerHTML = "Arrastra la foto hasta la caja de arriba";
            document.getElementById('paste-here').appendChild(img);
    })
    
    function limpiar(){
        document.getElementById('text').innerHTML = "";
        $('#paste-here').empty();
    }
</script>

</body>
</html>
