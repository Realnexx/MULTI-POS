CREATE TABLE ojt_students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    fname VARCHAR(100) NOT NULL,
    mname VARCHAR(100) NULL,
    lname VARCHAR(100) NOT NULL,
    course VARCHAR(150) NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    status ENUM('Active', 'Inactive', 'Completed', 'Dropped') DEFAULT 'Active'
);


CREATE TABLE ojt_attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    studID VARCHAR(50) NOT NULL,
    check_in DATETIME NULL,
    break_out DATETIME NULL,
    break_in DATETIME NULL,
    check_out DATETIME NULL,
    `timestamp` DATE NOT NULL,
    total_hours DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('Present', 'Absent', 'Late', 'Half-day') DEFAULT 'Present',
    description TEXT NULL,

    CONSTRAINT fk_ojt_attendance_student
        FOREIGN KEY (studID)
        REFERENCES ojt_students(student_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
