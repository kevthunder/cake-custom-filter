

-- --------------------------------------------------------

--
-- Structure de la table `custom_filters`
--

CREATE TABLE IF NOT EXISTS `custom_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_filter_group_id` int(11) DEFAULT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc` text COLLATE utf8_unicode_ci,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `editable` tinyint(1) NOT NULL DEFAULT '1',
  `deletable` tinyint(1) NOT NULL DEFAULT '1',
  `sql` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `joins` text COLLATE utf8_unicode_ci,
  `req_group` tinyint(1) DEFAULT NULL COMMENT 'If true, will add a group by on the primary key of the queried model',
  `advanced_opt` text COLLATE utf8_unicode_ci,
  `cond_count` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `custom_filter_conds`
--

CREATE TABLE IF NOT EXISTS `custom_filter_conds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_filter_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `op` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `not` tinyint(1) DEFAULT NULL,
  `val1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `val2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `advanced_opt` text COLLATE utf8_unicode_ci,
  `active` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `custom_filter_groups`
--

CREATE TABLE IF NOT EXISTS `custom_filter_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc` text COLLATE utf8_unicode_ci,
  `order` int(11) DEFAULT NULL,
  `or` tinyint(1) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `deletable` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Default Group
--

INSERT INTO `custom_filter_groups` (`id`, `title`, `desc`, `order`, `hidden`, `editable`, `deletable`, `active`, `created`, `modified`) VALUES ('1', NULL, NULL, '0', '1', '0', '0', '1', NULL, NULL);

