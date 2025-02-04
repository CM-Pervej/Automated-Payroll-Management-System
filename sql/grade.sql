CREATE TABLE grade( 
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade INT NOT NULL,
    scale VARCHAR(50) NOT NULL,
    increment INT NOT NULL,
    gradePercentage DECIMAL(5, 2) NOT NULL
);


INSERT INTO grade (id, grade, scale, increment, gradePercentage) VALUES   
(1, 1, '78000', 0, 0), 
(2, 2, '66000', 4, 3.75), 
(3, 3, '56500', 7, 4), 
(4, 4, '50000', 9, 4), 
(5, 5, '43000', 11, 4.5), 
(6, 6, '35500', 13, 5), 
(7, 7, '29000', 16, 5),
(8, 8, '23000', 18, 5),
(9, 9, '22000', 18, 5),
(10, 10, '16000', 18, 5),
(11, 11, '12500', 18, 5),
(12, 12, '11300', 18, 5),
(13, 13, '11000', 18, 5),
(14, 14, '10200', 18, 5),
(15, 15, '9700', 18, 5),
(16, 16, '9300', 18, 5),
(17, 17, '9000', 18, 5),
(18, 18, '8800', 18, 5),
(19, 19, '8500', 18, 5),
(20, 20, '8250', 18, 5);
