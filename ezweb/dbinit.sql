CREATE TABLE `automat` (
  `id` int(11) NOT NULL auto_increment,
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `days` varchar(255) NOT NULL,
  `cid` int(11) NOT NULL,
  `lastexec` smallint(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `collections` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `directory` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `files` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `bitrate` smallint(6) NOT NULL,
  `filemtime` bigint(20) NOT NULL,
  `length` varchar(8) NOT NULL,
  `id3_artist` varchar(128) NOT NULL,
  `id3_title` varchar(128) NOT NULL,
  `id3_album` varchar(128) NOT NULL,
  `id3_comment` varchar(255) NOT NULL,
  `lastplayed` bigint(20) NOT NULL,
  `edited` bigint(20) NOT NULL,
  `added` bigint(20) NOT NULL,
  `collection` int(11) NOT NULL,
  `weight` tinyint(4) NOT NULL default '5',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `history` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `fid` int(11) NOT NULL,
  `played` bigint(20) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `queue` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `fid` int(11) NOT NULL,
  `pos` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

