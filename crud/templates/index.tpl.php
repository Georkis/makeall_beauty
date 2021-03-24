<?= $helper->getHeadPrintCode($entity_class_name.' index'); ?>

{% block css %}
<link rel="stylesheet" href="{{ asset('assets/css/responsive.dataTables.min.css') }}">
{% endblock %}
{% block body %}
    <div class="page-header">
        <h4 class="page-title"><?= $entity_class_name ?></h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ path('dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Listado</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools float-right">
                        <a href="{{ path('<?= $route_name ?>_new') }}" class="btn btn-secondary btn-xs"><i class="fa fa-plus-circle"></i> Nuevo</a>
                    </div>
                </div>
                <div class="card-body table-responsive">

                    <table class="table table-striped" id="basic-datatables">
                        <thead>
                            <tr>
                <?php foreach ($entity_fields as $field): ?>
                                <th><?= ucfirst($field['fieldName']) ?></th>
                <?php endforeach; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
                            <tr>
                <?php foreach ($entity_fields as $field): ?>
                                <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
                <?php endforeach; ?>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-dark btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="d-none"><i class="fa fa-spinner fa-pulse"></i></span> Seleccionar
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="{{ path('<?= $route_name ?>_show', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="dropdown-item"><i class="fa fa-list"></i> Detalle</a>
                                            <a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="dropdown-item"><i class="fa fa-edit"></i> Editar</a>
                <!--                            <div class="dropdown-divider"></div>-->
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="<?= (count($entity_fields) + 1) ?>">No hay registro que mostrar</td>
                            </tr>
                        {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
{% block js %}
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.responsive.min.js') }}"></script>
<script>
    $('#basic-datatables').DataTable({
        "language": {
            "paginate": {
                "first": "Primera pagina",
                "last": "Última pagina",
                "previous": "Anterior",
                "next": "Siguiente"
            },
            "zeroRecords": "No hay registro que mostrar",
            "emptyTable": "No hay datos",
            "info": "Mostrar pagina _PAGE_ of _PAGES_",
            "infoEmpty": "No hay entradas que mostrar",
            "loadingRecords": "Espere por favor...",
            "search": "Buscar:",
            "zeroRecords": "No hay registro que mostrar",
            "sLengthMenu": "Mostrar _MENU_ entradas",
            "sInfoFiltered": "(filtrado de _MAX_ total de entradas)",
        },
        "responsive": true,
    });
</script>
{% endblock %}