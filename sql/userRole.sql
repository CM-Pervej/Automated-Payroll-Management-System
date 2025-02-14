CREATE TABLE userRole (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role varchar(255) NOT NULL
);

INSERT INTO userRole(id, role) VALUES
    (1, 'admin'),
    (2, 'hr manager'),
    (3, 'user (registration)'),
    (4, 'user (update)');