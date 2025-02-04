CREATE TABLE deductionList (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dedName VARCHAR(100) NOT NULL,
    dedPercentage INT NOT NULL,
    dedValue DECIMAL(10, 2) NOT NULL
);
