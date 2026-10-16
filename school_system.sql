-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 08:31 PM
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
-- Database: `school_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_text` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action_text`, `created_at`) VALUES
(1, 1, 'Logged in', '2026-05-12 10:20:19'),
(2, 1, 'Logged out', '2026-05-12 10:20:27'),
(3, NULL, 'New registration', '2026-05-12 10:22:00'),
(4, 1, 'Logged in', '2026-05-12 10:22:06'),
(5, 1, 'Approved ID 2', '2026-05-12 10:22:12'),
(6, 1, 'Logged out', '2026-05-12 10:22:17'),
(7, 2, 'Logged in', '2026-05-12 10:22:27'),
(8, 2, 'Logged out', '2026-05-12 10:23:00'),
(9, NULL, 'New registration', '2026-05-12 10:24:17'),
(10, 1, 'Logged in', '2026-05-12 10:24:29'),
(11, 1, 'Logged out', '2026-05-12 10:24:36'),
(12, NULL, 'New registration', '2026-05-12 10:25:44'),
(13, NULL, 'New registration', '2026-05-12 10:26:11'),
(14, 1, 'Logged in', '2026-05-12 10:26:17'),
(15, 1, 'Approved ID 4', '2026-05-12 10:26:27'),
(16, 1, 'Approved ID 5', '2026-05-12 10:26:28'),
(17, 1, 'Logged out', '2026-05-12 10:26:29'),
(18, 5, 'Logged in', '2026-05-12 10:26:37'),
(19, 5, 'Logged out', '2026-05-12 10:26:57'),
(20, 4, 'Logged in', '2026-05-12 10:27:01'),
(21, 4, 'Logged out', '2026-05-12 10:27:06'),
(22, 2, 'Logged in', '2026-05-12 10:27:09'),
(23, 2, 'Logged out', '2026-05-12 10:27:35'),
(24, 1, 'Logged in', '2026-05-12 10:27:38'),
(25, 1, 'Logged out', '2026-05-12 10:27:46'),
(26, 4, 'Logged in', '2026-05-12 10:27:51'),
(27, 4, 'Logged out', '2026-05-12 10:28:49'),
(28, 5, 'Logged in', '2026-05-12 10:28:52'),
(29, 5, 'Logged out', '2026-05-12 10:44:15'),
(30, 1, 'Logged in', '2026-05-12 11:23:57'),
(31, 1, 'Logged out', '2026-05-12 11:24:15'),
(32, NULL, 'New registration', '2026-05-12 16:47:52'),
(33, NULL, 'New registration', '2026-05-12 16:50:08'),
(34, 1, 'Logged in', '2026-05-12 17:05:10'),
(35, 1, 'Logged out', '2026-05-12 17:12:25'),
(36, 2, 'Logged in', '2026-05-12 17:12:36'),
(37, 2, 'Logged out', '2026-05-12 17:15:08'),
(38, 2, 'Logged in', '2026-05-12 17:35:10'),
(39, 2, 'Logged out', '2026-05-12 17:35:19'),
(40, 2, 'Logged in', '2026-05-12 17:35:52'),
(41, 2, 'Logged out', '2026-05-12 17:35:54'),
(42, 4, 'Logged in', '2026-05-12 17:37:19'),
(43, 4, 'Logged out', '2026-05-12 17:38:05'),
(44, 2, 'Logged in', '2026-05-12 17:38:14'),
(45, 2, 'Logged out', '2026-05-12 17:38:44'),
(46, NULL, 'New registration', '2026-05-12 17:39:27'),
(47, 1, 'Logged in', '2026-05-12 17:40:01'),
(48, 1, 'Approved ID 8', '2026-05-12 17:40:06'),
(49, 1, 'Logged out', '2026-05-12 17:40:08'),
(50, 8, 'Logged in', '2026-05-12 17:40:11'),
(51, 8, 'Logged out', '2026-05-12 17:40:19'),
(52, 2, 'Logged in', '2026-05-12 17:40:27'),
(53, 2, 'Logged out', '2026-05-12 17:40:36'),
(54, 1, 'Logged in', '2026-05-12 17:40:53'),
(55, 1, 'Logged out', '2026-05-12 17:41:26'),
(56, 8, 'Logged in', '2026-05-12 17:41:31'),
(57, 8, 'Logged out', '2026-05-12 17:42:20'),
(58, 4, 'Logged in', '2026-05-12 17:42:29'),
(59, 4, 'Logged out', '2026-05-12 17:44:11'),
(60, 5, 'Logged in', '2026-05-12 17:44:13'),
(61, 8, 'Logged in', '2026-05-12 17:45:48'),
(62, 5, 'Logged out', '2026-05-12 18:04:57'),
(63, 1, 'Logged in', '2026-05-12 18:05:03'),
(64, 1, 'Logged out', '2026-05-12 18:05:10'),
(65, NULL, 'New registration', '2026-05-12 18:06:00'),
(66, 1, 'Logged in', '2026-05-12 18:06:15'),
(67, 1, 'Approved ID 9', '2026-05-12 18:06:20'),
(68, 1, 'Logged out', '2026-05-12 18:06:21'),
(69, 9, 'Logged in', '2026-05-12 18:06:27'),
(70, 9, 'Logged out', '2026-05-12 18:06:59'),
(71, NULL, 'New registration', '2026-05-12 18:07:36'),
(72, 1, 'Logged in', '2026-05-12 18:07:44'),
(73, 1, 'Approved ID 10', '2026-05-12 18:07:47'),
(74, 1, 'Logged out', '2026-05-12 18:07:48'),
(75, 10, 'Logged in', '2026-05-12 18:07:51'),
(76, 10, 'Logged out', '2026-05-12 18:08:32'),
(77, 9, 'Logged in', '2026-05-12 18:08:53'),
(78, 9, 'Logged out', '2026-05-12 18:11:35'),
(79, 4, 'Logged in', '2026-05-12 18:11:49'),
(80, 4, 'Logged out', '2026-05-12 18:12:10'),
(81, 8, 'Logged in', '2026-05-12 18:12:15'),
(82, 8, 'Logged out', '2026-05-12 18:12:20'),
(83, 5, 'Logged in', '2026-05-12 18:12:24'),
(84, 5, 'Logged out', '2026-05-12 18:12:43'),
(85, 8, 'Logged in', '2026-05-12 18:12:47'),
(86, 8, 'Logged out', '2026-05-12 18:12:54'),
(87, 5, 'Logged in', '2026-05-12 18:13:03'),
(88, 8, 'Logged out', '2026-05-12 18:16:41'),
(89, 2, 'Logged in', '2026-05-12 18:16:46'),
(90, 2, 'Logged out', '2026-05-12 18:21:13'),
(91, 8, 'Logged in', '2026-05-12 18:21:21'),
(92, 8, 'Logged out', '2026-05-12 18:21:25'),
(93, 1, 'Logged in', '2026-05-12 18:21:29'),
(94, 1, 'Updated fees', '2026-05-12 18:21:35'),
(95, 5, 'Logged out', '2026-05-12 18:22:03'),
(96, 4, 'Logged in', '2026-05-12 18:22:10');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `max_units` int(11) DEFAULT 24
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `department`, `max_units`) VALUES
(1, 'BSIT', 'BS Information Technology', 'College of IT', 24),
(2, 'BSCS', 'BS Computer Science', 'College of IT', 25),
(3, 'BSBA', 'BS Business Administration', 'College of Business', 21);

-- --------------------------------------------------------

--
-- Table structure for table `course_subjects`
--

CREATE TABLE `course_subjects` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `yearlevel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_subjects`
--

INSERT INTO `course_subjects` (`id`, `course_id`, `subject_id`, `sy_id`, `sem_id`, `yearlevel`) VALUES
(2, 1, 5, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'College of IT'),
(2, 'College of Business'),
(3, 'College of Engineering'),
(4, 'College of Arts');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_subject_id` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade_1st` decimal(5,2) DEFAULT NULL,
  `grade_2nd` decimal(5,2) DEFAULT 0.00,
  `grade_3rd` decimal(5,2) DEFAULT 0.00,
  `grade_final` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_paid` timestamp NOT NULL DEFAULT current_timestamp(),
  `cashier_id` int(11) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(6, 'cashier'),
(3, 'dean'),
(4, 'finance'),
(7, 'records'),
(2, 'student'),
(5, 'teacher');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `name`) VALUES
(1, '2023-2024'),
(2, '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`, `course_code`, `sy_id`, `sem_id`) VALUES
(1, 'BSIT 1', 'BSIT', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`) VALUES
(1, '1st Semester'),
(2, '2nd Semester');

-- --------------------------------------------------------

--
-- Table structure for table `student_accounts`
--

CREATE TABLE `student_accounts` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `total_fees` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `permit_status` varchar(20) DEFAULT 'Pending',
  `discount_type` enum('none','fixed','percent') DEFAULT 'none',
  `discount_value` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_accounts`
--

INSERT INTO `student_accounts` (`id`, `student_id`, `sy_id`, `sem_id`, `total_fees`, `amount_paid`, `balance`, `permit_status`, `discount_type`, `discount_value`) VALUES
(1, 2, 1, 1, 0.00, 0.00, 0.00, 'Pending', 'none', 0.00),
(2, 2, 2, 1, 0.00, 0.00, 0.00, 'Pending', 'none', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `sy_id`, `sem_id`, `status`) VALUES
(9, 2, 5, 1, 1, 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `units` int(11) NOT NULL,
  `prerequisite_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `units`, `prerequisite_id`) VALUES
(1, 'IT 101', 'Introduction to Computing', 3, NULL),
(2, 'IT 102', 'Computer Programming 1', 3, NULL),
(3, 'IT 103', 'Web Development', 3, NULL),
(4, 'MATH 101', 'College Algebra', 3, NULL),
(5, 'ENG 101', 'English Communication', 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `fee_per_unit` decimal(10,2) DEFAULT 500.00,
  `misc_fee` decimal(10,2) DEFAULT 50.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `schedule` varchar(100) DEFAULT '',
  `room` varchar(50) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `teacher_id`, `subject_id`, `sy_id`, `sem_id`, `schedule`, `room`) VALUES
(1, 8, 5, 1, 1, '8:00am-10:00am', 'COMC');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `email`, `first_name`, `middle_name`, `last_name`, `suffix`, `birthdate`, `gender`, `contact_number`, `address`, `department`, `course`, `year_level`, `profile_pic`, `is_approved`, `created_at`) VALUES
(1, 1, 'admin', '$2y$10$OqpYAICoxGfMzoRcdFI0W.GbDpH/NJPv40I3ueAZICex9IGJ.uUc2', 'admin@school.com', 'Admin', NULL, 'User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'default.png', 1, '2026-05-11 07:48:49'),
(2, 2, 'student', '$2y$10$FljcQoxKT8kEvAq3Ox0LeuT7YVqr.OPXTY.ITHYMr1O72UHq8Eoz6', 'eqweqweqeqe@gmail.com', 'xyryl', 'bazarte', 'quibal', '', '2005-10-31', 'Male', '0546456456', 'bula', 'College of IT', 'BSIT', 2, '1778581320_hehe.png', 1, '2026-05-12 10:22:00'),
(3, 2, 'finance', '$2y$10$ojuvJ0JJMGbtmc7V8uvX7.G2bQf9kS/dXdxLqqK4QYgOhgUuc/Vzy', 'eqweqeqwewqe@gmail.com', 'eqweqeq', 'eqweqe', 'eqwewqe', '', '2005-10-31', 'Male', '0456456456', 'bula', 'College of Business', 'BSBA', 2, 'default.png', 2, '2026-05-12 10:24:17'),
(4, 4, 'finance1', '$2y$10$YLzVPQjZ/HCBDKwiZZc8Nev37uR3B6mj/kLrM5ny.XXYwC8MzSpv6', 'eqwqeqwkelqwek@gmail.com', 'dqdqwdqd', 'dwqdqww', 'dqwdqwdqw', '', '0000-00-00', 'Male', '03456456', '4645645', '', '', 0, 'default.png', 1, '2026-05-12 10:25:44'),
(5, 3, 'dean', '$2y$10$jw/hZacg.iemTvj2Zq84XOe.oeuJdLRlviH4VkqbMuwM/JdE6agn2', 'dakjdawkj@gmail.com', 'deaner', 'dl', 'dlster1', '', '2005-10-31', 'Male', '434234234', 'edgtert', 'College of IT', '', 0, 'default.png', 1, '2026-05-12 10:26:11'),
(8, 5, 'teacher', '$2y$10$K0kxYUUMSH0LRHuLwFFUvebg.TfhPS60zQqoa/ZQ46hsULLZ190xS', 'teacher@gmail.com', 'teacher', 'teacher', 'teacher', '', '2004-12-06', 'Female', '0909954226', '0252', 'College of IT', 'BSIT', 1, 'default.png', 1, '2026-05-12 17:39:27'),
(9, 6, 'cashier', '$2y$10$UCnZ2EUpY60N7s3NCeYWke0o3CeP5NduwYDdVWS9felXHg.hTQQlS', 'cashier@gmail.com', 'cashier', 'cashier', 'cashier', '', '2005-06-02', 'Female', '090995555', 'gensan 1 ', '', '', 0, 'default.png', 1, '2026-05-12 18:06:00'),
(10, 7, 'records', '$2y$10$qWWrFWjDDnvA16OjNhBeU.kPQulspUFsPWV/lFXuwdHrGtbJiAD7O', 'records@gmail.com', 'records', 'records', 'records', '', '1919-06-12', 'Female', '0909999552', 'mindanao 1', '', '', 0, 'default.png', 1, '2026-05-12 18:07:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_subjects`
--
ALTER TABLE `course_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_accounts`
--
ALTER TABLE `student_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_subjects`
--
ALTER TABLE `course_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_accounts`
--
ALTER TABLE `student_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
