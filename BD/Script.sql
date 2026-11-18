-- base de datos del supermercado los jaulares
-- crea las tablas, funciones, triggers, procedimientos y datos de prueba
-- borra la base y la vuelve a crear cada vez que se ejecuta
-- despues de este se ejecuta usuarios_permisos.sql
-- los errores salen como mensajes que php muestra en la alerta

DROP DATABASE IF EXISTS supermercado_jaulares;
CREATE DATABASE supermercado_jaulares
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE supermercado_jaulares;


-- 1. tablas

-- usuarios y cuentas

CREATE TABLE roles (
    id_rol      INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(30) NOT NULL,
    CONSTRAINT uq_roles_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    id_rol          INT NOT NULL,
    nombre          VARCHAR(60)  NOT NULL,
    apellidos       VARCHAR(100) NOT NULL,
    correo          VARCHAR(120) NOT NULL,
    telefono        VARCHAR(20)  NULL,
    password_hash   VARCHAR(255) NOT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_registro  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- intentos fallidos y hasta cuando queda bloqueada la cuenta
    intentos_fallidos INT        NOT NULL DEFAULT 0,
    bloqueado_hasta   DATETIME   NULL,
    CONSTRAINT uq_usuarios_correo UNIQUE (correo),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    CONSTRAINT ck_usuarios_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE recuperacion_password (
    id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT          NOT NULL,
    token           VARCHAR(255) NOT NULL,  -- se guarda el token cifrado
    vence_en        DATETIME     NOT NULL,
    utilizado       TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT uq_recuperacion_token UNIQUE (token),
    CONSTRAINT fk_recuperacion_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- catalogo

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(80)  NOT NULL,
    descripcion  VARCHAR(255) NULL,
    activo       TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

CREATE TABLE productos (
    id_producto  INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT           NOT NULL,
    nombre       VARCHAR(120)  NOT NULL,
    descripcion  TEXT          NULL,
    precio       DECIMAL(10,2) NOT NULL,
    imagen       VARCHAR(255)  NULL,   -- ruta de la imagen en view/assets/images
    activo       TINYINT(1)    NOT NULL DEFAULT 1,
    CONSTRAINT fk_productos_categoria FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria),
    CONSTRAINT ck_productos_precio CHECK (precio > 0)
) ENGINE=InnoDB;

-- stock de cada producto, uno por producto
CREATE TABLE inventario (
    id_inventario  INT AUTO_INCREMENT PRIMARY KEY,
    id_producto    INT      NOT NULL,
    stock          INT      NOT NULL DEFAULT 0,
    stock_minimo   INT      NOT NULL DEFAULT 5,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_inventario_producto UNIQUE (id_producto),
    CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT ck_inventario_stock CHECK (stock >= 0 AND stock_minimo >= 0)
) ENGINE=InnoDB;

-- historial de cambios de stock, lo llenan los triggers y no se puede editar
CREATE TABLE bitacora_inventario (
    id_bitacora    INT AUTO_INCREMENT PRIMARY KEY,
    id_producto    INT          NOT NULL,
    stock_anterior INT          NULL,
    stock_nuevo    INT          NOT NULL,
    motivo         VARCHAR(30)  NOT NULL,   -- alta, ajuste, pedido o cancelacion
    usuario_bd     VARCHAR(120) NOT NULL,   -- usuario de la base que hizo el cambio
    id_usuario_app INT          NULL,       -- usuario de la pagina que hizo el cambio
    fecha          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ix_bitacora_producto (id_producto)
) ENGINE=InnoDB;

-- guarda cambios importantes de usuarios, productos y pedidos, la llenan los triggers
CREATE TABLE auditoria (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    tabla          VARCHAR(40)  NOT NULL,
    id_registro    INT          NOT NULL,
    accion         VARCHAR(30)  NOT NULL,   -- que cambio: rol, estado, precio, bloqueo
    valor_anterior VARCHAR(255) NULL,
    valor_nuevo    VARCHAR(255) NULL,
    usuario_bd     VARCHAR(120) NOT NULL,   -- usuario de la base que hizo el cambio
    id_usuario_app INT          NULL,       -- usuario de la pagina que hizo el cambio
    fecha          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX ix_auditoria_tabla (tabla, id_registro),
    INDEX ix_auditoria_fecha (fecha)
) ENGINE=InnoDB;

-- carrito

CREATE TABLE carritos (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT         NOT NULL,
    estado     VARCHAR(20) NOT NULL DEFAULT 'Activo',
    creado_en  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carritos_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT ck_carritos_estado CHECK (estado IN ('Activo','Convertido'))
) ENGINE=InnoDB;

CREATE TABLE carrito_detalle (
    id_detalle_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_carrito         INT           NOT NULL,
    id_producto        INT           NOT NULL,
    cantidad           INT           NOT NULL,
    precio_unitario    DECIMAL(10,2) NOT NULL,
    CONSTRAINT uq_carrito_producto UNIQUE (id_carrito, id_producto),
    CONSTRAINT fk_cdet_carrito FOREIGN KEY (id_carrito)
        REFERENCES carritos(id_carrito) ON DELETE CASCADE,
    CONSTRAINT fk_cdet_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto),
    CONSTRAINT ck_cdet_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- pedidos y entregas

CREATE TABLE direcciones (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT          NOT NULL,
    provincia    VARCHAR(50)  NOT NULL,
    canton       VARCHAR(50)  NOT NULL,
    distrito     VARCHAR(50)  NOT NULL,
    detalle      VARCHAR(255) NOT NULL,  -- otras sennas
    activo       TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_direcciones_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- cada usuario puede tener varios pedidos
CREATE TABLE pedidos (
    id_pedido        INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario       INT           NOT NULL,
    id_direccion     INT           NOT NULL,
    fecha_pedido     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado           VARCHAR(30)   NOT NULL DEFAULT 'Recibido',
    metodo_pago      VARCHAR(20)   NOT NULL,
    tarjeta_ultimos4 CHAR(4)       NULL,    -- solo los ultimos 4 numeros de la tarjeta
    tarjeta_titular  VARCHAR(100)  NULL,    -- nombre del duenno de la tarjeta (nunca se guarda el numero completo ni el cvv)
    comprobante      VARCHAR(255)  NULL,    -- archivo del comprobante de SINPE
    total            DECIMAL(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_pedidos_direccion FOREIGN KEY (id_direccion)
        REFERENCES direcciones(id_direccion),
    CONSTRAINT ck_pedidos_estado CHECK (estado IN
        ('En espera','Recibido','En preparación','En camino','Entregado','Cancelado')),
    CONSTRAINT ck_pedidos_pago CHECK (metodo_pago IN ('Efectivo','Tarjeta','SINPE')),
    INDEX ix_pedidos_fecha (fecha_pedido)
) ENGINE=InnoDB;

CREATE TABLE pedido_detalle (
    id_detalle_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido         INT           NOT NULL,
    id_producto       INT           NOT NULL,
    cantidad          INT           NOT NULL,
    precio_unitario   DECIMAL(10,2) NOT NULL,
    subtotal          DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_pdet_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_pdet_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto),
    CONSTRAINT ck_pdet_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- entrega de cada pedido, una por pedido
CREATE TABLE entregas (
    id_entrega     INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido      INT         NOT NULL,
    fecha_entrega  DATE        NOT NULL,
    franja_horaria VARCHAR(50) NOT NULL,
    estado         VARCHAR(30) NOT NULL DEFAULT 'Programada',
    CONSTRAINT uq_entregas_pedido UNIQUE (id_pedido),
    CONSTRAINT fk_entregas_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    CONSTRAINT ck_entregas_franja CHECK (franja_horaria IN
        ('08:00-12:00','12:00-16:00','16:00-20:00')),
    CONSTRAINT ck_entregas_estado CHECK (estado IN
        ('Programada','En camino','Entregada','Cancelada'))
) ENGINE=InnoDB;

-- opcionales: promociones y calendario

CREATE TABLE promociones (
    id_promocion INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    tipo         VARCHAR(20)  NOT NULL DEFAULT 'Porcentaje', -- porcentaje o 2x1
    descuento    DECIMAL(5,2) NOT NULL DEFAULT 0,            -- porcentaje de descuento
    fecha_inicio DATE         NOT NULL,
    fecha_fin    DATE         NOT NULL,
    activo       TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT ck_promo_tipo CHECK (tipo IN ('Porcentaje','2x1')),
    CONSTRAINT ck_promo_descuento CHECK (descuento >= 0 AND descuento <= 100)
) ENGINE=InnoDB;

CREATE TABLE producto_promocion (
    id_producto  INT NOT NULL,
    id_promocion INT NOT NULL,
    PRIMARY KEY (id_producto, id_promocion),
    CONSTRAINT fk_pp_producto FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_pp_promocion FOREIGN KEY (id_promocion)
        REFERENCES promociones(id_promocion) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE eventos_calendario (
    id_evento          INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_creador INT          NOT NULL,
    titulo             VARCHAR(120) NOT NULL,
    descripcion        VARCHAR(255) NULL,
    tipo               VARCHAR(20)  NOT NULL DEFAULT 'Feria',  -- feria, promocion o cierre
    fecha_inicio       DATETIME     NOT NULL,
    fecha_fin          DATETIME     NOT NULL,
    CONSTRAINT fk_eventos_usuario FOREIGN KEY (id_usuario_creador)
        REFERENCES usuarios(id_usuario),
    CONSTRAINT ck_eventos_tipo CHECK (tipo IN ('Feria','Promoción','Cierre')),
    CONSTRAINT ck_eventos_fechas CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;

-- errores de la pagina, igual que terror del proyecto de clase, los guarda RegistrarError de php
CREATE TABLE errores (
    id_error    INT AUTO_INCREMENT PRIMARY KEY,
    mensaje     VARCHAR(4000) NULL,
    accion      VARCHAR(255)  NULL,
    id_usuario  INT           NULL,
    fecha       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- 2. funciones

DELIMITER $$

-- revisa si el usuario conectado a la base es admin, la usan los triggers de inventario
CREATE FUNCTION fn_bd_usuario_admin()
RETURNS TINYINT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_usuario VARCHAR(80) DEFAULT SUBSTRING_INDEX(USER(), '@', 1);
    DECLARE v_host    VARCHAR(255) DEFAULT SUBSTRING_INDEX(USER(), '@', -1);

    IF v_usuario = 'root' THEN
        RETURN 1;
    END IF;
    RETURN EXISTS (SELECT 1 FROM mysql.roles_mapping
                    WHERE User = v_usuario
                      AND (Host = v_host OR Host = 'localhost' OR Host = '%')
                      AND Role = 'rol_admin_jaulares');
END$$

-- revisa si el usuario de la pagina es administrador
CREATE FUNCTION fn_es_admin(p_id_usuario INT)
RETURNS TINYINT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN EXISTS (
        SELECT 1
          FROM usuarios u
          JOIN roles r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = p_id_usuario
           AND u.activo = 1
           AND r.nombre = 'Administrador');
END$$

-- junta nombre y apellidos
CREATE FUNCTION fn_nombre_completo(p_id_usuario INT)
RETURNS VARCHAR(161)
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN (SELECT CONCAT(nombre, ' ', apellidos)
              FROM usuarios WHERE id_usuario = p_id_usuario);
END$$

-- devuelve el stock de un producto
CREATE FUNCTION fn_stock_disponible(p_id_producto INT)
RETURNS INT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN COALESCE((SELECT stock FROM inventario
                      WHERE id_producto = p_id_producto), 0);
END$$

-- devuelve el precio con el descuento de la promocion
CREATE FUNCTION fn_precio_final(p_id_producto INT)
RETURNS DECIMAL(10,2)
NOT DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_precio    DECIMAL(10,2);
    DECLARE v_descuento DECIMAL(5,2);

    SET v_precio = (SELECT precio FROM productos WHERE id_producto = p_id_producto);
    SET v_descuento = COALESCE((
        SELECT MAX(pr.descuento)
          FROM producto_promocion pp
          JOIN promociones pr ON pr.id_promocion = pp.id_promocion
         WHERE pp.id_producto = p_id_producto
           AND pr.activo = 1
           AND pr.tipo = 'Porcentaje'
           AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin), 0);

    RETURN ROUND(v_precio * (1 - v_descuento / 100), 2);
END$$

-- revisa si el producto tiene 2x1
CREATE FUNCTION fn_tiene_2x1(p_id_producto INT)
RETURNS TINYINT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN EXISTS (
        SELECT 1
          FROM producto_promocion pp
          JOIN promociones pr ON pr.id_promocion = pp.id_promocion
         WHERE pp.id_producto = p_id_producto
           AND pr.activo = 1
           AND pr.tipo = '2x1'
           AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin);
END$$

-- calcula el subtotal de una linea, llama a fn_tiene_2x1
CREATE FUNCTION fn_subtotal_linea(p_id_producto INT, p_cantidad INT,
                                  p_precio DECIMAL(10,2))
RETURNS DECIMAL(10,2)
NOT DETERMINISTIC READS SQL DATA
BEGIN
    IF fn_tiene_2x1(p_id_producto) THEN
        RETURN (p_cantidad - FLOOR(p_cantidad / 2)) * p_precio;
    END IF;
    RETURN p_cantidad * p_precio;
END$$

-- suma el total del carrito, llama a fn_subtotal_linea
CREATE FUNCTION fn_total_carrito(p_id_carrito INT)
RETURNS DECIMAL(10,2)
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN COALESCE((
        SELECT SUM(fn_subtotal_linea(id_producto, cantidad, precio_unitario))
          FROM carrito_detalle
         WHERE id_carrito = p_id_carrito), 0);
END$$

-- revisa si la fecha es un dia de cierre
CREATE FUNCTION fn_es_dia_cierre(p_fecha DATE)
RETURNS TINYINT
NOT DETERMINISTIC READS SQL DATA
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM eventos_calendario
         WHERE tipo = 'Cierre'
           AND p_fecha BETWEEN DATE(fecha_inicio) AND DATE(fecha_fin));
END$$

DELIMITER ;


-- 3. triggers
-- los procedimientos les pasan el motivo y el usuario con @jaulares_origen y @jaulares_id_usuario

DELIMITER $$

-- usuarios: guarda el correo en minuscula

CREATE TRIGGER trg_usuarios_bi BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    SET NEW.correo = LOWER(TRIM(NEW.correo));
END$$

CREATE TRIGGER trg_usuarios_bu BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    SET NEW.correo = LOWER(TRIM(NEW.correo));
END$$

-- inventario: solo el admin cambia el stock, el cliente solo lo baja al comprar

CREATE TRIGGER trg_inventario_bi BEFORE INSERT ON inventario
FOR EACH ROW
BEGIN
    IF NOT fn_bd_usuario_admin() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo los administradores de la base pueden registrar stock.';
    END IF;
    IF NEW.stock < 0 OR NEW.stock_minimo < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El stock no puede ser negativo.';
    END IF;
    SET NEW.actualizado_en = NOW();
END$$

CREATE TRIGGER trg_inventario_bu BEFORE UPDATE ON inventario
FOR EACH ROW
BEGIN
    IF NEW.id_producto <> OLD.id_producto THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede cambiar el producto de un inventario.';
    END IF;

    IF NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insuficiente.';
    END IF;

    IF NOT fn_bd_usuario_admin() THEN
        -- el cliente solo baja stock al comprar
        IF NOT (COALESCE(@jaulares_origen, '') = 'PEDIDO'
                AND NEW.stock < OLD.stock
                AND NEW.stock_minimo = OLD.stock_minimo) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Solo los administradores de la base pueden modificar el stock.';
        END IF;
    END IF;

    SET NEW.actualizado_en = NOW();
END$$

CREATE TRIGGER trg_inventario_bd BEFORE DELETE ON inventario
FOR EACH ROW
BEGIN
    IF NOT fn_bd_usuario_admin() THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo los administradores de la base pueden borrar inventario.';
    END IF;
END$$

CREATE TRIGGER trg_inventario_ai AFTER INSERT ON inventario
FOR EACH ROW
BEGIN
    INSERT INTO bitacora_inventario
        (id_producto, stock_anterior, stock_nuevo, motivo, usuario_bd, id_usuario_app)
    VALUES
        (NEW.id_producto, NULL, NEW.stock, 'ALTA', USER(), @jaulares_id_usuario);
END$$

CREATE TRIGGER trg_inventario_au AFTER UPDATE ON inventario
FOR EACH ROW
BEGIN
    IF NEW.stock <> OLD.stock THEN
        INSERT INTO bitacora_inventario
            (id_producto, stock_anterior, stock_nuevo, motivo, usuario_bd, id_usuario_app)
        VALUES
            (NEW.id_producto, OLD.stock, NEW.stock,
             COALESCE(@jaulares_origen, 'AJUSTE'), USER(), @jaulares_id_usuario);
    END IF;
END$$

-- auditoria

-- guarda en auditoria los cambios de rol, estado, contrasenna y bloqueo
CREATE TRIGGER trg_usuarios_au AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.id_rol <> OLD.id_rol THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_anterior, valor_nuevo,
                               usuario_bd, id_usuario_app)
        VALUES ('usuarios', NEW.id_usuario, 'cambio_rol',
                (SELECT nombre FROM roles WHERE id_rol = OLD.id_rol),
                (SELECT nombre FROM roles WHERE id_rol = NEW.id_rol),
                USER(), @jaulares_id_usuario);
    END IF;
    IF NEW.activo <> OLD.activo THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_anterior, valor_nuevo,
                               usuario_bd, id_usuario_app)
        VALUES ('usuarios', NEW.id_usuario, 'cambio_estado', OLD.activo, NEW.activo,
                USER(), @jaulares_id_usuario);
    END IF;
    IF NEW.password_hash <> OLD.password_hash THEN
        INSERT INTO auditoria (tabla, id_registro, accion, usuario_bd, id_usuario_app)
        VALUES ('usuarios', NEW.id_usuario, 'cambio_password', USER(), @jaulares_id_usuario);
    END IF;
    IF NEW.bloqueado_hasta IS NOT NULL
       AND (OLD.bloqueado_hasta IS NULL OR NEW.bloqueado_hasta <> OLD.bloqueado_hasta) THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_nuevo, usuario_bd)
        VALUES ('usuarios', NEW.id_usuario, 'bloqueo_cuenta', NEW.bloqueado_hasta, USER());
    END IF;
END$$

-- no deja editar ni borrar la auditoria
CREATE TRIGGER trg_auditoria_bu BEFORE UPDATE ON auditoria
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La auditoría no se puede modificar.';
END$$

CREATE TRIGGER trg_auditoria_bd BEFORE DELETE ON auditoria
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La auditoría no se puede borrar.';
END$$

-- no deja editar ni borrar la bitacora
CREATE TRIGGER trg_bitacora_bu BEFORE UPDATE ON bitacora_inventario
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La bitácora de inventario no se puede modificar.';
END$$

CREATE TRIGGER trg_bitacora_bd BEFORE DELETE ON bitacora_inventario
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La bitácora de inventario no se puede borrar.';
END$$

-- productos

-- si se desactiva un producto lo saca de los carritos
CREATE TRIGGER trg_productos_au AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
    IF OLD.activo = 1 AND NEW.activo = 0 THEN
        DELETE cd
          FROM carrito_detalle cd
          JOIN carritos c ON c.id_carrito = cd.id_carrito
         WHERE cd.id_producto = NEW.id_producto
           AND c.estado = 'Activo';
    END IF;

    -- guarda el cambio de precio o estado en auditoria
    IF NEW.precio <> OLD.precio THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_anterior, valor_nuevo,
                               usuario_bd, id_usuario_app)
        VALUES ('productos', NEW.id_producto, 'cambio_precio', OLD.precio, NEW.precio,
                USER(), @jaulares_id_usuario);
    END IF;
    IF NEW.activo <> OLD.activo THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_anterior, valor_nuevo,
                               usuario_bd, id_usuario_app)
        VALUES ('productos', NEW.id_producto, 'cambio_estado', OLD.activo, NEW.activo,
                USER(), @jaulares_id_usuario);
    END IF;
END$$

-- carrito: revisa que la cantidad no pase del stock

CREATE TRIGGER trg_carrito_detalle_bi BEFORE INSERT ON carrito_detalle
FOR EACH ROW
BEGIN
    IF NEW.cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cantidad debe ser mayor a cero.';
    END IF;
    IF NEW.cantidad > fn_stock_disponible(NEW.id_producto) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay suficiente stock para esa cantidad.';
    END IF;
END$$

CREATE TRIGGER trg_carrito_detalle_bu BEFORE UPDATE ON carrito_detalle
FOR EACH ROW
BEGIN
    IF NEW.cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cantidad debe ser mayor a cero.';
    END IF;
    IF NEW.cantidad > fn_stock_disponible(NEW.id_producto) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No hay suficiente stock para esa cantidad.';
    END IF;
END$$

-- pedidos

-- calcula el subtotal si no viene, llama a fn_subtotal_linea
CREATE TRIGGER trg_pedido_detalle_bi BEFORE INSERT ON pedido_detalle
FOR EACH ROW
BEGIN
    IF NEW.subtotal IS NULL OR NEW.subtotal = 0 THEN
        SET NEW.subtotal = fn_subtotal_linea(NEW.id_producto, NEW.cantidad, NEW.precio_unitario);
    END IF;
END$$

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

-- guarda el cambio en auditoria y actualiza la entrega
CREATE TRIGGER trg_pedidos_au AFTER UPDATE ON pedidos
FOR EACH ROW
BEGIN
    IF NEW.estado <> OLD.estado THEN
        INSERT INTO auditoria (tabla, id_registro, accion, valor_anterior, valor_nuevo,
                               usuario_bd, id_usuario_app)
        VALUES ('pedidos', NEW.id_pedido, 'cambio_estado', OLD.estado, NEW.estado,
                USER(), @jaulares_id_usuario);

        UPDATE entregas
           SET estado = CASE NEW.estado
                            WHEN 'En camino' THEN 'En camino'
                            WHEN 'Entregado' THEN 'Entregada'
                            WHEN 'Cancelado' THEN 'Cancelada'
                            ELSE estado
                        END
         WHERE id_pedido = NEW.id_pedido;
    END IF;
END$$

-- promociones: revisa fechas y descuento

CREATE TRIGGER trg_promociones_bi BEFORE INSERT ON promociones
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin < NEW.fecha_inicio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha final no puede ser antes de la inicial.';
    END IF;
    IF NEW.tipo = 'Porcentaje' AND (NEW.descuento <= 0 OR NEW.descuento >= 100) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El descuento debe estar entre 1 y 99 %.';
    END IF;
    IF NEW.tipo = '2x1' THEN
        SET NEW.descuento = 0;
    END IF;
END$$

CREATE TRIGGER trg_promociones_bu BEFORE UPDATE ON promociones
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin < NEW.fecha_inicio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha final no puede ser antes de la inicial.';
    END IF;
    IF NEW.tipo = 'Porcentaje' AND (NEW.descuento <= 0 OR NEW.descuento >= 100) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El descuento debe estar entre 1 y 99 %.';
    END IF;
    IF NEW.tipo = '2x1' THEN
        SET NEW.descuento = 0;
    END IF;
END$$

DELIMITER ;


-- 4. procedimientos
-- cliente = lo usa la pagina del cliente, admin = solo el panel admin
-- los del cliente reciben el id del usuario para que solo vea sus datos

DELIMITER $$

-- usuarios

-- cliente: registra un cliente nuevo
CREATE PROCEDURE sp_usuario_registrar(
    IN p_nombre VARCHAR(60), IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(120), IN p_telefono VARCHAR(20),
    IN p_password_hash VARCHAR(255))
BEGIN
    IF EXISTS (SELECT 1 FROM usuarios WHERE correo = LOWER(TRIM(p_correo))) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El correo ya está en uso';
    END IF;

    INSERT INTO usuarios (id_rol, nombre, apellidos, correo, telefono, password_hash)
    VALUES ((SELECT id_rol FROM roles WHERE nombre = 'Cliente'),
            TRIM(p_nombre), TRIM(p_apellidos), p_correo, p_telefono, p_password_hash);

    SELECT LAST_INSERT_ID() AS id_usuario;
END$$

-- admin: crea otro administrador, llama a fn_es_admin
CREATE PROCEDURE sp_usuario_registrar_admin(
    IN p_id_admin INT,
    IN p_nombre VARCHAR(60), IN p_apellidos VARCHAR(100),
    IN p_correo VARCHAR(120), IN p_telefono VARCHAR(20),
    IN p_password_hash VARCHAR(255))
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    IF EXISTS (SELECT 1 FROM usuarios WHERE correo = LOWER(TRIM(p_correo))) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El correo ya está en uso';
    END IF;

    INSERT INTO usuarios (id_rol, nombre, apellidos, correo, telefono, password_hash)
    VALUES ((SELECT id_rol FROM roles WHERE nombre = 'Administrador'),
            TRIM(p_nombre), TRIM(p_apellidos), p_correo, p_telefono, p_password_hash);

    SELECT LAST_INSERT_ID() AS id_usuario;
END$$

-- cliente: trae el usuario por correo para el login, php revisa la contrasenna
CREATE PROCEDURE sp_usuario_login(IN p_correo VARCHAR(120))
BEGIN
    SELECT u.id_usuario, u.nombre, u.apellidos, r.nombre AS rol,
           u.password_hash, u.activo,
           (u.bloqueado_hasta IS NOT NULL AND u.bloqueado_hasta > NOW()) AS bloqueado,
           u.bloqueado_hasta
      FROM usuarios u
      JOIN roles r ON r.id_rol = u.id_rol
     WHERE u.correo = LOWER(TRIM(p_correo));
END$$

-- cliente: suma un intento fallido, a los 3 bloquea la cuenta 15 minutos
CREATE PROCEDURE sp_usuario_login_fallido(IN p_correo VARCHAR(120))
BEGIN
    DECLARE v_id_usuario INT;
    DECLARE v_intentos INT;

    SET v_id_usuario = (SELECT id_usuario FROM usuarios
                         WHERE correo = LOWER(TRIM(p_correo)));

    IF v_id_usuario IS NOT NULL THEN
        UPDATE usuarios
           SET intentos_fallidos = intentos_fallidos + 1
         WHERE id_usuario = v_id_usuario;

        SET v_intentos = (SELECT intentos_fallidos FROM usuarios
                           WHERE id_usuario = v_id_usuario);

        IF v_intentos >= 3 THEN
            UPDATE usuarios
               SET bloqueado_hasta = NOW() + INTERVAL 15 MINUTE,
                   intentos_fallidos = 0
             WHERE id_usuario = v_id_usuario;
        END IF;
    END IF;
END$$

-- cliente: reinicia los intentos cuando el login sale bien
CREATE PROCEDURE sp_usuario_login_exitoso(IN p_id_usuario INT)
BEGIN
    UPDATE usuarios
       SET intentos_fallidos = 0, bloqueado_hasta = NULL
     WHERE id_usuario = p_id_usuario;
END$$

-- admin: desbloquea una cuenta
CREATE PROCEDURE sp_usuario_desbloquear(IN p_id_admin INT, IN p_id_usuario INT)
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    UPDATE usuarios
       SET intentos_fallidos = 0, bloqueado_hasta = NULL
     WHERE id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: revisa si el correo ya existe
CREATE PROCEDURE sp_usuario_consultar_correo(IN p_correo VARCHAR(120))
BEGIN
    SELECT EXISTS (SELECT 1 FROM usuarios
                    WHERE correo = LOWER(TRIM(p_correo))) AS existe;
END$$

-- cliente: trae los datos del usuario para el perfil
CREATE PROCEDURE sp_usuario_obtener(IN p_id_usuario INT)
BEGIN
    SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, u.telefono,
           u.password_hash, r.nombre AS rol, u.activo, u.fecha_registro
      FROM usuarios u
      JOIN roles r ON r.id_rol = u.id_rol
     WHERE u.id_usuario = p_id_usuario;
END$$

-- cliente: actualiza el perfil
CREATE PROCEDURE sp_usuario_actualizar(
    IN p_id_usuario INT, IN p_nombre VARCHAR(60),
    IN p_apellidos VARCHAR(100), IN p_telefono VARCHAR(20))
BEGIN
    UPDATE usuarios
       SET nombre = TRIM(p_nombre), apellidos = TRIM(p_apellidos),
           telefono = p_telefono
     WHERE id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: guarda la nueva contrasenna
CREATE PROCEDURE sp_usuario_cambiar_password(
    IN p_id_usuario INT, IN p_password_hash VARCHAR(255))
BEGIN
    UPDATE usuarios SET password_hash = p_password_hash
     WHERE id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: lista los usuarios
CREATE PROCEDURE sp_usuario_listar()
BEGIN
    SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, u.telefono,
           r.nombre AS rol, u.activo, u.fecha_registro,
           (u.bloqueado_hasta IS NOT NULL AND u.bloqueado_hasta > NOW()) AS bloqueado
      FROM usuarios u
      JOIN roles r ON r.id_rol = u.id_rol
     ORDER BY u.fecha_registro DESC;
END$$

-- admin: activa o desactiva una cuenta
CREATE PROCEDURE sp_usuario_cambiar_estado(
    IN p_id_admin INT, IN p_id_usuario INT, IN p_activo TINYINT)
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    IF p_id_admin = p_id_usuario AND p_activo = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede desactivar su propia cuenta.';
    END IF;
    SET @jaulares_id_usuario = p_id_admin;
    UPDATE usuarios SET activo = p_activo WHERE id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
    SET @jaulares_id_usuario = NULL;
END$$

-- admin: cambia el rol de un usuario
CREATE PROCEDURE sp_usuario_cambiar_rol(
    IN p_id_admin INT, IN p_id_usuario INT, IN p_id_rol INT)
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    IF p_id_admin = p_id_usuario THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No puede cambiar su propio rol.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM roles WHERE id_rol = p_id_rol) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El rol no existe.';
    END IF;
    SET @jaulares_id_usuario = p_id_admin;
    UPDATE usuarios SET id_rol = p_id_rol WHERE id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
    SET @jaulares_id_usuario = NULL;
END$$

-- admin: lista los roles
CREATE PROCEDURE sp_rol_listar()
BEGIN
    SELECT id_rol, nombre FROM roles ORDER BY id_rol;
END$$

-- recuperar contrasenna

-- cliente: guarda el token para recuperar la contrasenna, vence en 30 minutos
CREATE PROCEDURE sp_recuperacion_crear(
    IN p_correo VARCHAR(120), IN p_token VARCHAR(255))
BEGIN
    DECLARE v_id_usuario INT;

    SET v_id_usuario = (SELECT id_usuario FROM usuarios
                         WHERE correo = LOWER(TRIM(p_correo)) AND activo = 1);

    IF v_id_usuario IS NOT NULL THEN
        -- anula los tokens viejos
        UPDATE recuperacion_password SET utilizado = 1
         WHERE id_usuario = v_id_usuario AND utilizado = 0;

        INSERT INTO recuperacion_password (id_usuario, token, vence_en)
        VALUES (v_id_usuario, SHA2(p_token, 256), NOW() + INTERVAL 30 MINUTE);

        SELECT u.id_usuario, u.nombre, u.correo, p_token AS token
          FROM usuarios u WHERE u.id_usuario = v_id_usuario;
    END IF;
END$$

-- cliente: revisa que el token sirva
CREATE PROCEDURE sp_recuperacion_validar(IN p_token VARCHAR(255))
BEGIN
    SELECT rp.id_recuperacion, rp.id_usuario, u.correo
      FROM recuperacion_password rp
      JOIN usuarios u ON u.id_usuario = rp.id_usuario
     WHERE rp.token = SHA2(p_token, 256)
       AND rp.utilizado = 0
       AND rp.vence_en > NOW()
       AND u.activo = 1;
END$$

-- cliente: cambia la contrasenna y marca el token como usado
CREATE PROCEDURE sp_recuperacion_usar(
    IN p_token VARCHAR(255), IN p_password_hash VARCHAR(255))
BEGIN
    DECLARE v_id_recuperacion INT;
    DECLARE v_id_usuario INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_recuperacion, id_usuario
      INTO v_id_recuperacion, v_id_usuario
      FROM recuperacion_password
     WHERE token = SHA2(p_token, 256) AND utilizado = 0 AND vence_en > NOW()
     LIMIT 1
       FOR UPDATE;

    IF v_id_recuperacion IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El enlace venció o ya fue usado. Solicite uno nuevo.';
    END IF;

    UPDATE usuarios
       SET password_hash = p_password_hash, intentos_fallidos = 0, bloqueado_hasta = NULL
     WHERE id_usuario = v_id_usuario;
    UPDATE recuperacion_password SET utilizado = 1
     WHERE id_recuperacion = v_id_recuperacion;

    COMMIT;
    SELECT v_id_usuario AS id_usuario;
END$$

-- categorias

-- admin: crea una categoria
CREATE PROCEDURE sp_categoria_insertar(
    IN p_nombre VARCHAR(80), IN p_descripcion VARCHAR(255))
BEGIN
    IF EXISTS (SELECT 1 FROM categorias WHERE nombre = TRIM(p_nombre)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe una categoría con ese nombre.';
    END IF;
    INSERT INTO categorias (nombre, descripcion) VALUES (TRIM(p_nombre), p_descripcion);
    SELECT LAST_INSERT_ID() AS id_categoria;
END$$

-- admin: edita una categoria
CREATE PROCEDURE sp_categoria_actualizar(
    IN p_id_categoria INT, IN p_nombre VARCHAR(80), IN p_descripcion VARCHAR(255))
BEGIN
    IF EXISTS (SELECT 1 FROM categorias
                WHERE nombre = TRIM(p_nombre) AND id_categoria <> p_id_categoria) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe una categoría con ese nombre.';
    END IF;
    UPDATE categorias SET nombre = TRIM(p_nombre), descripcion = p_descripcion
     WHERE id_categoria = p_id_categoria;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: activa o desactiva una categoria
CREATE PROCEDURE sp_categoria_cambiar_estado(IN p_id_categoria INT, IN p_activo TINYINT)
BEGIN
    UPDATE categorias SET activo = p_activo WHERE id_categoria = p_id_categoria;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: desactiva una categoria, llama a sp_categoria_cambiar_estado
CREATE PROCEDURE sp_categoria_desactivar(IN p_id_categoria INT)
BEGIN
    CALL sp_categoria_cambiar_estado(p_id_categoria, 0);
END$$

-- admin: lista las categorias con cuantos productos tienen
CREATE PROCEDURE sp_categoria_listar()
BEGIN
    SELECT c.id_categoria, c.nombre, c.descripcion, c.activo,
           COUNT(p.id_producto) AS total_productos
      FROM categorias c
      LEFT JOIN productos p ON p.id_categoria = c.id_categoria
     GROUP BY c.id_categoria, c.nombre, c.descripcion, c.activo
     ORDER BY c.nombre;
END$$

-- cliente: categorias activas para el menu
CREATE PROCEDURE sp_categoria_listar_activas()
BEGIN
    SELECT id_categoria, nombre, descripcion
      FROM categorias WHERE activo = 1 ORDER BY nombre;
END$$

-- admin: trae una categoria para editarla
CREATE PROCEDURE sp_categoria_obtener(IN p_id_categoria INT)
BEGIN
    SELECT id_categoria, nombre, descripcion, activo
      FROM categorias WHERE id_categoria = p_id_categoria;
END$$

-- productos e inventario

-- admin: crea el producto y su inventario
CREATE PROCEDURE sp_producto_insertar(
    IN p_id_admin INT, IN p_id_categoria INT, IN p_nombre VARCHAR(120),
    IN p_descripcion TEXT, IN p_precio DECIMAL(10,2), IN p_imagen VARCHAR(255),
    IN p_stock INT, IN p_stock_minimo INT)
BEGIN
    DECLARE v_id_producto INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;
        RESIGNAL;
    END;

    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    IF p_precio IS NULL OR p_precio <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El precio debe ser mayor a cero.';
    END IF;
    IF p_stock IS NULL OR p_stock < 0 OR p_stock_minimo < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El stock no puede ser negativo.';
    END IF;

    START TRANSACTION;
    SET @jaulares_origen = 'ALTA', @jaulares_id_usuario = p_id_admin;

    INSERT INTO productos (id_categoria, nombre, descripcion, precio, imagen)
    VALUES (p_id_categoria, TRIM(p_nombre), p_descripcion, p_precio, p_imagen);
    SET v_id_producto = LAST_INSERT_ID();

    INSERT INTO inventario (id_producto, stock, stock_minimo)
    VALUES (v_id_producto, p_stock, COALESCE(p_stock_minimo, 5));

    COMMIT;
    SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;
    SELECT v_id_producto AS id_producto;
END$$

-- admin: edita el producto, el stock se cambia con sp_inventario_actualizar
CREATE PROCEDURE sp_producto_actualizar(
    IN p_id_producto INT, IN p_id_categoria INT, IN p_nombre VARCHAR(120),
    IN p_descripcion TEXT, IN p_precio DECIMAL(10,2), IN p_imagen VARCHAR(255))
BEGIN
    IF p_precio IS NULL OR p_precio <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El precio debe ser mayor a cero.';
    END IF;
    UPDATE productos
       SET id_categoria = p_id_categoria,
           nombre       = TRIM(p_nombre),
           descripcion  = p_descripcion,
           precio       = p_precio,
           imagen       = COALESCE(p_imagen, imagen)
     WHERE id_producto = p_id_producto;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: activa o desactiva un producto
CREATE PROCEDURE sp_producto_cambiar_estado(IN p_id_producto INT, IN p_activo TINYINT)
BEGIN
    UPDATE productos SET activo = p_activo WHERE id_producto = p_id_producto;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: desactiva un producto, llama a sp_producto_cambiar_estado
CREATE PROCEDURE sp_producto_desactivar(IN p_id_producto INT)
BEGIN
    CALL sp_producto_cambiar_estado(p_id_producto, 0);
END$$

-- cliente: productos activos para el catalogo
CREATE PROCEDURE sp_producto_listar()
BEGIN
    SELECT p.id_producto, p.nombre, p.descripcion, p.precio,
           fn_precio_final(p.id_producto) AS precio_final,
           fn_tiene_2x1(p.id_producto)    AS es_2x1,
           p.imagen, c.id_categoria, c.nombre AS categoria,
           fn_stock_disponible(p.id_producto) AS stock
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.activo = 1 AND c.activo = 1
     ORDER BY p.nombre;
END$$

-- admin: todos los productos con su stock
CREATE PROCEDURE sp_producto_listar_admin()
BEGIN
    SELECT p.id_producto, p.nombre, c.nombre AS categoria, p.precio,
           i.stock, i.stock_minimo, (i.stock <= i.stock_minimo) AS stock_bajo,
           p.imagen, p.activo
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
      LEFT JOIN inventario i ON i.id_producto = p.id_producto
     ORDER BY p.nombre;
END$$

-- cliente: productos de una categoria
CREATE PROCEDURE sp_producto_por_categoria(IN p_id_categoria INT)
BEGIN
    SELECT p.id_producto, p.nombre, p.descripcion, p.precio,
           fn_precio_final(p.id_producto) AS precio_final,
           fn_tiene_2x1(p.id_producto)    AS es_2x1,
           p.imagen, fn_stock_disponible(p.id_producto) AS stock
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.id_categoria = p_id_categoria
       AND p.activo = 1 AND c.activo = 1
     ORDER BY p.nombre;
END$$

-- cliente: busca productos por nombre, categoria y orden de precio
CREATE PROCEDURE sp_producto_buscar(
    IN p_texto VARCHAR(120), IN p_id_categoria INT, IN p_orden VARCHAR(4))
BEGIN
    SELECT p.id_producto, p.nombre, p.descripcion, p.precio,
           fn_precio_final(p.id_producto) AS precio_final,
           fn_tiene_2x1(p.id_producto)    AS es_2x1,
           p.imagen, c.id_categoria, c.nombre AS categoria,
           fn_stock_disponible(p.id_producto) AS stock
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.activo = 1 AND c.activo = 1
       AND p.nombre LIKE CONCAT('%', COALESCE(TRIM(p_texto), ''), '%')
       AND (p_id_categoria IS NULL OR p_id_categoria = 0
            OR p.id_categoria = p_id_categoria)
     ORDER BY
       CASE WHEN UPPER(p_orden) = 'ASC'  THEN fn_precio_final(p.id_producto) END ASC,
       CASE WHEN UPPER(p_orden) = 'DESC' THEN fn_precio_final(p.id_producto) END DESC,
       p.nombre;
END$$

-- cliente: detalle de un producto
CREATE PROCEDURE sp_producto_detalle(IN p_id_producto INT)
BEGIN
    SELECT p.id_producto, p.nombre, p.descripcion, p.precio,
           fn_precio_final(p.id_producto) AS precio_final,
           fn_tiene_2x1(p.id_producto)    AS es_2x1,
           p.imagen, c.id_categoria, c.nombre AS categoria,
           fn_stock_disponible(p.id_producto) AS stock
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.id_producto = p_id_producto AND p.activo = 1;
END$$

-- admin: trae un producto para editarlo
CREATE PROCEDURE sp_producto_obtener(IN p_id_producto INT)
BEGIN
    SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion, p.precio,
           p.imagen, p.activo, i.stock, i.stock_minimo
      FROM productos p
      LEFT JOIN inventario i ON i.id_producto = p.id_producto
     WHERE p.id_producto = p_id_producto;
END$$

-- cliente: productos mas vendidos para la pagina de inicio
CREATE PROCEDURE sp_producto_destacados(IN p_limite INT)
BEGIN
    SELECT p.id_producto, p.nombre, p.precio,
           fn_precio_final(p.id_producto) AS precio_final,
           fn_tiene_2x1(p.id_producto)    AS es_2x1,
           p.imagen, c.nombre AS categoria,
           COALESCE(SUM(CASE WHEN pe.id_pedido IS NOT NULL
                             THEN pd.cantidad END), 0) AS vendidos
      FROM productos p
      JOIN categorias c ON c.id_categoria = p.id_categoria
      LEFT JOIN pedido_detalle pd ON pd.id_producto = p.id_producto
      LEFT JOIN pedidos pe ON pe.id_pedido = pd.id_pedido AND pe.estado <> 'Cancelado'
     WHERE p.activo = 1 AND c.activo = 1
     GROUP BY p.id_producto, p.nombre, p.precio, p.imagen, c.nombre
     ORDER BY vendidos DESC, p.id_producto DESC
     LIMIT p_limite;
END$$

-- admin: cambia el stock de un producto
CREATE PROCEDURE sp_inventario_actualizar(
    IN p_id_admin INT, IN p_id_producto INT, IN p_stock INT, IN p_stock_minimo INT)
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Solo los administradores pueden modificar el stock.';
    END IF;
    IF p_stock IS NULL OR p_stock < 0 OR p_stock_minimo < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El stock no puede ser negativo.';
    END IF;

    SET @jaulares_origen = 'AJUSTE', @jaulares_id_usuario = p_id_admin;
    UPDATE inventario
       SET stock = p_stock,
           stock_minimo = COALESCE(p_stock_minimo, stock_minimo)
     WHERE id_producto = p_id_producto;
    SELECT ROW_COUNT() AS filas;
    SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;
END$$

-- cliente: revisa si hay stock suficiente
CREATE PROCEDURE sp_inventario_validar_stock(IN p_id_producto INT, IN p_cantidad INT)
BEGIN
    SELECT fn_stock_disponible(p_id_producto) AS stock,
           (fn_stock_disponible(p_id_producto) >= p_cantidad) AS disponible;
END$$

-- admin: productos con poco stock
CREATE PROCEDURE sp_inventario_stock_bajo()
BEGIN
    SELECT p.id_producto, p.nombre, i.stock, i.stock_minimo
      FROM inventario i
      JOIN productos p ON p.id_producto = i.id_producto
     WHERE i.stock <= i.stock_minimo AND p.activo = 1
     ORDER BY i.stock;
END$$

-- admin: historial de stock de un producto, 0 trae todos
CREATE PROCEDURE sp_inventario_bitacora(IN p_id_producto INT)
BEGIN
    SELECT b.id_bitacora, b.id_producto, p.nombre AS producto,
           b.stock_anterior, b.stock_nuevo, b.motivo, b.usuario_bd,
           fn_nombre_completo(b.id_usuario_app) AS usuario_app, b.fecha
      FROM bitacora_inventario b
      JOIN productos p ON p.id_producto = b.id_producto
     WHERE p_id_producto IS NULL OR p_id_producto = 0 OR b.id_producto = p_id_producto
     ORDER BY b.fecha DESC, b.id_bitacora DESC;
END$$

-- carrito

-- cliente: trae el carrito del usuario o lo crea
CREATE PROCEDURE sp_carrito_obtener_o_crear(IN p_id_usuario INT)
BEGIN
    DECLARE v_id_carrito INT;

    SET v_id_carrito = (SELECT id_carrito FROM carritos
                         WHERE id_usuario = p_id_usuario AND estado = 'Activo'
                         ORDER BY id_carrito DESC LIMIT 1);
    IF v_id_carrito IS NULL THEN
        INSERT INTO carritos (id_usuario) VALUES (p_id_usuario);
        SET v_id_carrito = LAST_INSERT_ID();
    END IF;

    SELECT v_id_carrito AS id_carrito;
END$$

-- cliente: agrega un producto, si ya esta suma la cantidad
CREATE PROCEDURE sp_carrito_agregar(
    IN p_id_usuario INT, IN p_id_producto INT, IN p_cantidad INT)
BEGIN
    DECLARE v_id_carrito INT;

    IF NOT EXISTS (SELECT 1 FROM productos WHERE id_producto = p_id_producto AND activo = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El producto no está disponible.';
    END IF;

    SET v_id_carrito = (SELECT id_carrito FROM carritos
                         WHERE id_usuario = p_id_usuario AND estado = 'Activo'
                         ORDER BY id_carrito DESC LIMIT 1);
    IF v_id_carrito IS NULL THEN
        INSERT INTO carritos (id_usuario) VALUES (p_id_usuario);
        SET v_id_carrito = LAST_INSERT_ID();
    END IF;

    -- el trigger revisa que haya stock
    INSERT INTO carrito_detalle (id_carrito, id_producto, cantidad, precio_unitario)
    VALUES (v_id_carrito, p_id_producto, p_cantidad, fn_precio_final(p_id_producto))
    ON DUPLICATE KEY UPDATE
        cantidad = cantidad + VALUES(cantidad),
        precio_unitario = VALUES(precio_unitario);

    SELECT COALESCE(SUM(cantidad), 0) AS articulos
      FROM carrito_detalle WHERE id_carrito = v_id_carrito;
END$$

-- cliente: cambia la cantidad de un producto
CREATE PROCEDURE sp_carrito_actualizar_cantidad(
    IN p_id_usuario INT, IN p_id_detalle INT, IN p_cantidad INT)
BEGIN
    UPDATE carrito_detalle cd
      JOIN carritos c ON c.id_carrito = cd.id_carrito
       SET cd.cantidad = p_cantidad
     WHERE cd.id_detalle_carrito = p_id_detalle
       AND c.id_usuario = p_id_usuario
       AND c.estado = 'Activo';
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: quita un producto del carrito
CREATE PROCEDURE sp_carrito_eliminar_item(IN p_id_usuario INT, IN p_id_detalle INT)
BEGIN
    DELETE cd
      FROM carrito_detalle cd
      JOIN carritos c ON c.id_carrito = cd.id_carrito
     WHERE cd.id_detalle_carrito = p_id_detalle
       AND c.id_usuario = p_id_usuario
       AND c.estado = 'Activo';
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: vacia el carrito
CREATE PROCEDURE sp_carrito_vaciar(IN p_id_usuario INT)
BEGIN
    DELETE cd
      FROM carrito_detalle cd
      JOIN carritos c ON c.id_carrito = cd.id_carrito
     WHERE c.id_usuario = p_id_usuario AND c.estado = 'Activo';
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: lista el carrito con subtotales y total
CREATE PROCEDURE sp_carrito_listar(IN p_id_usuario INT)
BEGIN
    SELECT cd.id_detalle_carrito, p.id_producto, p.nombre, p.imagen,
           cd.precio_unitario, cd.cantidad,
           fn_subtotal_linea(cd.id_producto, cd.cantidad, cd.precio_unitario) AS subtotal,
           (cd.cantidad * cd.precio_unitario)
             - fn_subtotal_linea(cd.id_producto, cd.cantidad, cd.precio_unitario) AS descuento_2x1,
           fn_stock_disponible(p.id_producto) AS stock,
           fn_total_carrito(c.id_carrito) AS total
      FROM carritos c
      JOIN carrito_detalle cd ON cd.id_carrito = c.id_carrito
      JOIN productos p ON p.id_producto = cd.id_producto
     WHERE c.id_usuario = p_id_usuario AND c.estado = 'Activo'
     ORDER BY cd.id_detalle_carrito;
END$$

-- cliente: cantidad de articulos para el icono del carrito
CREATE PROCEDURE sp_carrito_contar(IN p_id_usuario INT)
BEGIN
    SELECT COALESCE(SUM(cd.cantidad), 0) AS articulos
      FROM carritos c
      JOIN carrito_detalle cd ON cd.id_carrito = c.id_carrito
     WHERE c.id_usuario = p_id_usuario AND c.estado = 'Activo';
END$$

-- cliente: antes de pagar revisa el stock de cada producto y ajusta el carrito
CREATE PROCEDURE sp_carrito_revalidar(IN p_id_usuario INT)
BEGIN
    DECLARE v_fin INT DEFAULT 0;
    DECLARE v_id_detalle INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_stock INT;
    DECLARE v_activo TINYINT;

    DECLARE cur_lineas CURSOR FOR
        SELECT cd.id_detalle_carrito, cd.id_producto, cd.cantidad, p.activo
          FROM carrito_detalle cd
          JOIN carritos c ON c.id_carrito = cd.id_carrito
          JOIN productos p ON p.id_producto = cd.id_producto
         WHERE c.id_usuario = p_id_usuario AND c.estado = 'Activo';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin = 1;

    DROP TEMPORARY TABLE IF EXISTS tmp_ajustes;
    CREATE TEMPORARY TABLE tmp_ajustes (
        id_producto INT, producto VARCHAR(120),
        cantidad_anterior INT, cantidad_nueva INT);

    OPEN cur_lineas;
    leer: LOOP
        FETCH cur_lineas INTO v_id_detalle, v_id_producto, v_cantidad, v_activo;
        IF v_fin = 1 THEN LEAVE leer; END IF;

        SET v_stock = IF(v_activo = 1, fn_stock_disponible(v_id_producto), 0);

        IF v_cantidad > v_stock THEN
            IF v_stock = 0 THEN
                DELETE FROM carrito_detalle WHERE id_detalle_carrito = v_id_detalle;
            ELSE
                UPDATE carrito_detalle SET cantidad = v_stock
                 WHERE id_detalle_carrito = v_id_detalle;
            END IF;
            INSERT INTO tmp_ajustes
            VALUES (v_id_producto,
                    (SELECT nombre FROM productos WHERE id_producto = v_id_producto),
                    v_cantidad, v_stock);
        END IF;
    END LOOP;
    CLOSE cur_lineas;

    SELECT * FROM tmp_ajustes;
    DROP TEMPORARY TABLE tmp_ajustes;
END$$

-- direcciones

-- cliente: agrega una direccion
CREATE PROCEDURE sp_direccion_insertar(
    IN p_id_usuario INT, IN p_provincia VARCHAR(50), IN p_canton VARCHAR(50),
    IN p_distrito VARCHAR(50), IN p_detalle VARCHAR(255))
BEGIN
    INSERT INTO direcciones (id_usuario, provincia, canton, distrito, detalle)
    VALUES (p_id_usuario, p_provincia, p_canton, p_distrito, p_detalle);
    SELECT LAST_INSERT_ID() AS id_direccion;
END$$

-- cliente: edita una direccion
CREATE PROCEDURE sp_direccion_actualizar(
    IN p_id_usuario INT, IN p_id_direccion INT, IN p_provincia VARCHAR(50),
    IN p_canton VARCHAR(50), IN p_distrito VARCHAR(50), IN p_detalle VARCHAR(255))
BEGIN
    UPDATE direcciones
       SET provincia = p_provincia, canton = p_canton,
           distrito = p_distrito, detalle = p_detalle
     WHERE id_direccion = p_id_direccion AND id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: desactiva una direccion, no se borra por los pedidos viejos
CREATE PROCEDURE sp_direccion_eliminar(IN p_id_usuario INT, IN p_id_direccion INT)
BEGIN
    UPDATE direcciones SET activo = 0
     WHERE id_direccion = p_id_direccion AND id_usuario = p_id_usuario;
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: lista las direcciones
CREATE PROCEDURE sp_direccion_listar(IN p_id_usuario INT)
BEGIN
    SELECT id_direccion, provincia, canton, distrito, detalle
      FROM direcciones
     WHERE id_usuario = p_id_usuario AND activo = 1
     ORDER BY id_direccion;
END$$

-- pedidos y entregas

-- admin: agrega una linea a un pedido a mano
CREATE PROCEDURE sp_pedido_detalle_insertar(
    IN p_id_pedido INT, IN p_id_producto INT, IN p_cantidad INT,
    IN p_precio_unitario DECIMAL(10,2))
BEGIN
    INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (p_id_pedido, p_id_producto, p_cantidad, p_precio_unitario, 0);
    UPDATE pedidos
       SET total = (SELECT SUM(subtotal) FROM pedido_detalle WHERE id_pedido = p_id_pedido)
     WHERE id_pedido = p_id_pedido;
    SELECT LAST_INSERT_ID() AS id_detalle_pedido;
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

-- cliente: historial de pedidos
CREATE PROCEDURE sp_pedido_listar_cliente(IN p_id_usuario INT)
BEGIN
    SELECT p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.total,
           e.fecha_entrega, e.franja_horaria,
           CONCAT(d.provincia, ', ', d.canton, ', ', d.distrito) AS direccion
      FROM pedidos p
      LEFT JOIN entregas e ON e.id_pedido = p.id_pedido
      JOIN direcciones d ON d.id_direccion = p.id_direccion
     WHERE p.id_usuario = p_id_usuario
     ORDER BY p.fecha_pedido DESC;
END$$

-- cliente: productos de un pedido
CREATE PROCEDURE sp_pedido_detalle_listar(IN p_id_usuario INT, IN p_id_pedido INT)
BEGIN
    SELECT pd.id_detalle_pedido, pd.id_producto, pr.nombre, pr.imagen,
           pd.cantidad, pd.precio_unitario, pd.subtotal
      FROM pedido_detalle pd
      JOIN pedidos p ON p.id_pedido = pd.id_pedido
      JOIN productos pr ON pr.id_producto = pd.id_producto
     WHERE pd.id_pedido = p_id_pedido
       AND (p.id_usuario = p_id_usuario OR fn_es_admin(p_id_usuario));
END$$

-- admin: lista pedidos con filtros
CREATE PROCEDURE sp_pedido_listar_admin(
    IN p_estado VARCHAR(30), IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.total,
           u.id_usuario, fn_nombre_completo(u.id_usuario) AS cliente, u.correo,
           e.fecha_entrega, e.franja_horaria
      FROM pedidos p
      JOIN usuarios u ON u.id_usuario = p.id_usuario
      LEFT JOIN entregas e ON e.id_pedido = p.id_pedido
     WHERE (p_estado IS NULL OR p_estado = '' OR p.estado = p_estado)
       AND (p_desde IS NULL OR DATE(p.fecha_pedido) >= p_desde)
       AND (p_hasta IS NULL OR DATE(p.fecha_pedido) <= p_hasta)
     ORDER BY p.fecha_pedido DESC;
END$$

-- admin: cambia el estado del pedido, si se cancela devuelve el stock
CREATE PROCEDURE sp_pedido_cambiar_estado(
    IN p_id_admin INT, IN p_id_pedido INT, IN p_estado VARCHAR(30))
BEGIN
    DECLARE v_fin INT DEFAULT 0;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;

    DECLARE cur_detalle CURSOR FOR
        SELECT id_producto, cantidad FROM pedido_detalle
         WHERE id_pedido = p_id_pedido ORDER BY id_producto;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;
        RESIGNAL;
    END;

    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pedidos WHERE id_pedido = p_id_pedido) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El pedido no existe.';
    END IF;

    START TRANSACTION;
    SET @jaulares_id_usuario = p_id_admin;

    -- el trigger trg_pedidos_bu revisa que el cambio sea valido
    UPDATE pedidos SET estado = p_estado WHERE id_pedido = p_id_pedido;

    IF p_estado = 'Cancelado' THEN
        SET @jaulares_origen = 'CANCELACION', @jaulares_id_usuario = p_id_admin;
        OPEN cur_detalle;
        devolver: LOOP
            FETCH cur_detalle INTO v_id_producto, v_cantidad;
            IF v_fin = 1 THEN LEAVE devolver; END IF;
            UPDATE inventario SET stock = stock + v_cantidad
             WHERE id_producto = v_id_producto;
        END LOOP;
        CLOSE cur_detalle;
    END IF;

    COMMIT;
    SET @jaulares_origen = NULL, @jaulares_id_usuario = NULL;

    SELECT p.id_pedido, p.estado, u.correo, u.nombre
      FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario
     WHERE p.id_pedido = p_id_pedido;
END$$

-- admin: cambia la fecha o la hora de una entrega
CREATE PROCEDURE sp_entrega_agendar(
    IN p_id_pedido INT, IN p_fecha_entrega DATE, IN p_franja_horaria VARCHAR(50))
BEGIN
    IF p_fecha_entrega < CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La fecha de entrega no puede ser en el pasado.';
    END IF;
    IF fn_es_dia_cierre(p_fecha_entrega) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El supermercado está cerrado ese día.';
    END IF;

    INSERT INTO entregas (id_pedido, fecha_entrega, franja_horaria)
    VALUES (p_id_pedido, p_fecha_entrega, p_franja_horaria)
    ON DUPLICATE KEY UPDATE
        fecha_entrega = VALUES(fecha_entrega),
        franja_horaria = VALUES(franja_horaria);
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: entregas para el calendario
CREATE PROCEDURE sp_entrega_calendario(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT e.id_entrega, e.id_pedido, e.fecha_entrega, e.franja_horaria,
           e.estado AS estado_entrega, p.estado AS estado_pedido, p.total,
           fn_nombre_completo(p.id_usuario) AS cliente
      FROM entregas e
      JOIN pedidos p ON p.id_pedido = e.id_pedido
     WHERE e.fecha_entrega BETWEEN p_desde AND p_hasta
     ORDER BY e.fecha_entrega, e.franja_horaria;
END$$

-- cliente: dias cerrados para bloquearlos en el calendario
CREATE PROCEDURE sp_fechas_cierre_listar(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT DATE(fecha_inicio) AS desde, DATE(fecha_fin) AS hasta, titulo
      FROM eventos_calendario
     WHERE tipo = 'Cierre'
       AND DATE(fecha_fin) >= p_desde AND DATE(fecha_inicio) <= p_hasta
     ORDER BY fecha_inicio;
END$$

-- promociones

-- admin: crea una promocion
CREATE PROCEDURE sp_promocion_insertar(
    IN p_nombre VARCHAR(100), IN p_tipo VARCHAR(20), IN p_descuento DECIMAL(5,2),
    IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN
    INSERT INTO promociones (nombre, tipo, descuento, fecha_inicio, fecha_fin)
    VALUES (p_nombre, p_tipo, COALESCE(p_descuento, 0), p_fecha_inicio, p_fecha_fin);
    SELECT LAST_INSERT_ID() AS id_promocion;
END$$

-- admin: edita una promocion
CREATE PROCEDURE sp_promocion_actualizar(
    IN p_id_promocion INT, IN p_nombre VARCHAR(100), IN p_tipo VARCHAR(20),
    IN p_descuento DECIMAL(5,2), IN p_fecha_inicio DATE, IN p_fecha_fin DATE)
BEGIN
    UPDATE promociones
       SET nombre = p_nombre, tipo = p_tipo, descuento = COALESCE(p_descuento, 0),
           fecha_inicio = p_fecha_inicio, fecha_fin = p_fecha_fin
     WHERE id_promocion = p_id_promocion;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: desactiva una promocion
CREATE PROCEDURE sp_promocion_desactivar(IN p_id_promocion INT)
BEGIN
    UPDATE promociones SET activo = 0 WHERE id_promocion = p_id_promocion;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: lista las promociones
CREATE PROCEDURE sp_promocion_listar()
BEGIN
    SELECT pr.id_promocion, pr.nombre, pr.tipo, pr.descuento,
           pr.fecha_inicio, pr.fecha_fin, pr.activo,
           COUNT(pp.id_producto) AS productos,
           (pr.activo = 1 AND CURDATE() BETWEEN pr.fecha_inicio AND pr.fecha_fin) AS vigente
      FROM promociones pr
      LEFT JOIN producto_promocion pp ON pp.id_promocion = pr.id_promocion
     GROUP BY pr.id_promocion, pr.nombre, pr.tipo, pr.descuento,
              pr.fecha_inicio, pr.fecha_fin, pr.activo
     ORDER BY pr.fecha_inicio DESC;
END$$

-- cliente: promociones vigentes para los banners
CREATE PROCEDURE sp_promocion_vigentes()
BEGIN
    SELECT id_promocion, nombre, tipo, descuento, fecha_inicio, fecha_fin
      FROM promociones
     WHERE activo = 1 AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
     ORDER BY fecha_fin;
END$$

-- admin: agrega un producto a una promocion
CREATE PROCEDURE sp_promocion_asignar_producto(IN p_id_promocion INT, IN p_id_producto INT)
BEGIN
    INSERT IGNORE INTO producto_promocion (id_producto, id_promocion)
    VALUES (p_id_producto, p_id_promocion);
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: quita un producto de una promocion
CREATE PROCEDURE sp_promocion_quitar_producto(IN p_id_promocion INT, IN p_id_producto INT)
BEGIN
    DELETE FROM producto_promocion
     WHERE id_promocion = p_id_promocion AND id_producto = p_id_producto;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: vuelve a activar una promocion
CREATE PROCEDURE sp_promocion_activar(IN p_id_promocion INT)
BEGIN
    UPDATE promociones SET activo = 1 WHERE id_promocion = p_id_promocion;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: productos que tiene una promocion
CREATE PROCEDURE sp_promocion_productos(IN p_id_promocion INT)
BEGIN
    SELECT p.id_producto, p.nombre, p.precio, p.imagen, p.activo,
           c.nombre AS categoria
      FROM producto_promocion pp
      JOIN productos p  ON p.id_producto = pp.id_producto
      JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE pp.id_promocion = p_id_promocion
     ORDER BY p.nombre;
END$$

-- admin: desactiva las promociones vencidas
CREATE PROCEDURE sp_promocion_desactivar_vencidas()
BEGIN
    DECLARE v_fin INT DEFAULT 0;
    DECLARE v_id INT;
    DECLARE v_nombre VARCHAR(100);

    DECLARE cur_vencidas CURSOR FOR
        SELECT id_promocion, nombre FROM promociones
         WHERE activo = 1 AND fecha_fin < CURDATE();
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin = 1;

    DROP TEMPORARY TABLE IF EXISTS tmp_vencidas;
    CREATE TEMPORARY TABLE tmp_vencidas (id_promocion INT, nombre VARCHAR(100));

    OPEN cur_vencidas;
    leer: LOOP
        FETCH cur_vencidas INTO v_id, v_nombre;
        IF v_fin = 1 THEN LEAVE leer; END IF;
        UPDATE promociones SET activo = 0 WHERE id_promocion = v_id;
        INSERT INTO tmp_vencidas VALUES (v_id, v_nombre);
    END LOOP;
    CLOSE cur_vencidas;

    SELECT * FROM tmp_vencidas;
    DROP TEMPORARY TABLE tmp_vencidas;
END$$

-- eventos del calendario

-- admin: crea un evento
CREATE PROCEDURE sp_evento_insertar(
    IN p_id_admin INT, IN p_titulo VARCHAR(120), IN p_descripcion VARCHAR(255),
    IN p_tipo VARCHAR(20), IN p_fecha_inicio DATETIME, IN p_fecha_fin DATETIME)
BEGIN
    IF NOT fn_es_admin(p_id_admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Acción permitida solo para administradores.';
    END IF;
    INSERT INTO eventos_calendario
        (id_usuario_creador, titulo, descripcion, tipo, fecha_inicio, fecha_fin)
    VALUES (p_id_admin, p_titulo, p_descripcion, p_tipo, p_fecha_inicio, p_fecha_fin);
    SELECT LAST_INSERT_ID() AS id_evento;
END$$

-- admin: edita un evento
CREATE PROCEDURE sp_evento_actualizar(
    IN p_id_evento INT, IN p_titulo VARCHAR(120), IN p_descripcion VARCHAR(255),
    IN p_tipo VARCHAR(20), IN p_fecha_inicio DATETIME, IN p_fecha_fin DATETIME)
BEGIN
    UPDATE eventos_calendario
       SET titulo = p_titulo, descripcion = p_descripcion, tipo = p_tipo,
           fecha_inicio = p_fecha_inicio, fecha_fin = p_fecha_fin
     WHERE id_evento = p_id_evento;
    SELECT ROW_COUNT() AS filas;
END$$

-- admin: borra un evento
CREATE PROCEDURE sp_evento_eliminar(IN p_id_evento INT)
BEGIN
    DELETE FROM eventos_calendario WHERE id_evento = p_id_evento;
    SELECT ROW_COUNT() AS filas;
END$$

-- cliente: eventos para el calendario
CREATE PROCEDURE sp_evento_listar(IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT id_evento, titulo, descripcion, tipo, fecha_inicio, fecha_fin
      FROM eventos_calendario
     WHERE DATE(fecha_fin) >= p_desde AND DATE(fecha_inicio) <= p_hasta
     ORDER BY fecha_inicio;
END$$

-- reportes

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

-- admin: productos mas vendidos
CREATE PROCEDURE sp_reporte_mas_vendidos(IN p_limite INT)
BEGIN
    SELECT pr.id_producto, pr.nombre,
           SUM(pd.cantidad) AS unidades,
           SUM(pd.subtotal) AS total_vendido
      FROM pedido_detalle pd
      JOIN pedidos p ON p.id_pedido = pd.id_pedido
      JOIN productos pr ON pr.id_producto = pd.id_producto
     WHERE p.estado <> 'Cancelado'
     GROUP BY pr.id_producto, pr.nombre
     ORDER BY unidades DESC
     LIMIT p_limite;
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

-- admin: consulta la auditoria
CREATE PROCEDURE sp_auditoria_listar(IN p_tabla VARCHAR(40), IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT a.id_auditoria, a.tabla, a.id_registro, a.accion,
           a.valor_anterior, a.valor_nuevo, a.usuario_bd,
           fn_nombre_completo(a.id_usuario_app) AS usuario_app, a.fecha
      FROM auditoria a
     WHERE (p_tabla IS NULL OR p_tabla = '' OR a.tabla = p_tabla)
       AND (p_desde IS NULL OR DATE(a.fecha) >= p_desde)
       AND (p_hasta IS NULL OR DATE(a.fecha) <= p_hasta)
     ORDER BY a.fecha DESC, a.id_auditoria DESC;
END$$

-- admin: numeros del dashboard
CREATE PROCEDURE sp_admin_resumen()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM productos WHERE activo = 1)              AS total_productos,
        (SELECT COUNT(*) FROM inventario i
           JOIN productos p ON p.id_producto = i.id_producto
          WHERE p.activo = 1 AND i.stock <= i.stock_minimo)            AS productos_stock_bajo,
        (SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
          WHERE r.nombre = 'Cliente')                                  AS clientes_registrados,
        (SELECT COUNT(*) FROM pedidos
          WHERE DATE(fecha_pedido) = CURDATE())                        AS pedidos_hoy;
END$$

-- errores

-- cliente y admin: guarda el error que atrapa el try catch de php
CREATE PROCEDURE sp_error_registrar(
    IN p_mensaje VARCHAR(4000), IN p_accion VARCHAR(255), IN p_id_usuario INT)
BEGIN
    INSERT INTO errores (mensaje, accion, id_usuario)
    VALUES (p_mensaje, p_accion, p_id_usuario);
END$$

DELIMITER ;


-- 5. datos de prueba
-- admin@jaulares.com clave Admin123, clientes clave Cliente123

INSERT INTO roles (nombre) VALUES ('Administrador'), ('Cliente');

INSERT INTO usuarios (id_rol, nombre, apellidos, correo, telefono, password_hash) VALUES
 (1, 'Admin',  'Los Jaulares',    'admin@jaulares.com',  '22223333',
     '$2y$10$Fgao9A87qFH7Fqy4E2clAenxhfKslwL341nDg5nhlgnIr4wsnEDHS'),
 (2, 'María',  'Rodríguez Mora',  'maria@correo.com',    '88887777',
     '$2y$10$Z7Hul4Gi.kN5xmXv7Q7U8eFj40B6iCjzRWh3PrqSWz1/SDPrTefYq'),
 (2, 'Carlos', 'Jiménez Solano',  'carlos@correo.com',   '87654321',
     '$2y$10$Z7Hul4Gi.kN5xmXv7Q7U8eFj40B6iCjzRWh3PrqSWz1/SDPrTefYq');

INSERT INTO categorias (nombre, descripcion) VALUES
 ('Frutas',     'Frutas frescas de temporada'),
 ('Verduras',   'Verduras y hortalizas frescas'),
 ('Lácteos',    'Leche, quesos, yogures y natillas'),
 ('Abarrotes',  'Granos, aceites, pastas y enlatados'),
 ('Limpieza',   'Productos de limpieza para el hogar'),
 ('Bebidas',    'Jugos, refrescos y agua');

-- imagen: ruta en view/assets/images
INSERT INTO productos (id_categoria, nombre, descripcion, precio, imagen) VALUES
 (1, 'Manzana roja (kg)',        'Manzana roja importada',              1950.00, 'productos/manzana.jpg'),
 (1, 'Banano (kg)',              'Banano nacional',                      650.00, 'productos/banano.jpg'),
 (1, 'Piña',                     'Piña dorada de la zona norte',        1200.00, 'productos/pina.jpg'),
 (1, 'Fresas 500 g',             'Fresas frescas',                      2300.00, 'productos/fresas.jpg'),
 (2, 'Tomate (kg)',              'Tomate de cocina',                    1100.00, 'productos/tomate.jpg'),
 (2, 'Papa (kg)',                'Papa blanca',                          900.00, 'productos/papa.jpg'),
 (2, 'Lechuga americana',        'Lechuga fresca',                       750.00, 'productos/lechuga.jpg'),
 (2, 'Zanahoria (kg)',           'Zanahoria nacional',                   800.00, 'productos/zanahoria.jpg'),
 (3, 'Leche entera 1 L',         'Leche entera semidescremada',          950.00, 'productos/leche.jpg'),
 (3, 'Queso turrialba 500 g',    'Queso fresco tipo Turrialba',         3200.00, 'productos/queso.jpg'),
 (3, 'Yogurt natural 1 L',       'Yogurt natural sin azúcar',           1850.00, 'productos/yogurt.jpg'),
 (3, 'Natilla 250 ml',           'Natilla tradicional',                  850.00, 'productos/natilla.jpg'),
 (4, 'Arroz 99% grano entero 2 kg', 'Arroz blanco',                     2100.00, 'productos/arroz.jpg'),
 (4, 'Frijoles negros 900 g',    'Frijol negro nacional',               1650.00, 'productos/frijoles.jpg'),
 (4, 'Aceite vegetal 1 L',       'Aceite de soya',                      2400.00, 'productos/aceite.jpg'),
 (4, 'Pasta spaghetti 500 g',    'Pasta de trigo',                       850.00, 'productos/pasta.jpg'),
 (5, 'Detergente en polvo 1 kg', 'Detergente para ropa',                2600.00, 'productos/detergente.jpg'),
 (5, 'Cloro 1 L',                'Cloro desinfectante',                  750.00, 'productos/cloro.jpg'),
 (5, 'Lavaplatos en crema 500 g','Lavaplatos aroma limón',              1300.00, 'productos/lavaplatos.jpg'),
 (6, 'Agua 600 ml',              'Agua purificada',                      550.00, 'productos/agua.jpg'),
 (6, 'Jugo de naranja 1 L',      'Jugo natural de naranja',             1500.00, 'productos/jugo.jpg'),
 (6, 'Refresco de cola 2 L',     'Bebida gaseosa',                      1450.00, 'productos/refresco.jpg');

SET @jaulares_origen = 'ALTA';
INSERT INTO inventario (id_producto, stock, stock_minimo) VALUES
 (1, 40, 10), (2, 80, 15), (3, 25, 5),  (4, 3, 5),
 (5, 50, 10), (6, 60, 10), (7, 30, 5),  (8, 45, 10),
 (9, 100,20), (10, 20, 5), (11, 35, 8), (12, 4, 5),
 (13, 70,15), (14, 65,15), (15, 40, 10),(16, 90, 20),
 (17, 30, 8), (18, 55,10), (19, 25, 5), (20, 120, 25),
 (21, 30, 8), (22, 60,10);
SET @jaulares_origen = NULL;

-- promociones de prueba por 60 dias
INSERT INTO promociones (nombre, tipo, descuento, fecha_inicio, fecha_fin) VALUES
 ('Frescura para tu hogar todos los días', 'Porcentaje', 15, CURDATE(), CURDATE() + INTERVAL 60 DAY),
 ('2x1 en productos seleccionados',        '2x1',         0, CURDATE(), CURDATE() + INTERVAL 60 DAY);

INSERT INTO producto_promocion (id_producto, id_promocion) VALUES
 (1, 1), (2, 1), (3, 1), (5, 1), (7, 1),      -- 15 % en frutas y verduras
 (16, 2), (18, 2), (20, 2);                   -- 2x1 en pasta, cloro y agua

INSERT INTO eventos_calendario (id_usuario_creador, titulo, descripcion, tipo, fecha_inicio, fecha_fin) VALUES
 (1, 'Navidad - cerrado', 'El supermercado no abre el 25 de diciembre', 'Cierre',
     CONCAT(YEAR(CURDATE()), '-12-25 00:00:00'), CONCAT(YEAR(CURDATE()), '-12-25 23:59:59')),
 (1, 'Feria del agricultor', 'Frutas y verduras de productores locales', 'Feria',
     CURDATE() + INTERVAL 7 DAY, CURDATE() + INTERVAL 7 DAY + INTERVAL 8 HOUR);

INSERT INTO direcciones (id_usuario, provincia, canton, distrito, detalle) VALUES
 (2, 'San José', 'Montes de Oca', 'San Pedro', '200 m norte de la iglesia, casa verde'),
 (3, 'Heredia',  'Heredia',       'Mercedes',  'Residencial Los Pinos, casa 14');

-- pedido de prueba
CALL sp_carrito_agregar(2, 9, 2);    -- 2 leches
CALL sp_carrito_agregar(2, 1, 1);    -- 1 kg de manzana (15 %)
CALL sp_carrito_agregar(2, 20, 4);   -- 4 aguas (2x1)
CALL sp_pedido_crear(2, 1, 'Efectivo', NULL, CURDATE() + INTERVAL 3 DAY, '08:00-12:00');
