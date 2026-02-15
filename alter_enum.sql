ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'volunteer', 'rescuer', 'organization') DEFAULT 'user';
