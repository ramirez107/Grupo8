-- Actualizacion de pedidos: pago con SINPE (comprobante), estado 'En espera'
-- hasta que el admin lo apruebe, titular de la tarjeta y reportes de ventas por rango.
-- Correr UNA vez sobre una base que ya existe (las instalaciones nuevas ya lo traen en Script.sql).
-- La parte 2 (permisos) se corre como root en phpMyAdmin.

USE supermercado_jaulares;

-- ===== parte 1: se puede correr con admin_jaulares =====

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS tarjeta_titular VARCHAR(100) NULL AFTER tarjeta_ultimos4,
    ADD COLUMN IF NOT EXISTS comprobante     VARCHAR(255) NULL AFTER tarjeta_titular;

ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS ck_pedidos_estado;
ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS ck_pedidos_pago;
ALTER TABLE pedidos
    ADD CONSTRAINT ck_pedidos_estado CHECK (estado IN
        ('En espera','Recibido','En preparación','En camino','Entregado','Cancelado')),
    ADD CONSTRAINT ck_pedidos_pago CHECK (metodo_pago IN ('Efectivo','Tarjeta','SINPE'));

DROP TRIGGER IF EXISTS trg_pedidos_bu;
DROP PROCEDURE IF EXISTS sp_pedido_crear;
DROP PROCEDURE IF EXISTS sp_reporte_ventas_rango;
DROP PROCEDURE IF EXISTS sp_pedido_obtener_admin;
DROP PROCEDURE IF EXISTS sp_reporte_resumen_rango;
DROP PROCEDURE IF EXISTS sp_reporte_ventas_metodo;
DROP PROCEDURE IF EXISTS sp_reporte_mas_vendidos_rango;

DELIMITER $$

-- revisa que el estado del pedido avance en orden
CREATE TRIGGER trg_pedidos_bu BEFORE UPDATE ON pedidos
FOR EACH ROW
BEGIN
    IF NEW.estado <> OLD.estado THEN
        IF OLD.estado IN ('Entregado','Cancelado') THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'El pedido ya está cerrado y no puede cambiar de estado.';
        END IF;
        IF NOT (
               (OLD.estado = 'En espera'      AND NEW.estado IN ('Recibido','Cancelado'))
            OR (OLD.estado = 'Recibido'       AND NEW.estado IN ('En preparación','Cancelado'))
            OR (OLD.estado = 'En preparación' AND NEW.estado IN ('En camino','Cancelado'))
            OR (OLD.estado = 'En camino'      AND NEW.estado IN ('Entregado','Cancelado'))
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Cambio de estado no permitido.';
        END IF;
    END IF;
END$$

-- cliente: convierte el carrito en pedido, baja el stock y agenda la entrega
-- si algo falla no guarda nada
CREATE PROCEDURE sp_pedido_crear(
    IN p_id_usuario INT, IN p_id_direccion INT, IN p_metodo_pago VARCHAR(20),
    IN p_tarjeta_ultimos4 CHAR(4), IN p_tarjeta_titular VARCHAR(100),
    IN p_comprobante VARCHAR(255), IN p_fecha_entrega DATE,
    IN p_franja_horaria VARCHAR(50))
BEGIN
    DECLARE v_fin INT DEFAULT 0;
    DECLARE v_id_carrito INT;
    DECLARE v_id_pedido INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_stock INT;
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_nombre VARCHAR(120);
    DECLARE v_lineas INT DEFAULT 0;
    DECLARE v_mensaje VARCHAR(255);

    DECLARE cur_carrito CURSOR FOR
        SELECT cd.id_producto, cd.cantidad
          FROM carrito_detalle cd
         WHERE cd.id_carrito = v_id_carrito
         ORDER BY cd.id_producto;          -- en orden para evitar bloqueos
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;
        RESIGNAL;
    END;

    -- validaciones
    IF NOT EXISTS (SELECT 1 FROM direcciones
                    WHERE id_direccion = p_id_direccion
                      AND id_usuario = p_id_usuario AND activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seleccione una dirección de entrega válida.';
    END IF;
    IF p_metodo_pago NOT IN ('Efectivo','Tarjeta','SINPE') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Método de pago no válido.';
    END IF;
    IF p_metodo_pago = 'Tarjeta' AND (p_tarjeta_ultimos4 IS NULL
                                      OR p_tarjeta_ultimos4 NOT REGEXP '^[0-9]{4}$'
                                      OR COALESCE(TRIM(p_tarjeta_titular), '') = '') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Faltan los datos de la tarjeta.';
    END IF;
    IF p_metodo_pago = 'SINPE' AND COALESCE(p_comprobante, '') = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Adjunte el comprobante del SINPE.';
    END IF;
    IF p_fecha_entrega IS NULL OR p_fecha_entrega < CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de entrega no puede ser en el pasado.';
    END IF;
    IF fn_es_dia_cierre(p_fecha_entrega) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El supermercado está cerrado ese día.';
    END IF;
    IF p_franja_horaria NOT IN ('08:00-12:00','12:00-16:00','16:00-20:00') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Franja horaria no válida.';
    END IF;

    SET v_id_carrito = (SELECT id_carrito FROM carritos
                         WHERE id_usuario = p_id_usuario AND estado = 'Activo'
                         ORDER BY id_carrito DESC LIMIT 1);
    IF v_id_carrito IS NULL
       OR NOT EXISTS (SELECT 1 FROM carrito_detalle WHERE id_carrito = v_id_carrito) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El carrito está vacío.';
    END IF;

    START TRANSACTION;
    SET @jaulares_origen = 'PEDIDO', @jaulares_id_usuario = p_id_usuario;

    -- el SINPE queda en espera hasta que el admin revise el comprobante
    INSERT INTO pedidos (id_usuario, id_direccion, estado, metodo_pago,
                         tarjeta_ultimos4, tarjeta_titular, comprobante)
    VALUES (p_id_usuario, p_id_direccion,
            IF(p_metodo_pago = 'SINPE', 'En espera', 'Recibido'),
            p_metodo_pago,
            IF(p_metodo_pago = 'Tarjeta', p_tarjeta_ultimos4, NULL),
            IF(p_metodo_pago = 'Tarjeta', TRIM(p_tarjeta_titular), NULL),
            IF(p_metodo_pago = 'SINPE', p_comprobante, NULL));
    SET v_id_pedido = LAST_INSERT_ID();

    OPEN cur_carrito;
    leer: LOOP
        FETCH cur_carrito INTO v_id_producto, v_cantidad;
        IF v_fin = 1 THEN LEAVE leer; END IF;

        -- aparta el stock mientras se guarda el pedido
        SET v_stock = (SELECT stock FROM inventario
                        WHERE id_producto = v_id_producto FOR UPDATE);
        SET v_nombre = (SELECT nombre FROM productos
                         WHERE id_producto = v_id_producto AND activo = 1);

        IF v_nombre IS NULL THEN
            SET v_mensaje = 'Un producto del carrito ya no está disponible.';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensaje;
        END IF;
        IF COALESCE(v_stock, 0) < v_cantidad THEN
            SET v_mensaje = CONCAT('Stock insuficiente de ', v_nombre,
                                   '. Disponibles: ', COALESCE(v_stock, 0));
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensaje;
        END IF;

        SET v_precio = fn_precio_final(v_id_producto);

        INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (v_id_pedido, v_id_producto, v_cantidad, v_precio,
                fn_subtotal_linea(v_id_producto, v_cantidad, v_precio));

        UPDATE inventario SET stock = stock - v_cantidad
         WHERE id_producto = v_id_producto;

        SET v_lineas = v_lineas + 1;
    END LOOP;
    CLOSE cur_carrito;

    UPDATE pedidos
       SET total = (SELECT SUM(subtotal) FROM pedido_detalle WHERE id_pedido = v_id_pedido)
     WHERE id_pedido = v_id_pedido;

    INSERT INTO entregas (id_pedido, fecha_entrega, franja_horaria)
    VALUES (v_id_pedido, p_fecha_entrega, p_franja_horaria);

    UPDATE carritos SET estado = 'Convertido' WHERE id_carrito = v_id_carrito;

    COMMIT;
    SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;

    SELECT p.id_pedido, p.total, p.fecha_pedido, p.estado, p.metodo_pago,
           v_lineas AS lineas, u.correo, u.nombre
      FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario
     WHERE p.id_pedido = v_id_pedido;
END$$

-- admin: ventas por dia
CREATE PROCEDURE sp_reporte_ventas_rango(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT DATE(fecha_pedido) AS fecha,
           COUNT(*)           AS pedidos,
           SUM(total)         AS total_ventas
      FROM pedidos
     WHERE estado NOT IN ('Cancelado','En espera')
       AND DATE(fecha_pedido) BETWEEN p_desde AND p_hasta
     GROUP BY DATE(fecha_pedido)
     ORDER BY fecha;
END$$

-- admin: un pedido con el cliente, la entrega y el pago (incluye el comprobante de SINPE)
CREATE PROCEDURE sp_pedido_obtener_admin(IN p_id_pedido INT)
BEGIN
    SELECT p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.total,
           p.tarjeta_ultimos4, p.tarjeta_titular, p.comprobante,
           fn_nombre_completo(u.id_usuario) AS cliente, u.correo, u.telefono,
           CONCAT(d.provincia, ', ', d.canton, ', ', d.distrito, '. ', d.detalle) AS direccion,
           e.fecha_entrega, e.franja_horaria
      FROM pedidos p
      JOIN usuarios u ON u.id_usuario = p.id_usuario
      JOIN direcciones d ON d.id_direccion = p.id_direccion
      LEFT JOIN entregas e ON e.id_pedido = p.id_pedido
     WHERE p.id_pedido = p_id_pedido;
END$$

-- admin: totales del rango para las tarjetas del reporte
CREATE PROCEDURE sp_reporte_resumen_rango(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT COUNT(*)                                   AS pedidos,
           COALESCE(SUM(total), 0)                    AS total_ventas,
           COALESCE(AVG(total), 0)                    AS ticket_promedio,
           (SELECT COUNT(*) FROM pedidos
             WHERE estado = 'En espera'
               AND DATE(fecha_pedido) BETWEEN p_desde AND p_hasta) AS en_espera,
           (SELECT COUNT(*) FROM pedidos
             WHERE estado = 'Cancelado'
               AND DATE(fecha_pedido) BETWEEN p_desde AND p_hasta) AS cancelados
      FROM pedidos
     WHERE estado NOT IN ('Cancelado','En espera')
       AND DATE(fecha_pedido) BETWEEN p_desde AND p_hasta;
END$$

-- admin: ventas por metodo de pago en el rango
CREATE PROCEDURE sp_reporte_ventas_metodo(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT metodo_pago, COUNT(*) AS pedidos, SUM(total) AS total_ventas
      FROM pedidos
     WHERE estado NOT IN ('Cancelado','En espera')
       AND DATE(fecha_pedido) BETWEEN p_desde AND p_hasta
     GROUP BY metodo_pago
     ORDER BY total_ventas DESC;
END$$

-- admin: productos mas vendidos en el rango
CREATE PROCEDURE sp_reporte_mas_vendidos_rango(IN p_desde DATE, IN p_hasta DATE, IN p_limite INT)
BEGIN
    SELECT pr.id_producto, pr.nombre, pr.imagen,
           SUM(pd.cantidad) AS unidades,
           SUM(pd.subtotal) AS total_vendido
      FROM pedido_detalle pd
      JOIN pedidos p ON p.id_pedido = pd.id_pedido
      JOIN productos pr ON pr.id_producto = pd.id_producto
     WHERE p.estado NOT IN ('Cancelado','En espera')
       AND DATE(p.fecha_pedido) BETWEEN p_desde AND p_hasta
     GROUP BY pr.id_producto, pr.nombre, pr.imagen
     ORDER BY unidades DESC
     LIMIT p_limite;
END$$

DELIMITER ;

-- ===== parte 2: permisos, correr como root =====
-- (al borrar y crear sp_pedido_crear de nuevo, MariaDB le quita el permiso al cliente)
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_pedido_crear TO rol_client_jaulares;
