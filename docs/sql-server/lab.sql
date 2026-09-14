CREATE SCHEMA lab;
GO
CREATE TABLE lab.Members (
    Id int IDENTITY PRIMARY KEY,
    Name nvarchar(160) NOT NULL,
    Active bit NOT NULL DEFAULT 1,
    ExpiresOn date NOT NULL
);
CREATE TABLE lab.CheckIns (
    Id bigint IDENTITY PRIMARY KEY,
    MemberId int NOT NULL REFERENCES lab.Members(Id),
    CheckedInAt datetime2 NOT NULL DEFAULT SYSUTCDATETIME()
);
CREATE INDEX IX_CheckIns_MemberDate ON lab.CheckIns(MemberId, CheckedInAt DESC);
CREATE TABLE lab.Audit (
    Id bigint IDENTITY PRIMARY KEY,
    MemberId int NOT NULL,
    OldExpiresOn date NOT NULL,
    NewExpiresOn date NOT NULL,
    ChangedAt datetime2 NOT NULL DEFAULT SYSUTCDATETIME()
);
GO
CREATE FUNCTION lab.MembershipStatus(@Active bit, @ExpiresOn date, @Today date)
RETURNS varchar(20)
AS
BEGIN
    RETURN CASE WHEN @Active = 0 THEN 'inactive'
                WHEN @ExpiresOn < @Today THEN 'expired'
                WHEN @ExpiresOn <= DATEADD(day, 5, @Today) THEN 'expiring_soon'
                ELSE 'active' END;
END;
GO
CREATE VIEW lab.DailyAttendance
AS
SELECT CAST(CheckedInAt AS date) AS AttendanceDate,
       COUNT_BIG(*) AS CheckInCount,
       COUNT(DISTINCT MemberId) AS UniqueMembers
FROM lab.CheckIns GROUP BY CAST(CheckedInAt AS date);
GO
CREATE PROCEDURE lab.RegisterCheckIn @MemberId int
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    BEGIN TRY
        BEGIN TRANSACTION;
        DECLARE @Active bit, @ExpiresOn date, @CheckInId bigint;
        SELECT @Active = Active, @ExpiresOn = ExpiresOn
        FROM lab.Members WITH (UPDLOCK, HOLDLOCK) WHERE Id = @MemberId;
        IF @Active IS NULL THROW 50001, 'Aluno inexistente.', 1;
        IF @Active = 0 OR @ExpiresOn < CAST(SYSUTCDATETIME() AS date)
            THROW 50002, 'Plano inativo ou vencido.', 1;
        SELECT TOP (1) @CheckInId = Id FROM lab.CheckIns
        WHERE MemberId = @MemberId AND CheckedInAt >= DATEADD(second, -60, SYSUTCDATETIME())
        ORDER BY CheckedInAt DESC;
        IF @CheckInId IS NULL
        BEGIN
            INSERT INTO lab.CheckIns(MemberId) VALUES (@MemberId);
            SET @CheckInId = SCOPE_IDENTITY();
        END;
        COMMIT;
        SELECT @CheckInId AS CheckInId;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        THROW;
    END CATCH;
END;
GO
CREATE TRIGGER lab.AuditRenewal ON lab.Members AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO lab.Audit(MemberId, OldExpiresOn, NewExpiresOn)
    SELECT i.Id, d.ExpiresOn, i.ExpiresOn
    FROM inserted i INNER JOIN deleted d ON i.Id = d.Id
    WHERE i.ExpiresOn <> d.ExpiresOn;
END;
GO
CREATE ROLE ironid_lab_operator;
GRANT EXECUTE ON OBJECT::lab.RegisterCheckIn TO ironid_lab_operator;
GRANT SELECT ON OBJECT::lab.DailyAttendance TO ironid_lab_operator;
GO
BEGIN TRANSACTION;
INSERT INTO lab.Members(Name, ExpiresOn) VALUES (N'Aluno de demonstração', DATEADD(day, 30, GETUTCDATE()));
DECLARE @MemberId int = SCOPE_IDENTITY();
EXEC lab.RegisterCheckIn @MemberId;
EXEC lab.RegisterCheckIn @MemberId;
IF (SELECT COUNT(*) FROM lab.CheckIns WHERE MemberId = @MemberId) <> 1
    THROW 50003, 'Falha na prevenção de duplicidade.', 1;
SELECT * FROM lab.DailyAttendance;
ROLLBACK;
