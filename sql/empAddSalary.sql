-- Create empAddSalary table
CREATE TABLE empAddSalary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    chargeAllw DECIMAL(10, 2) NOT NULL,
    telephoneAllwance DECIMAL(10, 2) NOT NULL,
    telephoneAllw_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE CASCADE,
    FOREIGN KEY (telephoneAllw_id) REFERENCES telephoneAllw(id) ON DELETE SET NULL
);

-- Create empAddDesignation table
CREATE TABLE empAddDesignation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empAddSalary_id INT NOT NULL,
    addDuty_id INT NOT NULL,
    AdditionalDesignation VARCHAR(255) NOT NULL,
    FOREIGN KEY (empAddSalary_id) REFERENCES empAddSalary(id) ON DELETE CASCADE,
    FOREIGN KEY (addDuty_id) REFERENCES addDuty(id) ON DELETE CASCADE
);
