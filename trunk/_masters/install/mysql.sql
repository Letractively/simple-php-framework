CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(65) NOT NULL,
  `password` varchar(65) NOT NULL,
  `level` enum('user','admin') NOT NULL,
  `email` varchar(65) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
);