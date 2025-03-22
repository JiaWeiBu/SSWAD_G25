# SSWAD_G25
 Server-Side Web Application Development

## Project Background
This project is an interactive recipe and culinary web application designed to allow users to manage recipes, plan meals, participate in cooking competitions, and engage with a community of culinary enthusiasts. The system provides tools to create and organize recipes, schedule meals, and encourage community interaction through discussions and competitions.

## Modules

### 1. Recipe Management Module
- **Functionality**: Users can create, store, and manage recipes.
- **Features**:
  - Add ingredients and preparation steps.
  - Categorize recipes by cuisine.
  - Search for recipes.
- **Files**:
  - `views/create_recipe.php`
  - `views/submit_entry.php`
  - `models/Recipe.php`
  - `controllers/RecipeController.php`

### 2. Meal Planning Module
- **Functionality**: Users can plan meals by selecting recipes or adding custom meal entries.
- **Features**:
  - Schedule meals for specific days/duration.
  - Modify meal plans.
  - Save meal plans for future use.
- **Files**:
  - `models/MealPlan.php`
  - `controllers/MealPlanController.php`

### 3. Community Engagement Module
- **Functionality**: Users can interact through discussions, comments, and ratings.
- **Features**:
  - Share cooking tips.
  - Provide feedback on recipes.
  - Rate submissions.
- **Files**:
  - `views/community.php`
  - `models/CommunityPost.php`
  - `controllers/CommunityController.php`

### 4. Cooking Competition Module
- **Functionality**: Users can participate in recipe contests by submitting their recipes to engage in friendly competition.
- **Features**:
  - Submit recipes.
  - View other entries.
  - Vote for favorites.
  - View the results.
- **Files**:
  - `views/competitions.php`
  - `views/competition_details.php`
  - `views/vote.php`
  - `models/Competition.php`
  - `models/CompetitionEntry.php`
  - `models/Vote.php`
  - `controllers/CompetitionController.php`
  - `controllers/EntryController.php`
  - `controllers/VoteController.php`

### User Management Module
- **Functionality**: Manages user accounts, enabling personalized access for both regular users and admins.
- **Features**:
  - User registration and login.
  - Profile management.
  - Authentication and authorization.
- **Files**:
  - `views/register.php`
  - `views/login.php`
  - `views/profile.php`
  - `views/dashboard.php`
  - `controllers/AuthController.php`
  - `controllers/UserController.php`
  - `models/User.php`

## Database
- **Database Configuration**: The database is configured in `config.php`.
- **Database Connection**: Managed in `db.php`.
- **Database Schema**: Defined in `Relation.sql`.

## Additional Files
- **Styles**: `styles.css` for the UI design.
- **Index Page**: `index.php` as the landing page.
- **Logout**: `logout.php` to handle user logout.

## Development Tools
- **XAMPP**: For local server setup.
- **MySQL phpMyAdmin**: For database management.

## Report Requirements
- **Site Hierarchy and Navigation**: Prepare a site map or website hierarchy diagram.
- **System Flowcharts**: Prepare system flowcharts for specific modules.
- **Database Structure**: Provide an overview using Database Schema Diagram or Entity Relationship Diagram.
- **Functional Requirements**: Outline core features and functionalities of the system.

## Group Assignment
- **Group Members**: 4 members per group, each responsible for one module.
- **Integration**: Group effort for integrating modules.
- **Group Leader**: Elected to coordinate and ensure completion of the assignment.

## Important Notes
- **Presentation**: Demonstrate the work and understanding during the presentation.
- **Report**: Include a title page, site hierarchy, system flowcharts, database structure, and functional requirements.
