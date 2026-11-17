<?php
include_once __DIR__ . '/../Model/CategoriaModel.php';
include_once __DIR__ . '/../Model/ProductoModel.php';
include_once __DIR__ . '/SeguridadController.php';

// categorias activas para el menu del catalogo
function ConsultarCategoriasCatalogo()
{
    return ConsultarCategoriasActivasModel();
}

// productos segun lo que venga en la url: buscar, categoria, orden y oferta
function ConsultarProductosCatalogo()
{
    $texto = trim($_GET['buscar'] ?? '');
    $idCategoria = (int) ($_GET['categoria'] ?? 0);
    $orden = strtoupper($_GET['orden'] ?? '');
    $oferta = $_GET['oferta'] ?? '';

    if (!in_array($orden, ['ASC', 'DESC'], true)) {
        $orden = '';
    }

    $productos = BuscarProductosModel($texto, $idCategoria, $orden);

    // filtros del menu: 2x1 o productos con descuento
    if ($oferta == '2x1') {
        $productos = array_values(array_filter($productos, function ($p) {
            return $p['es_2x1'] == 1;
        }));
    }
    else if ($oferta == 'descuento') {
        $productos = array_values(array_filter($productos, function ($p) {
            return $p['precio_final'] < $p['precio'];
        }));
    }

    return $productos;
}
