CREATE TABLE employee ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    employeeNo VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender VARCHAR(10),
    contactNo VARCHAR(15),
    email VARCHAR(100),
    empStatus VARCHAR(50),
    image VARCHAR(255),  -- Path to the uploaded image
    designation_id INT NOT NULL,
    department_id INT NOT NULL,
    no_of_increment INT DEFAULT 0,
    basic DECIMAL(10, 2),
    account_number VARCHAR(50) NOT NULL,
    grade_id INT NOT NULL,
    joining_date DATE NOT NULL,
    e_tin VARCHAR(50) NOT NULL,
    
    FOREIGN KEY (designation_id) REFERENCES designations(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (grade_id) REFERENCES grade(id)
);