-- phpMyAdmin SQL Dump
-- version 2.10.3deb1ubuntu0.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 09, 2008 at 08:08 AM
-- Server version: 5.0.45
-- PHP Version: 5.2.3-1ubuntu6.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `simpl_example`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `author`
-- 

DROP TABLE IF EXISTS `author`;
CREATE TABLE IF NOT EXISTS `author` (
  `author_id` int(10) unsigned NOT NULL auto_increment,
  `date_entered` datetime NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `email` varchar(48) NOT NULL,
  PRIMARY KEY  (`author_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used to keep track of all the blog authors';

-- --------------------------------------------------------

-- 
-- Table structure for table `post`
-- 

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `status` enum('Draft','Published') NOT NULL,
  `display_order` int(5) unsigned NOT NULL,
  `date_entered` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  `author_id` int(10) unsigned NOT NULL default '0',
  `tags` varchar(254) default NULL,
  `title` varchar(48) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `display_order` (`display_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Used to keep track of all the blog posts';

-- --------------------------------------------------------

-- 
-- Table structure for table `post_tag`
-- 

DROP TABLE IF EXISTS `post_tag`;
CREATE TABLE IF NOT EXISTS `post_tag` (
  `post_tag_id` int(5) unsigned NOT NULL auto_increment,
  `post_id` int(5) unsigned NOT NULL,
  `tag_id` int(5) unsigned NOT NULL,
  PRIMARY KEY  (`post_tag_id`),
  KEY `post_id` (`post_id`,`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `session`
-- 

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `ses_id` varchar(32) NOT NULL,
  `last_access` int(12) unsigned NOT NULL,
  `ses_start` int(12) unsigned NOT NULL,
  `ses_value` text NOT NULL,
  PRIMARY KEY  (`ses_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Used to store the sessions data';

-- --------------------------------------------------------

-- 
-- Table structure for table `tag`
-- 

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` int(5) unsigned NOT NULL auto_increment,
  `tag` varchar(24) NOT NULL,
  PRIMARY KEY  (`tag_id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
