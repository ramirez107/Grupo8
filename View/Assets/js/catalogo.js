// catalogo: al cambiar la categoria o el orden se busca de una vez (evento change)
$(function () {
    $('#ddlCategoria, #ddlOrden').on('change', function () {
        $('#formBuscar').submit();
    });
});
