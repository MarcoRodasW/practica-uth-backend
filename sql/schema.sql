CREATE DATABASE IF NOT EXISTS todo_api;
USE todo_api;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Todos table
CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    dueDate DATETIME,
    userId INT NOT NULL,
    status ENUM('Pending', 'InProgress', 'Completed') DEFAULT 'Pending' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);

-- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS CreateUser;
DROP PROCEDURE IF EXISTS GetUserByEmail;
DROP PROCEDURE IF EXISTS UpdateUserProfile;
DROP PROCEDURE IF EXISTS CreateTodo;
DROP PROCEDURE IF EXISTS GetUserTodos;
DROP PROCEDURE IF EXISTS GetTodoById;
DROP PROCEDURE IF EXISTS UpdateTodo;
DROP PROCEDURE IF EXISTS DeleteTodo;

-- Stored Procedures
DELIMITER //

CREATE PROCEDURE CreateUser(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    INSERT INTO users (username, email, password_hash) 
    VALUES (p_username, p_email, p_password_hash);
END //

CREATE PROCEDURE GetUserByEmail(IN p_email VARCHAR(100))
BEGIN
    SELECT id, username, email, password_hash, created_at 
    FROM users WHERE email = p_email;
END //

CREATE PROCEDURE UpdateUserProfile(
    IN p_id INT,
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    UPDATE users 
    SET username = COALESCE(p_username, username),
        email = COALESCE(p_email, email),
        password_hash = COALESCE(p_password_hash, password_hash)
    WHERE id = p_id;
END //

CREATE PROCEDURE CreateTodo(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_dueDate DATETIME,
    IN p_userId INT,
    IN p_status VARCHAR(20)
)
BEGIN
    INSERT INTO todos (title, description, dueDate, userId, status) 
    VALUES (p_title, p_description, p_dueDate, p_userId, p_status);
END //

CREATE PROCEDURE GetUserTodos(IN p_userId INT)
BEGIN
    SELECT * FROM todos WHERE userId = p_userId ORDER BY created_at DESC;
END //

CREATE PROCEDURE GetTodoById(IN p_id INT, IN p_userId INT)
BEGIN
    SELECT * FROM todos WHERE id = p_id AND userId = p_userId;
END //

CREATE PROCEDURE UpdateTodo(
    IN p_id INT,
    IN p_userId INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_dueDate DATETIME,
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE todos 
    SET title = COALESCE(p_title, title),
        description = COALESCE(p_description, description),
        dueDate = COALESCE(p_dueDate, dueDate),
        status = COALESCE(p_status, status)
    WHERE id = p_id AND userId = p_userId;
END //

CREATE PROCEDURE DeleteTodo(IN p_id INT, IN p_userId INT)
BEGIN
    DELETE FROM todos WHERE id = p_id AND userId = p_userId;
END //

DELIMITER ;