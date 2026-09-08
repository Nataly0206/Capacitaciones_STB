USE [master]
GO

IF DB_ID(N'db_capacitaciones_stb') IS NULL
BEGIN
    CREATE DATABASE [db_capacitaciones_stb]
END
GO

USE [db_capacitaciones_stb]
GO

/* ============================================================
   LIMPIEZA SEGURA
   ============================================================ */
IF OBJECT_ID(N'dbo.sp_aviso_correos_pendientes', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_aviso_correos_pendientes
GO
IF OBJECT_ID(N'dbo.sp_reporte_capacitacion', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_reporte_capacitacion
GO
IF OBJECT_ID(N'dbo.sp_reporte_departamento_capacitacion', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_reporte_departamento_capacitacion
GO
IF OBJECT_ID(N'dbo.sp_reporte_puesto_capacitacion', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_reporte_puesto_capacitacion
GO
IF OBJECT_ID(N'dbo.sp_reporte_empleado_capacitacion', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_reporte_empleado_capacitacion
GO
IF OBJECT_ID(N'dbo.sp_generar_empleado_capacitacion_desde_asignaciones', N'P') IS NOT NULL DROP PROCEDURE dbo.sp_generar_empleado_capacitacion_desde_asignaciones
GO

IF OBJECT_ID(N'dbo.vw_capacitaciones_por_vencer', N'V') IS NOT NULL DROP VIEW dbo.vw_capacitaciones_por_vencer
GO
IF OBJECT_ID(N'dbo.vw_reporte_capacitacion_general', N'V') IS NOT NULL DROP VIEW dbo.vw_reporte_capacitacion_general
GO
IF OBJECT_ID(N'dbo.vw_reporte_departamento_capacitacion', N'V') IS NOT NULL DROP VIEW dbo.vw_reporte_departamento_capacitacion
GO
IF OBJECT_ID(N'dbo.vw_reporte_puesto_capacitacion', N'V') IS NOT NULL DROP VIEW dbo.vw_reporte_puesto_capacitacion
GO
IF OBJECT_ID(N'dbo.vw_expediente_capacitacion_empleado', N'V') IS NOT NULL DROP VIEW dbo.vw_expediente_capacitacion_empleado
GO
IF OBJECT_ID(N'dbo.empleado_contenido_avance', N'U') IS NOT NULL DROP TABLE dbo.empleado_contenido_avance
GO
IF OBJECT_ID(N'dbo.historial_capacitacion_empleado', N'U') IS NOT NULL DROP TABLE dbo.historial_capacitacion_empleado
GO
IF OBJECT_ID(N'dbo.aviso_correo', N'U') IS NOT NULL DROP TABLE dbo.aviso_correo
GO
IF OBJECT_ID(N'dbo.configuracion_aviso', N'U') IS NOT NULL DROP TABLE dbo.configuracion_aviso
GO
IF OBJECT_ID('dbo.ejercicio_intento_respuesta', 'U') IS NOT NULL DROP TABLE dbo.ejercicio_intento_respuesta;
GO
IF OBJECT_ID('dbo.ejercicio_intento', 'U') IS NOT NULL DROP TABLE dbo.ejercicio_intento;
GO
IF OBJECT_ID('dbo.ejercicio_opcion', 'U') IS NOT NULL DROP TABLE dbo.ejercicio_opcion;
GO
IF OBJECT_ID('dbo.ejercicio_pregunta', 'U') IS NOT NULL DROP TABLE dbo.ejercicio_pregunta;
GO
IF OBJECT_ID('dbo.ejercicio', 'U') IS NOT NULL DROP TABLE dbo.ejercicio;
GO
IF OBJECT_ID(N'dbo.evaluacion_intento_respuesta', N'U') IS NOT NULL DROP TABLE dbo.evaluacion_intento_respuesta
GO
IF OBJECT_ID(N'dbo.evaluacion_intento', N'U') IS NOT NULL DROP TABLE dbo.evaluacion_intento
GO
IF OBJECT_ID(N'dbo.empleado_modulo_avance', N'U') IS NOT NULL DROP TABLE dbo.empleado_modulo_avance
GO
IF OBJECT_ID(N'dbo.empleado_capacitacion', N'U') IS NOT NULL DROP TABLE dbo.empleado_capacitacion
GO
IF OBJECT_ID(N'dbo.empleados_capacitacion', N'U') IS NOT NULL DROP TABLE dbo.empleados_capacitacion
GO
IF OBJECT_ID(N'dbo.departamentos_capacitacion', N'U') IS NOT NULL DROP TABLE dbo.departamentos_capacitacion
GO
IF OBJECT_ID(N'dbo.puestos_capacitacion', N'U') IS NOT NULL DROP TABLE dbo.puestos_capacitacion
GO
IF OBJECT_ID(N'dbo.evaluacion_opcion', N'U') IS NOT NULL DROP TABLE dbo.evaluacion_opcion
GO
IF OBJECT_ID(N'dbo.evaluacion_pregunta', N'U') IS NOT NULL DROP TABLE dbo.evaluacion_pregunta
GO
IF OBJECT_ID(N'dbo.evaluacion', N'U') IS NOT NULL DROP TABLE dbo.evaluacion
GO
IF OBJECT_ID(N'dbo.capacitacion_recurso', N'U') IS NOT NULL DROP TABLE dbo.capacitacion_recurso
GO
IF OBJECT_ID(N'dbo.capacitacion_modulo_seccion', N'U') IS NOT NULL DROP TABLE dbo.capacitacion_modulo_seccion
GO
IF OBJECT_ID(N'dbo.capacitacion_modulo', N'U') IS NOT NULL DROP TABLE dbo.capacitacion_modulo
GO
IF OBJECT_ID(N'dbo.capacitacion_area', N'U') IS NOT NULL DROP TABLE dbo.capacitacion_area
GO
IF OBJECT_ID(N'dbo.capacitacion', N'U') IS NOT NULL DROP TABLE dbo.capacitacion
GO
IF OBJECT_ID(N'dbo.instructor_user', N'U') IS NOT NULL DROP TABLE dbo.instructor_user
GO
IF OBJECT_ID(N'dbo.instructor', N'U') IS NOT NULL DROP TABLE dbo.instructor
GO
IF OBJECT_ID(N'dbo.empleado_user', N'U') IS NOT NULL DROP TABLE dbo.empleado_user
GO
IF OBJECT_ID(N'dbo.user_rol', N'U') IS NOT NULL DROP TABLE dbo.user_rol
GO
IF OBJECT_ID(N'dbo.rol', N'U') IS NOT NULL DROP TABLE dbo.rol
GO
IF OBJECT_ID(N'dbo.users', N'U') IS NOT NULL DROP TABLE dbo.users
GO
IF OBJECT_ID(N'dbo.empleado', N'U') IS NOT NULL DROP TABLE dbo.empleado
GO
IF OBJECT_ID(N'dbo.puesto_trabajo_matriz', N'U') IS NOT NULL DROP TABLE dbo.puesto_trabajo_matriz
GO
IF OBJECT_ID(N'dbo.departamento', N'U') IS NOT NULL DROP TABLE dbo.departamento
GO
IF OBJECT_ID(N'dbo.area_capacitacion', N'U') IS NOT NULL DROP TABLE dbo.area_capacitacion
GO

/* ============================================================
   TABLAS BASE
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[area_capacitacion](
    [id_area_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [area_capacitacion] [nvarchar](150) NOT NULL,
    [descripcion] [nvarchar](500) NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_area_capacitacion_id_area_capacitacion] PRIMARY KEY CLUSTERED ([id_area_capacitacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departamento](
    [id_departamento] [int] IDENTITY(1,1) NOT NULL,
    [departamento] [nvarchar](100) NOT NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_departamento_id_departamento] PRIMARY KEY CLUSTERED ([id_departamento] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[puesto_trabajo_matriz](
    [id_puesto_trabajo_matriz] [int] IDENTITY(1,1) NOT NULL,
    [puesto_trabajo_matriz] [nvarchar](150) NOT NULL,
    [id_departamento] [int] NOT NULL,
    [descripcion_general] [nvarchar](1000) NULL,
    [objetivo_puesto] [nvarchar](1000) NULL,
    [num_empleados] [int] NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_puesto_trabajo_matriz_id_puesto_trabajo_matriz] PRIMARY KEY CLUSTERED ([id_puesto_trabajo_matriz] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleado](
    [id_empleado] [int] IDENTITY(1,1) NOT NULL,
    [nombre_completo] [nvarchar](150) NOT NULL,
    [identidad] [nvarchar](50) NULL,
    [codigo_empleado] [nvarchar](20) NULL,
    [correo] [nvarchar](255) NULL,
    [telefono] [nvarchar](100) NULL,
    [id_puesto_trabajo_matriz] [int] NOT NULL,
    [fecha_ingreso] [date] NULL,
    [fecha_nacimiento] [date] NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_empleado_id_empleado] PRIMARY KEY CLUSTERED ([id_empleado] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[users](
    [id] [bigint] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL,
    [username] [nvarchar](50) NOT NULL,
    [email] [nvarchar](255) NOT NULL,
    [email_verified_at] [datetime] NULL,
    [password] [nvarchar](255) NOT NULL,
    [debe_cambiar_password] [bit] NOT NULL,
    [password_temporal_expira_en] [datetime] NULL,
    [remember_token] [nvarchar](100) NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
    [estado] [int] NOT NULL,
    [recibe_avisos_capacitaciones] [bit] NOT NULL,
    CONSTRAINT [PK_users_id] PRIMARY KEY CLUSTERED ([id] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rol](
    [id_rol] [int] IDENTITY(1,1) NOT NULL,
    [rol] [nvarchar](50) NOT NULL,
    [descripcion] [nvarchar](255) NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_rol_id_rol] PRIMARY KEY CLUSTERED ([id_rol] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[user_rol](
    [id_user_rol] [int] IDENTITY(1,1) NOT NULL,
    [id_user] [bigint] NOT NULL,
    [id_rol] [int] NOT NULL,
    [fecha_asignacion] [datetime] NOT NULL,
    CONSTRAINT [PK_user_rol_id_user_rol] PRIMARY KEY CLUSTERED ([id_user_rol] ASC)
) ON [PRIMARY]
GO

/* ============================================================
   SPATIE LARAVEL PERMISSION - TABLAS BASE
   ============================================================ */

IF OBJECT_ID('dbo.permissions', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.permissions (
        id BIGINT IDENTITY(1,1) NOT NULL,
        name NVARCHAR(100) NOT NULL,
        guard_name NVARCHAR(100) NOT NULL,
        created_at DATETIME2 NULL,
        updated_at DATETIME2 NULL,

        CONSTRAINT PK_permissions PRIMARY KEY (id),
        CONSTRAINT UQ_permissions_name_guard UNIQUE (name, guard_name)
    );
END
GO

IF OBJECT_ID('dbo.roles', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.roles (
        id BIGINT IDENTITY(1,1) NOT NULL,
        name NVARCHAR(100) NOT NULL,
        guard_name NVARCHAR(100) NOT NULL,
        created_at DATETIME2 NULL,
        updated_at DATETIME2 NULL,

        CONSTRAINT PK_roles PRIMARY KEY (id),
        CONSTRAINT UQ_roles_name_guard UNIQUE (name, guard_name)
    );
END
GO

IF OBJECT_ID('dbo.model_has_permissions', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.model_has_permissions (
        permission_id BIGINT NOT NULL,
        model_type NVARCHAR(100) NOT NULL,
        model_id BIGINT NOT NULL,

        CONSTRAINT PK_model_has_permissions
            PRIMARY KEY (permission_id, model_id, model_type),

        CONSTRAINT FK_model_has_permissions_permission_id
            FOREIGN KEY (permission_id)
            REFERENCES dbo.permissions(id)
            ON DELETE CASCADE
    );

    CREATE INDEX IX_model_has_permissions_model
    ON dbo.model_has_permissions (model_id, model_type);
END
GO

IF OBJECT_ID('dbo.model_has_roles', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.model_has_roles (
        role_id BIGINT NOT NULL,
        model_type NVARCHAR(100) NOT NULL,
        model_id BIGINT NOT NULL,

        CONSTRAINT PK_model_has_roles
            PRIMARY KEY (role_id, model_id, model_type),

        CONSTRAINT FK_model_has_roles_role_id
            FOREIGN KEY (role_id)
            REFERENCES dbo.roles(id)
            ON DELETE CASCADE
    );

    CREATE INDEX IX_model_has_roles_model
    ON dbo.model_has_roles (model_id, model_type);
END
GO

IF OBJECT_ID('dbo.role_has_permissions', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.role_has_permissions (
        permission_id BIGINT NOT NULL,
        role_id BIGINT NOT NULL,

        CONSTRAINT PK_role_has_permissions
            PRIMARY KEY (permission_id, role_id),

        CONSTRAINT FK_role_has_permissions_permission_id
            FOREIGN KEY (permission_id)
            REFERENCES dbo.permissions(id)
            ON DELETE CASCADE,

        CONSTRAINT FK_role_has_permissions_role_id
            FOREIGN KEY (role_id)
            REFERENCES dbo.roles(id)
            ON DELETE CASCADE
    );
END
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleado_user](
    [id_empleado_user] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado] [int] NOT NULL,
    [id_user] [bigint] NOT NULL,
    [fecha_asignacion] [datetime] NOT NULL,
    CONSTRAINT [PK_empleado_user_id_empleado_user] PRIMARY KEY CLUSTERED ([id_empleado_user] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[instructor_user](
    [id_instructor_user] [bigint] IDENTITY(1,1) NOT NULL,
    [id_user] [bigint] NOT NULL,
    [id_instructor] [int] NOT NULL,
    [fecha_asignacion] [datetime] NOT NULL,
    CONSTRAINT [PK_instructor_user_id_instructor_user] PRIMARY KEY CLUSTERED ([id_instructor_user] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[instructor](
    [id_instructor] [int] IDENTITY(1,1) NOT NULL,
    [instructor] [nvarchar](150) NOT NULL,
    [correo] [nvarchar](255) NULL,
    [telefono] [nvarchar](100) NULL,
    [interno] [int] NOT NULL,
    [estado] [int] NOT NULL,
    [id_empleado] [int] NULL,
    CONSTRAINT [PK_instructor_id_instructor] PRIMARY KEY CLUSTERED ([id_instructor] ASC)
) ON [PRIMARY]
GO

/* ============================================================
   CAPACITACIONES Y CONTENIDO
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[capacitacion](
    [id_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [capacitacion] [nvarchar](250) NOT NULL,
    [codigo] [nvarchar](50) NULL,
    [descripcion] [nvarchar](2000) NULL,
    [objetivo_general] [nvarchar](1000) NULL,
    [horas_estimadas] [int] NULL,
    [ruta_portada] [nvarchar](500) NULL,
    [porcentaje_aprobacion] [decimal](5,2) NOT NULL,
    [dias_vigencia] [int] NULL,
    [obligatoria] [int] NOT NULL,
    [permite_autogestion] [int] NOT NULL,
    [estado] [int] NOT NULL,
    [created_by] [bigint] NULL,
	[id_instructor] [int] NULL,
    [id_capacitacion_instructor] [int] NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
    CONSTRAINT [PK_capacitacion_id_capacitacion] PRIMARY KEY CLUSTERED ([id_capacitacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[capacitacion_area](
    [id_capacitacion_area] [int] IDENTITY(1,1) NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [id_area_capacitacion] [int] NOT NULL,
    CONSTRAINT [PK_capacitacion_area_id_capacitacion_area] PRIMARY KEY CLUSTERED ([id_capacitacion_area] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[capacitacion_modulo](
    [id_capacitacion_modulo] [int] IDENTITY(1,1) NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [titulo] [nvarchar](250) NULL,
    [descripcion] [nvarchar](2000) NULL,
    [objetivo] [nvarchar](1000) NULL,
    [orden] [int] NOT NULL,
    [duracion_horas] [decimal](10,2) NULL,
    [requiere_evaluacion] [int] NOT NULL,
    [porcentaje_aprobacion] [decimal](5,2) NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_capacitacion_modulo_id_capacitacion_modulo] PRIMARY KEY CLUSTERED ([id_capacitacion_modulo] ASC)
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[capacitacion_modulo_seccion](
    [id_capacitacion_modulo_seccion] [int] IDENTITY(1,1) NOT NULL,
    [id_capacitacion_modulo] [int] NOT NULL,
    [id_seccion_padre] [int] NULL,
    [titulo] [nvarchar](250) NULL,
    [contenido] [nvarchar](max) NULL,
    [orden] [int] NOT NULL,
    [nivel] [int] NOT NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_capacitacion_modulo_seccion] PRIMARY KEY CLUSTERED ([id_capacitacion_modulo_seccion] ASC)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[capacitacion_recurso](
    [id_capacitacion_recurso] [int] IDENTITY(1,1) NOT NULL,
    [id_capacitacion_modulo] [int] NOT NULL,
    [id_capacitacion_modulo_seccion] [int] NULL,
    [tipo_recurso] [nvarchar](50) NOT NULL,
    [titulo] [nvarchar](250) NOT NULL,
    [descripcion] [nvarchar](1000) NULL,
    [url_recurso] [nvarchar](1000) NULL,
    [ruta_archivo] [nvarchar](1000) NULL,
    [obligatorio] [int] NOT NULL,
    [orden] [int] NOT NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_capacitacion_recurso_id_capacitacion_recurso] PRIMARY KEY CLUSTERED ([id_capacitacion_recurso] ASC)
) ON [PRIMARY]
GO

IF COL_LENGTH('dbo.capacitacion_recurso', 'contenido_texto') IS NULL
BEGIN
    ALTER TABLE dbo.capacitacion_recurso
    ADD contenido_texto NVARCHAR(MAX) NULL;
END
GO

IF COL_LENGTH('dbo.capacitacion_recurso', 'permite_descarga') IS NULL
BEGIN
    ALTER TABLE dbo.capacitacion_recurso
    ADD permite_descarga INT NOT NULL CONSTRAINT DF_capacitacion_recurso_permite_descarga DEFAULT 1;
END
GO

IF COL_LENGTH('dbo.capacitacion_recurso', 'titulo') IS NOT NULL
BEGIN
    ALTER TABLE dbo.capacitacion_recurso
    ALTER COLUMN titulo NVARCHAR(250) NULL;
END
GO

/* ============================================================
   EVALUACIONES
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[evaluacion](
    [id_evaluacion] [int] IDENTITY(1,1) NOT NULL,
    [id_capacitacion_modulo] [int] NOT NULL,
    id_capacitacion_modulo_seccion INT NULL,
    [titulo] [nvarchar](250) NOT NULL,
    [descripcion] [nvarchar](1000) NULL,
    [porcentaje_aprobacion] [decimal](5,2) NOT NULL,
    [tiempo_limite_minutos] [int] NULL,
    [intentos_maximos] [int] NULL,
    [activa] [int] NOT NULL,
    instrucciones NVARCHAR(MAX) NULL,
    obligatorio BIT NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 1,
    mostrar_resultado_inmediato BIT NOT NULL DEFAULT 0,
    requiere_revision_manual BIT NOT NULL DEFAULT 0,
    CONSTRAINT [PK_evaluacion_id_evaluacion] PRIMARY KEY CLUSTERED ([id_evaluacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[evaluacion_pregunta](
    [id_evaluacion_pregunta] [int] IDENTITY(1,1) NOT NULL,
    [id_evaluacion] [int] NOT NULL,
    [pregunta] [nvarchar](2000) NOT NULL,
    [tipo_pregunta] [nvarchar](50) NOT NULL,
    [puntaje] [decimal](10,2) NOT NULL,
    [orden] [int] NOT NULL,
    [activa] [int] NOT NULL,
    respuesta_correcta_texto NVARCHAR(MAX) NULL,
    configuracion_json NVARCHAR(MAX) NULL,
    requiere_revision_manual BIT NOT NULL DEFAULT 0,
    CONSTRAINT [PK_evaluacion_pregunta_id_evaluacion_pregunta] PRIMARY KEY CLUSTERED ([id_evaluacion_pregunta] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[evaluacion_opcion](
    [id_evaluacion_opcion] [int] IDENTITY(1,1) NOT NULL,
    [id_evaluacion_pregunta] [int] NOT NULL,
    [opcion] [nvarchar](1000) NOT NULL,
    [es_correcta] [int] NOT NULL,
    [orden] [int] NOT NULL,
    CONSTRAINT [PK_evaluacion_opcion_id_evaluacion_opcion] PRIMARY KEY CLUSTERED ([id_evaluacion_opcion] ASC)
) ON [PRIMARY]
GO

CREATE TABLE dbo.ejercicio (
    id_ejercicio INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    id_capacitacion_modulo INT NOT NULL,
    id_capacitacion_modulo_seccion INT NULL,
    titulo NVARCHAR(250) NOT NULL,
    descripcion NVARCHAR(1000) NULL,
    instrucciones NVARCHAR(MAX) NULL,
    intentos_maximos INT NULL,
    tiempo_limite_minutos INT NULL,
    porcentaje_aprobacion DECIMAL(5,2) NOT NULL CONSTRAINT DF_ejercicio_porcentaje_aprobacion DEFAULT(70),
    obligatorio INT NOT NULL CONSTRAINT DF_ejercicio_obligatorio DEFAULT (0),
    orden INT NOT NULL,
    estado INT NOT NULL CONSTRAINT DF_ejercicio_estado DEFAULT (1),
    mostrar_resultado_inmediato INT NOT NULL CONSTRAINT DF_ejercicio_resultado_inmediato DEFAULT (1),
    requiere_revision_manual INT NOT NULL CONSTRAINT DF_ejercicio_revision_manual DEFAULT (0)
);
GO

CREATE TABLE dbo.ejercicio_pregunta (
    id_ejercicio_pregunta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    id_ejercicio INT NOT NULL,
    enunciado NVARCHAR(MAX) NOT NULL,
    tipo_pregunta NVARCHAR(50) NOT NULL,
    puntaje DECIMAL(10,2) NOT NULL CONSTRAINT DF_ejercicio_pregunta_puntaje DEFAULT (1),
    orden INT NOT NULL,
    activa INT NOT NULL CONSTRAINT DF_ejercicio_pregunta_activa DEFAULT (1),
    respuesta_correcta_texto NVARCHAR(MAX) NULL,
    configuracion_json NVARCHAR(MAX) NULL,
    requiere_revision_manual INT NOT NULL CONSTRAINT DF_ejercicio_pregunta_revision_manual DEFAULT (0)
);
GO

CREATE TABLE dbo.ejercicio_opcion (
    id_ejercicio_opcion INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    id_ejercicio_pregunta INT NOT NULL,
    opcion NVARCHAR(1000) NOT NULL,
    lado NVARCHAR(20) NULL,
    clave_relacion NVARCHAR(100) NULL,
    es_correcta INT NULL,
    orden INT NOT NULL
);
GO

CREATE TABLE dbo.ejercicio_intento (
    id_ejercicio_intento INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    id_ejercicio INT NOT NULL,
    id_empleado INT NOT NULL,
    id_empleado_capacitacion INT NOT NULL,
    numero_intento INT NOT NULL,
    fecha_inicio DATETIME NULL,
    fecha_fin DATETIME NULL,
    puntaje_obtenido DECIMAL(10,2) NULL,
    porcentaje_obtenido DECIMAL(5,2) NULL,
    aprobado INT NULL,
    estado VARCHAR(30) NOT NULL,
    comentario_revision NVARCHAR(2000) NULL
);
GO

CREATE TABLE dbo.ejercicio_intento_respuesta (
    id_ejercicio_intento_respuesta INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    id_ejercicio_intento INT NOT NULL,
    id_ejercicio_pregunta INT NOT NULL,
    respuesta_texto NVARCHAR(MAX) NULL,
    respuesta_json NVARCHAR(MAX) NULL,
    es_correcta INT NULL,
    puntaje_obtenido DECIMAL(10,2) NULL,
    comentario_revision NVARCHAR(2000) NULL
);
GO

/* ============================================================
   ASIGNACIONES DE CAPACITACION
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[puestos_capacitacion](
    [id_puestos_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [id_puesto_trabajo_matriz] [int] NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [obligatoria] [int] NOT NULL,
    [dias_para_vencer] [int] NULL,
    [fecha_asignacion] [date] NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_puestos_capacitacion_id_puestos_capacitacion] PRIMARY KEY CLUSTERED ([id_puestos_capacitacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departamentos_capacitacion](
    [id_departamentos_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [id_departamento] [int] NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [obligatoria] [int] NOT NULL,
    [dias_para_vencer] [int] NULL,
    [fecha_asignacion] [date] NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_departamentos_capacitacion_id_departamentos_capacitacion] PRIMARY KEY CLUSTERED ([id_departamentos_capacitacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleados_capacitacion](
    [id_empleados_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado] [int] NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [obligatoria] [int] NOT NULL,
    [dias_para_vencer] [int] NULL,
    [fecha_asignacion] [date] NULL,
    [estado] [int] NOT NULL,
    CONSTRAINT [PK_empleados_capacitacion_id_empleados_capacitacion] PRIMARY KEY CLUSTERED ([id_empleados_capacitacion] ASC)
) ON [PRIMARY]
GO

/* ============================================================
   SEGUIMIENTO DEL EMPLEADO
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleado_capacitacion](
    [id_empleado_capacitacion] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado] [int] NOT NULL,
    [id_capacitacion] [int] NOT NULL,
    [origen_asignacion] [varchar](20) NOT NULL,
    [id_referencia_asignacion] [int] NULL,
    [obligatoria] [int] NOT NULL,
    [fecha_asignacion] [date] NOT NULL,
    [fecha_inicio] [datetime] NULL,
    [fecha_limite] [date] NULL,
    [fecha_vencimiento] [date] NULL,
    [fecha_finalizacion] [datetime] NULL,
    [estado] [varchar](30) NOT NULL,
    [progreso] [decimal](5,2) NOT NULL,
    [nota_final] [decimal](5,2) NULL,
    [aprobado] [int] NULL,
    [id_usuario_asigno] [bigint] NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
    CONSTRAINT [PK_empleado_capacitacion_id_empleado_capacitacion] PRIMARY KEY CLUSTERED ([id_empleado_capacitacion] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleado_modulo_avance](
    [id_empleado_modulo_avance] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado_capacitacion] [int] NOT NULL,
    [id_capacitacion_modulo] [int] NOT NULL,
    [fecha_inicio] [datetime] NULL,
    [fecha_ultima_actividad] [datetime] NULL,
    [fecha_finalizacion] [datetime] NULL,
    [estado] [varchar](30) NOT NULL,
    [progreso] [decimal](5,2) NOT NULL,
    [nota] [decimal](5,2) NULL,
    [aprobado] [int] NULL,
    CONSTRAINT [PK_empleado_modulo_avance_id_empleado_modulo_avance] PRIMARY KEY CLUSTERED ([id_empleado_modulo_avance] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[empleado_contenido_avance](
    [id_empleado_contenido_avance] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado_capacitacion] [int] NOT NULL,
    [id_capacitacion_modulo] [int] NOT NULL,
    [tipo_contenido] [varchar](30) NOT NULL,
    [id_capacitacion_modulo_seccion] [int] NULL,
    [id_capacitacion_recurso] [int] NULL,
    [id_ejercicio] [int] NULL,
    [id_evaluacion] [int] NULL,
    [estado] [varchar](30) NOT NULL,
    [fecha_inicio] [datetime] NOT NULL,
    [fecha_ultima_actividad] [datetime] NOT NULL,
    [fecha_completado] [datetime] NULL,
    [created_at] [datetime] NULL,
    [updated_at] [datetime] NULL,
    CONSTRAINT [PK_empleado_contenido_avance] PRIMARY KEY CLUSTERED ([id_empleado_contenido_avance] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[evaluacion_intento](
    [id_evaluacion_intento] [int] IDENTITY(1,1) NOT NULL,
    [id_evaluacion] [int] NOT NULL,
    [id_empleado] [int] NOT NULL,
    [id_empleado_capacitacion] [int] NOT NULL,
    [numero_intento] [int] NOT NULL,
    [fecha_inicio] [datetime] NOT NULL,
    [fecha_fin] [datetime] NULL,
    [nota] [decimal](5,2) NULL,
    [aprobado] [int] NULL,
    [estado] [varchar](30) NOT NULL,
    CONSTRAINT [PK_evaluacion_intento_id_evaluacion_intento] PRIMARY KEY CLUSTERED ([id_evaluacion_intento] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[evaluacion_intento_respuesta](
    [id_evaluacion_intento_respuesta] [int] IDENTITY(1,1) NOT NULL,
    [id_evaluacion_intento] [int] NOT NULL,
    [id_evaluacion_pregunta] [int] NOT NULL,
    [id_evaluacion_opcion] [int] NULL,
    [respuesta_texto] [nvarchar](2000) NULL,
    [es_correcta] [int] NULL,
    [puntaje_obtenido] [decimal](10,2) NULL,
    [comentario_revision] [nvarchar] (2000) NULL,
    CONSTRAINT [PK_evaluacion_intento_respuesta_id_evaluacion_intento_respuesta] PRIMARY KEY CLUSTERED ([id_evaluacion_intento_respuesta] ASC)
) ON [PRIMARY]
GO

/* ============================================================
   AVISOS Y EXPEDIENTE
   ============================================================ */
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[configuracion_aviso](
    [id_configuracion_aviso] [int] IDENTITY(1,1) NOT NULL,
    [tipo_aviso] [varchar](30) NOT NULL,
    [dias_anticipacion] [int] NULL,
    [enviar_a_empleado] [int] NOT NULL,
    [enviar_a_admin] [int] NOT NULL,
    [activo] [int] NOT NULL,
    CONSTRAINT [PK_configuracion_aviso_id_configuracion_aviso] PRIMARY KEY CLUSTERED ([id_configuracion_aviso] ASC)
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[aviso_correo](
    [id_aviso_correo] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado_capacitacion] [int] NOT NULL,
	[id_configuracion_aviso] [int] NOT NULL,
    [tipo_aviso] [varchar](30) NOT NULL,
    [destinatario_tipo] [varchar](20) NOT NULL,
    [destinatario_email] [nvarchar](255) NOT NULL,
    [asunto] [nvarchar](255) NOT NULL,
    [mensaje] [nvarchar](max) NOT NULL,
    [fecha_programada] [datetime] NOT NULL,
    [fecha_enviada] [datetime] NULL,
    [estado] [varchar](30) NOT NULL,
    [intentos_envio] [int] NOT NULL,
    [error_envio] [nvarchar](2000) NULL,
    CONSTRAINT [PK_aviso_correo_id_aviso_correo] PRIMARY KEY CLUSTERED ([id_aviso_correo] ASC)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[historial_capacitacion_empleado](
    [id_historial_capacitacion_empleado] [int] IDENTITY(1,1) NOT NULL,
    [id_empleado_capacitacion] [int] NOT NULL,
    [estado_anterior] [varchar](30) NULL,
    [estado_nuevo] [varchar](30) NOT NULL,
    [observacion] [nvarchar](1000) NULL,
    [fecha_movimiento] [datetime] NOT NULL,
    [id_user] [bigint] NULL,
    CONSTRAINT [PK_historial_capacitacion_empleado_id_historial_capacitacion_empleado] PRIMARY KEY CLUSTERED ([id_historial_capacitacion_empleado] ASC)
) ON [PRIMARY]
GO

/* ============================================================
   RESTRICCIONES
   ============================================================ */
ALTER TABLE [dbo].[area_capacitacion] ADD CONSTRAINT [DF_area_capacitacion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[departamento] ADD CONSTRAINT [DF_departamento_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[puesto_trabajo_matriz] ADD CONSTRAINT [DF_puesto_trabajo_matriz_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[empleado] ADD CONSTRAINT [DF_empleado_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[users] ADD CONSTRAINT [DF_users_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[users] ADD CONSTRAINT [DF_users_debe_cambiar_password] DEFAULT ((0)) FOR [debe_cambiar_password]
GO
ALTER TABLE [dbo].[rol] ADD CONSTRAINT [DF_rol_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[user_rol] ADD CONSTRAINT [DF_user_rol_fecha_asignacion] DEFAULT (GETDATE()) FOR [fecha_asignacion]
GO
ALTER TABLE [dbo].[empleado_user] ADD CONSTRAINT [DF_empleado_user_fecha_asignacion] DEFAULT (GETDATE()) FOR [fecha_asignacion]
GO
ALTER TABLE [dbo].[instructor] ADD CONSTRAINT [DF_instructor_interno] DEFAULT ((0)) FOR [interno]
GO
ALTER TABLE [dbo].[instructor] ADD CONSTRAINT [DF_instructor_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [DF_capacitacion_porcentaje_aprobacion] DEFAULT ((70.00)) FOR [porcentaje_aprobacion]
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [DF_capacitacion_obligatoria] DEFAULT ((1)) FOR [obligatoria]
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [DF_capacitacion_permita_autogestion] DEFAULT ((1)) FOR [permite_autogestion]
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [DF_capacitacion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [DF_capacitacion_created_at] DEFAULT (GETDATE()) FOR [created_at]
GO
ALTER TABLE [dbo].[capacitacion_modulo] ADD CONSTRAINT [DF_capacitacion_modulo_requiere_evaluacion] DEFAULT ((0)) FOR [requiere_evaluacion]
GO
ALTER TABLE [dbo].[capacitacion_modulo] ADD CONSTRAINT [DF_capacitacion_modulo_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[capacitacion_modulo_seccion] ADD CONSTRAINT [DF_capacitacion_modulo_seccion_nivel] DEFAULT ((1)) FOR [nivel]
GO
ALTER TABLE [dbo].[capacitacion_modulo_seccion] ADD CONSTRAINT [DF_capacitacion_modulo_seccion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[capacitacion_recurso] ADD CONSTRAINT [DF_capacitacion_recurso_obligatorio] DEFAULT ((1)) FOR [obligatorio]
GO
ALTER TABLE [dbo].[capacitacion_recurso] ADD CONSTRAINT [DF_capacitacion_recurso_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[evaluacion] ADD CONSTRAINT [DF_evaluacion_aprobacion] DEFAULT ((70.00)) FOR [porcentaje_aprobacion]
GO
ALTER TABLE [dbo].[evaluacion] ADD CONSTRAINT [DF_evaluacion_activa] DEFAULT ((1)) FOR [activa]
GO
ALTER TABLE [dbo].[evaluacion_pregunta] ADD CONSTRAINT [DF_evaluacion_pregunta_puntaje] DEFAULT ((1.00)) FOR [puntaje]
GO
ALTER TABLE [dbo].[evaluacion_pregunta] ADD CONSTRAINT [DF_evaluacion_pregunta_activa] DEFAULT ((1)) FOR [activa]
GO
ALTER TABLE [dbo].[evaluacion_opcion] ADD CONSTRAINT [DF_evaluacion_opcion_es_correcta] DEFAULT ((0)) FOR [es_correcta]
GO
ALTER TABLE [dbo].[puestos_capacitacion] ADD CONSTRAINT [DF_puestos_capacitacion_obligatoria] DEFAULT ((1)) FOR [obligatoria]
GO
ALTER TABLE [dbo].[puestos_capacitacion] ADD CONSTRAINT [DF_puestos_capacitacion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[departamentos_capacitacion] ADD CONSTRAINT [DF_departamentos_capacitacion_obligatoria] DEFAULT ((1)) FOR [obligatoria]
GO
ALTER TABLE [dbo].[departamentos_capacitacion] ADD CONSTRAINT [DF_departamentos_capacitacion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[empleados_capacitacion] ADD CONSTRAINT [DF_empleados_capacitacion_obligatoria] DEFAULT ((1)) FOR [obligatoria]
GO
ALTER TABLE [dbo].[empleados_capacitacion] ADD CONSTRAINT [DF_empleados_capacitacion_estado] DEFAULT ((1)) FOR [estado]
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [DF_empleado_capacitacion_fecha_asignacion] DEFAULT (CAST(GETDATE() AS date)) FOR [fecha_asignacion]
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [DF_empleado_capacitacion_estado] DEFAULT ('pendiente') FOR [estado]
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [DF_empleado_capacitacion_progreso] DEFAULT ((0.00)) FOR [progreso]
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [DF_empleado_capacitacion_created_at] DEFAULT (GETDATE()) FOR [created_at]
GO
ALTER TABLE [dbo].[empleado_modulo_avance] ADD CONSTRAINT [DF_empleado_modulo_avance_estado] DEFAULT ('pendiente') FOR [estado]
GO
ALTER TABLE [dbo].[empleado_modulo_avance] ADD CONSTRAINT [DF_empleado_modulo_avance_progreso] DEFAULT ((0.00)) FOR [progreso]
GO
ALTER TABLE [dbo].[empleado_contenido_avance] ADD CONSTRAINT [DF_empleado_contenido_avance_estado] DEFAULT ('completado') FOR [estado]
GO
ALTER TABLE [dbo].[evaluacion_intento] ADD CONSTRAINT [DF_evaluacion_intento_fecha_inicio] DEFAULT (GETDATE()) FOR [fecha_inicio]
GO
ALTER TABLE [dbo].[evaluacion_intento] ADD CONSTRAINT [DF_evaluacion_intento_estado] DEFAULT ('en_proceso') FOR [estado]
GO
ALTER TABLE [dbo].[configuracion_aviso] ADD CONSTRAINT [DF_configuracion_aviso_enviar_empleado] DEFAULT ((1)) FOR [enviar_a_empleado]
GO
ALTER TABLE [dbo].[configuracion_aviso] ADD CONSTRAINT [DF_configuracion_aviso_enviar_admin] DEFAULT ((1)) FOR [enviar_a_admin]
GO
ALTER TABLE [dbo].[configuracion_aviso] ADD CONSTRAINT [DF_configuracion_aviso_activo] DEFAULT ((1)) FOR [activo]
GO
ALTER TABLE [dbo].[users] ADD CONSTRAINT [DF_users_recibe_avisos_capacitaciones] DEFAULT ((0)) FOR [recibe_avisos_capacitaciones]
GO
ALTER TABLE [dbo].[aviso_correo] ADD CONSTRAINT [DF_aviso_correo_estado] DEFAULT ('pendiente') FOR [estado]
GO
ALTER TABLE [dbo].[aviso_correo] ADD CONSTRAINT [DF_aviso_correo_intentos] DEFAULT ((0)) FOR [intentos_envio]
GO
ALTER TABLE [dbo].[historial_capacitacion_empleado] ADD CONSTRAINT [DF_historial_capacitacion_empleado_fecha] DEFAULT (GETDATE()) FOR [fecha_movimiento]
GO
ALTER TABLE [dbo].[instructor_user] ADD CONSTRAINT [DF_instructor_user_fecha_asignacion] DEFAULT (GETDATE()) FOR [fecha_asignacion]
GO

ALTER TABLE [dbo].[users] ADD CONSTRAINT [UQ_users_email] UNIQUE ([email])
GO
ALTER TABLE [dbo].[users] ADD CONSTRAINT [UQ_users_username] UNIQUE ([username])
GO
ALTER TABLE [dbo].[rol] ADD CONSTRAINT [UQ_rol_rol] UNIQUE ([rol])
GO
ALTER TABLE [dbo].[user_rol] ADD CONSTRAINT [UQ_user_rol_user_rol] UNIQUE ([id_user], [id_rol])
GO
ALTER TABLE [dbo].[empleado_user] ADD CONSTRAINT [UQ_empleado_user_empleado] UNIQUE ([id_empleado])
GO
ALTER TABLE [dbo].[empleado_user] ADD CONSTRAINT [UQ_empleado_user_user] UNIQUE ([id_user])
GO
ALTER TABLE [dbo].[instructor_user] ADD CONSTRAINT [UQ_instructor_user_user] UNIQUE ([id_user])
GO
ALTER TABLE [dbo].[instructor_user] ADD CONSTRAINT [UQ_instructor_user_instructor] UNIQUE ([id_instructor])
GO
ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [UQ_capacitacion_codigo] UNIQUE ([codigo])
GO
ALTER TABLE [dbo].[capacitacion_area] ADD CONSTRAINT [UQ_capacitacion_area] UNIQUE ([id_capacitacion], [id_area_capacitacion])
GO
ALTER TABLE [dbo].[capacitacion_modulo] ADD CONSTRAINT [UQ_capacitacion_modulo_orden] UNIQUE ([id_capacitacion], [orden])
GO
ALTER TABLE [dbo].[capacitacion_modulo_seccion] ADD CONSTRAINT [UQ_capacitacion_modulo_seccion_orden] UNIQUE ([id_capacitacion_modulo], [orden])
GO
ALTER TABLE [dbo].[capacitacion_recurso] ADD CONSTRAINT [UQ_capacitacion_recurso_orden] UNIQUE ([id_capacitacion_modulo], [orden])
GO
ALTER TABLE [dbo].[evaluacion_pregunta] ADD CONSTRAINT [UQ_evaluacion_pregunta_orden] UNIQUE ([id_evaluacion], [orden])
GO
ALTER TABLE [dbo].[evaluacion_opcion] ADD CONSTRAINT [UQ_evaluacion_opcion_orden] UNIQUE ([id_evaluacion_pregunta], [orden])
GO
ALTER TABLE [dbo].[puestos_capacitacion] ADD CONSTRAINT [UQ_puestos_capacitacion] UNIQUE ([id_puesto_trabajo_matriz], [id_capacitacion])
GO
ALTER TABLE [dbo].[departamentos_capacitacion] ADD CONSTRAINT [UQ_departamentos_capacitacion] UNIQUE ([id_departamento], [id_capacitacion])
GO
ALTER TABLE [dbo].[empleados_capacitacion] ADD CONSTRAINT [UQ_empleados_capacitacion] UNIQUE ([id_empleado], [id_capacitacion])
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [UQ_empleado_capacitacion_origen] UNIQUE ([id_empleado], [id_capacitacion], [origen_asignacion], [id_referencia_asignacion])
GO
ALTER TABLE [dbo].[configuracion_aviso] ADD CONSTRAINT [UQ_configuracion_aviso_tipo_aviso] UNIQUE ([tipo_aviso])
GO
ALTER TABLE [dbo].[empleado_modulo_avance] ADD CONSTRAINT [UQ_empleado_modulo_avance] UNIQUE ([id_empleado_capacitacion], [id_capacitacion_modulo])
GO
ALTER TABLE [dbo].[evaluacion_intento] ADD CONSTRAINT [UQ_evaluacion_intento_numero] UNIQUE ([id_evaluacion], [id_empleado], [numero_intento])
GO

ALTER TABLE dbo.ejercicio ADD CONSTRAINT FK_ejercicio_modulo FOREIGN KEY (id_capacitacion_modulo) REFERENCES dbo.capacitacion_modulo(id_capacitacion_modulo);
GO

ALTER TABLE dbo.ejercicio ADD CONSTRAINT FK_ejercicio_modulo_seccion FOREIGN KEY (id_capacitacion_modulo_seccion) REFERENCES dbo.capacitacion_modulo_seccion(id_capacitacion_modulo_seccion);
GO

ALTER TABLE dbo.ejercicio_pregunta ADD CONSTRAINT FK_ejercicio_pregunta_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES dbo.ejercicio(id_ejercicio);
GO

ALTER TABLE dbo.ejercicio_opcion ADD CONSTRAINT FK_ejercicio_opcion_pregunta FOREIGN KEY (id_ejercicio_pregunta) REFERENCES dbo.ejercicio_pregunta(id_ejercicio_pregunta);
GO

ALTER TABLE dbo.ejercicio_intento ADD CONSTRAINT FK_ejercicio_intento_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES dbo.ejercicio(id_ejercicio);
GO

ALTER TABLE dbo.ejercicio_intento ADD CONSTRAINT FK_ejercicio_intento_empleado FOREIGN KEY (id_empleado) REFERENCES dbo.empleado(id_empleado);
GO

ALTER TABLE dbo.ejercicio_intento ADD CONSTRAINT FK_ejercicio_intento_empleado_capacitacion FOREIGN KEY (id_empleado_capacitacion) REFERENCES dbo.empleado_capacitacion(id_empleado_capacitacion);
GO

ALTER TABLE dbo.ejercicio_intento_respuesta ADD CONSTRAINT FK_ejercicio_intento_respuesta_intento FOREIGN KEY (id_ejercicio_intento) REFERENCES dbo.ejercicio_intento(id_ejercicio_intento);
GO

ALTER TABLE dbo.ejercicio_intento_respuesta ADD CONSTRAINT FK_ejercicio_intento_respuesta_pregunta FOREIGN KEY (id_ejercicio_pregunta) REFERENCES dbo.ejercicio_pregunta(id_ejercicio_pregunta);
GO

ALTER TABLE [dbo].[puesto_trabajo_matriz]  WITH CHECK ADD CONSTRAINT [FK_puesto_trabajo_matriz_departamento]
FOREIGN KEY([id_departamento]) REFERENCES [dbo].[departamento] ([id_departamento])
GO
ALTER TABLE [dbo].[empleado]  WITH CHECK ADD CONSTRAINT [FK_empleado_puesto_trabajo_matriz]
FOREIGN KEY([id_puesto_trabajo_matriz]) REFERENCES [dbo].[puesto_trabajo_matriz] ([id_puesto_trabajo_matriz])
GO
ALTER TABLE [dbo].[user_rol]  WITH CHECK ADD CONSTRAINT [FK_user_rol_users]
FOREIGN KEY([id_user]) REFERENCES [dbo].[users] ([id]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[user_rol]  WITH CHECK ADD CONSTRAINT [FK_user_rol_rol]
FOREIGN KEY([id_rol]) REFERENCES [dbo].[rol] ([id_rol])
GO
ALTER TABLE [dbo].[empleado_user]  WITH CHECK ADD CONSTRAINT [FK_empleado_user_users]
FOREIGN KEY([id_user]) REFERENCES [dbo].[users] ([id]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[instructor_user]  WITH CHECK ADD CONSTRAINT [FK_instructor_user_users]
FOREIGN KEY([id_user]) REFERENCES [dbo].[users] ([id]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[capacitacion]  WITH CHECK ADD CONSTRAINT [FK_capacitacion_users_created_by]
FOREIGN KEY([created_by]) REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[instructor]  WITH CHECK ADD CONSTRAINT [FK_instructor_empleado]
FOREIGN KEY([id_empleado]) REFERENCES [dbo].[empleado] ([id_empleado])
GO
ALTER TABLE [dbo].[instructor] CHECK CONSTRAINT [FK_instructor_empleado]
GO
ALTER TABLE [dbo].[capacitacion_area]  WITH CHECK ADD CONSTRAINT [FK_capacitacion_area_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[capacitacion_area]  WITH CHECK ADD CONSTRAINT [FK_capacitacion_area_area_capacitacion]
FOREIGN KEY([id_area_capacitacion]) REFERENCES [dbo].[area_capacitacion] ([id_area_capacitacion])
GO
ALTER TABLE [dbo].[capacitacion_modulo]  WITH CHECK ADD CONSTRAINT [FK_capacitacion_modulo_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[capacitacion_recurso]  WITH CHECK ADD CONSTRAINT [FK_capacitacion_recurso_capacitacion_modulo]
FOREIGN KEY([id_capacitacion_modulo]) REFERENCES [dbo].[capacitacion_modulo] ([id_capacitacion_modulo]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[capacitacion_recurso] WITH CHECK ADD CONSTRAINT [FK_capacitacion_recurso_modulo_seccion]
FOREIGN KEY([id_capacitacion_modulo_seccion]) REFERENCES [dbo].[capacitacion_modulo_seccion] ([id_capacitacion_modulo_seccion])
GO
ALTER TABLE [dbo].[capacitacion_modulo_seccion] WITH CHECK ADD CONSTRAINT [FK_capacitacion_modulo_seccion_modulo]
FOREIGN KEY([id_capacitacion_modulo]) REFERENCES [dbo].[capacitacion_modulo] ([id_capacitacion_modulo]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[evaluacion] WITH CHECK ADD CONSTRAINT [FK_evaluacion_modulo_seccion]
FOREIGN KEY([id_capacitacion_modulo_seccion]) REFERENCES [dbo].[capacitacion_modulo_seccion] ([id_capacitacion_modulo_seccion])
GO
ALTER TABLE [dbo].[evaluacion]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_capacitacion_modulo]
FOREIGN KEY([id_capacitacion_modulo]) REFERENCES [dbo].[capacitacion_modulo] ([id_capacitacion_modulo]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[evaluacion_pregunta]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_pregunta_evaluacion]
FOREIGN KEY([id_evaluacion]) REFERENCES [dbo].[evaluacion] ([id_evaluacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[evaluacion_opcion]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_opcion_evaluacion_pregunta]
FOREIGN KEY([id_evaluacion_pregunta]) REFERENCES [dbo].[evaluacion_pregunta] ([id_evaluacion_pregunta]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[puestos_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_puestos_capacitacion_puesto]
FOREIGN KEY([id_puesto_trabajo_matriz]) REFERENCES [dbo].[puesto_trabajo_matriz] ([id_puesto_trabajo_matriz]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[puestos_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_puestos_capacitacion_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[departamentos_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_departamentos_capacitacion_departamento]
FOREIGN KEY([id_departamento]) REFERENCES [dbo].[departamento] ([id_departamento]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[departamentos_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_departamentos_capacitacion_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[empleados_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_empleados_capacitacion_empleado]
FOREIGN KEY([id_empleado]) REFERENCES [dbo].[empleado] ([id_empleado]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[empleados_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_empleados_capacitacion_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[empleado_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_empleado_capacitacion_capacitacion]
FOREIGN KEY([id_capacitacion]) REFERENCES [dbo].[capacitacion] ([id_capacitacion])
GO
ALTER TABLE [dbo].[empleado_capacitacion]  WITH CHECK ADD CONSTRAINT [FK_empleado_capacitacion_users]
FOREIGN KEY([id_usuario_asigno]) REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[empleado_modulo_avance]  WITH CHECK ADD CONSTRAINT [FK_empleado_modulo_avance_empleado_capacitacion]
FOREIGN KEY([id_empleado_capacitacion]) REFERENCES [dbo].[empleado_capacitacion] ([id_empleado_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[empleado_contenido_avance] WITH CHECK ADD CONSTRAINT [FK_empleado_contenido_avance_empleado_capacitacion]
FOREIGN KEY([id_empleado_capacitacion]) REFERENCES [dbo].[empleado_capacitacion] ([id_empleado_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[empleado_modulo_avance]  WITH CHECK ADD CONSTRAINT [FK_empleado_modulo_avance_capacitacion_modulo]
FOREIGN KEY([id_capacitacion_modulo]) REFERENCES [dbo].[capacitacion_modulo] ([id_capacitacion_modulo])
GO
ALTER TABLE [dbo].[evaluacion_intento]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_evaluacion]
FOREIGN KEY([id_evaluacion]) REFERENCES [dbo].[evaluacion] ([id_evaluacion])
GO
ALTER TABLE [dbo].[evaluacion_intento]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_empleado]
FOREIGN KEY([id_empleado]) REFERENCES [dbo].[empleado] ([id_empleado])
GO
ALTER TABLE [dbo].[evaluacion_intento]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_empleado_capacitacion]
FOREIGN KEY([id_empleado_capacitacion]) REFERENCES [dbo].[empleado_capacitacion] ([id_empleado_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[evaluacion_intento_respuesta]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_respuesta_intento]
FOREIGN KEY([id_evaluacion_intento]) REFERENCES [dbo].[evaluacion_intento] ([id_evaluacion_intento]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[evaluacion_intento_respuesta]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_respuesta_pregunta]
FOREIGN KEY([id_evaluacion_pregunta]) REFERENCES [dbo].[evaluacion_pregunta] ([id_evaluacion_pregunta])
GO
ALTER TABLE [dbo].[evaluacion_intento_respuesta]  WITH CHECK ADD CONSTRAINT [FK_evaluacion_intento_respuesta_opcion]
FOREIGN KEY([id_evaluacion_opcion]) REFERENCES [dbo].[evaluacion_opcion] ([id_evaluacion_opcion])
GO
ALTER TABLE [dbo].[aviso_correo]  WITH CHECK ADD CONSTRAINT [FK_aviso_correo_empleado_capacitacion]
FOREIGN KEY([id_empleado_capacitacion]) REFERENCES [dbo].[empleado_capacitacion] ([id_empleado_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[aviso_correo]  WITH CHECK ADD CONSTRAINT [FK_aviso_correo_configuracion_aviso]
FOREIGN KEY([id_configuracion_aviso]) REFERENCES [dbo].[configuracion_aviso] ([id_configuracion_aviso])
GO
ALTER TABLE [dbo].[historial_capacitacion_empleado]  WITH CHECK ADD CONSTRAINT [FK_historial_capacitacion_empleado_empleado_capacitacion]
FOREIGN KEY([id_empleado_capacitacion]) REFERENCES [dbo].[empleado_capacitacion] ([id_empleado_capacitacion]) ON DELETE CASCADE
GO
ALTER TABLE [dbo].[historial_capacitacion_empleado]  WITH CHECK ADD CONSTRAINT [FK_historial_capacitacion_empleado_user]
FOREIGN KEY([id_user]) REFERENCES [dbo].[users] ([id])
GO

ALTER TABLE [dbo].[capacitacion] ADD CONSTRAINT [CK_capacitacion_porcentaje_aprobacion] CHECK ([porcentaje_aprobacion] >= 0 AND [porcentaje_aprobacion] <= 100)
GO
ALTER TABLE [dbo].[capacitacion_modulo] ADD CONSTRAINT [CK_capacitacion_modulo_porcentaje_aprobacion] CHECK ([porcentaje_aprobacion] IS NULL OR ([porcentaje_aprobacion] >= 0 AND [porcentaje_aprobacion] <= 100))
GO
ALTER TABLE [dbo].[evaluacion] ADD CONSTRAINT [CK_evaluacion_porcentaje_aprobacion] CHECK ([porcentaje_aprobacion] >= 0 AND [porcentaje_aprobacion] <= 100)
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [CK_empleado_capacitacion_estado] CHECK ([estado] IN ('pendiente','en_proceso','aprobada','reprobada','vencida','cancelada'))
GO
ALTER TABLE [dbo].[empleado_capacitacion] ADD CONSTRAINT [CK_empleado_capacitacion_progreso] CHECK ([progreso] >= 0 AND [progreso] <= 100)
GO
ALTER TABLE [dbo].[empleado_modulo_avance] ADD CONSTRAINT [CK_empleado_modulo_avance_estado] CHECK ([estado] IN ('pendiente','en_proceso','completado','reprobado','vencido'))
GO
ALTER TABLE [dbo].[empleado_modulo_avance] ADD CONSTRAINT [CK_empleado_modulo_avance_progreso] CHECK ([progreso] >= 0 AND [progreso] <= 100)
GO
ALTER TABLE [dbo].[empleado_contenido_avance] ADD CONSTRAINT [CK_empleado_contenido_avance_tipo] CHECK ([tipo_contenido] IN ('seccion','recurso','ejercicio','evaluacion'))
GO

ALTER TABLE [dbo].[empleado_contenido_avance] ADD CONSTRAINT [CK_empleado_contenido_avance_estado] CHECK ([estado] IN ('visto','completado'))
GO
ALTER TABLE [dbo].[evaluacion_intento] ADD CONSTRAINT [CK_evaluacion_intento_estado] CHECK ([estado] IN ('en_proceso','finalizado','cancelado','revisado'))
GO
ALTER TABLE [dbo].[configuracion_aviso] ADD CONSTRAINT [CK_configuracion_aviso_tipo] CHECK ([tipo_aviso] IN ('asignada','por_vencer','vencida','terminada'))
GO
ALTER TABLE [dbo].[aviso_correo] ADD CONSTRAINT [CK_aviso_correo_destinatario] CHECK ([destinatario_tipo] IN ('empleado','admin'))
GO
ALTER TABLE [dbo].[aviso_correo] ADD CONSTRAINT [CK_aviso_correo_estado] CHECK ([estado] IN ('pendiente','enviado','error','cancelado'))
GO

/* ============================================================
   INDICES
   ============================================================ */
CREATE INDEX [IX_empleado_id_puesto_trabajo_matriz] ON [dbo].[empleado]([id_puesto_trabajo_matriz])
GO
CREATE INDEX [IX_capacitacion_created_by] ON [dbo].[capacitacion]([created_by])
GO
CREATE INDEX [IX_capacitacion_id_instructor] ON [dbo].[capacitacion]([id_instructor])
GO
CREATE INDEX [IX_instructor_id_empleado] ON [dbo].[instructor]([id_empleado])
GO
CREATE INDEX [IX_capacitacion_modulo_id_capacitacion] ON [dbo].[capacitacion_modulo]([id_capacitacion])
GO
CREATE INDEX [IX_capacitacion_modulo_seccion_modulo] ON [dbo].[capacitacion_modulo_seccion]([id_capacitacion_modulo])
GO
CREATE INDEX [IX_capacitacion_modulo_seccion_padre] ON [dbo].[capacitacion_modulo_seccion]([id_seccion_padre])
GO
CREATE INDEX [IX_capacitacion_recurso_modulo_seccion] ON [dbo].[capacitacion_recurso]([id_capacitacion_modulo_seccion])
GO
CREATE INDEX [IX_ejercicio_modulo_seccion] ON [dbo].[ejercicio]([id_capacitacion_modulo_seccion])
GO
CREATE INDEX [IX_evaluacion_modulo_seccion] ON [dbo].[evaluacion]([id_capacitacion_modulo_seccion])
GO
CREATE INDEX [IX_puestos_capacitacion_id_capacitacion] ON [dbo].[puestos_capacitacion]([id_capacitacion])
GO
CREATE INDEX [IX_departamentos_capacitacion_id_capacitacion] ON [dbo].[departamentos_capacitacion]([id_capacitacion])
GO
CREATE INDEX [IX_empleados_capacitacion_id_capacitacion] ON [dbo].[empleados_capacitacion]([id_capacitacion])
GO
CREATE INDEX [IX_empleado_capacitacion_estado_fecha] ON [dbo].[empleado_capacitacion]([estado], [fecha_limite], [fecha_vencimiento])
GO
CREATE INDEX [IX_empleado_capacitacion_id_empleado] ON [dbo].[empleado_capacitacion]([id_empleado])
GO
CREATE INDEX [IX_empleado_modulo_avance_id_empleado_capacitacion] ON [dbo].[empleado_modulo_avance]([id_empleado_capacitacion])
GO
CREATE INDEX [IX_empleado_contenido_avance_capacitacion] ON [dbo].[empleado_contenido_avance]([id_empleado_capacitacion])
GO
CREATE INDEX [IX_empleado_contenido_avance_modulo] ON [dbo].[empleado_contenido_avance]([id_capacitacion_modulo])
GO
CREATE INDEX [IX_empleado_contenido_avance_tipo] ON [dbo].[empleado_contenido_avance]([tipo_contenido])
GO
CREATE INDEX [IX_evaluacion_intento_id_empleado] ON [dbo].[evaluacion_intento]([id_empleado])
GO
CREATE INDEX [IX_aviso_correo_estado_fecha_programada] ON [dbo].[aviso_correo]([estado], [fecha_programada])
GO
CREATE INDEX [IX_aviso_correo_id_configuracion_aviso] ON [dbo].[aviso_correo]([id_configuracion_aviso])
GO

/* ============================================================
   INDICES DE OPTIMIZACION - NAVEGACION, SEGUIMIENTO Y DASHBOARD
   ============================================================ */
CREATE INDEX [IX_ec_capacitacion_estado_empleado]
ON [dbo].[empleado_capacitacion]([id_capacitacion], [estado], [id_empleado])
INCLUDE ([progreso], [nota_final], [aprobado], [fecha_inicio], [fecha_finalizacion], [fecha_limite], [fecha_vencimiento])
GO

CREATE INDEX [IX_ec_empleado_estado_fechas]
ON [dbo].[empleado_capacitacion]([id_empleado], [estado], [fecha_vencimiento], [fecha_limite])
INCLUDE ([id_capacitacion], [progreso], [nota_final], [aprobado], [fecha_inicio], [fecha_finalizacion])
GO

CREATE INDEX [IX_eca_busqueda_contenido]
ON [dbo].[empleado_contenido_avance](
    [id_empleado_capacitacion],
    [id_capacitacion_modulo],
    [tipo_contenido],
    [id_capacitacion_modulo_seccion],
    [id_capacitacion_recurso],
    [id_ejercicio],
    [id_evaluacion]
)
INCLUDE ([estado], [fecha_inicio], [fecha_ultima_actividad], [fecha_completado])
GO

CREATE INDEX [IX_eval_intento_cap_eval_numero]
ON [dbo].[evaluacion_intento]([id_empleado_capacitacion], [id_evaluacion], [numero_intento] DESC)
INCLUDE ([id_empleado], [aprobado], [estado], [fecha_inicio], [fecha_fin], [nota])
GO

CREATE INDEX [IX_ejer_intento_cap_ejer_numero]
ON [dbo].[ejercicio_intento]([id_empleado_capacitacion], [id_ejercicio], [numero_intento] DESC)
INCLUDE ([id_empleado], [aprobado], [estado], [fecha_inicio], [fecha_fin], [porcentaje_obtenido], [puntaje_obtenido])
GO

CREATE INDEX [IX_historial_capacitacion_fecha]
ON [dbo].[historial_capacitacion_empleado]([id_empleado_capacitacion], [fecha_movimiento] DESC)
INCLUDE ([estado_anterior], [estado_nuevo], [id_user])
GO

CREATE INDEX [IX_recurso_modulo_estado_orden]
ON [dbo].[capacitacion_recurso]([id_capacitacion_modulo], [estado], [orden], [id_capacitacion_recurso])
INCLUDE ([id_capacitacion_modulo_seccion], [titulo], [tipo_recurso])
GO

CREATE INDEX [IX_ejercicio_modulo_estado_orden]
ON [dbo].[ejercicio]([id_capacitacion_modulo], [estado], [orden], [id_ejercicio])
INCLUDE ([id_capacitacion_modulo_seccion], [titulo], [obligatorio], [intentos_maximos], [porcentaje_aprobacion])
GO

CREATE INDEX [IX_evaluacion_modulo_activa_orden]
ON [dbo].[evaluacion]([id_capacitacion_modulo], [activa], [orden], [id_evaluacion])
INCLUDE ([id_capacitacion_modulo_seccion], [titulo], [intentos_maximos], [porcentaje_aprobacion])
GO

CREATE INDEX [IX_user_rol_user_rol]
ON [dbo].[user_rol]([id_user], [id_rol])
GO

/* ============================================================
   DATOS BASE
   ============================================================ */

/* ============================================================
   DATOS BASE
   ============================================================ */
SET IDENTITY_INSERT [dbo].[rol] ON
GO
INSERT INTO [dbo].[rol] ([id_rol], [rol], [descripcion], [estado]) VALUES (1, N'admin', N'Administrador del sistema de capacitaciones', 1)
INSERT INTO [dbo].[rol] ([id_rol], [rol], [descripcion], [estado]) VALUES (2, N'empleado', N'Empleado que recibe capacitaciones', 1)
INSERT INTO [dbo].[rol] ([id_rol], [rol], [descripcion], [estado]) VALUES (3, N'instructor', N'Instructor que gestiona sus propias capacitaciones', 1)
GO
SET IDENTITY_INSERT [dbo].[rol] OFF
GO

/* ============================================================
   SPATIE LARAVEL PERMISSION - ROLES BASE
   ============================================================ */

DECLARE @AhoraSpatie DATETIME2 = SYSDATETIME();

INSERT INTO dbo.roles (name, guard_name, created_at, updated_at)
SELECT 'admin', 'web', @AhoraSpatie, @AhoraSpatie
WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.roles
    WHERE name = 'admin'
    AND guard_name = 'web'
);

INSERT INTO dbo.roles (name, guard_name, created_at, updated_at)
SELECT 'instructor', 'web', @AhoraSpatie, @AhoraSpatie
WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.roles
    WHERE name = 'instructor'
    AND guard_name = 'web'
);

INSERT INTO dbo.roles (name, guard_name, created_at, updated_at)
SELECT 'usuario', 'web', @AhoraSpatie, @AhoraSpatie
WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.roles
    WHERE name = 'usuario'
    AND guard_name = 'web'
);


/* ============================================================
   SPATIE LARAVEL PERMISSION - PERMISOS BASE
   ============================================================ */

INSERT INTO dbo.permissions (name, guard_name, created_at, updated_at)
SELECT permiso, 'web', @AhoraSpatie, @AhoraSpatie
FROM (
    VALUES
        ('dashboard.admin.ver'),
        ('usuarios.ver'),
        ('usuarios.crear'),
        ('usuarios.editar'),
        ('usuarios.eliminar'),
        ('instructores.ver'),
        ('instructores.crear'),
        ('instructores.editar'),
        ('instructores.eliminar'),
        ('capacitaciones.ver'),
        ('capacitaciones.crear'),
        ('capacitaciones.editar'),
        ('capacitaciones.eliminar'),
        ('constructor.ver'),
        ('modulos.ver'),
        ('modulos.crear'),
        ('modulos.editar'),
        ('modulos.eliminar'),
        ('recursos.ver'),
        ('recursos.crear'),
        ('recursos.editar'),
        ('recursos.eliminar'),
        ('evaluaciones.ver'),
        ('evaluaciones.crear'),
        ('evaluaciones.editar'),
        ('evaluaciones.eliminar'),
        ('ejercicios.ver'),
        ('ejercicios.crear'),
        ('ejercicios.editar'),
        ('ejercicios.eliminar'),
        ('asignaciones.ver'),
        ('asignaciones.crear'),
        ('asignaciones.editar'),
        ('matriz.ver'),
        ('necesidades.ver'),
        ('seguimiento.ver'),
        ('seguimiento.revisar'),
        ('reportes.ver'),
        ('reportes.exportar'),
        ('avisos.ver'),
        ('avisos.configurar'),
        ('avisos.generar'),
        ('avisos.enviar'),
        ('dashboard.usuario.ver'),
        ('mis_capacitaciones.ver'),
        ('mis_modulos.ver'),
        ('mis_recursos.ver'),
        ('mis_ejercicios.ver'),
        ('mis_evaluaciones.ver'),
        ('mis_calificaciones.ver')
) AS permisos(permiso)
WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.permissions
    WHERE name = permisos.permiso
    AND guard_name = 'web'
);


/* ============================================================
   SPATIE LARAVEL PERMISSION - ASIGNAR PERMISOS A ROLES
   ============================================================ */

/* Admin recibe todos los permisos */
INSERT INTO dbo.role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM dbo.permissions p
INNER JOIN dbo.roles r
    ON r.name = 'admin'
   AND r.guard_name = 'web'
WHERE NOT EXISTS (
    SELECT 1
    FROM dbo.role_has_permissions rhp
    WHERE rhp.permission_id = p.id
    AND rhp.role_id = r.id
);


/* Instructor recibe permisos relacionados con capacitación */
INSERT INTO dbo.role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM dbo.permissions p
INNER JOIN dbo.roles r
    ON r.name = 'instructor'
   AND r.guard_name = 'web'
WHERE p.name IN (
    'dashboard.admin.ver',
    'capacitaciones.ver',
    'constructor.ver',
    'modulos.ver',
    'recursos.ver',
    'evaluaciones.ver',
    'ejercicios.ver',
    'seguimiento.ver'
)
AND NOT EXISTS (
    SELECT 1
    FROM dbo.role_has_permissions rhp
    WHERE rhp.permission_id = p.id
    AND rhp.role_id = r.id
);


/* Usuario recibe permisos de su propio flujo */
INSERT INTO dbo.role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM dbo.permissions p
INNER JOIN dbo.roles r
    ON r.name = 'usuario'
   AND r.guard_name = 'web'
WHERE p.name IN (
    'dashboard.usuario.ver',
    'mis_capacitaciones.ver',
    'mis_modulos.ver',
    'mis_recursos.ver',
    'mis_ejercicios.ver',
    'mis_evaluaciones.ver',
    'mis_calificaciones.ver'
)
AND NOT EXISTS (
    SELECT 1
    FROM dbo.role_has_permissions rhp
    WHERE rhp.permission_id = p.id
    AND rhp.role_id = r.id
);


/* ============================================================
   SPATIE LARAVEL PERMISSION - SINCRONIZAR ROLES ACTUALES
   VERSIÓN CORREGIDA PARA TU TABLA dbo.rol
   ============================================================ */

IF OBJECT_ID('dbo.rol', 'U') IS NOT NULL
AND OBJECT_ID('dbo.user_rol', 'U') IS NOT NULL
AND OBJECT_ID('dbo.users', 'U') IS NOT NULL
BEGIN
    DECLARE @RolIdColumnSpatie SYSNAME;
    DECLARE @RolNameColumnSpatie SYSNAME;
    DECLARE @UserRolUserColumnSpatie SYSNAME;
    DECLARE @UserRolRolColumnSpatie SYSNAME;
    DECLARE @SQLSpatie NVARCHAR(MAX);

    SELECT @RolIdColumnSpatie =
        CASE
            WHEN COL_LENGTH('dbo.rol', 'id_rol') IS NOT NULL THEN 'id_rol'
            WHEN COL_LENGTH('dbo.rol', 'rol_id') IS NOT NULL THEN 'rol_id'
            WHEN COL_LENGTH('dbo.rol', 'id') IS NOT NULL THEN 'id'
            ELSE NULL
        END;

    SELECT @RolNameColumnSpatie =
        CASE
            WHEN COL_LENGTH('dbo.rol', 'rol') IS NOT NULL THEN 'rol'
            WHEN COL_LENGTH('dbo.rol', 'nombre') IS NOT NULL THEN 'nombre'
            WHEN COL_LENGTH('dbo.rol', 'nombre_rol') IS NOT NULL THEN 'nombre_rol'
            WHEN COL_LENGTH('dbo.rol', 'descripcion') IS NOT NULL THEN 'descripcion'
            WHEN COL_LENGTH('dbo.rol', 'descripcion_rol') IS NOT NULL THEN 'descripcion_rol'
            WHEN COL_LENGTH('dbo.rol', 'tipo_rol') IS NOT NULL THEN 'tipo_rol'
            WHEN COL_LENGTH('dbo.rol', 'name') IS NOT NULL THEN 'name'
            ELSE NULL
        END;

    SELECT @UserRolUserColumnSpatie =
        CASE
            WHEN COL_LENGTH('dbo.user_rol', 'id_user') IS NOT NULL THEN 'id_user'
            WHEN COL_LENGTH('dbo.user_rol', 'id_usuario') IS NOT NULL THEN 'id_usuario'
            WHEN COL_LENGTH('dbo.user_rol', 'user_id') IS NOT NULL THEN 'user_id'
            ELSE NULL
        END;

    SELECT @UserRolRolColumnSpatie =
        CASE
            WHEN COL_LENGTH('dbo.user_rol', 'id_rol') IS NOT NULL THEN 'id_rol'
            WHEN COL_LENGTH('dbo.user_rol', 'rol_id') IS NOT NULL THEN 'rol_id'
            WHEN COL_LENGTH('dbo.user_rol', 'id_role') IS NOT NULL THEN 'id_role'
            ELSE NULL
        END;

    IF @RolIdColumnSpatie IS NOT NULL
    AND @RolNameColumnSpatie IS NOT NULL
    AND @UserRolUserColumnSpatie IS NOT NULL
    AND @UserRolRolColumnSpatie IS NOT NULL
    BEGIN
        SET @SQLSpatie = '
        INSERT INTO dbo.roles (name, guard_name, created_at, updated_at)
        SELECT DISTINCT
            CASE
                WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''admin'', ''administrador'') THEN ''admin''
                WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''instructor'', ''instructores'') THEN ''instructor''
                WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''usuario'', ''user'', ''empleado'', ''empleados'') THEN ''usuario''
                ELSE LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100)))))
            END,
            ''web'',
            SYSDATETIME(),
            SYSDATETIME()
        FROM dbo.rol r
        WHERE r.' + QUOTENAME(@RolNameColumnSpatie) + ' IS NOT NULL
        AND NOT EXISTS (
            SELECT 1
            FROM dbo.roles sr
            WHERE sr.name =
                CASE
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''admin'', ''administrador'') THEN ''admin''
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''instructor'', ''instructores'') THEN ''instructor''
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''usuario'', ''user'', ''empleado'', ''empleados'') THEN ''usuario''
                    ELSE LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100)))))
                END
            AND sr.guard_name = ''web''
        );
        ';

        EXEC sp_executesql @SQLSpatie;

        SET @SQLSpatie = '
        INSERT INTO dbo.model_has_roles (role_id, model_type, model_id)
        SELECT DISTINCT
            sr.id,
            ''App\Models\User'',
            CAST(ur.' + QUOTENAME(@UserRolUserColumnSpatie) + ' AS BIGINT)
        FROM dbo.user_rol ur
        INNER JOIN dbo.rol r
            ON r.' + QUOTENAME(@RolIdColumnSpatie) + ' = ur.' + QUOTENAME(@UserRolRolColumnSpatie) + '
        INNER JOIN dbo.roles sr
            ON sr.name =
                CASE
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''admin'', ''administrador'') THEN ''admin''
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''instructor'', ''instructores'') THEN ''instructor''
                    WHEN LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100))))) IN (''usuario'', ''user'', ''empleado'', ''empleados'') THEN ''usuario''
                    ELSE LOWER(LTRIM(RTRIM(CAST(r.' + QUOTENAME(@RolNameColumnSpatie) + ' AS NVARCHAR(100)))))
                END
           AND sr.guard_name = ''web''
        INNER JOIN dbo.users u
            ON u.id = ur.' + QUOTENAME(@UserRolUserColumnSpatie) + '
        WHERE NOT EXISTS (
            SELECT 1
            FROM dbo.model_has_roles mhr
            WHERE mhr.role_id = sr.id
            AND mhr.model_type = ''App\Models\User''
            AND mhr.model_id = CAST(ur.' + QUOTENAME(@UserRolUserColumnSpatie) + ' AS BIGINT)
        );
        ';

        EXEC sp_executesql @SQLSpatie;
    END
END
GO

UPDATE u
SET u.recibe_avisos_capacitaciones = 1
FROM dbo.users u
WHERE u.estado = 1
  AND EXISTS (
      SELECT 1
      FROM dbo.user_rol ur
      INNER JOIN dbo.rol r ON r.id_rol = ur.id_rol
      WHERE ur.id_user = u.id
        AND r.rol = 'admin'
  )
GO

INSERT INTO [dbo].[configuracion_aviso] ([tipo_aviso], [dias_anticipacion], [enviar_a_empleado], [enviar_a_admin], [activo]) VALUES ('asignada', NULL, 1, 1, 1)
INSERT INTO [dbo].[configuracion_aviso] ([tipo_aviso], [dias_anticipacion], [enviar_a_empleado], [enviar_a_admin], [activo]) VALUES ('por_vencer', 2, 1, 1, 1)
INSERT INTO [dbo].[configuracion_aviso] ([tipo_aviso], [dias_anticipacion], [enviar_a_empleado], [enviar_a_admin], [activo]) VALUES ('vencida', NULL, 1, 1, 1)
INSERT INTO [dbo].[configuracion_aviso] ([tipo_aviso], [dias_anticipacion], [enviar_a_empleado], [enviar_a_admin], [activo]) VALUES ('terminada', NULL, 1, 1, 1)
GO

/* ============================================================
   VISTAS DE REPORTE
   ============================================================ */
CREATE VIEW [dbo].[vw_expediente_capacitacion_empleado]
AS
SELECT
    ec.id_empleado_capacitacion,
    e.id_empleado,
    e.nombre_completo,
    e.codigo_empleado,
    e.correo,
    pt.id_puesto_trabajo_matriz,
    pt.puesto_trabajo_matriz,
    d.id_departamento,
    d.departamento,
    c.id_capacitacion,
    c.capacitacion,
    ec.origen_asignacion,
    ec.obligatoria,
    ec.fecha_asignacion,
    ec.fecha_inicio,
    ec.fecha_limite,
    ec.fecha_vencimiento,
    ec.fecha_finalizacion,
    ec.estado,
    ec.progreso,
    ec.nota_final,
    ec.aprobado
FROM dbo.empleado_capacitacion ec
INNER JOIN dbo.empleado e ON e.id_empleado = ec.id_empleado
INNER JOIN dbo.puesto_trabajo_matriz pt ON pt.id_puesto_trabajo_matriz = e.id_puesto_trabajo_matriz
INNER JOIN dbo.departamento d ON d.id_departamento = pt.id_departamento
INNER JOIN dbo.capacitacion c ON c.id_capacitacion = ec.id_capacitacion
GO

CREATE VIEW [dbo].[vw_reporte_puesto_capacitacion]
AS
SELECT
    pt.id_puesto_trabajo_matriz,
    pt.puesto_trabajo_matriz,
    d.departamento,
    COUNT(ec.id_empleado_capacitacion) AS total_asignadas,
    SUM(CASE WHEN ec.estado = 'aprobada' THEN 1 ELSE 0 END) AS total_aprobadas,
    SUM(CASE WHEN ec.estado = 'reprobada' THEN 1 ELSE 0 END) AS total_reprobadas,
    SUM(CASE WHEN ec.estado = 'en_proceso' THEN 1 ELSE 0 END) AS total_en_proceso,
    SUM(CASE WHEN ec.estado = 'pendiente' THEN 1 ELSE 0 END) AS total_pendientes,
    SUM(CASE WHEN ec.estado = 'vencida' THEN 1 ELSE 0 END) AS total_vencidas
FROM dbo.puesto_trabajo_matriz pt
INNER JOIN dbo.departamento d ON d.id_departamento = pt.id_departamento
LEFT JOIN dbo.empleado e ON e.id_puesto_trabajo_matriz = pt.id_puesto_trabajo_matriz
LEFT JOIN dbo.empleado_capacitacion ec ON ec.id_empleado = e.id_empleado
GROUP BY pt.id_puesto_trabajo_matriz, pt.puesto_trabajo_matriz, d.departamento
GO

CREATE VIEW [dbo].[vw_reporte_departamento_capacitacion]
AS
SELECT
    d.id_departamento,
    d.departamento,
    COUNT(ec.id_empleado_capacitacion) AS total_asignadas,
    SUM(CASE WHEN ec.estado = 'aprobada' THEN 1 ELSE 0 END) AS total_aprobadas,
    SUM(CASE WHEN ec.estado = 'reprobada' THEN 1 ELSE 0 END) AS total_reprobadas,
    SUM(CASE WHEN ec.estado = 'en_proceso' THEN 1 ELSE 0 END) AS total_en_proceso,
    SUM(CASE WHEN ec.estado = 'pendiente' THEN 1 ELSE 0 END) AS total_pendientes,
    SUM(CASE WHEN ec.estado = 'vencida' THEN 1 ELSE 0 END) AS total_vencidas
FROM dbo.departamento d
LEFT JOIN dbo.puesto_trabajo_matriz pt ON pt.id_departamento = d.id_departamento
LEFT JOIN dbo.empleado e ON e.id_puesto_trabajo_matriz = pt.id_puesto_trabajo_matriz
LEFT JOIN dbo.empleado_capacitacion ec ON ec.id_empleado = e.id_empleado
GROUP BY d.id_departamento, d.departamento
GO

CREATE VIEW [dbo].[vw_reporte_capacitacion_general]
AS
SELECT
    c.id_capacitacion,
    c.capacitacion,
    c.codigo,
    COUNT(ec.id_empleado_capacitacion) AS total_asignadas,
    SUM(CASE WHEN ec.estado = 'aprobada' THEN 1 ELSE 0 END) AS total_aprobadas,
    SUM(CASE WHEN ec.estado = 'reprobada' THEN 1 ELSE 0 END) AS total_reprobadas,
    SUM(CASE WHEN ec.estado = 'en_proceso' THEN 1 ELSE 0 END) AS total_en_proceso,
    SUM(CASE WHEN ec.estado = 'pendiente' THEN 1 ELSE 0 END) AS total_pendientes,
    SUM(CASE WHEN ec.estado = 'vencida' THEN 1 ELSE 0 END) AS total_vencidas,
    AVG(CAST(ec.nota_final AS decimal(10,2))) AS promedio_nota
FROM dbo.capacitacion c
LEFT JOIN dbo.empleado_capacitacion ec ON ec.id_capacitacion = c.id_capacitacion
GROUP BY c.id_capacitacion, c.capacitacion, c.codigo
GO

CREATE VIEW [dbo].[vw_capacitaciones_por_vencer]
AS
SELECT
    ec.id_empleado_capacitacion,
    e.nombre_completo,
    e.correo,
    c.capacitacion,
    ec.fecha_limite,
    ec.fecha_vencimiento,
    ec.estado,
    DATEDIFF(DAY, CAST(GETDATE() AS date), ec.fecha_limite) AS dias_restantes
FROM dbo.empleado_capacitacion ec
INNER JOIN dbo.empleado e ON e.id_empleado = ec.id_empleado
INNER JOIN dbo.capacitacion c ON c.id_capacitacion = ec.id_capacitacion
WHERE ec.estado IN ('pendiente', 'en_proceso')
  AND ec.fecha_limite IS NOT NULL
GO

/* ============================================================
   PROCEDIMIENTOS
   ============================================================ */
CREATE PROCEDURE [dbo].[sp_generar_empleado_capacitacion_desde_asignaciones]
    @p_id_empleado INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    ;WITH base AS (
        SELECT
            e.id_empleado,
            pc.id_capacitacion,
            CAST('PUESTO' AS varchar(20)) AS origen_asignacion,
            pc.id_puestos_capacitacion AS id_referencia_asignacion,
            pc.obligatoria,
            ISNULL(pc.fecha_asignacion, CAST(GETDATE() AS date)) AS fecha_asignacion,
            pc.dias_para_vencer
        FROM dbo.empleado e
        INNER JOIN dbo.puestos_capacitacion pc
            ON pc.id_puesto_trabajo_matriz = e.id_puesto_trabajo_matriz
        WHERE pc.estado = 1
          AND (@p_id_empleado IS NULL OR e.id_empleado = @p_id_empleado)

        UNION ALL

        SELECT
            e.id_empleado,
            dc.id_capacitacion,
            CAST('DEPARTAMENTO' AS varchar(20)) AS origen_asignacion,
            dc.id_departamentos_capacitacion AS id_referencia_asignacion,
            dc.obligatoria,
            ISNULL(dc.fecha_asignacion, CAST(GETDATE() AS date)) AS fecha_asignacion,
            dc.dias_para_vencer
        FROM dbo.empleado e
        INNER JOIN dbo.puesto_trabajo_matriz pt
            ON pt.id_puesto_trabajo_matriz = e.id_puesto_trabajo_matriz
        INNER JOIN dbo.departamentos_capacitacion dc
            ON dc.id_departamento = pt.id_departamento
        WHERE dc.estado = 1
          AND (@p_id_empleado IS NULL OR e.id_empleado = @p_id_empleado)

        UNION ALL

        SELECT
            ec.id_empleado,
            ec.id_capacitacion,
            CAST('EMPLEADO' AS varchar(20)) AS origen_asignacion,
            ec.id_empleados_capacitacion AS id_referencia_asignacion,
            ec.obligatoria,
            ISNULL(ec.fecha_asignacion, CAST(GETDATE() AS date)) AS fecha_asignacion,
            ec.dias_para_vencer
        FROM dbo.empleados_capacitacion ec
        WHERE ec.estado = 1
          AND (@p_id_empleado IS NULL OR ec.id_empleado = @p_id_empleado)
    )
    INSERT INTO dbo.empleado_capacitacion
    (
        id_empleado,
        id_capacitacion,
        origen_asignacion,
        id_referencia_asignacion,
        obligatoria,
        fecha_asignacion,
        fecha_limite,
        fecha_vencimiento,
        estado,
        progreso,
        created_at,
        updated_at
    )
    SELECT
        b.id_empleado,
        b.id_capacitacion,
        b.origen_asignacion,
        b.id_referencia_asignacion,
        b.obligatoria,
        b.fecha_asignacion,
        CASE WHEN b.dias_para_vencer IS NULL THEN NULL ELSE DATEADD(DAY, b.dias_para_vencer, b.fecha_asignacion) END,
        CASE WHEN b.dias_para_vencer IS NULL THEN NULL ELSE DATEADD(DAY, b.dias_para_vencer, b.fecha_asignacion) END,
        'pendiente',
        0,
        GETDATE(),
        GETDATE()
    FROM base b
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.empleado_capacitacion x
        WHERE x.id_empleado = b.id_empleado
          AND x.id_capacitacion = b.id_capacitacion
          AND x.origen_asignacion = b.origen_asignacion
          AND ISNULL(x.id_referencia_asignacion, 0) = ISNULL(b.id_referencia_asignacion, 0)
    );
END
GO

CREATE PROCEDURE [dbo].[sp_reporte_empleado_capacitacion]
    @p_id_empleado INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.vw_expediente_capacitacion_empleado
    WHERE id_empleado = @p_id_empleado
    ORDER BY fecha_asignacion DESC, capacitacion ASC;
END
GO

CREATE PROCEDURE [dbo].[sp_reporte_puesto_capacitacion]
    @p_id_puesto_trabajo_matriz INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.vw_reporte_puesto_capacitacion
    WHERE @p_id_puesto_trabajo_matriz IS NULL
       OR id_puesto_trabajo_matriz = @p_id_puesto_trabajo_matriz
    ORDER BY puesto_trabajo_matriz ASC;
END
GO

CREATE PROCEDURE [dbo].[sp_reporte_departamento_capacitacion]
    @p_id_departamento INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.vw_reporte_departamento_capacitacion
    WHERE @p_id_departamento IS NULL
       OR id_departamento = @p_id_departamento
    ORDER BY departamento ASC;
END
GO

CREATE PROCEDURE [dbo].[sp_reporte_capacitacion]
    @p_id_capacitacion INT = NULL,
    @p_fecha_inicio DATE = NULL,
    @p_fecha_fin DATE = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        ece.id_empleado_capacitacion,
        ece.id_empleado,
        ece.nombre_completo,
        ece.codigo_empleado,
        ece.departamento,
        ece.puesto_trabajo_matriz,
        ece.id_capacitacion,
        ece.capacitacion,
        ece.fecha_asignacion,
        ece.fecha_inicio,
        ece.fecha_finalizacion,
        ece.fecha_limite,
        ece.estado,
        ece.progreso,
        ece.nota_final,
        ece.aprobado
    FROM dbo.vw_expediente_capacitacion_empleado ece
    WHERE (@p_id_capacitacion IS NULL OR ece.id_capacitacion = @p_id_capacitacion)
      AND (@p_fecha_inicio IS NULL OR CAST(ece.fecha_asignacion AS date) >= @p_fecha_inicio)
      AND (@p_fecha_fin IS NULL OR CAST(ece.fecha_asignacion AS date) <= @p_fecha_fin)
    ORDER BY ece.capacitacion ASC, ece.nombre_completo ASC;
END
GO

CREATE PROCEDURE [dbo].[sp_aviso_correos_pendientes]
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        ac.id_aviso_correo,
        ac.tipo_aviso,
        ac.destinatario_tipo,
        ac.destinatario_email,
        ac.asunto,
        ac.mensaje,
        ac.fecha_programada,
        ec.id_empleado_capacitacion,
        e.nombre_completo,
        c.capacitacion,
        ec.estado,
        ec.fecha_limite,
        ec.fecha_vencimiento
    FROM dbo.aviso_correo ac
    INNER JOIN dbo.empleado_capacitacion ec ON ec.id_empleado_capacitacion = ac.id_empleado_capacitacion
    INNER JOIN dbo.empleado e ON e.id_empleado = ec.id_empleado
    INNER JOIN dbo.capacitacion c ON c.id_capacitacion = ec.id_capacitacion
    WHERE ac.estado = 'pendiente'
      AND ac.fecha_programada <= GETDATE()
    ORDER BY ac.fecha_programada ASC;
END
GO

/* ============================================================
   EJEMPLOS DE USO
   ============================================================
   EXEC dbo.sp_generar_empleado_capacitacion_desde_asignaciones;
   EXEC dbo.sp_generar_empleado_capacitacion_desde_asignaciones @p_id_empleado = 1;
   EXEC dbo.sp_reporte_empleado_capacitacion @p_id_empleado = 1;
   EXEC dbo.sp_reporte_puesto_capacitacion;
   EXEC dbo.sp_reporte_departamento_capacitacion;
   EXEC dbo.sp_reporte_capacitacion @p_id_capacitacion = NULL, @p_fecha_inicio = '2026-01-01', @p_fecha_fin = '2026-12-31';
   EXEC dbo.sp_aviso_correos_pendientes;
   ============================================================ */
