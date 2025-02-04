CREATE TABLE empDeduction (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    deductionList_id INT NOT NULL,
    dedPercentage DECIMAL(10, 2) NOT NULL,
    dedValue DECIMAL(10, 2) NOT NULL,
    dedTotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id),
    FOREIGN KEY (deductionList_id) REFERENCES deductionList(id)
);
