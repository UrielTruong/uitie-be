-- ============================================================
--01_UITie_Schema.sql        Tables, Views, SP, Trigger, Seed
--02_UITie_Security.sql      Login, User, Role, Role Member
--03_UITie_Permissions.sql   GRANT / DENY (tables + SP)
-- ============================================================

-- 0. CREATE DATABASE + USE
IF DB_ID('uitie_demo') IS NULL
    CREATE DATABASE uitie_demo;
GO

USE uitie_demo;
GO

-- ============================================================
-- 1. CREATE TABLE
-- ============================================================

-- 1.1. users
CREATE TABLE dbo.users (
    id              BIGINT IDENTITY(1,1) NOT NULL,
    email           NVARCHAR(255) NOT NULL,
    password        NVARCHAR(255) NOT NULL,
    full_name       NVARCHAR(255) NOT NULL,
    mssv            NVARCHAR(20)  NULL,
    phone_number    NVARCHAR(20)  NULL,

    role            NVARCHAR(20)  NOT NULL
        CONSTRAINT DF_users_role DEFAULT ('Student'),
        CONSTRAINT CK_users_role
            CHECK (role IN ('Super Admin','Admin','Student')),

    status          NVARCHAR(20)  NOT NULL
        CONSTRAINT DF_users_status DEFAULT ('Inactive'),
        CONSTRAINT CK_users_status
            CHECK (status IN ('Inactive','Active','Locked')),

    status_reason   NVARCHAR(MAX) NULL,
    faculty         NVARCHAR(255) NULL,
    class_name      NVARCHAR(100) NULL,
    academic_year   NVARCHAR(20)  NULL,
    remember_token  NVARCHAR(100) NULL,

    created_at      DATETIME      NULL,
    updated_at      DATETIME      NULL,

    CONSTRAINT PK_users PRIMARY KEY (id),
    CONSTRAINT users_email_unique UNIQUE (email),

    CONSTRAINT CK_users_email
        CHECK (email LIKE '%@ms.uit.edu.vn')
);
GO

-- Filtered unique index: mssv unique nếu NOT NULL
CREATE UNIQUE NONCLUSTERED INDEX UX_users_mssv_not_null
ON dbo.users(mssv)
WHERE mssv IS NOT NULL;
GO

-- 1.2. otp_verification (singular theo migration)
CREATE TABLE dbo.otp_verification (
    otp_id      BIGINT IDENTITY(1,1) NOT NULL,
    user_id     BIGINT        NOT NULL,
    otp_code    NVARCHAR(10)  NOT NULL,

    otp_type    NVARCHAR(30)  NOT NULL,
        CONSTRAINT CK_otp_verification_type
            CHECK (otp_type IN ('LOGIN','FORGOT_PASSWORD','VERIFY_PHONE')),

    expired_at  DATETIME      NULL,
    is_used     BIT           NOT NULL CONSTRAINT DF_otp_verification_is_used DEFAULT (0),
    created_at  DATETIME      NULL,
    updated_at  DATETIME      NULL,

    CONSTRAINT PK_otp_verification PRIMARY KEY (otp_id),
    CONSTRAINT otp_verification_user_id_foreign
        FOREIGN KEY (user_id)
        REFERENCES dbo.users(id)
        ON DELETE CASCADE
);
GO

-- 1.3. categories
CREATE TABLE dbo.categories (
    id            BIGINT IDENTITY(1,1) NOT NULL,
    category_name NVARCHAR(255) NOT NULL,
    description   NVARCHAR(MAX) NULL,
    created_at    DATETIME      NULL,
    updated_at    DATETIME      NULL,
    CONSTRAINT PK_categories PRIMARY KEY (id)
);
GO

-- 1.4. posts
CREATE TABLE dbo.posts (
    id             BIGINT IDENTITY(1,1) NOT NULL,
    user_id        BIGINT        NOT NULL,
    category_id    BIGINT        NULL,
    parent_post_id BIGINT        NULL,
    content        NVARCHAR(MAX) NULL,

    visibility     NVARCHAR(20)  NOT NULL
        CONSTRAINT DF_posts_visibility DEFAULT ('Public'),
        CONSTRAINT CK_posts_visibility
            CHECK (visibility IN ('Public','Private')),

    status         NVARCHAR(20)  NOT NULL
        CONSTRAINT DF_posts_status DEFAULT ('Pending'),
        CONSTRAINT CK_posts_status
            CHECK (status IN ('Pending','Accepted','Rejected')),

    reject_reason  NVARCHAR(MAX) NULL,
    is_edited      BIT           NOT NULL CONSTRAINT DF_posts_is_edited DEFAULT (0),

    created_at     DATETIME      NULL,
    updated_at     DATETIME      NULL,
    deleted_at     DATETIME      NULL,

    CONSTRAINT PK_posts PRIMARY KEY (id),

    CONSTRAINT posts_user_id_foreign
        FOREIGN KEY (user_id)
        REFERENCES dbo.users(id)
        ON DELETE CASCADE,

    CONSTRAINT posts_category_id_foreign
        FOREIGN KEY (category_id)
        REFERENCES dbo.categories(id)
        ON DELETE SET NULL,

    CONSTRAINT posts_parent_post_id_foreign
        FOREIGN KEY (parent_post_id)
        REFERENCES dbo.posts(id)
        ON DELETE NO ACTION
);
GO

-- 1.5. comments
CREATE TABLE dbo.comments (
    id                BIGINT IDENTITY(1,1) NOT NULL,
    post_id           BIGINT        NOT NULL,
    user_id           BIGINT        NOT NULL,
    parent_comment_id BIGINT        NULL,
    content           NVARCHAR(MAX) NULL,
    created_at        DATETIME      NULL,
    updated_at        DATETIME      NULL,
    CONSTRAINT PK_comments PRIMARY KEY (id),
    CONSTRAINT comments_post_id_foreign
        FOREIGN KEY (post_id)           REFERENCES dbo.posts(id)    ON DELETE CASCADE,
    CONSTRAINT comments_user_id_foreign
        FOREIGN KEY (user_id)           REFERENCES dbo.users(id)    ON DELETE NO ACTION,
    CONSTRAINT comments_parent_comment_id_foreign
        FOREIGN KEY (parent_comment_id) REFERENCES dbo.comments(id) ON DELETE NO ACTION
);
GO

-- 1.6. likes
CREATE TABLE dbo.likes (
    id         BIGINT IDENTITY(1,1) NOT NULL,
    user_id    BIGINT   NOT NULL,
    post_id    BIGINT   NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    CONSTRAINT PK_likes PRIMARY KEY (id),
    CONSTRAINT likes_user_id_post_id_unique UNIQUE (user_id, post_id),
    CONSTRAINT likes_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE NO ACTION,
    CONSTRAINT likes_post_id_foreign
        FOREIGN KEY (post_id) REFERENCES dbo.posts(id) ON DELETE CASCADE
);
GO

-- 1.7. follows
CREATE TABLE dbo.follows (
    id           BIGINT IDENTITY(1,1) NOT NULL,
    follower_id  BIGINT   NOT NULL,
    following_id BIGINT   NOT NULL,
    created_at   DATETIME NULL,
    updated_at   DATETIME NULL,
    CONSTRAINT PK_follows PRIMARY KEY (id),
    CONSTRAINT follows_follower_id_following_id_unique UNIQUE (follower_id, following_id),
    CONSTRAINT CK_follows_not_self CHECK (follower_id <> following_id),
    CONSTRAINT follows_follower_id_foreign
        FOREIGN KEY (follower_id)  REFERENCES dbo.users(id) ON DELETE CASCADE,
    CONSTRAINT follows_following_id_foreign
        FOREIGN KEY (following_id) REFERENCES dbo.users(id) ON DELETE NO ACTION
);
GO

-- 1.8. group_chats
CREATE TABLE dbo.group_chats (
    id         BIGINT IDENTITY(1,1) NOT NULL,
    group_name NVARCHAR(255) NULL,
    created_by BIGINT        NULL,
    created_at DATETIME      NULL,
    updated_at DATETIME      NULL,
    CONSTRAINT PK_group_chats PRIMARY KEY (id),
    CONSTRAINT group_chats_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES dbo.users(id) ON DELETE SET NULL
);
GO

-- 1.9. group_members
CREATE TABLE dbo.group_members (
    id         BIGINT IDENTITY(1,1) NOT NULL,
    group_id   BIGINT       NOT NULL,
    user_id    BIGINT       NOT NULL,
    status     NVARCHAR(20) NOT NULL CONSTRAINT DF_group_members_status DEFAULT ('Pending'),
    joined_at  DATETIME     NULL,
    created_at DATETIME     NULL,
    updated_at DATETIME     NULL,
    CONSTRAINT PK_group_members PRIMARY KEY (id),
    CONSTRAINT group_members_group_id_user_id_unique UNIQUE (group_id, user_id),
    CONSTRAINT CK_group_members_status CHECK (status IN ('Pending','Accepted','Rejected')),
    CONSTRAINT group_members_group_id_foreign
        FOREIGN KEY (group_id) REFERENCES dbo.group_chats(id) ON DELETE CASCADE,
    CONSTRAINT group_members_user_id_foreign
        FOREIGN KEY (user_id)  REFERENCES dbo.users(id)       ON DELETE NO ACTION
);
GO

-- 1.10. messages
CREATE TABLE dbo.messages (
    id          BIGINT IDENTITY(1,1) NOT NULL,
    sender_id   BIGINT        NOT NULL,
    receiver_id BIGINT        NULL,
    group_id    BIGINT        NULL,
    content     NVARCHAR(MAX) NULL,
    created_at  DATETIME      NULL,
    updated_at  DATETIME      NULL,
    CONSTRAINT PK_messages PRIMARY KEY (id),
    CONSTRAINT CK_messages_target CHECK (
        (receiver_id IS NOT NULL AND group_id IS NULL)
        OR (receiver_id IS NULL AND group_id IS NOT NULL)
    ),
    CONSTRAINT messages_sender_id_foreign
        FOREIGN KEY (sender_id)   REFERENCES dbo.users(id)       ON DELETE NO ACTION,
    CONSTRAINT messages_receiver_id_foreign
        FOREIGN KEY (receiver_id) REFERENCES dbo.users(id)       ON DELETE NO ACTION,
    CONSTRAINT messages_group_id_foreign
        FOREIGN KEY (group_id)    REFERENCES dbo.group_chats(id) ON DELETE CASCADE
);
GO

-- 1.11. notifications
CREATE TABLE dbo.notifications (
    id           BIGINT IDENTITY(1,1) NOT NULL,
    user_id      BIGINT        NOT NULL,
    content      NVARCHAR(MAX) NULL,
    type         NVARCHAR(50)  NULL,
    is_read      BIT           NOT NULL CONSTRAINT DF_notifications_is_read DEFAULT (0),
    reference_id BIGINT        NULL,
    created_at   DATETIME      NULL,
    updated_at   DATETIME      NULL,
    CONSTRAINT PK_notifications PRIMARY KEY (id),
    CONSTRAINT CK_notifications_type CHECK (type IN (
        'POST_APPROVED','POST_REJECTED','NEW_LIKE','NEW_COMMENT',
        'NEW_FOLLOWER','GROUP_INVITE','SYSTEM_ALERT'
    )),
    CONSTRAINT notifications_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE
);
GO

CREATE INDEX idx_notifications_user ON dbo.notifications(user_id);
CREATE INDEX idx_notifications_read ON dbo.notifications(is_read);
GO

-- 1.12. reports
CREATE TABLE dbo.reports (
    id               BIGINT IDENTITY(1,1) NOT NULL,
    reporter_id      BIGINT        NOT NULL,
    reported_user_id BIGINT        NULL,
    reported_post_id BIGINT        NULL,
    resolved_by      BIGINT        NULL,
    reason           NVARCHAR(MAX) NULL,
    status           NVARCHAR(20)  NOT NULL CONSTRAINT DF_reports_status DEFAULT ('Pending'),
    target_type      NVARCHAR(20)  NULL,
    resolved_at      DATETIME      NULL,
    created_at       DATETIME      NULL,
    updated_at       DATETIME      NULL,
    CONSTRAINT PK_reports PRIMARY KEY (id),
    CONSTRAINT CK_reports_status      CHECK (status IN ('Pending','Resolved','Dismissed')),
    CONSTRAINT CK_reports_target_type CHECK (target_type IN ('User','Post')),
    CONSTRAINT CK_reports_target CHECK (
        (target_type = 'User' AND reported_user_id IS NOT NULL AND reported_post_id IS NULL)
        OR (target_type = 'Post' AND reported_post_id IS NOT NULL AND reported_user_id IS NULL)
    ),
    CONSTRAINT CK_reports_status_logic CHECK (
        (status = 'Pending' AND resolved_by IS NULL AND resolved_at IS NULL)
        OR (status IN ('Resolved','Dismissed') AND resolved_by IS NOT NULL AND resolved_at IS NOT NULL)
    ),
    CONSTRAINT reports_reporter_id_foreign
        FOREIGN KEY (reporter_id)      REFERENCES dbo.users(id) ON DELETE NO ACTION,
    CONSTRAINT reports_reported_user_id_foreign
        FOREIGN KEY (reported_user_id) REFERENCES dbo.users(id) ON DELETE NO ACTION,
    CONSTRAINT reports_reported_post_id_foreign
        FOREIGN KEY (reported_post_id) REFERENCES dbo.posts(id) ON DELETE NO ACTION,
    CONSTRAINT reports_resolved_by_foreign
        FOREIGN KEY (resolved_by)      REFERENCES dbo.users(id) ON DELETE SET NULL
);
GO

PRINT N'>> Schema đã được tạo (12 bảng).';
GO

-- ============================================================
-- 2. ATTACHMENT TABLES
-- ============================================================
CREATE TABLE dbo.attachments (
    id         BIGINT IDENTITY(1,1) NOT NULL,
    file_url   NVARCHAR(MAX) NOT NULL,
    file_type  NVARCHAR(20)  NULL,
    created_at DATETIME      NULL,
    CONSTRAINT PK_attachments PRIMARY KEY (id),
    CONSTRAINT CK_attachments_file_type CHECK (file_type IN ('Image','Video','Document'))
);
GO

CREATE TABLE dbo.post_attachments (
    post_id       BIGINT NOT NULL,
    attachment_id BIGINT NOT NULL,
    CONSTRAINT PK_post_attachments PRIMARY KEY (post_id, attachment_id),
    CONSTRAINT post_attachments_post_id_foreign
        FOREIGN KEY (post_id)       REFERENCES dbo.posts(id)       ON DELETE CASCADE,
    CONSTRAINT post_attachments_attachment_id_foreign
        FOREIGN KEY (attachment_id) REFERENCES dbo.attachments(id) ON DELETE CASCADE
);
GO

CREATE TABLE dbo.message_attachments (
    message_id    BIGINT NOT NULL,
    attachment_id BIGINT NOT NULL,
    CONSTRAINT PK_message_attachments PRIMARY KEY (message_id, attachment_id),
    CONSTRAINT message_attachments_message_id_foreign
        FOREIGN KEY (message_id)    REFERENCES dbo.messages(id)    ON DELETE CASCADE,
    CONSTRAINT message_attachments_attachment_id_foreign
        FOREIGN KEY (attachment_id) REFERENCES dbo.attachments(id) ON DELETE CASCADE
);
GO

CREATE TABLE dbo.comment_attachments (
    comment_id    BIGINT NOT NULL,
    attachment_id BIGINT NOT NULL,
    CONSTRAINT PK_comment_attachments PRIMARY KEY (comment_id, attachment_id),
    CONSTRAINT comment_attachments_comment_id_foreign
        FOREIGN KEY (comment_id)    REFERENCES dbo.comments(id)    ON DELETE CASCADE,
    CONSTRAINT comment_attachments_attachment_id_foreign
        FOREIGN KEY (attachment_id) REFERENCES dbo.attachments(id) ON DELETE CASCADE
);
GO

PRINT N'>> Attachment tables đã được tạo.';
GO

-- ============================================================
-- 3. CREATE VIEW (SYNC WITH NEW SCHEMA)
-- ============================================================

-- 3.1. USERS — View người dùng đang hoạt động
CREATE OR ALTER VIEW dbo.vw_active_users AS
SELECT
    id              AS user_id,
    email,
    password        AS password,
    full_name,
    mssv,
    phone_number,
    role,
    status,
    status_reason,
    faculty,
    class_name,
    academic_year,
    created_at
FROM dbo.users
WHERE status = 'Active';
GO


-- 3.2. USERS — View user bị khóa / inactive (quản trị)
CREATE OR ALTER VIEW dbo.vw_inactive_users AS
SELECT
    id          AS user_id,
    email,
    full_name,
    role,
    status,
    status_reason,
    created_at
FROM dbo.users
WHERE status IN ('Inactive', 'Locked');
GO


-- 3.3. POSTS — View bài viết công khai (feed chính)
CREATE OR ALTER VIEW dbo.vw_public_posts AS
SELECT
    id             AS post_id,
    user_id,
    category_id,
    parent_post_id,
    content,
    visibility,
    status,
    reject_reason,
    is_edited,
    created_at,
    updated_at,
    deleted_at
FROM dbo.posts
WHERE deleted_at IS NULL
  AND visibility = 'Public'
  AND status = 'Accepted';
GO


-- 3.4. POSTS — View bài viết chờ duyệt (admin)
CREATE OR ALTER VIEW dbo.vw_pending_posts AS
SELECT
    id          AS post_id,
    user_id,
    category_id,
    content,
    visibility,
    status,
    created_at
FROM dbo.posts
WHERE status = 'Pending'
  AND deleted_at IS NULL;
GO


-- 3.5. COMMENTS — View comment cấp 1 của post
CREATE OR ALTER VIEW dbo.vw_post_comments AS
SELECT
    id              AS comment_id,
    post_id,
    user_id,
    parent_comment_id,
    content,
    created_at
FROM dbo.comments
WHERE parent_comment_id IS NULL;
GO


-- 3.6. COMMENTS — View reply comment
CREATE OR ALTER VIEW dbo.vw_comment_replies AS
SELECT
    id              AS comment_id,
    post_id,
    user_id,
    parent_comment_id,
    content,
    created_at
FROM dbo.comments
WHERE parent_comment_id IS NOT NULL;
GO


-- 3.7. LIKES — View đếm số lượt thích mỗi post
CREATE OR ALTER VIEW dbo.vw_post_like_count AS
SELECT
    post_id,
    COUNT(*) AS like_count
FROM dbo.likes
GROUP BY post_id;
GO


-- 3.8. FOLLOWS — View số follower mỗi user
CREATE OR ALTER VIEW dbo.vw_user_follower_count AS
SELECT
    following_id AS user_id,
    COUNT(*)     AS follower_count
FROM dbo.follows
GROUP BY following_id;
GO


-- 3.9. NOTIFICATIONS — View thông báo chưa đọc
CREATE OR ALTER VIEW dbo.vw_unread_notifications AS
SELECT
    id          AS notification_id,
    user_id,
    content,
    type,
    is_read,
    reference_id,
    created_at
FROM dbo.notifications
WHERE is_read = 0;
GO


-- 3.10. REPORTS — View báo cáo chưa xử lý
CREATE OR ALTER VIEW dbo.vw_pending_reports AS
SELECT
    id               AS report_id,
    reporter_id,
    reported_user_id,
    reported_post_id,
    resolved_by,
    reason,
    status,
    target_type,
    created_at,
    resolved_at
FROM dbo.reports
WHERE status = 'Pending';
GO


-- 3.11. MESSAGES — View tin nhắn cá nhân (1-1)
CREATE OR ALTER VIEW dbo.vw_private_messages AS
SELECT
    id          AS message_id,
    sender_id,
    receiver_id,
    content,
    created_at
FROM dbo.messages
WHERE receiver_id IS NOT NULL;
GO


-- 3.12. MESSAGES — View tin nhắn nhóm
CREATE OR ALTER VIEW dbo.vw_group_messages AS
SELECT
    id          AS message_id,
    sender_id,
    group_id,
    content,
    created_at
FROM dbo.messages
WHERE group_id IS NOT NULL;
GO


-- 3.13. GROUP_MEMBERS — View thành viên nhóm đã tham gia
CREATE OR ALTER VIEW dbo.vw_group_members AS
SELECT
    id        AS member_id,
    group_id,
    user_id,
    status,
    joined_at
FROM dbo.group_members
WHERE status = 'Accepted';
GO


PRINT N'>> Views đã được tạo';
GO
-- ============================================================
-- 4. STORED PROCEDURES
-- ============================================================

-- 4.1. Gửi tin nhắn
IF OBJECT_ID('dbo.sp_send_message', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_send_message AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_send_message
    @sender_id   BIGINT,
    @receiver_id BIGINT        = NULL,
    @group_id    BIGINT        = NULL,
    @content     NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRAN;

    BEGIN TRY
        IF NOT (
            (@receiver_id IS NOT NULL AND @group_id IS NULL)
         OR (@receiver_id IS NULL     AND @group_id IS NOT NULL)
        )
        BEGIN
            RAISERROR (N'Phải chọn chat 1-1 hoặc chat nhóm.', 16, 1);
        END;

        INSERT INTO dbo.messages
            (sender_id, receiver_id, group_id, content, created_at, updated_at)
        VALUES
            (@sender_id, @receiver_id, @group_id, @content, GETDATE(), GETDATE());

        DECLARE @message_id BIGINT = SCOPE_IDENTITY();

        IF @receiver_id IS NOT NULL
        BEGIN
            INSERT INTO dbo.notifications
                (user_id, content, type, created_at, updated_at)
            VALUES
                (@receiver_id, N'Bạn có tin nhắn mới.', 'SYSTEM_ALERT', GETDATE(), GETDATE());
        END;

        COMMIT;
        SELECT @message_id AS message_id;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        THROW;
    END CATCH;
END;
GO

-- 4.2. Thêm attachment vào tin nhắn
IF OBJECT_ID('dbo.sp_add_message_attachment', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_add_message_attachment AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_add_message_attachment
    @message_id    BIGINT,
    @attachment_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.message_attachments (message_id, attachment_id)
    VALUES (@message_id, @attachment_id);
END;
GO

-- 4.3. Đánh dấu thông báo đã đọc
IF OBJECT_ID('dbo.sp_mark_notification_read', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_mark_notification_read AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_mark_notification_read
    @notification_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.notifications
    SET is_read = 1,
        updated_at = GETDATE()
    WHERE id = @notification_id;
END;
GO

-- 4.4. Lấy danh sách thông báo
IF OBJECT_ID('dbo.sp_get_notifications', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_get_notifications AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_get_notifications
    @user_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.notifications
    WHERE user_id = @user_id
    ORDER BY created_at DESC;
END;
GO

-- 4.5. Xử lý báo cáo
IF OBJECT_ID('dbo.sp_resolve_report', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_resolve_report AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_resolve_report
    @report_id BIGINT,
    @admin_id  BIGINT,
    @status    NVARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRAN;

    BEGIN TRY
        IF NOT EXISTS (
            SELECT 1
            FROM dbo.users
            WHERE id = @admin_id
              AND role IN ('Admin', 'Super Admin')
        )
        BEGIN
            RAISERROR (N'Chỉ Admin mới được xử lý report.', 16, 1);
        END;

        IF @status NOT IN ('Resolved', 'Dismissed')
        BEGIN
            RAISERROR (N'Trạng thái không hợp lệ.', 16, 1);
        END;

        UPDATE dbo.reports
        SET status      = @status,
            resolved_by = @admin_id,
            resolved_at = GETDATE(),
            updated_at  = GETDATE()
        WHERE id = @report_id;

        DECLARE @reporter_id BIGINT;
        SELECT @reporter_id = reporter_id
        FROM dbo.reports
        WHERE id = @report_id;

        INSERT INTO dbo.notifications
            (user_id, content, type, created_at, updated_at)
        VALUES
            (@reporter_id, N'Báo cáo của bạn đã được xử lý.', 'SYSTEM_ALERT', GETDATE(), GETDATE());

        COMMIT;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        THROW;
    END CATCH;
END;
GO

-- 4.6. Soft delete bài viết
IF OBJECT_ID('dbo.sp_soft_delete_post', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_soft_delete_post AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_soft_delete_post
    @post_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.posts
    SET deleted_at = GETDATE(),
        updated_at = GETDATE()
    WHERE id = @post_id;
END;
GO

-- 4.7. Like / Unlike
IF OBJECT_ID('dbo.sp_toggle_like', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_toggle_like AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_toggle_like
    @user_id BIGINT,
    @post_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM dbo.likes
        WHERE user_id = @user_id
          AND post_id = @post_id
    )
        DELETE FROM dbo.likes
        WHERE user_id = @user_id
          AND post_id = @post_id;
    ELSE
        INSERT INTO dbo.likes
            (user_id, post_id, created_at, updated_at)
        VALUES
            (@user_id, @post_id, GETDATE(), GETDATE());
END;
GO
-- ============================================================
-- 4.8. Cập nhật hồ sơ cá nhân
--  - Student chỉ được sửa hồ sơ của chính mình
--  - Admin / Super Admin được sửa mọi user
IF OBJECT_ID('dbo.sp_update_profile', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_update_profile AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_update_profile
    @requesting_user_id BIGINT,
    @target_user_id     BIGINT,
    @full_name          NVARCHAR(255) = NULL,
    @phone_number       NVARCHAR(20)  = NULL,
    @faculty            NVARCHAR(255) = NULL,
    @class_name         NVARCHAR(100) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @requester_role NVARCHAR(20);
    SELECT @requester_role = role
    FROM dbo.users
    WHERE id = @requesting_user_id;

    IF @requester_role = 'Student'
       AND @requesting_user_id <> @target_user_id
    BEGIN
        RAISERROR(N'Bạn chỉ có quyền chỉnh sửa hồ sơ của chính mình.', 16, 1);
        RETURN;
    END;

    UPDATE dbo.users
    SET full_name    = ISNULL(@full_name,    full_name),
        phone_number = ISNULL(@phone_number, phone_number),
        faculty      = ISNULL(@faculty,      faculty),
        class_name   = ISNULL(@class_name,   class_name),
        updated_at   = GETDATE()
    WHERE id = @target_user_id;
END;
GO

-- ============================================================
-- 4.9. Kiểm duyệt bài đăng
--  - Admin / Super Admin duyệt hoặc từ chối bài viết
--  - Tự động tạo notification cho chủ bài viết
IF OBJECT_ID('dbo.sp_moderate_post', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_moderate_post AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_moderate_post
    @post_id       BIGINT,
    @action        NVARCHAR(20),
    @reject_reason NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @action NOT IN ('Accepted', 'Rejected')
    BEGIN
        RAISERROR(N'Hành động không hợp lệ. Chọn: Accepted | Rejected', 16, 1);
        RETURN;
    END;

    IF @action = 'Rejected' AND @reject_reason IS NULL
    BEGIN
        RAISERROR(N'Phải cung cấp lý do khi từ chối bài đăng.', 16, 1);
        RETURN;
    END;

    IF NOT EXISTS (
        SELECT 1
        FROM dbo.posts
        WHERE id = @post_id
          AND status = 'Pending'
          AND deleted_at IS NULL
    )
    BEGIN
        RAISERROR(N'Bài viết không tồn tại hoặc không ở trạng thái Pending.', 16, 1);
        RETURN;
    END;

    UPDATE dbo.posts
    SET status        = @action,
        reject_reason = @reject_reason,
        updated_at    = GETDATE()
    WHERE id = @post_id;

    DECLARE @owner_id BIGINT;
    SELECT @owner_id = user_id
    FROM dbo.posts
    WHERE id = @post_id;

    INSERT INTO dbo.notifications
        (user_id, content, type, reference_id, created_at, updated_at)
    VALUES (
        @owner_id,
        CASE
            WHEN @action = 'Accepted'
                THEN N'Bài viết của bạn đã được duyệt.'
            ELSE
                N'Bài viết của bạn đã bị từ chối: ' + ISNULL(@reject_reason, N'')
        END,
        CASE
            WHEN @action = 'Accepted' THEN 'POST_APPROVED'
            ELSE 'POST_REJECTED'
        END,
        @post_id,
        GETDATE(),
        GETDATE()
    );
END;
GO

-- ============================================================
-- 4.10. Gửi báo cáo vi phạm
--  - Cho phép report User hoặc Post
--  - Không cho tự report chính mình
IF OBJECT_ID('dbo.sp_submit_report', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_submit_report AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_submit_report
    @reporter_id      BIGINT,
    @target_type      NVARCHAR(20),
    @reason           NVARCHAR(MAX),
    @reported_user_id BIGINT = NULL,
    @reported_post_id BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @target_type NOT IN ('User', 'Post')
    BEGIN
        RAISERROR(N'target_type không hợp lệ. Chọn: User | Post', 16, 1);
        RETURN;
    END;

    IF @target_type = 'User' AND @reported_user_id IS NULL
    BEGIN
        RAISERROR(N'Phải cung cấp reported_user_id khi target_type = User.', 16, 1);
        RETURN;
    END;

    IF @target_type = 'Post' AND @reported_post_id IS NULL
    BEGIN
        RAISERROR(N'Phải cung cấp reported_post_id khi target_type = Post.', 16, 1);
        RETURN;
    END;

    IF @target_type = 'User' AND @reporter_id = @reported_user_id
    BEGIN
        RAISERROR(N'Bạn không thể báo cáo chính mình.', 16, 1);
        RETURN;
    END;

    INSERT INTO dbo.reports
        (reporter_id, reported_user_id, reported_post_id,
         target_type, reason, status, created_at, updated_at)
    VALUES
        (@reporter_id, @reported_user_id, @reported_post_id,
         @target_type, @reason, 'Pending', GETDATE(), GETDATE());

    SELECT SCOPE_IDENTITY() AS new_report_id;
END;
GO

-- ============================================================
-- 4.11. Xử lý báo cáo – bản safe
--  - Chỉ Admin / Super Admin
--  - Không được xử lý report của chính mình
--  - Không được xử lý report nhắm vào chính mình
IF OBJECT_ID('dbo.sp_resolve_report_safe', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_resolve_report_safe AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_resolve_report_safe
    @report_id BIGINT,
    @admin_id  BIGINT,
    @status    NVARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRAN;

    BEGIN TRY
        IF NOT EXISTS (
            SELECT 1
            FROM dbo.users
            WHERE id = @admin_id
              AND role IN ('Admin', 'Super Admin')
        )
        BEGIN
            RAISERROR(N'Chỉ Admin mới được xử lý report.', 16, 1);
            ROLLBACK; RETURN;
        END;

        IF @status NOT IN ('Resolved', 'Dismissed')
        BEGIN
            RAISERROR(N'Trạng thái không hợp lệ.', 16, 1);
            ROLLBACK; RETURN;
        END;

        IF EXISTS (
            SELECT 1
            FROM dbo.reports
            WHERE id = @report_id
              AND reporter_id = @admin_id
        )
        BEGIN
            RAISERROR(N'Không thể xử lý báo cáo do chính mình gửi.', 16, 1);
            ROLLBACK; RETURN;
        END;

        IF EXISTS (
            SELECT 1
            FROM dbo.reports
            WHERE id = @report_id
              AND reported_user_id = @admin_id
        )
        BEGIN
            RAISERROR(N'Không thể xử lý báo cáo nhắm vào chính mình.', 16, 1);
            ROLLBACK; RETURN;
        END;

        UPDATE dbo.reports
        SET status      = @status,
            resolved_by = @admin_id,
            resolved_at = GETDATE(),
            updated_at  = GETDATE()
        WHERE id = @report_id;

        DECLARE @reporter_id BIGINT;
        SELECT @reporter_id = reporter_id
        FROM dbo.reports
        WHERE id = @report_id;

        INSERT INTO dbo.notifications
            (user_id, content, type, created_at, updated_at)
        VALUES
            (@reporter_id,
             N'Báo cáo của bạn đã được xử lý.',
             'SYSTEM_ALERT',
             GETDATE(),
             GETDATE());

        COMMIT;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        THROW;
    END CATCH;
END;
GO

PRINT N'>> Stored Procedures đã được tạo.';
GO
-- ============================================================
-- 4.12. Tạo user - chỉ super admin
IF OBJECT_ID('dbo.sp_create_user', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_create_user AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_create_user
    @actor_id      BIGINT,          -- người thực hiện
    @email         NVARCHAR(255),
    @password      NVARCHAR(255),
    @full_name     NVARCHAR(255),
    @role          NVARCHAR(20) = 'Student',
    @status        NVARCHAR(20) = 'Inactive'
AS
BEGIN
    SET NOCOUNT ON;

    -- Kiểm tra quyền Super Admin
    IF NOT EXISTS (
        SELECT 1
        FROM dbo.users
        WHERE id = @actor_id
          AND role = 'Super Admin'
    )
    BEGIN
        RAISERROR (N'Chỉ Super Admin mới được tạo user.', 16, 1);
        RETURN;
    END;

    INSERT INTO dbo.users (
        email, password, full_name, role, status,
        created_at, updated_at
    )
    VALUES (
        @email, @password, @full_name, @role, @status,
        GETDATE(), GETDATE()
    );
END;
GO
-- ============================================================
-- 4.13. Xem danh sách user - chỉ super admin
IF OBJECT_ID('dbo.sp_get_all_users', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_get_all_users AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_get_all_users
    @actor_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    IF NOT EXISTS (
        SELECT 1
        FROM dbo.users
        WHERE id = @actor_id
          AND role = 'Super Admin'
    )
    BEGIN
        RAISERROR (N'Chỉ Super Admin mới được xem danh sách user.', 16, 1);
        RETURN;
    END;

    SELECT *
    FROM dbo.users
    ORDER BY created_at;
END;
GO
-- ============================================================
-- 4.14. Cập nhật user - chỉ super admin
IF OBJECT_ID('dbo.sp_update_user', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_update_user AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_update_user
    @actor_id   BIGINT,
    @target_id  BIGINT,
    @role       NVARCHAR(20) = NULL,
    @status     NVARCHAR(20) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF NOT EXISTS (
        SELECT 1
        FROM dbo.users
        WHERE id = @actor_id
          AND role = 'Super Admin'
    )
    BEGIN
        RAISERROR (N'Chỉ Super Admin mới được cập nhật user.', 16, 1);
        RETURN;
    END;

    UPDATE dbo.users
    SET role       = ISNULL(@role, role),
        status     = ISNULL(@status, status),
        updated_at = GETDATE()
    WHERE id = @target_id;
END;
GO
-- ============================================================
-- 4.15. Xóa user - chỉ super admin
IF OBJECT_ID('dbo.sp_delete_user', 'P') IS NULL
BEGIN
    EXEC ('CREATE PROCEDURE dbo.sp_delete_user AS BEGIN SET NOCOUNT ON; END');
END
GO

ALTER PROCEDURE dbo.sp_delete_user
    @actor_id  BIGINT,
    @target_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;

    IF NOT EXISTS (
        SELECT 1
        FROM dbo.users
        WHERE id = @actor_id
          AND role = 'Super Admin'
    )
    BEGIN
        RAISERROR (N'Chỉ Super Admin mới được xóa user.', 16, 1);
        RETURN;
    END;

    DELETE FROM dbo.users
    WHERE id = @target_id;
END;
GO
-- ============================================================
-- 5. TRIGGERS
-- ============================================================

-- 5.1. Auto cập nhật updated_at khi post thay đổi
CREATE OR ALTER TRIGGER dbo.trg_post_update_time
ON dbo.posts
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE dbo.posts
    SET updated_at = GETDATE()
    FROM INSERTED i
    WHERE dbo.posts.id = i.id;
END;
GO

-- 5.2. Tạo notification khi có like mới
CREATE OR ALTER TRIGGER dbo.trg_notify_like
ON dbo.likes
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO dbo.notifications (user_id, content, type, reference_id, created_at, updated_at)
    SELECT
        p.user_id,
        N'Bài viết của bạn có lượt thích mới.',
        'NEW_LIKE',
        i.post_id,
        GETDATE(),
        GETDATE()
    FROM INSERTED i
    JOIN dbo.posts p ON i.post_id = p.id
    WHERE i.user_id <> p.user_id;
END;
GO

-- 5.3. Tạo notification khi có comment mới
CREATE OR ALTER TRIGGER dbo.trg_notify_comment
ON dbo.comments
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    -- Notify chủ post
    INSERT INTO dbo.notifications (user_id, content, type, reference_id, created_at, updated_at)
    SELECT DISTINCT
        p.user_id,
        N'Bài viết của bạn có bình luận mới.',
        'NEW_COMMENT',
        i.post_id,
        GETDATE(),
        GETDATE()
    FROM INSERTED i
    JOIN dbo.posts p ON i.post_id = p.id
    WHERE p.deleted_at IS NULL
      AND i.user_id <> p.user_id;

    -- Notify người bị reply
    INSERT INTO dbo.notifications (user_id, content, type, reference_id, created_at, updated_at)
    SELECT DISTINCT
        c.user_id,
        N'Bình luận của bạn có phản hồi mới.',
        'NEW_COMMENT',
        i.post_id,
        GETDATE(),
        GETDATE()
    FROM INSERTED i
    JOIN dbo.comments c ON i.parent_comment_id = c.id
    WHERE i.parent_comment_id IS NOT NULL
      AND i.user_id <> c.user_id;
END;
GO

-- 5.4. Tạo notification khi có follow mới
CREATE OR ALTER TRIGGER dbo.trg_notify_follow
ON dbo.follows
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO dbo.notifications (user_id, content, type, reference_id, created_at, updated_at)
    SELECT
        i.following_id,
        N'Bạn có người theo dõi mới.',
        'NEW_FOLLOWER',
        i.follower_id,
        GETDATE(),
        GETDATE()
    FROM INSERTED i;
END;
GO

PRINT N'>> Triggers đã được tạo.';
GO

-- ============================================================
-- 6. SEED DATA (demo)
-- ============================================================

-- 6.1. users
INSERT INTO dbo.users (
    email, password, full_name, mssv, phone_number,
    role, status, faculty, class_name, academic_year, created_at, updated_at
)
VALUES
(N'superadmin@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Super Admin UIT', NULL, NULL,
 'Super Admin', 'Active', NULL, NULL, NULL, GETDATE(), GETDATE()),
(N'admin1@ms.uit.edu.vn',     N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a',     N'Admin One',       NULL, NULL,
 'Admin', 'Active', NULL, NULL, NULL, GETDATE(), GETDATE()),
(N'admin2@ms.uit.edu.vn',     N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a',     N'Admin Two',       NULL, NULL,
 'Admin', 'Active', NULL, NULL, NULL, GETDATE(), GETDATE()),

(N'student01@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Nguyễn Văn A', '22520001', '0900000001',
 'Student', 'Active', N'CNPM', N'SE1', '2022-2026', GETDATE(), GETDATE()),
(N'student02@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Trần Thị B',   '22520002', '0900000002',
 'Student', 'Active', N'CNPM', N'SE1', '2022-2026', GETDATE(), GETDATE()),
(N'student03@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Lê Văn C',     '22520003', '0900000003',
 'Student', 'Active', N'KHMT', N'CS1', '2022-2026', GETDATE(), GETDATE()),
(N'student04@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Phạm Thị D',   '22520004', '0900000004',
 'Student', 'Active', N'HTTT', N'IS1', '2021-2025', GETDATE(), GETDATE()),
(N'student05@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Hoàng Văn E',  '22520005', '0900000005',
 'Student', 'Active', N'CNPM', N'SE2', '2021-2025', GETDATE(), GETDATE()),
(N'student06@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Võ Thị F',     '22520006', '0900000006',
 'Student', 'Active', N'KHMT', N'CS2', '2022-2026', GETDATE(), GETDATE()),
(N'student07@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Đặng Văn G',   '22520007', '0900000007',
 'Student', 'Active', N'HTTT', N'IS2', '2020-2024', GETDATE(), GETDATE()),
(N'student08@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Bùi Thị H',    '22520008', '0900000008',
 'Student', 'Active', N'CNPM', N'SE3', '2023-2027', GETDATE(), GETDATE()),
(N'student09@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Nguyễn Văn I', '22520009', '0900000009',
 'Student', 'Active', N'KHMT', N'CS3', '2023-2027', GETDATE(), GETDATE()),
(N'student10@ms.uit.edu.vn', N'$2y$12$AHTbvqrX5DkKc8ZZEJX6wuNDiooytBQ9fPRspAZWsZCKAhi82ni0a', N'Trần Thị K',   '22520010', '0900000010',
 'Student', 'Active', N'HTTT', N'IS3', '2023-2027', GETDATE(), GETDATE());
GO

UPDATE dbo.users
SET status = 'Inactive',
    status_reason = N'Tài khoản chưa kích hoạt',
    updated_at = GETDATE()
WHERE email = 'student09@ms.uit.edu.vn';

UPDATE dbo.users
SET status = 'Locked',
    status_reason = N'Vi phạm quy định cộng đồng',
    updated_at = GETDATE()
WHERE email = 'student10@ms.uit.edu.vn';
GO

-- 2.2. categories
INSERT INTO dbo.categories (category_name, description, created_at, updated_at)
VALUES
(N'Học tập',     N'Review môn học, đánh giá giảng viên, chia sẻ tài liệu học tập, đề thi và kinh nghiệm học tập tại UIT', GETDATE(), GETDATE()),
(N'Hành chính', N'Thông tin về đăng ký môn học, thủ tục học phí, học bổng, giấy tờ hành chính và các thông báo học vụ',  GETDATE(), GETDATE()),
(N'Hướng nghiệp', N'Cơ hội việc làm, thực tập, workshop, định hướng nghề nghiệp và chia sẻ kinh nghiệm phỏng vấn',       GETDATE(), GETDATE()),
(N'Đời sống',    N'Đời sống sinh viên: canteen, ký túc xá, câu lạc bộ, hoạt động ngoại khóa và các cảnh báo học vụ',     GETDATE(), GETDATE());
GO

-- 6.3. posts
INSERT INTO dbo.posts (user_id, category_id, content, visibility, status, created_at, updated_at)
VALUES
-- HỌC TẬP
(4, 1, N'Review môn Cơ sở dữ liệu: môn khá nặng nhưng rất hữu ích cho backend.', 'Public', 'Accepted', GETDATE(), GETDATE()),
(5, 1, N'Chia sẻ tài liệu ôn tập cuối kỳ môn Lập trình Web.',                     'Public', 'Accepted', GETDATE(), GETDATE()),
(6, 1, N'Kinh nghiệm học tốt môn Cấu trúc dữ liệu và giải thuật.',                'Public', 'Pending',  GETDATE(), GETDATE()),
-- HÀNH CHÍNH
(4, 2, N'Hỏi về thủ tục đăng ký môn học học kỳ tiếp theo.',                       'Public', 'Accepted', GETDATE(), GETDATE()),
(5, 2, N'Thông tin mới về học phí và thời hạn đóng học kỳ này.',                  'Public', 'Accepted', GETDATE(), GETDATE()),
-- HƯỚNG NGHIỆP
(6, 3, N'Chia sẻ cơ hội thực tập Backend cho sinh viên năm 3.',                   'Public', 'Accepted', GETDATE(), GETDATE()),
(4, 3, N'Mọi người review giúp workshop về AI tuần sau có đáng đi không?',        'Public', 'Pending',  GETDATE(), GETDATE()),
-- ĐỜI SỐNG
(5, 4, N'Canteen trường dạo này có món nào ngon không mọi người?',                'Public', 'Accepted', GETDATE(), GETDATE()),
(6, 4, N'Ký túc xá khu A hiện tại còn chỗ trống không?',                          'Public', 'Accepted', GETDATE(), GETDATE()),
-- POST RIÊNG TƯ
(4, 1, N'Ghi chú cá nhân về kế hoạch học tập trong kỳ.',                          'Private', 'Accepted', GETDATE(), GETDATE());
GO

-- 6.4. comments
INSERT INTO dbo.comments (post_id, user_id, content, created_at, updated_at)
VALUES
(1, 5, N'Bài viết rất hữu ích, cảm ơn bạn đã chia sẻ.', GETDATE(), GETDATE()),
(1, 6, N'Mình cũng đang học môn này, thấy khá khó.',     GETDATE(), GETDATE()),
(2, 4, N'Thông tin này rất cần thiết cho sinh viên.',    GETDATE(), GETDATE()),
(3, 5, N'Mình đang chờ admin duyệt bài này.',            GETDATE(), GETDATE()),
(4, 6, N'Câu hỏi hay, mình cũng đang thắc mắc giống bạn.', GETDATE(), GETDATE()),
(5, 4, N'Cảm ơn đã cập nhật thông tin học phí.',         GETDATE(), GETDATE());
GO

INSERT INTO dbo.comments (post_id, user_id, parent_comment_id, content, created_at, updated_at)
VALUES
(1, 4, 1, N'Cảm ơn bạn đã phản hồi nhé!',                  GETDATE(), GETDATE()),
(1, 5, 2, N'Bạn cần tài liệu bổ sung không?',              GETDATE(), GETDATE()),
(4, 4, 5, N'Mình đã hỏi phòng đào tạo rồi, chờ phản hồi.', GETDATE(), GETDATE()),
(5, 5, 6, N'Chuẩn rồi, thông báo này rất quan trọng.',     GETDATE(), GETDATE());
GO

-- 6.5. follows
INSERT INTO dbo.follows (follower_id, following_id, created_at, updated_at)
VALUES
(5, 4, GETDATE(), GETDATE()),
(6, 4, GETDATE(), GETDATE()),
(4, 5, GETDATE(), GETDATE()),
(6, 5, GETDATE(), GETDATE()),
(4, 2, GETDATE(), GETDATE()),
(5, 2, GETDATE(), GETDATE()),
(6, 3, GETDATE(), GETDATE()),
(2, 4, GETDATE(), GETDATE()),
(3, 5, GETDATE(), GETDATE()),
(5, 1, GETDATE(), GETDATE());
GO

-- 6.6. likes
INSERT INTO dbo.likes (user_id, post_id, created_at, updated_at)
VALUES
(5, 1, GETDATE(), GETDATE()),
(6, 1, GETDATE(), GETDATE()),
(4, 2, GETDATE(), GETDATE()),
(4, 4, GETDATE(), GETDATE()),
(5, 5, GETDATE(), GETDATE()),
(6, 6, GETDATE(), GETDATE()),
(5, 8, GETDATE(), GETDATE()),
(6, 9, GETDATE(), GETDATE()),
(2, 1, GETDATE(), GETDATE()),
(3, 4, GETDATE(), GETDATE());
GO

-- 6.7. group_chats
INSERT INTO dbo.group_chats (group_name, created_by, created_at, updated_at)
VALUES
(N'Nhóm học Cơ sở dữ liệu',     4, GETDATE(), GETDATE()),
(N'Nhóm ôn tập Lập trình Web',  5, GETDATE(), GETDATE()),
(N'Nhóm sinh viên năm 3 CNTT',  6, GETDATE(), GETDATE()),
(N'Nhóm thông báo học vụ',      2, GETDATE(), GETDATE()),
(N'Nhóm hỗ trợ đăng ký môn',    2, GETDATE(), GETDATE()),
(N'Nhóm hướng nghiệp Backend',  3, GETDATE(), GETDATE()),
(N'Nhóm workshop AI',           3, GETDATE(), GETDATE()),
(N'Nhóm đời sống KTX',          4, GETDATE(), GETDATE()),
(N'Nhóm CLB Công nghệ',         5, GETDATE(), GETDATE()),
(N'Nhóm thảo luận chung UIT',   1, GETDATE(), GETDATE());
GO

-- 6.8. group_members
INSERT INTO dbo.group_members (group_id, user_id, status, joined_at, created_at, updated_at)
VALUES
(1, 4, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(1, 5, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(1, 6, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(2, 5, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(2, 4, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(2, 6, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(3, 6, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(3, 4, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(4, 2, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(4, 4, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(4, 5, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(5, 2, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(5, 6, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(6, 3, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(6, 4, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(7, 3, 'Accepted', GETDATE(), GETDATE(), GETDATE()),
(10, 1, 'Accepted', GETDATE(), GETDATE(), GETDATE());
GO

-- 6.9. messages
INSERT INTO dbo.messages (sender_id, receiver_id, group_id, content, created_at, updated_at)
VALUES
-- private 1-1
(4, 5, NULL, N'Chào bạn, bạn có tài liệu môn CSDL không?',                  GETDATE(), GETDATE()),
(5, 4, NULL, N'Mình có, để mình gửi cho bạn sau nhé.',                       GETDATE(), GETDATE()),
(6, 4, NULL, N'Bạn đã đăng ký môn học kỳ tới chưa?',                         GETDATE(), GETDATE()),
(4, 6, NULL, N'Mình đăng ký rồi, còn bạn thì sao?',                          GETDATE(), GETDATE()),
-- student ↔ admin
(5, 2, NULL, N'Admin cho em hỏi về thủ tục đăng ký môn học.',                GETDATE(), GETDATE()),
(2, 5, NULL, N'Em xem hướng dẫn trên website hoặc inbox phòng đào tạo nhé.', GETDATE(), GETDATE()),
-- group chat
(4, NULL, 1, N'Chào mọi người, mình tạo nhóm này để ôn tập CSDL.',            GETDATE(), GETDATE()),
(5, NULL, 1, N'Ok bạn, mình tham gia cùng nhé.',                              GETDATE(), GETDATE()),
(6, NULL, 2, N'Nhóm ôn tập Web có tài liệu gì mới chưa?',                     GETDATE(), GETDATE()),
(2, NULL, 4, N'Thông báo: hạn đăng ký môn học là cuối tuần này.',             GETDATE(), GETDATE());
GO

-- 6.10. reports

INSERT INTO dbo.reports (
    reporter_id,
    reported_user_id,
    reported_post_id,
    target_type,
    reason,
    status,
    created_at,
    updated_at
)
VALUES
-- 1. Student report Student
(4, 5, NULL, 'User', N'Người dùng có hành vi spam comment.', 'Pending', GETDATE(), GETDATE()),

-- 2. Student report Post
(5, NULL, 1, 'Post', N'Nội dung bài viết không phù hợp.', 'Pending', GETDATE(), GETDATE()),

-- 3. Student report Post
(6, NULL, 2, 'Post', N'Bài viết có thông tin sai sự thật.', 'Pending', GETDATE(), GETDATE()),

-- 4. Student report Student
(7, 6, NULL, 'User', N'Tài khoản này nhắn tin làm phiền.', 'Pending', GETDATE(), GETDATE()),

-- 5. Student report Post
(8, NULL, 4, 'Post', N'Bài viết mang tính công kích cá nhân.', 'Pending', GETDATE(), GETDATE()),

-- 6. Student report Student
(9, 4, NULL, 'User', N'Người dùng này vi phạm quy định nhóm.', 'Pending', GETDATE(), GETDATE()),

-- 7. Student report Post
(4, NULL, 5, 'Post', N'Bài viết quảng cáo trái phép.', 'Pending', GETDATE(), GETDATE()),

-- 8. Student report Student
(5, 7, NULL, 'User', N'Cư xử thiếu văn minh trong bình luận.', 'Pending', GETDATE(), GETDATE()),

-- 9. Student report Post
(6, NULL, 8, 'Post', N'Nội dung không liên quan đến UIT.', 'Pending', GETDATE(), GETDATE()),

-- 10. Student report Student
(8, 9, NULL, 'User', N'Tài khoản có dấu hiệu giả mạo.', 'Pending', GETDATE(), GETDATE());
GO