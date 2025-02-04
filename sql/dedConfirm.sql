CREATE TABLE dedConfirm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    dedTotal DECIMAL(10, 2),
    empDeduction_id INT,
    deductionList_id INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id),
    FOREIGN KEY (empDeduction_id) REFERENCES empDeduction(id),
    FOREIGN KEY (deductionList_id) REFERENCES deductionList(id)
);