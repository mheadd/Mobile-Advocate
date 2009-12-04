-- 
-- Table structure for table `calls`
-- 

DROP TABLE IF EXISTS `calls`;
CREATE TABLE `calls` (
  `id` int(10) NOT NULL auto_increment,
  `citizen_phone` char(10) NOT NULL,
  `call_datetime` datetime NOT NULL,
  `call_length` int(15) NOT NULL,
  `recorded_message` tinyint(1) NOT NULL,
  `map_call` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `citizen`
-- 

DROP TABLE IF EXISTS `citizen`;
CREATE TABLE `citizen` (
  `phone_number` char(10) NOT NULL,
  `name` varchar(75) NOT NULL,
  `address` varchar(125) NOT NULL,
  `lat` double NOT NULL,
  `long` double NOT NULL,
  `sen_firstName` varchar(45) NOT NULL,
  `sen_middleName` varchar(25) NOT NULL,
  `sen_lastName` varchar(95) NOT NULL,
  `sen_suffix` varchar(25) NOT NULL,
  `sen_district` varchar(2) NOT NULL,
  `sen_phone` varchar(15) NOT NULL,
  `sen_party` varchar(45) NOT NULL,
  `amb_firstName` varchar(45) NOT NULL,
  `amb_middleName` varchar(25) NOT NULL,
  `amb_lastName` varchar(95) NOT NULL,
  `amb_suffix` varchar(25) NOT NULL,
  `amb_district` varchar(3) NOT NULL,
  `amb_phone` varchar(15) NOT NULL,
  `amb_party` varchar(45) NOT NULL,
  `recruiter_id` varchar(50) NOT NULL,
  `step` int(1) NOT NULL,
  PRIMARY KEY  (`phone_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `recruiter`
-- 

DROP TABLE IF EXISTS `recruiter`;
CREATE TABLE `recruiter` (
  `id` varchar(50) NOT NULL,
  `name` varchar(75) NOT NULL,
  `phone_number` int(10) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;