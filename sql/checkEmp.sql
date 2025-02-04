
CREATE TABLE checkEmployee ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    employeeNo VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL, 
    empStatus VARCHAR(50), 
    grade VARCHAR(255) NOT NULL,
    designation VARCHAR(255) NOT NULL,
    department_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id) 
);
