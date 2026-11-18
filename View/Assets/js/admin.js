// javascript del panel admin: validaciones con jquery validate y confirmaciones

$(function () {

    
    // confirmar cambios de estado con un modal
    $('form.confirmar').on('submit', function (e) {
        e.preventDefault();

        const formulario = this;
        const pregunta = $(formulario).data('pregunta');

        // form.submit() no manda el boton, se agrega su name para que el controlador lo reciba
        const boton = e.originalEvent && e.originalEvent.submitter
            ? e.originalEvent.submitter
            : formulario.querySelector('button[type="submit"][name]');
        if (boton && boton.name && !formulario.querySelector('input[type="hidden"][name="' + boton.name + '"]')) {
            const campo = document.createElement('input');
            campo.type = 'hidden';
            campo.name = boton.name;
            campo.value = boton.value || '1';
            formulario.appendChild(campo);
        }

        const modalElemento = document.createElement('div');
        modalElemento.className = 'modal fade';
        modalElemento.tabIndex = -1;

        modalElemento.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar acción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" id="mensajeConfirmacion"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="aceptarConfirmacion">Confirmar</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modalElemento);
        modalElemento.querySelector('#mensajeConfirmacion').textContent = pregunta;

        const modal = new bootstrap.Modal(modalElemento);

        modalElemento.querySelector('#aceptarConfirmacion').addEventListener('click', function () {
            modal.hide();
            HTMLFormElement.prototype.submit.call(formulario);
        });

        modalElemento.addEventListener('hidden.bs.modal', function () {
            modal.dispose();
            modalElemento.remove();
        });

        modal.show();
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

    // formulario de promocion (2x1 o descuento)
    $('#formPromocion').validate($.extend({}, reglasComunes, {
        rules: {
            txtNombre: { required: true, maxlength: 100 },
            txtDescuento: { required: true, number: true, min: 1, max: 99 },
            txtFechaInicio: { required: true },
            txtFechaFin: { required: true }
        },
        messages: {
            txtNombre: { required: 'El nombre es obligatorio', maxlength: 'Máximo 100 caracteres' },
            txtDescuento: {
                required: 'El descuento es obligatorio',
                number: 'Ingrese un número',
                min: 'El descuento debe estar entre 1 y 99 %',
                max: 'El descuento debe estar entre 1 y 99 %'
            },
            txtFechaInicio: { required: 'Seleccione la fecha de inicio' },
            txtFechaFin: { required: 'Seleccione la fecha final' }
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
