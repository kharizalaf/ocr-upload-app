CREATE TABLE `ocr_results` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `filename` varchar(255) DEFAULT NULL,
 `ocr_text` text DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
