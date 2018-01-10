SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `authors` (
  `id` int(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` enum('beginner','professional','senior','') NOT NULL,
  `fk_revisor` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dfgdfg';

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `publicated_at` date DEFAULT NULL,
  `fk_author` int(10) NOT NULL,
  `aproved` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='ns=Posts';

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `fk_post` int(11) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='class_name=Comment';

CREATE TABLE `tagged` (
  `fk_post` int(11) NOT NULL,
  `fk_tag` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='pivot=tags|posts&ns=Posts';

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `label` varchar(24) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='ns=Posts';


ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fk_revisor_2` (`fk_revisor`),
  ADD KEY `fk_revisor` (`fk_revisor`);

ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_author` (`fk_author`);

ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post` (`fk_post`);

ALTER TABLE `tagged`
  ADD KEY `fk_post` (`fk_post`),
  ADD KEY `fk_tag` (`fk_tag`);

ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`);


ALTER TABLE `authors`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `authors`
  ADD CONSTRAINT `dependent` FOREIGN KEY (`fk_revisor`) REFERENCES `authors` (`id`);

ALTER TABLE `posts`
  ADD CONSTRAINT `post` FOREIGN KEY (`fk_author`) REFERENCES `authors` (`id`);

ALTER TABLE `post_comments`
  ADD CONSTRAINT `comments` FOREIGN KEY (`fk_post`) REFERENCES `posts` (`id`);

ALTER TABLE `tagged`
  ADD CONSTRAINT `posts` FOREIGN KEY (`fk_tag`) REFERENCES `tags` (`id`),
  ADD CONSTRAINT `tags` FOREIGN KEY (`fk_post`) REFERENCES `posts` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
