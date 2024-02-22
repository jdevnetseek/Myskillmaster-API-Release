SET @dbname := 'mysql_test_database';
SET @username := 'mysql_test_user';
SET @password := 'mysql_test_password';

-- Create Database
SET @dbStmt = CONCAT('CREATE DATABASE IF NOT EXISTS', ' ', @dbname);
PREPARE dbstmt FROM @dbStmt;
EXECUTE dbstmt;
DEALLOCATE PREPARE dbstmt;

-- Create User
SET @userStmt = CONCAT('CREATE USER IF NOT EXISTS "', @username, '"@"%"', ' IDENTIFIED BY  "', @password, '"');
PREPARE userstmt FROM @userStmt;
EXECUTE userstmt;
DEALLOCATE PREPARE userstmt;

-- Grand User PRIVILEGES
SET @grantStmt = CONCAT('GRANT ALL PRIVILEGES ON ', @dbname, '.*', ' TO "', @username, '"@"%"');
PREPARE grantStmt FROM @grantStmt;
EXECUTE grantStmt;
DEALLOCATE PREPARE grantStmt;
