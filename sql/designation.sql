CREATE TABLE  designations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    designation VARCHAR(100) NOT NULL
);

INSERT INTO `designations` (`id`, `designation`) VALUES
(1, 'Professor'),
(2, 'Associate Professor'),
(3, 'Assistant Professor'),
(4, 'Lecturer'),
(5, 'Director'),
(6, 'Deputy Director'),
(7, 'Assistant Director');