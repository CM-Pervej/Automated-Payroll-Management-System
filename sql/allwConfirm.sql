CREATE TABLE allwConfirm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    allwTotal DECIMAL(10, 2),
    empAllowance_id INT,
    allowanceList_id INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employee(id),
    FOREIGN KEY (empAllowance_id) REFERENCES empAllowance(id),
    FOREIGN KEY (allowanceList_id) REFERENCES allowanceList(id)
);
