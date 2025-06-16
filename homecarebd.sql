-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 11:33 AM
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
-- Database: `homecarebd`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `User_ID` int(11) NOT NULL,
  `Admin_since` date DEFAULT NULL,
  `Last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`User_ID`, `Admin_since`, `Last_login`) VALUES
(9, '2023-01-01', '2025-05-03 06:03:39'),
(10, '2024-06-15', '2025-04-28 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendant`
--

CREATE TABLE `attendant` (
  `User_ID` int(11) NOT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `Qualification` varchar(100) DEFAULT NULL,
  `Experience` int(11) DEFAULT NULL,
  `Availability` enum('available','unavailable') DEFAULT 'available',
  `Average_rating` decimal(3,1) DEFAULT NULL,
  `Verification_status` enum('pending','verified','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendant`
--

INSERT INTO `attendant` (`User_ID`, `Specialization`, `Qualification`, `Experience`, `Availability`, `Average_rating`, `Verification_status`) VALUES
(1, 'Elderly Care', 'Diploma in Nursing', 5, 'available', 4.5, 'verified'),
(3, 'Disability Support', 'Certified Caregiver', 3, '', 4.2, 'pending'),
(5, 'Post-Surgery Assistance', 'Bachelor of Nursing', 4, '', 4.7, 'verified');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `Log_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Action` text NOT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_request`
--

CREATE TABLE `emergency_request` (
  `Request_ID` int(11) NOT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Attendant_ID` int(11) DEFAULT NULL,
  `Request_time` datetime DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Status` enum('pending','resolved') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `Feedback_ID` int(11) NOT NULL,
  `Reviewer_ID` int(11) DEFAULT NULL,
  `Reviewee_ID` int(11) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` between 1 and 5),
  `Comment` text DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_application`
--

CREATE TABLE `job_application` (
  `Application_ID` int(11) NOT NULL,
  `Job_ID` int(11) DEFAULT NULL,
  `Attendant_ID` int(11) DEFAULT NULL,
  `Application_Date` date DEFAULT NULL,
  `Status` enum('pending','accepted','rejected') DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_application`
--

INSERT INTO `job_application` (`Application_ID`, `Job_ID`, `Attendant_ID`, `Application_Date`, `Status`, `Created_at`) VALUES
(1, 1, 1, '2025-04-28', 'pending', '2025-04-28 03:59:02'),
(2, 2, 3, '2025-04-28', 'accepted', '2025-04-28 03:59:02'),
(3, 3, 5, '2025-04-28', 'rejected', '2025-04-28 03:59:02');

-- --------------------------------------------------------

--
-- Table structure for table `job_posting`
--

CREATE TABLE `job_posting` (
  `Job_ID` int(11) NOT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Job_title` varchar(100) DEFAULT NULL,
  `Job_description` text DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Start_date` date DEFAULT NULL,
  `End_date` date DEFAULT NULL,
  `Status` enum('open','closed','in progress') DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_posting`
--

INSERT INTO `job_posting` (`Job_ID`, `Patient_ID`, `Job_title`, `Job_description`, `Location`, `Start_date`, `End_date`, `Status`, `Created_at`) VALUES
(1, 6, 'Elderly Caregiver Needed', 'Looking for a full-time caregiver to assist with daily activities and medication management.', 'Dhaka, Bangladesh', '2025-05-01', '2025-08-01', 'open', '2025-04-28 03:58:03'),
(2, 7, 'Diabetes Care Attendant', 'Seeking an attendant experienced in diabetes management for home visits.', 'Chittagong, Bangladesh', '2025-05-10', '2025-09-10', 'open', '2025-04-28 03:58:03'),
(3, 8, 'Post-Surgery Support', 'Need a caregiver for a recovering patient after surgery, light duties and support.', 'Sylhet, Bangladesh', '2025-05-05', '2025-07-05', 'open', '2025-04-28 03:58:03'),
(4, 6, 'diabetic care', 'lorem ipsum', 'Tongir chipa', '2025-05-04', '2025-05-31', 'open', '2025-05-03 05:32:07'),
(5, 12, 'Physiotherapy', 'asskfhj', 'dmd', '2025-05-13', '2025-05-31', 'open', '2025-05-03 05:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `History_ID` int(11) NOT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Condition` text DEFAULT NULL,
  `Medication` text DEFAULT NULL,
  `Allergies` text DEFAULT NULL,
  `Updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blood_group` varchar(5) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_history`
--

INSERT INTO `medical_history` (`History_ID`, `Patient_ID`, `Condition`, `Medication`, `Allergies`, `Updated_at`, `blood_group`, `height`, `weight`, `emergency_contact`, `additional_notes`) VALUES
(1, 6, 'Hypertension', 'Amlodipine', 'None', '2025-04-28 04:06:36', NULL, NULL, NULL, NULL, NULL),
(2, 7, 'Diabetes', 'Insulin', 'None', '2025-04-28 04:06:36', NULL, NULL, NULL, NULL, NULL),
(3, 8, 'Post-surgery Recovery', 'Painkillers', 'Aspirin', '2025-04-28 04:06:36', NULL, NULL, NULL, NULL, NULL),
(4, 6, 'Hypertension', 'Amlodipine', 'None', '2025-05-03 06:33:12', 'A+', 0.02, 0.00, '1312423', 'gvncgn'),
(5, 6, 'Hypertension', 'Amlodipine', 'many', '2025-05-03 06:33:46', 'A+', 0.24, 1.00, '1312423', 'ssfsdsdg'),
(6, 6, 'Hypertension', 'Amlodipine', 'many', '2025-05-03 06:33:50', 'A+', 0.24, 1.00, '1312423', 'ssfsdsdg');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `Message_ID` int(11) NOT NULL,
  `Sender_ID` int(11) DEFAULT NULL,
  `Receiver_ID` int(11) DEFAULT NULL,
  `Message` text NOT NULL,
  `Status` enum('read','unread') DEFAULT 'unread',
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`Message_ID`, `Sender_ID`, `Receiver_ID`, `Message`, `Status`, `Created_at`) VALUES
(1, 9, 6, 'Hello, please check the new job posting for elderly care.', 'read', '2025-04-28 04:05:40'),
(2, 10, 7, 'Your application has been accepted!', 'read', '2025-04-28 04:05:40'),
(3, 6, 9, 'I have a question about the job details.', 'read', '2025-04-28 04:05:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Message` text NOT NULL,
  `Type` enum('booking','emergency','payment','general') DEFAULT 'general',
  `Status` enum('unread','read') DEFAULT 'unread',
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`Notification_ID`, `User_ID`, `Message`, `Type`, `Status`, `Created_at`) VALUES
(1, 6, 'New job posting for elderly care is available.', '', 'unread', '2025-04-28 04:05:51'),
(2, 7, 'Your application has been accepted.', '', 'unread', '2025-04-28 04:05:51'),
(3, 9, 'New message from patient regarding job.', '', 'read', '2025-04-28 04:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `User_ID` int(11) NOT NULL,
  `Age` int(11) DEFAULT NULL,
  `Medical_condition` text DEFAULT NULL,
  `Emergency_contact` varchar(20) DEFAULT NULL,
  `Verification_status` enum('pending','verified','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`User_ID`, `Age`, `Medical_condition`, `Emergency_contact`, `Verification_status`) VALUES
(6, 65, 'Hypertension', '01888889999', 'verified'),
(7, 45, 'Diabetes', '01777778888', 'pending'),
(8, 30, 'Post-surgical recovery', '01999990000', 'verified'),
(12, NULL, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Job_ID` int(11) DEFAULT NULL,
  `Payer_ID` int(11) DEFAULT NULL,
  `Receiver_ID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Payment_status` enum('paid','unpaid') DEFAULT NULL,
  `Payment_method` enum('bkash','nagad','bank transfer','cash') DEFAULT NULL,
  `bkash_number` varchar(20) DEFAULT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Job_ID`, `Payer_ID`, `Receiver_ID`, `Amount`, `Date`, `Payment_status`, `Payment_method`, `bkash_number`, `transaction_id`, `Created_at`) VALUES
(1, 1, 6, 1, 5000.00, '2025-04-28', '', 'bkash', '01727272350', 'T123456789', '2025-04-28 04:06:01'),
(2, 2, 7, 3, 4500.00, '2025-04-28', '', 'bkash', '01812345678', 'T987654321', '2025-04-28 04:06:01'),
(3, 3, 8, 5, 6000.00, '2025-04-28', '', 'bkash', '01733334444', 'T112233445', '2025-04-28 04:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `Schedule_ID` int(11) NOT NULL,
  `Attendant_ID` int(11) DEFAULT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Start_time` datetime DEFAULT NULL,
  `End_time` datetime DEFAULT NULL,
  `Status` enum('pending','confirmed','completed','canceled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`Schedule_ID`, `Attendant_ID`, `Patient_ID`, `Start_time`, `End_time`, `Status`) VALUES
(1, 1, 6, '2025-05-01 08:00:00', '2025-05-01 16:00:00', ''),
(2, 3, 7, '2025-05-02 09:00:00', '2025-05-02 17:00:00', ''),
(3, 5, 8, '2025-05-03 07:00:00', '2025-05-03 15:00:00', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Birth_date` date DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `Role` enum('attendant','patient','admin') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Name`, `Username`, `Birth_date`, `Email`, `Password`, `Phone`, `Verification_status`, `Role`) VALUES
(1, 'Md. Rahim Uddin', 'rahim.bd', '1980-02-15', 'rahim.bd@gmail.com', '1234', '01727272350', 'verified', 'attendant'),
(2, 'Sumaiya Akter', 'sumaiya.akter', '1995-06-22', 'sumaiya.akter@gmail.com', 'abcd', '01812345678', 'verified', 'attendant'),
(3, 'Hasibul Islam', 'hasibul.islam', '1988-11-30', 'hasibul88@gmail.com', 'pass123', '01987654321', 'pending', 'attendant'),
(4, 'Rafiq Hasan', 'rafiq.hasan', '1975-03-10', 'rafiq1975@gmail.com', 'mypassword', '01611112222', 'verified', 'patient'),
(5, 'Sharmin Jahan', 'sharmin.jahan', '1992-09-05', 'sharmin.jahan92@gmail.com', 'securepwd', '01733334444', 'pending', 'attendant'),
(6, 'Kamal Hossain', 'kamal.h', '1959-01-10', 'kamal.h@gmail.com', 'pass6', '01710001111', 'verified', 'patient'),
(7, 'Farhana Yeasmin', 'farhana.y', '1979-05-15', 'farhana.y@gmail.com', 'pass7', '01820002222', 'pending', 'patient'),
(8, 'Naimur Rahman', 'naimur.r', '1995-07-20', 'naimur.r@gmail.com', 'pass8', '01930003333', 'verified', 'patient'),
(9, 'Admin One', 'admin1', '1990-05-10', 'admin1@example.com', 'adminpass1', '01799998888', 'verified', 'admin'),
(10, 'Admin Two', 'admin2', '1992-08-20', 'admin2@example.com', 'adminpass2', '01888887777', 'verified', 'admin'),
(12, 'Nuzhat Shreya', 'shreyayaay', '2021-01-02', 'shreyayaay4@gmail.com', '1234', '01841550208', 'pending', 'patient');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `attendant`
--
ALTER TABLE `attendant`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`);

--
-- Indexes for table `emergency_request`
--
ALTER TABLE `emergency_request`
  ADD PRIMARY KEY (`Request_ID`),
  ADD KEY `Patient_ID` (`Patient_ID`),
  ADD KEY `Attendant_ID` (`Attendant_ID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`Feedback_ID`),
  ADD KEY `Reviewer_ID` (`Reviewer_ID`),
  ADD KEY `Reviewee_ID` (`Reviewee_ID`);

--
-- Indexes for table `job_application`
--
ALTER TABLE `job_application`
  ADD PRIMARY KEY (`Application_ID`),
  ADD KEY `Job_ID` (`Job_ID`),
  ADD KEY `Attendant_ID` (`Attendant_ID`);

--
-- Indexes for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD PRIMARY KEY (`Job_ID`),
  ADD KEY `Patient_ID` (`Patient_ID`),
  ADD KEY `idx_status` (`Status`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`History_ID`),
  ADD KEY `Patient_ID` (`Patient_ID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`Message_ID`),
  ADD KEY `Sender_ID` (`Sender_ID`),
  ADD KEY `Receiver_ID` (`Receiver_ID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Job_ID` (`Job_ID`),
  ADD KEY `Payer_ID` (`Payer_ID`),
  ADD KEY `Receiver_ID` (`Receiver_ID`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`Schedule_ID`),
  ADD KEY `Attendant_ID` (`Attendant_ID`),
  ADD KEY `Patient_ID` (`Patient_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_request`
--
ALTER TABLE `emergency_request`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `Feedback_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_application`
--
ALTER TABLE `job_application`
  MODIFY `Application_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `job_posting`
--
ALTER TABLE `job_posting`
  MODIFY `Job_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `History_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `Message_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `attendant`
--
ALTER TABLE `attendant`
  ADD CONSTRAINT `attendant_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`User_ID`);

--
-- Constraints for table `emergency_request`
--
ALTER TABLE `emergency_request`
  ADD CONSTRAINT `emergency_request_ibfk_1` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`User_ID`),
  ADD CONSTRAINT `emergency_request_ibfk_2` FOREIGN KEY (`Attendant_ID`) REFERENCES `attendant` (`User_ID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`Reviewer_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`Reviewee_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `job_application`
--
ALTER TABLE `job_application`
  ADD CONSTRAINT `job_application_ibfk_1` FOREIGN KEY (`Job_ID`) REFERENCES `job_posting` (`Job_ID`),
  ADD CONSTRAINT `job_application_ibfk_2` FOREIGN KEY (`Attendant_ID`) REFERENCES `attendant` (`User_ID`);

--
-- Constraints for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD CONSTRAINT `job_posting_ibfk_1` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`User_ID`);

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`User_ID`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`Sender_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`Receiver_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Job_ID`) REFERENCES `job_posting` (`Job_ID`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`Payer_ID`) REFERENCES `users` (`User_ID`),
  ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`Receiver_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`Attendant_ID`) REFERENCES `attendant` (`User_ID`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
