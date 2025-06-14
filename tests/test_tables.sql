--
-- Table structure for table `wptests_frmt_form_entry`
--

DROP TABLE IF EXISTS `wptests_frmt_form_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wptests_frmt_form_entry` (
  `entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_type` varchar(191) NOT NULL,
  `draft_id` varchar(12) DEFAULT NULL,
  `form_id` bigint(20) unsigned NOT NULL,
  `is_spam` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`entry_id`),
  KEY `entry_is_spam` (`is_spam`),
  KEY `entry_type` (`entry_type`),
  KEY `entry_form_id` (`form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wptests_frmt_form_entry`
--

LOCK TABLES `wptests_frmt_form_entry` WRITE;
/*!40000 ALTER TABLE `wptests_frmt_form_entry` DISABLE KEYS */;
INSERT INTO `wptests_frmt_form_entry` VALUES
(1,'custom-forms',NULL,6,0,'2025-05-25 06:26:02'),
(2,'custom-forms',NULL,7,0,'2025-05-25 06:26:12'),
(3,'custom-forms',NULL,8,0,'2025-05-25 06:26:24'),
(4,'custom-forms',NULL,8,0,'2025-05-25 06:26:48');
/*!40000 ALTER TABLE `wptests_frmt_form_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wptests_frmt_form_entry_meta`
--

DROP TABLE IF EXISTS `wptests_frmt_form_entry_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wptests_frmt_form_entry_meta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `meta_key` varchar(191) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`meta_id`),
  KEY `meta_key` (`meta_key`),
  KEY `meta_entry_id` (`entry_id`),
  KEY `meta_key_object` (`entry_id`,`meta_key`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wptests_frmt_form_entry_meta`
--

LOCK TABLES `wptests_frmt_form_entry_meta` WRITE;
/*!40000 ALTER TABLE `wptests_frmt_form_entry_meta` DISABLE KEYS */;
INSERT INTO `wptests_frmt_form_entry_meta` VALUES
(1,1,'email-1','kjell@ullared.se','2025-05-25 06:26:02','0000-00-00 00:00:00'),
(2,1,'_forminator_user_ip','172.20.0.1','2025-05-25 06:26:02','0000-00-00 00:00:00'),
(3,2,'email-1','kjell@ullared.se','2025-05-25 06:26:12','0000-00-00 00:00:00'),
(4,2,'_forminator_user_ip','172.20.0.1','2025-05-25 06:26:12','0000-00-00 00:00:00'),
(5,3,'email-1','kjell@ullared.se','2025-05-25 06:26:24','0000-00-00 00:00:00'),
(6,3,'_forminator_user_ip','172.20.0.1','2025-05-25 06:26:24','0000-00-00 00:00:00'),
(7,4,'email-1','kjell@ullared.se','2025-05-25 06:26:48','0000-00-00 00:00:00'),
(8,4,'_forminator_user_ip','172.20.0.1','2025-05-25 06:26:48','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `wptests_frmt_form_entry_meta` ENABLE KEYS */;
UNLOCK TABLES;

