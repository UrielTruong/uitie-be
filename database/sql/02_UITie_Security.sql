-- ============================================================
--01_UITie_Schema.sql        Tables, Views, SP, Trigger, Seed
--02_UITie_Security.sql      Login, User, Role, Role Member
--03_UITie_Permissions.sql   GRANT / DENY (tables + SP)
-- ============================================================
--  FILE  : 02_UITie_Security.sql
--  MỤC ĐÍCH:
--    - Tạo bảng audit_logs
--    - Tạo LOGIN (Server-level)
--    - Tạo DATABASE USER
--    - Tạo ROLE & gán USER vào ROLE
-- ============================================================

USE uitie_demo;
GO

-- ============================================================
-- [1] TẠO BẢNG AUDIT LOG
-- ============================================================

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'audit_logs')
BEGIN
    CREATE TABLE dbo.audit_logs (
        log_id      INT IDENTITY(1,1) PRIMARY KEY,
        table_name  NVARCHAR(50)  NOT NULL,
        action      NVARCHAR(10)  NOT NULL,
        record_id   BIGINT,
        old_data    NVARCHAR(MAX),
        new_data    NVARCHAR(MAX),
        executed_by NVARCHAR(100),
        executed_at DATETIME DEFAULT GETDATE()
    );
END;
GO

PRINT N'>> [SECURITY] audit_logs ready';
GO


-- ============================================================
-- [2] TẠO LOGIN CẤP SERVER
-- ============================================================

USE master;
GO

IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'sa_uitie_01')
    CREATE LOGIN sa_uitie_01 WITH PASSWORD = 'SuperAdmin@uitie_demo#01', DEFAULT_DATABASE = uitie_demo;
IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'sa_uitie_02')
    CREATE LOGIN sa_uitie_02 WITH PASSWORD = 'SuperAdmin@uitie_demo#02', DEFAULT_DATABASE = uitie_demo;

IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'admin_uitie_01')
    CREATE LOGIN admin_uitie_01 WITH PASSWORD = 'Admin@uitie_demo#01', DEFAULT_DATABASE = uitie_demo;
IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'admin_uitie_02')
    CREATE LOGIN admin_uitie_02 WITH PASSWORD = 'Admin@uitie_demo#02', DEFAULT_DATABASE = uitie_demo;

IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'student_uitie_01')
    CREATE LOGIN student_uitie_01 WITH PASSWORD = 'Student@uitie_demo#01', DEFAULT_DATABASE = uitie_demo;
IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = 'student_uitie_02')
    CREATE LOGIN student_uitie_02 WITH PASSWORD = 'Student@uitie_demo#02', DEFAULT_DATABASE = uitie_demo;
GO

PRINT N'>> [SECURITY] Server logins created';
GO


-- ============================================================
-- [3] TẠO DATABASE USER
-- ============================================================

USE uitie_demo;
GO

IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'sa_uitie_01')
    CREATE USER sa_uitie_01 FOR LOGIN sa_uitie_01;
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'sa_uitie_02')
    CREATE USER sa_uitie_02 FOR LOGIN sa_uitie_02;

IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'admin_uitie_01')
    CREATE USER admin_uitie_01 FOR LOGIN admin_uitie_01;
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'admin_uitie_02')
    CREATE USER admin_uitie_02 FOR LOGIN admin_uitie_02;

IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'student_uitie_01')
    CREATE USER student_uitie_01 FOR LOGIN student_uitie_01;
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'student_uitie_02')
    CREATE USER student_uitie_02 FOR LOGIN student_uitie_02;
GO

PRINT N'>> [SECURITY] Database users created';
GO


-- ============================================================
-- [4] TẠO ROLE & GÁN USER
-- ============================================================

IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'role_super_admin')
    CREATE ROLE role_super_admin;
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'role_admin')
    CREATE ROLE role_admin;
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = 'role_student')
    CREATE ROLE role_student;
GO

ALTER ROLE role_super_admin ADD MEMBER sa_uitie_01;
ALTER ROLE role_super_admin ADD MEMBER sa_uitie_02;
ALTER ROLE role_admin       ADD MEMBER admin_uitie_01;
ALTER ROLE role_admin       ADD MEMBER admin_uitie_02;
ALTER ROLE role_student     ADD MEMBER student_uitie_01;
ALTER ROLE role_student     ADD MEMBER student_uitie_02;
GO

PRINT N'>> [SECURITY] Roles & members configured';
GO
