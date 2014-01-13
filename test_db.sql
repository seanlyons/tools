USE seandb;
CREATE TABLE `test_db` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
    `varch` varchar(12) NOT NULL,
    `integ` int(10) NOT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE=InnoDB;