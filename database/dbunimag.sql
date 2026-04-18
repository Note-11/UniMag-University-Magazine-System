-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 26, 2026 at 11:47 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbunimag`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblacademicyear`
--

CREATE TABLE `tblacademicyear` (
  `academicyearid` int(11) NOT NULL,
  `yearname` varchar(50) NOT NULL,
  `submission_closure_date` date NOT NULL,
  `final_closure_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblacademicyear`
--

INSERT INTO `tblacademicyear` (`academicyearid`, `yearname`, `submission_closure_date`, `final_closure_date`) VALUES
(1, 'Spring 2026', '2025-12-30', '2026-03-30'),
(2, 'Summer 2026', '2026-01-15', '2026-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `categoryid` int(11) NOT NULL,
  `academicyearid` int(11) NOT NULL,
  `categoryname` varchar(100) NOT NULL,
  `categorystartdate` date NOT NULL,
  `categoryclosuredate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`categoryid`, `academicyearid`, `categoryname`, `categorystartdate`, `categoryclosuredate`) VALUES
(1, 1, 'Magazine 1', '2026-01-01', '2026-03-09'),
(2, 1, 'Magazine 2', '2026-01-31', '2026-04-02'),
(3, 2, 'Magazine 3', '2026-02-15', '2026-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `tblcomment`
--

CREATE TABLE `tblcomment` (
  `commentid` int(11) NOT NULL,
  `contributionid` int(11) NOT NULL,
  `coordinatorid` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `comment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcomment`
--

INSERT INTO `tblcomment` (`commentid`, `contributionid`, `coordinatorid`, `comment_text`, `comment_date`) VALUES
(1, 2, 3, 'Good Idea!', '2026-03-26'),
(2, 1, 3, 'Well..', '2026-03-26');

-- --------------------------------------------------------

--
-- Table structure for table `tblcontribution`
--

CREATE TABLE `tblcontribution` (
  `contributionid` int(11) NOT NULL,
  `studentid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `submission_date` date NOT NULL,
  `status` enum('draft','submitted','selected','rejected') NOT NULL DEFAULT 'draft',
  `is_selected_for_publication` tinyint(1) DEFAULT 0,
  `selected_by` int(11) DEFAULT NULL,
  `selecteddate` date DEFAULT NULL,
  `filepath1` varchar(255) DEFAULT NULL,
  `filepath2` varchar(255) DEFAULT NULL,
  `filepath3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcontribution`
--

INSERT INTO `tblcontribution` (`contributionid`, `studentid`, `categoryid`, `title`, `description`, `submission_date`, `status`, `is_selected_for_publication`, `selected_by`, `selecteddate`, `filepath1`, `filepath2`, `filepath3`) VALUES
(1, 7, 1, 'BFF', 'Best Friends Forever :)', '2026-03-06', 'draft', 0, NULL, NULL, 'friends1.jpeg', 'friends2.jpeg', ''),
(2, 8, 1, 'My Friend', 'We are always friends...', '2026-03-06', 'selected', 1, 3, '2026-03-08', 'friends3.jpeg', 'friends_poem2.jpeg', ''),
(4, 9, 1, 'Guys', 'Happy Hours :D', '2026-03-07', 'draft', 0, NULL, NULL, 'friends3.jpeg', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `tblfaculty`
--

CREATE TABLE `tblfaculty` (
  `facultyid` int(11) NOT NULL,
  `facultyname` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblfaculty`
--

INSERT INTO `tblfaculty` (`facultyid`, `facultyname`, `description`) VALUES
(1, 'Computer Science', 'Science'),
(2, 'Computing', 'developing'),
(3, 'Information System', 'System Management'),
(4, 'Networking', 'Cyber and Connecting');

-- --------------------------------------------------------

--
-- Table structure for table `tblnotification_log`
--

CREATE TABLE `tblnotification_log` (
  `notificationid` int(11) NOT NULL,
  `contributionid` int(11) NOT NULL,
  `sent_to` int(11) NOT NULL,
  `sent_date` date NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblnotification_log`
--

INSERT INTO `tblnotification_log` (`notificationid`, `contributionid`, `sent_to`, `sent_date`, `status`) VALUES
(1, 2, 8, '2026-03-26', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `tblrole`
--

CREATE TABLE `tblrole` (
  `roleid` int(11) NOT NULL,
  `rolename` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblrole`
--

INSERT INTO `tblrole` (`roleid`, `rolename`) VALUES
(1, 'Adminstrator'),
(5, 'Guest'),
(3, 'Marketing Coordinator'),
(4, 'Marketing Manager'),
(2, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `tblterms_and_conditions`
--

CREATE TABLE `tblterms_and_conditions` (
  `agreementid` int(11) NOT NULL,
  `studentid` int(11) NOT NULL,
  `academicyearid` int(11) NOT NULL,
  `agreed_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblterms_and_conditions`
--

INSERT INTO `tblterms_and_conditions` (`agreementid`, `studentid`, `academicyearid`, `agreed_at`) VALUES
(1, 7, 1, '2026-03-26'),
(2, 8, 1, '2026-03-26'),
(3, 8, 1, '2026-03-26'),
(4, 9, 1, '2026-03-26');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `userid` int(11) NOT NULL,
  `facultyid` int(11) DEFAULT NULL,
  `roleid` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`userid`, `facultyid`, `roleid`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 1, 1, 'Administrator', 'admin@unimag.com', '$2y$10$y93NwnpkKSdmXMWaOlleBeJNkCeaGyzCQoOxgJsBF1BTuZlr1fWXK', '2026-03-26 00:00:00'),
(2, 1, 4, 'Aung Kaung Myat', 'akm@unimag.com', '$2y$10$po8R8Fvwyri6Iv0pciasUejojoZiiZ9eDyJDg1fQQDh3rzv2kj9IC', '2026-03-26 00:00:00'),
(3, 1, 3, 'Phyu', 'phyu@unimag.com', '$2y$10$1VElz9bThjpzSATU7edObO6q6jZXe0IyhgMoI0qO1lLGfUf0eS3r.', '2026-03-26 00:00:00'),
(4, 2, 3, 'Htar', 'htar@unimag.com', '$2y$10$jYwNGuLiHjHU.0b9b0Chd.Y2m6acAsz7LWM4oa2s5uGJdBfkOwPUC', '2026-03-26 00:00:00'),
(5, 3, 3, 'Moe', 'moe@unimag.com', '$2y$10$qncu1Rlg5Aw8QO2LAH347.1BBdgD44Sj4xcXPZR5nFa/tiKUlWzQK', '2026-03-26 00:00:00'),
(6, 4, 3, 'Wah', 'wah@unimag.com', '$2y$10$vS65QIZFUKCPJ0GQMwbJwO.23y6qMSuW3JPT65aEOuqlYsM6TZBci', '2026-03-26 00:00:00'),
(7, 1, 2, 'Su Su', 'su@unimag.com', '$2y$10$BS3Ge.2cdoeC5YI7E4n.xurEEBzEHlPN/hXLxAU1nkAiNS4apczKq', '2026-03-26 00:00:00'),
(8, 1, 2, 'Aye Aye', 'aye@unimag.com', '$2y$10$NUix/2kT6BA6uoRL1WzEjuRnZP.gnk7l22jC62lELE25ACuo1L5Ku', '2026-03-26 00:00:00'),
(9, 2, 2, 'Kyaw', 'kyaw@unimag.com', '$2y$10$J5xoEekbZM36JNs42yyeb.I7w2MqAfubQss/RBjww19UWmqCleFki', '2026-03-26 00:00:00'),
(10, 4, 2, 'Lin Lin', 'lin@unimag.cm', '$2y$10$ttamVUoaJYzOmRBey6VSFuBnN.ELNdzscKTQHcV.nQKPgXBKuKQoa', '2026-03-26 00:00:00'),
(11, 1, 5, 'Jack', 'jack@gmail.com', '$2y$10$liKL/a.csOFsnvEYuipBZuYFe/7SSrB7lwg9vWu1TLi/n2W9AUioC', '2026-03-26 00:00:00'),
(12, 2, 5, 'John', 'john@gmail.com', '$2y$10$zn0WvNNwmulV/fi9juroiuQi6Zb.zd5dTknpELMkc487zdUS4iMuK', '2026-03-26 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblacademicyear`
--
ALTER TABLE `tblacademicyear`
  ADD PRIMARY KEY (`academicyearid`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`categoryid`),
  ADD KEY `academicyearid` (`academicyearid`);

--
-- Indexes for table `tblcomment`
--
ALTER TABLE `tblcomment`
  ADD PRIMARY KEY (`commentid`),
  ADD KEY `contributionid` (`contributionid`),
  ADD KEY `coordinatorid` (`coordinatorid`);

--
-- Indexes for table `tblcontribution`
--
ALTER TABLE `tblcontribution`
  ADD PRIMARY KEY (`contributionid`),
  ADD KEY `studentid` (`studentid`),
  ADD KEY `categoryid` (`categoryid`),
  ADD KEY `selected_by` (`selected_by`);

--
-- Indexes for table `tblfaculty`
--
ALTER TABLE `tblfaculty`
  ADD PRIMARY KEY (`facultyid`);

--
-- Indexes for table `tblnotification_log`
--
ALTER TABLE `tblnotification_log`
  ADD PRIMARY KEY (`notificationid`),
  ADD KEY `contributionid` (`contributionid`),
  ADD KEY `sent_to` (`sent_to`);

--
-- Indexes for table `tblrole`
--
ALTER TABLE `tblrole`
  ADD PRIMARY KEY (`roleid`),
  ADD UNIQUE KEY `rolename` (`rolename`);

--
-- Indexes for table `tblterms_and_conditions`
--
ALTER TABLE `tblterms_and_conditions`
  ADD PRIMARY KEY (`agreementid`),
  ADD KEY `studentid` (`studentid`),
  ADD KEY `academicyearid` (`academicyearid`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblacademicyear`
--
ALTER TABLE `tblacademicyear`
  MODIFY `academicyearid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `categoryid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblcomment`
--
ALTER TABLE `tblcomment`
  MODIFY `commentid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblcontribution`
--
ALTER TABLE `tblcontribution`
  MODIFY `contributionid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblfaculty`
--
ALTER TABLE `tblfaculty`
  MODIFY `facultyid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblnotification_log`
--
ALTER TABLE `tblnotification_log`
  MODIFY `notificationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblrole`
--
ALTER TABLE `tblrole`
  MODIFY `roleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblterms_and_conditions`
--
ALTER TABLE `tblterms_and_conditions`
  MODIFY `agreementid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD CONSTRAINT `tblcategory_ibfk_1` FOREIGN KEY (`academicyearid`) REFERENCES `tblacademicyear` (`academicyearid`);

--
-- Constraints for table `tblcomment`
--
ALTER TABLE `tblcomment`
  ADD CONSTRAINT `tblcomment_ibfk_1` FOREIGN KEY (`contributionid`) REFERENCES `tblcontribution` (`contributionid`),
  ADD CONSTRAINT `tblcomment_ibfk_2` FOREIGN KEY (`coordinatorid`) REFERENCES `tbluser` (`userid`);

--
-- Constraints for table `tblcontribution`
--
ALTER TABLE `tblcontribution`
  ADD CONSTRAINT `tblcontribution_ibfk_1` FOREIGN KEY (`studentid`) REFERENCES `tbluser` (`userid`),
  ADD CONSTRAINT `tblcontribution_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `tblcategory` (`categoryid`),
  ADD CONSTRAINT `tblcontribution_ibfk_3` FOREIGN KEY (`selected_by`) REFERENCES `tbluser` (`userid`);

--
-- Constraints for table `tblnotification_log`
--
ALTER TABLE `tblnotification_log`
  ADD CONSTRAINT `tblnotification_log_ibfk_1` FOREIGN KEY (`contributionid`) REFERENCES `tblcontribution` (`contributionid`),
  ADD CONSTRAINT `tblnotification_log_ibfk_2` FOREIGN KEY (`sent_to`) REFERENCES `tbluser` (`userid`);

--
-- Constraints for table `tblterms_and_conditions`
--
ALTER TABLE `tblterms_and_conditions`
  ADD CONSTRAINT `tblterms_and_conditions_ibfk_1` FOREIGN KEY (`studentid`) REFERENCES `tbluser` (`userid`),
  ADD CONSTRAINT `tblterms_and_conditions_ibfk_2` FOREIGN KEY (`academicyearid`) REFERENCES `tblacademicyear` (`academicyearid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
