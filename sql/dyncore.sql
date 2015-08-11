-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 09, 2015 at 06:14 PM
-- Server version: 5.0.51b-community-nt-log
-- PHP Version: 5.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `userdata`
--

CREATE TABLE IF NOT EXISTS `userdata` (
  `user_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `type` varchar(8) default NULL,
  `value` text,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `userdata`
--

INSERT INTO `userdata` (`user_id`, `name`, `type`, `value`) VALUES
(4, 'province', NULL, 'provx'),
(4, 'address', NULL, 'adrre <html> </body>'),
(4, 'postal', NULL, '10550'),
(4, 'city', NULL, 'citi'),
(4, 'country', NULL, 'count');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `access_token_time` datetime default NULL,
  `access_token` varchar(96) default NULL,
  `user_type` varchar(64) NOT NULL,
  `user_title` varchar(255) NOT NULL,
  `user_email` varchar(255) default NULL,
  `user_avatar` varchar(255) default NULL,
  `user_about` varchar(255) default NULL,
  `created_time` datetime default NULL,
  `accessed_time` datetime default NULL,
  `updated_time` datetime default NULL,
  `user_permissions` text,
  `user_options` text,
  `user_status` int(11) default NULL,
  `first_name` varchar(128) default NULL,
  `mid_name` varchar(64) default NULL,
  `last_name` varchar(128) default NULL,
  `language` varchar(16) default NULL,
  `timezone` varchar(16) default NULL,
  `birthday` date default NULL,
  `flags` int(11) default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `access_token` (`access_token`),
  KEY `username` (`username`),
  KEY `user_id` (`user_id`),
  KEY `access_token_2` (`access_token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `access_token_time`, `access_token`, `user_type`, `user_title`, `user_email`, `user_avatar`, `user_about`, `created_time`, `accessed_time`, `updated_time`, `user_permissions`, `user_options`, `user_status`, `first_name`, `mid_name`, `last_name`, `language`, `timezone`, `birthday`, `flags`) VALUES
(1, 'a', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '0000-00-00 00:00:00', 'd994baaacb1d7851b921bf9ef6ed44d49879f2c9189cfdd150012d47912f324d', 'user', 'a', '0', NULL, '', '0000-00-00 00:00:00', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 'b', '71739a9a7aa0958bf43eddb283565697c112e6b618affcbc23081e9e62592ba8', NULL, '', 'staff', 'a', '0', NULL, '', '0000-00-00 00:00:00', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 'c', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', NULL, '', 'member', 'c', 'c@c.com', NULL, '', '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
