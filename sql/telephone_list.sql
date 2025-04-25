CREATE TABLE add_telephone_list ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    telephoneAllw DECIMAL(10, 2) NOT NULL 
);

CREATE TABLE add_duty_telephone (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addDuty_id INT NOT NULL,
    telephoneAllw_id INT NOT NULL,
    FOREIGN KEY (addDuty_id) REFERENCES addDuty(id),
    FOREIGN KEY (telephoneAllw_id) REFERENCES add_telephone_list(id)
);

INSERT INTO add_telephone_list (id, telephoneAllw) VALUES
(1, 0.00),
(2, 300.00),
(3, 400.00),
(4, 500.00),
(5, 600.00),
(6, 700.00),
(7, 800.00),
(8, 1100.00),
(9, 1200.00),
(10, 1300.00),
(11, 1500.00),
(12, 2500.00);
