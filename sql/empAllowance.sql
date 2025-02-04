CREATE TABLE empAllowance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    allowanceList_id INT NOT NULL,
    allwPercentage DECIMAL(10, 2) NOT NULL,
    allwValue DECIMAL(10, 2) NOT NULL,
    allwTotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id),
    FOREIGN KEY (allowanceList_id) REFERENCES allowanceList(id)
);
