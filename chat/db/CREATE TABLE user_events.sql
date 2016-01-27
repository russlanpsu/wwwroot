CREATE TABLE `user_events` (
  `user_id` int(11) NOT NULL,
  `events` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
