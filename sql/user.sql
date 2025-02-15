CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employeeNo` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `userrole_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 2,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data with status column
INSERT INTO `user` (`id`, `employee_id`, `employeeNo`, `name`, `userrole_id`, `email`, `password`, `status`) VALUES
(7, 2, '12500001', 'PERVEJ CHOWKIDER', 1, 'admin@gmail.com', '$2y$10$92wU5LfcQ3PsiFWZJ1dcs.PyC3s60YoM03IuYtC499A5g2JHe1XJS', 1),
(10, 9, '12500003', 'NAYAN MALAKAR', 2, 'naayan@gmail.com', '$2y$10$gggwPDL8IGFCJxSFOSTCZOzZ6uJFxmrKw2WwjePzPZv9qgIY6t0Eu', 1),
(11, 10, '22500004', 'NUPUR', 3, 'nupur@gmail.com', '$2y$10$7TuWRjdMnfu1EsIaKA8AaeMf8sEO2mtx1RtufRSizTayzChOxWWLq', 1),
(12, 11, '22500005', 'NUHIN', 4, 'nuhin@gmail.com', '$2y$10$V3mUACXcPtP4qr0q6jtUHe3QBEBJ7nClzyqWQ.tEWVv9ofPnl8OzO', 1);
