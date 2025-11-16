-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 02:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hr_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `work_hours` decimal(4,2) DEFAULT NULL,
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `date` date NOT NULL,
  `status` enum('present','absent','late','half_day','holiday','sick_leave','vacation') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `emp_id`, `check_in`, `check_out`, `work_hours`, `overtime_hours`, `date`, `status`, `notes`, `created_at`) VALUES
(1, 2, '2024-01-25 09:00:00', '2024-01-25 17:00:00', NULL, 0.00, '2024-01-25', 'present', NULL, '2025-11-14 01:04:51'),
(2, 3, '2024-01-25 09:15:00', '2024-01-25 17:00:00', NULL, 0.00, '2024-01-25', 'present', NULL, '2025-11-14 01:04:51'),
(3, 4, '2024-01-25 08:45:00', '2024-01-25 17:00:00', NULL, 0.00, '2024-01-25', 'present', NULL, '2025-11-14 01:04:51'),
(4, 5, '2024-01-25 09:05:00', '2024-01-25 17:00:00', NULL, 0.00, '2024-01-25', 'present', NULL, '2025-11-14 01:04:51'),
(5, 8, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 7.75, 0.00, '2025-11-14', 'present', '', '2025-11-14 01:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `job_id` int(11) NOT NULL,
  `applied_date` date DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `source` enum('website','linkedin','referral','indeed','other') DEFAULT 'website',
  `status` enum('applied','screening','phone_interview','technical_test','onsite_interview','reference_check','offer_pending','offer_accepted','rejected','withdrawn') DEFAULT 'applied',
  `current_stage_date` date DEFAULT NULL,
  `expected_salary` decimal(10,2) DEFAULT NULL,
  `notice_period` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `first_name`, `last_name`, `email`, `phone`, `job_id`, `applied_date`, `resume_path`, `cover_letter`, `source`, `status`, `current_stage_date`, `expected_salary`, `notice_period`, `notes`, `created_at`) VALUES
(1, 'Alex', 'Thompson', 'alex.t@email.com', '9876543210', 1, '2024-01-20', NULL, NULL, 'website', '', NULL, 85000.00, NULL, NULL, '2025-11-14 01:04:51'),
(2, 'Lisa', 'Wang', 'lisa.w@email.com', '9876543211', 2, '2024-01-18', NULL, NULL, 'website', 'screening', NULL, 50000.00, NULL, NULL, '2025-11-14 01:04:51'),
(3, 'James', 'Rodriguez', 'james.r@email.com', '9876543212', 3, '2024-01-22', NULL, NULL, 'website', 'applied', NULL, 60000.00, NULL, NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`, `manager_id`, `location`, `description`, `budget`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources', NULL, 'Floor 1', 'Handles recruitment, training, and employee relations', 500000.00, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(2, 'Information Technology', 2, 'Floor 2', 'Manages technology infrastructure and software development', 1200000.00, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(3, 'Finance', NULL, 'Floor 3', 'Handles accounting, budgeting, and financial reporting', 800000.00, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(4, 'Marketing', 6, 'Floor 4', 'Responsible for marketing campaigns and brand management', 600000.00, '2025-11-14 01:04:50', '2025-11-14 01:04:51'),
(5, 'Operations', NULL, 'Floor 5', 'Manages daily business operations and logistics', 900000.00, '2025-11-14 01:04:50', '2025-11-14 01:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `emp_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `hire_date` date NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_role` enum('admin','manager','employee') DEFAULT 'employee',
  `status` enum('active','inactive','on_leave','terminated') DEFAULT 'active',
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`emp_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `date_of_birth`, `gender`, `hire_date`, `job_title`, `dept_id`, `salary`, `password_hash`, `user_role`, `status`, `emergency_contact_name`, `emergency_contact_phone`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@hrms.com', '1234567890', NULL, '1985-01-15', 'Male', '2020-01-01', 'System Administrator', 1, 75000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(2, 'John', 'Manager', 'john.manager@hrms.com', '1234567891', NULL, '1988-03-20', 'Male', '2021-03-15', 'IT Manager', 2, 65000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(3, 'Sarah', 'Johnson', 'sarah.j@hrms.com', '1234567892', NULL, '1992-06-10', 'Female', '2022-06-01', 'HR Specialist', 1, 50000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(4, 'Mike', 'Chen', 'mike.chen@hrms.com', '1234567893', NULL, '1990-11-05', 'Male', '2021-11-20', 'Software Developer', 2, 60000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(5, 'Emily', 'Davis', 'emily.d@hrms.com', '1234567894', NULL, '1993-02-28', 'Female', '2023-01-10', 'Financial Analyst', 3, 55000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(6, 'David', 'Wilson', 'david.w@hrms.com', '1234567895', NULL, '1987-09-15', 'Male', '2020-08-12', 'Marketing Manager', 4, 62000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(7, 'Lisa', 'Brown', 'lisa.b@hrms.com', '1234567896', NULL, '1991-12-03', 'Female', '2022-02-20', 'Operations Coordinator', 5, 48000.00, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', 'active', NULL, NULL, NULL, '2025-11-14 01:04:50', '2025-11-14 01:04:50'),
(8, 'Saad', 'Ur Rahman', 'saad@gmail.com', '03398364789', '', NULL, NULL, '2015-02-22', 'Team Lead', 2, 45000.00, '$2y$10$8p0N/2KT/UwKs4TxzE7E..SCT949/.TOBXuta9BjCPE9vZBpcIBry', 'employee', 'active', NULL, NULL, NULL, '2025-11-14 01:09:27', '2025-11-14 01:10:16');

-- --------------------------------------------------------

--
-- Table structure for table `employee_projects`
--

CREATE TABLE `employee_projects` (
  `emp_project_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `hours_worked` decimal(6,2) DEFAULT 0.00,
  `status` enum('assigned','active','completed','removed') DEFAULT 'assigned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_projects`
--

INSERT INTO `employee_projects` (`emp_project_id`, `emp_id`, `project_id`, `role`, `assigned_date`, `hours_worked`, `status`, `created_at`) VALUES
(1, 3, 1, 'HR Analyst', '2024-01-01', 0.00, 'assigned', '2025-11-14 01:04:51'),
(2, 4, 2, 'Lead Developer', '2024-02-01', 0.00, 'assigned', '2025-11-14 01:04:51'),
(3, 5, 2, 'Database Specialist', '2024-02-01', 0.00, 'assigned', '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `employee_skills`
--

CREATE TABLE `employee_skills` (
  `emp_skill_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `years_of_experience` int(11) DEFAULT 0,
  `certified` tinyint(1) DEFAULT 0,
  `certification_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_skills`
--

INSERT INTO `employee_skills` (`emp_skill_id`, `emp_id`, `skill_id`, `proficiency_level`, `years_of_experience`, `certified`, `certification_date`, `created_at`) VALUES
(1, 2, 1, 'advanced', 5, 0, NULL, '2025-11-14 01:04:51'),
(2, 2, 2, 'advanced', 5, 0, NULL, '2025-11-14 01:04:51'),
(3, 2, 3, 'advanced', 5, 0, NULL, '2025-11-14 01:04:51'),
(4, 2, 4, 'intermediate', 3, 0, NULL, '2025-11-14 01:04:51'),
(5, 4, 1, 'expert', 8, 0, NULL, '2025-11-14 01:04:51'),
(6, 4, 2, 'expert', 8, 0, NULL, '2025-11-14 01:04:51'),
(7, 4, 3, 'expert', 8, 0, NULL, '2025-11-14 01:04:51'),
(8, 4, 7, 'advanced', 4, 0, NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `employee_training`
--

CREATE TABLE `employee_training` (
  `emp_training_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `enrollment_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `completion_status` enum('enrolled','in_progress','completed','dropped') DEFAULT 'enrolled',
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `interview_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `interview_type` enum('phone','video','technical','onsite','hr') DEFAULT 'phone',
  `interview_date` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `interviewer_id` int(11) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  `feedback` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `job_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `salary_range_min` decimal(10,2) DEFAULT NULL,
  `salary_range_max` decimal(10,2) DEFAULT NULL,
  `position_type` enum('full_time','part_time','contract','internship') DEFAULT 'full_time',
  `work_location` varchar(100) DEFAULT NULL,
  `experience_required` varchar(100) DEFAULT NULL,
  `education_required` varchar(100) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `status` enum('draft','open','closed','filled','cancelled') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`job_id`, `job_title`, `dept_id`, `job_description`, `requirements`, `responsibilities`, `salary_range_min`, `salary_range_max`, `position_type`, `work_location`, `experience_required`, `education_required`, `posting_date`, `closing_date`, `status`, `created_by`, `created_at`) VALUES
(1, 'Senior Developer', 2, 'Develop and maintain software applications using modern technologies', '5+ years experience, PHP, MySQL, JavaScript, React', NULL, 70000.00, 90000.00, 'full_time', NULL, NULL, NULL, '2024-01-15', '2024-02-15', 'open', NULL, '2025-11-14 01:04:51'),
(2, 'HR Coordinator', 1, 'Assist in HR operations and employee management', '2+ years HR experience, communication skills', NULL, 45000.00, 55000.00, 'full_time', NULL, NULL, NULL, '2024-01-10', '2024-02-10', 'open', NULL, '2025-11-14 01:04:51'),
(3, 'Financial Analyst', 3, 'Analyze financial data and prepare reports', '3+ years finance experience, Excel, Accounting degree', NULL, 50000.00, 65000.00, 'full_time', NULL, NULL, NULL, '2024-01-20', '2024-02-20', 'open', NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `leave_type` enum('sick_leave','vacation','personal','maternity','paternity','emergency') DEFAULT 'vacation',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `emp_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `approved_date`, `comments`, `created_at`) VALUES
(1, 3, 'vacation', '2024-02-10', '2024-02-14', 5, 'Family vacation', 'approved', NULL, NULL, NULL, '2025-11-14 01:04:51'),
(2, 4, 'sick_leave', '2024-01-20', '2024-01-21', 2, 'Medical appointment', 'approved', NULL, NULL, NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `related_module` enum('attendance','payroll','leave','performance','training','recruitment') DEFAULT 'attendance',
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `emp_id`, `title`, `message`, `type`, `is_read`, `related_module`, `related_id`, `created_at`) VALUES
(1, 2, 'Payroll Processed', 'Your salary for January has been processed', 'success', 0, 'payroll', NULL, '2025-11-14 01:04:51'),
(2, 3, 'Leave Approved', 'Your vacation leave has been approved', 'success', 0, 'leave', NULL, '2025-11-14 01:04:51'),
(3, 4, 'Training Reminder', 'PHP Advanced Course starts next week', 'info', 0, 'training', NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `review_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `rating` decimal(3,2) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `work_quality` decimal(3,2) DEFAULT NULL,
  `punctuality` decimal(3,2) DEFAULT NULL,
  `teamwork` decimal(3,2) DEFAULT NULL,
  `communication` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `goals` text DEFAULT NULL,
  `review_date` date NOT NULL,
  `next_review_date` date DEFAULT NULL,
  `status` enum('draft','submitted','approved') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_reviews`
--

INSERT INTO `performance_reviews` (`review_id`, `emp_id`, `reviewer_id`, `rating`, `work_quality`, `punctuality`, `teamwork`, `communication`, `comments`, `strengths`, `areas_for_improvement`, `goals`, `review_date`, `next_review_date`, `status`, `created_at`) VALUES
(1, 3, 1, 4.50, 4.50, 4.00, 4.50, 4.00, 'Excellent performance in HR operations', NULL, NULL, NULL, '2024-01-15', NULL, 'approved', '2025-11-14 01:04:51'),
(2, 4, 2, 4.80, 5.00, 4.50, 4.50, 4.50, 'Outstanding technical skills and dedication', NULL, NULL, NULL, '2024-01-18', NULL, 'approved', '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `project_manager_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planning','in_progress','on_hold','completed','cancelled') DEFAULT 'planning',
  `budget` decimal(15,2) DEFAULT NULL,
  `actual_cost` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `description`, `dept_id`, `project_manager_id`, `start_date`, `end_date`, `status`, `budget`, `actual_cost`, `created_at`) VALUES
(1, 'HR System Upgrade', 'Upgrade current HR management system', 1, 1, '2024-01-01', '2024-06-30', 'in_progress', 50000.00, NULL, '2025-11-14 01:04:51'),
(2, 'E-commerce Platform', 'Develop new e-commerce website', 2, 2, '2024-02-01', '2024-08-31', 'planning', 150000.00, NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `salary_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` enum('bank_transfer','cash','check') DEFAULT 'bank_transfer',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`salary_id`, `emp_id`, `base_salary`, `bonus`, `allowances`, `deductions`, `tax_amount`, `net_salary`, `pay_period_start`, `pay_period_end`, `payment_date`, `payment_status`, `payment_method`, `notes`, `created_at`) VALUES
(1, 2, 65000.00, 3000.00, 0.00, 1200.00, 9750.00, 57050.00, '2024-01-01', '2024-01-31', NULL, 'paid', 'bank_transfer', NULL, '2025-11-14 01:04:51'),
(2, 3, 50000.00, 2000.00, 0.00, 800.00, 7500.00, 43700.00, '2024-01-01', '2024-01-31', NULL, 'paid', 'bank_transfer', NULL, '2025-11-14 01:04:51'),
(3, 4, 60000.00, 2500.00, 0.00, 1000.00, 9000.00, 52500.00, '2024-01-01', '2024-01-31', NULL, 'paid', 'bank_transfer', NULL, '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `category` enum('technical','soft','language','certification') DEFAULT 'technical',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `skill_name`, `category`, `description`, `created_at`) VALUES
(1, 'PHP', 'technical', 'Server-side scripting language', '2025-11-14 01:04:51'),
(2, 'JavaScript', 'technical', 'Client-side programming language', '2025-11-14 01:04:51'),
(3, 'MySQL', 'technical', 'Database management system', '2025-11-14 01:04:51'),
(4, 'Project Management', 'soft', 'Managing projects and teams', '2025-11-14 01:04:51'),
(5, 'Communication', 'soft', 'Effective verbal and written communication', '2025-11-14 01:04:51'),
(6, 'Leadership', 'soft', 'Leading and motivating teams', '2025-11-14 01:04:51'),
(7, 'React', 'technical', 'JavaScript library for building user interfaces', '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'company_name', 'Tech Solutions Inc.', 'string', 'Company name for system display', '2025-11-14 01:04:51'),
(2, 'attendance_start_time', '09:00:00', 'string', 'Official work start time', '2025-11-14 01:04:51'),
(3, 'attendance_end_time', '17:00:00', 'string', 'Official work end time', '2025-11-14 01:04:51'),
(4, 'late_threshold_minutes', '15', 'integer', 'Minutes after start time considered late', '2025-11-14 01:04:51'),
(5, 'annual_leave_days', '21', 'integer', 'Annual leave entitlement in days', '2025-11-14 01:04:51'),
(6, 'sick_leave_days', '10', 'integer', 'Sick leave entitlement in days', '2025-11-14 01:04:51'),
(7, 'tax_percentage', '15', 'integer', 'Default tax percentage for payroll', '2025-11-14 01:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `training_id` int(11) NOT NULL,
  `program_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `trainer` varchar(100) DEFAULT NULL,
  `training_type` enum('technical','soft_skills','compliance','leadership') DEFAULT 'technical',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_programs`
--

INSERT INTO `training_programs` (`training_id`, `program_name`, `description`, `trainer`, `training_type`, `start_date`, `end_date`, `duration_hours`, `location`, `max_participants`, `cost`, `status`, `created_at`) VALUES
(1, 'Leadership Development', 'Develop leadership skills for managers', 'Dr. Sarah Johnson', 'leadership', '2024-02-01', '2024-02-05', 20, NULL, 15, NULL, 'scheduled', '2025-11-14 01:04:51'),
(2, 'PHP Advanced Course', 'Advanced PHP programming techniques', 'Mike Chen', 'technical', '2024-02-10', '2024-02-12', 15, NULL, 10, NULL, 'scheduled', '2025-11-14 01:04:51'),
(3, 'Communication Skills', 'Improve workplace communication', 'Emily Davis', 'soft_skills', '2024-02-15', '2024-02-16', 10, NULL, 20, NULL, 'scheduled', '2025-11-14 01:04:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_employee_date` (`emp_id`,`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `dept_name` (`dept_name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`emp_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `employee_projects`
--
ALTER TABLE `employee_projects`
  ADD PRIMARY KEY (`emp_project_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD PRIMARY KEY (`emp_skill_id`),
  ADD UNIQUE KEY `unique_employee_skill` (`emp_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD PRIMARY KEY (`emp_training_id`),
  ADD UNIQUE KEY `unique_employee_training` (`emp_id`,`training_id`),
  ADD KEY `training_id` (`training_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `interviewer_id` (`interviewer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `project_manager_id` (`project_manager_id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`salary_id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `training_programs`
--
ALTER TABLE `training_programs`
  ADD PRIMARY KEY (`training_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_projects`
--
ALTER TABLE `employee_projects`
  MODIFY `emp_project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `emp_skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_training`
--
ALTER TABLE `employee_training`
  MODIFY `emp_training_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `training_programs`
--
ALTER TABLE `training_programs`
  MODIFY `training_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_projects`
--
ALTER TABLE `employee_projects`
  ADD CONSTRAINT `employee_projects_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD CONSTRAINT `employee_skills_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD CONSTRAINT `employee_training_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_training_ibfk_2` FOREIGN KEY (`training_id`) REFERENCES `training_programs` (`training_id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interviews_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interviews_ibfk_3` FOREIGN KEY (`interviewer_id`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_postings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `performance_reviews_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`project_manager_id`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL;

--
-- Constraints for table `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
