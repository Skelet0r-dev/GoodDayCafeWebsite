CREATE TABLE `order` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `order_placed` DATE NOT NULL,
  `payment` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `position` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB;

CREATE TABLE `order_item` (
  `order_item_id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `fk_order_item_order` (`order_id`),
  CONSTRAINT `fk_order_item_order`
    FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `token` VARCHAR(191) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `product_image` (
  `image_id` INT NOT NULL AUTO_INCREMENT,
  `image_name` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(500) NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `product_id` INT NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(100) NOT NULL,
  `product_category` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB;

CREATE TABLE `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(50) NOT NULL,
  `lastname` VARCHAR(50) NOT NULL,
  `dateofbirth` DATE NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `pass` VARCHAR(255) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB;
