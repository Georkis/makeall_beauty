{{ form_start(form) }}
    {{ form_widget(form) }}
<div class="form-group">
    <button class="btn btn-secondary">{{ button_label|default('Guardar') }}</button>
</div>
{{ form_end(form) }}
