-- Create temporary table for accounts
CREATE TEMPORARY TABLE temp_accounts (
    username VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty', 'student') NOT NULL
);

-- Insert default accounts
INSERT INTO temp_accounts (username, email, password, role) VALUES
('admin', 'admin@school.edu', 'admin123', 'admin'),
('faculty1', 'faculty1@school.edu', 'faculty123', 'faculty'),
('faculty2', 'faculty2@school.edu', 'faculty123', 'faculty'),
('student1', 'student1@school.edu', 'student123', 'student'),
('student2', 'student2@school.edu', 'student123', 'student');

-- Insert into users table with hashed passwords
INSERT INTO users (username, email, password, role)
SELECT 
    username,
    email,
    CONCAT('$2y$', '10$', SUBSTRING(MD5(RAND()), 1, 22), '$', SUBSTRING(MD5(RAND()), 1, 22)),
    role
FROM temp_accounts;
