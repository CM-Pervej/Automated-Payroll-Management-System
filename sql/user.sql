CREATE TABLE `user` (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    employee_id INT NOT NULL UNIQUE,
    employeeNo VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE, 
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id)
);