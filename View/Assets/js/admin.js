// javascript del panel admin: validaciones con jquery validate y confirmaciones

$(function () {

    // confirmar antes de desactivar un producto o una categoria
    $('form.confirmar').on('submit', function (e) {
        if (!confirm($(this).data('pregunta'))) {
            e.preventDefault();
        }
    });

    const reglasComunes = {
        // se valida al escribir y al enviar, asi el boton no se mueve al dar clic
        onfocusout: false,
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        errorPlacement: function (error, elemento) {
            error.insertAfter(elemento);
        },
        highlight: function (elemento) {
            $(elemento).addClass('is-invalid');
        },
        unhighlight: function (elemento) {
            $(elemento).removeClass('is-invalid');
        }
    };

    // formulario de producto (registro y editar)
    $('#formProducto').validate($.extend({}, reglasComunes, {
        rules: {
            txtNombre: { required: true },
            ddlCategoria: { required: true },
            txtPrecio: { required: true, number: true, min: 0.01 },
            txtStock: { required: true, digits: true, min: 0 },
            txtStockMinimo: { required: true, digits: true, min: 0 },
            fileImagen: { required: $('#formProducto').data('imagen-obligatoria') == 1, extension: 'jpg|jpeg|png' }
        },
        messages: {
            txtNombre: { required: 'El nombre es obligatorio' },
            ddlCategoria: { required: 'Seleccione una categoría' },
            txtPrecio: {
                required: 'El precio es obligatorio',
                number: 'Ingrese un número',
                min: 'El precio debe ser mayor a cero'
            },
            txtStock: {
                required: 'El stock es obligatorio',
                digits: 'Ingrese un número entero',
                min: 'El stock no puede ser negativo'
            },
            txtStockMinimo: {
                required: 'El stock mínimo es obligatorio',
                digits: 'Ingrese un número entero',
                min: 'El stock no puede ser negativo'
            },
            fileImagen: {
                required: 'Seleccione la imagen del producto',
                extension: 'La imagen debe ser JPG o PNG'
            }
        }
    }));

    // formulario de categoria
    $('#formCategoria').validate($.extend({}, reglasComunes, {
        rules: {
            txtNombre: { required: true, maxlength: 80 }
        },
        messages: {
            txtNombre: {
                required: 'El nombre es obligatorio',
                maxlength: 'Máximo 80 caracteres'
            }
        }
    }));
});

// extension de archivo, como el metodo de additional-methods de jquery validate
if (window.jQuery && $.validator) {
    $.validator.addMethod('extension', function (valor, elemento, tipos) {
        return this.optional(elemento) || new RegExp('\\.(' + tipos + ')$', 'i').test(valor);
    });
}
