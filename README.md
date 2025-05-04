# Web-based Automated Payroll Management System

## Introduction

### System Overview
The **Web-based Automated Payroll Management System (WAPMS)** is a comprehensive solution developed to automate payroll processes in organizations. It eliminates the need for manual intervention in calculating salaries, allowances, deductions, and generating payroll reports. This system ensures accurate and timely processing of employee payrolls, reducing human error, administrative effort, and processing time.

By using this automated system, administrators can easily manage employee records, process salaries, generate reports, and handle allowances and deductions efficiently. The system uses modern technologies, including **PHP**, **MySQL**, **Tailwind CSS**, and **DaisyUI**, to ensure robustness, scalability, and security.

---

## Manual Payroll System vs. Web-based Automated System

### Manual Payroll System
In traditional organizations, payroll systems are often managed manually, either using spreadsheets or paper-based records. The process usually involves several steps that require significant human effort and are prone to errors. The following challenges are commonly faced with manual payroll systems:

- **Time-Consuming**: Payroll processing requires manually calculating salaries, allowances, deductions, and other components for each employee, which can be very time-consuming, especially for large organizations.
- **Human Error**: Calculations are prone to errors, such as incorrect deductions, allowances, or tax rates, leading to discrepancies in employee salaries.
- **Lack of Transparency**: Employees and administrators may have difficulty accessing accurate information regarding payroll details, allowances, or deductions.
- **Complexity**: Managing numerous employees with different allowances and deductions manually can lead to confusion and mistakes.
- **Limited Reporting**: Generating payroll reports is often cumbersome and may require significant time to compile, leading to delays in getting accurate payroll summaries.

### Web-based Automated Payroll System
In contrast, the Web-based Automated Payroll Management System provides an automated, secure, and efficient solution for managing payroll operations. Here’s how it compares to the manual payroll system:

- **Efficiency**: The system automates payroll calculations, reducing the time taken to process salaries from hours to minutes. Employees' salaries, allowances, and deductions are calculated automatically based on predefined rules.
- **Accuracy**: Automated calculations reduce the risk of human errors, ensuring accurate salary processing every time.
- **Transparency**: Employees and administrators can easily access payroll details and reports through the system. Employees can view their payslips and other payroll-related data on-demand, improving transparency.
- **Simplicity**: Payroll processing is simplified with automated features, such as salary calculation, allowance management, deduction setup, and report generation.
- **Reporting**: The system allows for easy generation of payroll reports that can be downloaded in PDF format, ensuring that payroll summaries are readily available for review by employees, HR, and finance departments.
- **Security**: Sensitive payroll data is securely stored in the database and can only be accessed by authorized users, providing a high level of confidentiality.

## Key Benefits:
- **Reduced Human Effort**: Significant reduction in manual work for HR staff and payroll administrators.
- **Faster Processing**: Payroll runs can be completed in minutes, allowing for faster turnaround and more time for strategic tasks.
- **Compliance**: Automatic updates for tax laws and deductions can be incorporated, ensuring the organization stays compliant with national and regional regulations.
- **Scalability**: As the organization grows, the system can handle the addition of new employees and complex payroll structures without requiring additional resources.

---

## The System in Action

With the Web-based Automated Payroll Management System, you can:

- **Manage Employee Information**: All employee data, such as personal information, department, designation, and salary components, are stored in a centralized database. This allows easy access and management.
- **Process Payroll**: Payroll is automatically calculated based on the rules set by the admin, including base salary, bonuses, allowances, and deductions.
- **Generate Reports**: Payroll reports for individual employees or entire departments can be generated in PDF format.
- **Manage Allowances and Deductions**: Various types of allowances (e.g., travel, housing) and deductions (e.g., taxes, insurance) can be configured and managed easily.
- **Real-Time Updates**: Any changes in employee data, allowances, or deductions are reflected immediately, ensuring the payroll system stays up-to-date.

---

## Key Features

### 1. **User Roles and Access Control**
   - **Super Admin**: Full access to all system functionalities.
   - **Admin**: Can manage employee records, salaries, and generate reports.
   - **Registrant**: Can register new employees into the system.
   - **Updater**: Can update existing employee data but cannot create new users.

   Dashboard of authorized users:
   ![Role Management](./uploads/dashboard.png)

   Admin Panel to accept and manage user roles:
   ![Role Management](./uploads/role.png)

---

### 2. **Employee List of the System**
   - Employees have no access to the system but can view their reports after generation by the admin.
   ![Employee Management](./uploads/employee.png)

---

### 3. **Users of the System**
   - Users have different access based on their roles in the system.
   ![Users](./uploads/users.png)

---

### 4. **Allowance and Deduction Management**
   - Authorized users can configure and manage different types of allowances (e.g., travel, housing) and deductions (e.g., tax, insurance).
   ![Allowance](./uploads/allowance.png) 
   ![Deduction](./uploads/deduction.png)

---

### 5. **Additional Duty**
   - Employees assigned to additional duties will receive salary benefits, calculated based on the maximum salary and a specific allowance.
   ![Additional Duty](./uploads/add.png)

---

### 6. **Employee Management**
   - Store and manage detailed employee profiles, including registration, data updates, and deletions.
   ![Profile Management](./uploads/profile.png)

---

### 7. **Payroll Processing**
   - Automates salary calculation based on base salary, allowances, deductions, and other payroll components.
   ![Payroll](./uploads/payroll.png)

---

### 8. **Report Generation**
   - Payroll reports can be generated for employees, departments, designations, grades, genders, and the entire university, then exported as PDF files.
   
   Employee reports (monthly and yearly):
   ![Employee Month](./uploads/empMonth.png)
   ![Employee Year](./uploads/empYear.png)

   Department reports (monthly and yearly):
   ![Department Month](./uploads/deptMonth.png)
   ![Department Year](./uploads/deptYear.png)

   Designation reports (monthly and yearly):
   ![Designation Month](./uploads/desiMonth.png)
   ![Designation Year](./uploads/desiYear.png)

   Grade reports (monthly and yearly):
   ![Grade Month](./uploads/gradeMonth.png)
   ![Grade Year](./uploads/gradeYear.png)

   Gender reports (monthly and yearly):
   ![Gender Month](./uploads/genderMonth.png)
   ![Gender Year](./uploads/genderYear.png)

   University-wide reports (monthly and yearly):
   ![University Month](./uploads/uniMonth.png)
   ![University Year](./uploads/uniYear.png)

---

### 9. **Database of the System**
   - **Database design**:
   ![Database Design](./uploads/database.png)

   **Flow Chart**:
   ![Flowchart](./uploads/flowchart.png)

---

## Technologies Used

- **Frontend**: HTML, Tailwind CSS, JavaScript, DaisyUI, JSPDF
- **Backend**: PHP
- **Database**: MySQL
- **Development Tools**: VS Code, XAMPP, Git

---

### Next Steps
- **Installation**: Set up the system by cloning the repository and following the installation instructions.
- **Contributing**: Feel free to fork the repository and submit a pull request with any enhancements or fixes.
- **License**: This project is licensed under the MIT License.

---

### Conclusion

The **Web-based Automated Payroll Management System** is a solution designed to improve payroll efficiency, reduce errors, and provide transparency in payroll processing. By automating payroll and related functions, organizations can save time, ensure compliance, and maintain high accuracy. 

This project demonstrates the power of automation in managing complex administrative tasks, making it an essential tool for modern organizations.


