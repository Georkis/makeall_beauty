<?= $helper->getHeadPrintCode($entity_class_name) ?>

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
            <li class="nav-home">
                <a href="{{ path('<?= $route_name ?>_index') }}">
                    Listado
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Mostrar </a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Mostrar</h5>
                    <div class="card-tools float-right">
                        <a href="{{ path('<?= $route_name ?>_index') }}" class="btn btn-secondary btn-xs"><i class="fa fa-list"></i> Regresar</a>
                        <a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Editar</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                            <?php foreach ($entity_fields as $field): ?>
                                <tr>
                                    <th><?= ucfirst($field['fieldName']) ?></th>
                                    <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
