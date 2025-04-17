-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 05:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `culinary_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `discussion_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'I always salt the water well and cook it just until al dente!', '2025-04-04 10:44:02', '2025-04-04 10:44:02'),
(2, 2, 1, 'Applesauce works great for cakes and muffins as an egg substitute', '2025-04-04 10:44:02', '2025-04-14 05:49:09'),
(3, 1, 3, 'Use the pot to cook pasta.', '2025-04-04 11:58:33', '2025-04-04 11:58:33'),
(13, 9, 6, 'wow', '2025-04-13 08:22:01', '2025-04-13 08:22:01'),
(14, 12, 4, 'I would love to try it.', '2025-04-13 08:55:22', '2025-04-13 08:55:22'),
(16, 1, 5, 'BLABLA', '2025-04-14 05:37:15', '2025-04-14 05:37:15'),
(28, 18, 6, 'test test', '2025-04-14 05:55:27', '2025-04-14 05:55:27'),
(29, 22, 4, '123', '2025-04-15 05:50:06', '2025-04-15 05:50:06');

-- --------------------------------------------------------

--
-- Table structure for table `comment_reactions`
--

CREATE TABLE `comment_reactions` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_reactions`
--

INSERT INTO `comment_reactions` (`id`, `comment_id`, `user_id`, `created_at`) VALUES
(22, 3, 4, '2025-04-13 04:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `user_id`, `title`, `description`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 4, 'aaa111', 'testing123', '2025-04-14', '2025-04-15', '2025-04-14 02:35:23', '2025-04-14 02:35:23'),
(2, 9, 'avbcd', 'aasasd', '2025-04-15', '2025-04-16', '2025-04-15 14:35:06', '2025-04-15 14:35:06');

-- --------------------------------------------------------

--
-- Table structure for table `competition_entries`
--

CREATE TABLE `competition_entries` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `submission` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_entries`
--

INSERT INTO `competition_entries` (`id`, `competition_id`, `user_id`, `recipe_id`, `title`, `description`, `submission`, `created_at`, `updated_at`) VALUES
(2, 1, 4, 4, 'abcd', 'abcd', 'abcd', '2025-04-14 03:50:27', '2025-04-14 03:50:27'),
(3, 1, 4, 12, '123', '123', '123', '2025-04-14 04:23:55', '2025-04-14 04:23:55'),
(4, 1, 9, 15, 'this is testing 2', 'avbcdf', 'asasdasdadas', '2025-04-15 14:33:47', '2025-04-15 14:33:47'),
(6, 2, 9, 15, 'abcd', 'asvsd', 'aassda', '2025-04-15 14:40:14', '2025-04-15 14:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `competition_entries_comments`
--

CREATE TABLE `competition_entries_comments` (
  `id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recipe_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`id`, `user_id`, `title`, `content`, `created_at`, `updated_at`, `recipe_id`) VALUES
(1, 1, 'Best way to cook pasta?', 'I\'ve been trying different methods to cook pasta perfectly. What\'s your favorite technique?', '2025-04-04 10:43:52', '2025-04-04 10:43:52', NULL),
(2, 2, 'Vegan substitutes for eggs in baking', 'Looking for recommendations on the best egg substitutes for different types of baked goods.', '2025-04-04 10:43:52', '2025-04-04 10:43:52', NULL),
(3, 3, 'What is your favourite food?', 'Mine is mee goreng!', '2025-04-04 16:36:35', '2025-04-04 16:36:35', NULL),
(9, 4, 'Shared Recipe: Sweet Red Bean Soup', 'This is my first recipe.', '2025-04-13 06:52:34', '2025-04-13 08:30:51', 4),
(12, 4, 'Shared Recipe: Nasi Lemak', 'This is my second recipe.', '2025-04-13 08:46:40', '2025-04-13 08:46:40', 5),
(13, 4, 'Shared Recipe: Tandoni Chicken', '', '2025-04-14 03:10:01', '2025-04-14 03:10:01', 11),
(15, 4, '123', '123', '2025-04-14 04:40:40', '2025-04-14 04:40:40', NULL),
(16, 4, 'Shared Recipe: Testing123', '123', '2025-04-14 05:07:49', '2025-04-14 05:07:49', 12),
(17, 4, 'Shared Recipe: Tandoni Chicken', 'Shared a recipe!', '2025-04-14 05:07:58', '2025-04-14 05:07:58', 11),
(18, 7, 'Shared Recipe: Testtest', 'test test', '2025-04-14 05:17:07', '2025-04-14 05:17:07', 13),
(22, 4, 'Testing', '123', '2025-04-15 05:49:35', '2025-04-15 05:49:35', NULL),
(23, 4, '1233', '1134', '2025-04-15 05:52:13', '2025-04-15 05:52:13', NULL),
(24, 9, '123', '123', '2025-04-15 14:11:19', '2025-04-15 14:11:19', NULL),
(26, 9, 'Shared Recipe: testing2', 'this is testing 2', '2025-04-15 14:28:58', '2025-04-15 14:28:58', 15),
(27, 9, 'Shared Recipe: testing3', 'testing3', '2025-04-15 14:44:49', '2025-04-15 14:44:49', 16),
(28, 9, 'Shared Recipe: testing2', 'testing3', '2025-04-15 14:45:19', '2025-04-15 14:45:19', 15),
(29, 9, 'Shared Recipe: testing2', '1234', '2025-04-15 14:49:28', '2025-04-15 14:49:28', 15);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `quantity` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `name`, `quantity`) VALUES
(13, 5, 'rice', '1 cup'),
(14, 5, 'coconut milk', '3/4 cup'),
(15, 5, 'water', '1 cup'),
(16, 5, 'lemongrass', '1'),
(17, 5, 'galangal', '1'),
(18, 5, 'kaffir lime leaves', '5'),
(19, 5, 'salt', 'to taste'),
(20, 5, 'roasted peanuts', 'as required'),
(21, 5, 'crispy fried anchovies', 'as required'),
(22, 5, 'few sliced cucumber', 'as required'),
(23, 5, 'chilli sambal', '1 tbsp'),
(24, 5, 'boiled egg', '1'),
(31, 4, 'dried azuki beans', '2 cups'),
(32, 4, 'dried tangerine peel', '3 small pieces'),
(33, 4, 'baking soda', '1/2 teaspoon'),
(34, 4, 'small white tapioca pearls', '2 tablespoons'),
(35, 4, 'lump sugar', '5 ounces'),
(42, 11, 'cloves', '5'),
(43, 11, 'dried guajillo chiles', '2'),
(44, 11, 'green cardamom', '2'),
(45, 11, 'black cardamom', '1'),
(46, 11, 'coriander seeds', '1 teaspoon'),
(47, 11, 'fennel seeds', '1/2 teaspoon'),
(48, 11, 'fenugreek seeds', '1/2 teaspoon'),
(49, 11, 'whole fat plain yogurt', '1 cup'),
(50, 12, 'Testing', '123'),
(51, 13, 'telur', '2 biji'),
(52, 15, 'testing2', '2 ab'),
(56, 16, 'testing222', '222'),
(57, 16, 'testing333', '333'),
(58, 16, 'testing444', '444');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `entry_id`, `created_at`) VALUES
(7, 4, 3, '2025-04-14 05:57:01'),
(13, 9, 2, '2025-04-15 14:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner','Supper') NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `custom_meal_name` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `custom_meal_ingredients` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plans`
--

INSERT INTO `meal_plans` (`plan_id`, `user_id`, `meal_date`, `meal_type`, `recipe_id`, `custom_meal_name`, `created_at`, `notes`, `custom_meal_ingredients`) VALUES
(1, 8, '2025-04-15', 'Breakfast', 4, NULL, 2147483647, 'abcd', NULL),
(2, 8, '2025-04-16', 'Breakfast', 4, NULL, 2147483647, 'abcd', NULL),
(3, 9, '2025-04-15', 'Supper', 16, NULL, 2147483647, 'this is testing 3', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `discussion_id`, `user_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, '2025-04-04 10:44:34', '2025-04-04 10:44:34'),
(2, 2, 1, 4, '2025-04-04 10:44:34', '2025-04-04 10:44:34'),
(3, 1, 3, 5, '2025-04-04 11:57:25', '2025-04-04 16:29:24'),
(4, 3, 3, 1, '2025-04-04 16:36:57', '2025-04-04 16:37:00'),
(5, 3, 4, 5, '2025-04-12 06:33:55', '2025-04-13 08:46:22'),
(7, 9, 4, 1, '2025-04-13 07:04:41', '2025-04-13 07:16:26'),
(8, 9, 6, 3, '2025-04-13 08:21:28', '2025-04-13 08:21:49'),
(10, 12, 4, 5, '2025-04-13 08:55:09', '2025-04-14 05:32:27'),
(11, 12, 6, 1, '2025-04-13 11:15:21', '2025-04-13 11:15:21'),
(12, 18, 4, 5, '2025-04-14 05:32:43', '2025-04-14 05:32:57'),
(13, 18, 5, 1, '2025-04-14 05:33:10', '2025-04-14 05:33:10'),
(14, 24, 9, 5, '2025-04-15 14:11:28', '2025-04-15 14:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `cuisine` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `name`, `cuisine`, `image`, `created_at`, `updated_at`) VALUES
(4, 4, 'Sweet Red Bean Soup', 'Chinese', 'uploads/rbs.jpeg', '2025-04-13 03:24:41', NULL),
(5, 4, 'Nasi Lemak', 'Malay', 'uploads/nasilemak.jpeg', '2025-04-13 03:49:59', NULL),
(11, 4, 'Tandoni Chicken', 'Indian', 'uploads/1744511408_tandoni.jpeg', '2025-04-13 10:30:08', NULL),
(12, 1, 'Lotus Root Soup', 'Chinese', 'uploads/lrs.jpeg', '2025-04-13 05:07:19', NULL),
(14, 1, 'Mango Sticky Rice', 'Thai', 'uploads/1744563136_rainbow-mango-sticky-rice-sq.jpg', '2025-04-13 16:52:16', NULL),
(15, 9, 'testing2', 'Malay', 'uploads/1744725506_cat4.png', NULL, NULL),
(16, 9, 'testing3', 'Chinese', 'uploads/1744725718_cat4.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saved_meal_plans`
--

CREATE TABLE `saved_meal_plans` (
  `saved_plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_type` varchar(50) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `custom_meal_name` varchar(255) DEFAULT NULL,
  `custom_meal_ingredients` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_meal_plans`
--

INSERT INTO `saved_meal_plans` (`saved_plan_id`, `user_id`, `meal_type`, `recipe_id`, `custom_meal_name`, `custom_meal_ingredients`, `notes`, `created_at`) VALUES
(1, 9, 'Supper', 16, NULL, NULL, 'this is testing 3', '2025-04-15 14:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `steps`
--

CREATE TABLE `steps` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `steps`
--

INSERT INTO `steps` (`id`, `recipe_id`, `step_number`, `description`) VALUES
(11, 5, 1, 'Pressure cook the drained rice in coconut milk, water, lemongrass, galangal, kaffir lime leaves and salt to taste for 2 whistles. Keep aside.'),
(12, 5, 2, 'When serving, place the rice in the centre of the plate and top it up with the chilli sambal. Place the peanuts, anchovies, cucumber and eggs all around and garnish with coriander leaves.'),
(13, 5, 3, 'Chilli Sambal - Heat oil in a pan and fry the anchovies till crisp. Drain on a kitchen towel. When cool, grind along with chilies, garlic, lemon grass, shallots, salt, vinegar and sugar to a smooth paste.'),
(14, 5, 4, 'Heat the same oil in which the anchovies were fried. Saute the prepared paste on a low flame till it turns slightly dark brown in colour and the oil starts to separate from the sides of the pan. Serve along with Nasi Lemak.'),
(31, 11, 1, 'To make the marinade, toast the cloves, whole chiles, both types of cardamom seeds, coriander seeds, fennel seeds and fenugreek seeds in a cast-iron skillet until fragrant, 3 minutes or so, shaking the pan. Then, pour the spices into a spice grinder and grind them until you get a fine powder. '),
(32, 11, 2, 'In large bowl, whisk together the spice mixture you just made with the yogurt, oil, malt vinegar, salt, ground cinnamon, paprika, turmeric, cayenne pepper, garlic and ginger until well combined. It should smell amazing! Taste and adjust with more salt if it needs it. '),
(33, 11, 3, 'Reserve 1/3 cup of the marinade and set aside; you\'re going to make a sauce out of this reserved marinade. '),
(34, 11, 4, 'Prick the chicken thighs with a fork. Add the thighs to the rest of the marinade, and toss to coat. Marinate at least 1 hour in the fridge, and at most overnight. '),
(35, 11, 5, 'When you\'re ready to cook, line a baking sheet with foil, and turn your broiler on. Place each chicken thigh on the baking sheet, making sure each one is coated with the marinade, but isn\'t swimming in it. Cook the chicken thighs under the broiler until starting to blacken, about 5 minutes. Then turn the oven to 350, and cook until a meat thermometer inserted in the meatiest part of the thigh registers 160 degrees F, another 10 minutes. Remove from the oven. '),
(36, 11, 6, 'While the chicken is cooking, pour the reserved marinade into a small saucepan, along with 1/2 cup water and the honey. Bring to a gentle boil over medium-low heat, whisking all the time. Taste and season with salt and pepper. Remove from the heat and pour into a small bowl or gravy boat for serving. '),
(37, 11, 7, 'Serve the chicken thighs on a platter with a fresh squeeze of lime and a drizzle of the sauce.'),
(56, 14, 1, 'Place the rice in a bowl, cover with cold water and leave to soak for 2-3 hours. Using a sieve, strain the soaked rice, then place it in a steamer or the top half of a double boiler. Cover and steam over a high heat for 25-30 minutes, until soft and translucent.'),
(57, 14, 2, 'Pour 250ml of the coconut milk into a small mixing bowl. Add the sugar and salt, stirring until the dissolve. Add the cooked sticky rice and stir until well mixed. Cover and leave to stand for 15 minutes.'),
(58, 14, 3, 'Meanwhile, peel and slice the mangoes and set aside.'),
(59, 14, 4, 'Place the remaining coconut milk in a small saucepan, bring to the boil, then take off the heat.'),
(60, 14, 5, 'Divide the rice between 2 serving places and top with the mango slices. Spoon over 3 tablespoons of the hot coconut milk and finish off with a sprig of basil (or mint).'),
(270, 12, 1, 'Rinse the dried shiitake mushrooms and place them in enough water to cover in an airtight container. Place them in the refrigerator to soak 8 hours or overnight. Once soaked, squeeze the mushrooms to remove water, trim off the stems, cut the caps in half and set them aside. Measure the mushroom soaking liquid and add enough cold water to make 10 cups.'),
(271, 12, 2, 'Place the raw peanuts and black beans in a medium bowl filled with cold water. Cover the bowl and place it in the refrigerator to soak until the legumes are plump, at least 6 hours or overnight. Drain the legumes and set aside. Discard the soaking liquid.'),
(272, 12, 3, 'Cut the potato in half, and then slice it into 1/2-inch-thick slices. Peel and trim the carrot and cut it diagonally into 1/2-inch-thick slices.'),
(273, 12, 4, 'Peel the lotus root and cut the two dried ends off. Cut the lotus root in half and then make 1/4-inch-thick slices.'),
(274, 12, 5, 'Soak the goji berries in a small bowl until plump, about 10 minutes. Strain and discard the soaking liquid. Set aside.'),
(275, 12, 6, 'Place the soaked peanuts and black beans, shiitake mushrooms, potato, carrot, lotus root, jujubes, sliced ginger, white peppercorns and the reserved 10 cups mushroom soaking liquid in a large pot. Cover and bring to a boil. Reduce the heat so the soup simmers. Partially cover the pot and simmer gently until the peanuts are tender, at least 2 hours; stir in the goji berries about 30 minutes before the soup is done.'),
(276, 12, 7, 'Season the soup with 2 teaspoons kosher salt. Taste and add more salt if needed. Ladle the soup into bowls making sure each has some lotus root.'),
(278, 4, 1, 'Soak the beans in a medium bowl with enough cold water to cover by about 2 inches, for at least 8 hours or overnight.'),
(279, 4, 2, 'When the beans are almost ready, soak the dried tangerine peel in a small bowl with enough warm water to cover until softened, about 20 minutes. Using a spoon, scrape off as much of the white pith as possible, then finely chop the peel.  '),
(280, 4, 3, 'Drain the beans and transfer them to a 5-quart Dutch oven. Add 1 teaspoon of the chopped tangerine peel, the baking soda and 7 cups cold water and bring to a boil. Reduce to a simmer and cook the beans, partially covered and stirring occasionally, until soft, about 1 hour.  '),
(281, 4, 4, 'Meanwhile, soak the tapioca pearls in a small bowl with enough cold water to cover until softened, about 30 minutes. Drain and set aside. '),
(282, 4, 5, 'When the beans are ready, strain out 1/2 cup of the cooked beans and transfer to a small bowl. Mash with a fork or potato masher and then return the mashed beans to the pot. Add the drained tapioca pearls, sugar and salt. Simmer over medium-low heat, stirring occasionally, until the pearls are completely clear and soft, about 15 minutes. Serve hot.'),
(283, 15, 1, 'Step1'),
(286, 16, 1, 'Step 1 Testing 1'),
(287, 16, 2, 'Step 2 Testing 2');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `updated_at`, `role`) VALUES
(1, 'john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', '2025-04-04 10:43:28', '2025-04-04 10:43:28', 'user'),
(2, 'jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@example.com', '2025-04-04 10:43:28', '2025-04-04 10:43:28', 'user'),
(3, 'abcd1234', '$2y$10$5VNigSdwHK7LG8wY61wzOuCQzhycS.tgyWRZhFCIzVVocj6hhVyVG', 'abcd1234@gmail.com', '2025-04-04 11:57:13', '2025-04-04 11:57:13', 'user'),
(4, 'aaa111', '$2y$10$Q6cAZCy2dkYvL7WPuqXLNuoeDka.8QM4LuddbkukbtUoOkEZh7bX6', 'aaa111@gmail.com', '2025-04-12 06:33:30', '2025-04-13 03:13:34', 'user'),
(5, 'bbb222', '$2y$10$4jWSE9dH30W5RPsfnlBVruys3aRtDVW4SEKr0shAPoR0OZ/bENBKm', 'bbb222@gmail.com', '2025-04-12 15:45:35', '2025-04-12 15:45:35', 'user'),
(6, 'admin123', '$2y$10$rhYhgNHR95Aofm4imSh6h.X73brjMDcoDZ4qZpwXL3RbaUwIiviNO', 'admin123@gmail.com', '2025-04-13 07:56:55', '2025-04-13 08:01:19', 'admin'),
(7, 'testing123', '$2y$10$KInBtzrA88pk7UMuskKpI.rljCYcB65T.Z0Y8DkPrDAXIfMWPAs3i', 'testing123@gmail.com', '2025-04-14 05:16:21', '2025-04-14 05:16:21', 'user'),
(8, 'testing1', '$2y$10$LfH7IMkx4pxL35JNfjfVuuU3Fz3mg5ZBBa.jcDK71ZruYK/eJ85MG', 'testing1@gmail.com', '2025-04-15 09:16:44', '2025-04-15 09:16:44', 'user'),
(9, 'testing2', '$2y$10$o4rQ1mO0aTty59z8cOL2v.UEBB1Uh05fRA11f7QtKtw1HPTcOfsX2', 'testing2@gmail.com', '2025-04-15 13:43:09', '2025-04-15 13:43:09', 'user'),
(10, 'testing3', '$2y$10$M5kSWmaiC1DW2tpbUqbItuJyQJLrEh6VGWywfGqJ71hNfgYvoKYFu', 'testing3@gmail.com', '2025-04-15 14:48:50', '2025-04-15 14:48:50', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discussion_id` (`discussion_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `competition_entries`
--
ALTER TABLE `competition_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `competition_entries_comments`
--
ALTER TABLE `competition_entries_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entry_id` (`entry_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`entry_id`),
  ADD KEY `entry_id` (`entry_id`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`discussion_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_meal_plans`
--
ALTER TABLE `saved_meal_plans`
  ADD PRIMARY KEY (`saved_plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `competition_entries`
--
ALTER TABLE `competition_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `competition_entries_comments`
--
ALTER TABLE `competition_entries_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `saved_meal_plans`
--
ALTER TABLE `saved_meal_plans`
  MODIFY `saved_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `steps`
--
ALTER TABLE `steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD CONSTRAINT `comment_reactions_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competitions`
--
ALTER TABLE `competitions`
  ADD CONSTRAINT `competitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_entries`
--
ALTER TABLE `competition_entries`
  ADD CONSTRAINT `competition_entries_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_entries_ibfk_3` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_entries_comments`
--
ALTER TABLE `competition_entries_comments`
  ADD CONSTRAINT `competition_entries_comments_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `competition_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_entries_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussions_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `competition_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
