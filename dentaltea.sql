--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(25) NOT NULL DEFAULT 'artist',
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Estructura de tabla para la tabla `pictograms`
--

CREATE TABLE `pictograms` (
  `id` int(11) NOT NULL,
  `image` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

--
-- Estructura de tabla para la tabla `advices`
--

CREATE TABLE `advices` (
  `id` int(11) NOT NULL,
  `title` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(300) DEFAULT NULL,
  `sort_number` int(11) NOT NULL DEFAULT 0,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

--
-- Estructura de tabla para la tabla `sequences`
--

CREATE TABLE `sequences` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);


--
-- Estructura de tabla para la tabla `sequence_pictograms`
--

CREATE TABLE `sequence_pictograms` (
  `sequence_id` int(11) NOT NULL,
  `pictogram_id` int(11) NOT NULL,
  `sort_number` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`sequence_id`, `pictogram_id`),
  FOREIGN KEY (`sequence_id`) REFERENCES `sequences`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`pictogram_id`) REFERENCES `pictograms`(`id`) ON DELETE CASCADE
);

--
-- Estructura de tabla para la tabla `dates`
--

CREATE TABLE `dates` (
  `id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `creation_time` datetime NOT NULL DEFAULT current_timestamp(),
  `sequence_id` int(11) DEFAULT NULL,
  `notifications_mobile` tinyint(1) NOT NULL DEFAULT 0,
  `notifications_email` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);