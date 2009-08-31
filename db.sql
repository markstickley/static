-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 31, 2009 at 10:15 PM
-- Server version: 5.0.45
-- PHP Version: 5.3.0
-- 
-- Database: `qk_assets`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `files`
-- 

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text collate utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `type` text collate utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL COMMENT 'in bytes',
  `visibility` enum('public','private') collate utf8_unicode_ci NOT NULL,
  `filename` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `files`
-- 

INSERT INTO `files` (`id`, `name`, `parent`, `created`, `modified`, `type`, `size`, `visibility`, `filename`) VALUES (4, 'battlechart.png', 15, '2009-08-31 01:14:33', '2009-08-31 01:14:33', 'image/png', 7589, 'private', 'battlechart.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `folders`
-- 

DROP TABLE IF EXISTS `folders`;
CREATE TABLE `folders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text collate utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `visibility` enum('public','private') collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=19 ;

-- 
-- Dumping data for table `folders`
-- 

INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (1, 'path', 0, '2009-07-29 23:34:20', '2009-07-29 23:34:20', 'public');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (12, 'to', 1, '2009-08-30 01:29:00', '2009-08-30 01:29:00', 'private');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (13, 'folder', 12, '2009-08-30 01:30:23', '2009-08-30 01:30:23', 'private');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (14, 'test', 12, '2009-08-30 01:31:10', '2009-08-30 01:31:10', 'private');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (15, 'whatever', 12, '2009-08-30 01:32:01', '2009-08-30 01:32:01', 'private');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (17, 'from', 1, '2009-08-30 17:53:26', '2009-08-30 17:53:26', 'private');
INSERT INTO `folders` (`id`, `name`, `parent`, `created`, `modified`, `visibility`) VALUES (18, 'heck', 17, '2009-08-30 17:53:38', '2009-08-30 17:53:38', 'private');
