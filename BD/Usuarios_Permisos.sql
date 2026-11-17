-- usuarios de la base, roles y permisos
-- se ejecuta con root despues de script.sql
-- admin_jaulares: lo usa el equipo para el backend y el panel admin
-- client_jaulares: lo usa la pagina, solo puede llamar los procedimientos del cliente
-- las claves son de ejemplo

USE supermercado_jaulares;


-- 1. reglas de contrasenna, bloqueo y auditoria del servidor
-- va en un procedimiento temporal para que no falle si el plugin ya esta

DROP PROCEDURE IF EXISTS tmp_configurar_servidor;
DELIMITER $$
CREATE PROCEDURE tmp_configurar_servidor()
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    -- execute immediate porque las variables salen al instalar el plugin

    -- clave de minimo 10 caracteres con mayuscula, minuscula, numero y simbolo
    INSTALL SONAME 'simple_password_check';
    EXECUTE IMMEDIATE "SET GLOBAL simple_password_check_minimal_length = 10";
    EXECUTE IMMEDIATE "SET GLOBAL simple_password_check_letters_same_case = 1";
    EXECUTE IMMEDIATE "SET GLOBAL simple_password_check_digits = 1";
    EXECUTE IMMEDIATE "SET GLOBAL simple_password_check_other_characters = 1";

    -- a los 3 intentos fallidos se bloquea la cuenta de la base
    EXECUTE IMMEDIATE "SET GLOBAL max_password_errors = 3";

    -- guarda en un log las conexiones y cambios de permisos
    INSTALL SONAME 'server_audit';
    EXECUTE IMMEDIATE "SET GLOBAL server_audit_events = 'CONNECT,QUERY_DDL,QUERY_DCL'";
    EXECUTE IMMEDIATE "SET GLOBAL server_audit_logging = ON";
END$$
DELIMITER ;

CALL tmp_configurar_servidor();
DROP PROCEDURE tmp_configurar_servidor;

-- esto se borra al reiniciar el servidor, para dejarlo fijo copiar en my.ini:
--   plugin_load_add = simple_password_check
--   plugin_load_add = server_audit
--   simple_password_check_minimal_length = 10
--   max_password_errors = 3
--   server_audit_events = connect,query_ddl,query_dcl
--   server_audit_logging = on


-- 2. roles, los permisos se dan al rol y el rol al usuario

DROP USER IF EXISTS 'admin_jaulares'@'localhost';
DROP USER IF EXISTS 'client_jaulares'@'localhost';
DROP ROLE IF EXISTS rol_admin_jaulares;
DROP ROLE IF EXISTS rol_client_jaulares;

CREATE ROLE rol_admin_jaulares;
CREATE ROLE rol_client_jaulares;

-- rol admin: maneja las tablas y procedimientos de esta base, no puede dar permisos
GRANT SELECT, INSERT, UPDATE, DELETE,
      CREATE, ALTER, DROP, INDEX, REFERENCES,
      CREATE VIEW, SHOW VIEW,
      CREATE ROUTINE, ALTER ROUTINE, EXECUTE,
      TRIGGER, EVENT,
      CREATE TEMPORARY TABLES, LOCK TABLES
   ON supermercado_jaulares.* TO rol_admin_jaulares;

-- rol client: solo ejecuta los procedimientos del cliente, no toca tablas

-- cuentas y contrasennas
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_registrar         TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_login             TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_login_fallido     TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_login_exitoso     TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_consultar_correo  TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_obtener           TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_actualizar        TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_usuario_cambiar_password  TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_recuperacion_crear        TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_recuperacion_validar      TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_recuperacion_usar         TO rol_client_jaulares;

-- catalogo
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_categoria_listar_activas  TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_producto_listar           TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_producto_por_categoria    TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_producto_buscar           TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_producto_detalle          TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_producto_destacados       TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_promocion_vigentes        TO rol_client_jaulares;

-- errores
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_error_registrar         TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_inventario_validar_stock  TO rol_client_jaulares;

-- carrito
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_obtener_o_crear     TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_agregar             TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_actualizar_cantidad TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_eliminar_item       TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_vaciar              TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_listar              TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_contar              TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_carrito_revalidar           TO rol_client_jaulares;

-- direcciones
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_direccion_insertar   TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_direccion_actualizar TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_direccion_eliminar   TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_direccion_listar     TO rol_client_jaulares;

-- pedidos y calendario, sp_pedido_crear es lo unico que baja el stock
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_pedido_crear           TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_pedido_listar_cliente  TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_pedido_detalle_listar  TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_fechas_cierre_listar   TO rol_client_jaulares;
GRANT EXECUTE ON PROCEDURE supermercado_jaulares.sp_evento_listar          TO rol_client_jaulares;


-- 3. usuarios
-- admin: la clave vence cada 90 dias, maximo 5 conexiones
-- client: la clave vence cada 180 dias, maximo 100 conexiones

CREATE USER 'admin_jaulares'@'localhost'
    IDENTIFIED BY 'Admin_Jaulares2026!'
    WITH MAX_USER_CONNECTIONS 5
    PASSWORD EXPIRE INTERVAL 90 DAY;

CREATE USER 'client_jaulares'@'localhost'
    IDENTIFIED BY 'Client_Jaulares2026!'
    WITH MAX_USER_CONNECTIONS 100
    PASSWORD EXPIRE INTERVAL 180 DAY;

GRANT rol_admin_jaulares  TO 'admin_jaulares'@'localhost';
GRANT rol_client_jaulares TO 'client_jaulares'@'localhost';

-- activa el rol al conectarse
SET DEFAULT ROLE rol_admin_jaulares  FOR 'admin_jaulares'@'localhost';
SET DEFAULT ROLE rol_client_jaulares FOR 'client_jaulares'@'localhost';

FLUSH PRIVILEGES;


-- 4. comandos de ayuda, no se ejecutan

-- ver permisos:
--   show grants for 'admin_jaulares'@'localhost';
--   show grants for 'client_jaulares'@'localhost';
--   show grants for rol_client_jaulares;
--   select * from information_schema.applicable_roles;   -- conectado como el usuario
--   select current_role();                                -- rol activo

-- agregar otra persona al equipo admin:
--   create user 'nombre'@'localhost' identified by 'Clave_Segura2026!'
--       with max_user_connections 5 password expire interval 90 day;
--   grant rol_admin_jaulares to 'nombre'@'localhost';
--   set default role rol_admin_jaulares for 'nombre'@'localhost';

-- quitar un permiso o un rol:
--   revoke execute on procedure supermercado_jaulares.sp_carrito_vaciar from rol_client_jaulares;
--   revoke rol_admin_jaulares from 'nombre'@'localhost';

-- bloquear o desbloquear una cuenta:
--   alter user 'client_jaulares'@'localhost' account lock;
--   alter user 'client_jaulares'@'localhost' account unlock;
--   flush privileges;   -- desbloquea las que se bloquearon por intentos

-- obligar a cambiar la clave:
--   alter user 'admin_jaulares'@'localhost' password expire;
--   alter user 'admin_jaulares'@'localhost' identified by 'Otra_Clave2026!';

-- ver la auditoria de la pagina:
--   call sp_auditoria_listar(null, null, null);
--   call sp_inventario_bitacora(0);
