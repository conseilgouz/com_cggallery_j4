CREATE TABLE IF NOT EXISTS `#__cggallery_page` (`id` integer NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`state` integer NOT NULL default 0,`page_params` text NOT NULL,`slides` text NOT NULL ,`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',`created_by` int(10) unsigned NOT NULL DEFAULT '0',`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',`modified_by` int(10) unsigned NOT NULL DEFAULT '0',`checked_out` int(10) unsigned NOT NULL DEFAULT '0',`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',`language` char(7) NOT NULL DEFAULT '',PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='definition des sections';