-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2023 at 05:57 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aar9speed`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `CATEGORY_NAME` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CATEGORY_ID`, `CATEGORY_NAME`) VALUES
(1, 'Parts'),
(2, 'Apparel'),
(3, 'Service'),
(4, 'Riding gear'),
(5, 'Tires & wheels'),
(6, 'Electronics');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CUS_ID` int(11) NOT NULL,
  `CUS_FNAME` varchar(20) DEFAULT NULL,
  `CUS_LNAME` varchar(20) DEFAULT NULL,
  `CUS_ADDRESS` varchar(50) DEFAULT NULL,
  `CUS_EMAIL` varchar(50) DEFAULT NULL,
  `CUS_PHONENUM` varchar(20) DEFAULT NULL,
  `CUS_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `CUS_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CUS_ID`, `CUS_FNAME`, `CUS_LNAME`, `CUS_ADDRESS`, `CUS_EMAIL`, `CUS_PHONENUM`, `CUS_CREATED_AT`, `CUS_UPDATED_AT`) VALUES
(1, 'WALK IN', NULL, NULL, NULL, NULL, '2023-01-17 19:24:18', '2023-01-17 19:24:18'),
(2, 'John', 'Smith', '1234 Elm Street, Manila', 'johnsmith@email.com', '09123456789', '2023-01-17 19:32:12', '2020-12-31 16:00:00'),
(4, 'Mark', 'Williams', '1234 Pine Road, Davao', 'markwilliams@email.com', '9087654321', '2021-02-28 16:00:00', '2021-02-28 16:00:00'),
(5, 'Jessica', 'Jones', '9101 Cedar Blvd, Quezon City', 'jessicajones@email.com', 'N/A', '2021-03-31 16:00:00', '2021-03-31 16:00:00'),
(6, 'Robert', 'Brown', '1234 Maple St, Bacolod', 'robertbrown@email.com', '9177654321', '2021-04-30 16:00:00', '2021-04-30 16:00:00'),
(7, 'Amanda', 'Miller', '5678 Birch Ave, Cagayan de Oro', 'amandamiller@email.com', '9166543210', '2021-05-31 16:00:00', '2021-05-31 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `ITEM_ID` int(11) NOT NULL,
  `ITEM_NAME` varchar(20) NOT NULL,
  `ITEM_DESC` varchar(500) DEFAULT NULL,
  `ITEM_PRICE` double(9,2) DEFAULT NULL,
  `ITEM_QUANTITY` int(11) NOT NULL,
  `CATEGORY_ID` int(11) DEFAULT NULL,
  `SUPPLIER_ID` int(11) DEFAULT NULL,
  `ITEM_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `ITEM_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `ITEM_CODE` int(11) NOT NULL,
  `ITEM_IMAGE` varchar(500) DEFAULT NULL,
  `ITEM_STATUS` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`ITEM_ID`, `ITEM_NAME`, `ITEM_DESC`, `ITEM_PRICE`, `ITEM_QUANTITY`, `CATEGORY_ID`, `SUPPLIER_ID`, `ITEM_CREATED_AT`, `ITEM_UPDATED_AT`, `ITEM_CODE`, `ITEM_IMAGE`, `ITEM_STATUS`) VALUES
(1, 'OIL FILTER', 'K&N Engineering oil filters are designed to increa', 500.00, 2, 1, 3, '2023-01-17 19:03:28', '2023-01-18 14:21:37', 202301, 'images/1Ep2aYq2/oilfilter.jpg', 'ACTIVE'),
(2, 'Air filters', 'Dainese air filters are designed to increase horsepower and acceleration while providing excellent air filtration. These filters are washable and reusable and come with a million mile limited warranty.', 1000.00, 2, 1, 1, '2023-01-17 19:06:05', '2023-01-18 13:51:03', 202302, 'images/PH2Arp6n/Air filters.jpg', 'ACTIVE'),
(3, 'Spark Plug', 'Mobil1 spark plugs are designed to increase horsepower and acceleration while providing excellent ignition. These spark plugs are designed to last long and come with a million mile limited warranty.', 750.00, 1, 1, 2, '2023-01-17 19:07:32', '2023-01-18 13:53:10', 202303, 'images/xBHiZoJ1/sparkplugs.png', 'ACTIVE'),
(4, 'EVO Helmet', 'The K&N Engineering helmet is designed for maximum protection and comfort. The helmet features advanced ventilation and aerodynamics, a comfortable interior, and a sleek design.', 10000.00, 3, 4, 3, '2023-01-17 19:09:08', '2023-01-18 14:22:49', 202304, 'images/aPyb8YJw/EVO.jpg', 'ACTIVE'),
(5, 'Jacket', 'The Dainese jacket is designed for maximum protection and comfort. The jacket features advanced ventilation, a comfortable interior and a sleek design.', 25000.00, 1, 2, 1, '2023-01-17 19:10:44', '2023-01-18 14:22:58', 202305, 'images/fwL7A9y2/jacket.jpg', 'ACTIVE'),
(6, 'Gloves', 'The Mobil1 gloves are designed for maximum protection and comfort. The gloves feature advanced ventilation, a comfortable interior and a sleek design.', 5000.00, 151, 2, 2, '2023-01-17 19:12:06', '2023-01-18 14:23:05', 202306, 'images/7CsLCQDK/Gloves.jpg', 'ACTIVE'),
(7, 'Change Oil', 'Oil change service includes the use of high-quality synthetic oil and a new oil filter. It also includes a comprehensive inspection of the motorcycle to ensure that it is running at its best.', 500.00, 9999999, 3, 4, '2023-01-17 19:14:42', '2023-01-18 14:23:34', 202307, 'images/WFgHH2E3/oilchange.jpg', 'ACTIVE'),
(8, 'Tire Replacement', 'tire replacement service includes the use of high-quality tires and a comprehensive inspection of the motorcycle\'s suspension and alignment.', 200.00, 9999999, 3, 4, '2023-01-17 19:15:58', '2023-01-18 14:25:39', 202310, 'images/w1qDqyeG/TireReplacement.jpg', 'ACTIVE'),
(9, 'Brake repair', 'brake repair service includes the use of high-quality brake pads and a comprehensive inspection of the motorcycle\'s braking system.', 1200.00, 252, 3, 4, '2023-01-17 19:17:04', '2023-01-18 14:24:03', 202308, 'images/547Fvo1O/brakerepair.jpg', 'ACTIVE'),
(10, 'Metzeler Tire', 'Metzeler tires are designed for maximum performance and durability. These tires offer excellent grip and handling in both wet and dry conditions and are suitable for sports and touring bikes.', 3040.00, 654, 5, 2, '2023-01-17 19:23:38', '2023-01-18 14:24:13', 202309, 'images/dMpk4heF/Metzeler.png', 'ACTIVE');

--
-- Triggers `item`
--
DELIMITER $$
CREATE TRIGGER `set_inactive_status` BEFORE UPDATE ON `item` FOR EACH ROW BEGIN
    IF NEW.ITEM_QUANTITY = 0 THEN
        SET NEW.ITEM_STATUS = 'INACTIVE';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `Noti_id` int(11) NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `message` varchar(500) DEFAULT NULL,
  `date_in` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`Noti_id`, `subject`, `message`, `date_in`) VALUES
(1, 'OIL FILTER', 'is successfully added recently!', '2023-01-17 19:03:28'),
(2, 'Air filters', 'is successfully added recently!', '2023-01-17 19:06:05'),
(3, 'Spark Plug', 'is successfully added recently!', '2023-01-17 19:07:32'),
(4, 'EVO Helmet', 'is successfully added recently!', '2023-01-17 19:09:08'),
(5, 'Jacket', 'is successfully added recently!', '2023-01-17 19:10:44'),
(6, 'Gloves', 'is successfully added recently!', '2023-01-17 19:12:06'),
(7, 'Change Oil', 'is successfully added recently!', '2023-01-17 19:14:42'),
(8, 'Tire Replacement', 'is successfully added recently!', '2023-01-17 19:15:58'),
(9, 'Brake repair', 'is successfully added recently!', '2023-01-17 19:17:04'),
(10, 'Metzeler Tire', 'is successfully added recently!', '2023-01-17 19:23:38'),
(11, 'OIL FILTER', 'is almost depleted!', '2023-01-18 14:31:03'),
(12, 'OIL FILTER', 'is almost depleted!', '2023-01-18 16:51:54'),
(13, 'OIL FILTER', 'is almost depleted!', '2023-01-18 16:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `PORD_ID` int(11) NOT NULL,
  `ITEM_ID` int(11) DEFAULT NULL,
  `STAFF_ID` int(11) NOT NULL,
  `PORD_QUANTITY` int(11) NOT NULL,
  `PORD_STATUS` varchar(20) DEFAULT NULL,
  `PORD_DATE` timestamp NULL DEFAULT current_timestamp(),
  `PORD_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `PORD_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`PORD_ID`, `ITEM_ID`, `STAFF_ID`, `PORD_QUANTITY`, `PORD_STATUS`, `PORD_DATE`, `PORD_CREATED_AT`, `PORD_UPDATED_AT`) VALUES
(1, 2, 1, 29, 'SUCCESS', '2023-01-17 19:52:39', '2023-01-17 19:52:39', '2023-01-17 19:52:39'),
(2, 3, 1, 65, 'SUCCESS', '2023-01-17 19:52:52', '2023-01-17 19:52:52', '2023-01-17 19:52:52'),
(3, 5, 1, 56, 'SUCCESS', '2023-01-18 13:54:23', '2023-01-18 13:54:23', '2023-01-18 13:54:23'),
(4, 6, 1, 151, 'SUCCESS', '2023-01-18 13:54:33', '2023-01-18 13:54:33', '2023-01-18 13:54:33'),
(5, 10, 1, 654, 'SUCCESS', '2023-01-18 13:54:44', '2023-01-18 13:54:44', '2023-01-18 13:54:44'),
(6, 1, 1, 60, 'SUCCESS', '2023-01-18 13:54:55', '2023-01-18 13:54:55', '2023-01-18 13:54:55'),
(7, 4, 1, 90, 'SUCCESS', '2023-01-18 13:55:07', '2023-01-18 13:55:07', '2023-01-18 13:55:07'),
(8, 7, 1, 9999999, 'SUCCESS', '2023-01-18 13:55:26', '2023-01-18 13:55:26', '2023-01-18 13:55:26'),
(9, 8, 1, 9999999, 'SUCCESS', '2023-01-18 13:55:36', '2023-01-18 13:55:36', '2023-01-18 13:55:36'),
(10, 9, 1, 252, 'SUCCESS', '2023-01-18 13:55:46', '2023-01-18 13:55:46', '2023-01-18 13:55:46');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `roles` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `roles`) VALUES
(1, 'ADMIN'),
(2, 'STAFF');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `STAFF_ID` int(11) NOT NULL,
  `STAFF_USER` varchar(20) NOT NULL,
  `staff_pass` varchar(50) DEFAULT NULL,
  `STAFF_FNAME` varchar(20) DEFAULT NULL,
  `STAFF_LNAME` varchar(20) DEFAULT NULL,
  `STAFF_ADDRESS` varchar(50) DEFAULT NULL,
  `STAFF_EMAIL` varchar(20) DEFAULT NULL,
  `STAFF_PHONENUM` varchar(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `STAFF_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `STAFF_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`STAFF_ID`, `STAFF_USER`, `staff_pass`, `STAFF_FNAME`, `STAFF_LNAME`, `STAFF_ADDRESS`, `STAFF_EMAIL`, `STAFF_PHONENUM`, `role_id`, `STAFF_CREATED_AT`, `STAFF_UPDATED_AT`) VALUES
(1, 'admin', 'oten', '-', '-', '-', '-@gmail.com', '0', 1, '2023-01-18 14:45:21', '2023-01-18 14:45:21');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `SUPPLIER_ID` int(11) NOT NULL,
  `SUPPLIER_FNAME` varchar(20) DEFAULT NULL,
  `SUPPLIER_LNAME` varchar(20) DEFAULT NULL,
  `SUPPLIER_ADDRESS` varchar(50) DEFAULT NULL,
  `SUPPLIER_PHONENUM` varchar(20) DEFAULT NULL,
  `SUPPLIER_COMPANY` varchar(250) DEFAULT NULL,
  `SUPPLIER_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `SUPPLIER_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`SUPPLIER_ID`, `SUPPLIER_FNAME`, `SUPPLIER_LNAME`, `SUPPLIER_ADDRESS`, `SUPPLIER_PHONENUM`, `SUPPLIER_COMPANY`, `SUPPLIER_CREATED_AT`, `SUPPLIER_UPDATED_AT`) VALUES
(1, 'Maderia', 'Blindslac', 'Manila, Philippines', '09123456789', 'Dainese', '2023-01-17 18:49:21', '2023-01-17 18:54:47'),
(2, 'Jezuzireel', 'Buselbow', 'Cebu City, Philippines', '09987654321', 'Mobil1', '2023-01-17 18:52:00', '2023-01-17 18:55:04'),
(3, 'Kurisu', 'Manforest', 'Cordova, Cebu', '09321654987', 'K&N Engineering', '2023-01-17 18:53:01', '2023-01-17 18:54:56'),
(4, '-', '-', 'Cebu City, Philippines', '9311258458', 'AAR9SPEED', '2023-01-17 19:13:08', '2023-01-17 19:13:08');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `TRANS_ID` int(11) NOT NULL,
  `ITEM_ID` int(11) NOT NULL,
  `CUS_ID` int(11) NOT NULL,
  `STAFF_ID` int(11) NOT NULL,
  `trans_code` varchar(50) DEFAULT NULL,
  `TRANS_QUANTITY` int(11) NOT NULL,
  `TRANS_CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `TRANS_UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `TRANS_PRICE` double(9,2) DEFAULT NULL,
  `TRANS_PAYMENT` double(9,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`TRANS_ID`, `ITEM_ID`, `CUS_ID`, `STAFF_ID`, `trans_code`, `TRANS_QUANTITY`, `TRANS_CREATED_AT`, `TRANS_UPDATED_AT`, `TRANS_PRICE`, `TRANS_PAYMENT`) VALUES
(1, 3, 6, 1, '30118035158', 2, '2023-01-17 19:53:29', '2023-01-17 19:53:29', 1500.00, 5000.00),
(2, 2, 6, 1, '30118035158', 1, '2023-01-17 19:53:29', '2023-01-17 19:53:29', 1000.00, 5000.00),
(3, 2, 1, 1, '30118035538', 1, '2023-01-17 19:55:47', '2023-01-17 19:55:47', 1000.00, 25555.00),
(4, 2, 1, 1, '30118035712', 1, '2023-01-17 19:57:49', '2023-01-17 19:57:49', 1000.00, 5000.00),
(5, 2, 1, 1, '30118040107', 1, '2023-01-17 20:01:35', '2023-01-17 20:01:35', 1000.00, 2220.00),
(6, 2, 1, 1, '30118040302', 1, '2023-01-17 20:03:10', '2023-01-17 20:03:10', 1000.00, 2422.00),
(7, 2, 1, 1, '30118221256', 22, '2023-01-18 14:13:29', '2023-01-18 14:13:29', 22000.00, 22003.00),
(8, 1, 1, 1, '30118221449', 58, '2023-01-18 14:15:08', '2023-01-18 14:15:08', 29000.00, 30000.00),
(9, 5, 1, 1, '30118221603', 54, '2023-01-18 14:16:29', '2023-01-18 14:16:29', 1350000.00, 2000000.00),
(10, 5, 1, 1, '30118222612', 1, '2023-01-18 14:26:23', '2023-01-18 14:26:23', 25000.00, 30000.00),
(11, 3, 1, 1, '30118222728', 62, '2023-01-18 14:27:50', '2023-01-18 14:27:50', 46500.00, 90000.00),
(12, 4, 1, 1, '30118222945', 87, '2023-01-18 14:30:00', '2023-01-18 14:30:00', 870000.00, 1000000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CATEGORY_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CUS_ID`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`ITEM_ID`),
  ADD KEY `CATEGORY_ID` (`CATEGORY_ID`),
  ADD KEY `SUPPLIER_ID` (`SUPPLIER_ID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`Noti_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`PORD_ID`),
  ADD KEY `STAFF_ID` (`STAFF_ID`),
  ADD KEY `ITEM_ID` (`ITEM_ID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`STAFF_ID`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`SUPPLIER_ID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`TRANS_ID`),
  ADD KEY `ITEM_ID` (`ITEM_ID`),
  ADD KEY `CUS_ID` (`CUS_ID`),
  ADD KEY `STAFF_ID` (`STAFF_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CUS_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `ITEM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `Noti_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `PORD_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `STAFF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `SUPPLIER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `TRANS_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`CATEGORY_ID`) REFERENCES `category` (`CATEGORY_ID`),
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`SUPPLIER_ID`) REFERENCES `supplier` (`SUPPLIER_ID`);

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`STAFF_ID`) REFERENCES `staff` (`STAFF_ID`),
  ADD CONSTRAINT `purchase_order_ibfk_3` FOREIGN KEY (`ITEM_ID`) REFERENCES `item` (`ITEM_ID`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`ITEM_ID`) REFERENCES `item` (`ITEM_ID`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`CUS_ID`) REFERENCES `customer` (`CUS_ID`),
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`STAFF_ID`) REFERENCES `staff` (`STAFF_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
