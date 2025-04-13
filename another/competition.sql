-- Add or update tables for competitions, votes, and related entities

-- Table structure for table `competitions`
CREATE TABLE IF NOT EXISTS `competitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `competitions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `recipes`
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `competition_entries`
CREATE TABLE IF NOT EXISTS `competition_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `submission` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`),
  KEY `user_id` (`user_id`),
  KEY `recipe_id` (`recipe_id`),
  CONSTRAINT `competition_entries_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `competition_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `competition_entries_ibfk_3` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `likes`
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`user_id`, `entry_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`entry_id`) REFERENCES `competition_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `competition_entries_comments`
CREATE TABLE IF NOT EXISTS `competition_entries_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`entry_id`) REFERENCES `competition_entries` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert new user Elaina
INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `updated_at`) VALUES
(5, 'Elaina', '$2y$10$v6OW1gW6/gnqjsC7v9Uz3.wkq/ttgQxjzDrcwiyWqjgQk0mjgsxre', 'Elaina@utar.my', current_timestamp(), '2025-04-13 16:45:08');

-- Update sample data in `competitions` to reflect 2 finished, 2 ongoing, and 2 upcoming competitions
DELETE FROM `competitions`;

INSERT INTO `competitions` (`id`, `title`, `description`, `start_date`, `end_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Best Sandwich Competition', 'Showcase your sandwich-making skills!', '2025-04-01', '2025-04-10', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'), -- Finished
(2, 'Best Noodle Dish', 'Showcase your noodle-making skills!', '2025-04-05', '2025-04-15', 2, '2025-04-05 10:00:00', '2025-04-05 10:00:00'), -- Ongoing
(3, 'Spicy Food Challenge', 'Who can make the spiciest dish?', '2025-04-10', '2025-04-20', 3, '2025-04-10 10:00:00', '2025-04-10 10:00:00'), -- Ongoing
(4, 'Best Curry Recipe', 'Show us your best curry recipe!', '2025-04-15', '2025-04-25', 4, '2025-04-15 10:00:00', '2025-04-15 10:00:00'), -- Upcoming
(5, 'Street Food Extravaganza', 'Create the best street food dish!', '2025-04-20', '2025-04-30', 2, '2025-04-20 10:00:00', '2025-04-20 10:00:00'), -- Upcoming
(6, 'Dessert Masterpiece', 'Showcase your dessert-making skills!', '2025-03-20', '2025-03-30', 1, '2025-03-20 10:00:00', '2025-03-20 10:00:00'); -- Finished

-- Insert sample data into `recipes` with shuffled `created_by`
INSERT INTO `recipes` (`id`, `name`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Turkey Sandwich', 'A delicious turkey sandwich recipe.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(2, 'Pad Thai', 'A classic Thai noodle dish.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(3, 'Nasi Goreng', 'Indonesian fried rice.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(4, 'Pho', 'Vietnamese noodle soup.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(5, 'Laksa', 'Spicy noodle soup popular in Malaysia and Singapore.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(6, 'Adobo', 'A Filipino dish with meat marinated in vinegar and soy sauce.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(7, 'Tom Yum', 'A hot and sour Thai soup.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(8, 'Rendang', 'A spicy Indonesian meat dish.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(9, 'Hainanese Chicken Rice', 'A Singaporean dish of poached chicken and rice.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(10, 'Char Kway Teow', 'A stir-fried noodle dish from Malaysia.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(11, 'Banh Mi', 'A Vietnamese sandwich.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(12, 'Satay', 'Grilled meat skewers with peanut sauce.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(13, 'Som Tum', 'A Thai green papaya salad.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(14, 'Kaya Toast', 'A Singaporean breakfast dish.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(15, 'Beef Rendang', 'A rich and tender Indonesian beef dish.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(16, 'Mee Goreng', 'A spicy fried noodle dish from Indonesia.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(17, 'Lumpia', 'Filipino spring rolls.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(18, 'Green Curry', 'A Thai curry with green chili paste.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(19, 'Roti Canai', 'A Malaysian flatbread served with curry.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(20, 'Halo-Halo', 'A Filipino dessert with mixed ingredients.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(21, 'Chicken Tikka', 'A spicy grilled chicken dish.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(22, 'Pasta Carbonara', 'A creamy Italian pasta dish.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(23, 'Fish Tacos', 'A Mexican dish with crispy fish and fresh toppings.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(24, 'Vegetable Stir Fry', 'A healthy and colorful stir fry.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(25, 'Chocolate Cake', 'A rich and moist chocolate dessert.', 1, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(26, 'Caesar Salad', 'A classic salad with creamy dressing.', 2, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(27, 'Beef Stroganoff', 'A creamy beef and mushroom dish.', 3, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(28, 'Shrimp Scampi', 'A garlic butter shrimp pasta.', 4, '2025-04-01 10:00:00', '2025-04-01 10:00:00'),
(29, 'Pineapple Fried Rice', 'A sweet and savory fried rice with pineapple.', 5, '2025-04-13 16:50:00', '2025-04-13 16:50:00'),
(30, 'Mango Sticky Rice', 'A classic Thai dessert with mango and sticky rice.', 5, '2025-04-13 16:51:00', '2025-04-13 16:51:00'),
(31, 'Chicken Satay', 'Grilled chicken skewers with peanut sauce.', 5, '2025-04-13 16:52:00', '2025-04-13 16:52:00');

-- Insert sample data into `competition_entries` with shuffled `user_id` and `recipe_id`
INSERT INTO `competition_entries` (`id`, `competition_id`, `user_id`, `recipe_id`, `title`, `description`, `submission`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'My Turkey Sandwich', 'A special turkey sandwich with a secret sauce.', 'My special turkey sandwich recipe.', '2025-04-02 12:00:00', '2025-04-02 12:00:00'),
(2, 2, 3, 2, 'Pad Thai Delight', 'A flavorful Pad Thai recipe.', 'My special Pad Thai recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(3, 2, 4, 3, 'Nasi Goreng Special', 'A unique take on Indonesian fried rice.', 'My Nasi Goreng recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(4, 2, 1, 4, 'Pho Perfection', 'A perfect Vietnamese noodle soup.', 'My Pho recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(5, 2, 2, 5, 'Laksa Love', 'A spicy and creamy Laksa.', 'My Laksa recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(6, 3, 3, 6, 'Adobo Magic', 'A classic Filipino Adobo.', 'My Adobo recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(7, 3, 4, 7, 'Tom Yum Explosion', 'A hot and sour Tom Yum soup.', 'My Tom Yum recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(8, 3, 1, 8, 'Rendang Supreme', 'A rich and spicy Rendang.', 'My Rendang recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(9, 3, 2, 9, 'Hainanese Perfection', 'A perfect Hainanese Chicken Rice.', 'My Hainanese Chicken Rice recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(10, 3, 3, 10, 'Char Kway Teow Bliss', 'A stir-fried noodle delight.', 'My Char Kway Teow recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(11, 4, 4, 11, 'Banh Mi Bonanza', 'A Vietnamese sandwich masterpiece.', 'My Banh Mi recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(12, 4, 1, 12, 'Satay Sensation', 'Grilled meat skewers with a twist.', 'My Satay recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(13, 4, 2, 13, 'Som Tum Surprise', 'A refreshing Thai salad.', 'My Som Tum recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(14, 4, 3, 14, 'Kaya Toast Treat', 'A delightful Singaporean breakfast.', 'My Kaya Toast recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(15, 4, 4, 15, 'Beef Rendang Bliss', 'A tender and flavorful dish.', 'My Beef Rendang recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(16, 1, 3, 21, 'Chicken Tikka Delight', 'A flavorful grilled chicken dish.', 'My Chicken Tikka recipe.', '2025-04-02 12:00:00', '2025-04-02 12:00:00'),
(17, 1, 4, 22, 'Pasta Carbonara Perfection', 'A creamy and delicious pasta.', 'My Pasta Carbonara recipe.', '2025-04-02 12:00:00', '2025-04-02 12:00:00'),
(18, 2, 1, 23, 'Fish Tacos Fiesta', 'A fresh and crispy taco dish.', 'My Fish Tacos recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(19, 2, 2, 24, 'Vegetable Stir Fry Bliss', 'A healthy and colorful stir fry.', 'My Vegetable Stir Fry recipe.', '2025-05-02 12:00:00', '2025-05-02 12:00:00'),
(20, 3, 4, 25, 'Chocolate Cake Heaven', 'A rich and moist chocolate dessert.', 'My Chocolate Cake recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(21, 3, 1, 26, 'Caesar Salad Supreme', 'A classic salad with creamy dressing.', 'My Caesar Salad recipe.', '2025-06-02 12:00:00', '2025-06-02 12:00:00'),
(22, 4, 2, 27, 'Beef Stroganoff Delight', 'A creamy beef and mushroom dish.', 'My Beef Stroganoff recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(23, 4, 3, 28, 'Shrimp Scampi Perfection', 'A garlic butter shrimp pasta.', 'My Shrimp Scampi recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(24, 5, 1, 21, 'Chicken Tikka Extravaganza', 'A spicy grilled chicken dish.', 'My Chicken Tikka recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(25, 5, 2, 22, 'Pasta Carbonara Delight', 'A creamy Italian pasta dish.', 'My Pasta Carbonara recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(26, 6, 3, 23, 'Fish Tacos Supreme', 'A Mexican dish with crispy fish.', 'My Fish Tacos recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(27, 6, 4, 24, 'Vegetable Stir Fry Perfection', 'A healthy and colorful stir fry.', 'My Vegetable Stir Fry recipe.', '2025-07-02 12:00:00', '2025-07-02 12:00:00'),
(28, 5, 5, 29, 'Pineapple Fried Rice Delight', 'A tropical twist on fried rice.', 'My Pineapple Fried Rice recipe.', '2025-04-13 17:00:00', '2025-04-13 17:00:00'),
(29, 6, 5, 30, 'Mango Sticky Rice Bliss', 'A sweet and creamy dessert.', 'My Mango Sticky Rice recipe.', '2025-04-13 17:01:00', '2025-04-13 17:01:00'),
(30, 6, 5, 31, 'Chicken Satay Perfection', 'A flavorful grilled chicken dish.', 'My Chicken Satay recipe.', '2025-04-13 17:02:00', '2025-04-13 17:02:00');

-- Insert sample data into `likes`
INSERT INTO `likes` (`id`, `user_id`, `entry_id`, `created_at`) VALUES
(1, 3, 1, '2025-04-03 14:00:00'),
(2, 4, 1, '2025-04-03 15:00:00'),
(3, 2, 1, '2025-04-03 16:00:00');

-- Insert sample data into `competition_entries_comments`
INSERT INTO `competition_entries_comments` (`id`, `entry_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 3, 'Great recipe!', '2025-04-03 14:00:00'),
(2, 1, 4, 'Looks delicious!', '2025-04-03 15:00:00');

