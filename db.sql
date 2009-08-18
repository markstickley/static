-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 18, 2009 at 02:26 PM
-- Server version: 5.1.32
-- PHP Version: 5.2.8
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `files`
-- 

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL COMMENT 'in bytes',
  `visibility` enum('public','private') COLLATE utf8_unicode_ci NOT NULL,
  `filename` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `files`
-- 

INSERT INTO `files` (`id`, `name`, `parent`, `created`, `modified`, `type`, `size`, `visibility`, `filename`) VALUES (1, 'file.jpg', 2, '2009-07-29 23:36:14', '2009-07-29 23:36:14', 'image/jpeg', 4000, 'public', 'test.jpg');

-- --------------------------------------------------------

-- 
-- Table structure for table `folders`
-- 

DROP TABLE IF EXISTS `folders`;
CREATE TABLE `folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `visibility` enum('public','private') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `folders`
-- 

INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (1, 'path', 0, '2009-07-29 23:34:20', '2009-07-29 23:34:20', 'public');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (2, 'to', 1, '2009-07-29 23:34:20', '2009-07-29 23:34:20', 'public');
