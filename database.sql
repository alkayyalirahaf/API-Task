create DATABASE student_api
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

use student_api;

CREATE TABLE student (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY ,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    pass VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO student(name,email,course,pass)
VALUES
-- ('AHMAD' , 'ahmad1@gmail.com','php','$2y$12$T7C/MbTV9o348vJqs2PAW.P1LBnsgBLHyvPJQOx71hXXegQy2FJCO'),
('AHMAD' , 'ahmad2@gmail.com','C#','$2y$12$T7C/MbTV9o348vJqs2PAW.P1LBnsgBLHyvPJQOx71hXXegQy2FJCO'),
('AHMAD' , 'ahmad3@gmail.com','C++','$2y$12$T7C/MbTV9o348vJqs2PAW.P1LBnsgBLHyvPJQOx71hXXegQy2FJCO');