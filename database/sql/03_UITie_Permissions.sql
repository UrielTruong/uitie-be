-- ============================================================
--  FILE  : 03_UITie_Permissions.sql
--  MỤC ĐÍCH:
--    - Phân quyền bảng
--    - Phân quyền Stored Procedure
--    - DENY tường minh cho từng user
--    - Script kiểm tra phân quyền
-- ============================================================

USE uitie_demo;
GO

-- ============================================================
-- [1] PHÂN QUYỀN TRÊN BẢNG
-- ============================================================

---------------------------------------------------------------
-- SUPER ADMIN
---------------------------------------------------------------
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.users         TO role_super_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.posts         TO role_super_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.comments      TO role_super_admin;
GRANT SELECT, INSERT, DELETE         ON dbo.likes         TO role_super_admin;
GRANT SELECT, INSERT, DELETE         ON dbo.follows       TO role_super_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.notifications TO role_super_admin;
GRANT SELECT                         ON dbo.audit_logs    TO role_super_admin;
DENY  INSERT, UPDATE, DELETE         ON dbo.audit_logs    TO role_super_admin;

---------------------------------------------------------------
-- ADMIN
---------------------------------------------------------------
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.users         TO role_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.posts         TO role_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.comments      TO role_admin;
GRANT SELECT, INSERT, DELETE         ON dbo.likes         TO role_admin;
GRANT SELECT                         ON dbo.audit_logs    TO role_admin;
DENY  INSERT, UPDATE, DELETE         ON dbo.audit_logs    TO role_admin;

---------------------------------------------------------------
-- STUDENT
---------------------------------------------------------------
GRANT SELECT, UPDATE                 ON dbo.users         TO role_student;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.posts         TO role_student;
GRANT SELECT, INSERT, UPDATE, DELETE ON dbo.comments      TO role_student;
GRANT SELECT, INSERT, DELETE         ON dbo.likes         TO role_student;
DENY  SELECT, INSERT, UPDATE, DELETE ON dbo.audit_logs    TO role_student;
GO

PRINT N'>> [PERMISSION] Table permissions applied';
GO


-- ============================================================
-- [2] PHÂN QUYỀN STORED PROCEDURES
-- ============================================================

---------------------------------------------------------------
-- Messaging
---------------------------------------------------------------
GRANT EXECUTE ON dbo.sp_send_message      TO role_super_admin;
GRANT EXECUTE ON dbo.sp_send_message      TO role_admin;
GRANT EXECUTE ON dbo.sp_send_message      TO role_student;

GRANT EXECUTE ON dbo.sp_get_notifications TO role_super_admin;
GRANT EXECUTE ON dbo.sp_get_notifications TO role_admin;
GRANT EXECUTE ON dbo.sp_get_notifications TO role_student;

---------------------------------------------------------------
-- Profile
---------------------------------------------------------------
GRANT EXECUTE ON dbo.sp_update_profile    TO role_super_admin;
GRANT EXECUTE ON dbo.sp_update_profile    TO role_admin;
GRANT EXECUTE ON dbo.sp_update_profile    TO role_student;

---------------------------------------------------------------
-- Moderation
---------------------------------------------------------------
GRANT EXECUTE ON dbo.sp_moderate_post     TO role_super_admin;
GRANT EXECUTE ON dbo.sp_moderate_post     TO role_admin;
DENY  EXECUTE ON dbo.sp_moderate_post     TO role_student;

---------------------------------------------------------------
-- Reports
---------------------------------------------------------------
GRANT EXECUTE ON dbo.sp_submit_report     TO role_admin;
GRANT EXECUTE ON dbo.sp_submit_report     TO role_student;
DENY  EXECUTE ON dbo.sp_submit_report     TO role_super_admin;

GRANT EXECUTE ON dbo.sp_resolve_report_safe TO role_super_admin;
GRANT EXECUTE ON dbo.sp_resolve_report_safe TO role_admin;
DENY  EXECUTE ON dbo.sp_resolve_report_safe TO role_student;
GO

PRINT N'>> [PERMISSION] Stored procedure permissions applied';
GO


-- ============================================================
-- [3] DENY TƯỜNG MINH CHO USER
-- ============================================================

---------------------------------------------------------------
-- Student bị chặn các SP quản trị
---------------------------------------------------------------
DENY EXECUTE ON dbo.sp_moderate_post        TO student_uitie_01;
DENY EXECUTE ON dbo.sp_moderate_post        TO student_uitie_02;

DENY EXECUTE ON dbo.sp_resolve_report_safe  TO student_uitie_01;
DENY EXECUTE ON dbo.sp_resolve_report_safe  TO student_uitie_02;

---------------------------------------------------------------
-- Super Admin bị chặn gửi report
---------------------------------------------------------------
DENY EXECUTE ON dbo.sp_submit_report        TO sa_uitie_01;
DENY EXECUTE ON dbo.sp_submit_report        TO sa_uitie_02;

---------------------------------------------------------------
-- Admin không được ghi audit log
---------------------------------------------------------------
DENY INSERT, UPDATE, DELETE ON dbo.audit_logs TO admin_uitie_01;
DENY INSERT, UPDATE, DELETE ON dbo.audit_logs TO admin_uitie_02;
GO
---------------------------------------------------------------
-- Cấm CRUD trực tiếp bảng users
---------------------------------------------------------------
DENY INSERT, UPDATE, DELETE ON dbo.users TO PUBLIC;
GO

PRINT N'>> [PERMISSION] Explicit DENY applied';
GO


-- ============================================================
-- [4] KIỂM TRA PHÂN QUYỀN
-- ============================================================

SELECT
    dp.name            AS Principal,
    o.name             AS ObjectName,
    p.permission_name  AS Permission,
    p.state_desc       AS State
FROM sys.database_permissions p
JOIN sys.database_principals dp ON dp.principal_id = p.grantee_principal_id
LEFT JOIN sys.objects o          ON o.object_id = p.major_id
ORDER BY dp.name, o.name;
GO

PRINT N'>> [PERMISSION] Verification completed';
GO