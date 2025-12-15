-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Дек 11 2025 г., 21:16
-- Версия сервера: 11.4.7-MariaDB-ubu2404
-- Версия PHP: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `levitin_Glags-API`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1 CHECK (`quantity` > 0),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 2, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(2, 2, 4, 1, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(3, 2, 9, 3, '2025-11-25 07:01:54', '2025-11-25 07:01:54');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Новогодние сувениры', '2025-11-25 06:48:14', '2025-11-25 06:48:14'),
(2, 'Свадебные', '2025-11-25 06:48:14', '2025-11-25 06:48:14'),
(3, 'Подарки на день рождения', '2025-11-25 06:48:14', '2025-11-25 06:48:14'),
(4, 'Фигурки животных', '2025-11-25 06:48:14', '2025-11-25 06:48:14'),
(5, 'Вазы и подсвечники', '2025-11-25 06:48:14', '2025-11-25 06:48:14');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `guest_first_name` varchar(50) DEFAULT NULL,
  `guest_last_name` varchar(50) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(15) DEFAULT NULL,
  `status` enum('new','confirmed','declined','cancelled') NOT NULL DEFAULT 'new',
  `total_sum` decimal(10,2) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `guest_first_name`, `guest_last_name`, `guest_email`, `guest_phone`, `status`, `total_sum`, `delivery_address`, `comment`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, NULL, NULL, 'confirmed', 8970.00, 'Санкт-Петербург, пр. Невский, д. 25, кв. 12', 'Подарочная упаковка, пожалуйста', '2025-11-25 07:01:54', '2025-12-04 19:57:50'),
(2, NULL, 'Анна', 'Иванова', 'anna.guest@mail.ru', '+79991234567', 'new', 18900.00, 'Москва, ул. Ленина, д. 10', 'Позвонить за час до доставки', '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(3, 2, NULL, NULL, NULL, NULL, 'confirmed', 12500.00, 'Санкт-Петербург, ул. Малая Морская, д. 8', NULL, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(4, 2, NULL, NULL, NULL, NULL, 'new', 8970.00, 'Санкт-Петербург, пр. Невский, д. 25, кв. 12', 'Подарочная упаковка, пожалуйста', '2025-12-01 11:03:54', '2025-12-01 11:03:54'),
(5, 5, NULL, NULL, NULL, NULL, 'new', 7560.00, 'Санкт-Петербург, Ул. Пушкина, 34', NULL, '2025-12-04 20:23:57', '2025-12-04 20:23:57'),
(6, 6, NULL, NULL, NULL, NULL, 'new', 1780.00, 'Санкт-Петербург, Ул. Пушкина, 14', NULL, '2025-12-04 20:26:12', '2025-12-04 20:26:12'),
(7, 6, NULL, NULL, NULL, NULL, 'confirmed', 43800.00, 'Санкт-Петербург, пр. Невский, д. 25, кв. 19', NULL, '2025-12-04 21:03:34', '2025-12-04 21:04:56'),
(8, NULL, 'Иван', 'Костин', 'qwe@mail.ru', '89123784390', 'new', 5780.00, 'Санкт-Петербург, Коломяжский пр., д. 115, кв. 19', NULL, '2025-12-05 07:07:45', '2025-12-05 07:07:45'),
(9, NULL, 'Иван', 'Петров', 'ivan@example.com', '+79001234567', 'new', 5780.00, 'Санкт-Петербург, пр. Невский, д. 25, кв. 12', 'Подарочная упаковка', '2025-12-09 16:09:37', '2025-12-09 16:09:37');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL CHECK (`quantity` > 0),
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 1, 1, 2890.00, '2025-11-25 07:01:54'),
(2, 1, 2, 2, 890.00, '2025-11-25 07:01:54'),
(3, 1, 10, 1, 4290.00, '2025-11-25 07:01:54'),
(4, 2, 8, 1, 18900.00, '2025-11-25 07:01:54'),
(5, 3, 6, 1, 12500.00, '2025-11-25 07:01:54'),
(6, 5, 1, 2, 2890.00, '2025-12-04 20:23:57'),
(7, 5, 2, 2, 890.00, '2025-12-04 20:23:57'),
(8, 6, 2, 2, 890.00, '2025-12-04 20:26:12'),
(9, 7, 12, 2, 21900.00, '2025-12-04 21:03:34'),
(10, 8, 1, 2, 2890.00, '2025-12-05 07:07:45'),
(11, 9, 1, 2, 2890.00, '2025-12-09 16:09:37');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` > 0),
  `category_id` int(10) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category_id`, `description`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 'Стеклянный шар «Снежная сказка» с LED-подсветкой', 11900.00, 1, 'Ручная работа, диаметр 10 см, подсветка меняет цвета', 0, '2025-11-25 07:01:54', '2025-12-09 18:58:36'),
(2, 'Ёлочная игрушка «Дед Мороз» из цветного стекла', 890.00, 1, 'Высота 12 см, ручная роспись', 50, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(3, 'Снеговик из матового стекла с подсветкой', 1590.00, 1, 'Высота 18 см, работает от батареек', 8, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(4, 'Свадебные бокалы «Лебеди» (пара)', 8900.00, 2, 'Хрустальное стекло, гравировка, подарочная упаковка', 12, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(5, 'Сувенир «Сердце» из красного стекла', 4590.00, 2, 'Размер 15×15 см, в подарочной коробке', 20, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(6, 'Стеклянная ваза «Морской бриз»', 12500.00, 3, 'Ручная работа, высота 42 см, синие волны', 5, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(7, 'Фигурка «Сова мудрости» из цветного стекла', 3790.00, 3, 'Высота 25 см, символ мудрости и успеха', 30, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(8, 'Стеклянный дракон «Хранитель»', 18900.00, 4, 'Ограниченная серия, высота 35 см', 3, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(9, 'Котёнок из молочного стекла', 2490.00, 4, 'Высота 12 см, очень милый', 45, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(10, 'Дельфин на волне', 5690.00, 4, 'Динамичная композиция, высота 28 см', 10, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(11, 'Подсвечник «Лотос»', 4290.00, 5, 'Для одной свечи, диаметр 15 см', 25, '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(12, 'Ваза напольная «Волна»', 21900.00, 5, 'Высота 80 см, авторская работа', 2, '2025-11-25 07:01:54', '2025-11-25 07:01:54');

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `created_at`) VALUES
(1, 1, 'https://api.glags.ru/uploads/products/shar_snezhnaya_1.jpg', '2025-11-25 07:01:54'),
(2, 1, 'https://api.glags.ru/uploads/products/shar_snezhnaya_2.jpg', '2025-11-25 07:01:54'),
(3, 1, 'https://api.glags.ru/uploads/products/shar_snezhnaya_3.jpg', '2025-11-25 07:01:54'),
(4, 2, 'https://api.glags.ru/uploads/products/ded_moroz.jpg', '2025-11-25 07:01:54'),
(5, 4, 'https://api.glags.ru/uploads/products/svadebnye_bokaly_1.jpg', '2025-11-25 07:01:54'),
(6, 4, 'https://api.glags.ru/uploads/products/svadebnye_bokaly_2.jpg', '2025-11-25 07:01:54'),
(7, 6, 'https://api.glags.ru/uploads/products/vaza_morskoi_briz.jpg', '2025-11-25 07:01:54'),
(8, 8, 'https://api.glags.ru/uploads/products/dracon_1.jpg', '2025-11-25 07:01:54'),
(9, 8, 'https://api.glags.ru/uploads/products/dracon_2.jpg', '2025-11-25 07:01:54'),
(10, 9, 'https://api.glags.ru/uploads/products/kotenok.jpg', '2025-11-25 07:01:54');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `auth_token`, `avatar_url`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Админ', 'Админович', 'admin@glags.ru', '+79000000000', '$2y$13$z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8z8', NULL, NULL, 'admin', '2025-11-25 06:48:14', '2025-11-25 06:48:14'),
(2, 'Иван', 'Петров', 'ivan@example.com', '+79001234567', '$2y$13$9g8K8z8K8z8K8z8K8z8K8u8K8z8K8z8K8z8K8z8K8z8K8z8K8z8K8z', NULL, NULL, 'user', '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(3, 'Мария', 'Сидорова', 'maria@example.com', '+79111234567', '$2y$13$9g8K8z8K8z8K8z8K8z8K8u8K8z8K8z8K8z8K8z8K8z8K8z8K8z8K8z', NULL, 'https://api.glags.ru/uploads/avatars/maria.jpg', 'user', '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(4, 'Алексей', 'Козлов', 'alex@example.com', '+79219876543', '$2y$13$9g8K8z8K8z8K8z8K8z8K8u8K8z8K8z8K8z8K8z8K8z8K8z8K8z8K8z', NULL, NULL, 'user', '2025-11-25 07:01:54', '2025-11-25 07:01:54'),
(5, 'Александр', 'Левитин', 'slevitin1@gmail.com', '+78641234567', '$2y$13$osQyd2bs9VwRi3cizdpyhegEnir.UE055ooy5cVBaU0d7z2QV.oae', 'SQxxWfpHgFXdODFLtkcviCfQcLzL1NwCOIDXHJc60mSpZKrT9n8OCpAP_s_P0fPk', '/uploads/avatars/user5_1765296697.png', 'admin', '2025-12-01 10:54:27', '2025-12-11 16:43:13'),
(6, 'Дмитрий', 'Александрович', 'klot33@gmail.com', '+79456234568', '$2y$13$x1nwteHrFboXQFMeVu4/qOP94OQntUCd4cyUlSr4NEDtaBS.3KXC.', NULL, '/uploads/avatars/user6_1765297316.jpg', 'user', '2025-12-04 19:31:13', '2025-12-09 18:22:28');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_category_name` (`name`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_name` (`name`),
  ADD KEY `idx_category` (`category_id`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD UNIQUE KEY `uq_phone` (`phone`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_image_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
