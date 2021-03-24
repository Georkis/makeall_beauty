<?= $helper->getHeadPrintCode('New '.$entity_class_name) ?>

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
                <a href="{{ path('<?= $route_name ?>_index') }}">Listado</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Crear nuevo <?= $entity_class_name ?></a>
            </li>
        </ul>
    </div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-tools float-right">
                    <a href="{{ path('<?= $route_name ?>_index') }}" class="btn btn-secondary btn-xs"><i class="fa fa-list"></i> Regresar</a>
                </div>
            </div>
            <div class="card-body">
                {{ include('<?= $route_name ?>/_form.html.twig') }}
            </div>
        </div>
    </div>
</div>
{% endblock %}
