CREATE OR REPLACE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE Users
ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL;

CREATE OR REPLACE TABLE Recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    cuisine VARCHAR(100),
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE Ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

CREATE OR REPLACE TABLE RecipeIngredients (
    recipe_id INT,
    ingredient_id INT,
    quantity VARCHAR(50),
    PRIMARY KEY (recipe_id, ingredient_id),
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES Ingredients(ingredient_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE MealPlans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    recipe_id INT,
    meal_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE CommunityPosts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE Competitions (
    competition_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE CompetitionEntries (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    competition_id INT,
    user_id INT,
    recipe_id INT,
    submission TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (competition_id) REFERENCES Competitions(competition_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id) ON DELETE CASCADE
);

CREATE OR REPLACE TABLE Votes (
    vote_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    entry_id INT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (entry_id) REFERENCES CompetitionEntries(entry_id) ON DELETE CASCADE
);

-- sample text data
INSERT INTO Ingredients (name, description) VALUES
('Lettuce', 'Fresh lettuce leaves'),
('Tomato', 'Sliced tomatoes'),
('Cucumber', 'Sliced cucumbers'),
('Turkey', 'Sliced turkey breast'),
('Ham', 'Sliced ham'),
('Cheddar Cheese', 'Sliced cheddar cheese'),
('Mayonnaise', 'Creamy mayonnaise'),
('Mustard', 'Yellow mustard'),
('Foot-long Bread', 'Freshly baked foot-long bread');

INSERT INTO Recipes (user_id, title, cuisine, instructions) VALUES
(1, 'Turkey Sandwich', 'American', 'Layer turkey, lettuce, tomato, and cucumber on bread. Add mayonnaise and mustard.'),
(1, 'Ham Sandwich', 'American', 'Layer ham, lettuce, tomato, and cucumber on bread. Add mayonnaise and mustard.'),
(2, 'Turkey and Ham Sandwich', 'American', 'Layer turkey, ham, lettuce, tomato, and cucumber on bread. Add mayonnaise and mustard.'),
(2, 'Veggie Sandwich', 'American', 'Layer lettuce, tomato, cucumber, and cheese on bread. Add mayonnaise and mustard.'),
(3, 'Cheese Sandwich', 'American', 'Layer cheese, lettuce, tomato, and cucumber on bread. Add mayonnaise and mustard.');

INSERT INTO RecipeIngredients (recipe_id, ingredient_id, quantity) VALUES
(1, 1, '1 cup'),
(1, 2, '1 cup'),
(1, 3, '1 cup'),
(1, 4, '200g'),
(1, 7, '2 tbsp'),
(1, 8, '1 tbsp'),
(1, 9, '1 foot-long'),

(2, 1, '1 cup'),
(2, 2, '1 cup'),
(2, 3, '1 cup'),
(2, 5, '200g'),
(2, 7, '2 tbsp'),
(2, 8, '1 tbsp'),
(2, 9, '1 foot-long'),

(3, 1, '1 cup'),
(3, 2, '1 cup'),
(3, 3, '1 cup'),
(3, 4, '100g'),
(3, 5, '100g'),
(3, 7, '2 tbsp'),
(3, 8, '1 tbsp'),
(3, 9, '1 foot-long'),

(4, 1, '1 cup'),
(4, 2, '1 cup'),
(4, 3, '1 cup'),
(4, 6, '100g'),
(4, 7, '2 tbsp'),
(4, 8, '1 tbsp'),
(4, 9, '1 foot-long'),

(5, 1, '1 cup'),
(5, 2, '1 cup'),
(5, 3, '1 cup'),
(5, 6, '200g'),
(5, 7, '2 tbsp'),
(5, 8, '1 tbsp'),
(5, 9, '1 foot-long');

-- Insert data into Users table
INSERT INTO Users (user_id, username, email, password_hash, role, created_at) VALUES
(1, 'Admin', 'Admin@utar.my', '$2y$10$BNyytPQTylj1kgIUhs6vNO3lPZDVwJzBIYX.JLDvjXuNrUQdIqeMi', 'user', '2025-03-22 06:45:25'),
(2, 'Elaina', 'Elaina@utar.my', '$2y$10$TJLNpsacw9rA/NN1/dkbs.oMdlPftYGJoPMSebbsScRFuDKwBntdq', 'user', '2025-03-22 06:45:40'),
(3, 'JohnDoe', 'JohnDoe@utar.my', '$2y$10$abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd12', 'user', '2025-03-22 06:46:00');

-- Insert data into Competitions table
INSERT INTO Competitions (competition_id, title, description, start_date, end_date, created_by) VALUES
(1, 'Chicken Nuggets', 'Chicken Nugget Numba 1', '2025-03-22', '2025-03-30', 1);