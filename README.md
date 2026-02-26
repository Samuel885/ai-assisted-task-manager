AI-Assisted Task Management Platform
Overview

The AI-Assisted Task Management Platform is a web-based system designed to help students manage academic tasks efficiently. The system provides secure authentication, full CRUD functionality, task organization features, and a rule-based workload recommendation module that suggests which task to prioritize based on urgency and difficulty.

Features
* Authentication
* User registration
* Secure login
* Logout with session termination
* Session-protected pages

Task Management (CRUD)
* Add new tasks
* View task list
* Edit existing tasks
* Delete tasks
* Tasks linked to the logged-in user only
Workload Recommendation System
* Rule-based prioritization logic
  
Workload score calculated using:
* Priority
* Difficulty
* Due date proximity
* Estimated hours
* Displays recommended task to complete first
* Completed tasks excluded from recommendations

Task Organization
* Sorting by:
   * Due date
   * Priority
   * Status

* Filtering by:
   * Priority
   * Status
* Reset filter functionality

System Testing
* Functional testing for authentication
* CRUD operation validation
* Workload scoring verification
* Sorting and filtering validation
* Session and access control verification

Technologies Used
* PHP
* MySQL
* XAMPP
* HTML
* CSS

Database Schema
* users table
* user_id
* full_name
* email
* password (hashed)
* created_at

tasks table
* task_id
* user_id (foreign key)
* title
* description
* due_date
* priority
* difficulty
* estimated_hours
* status
* workload_score
* created_at
* updated_at

How to Run the Project

1. Install XAMPP.
2. Start Apache and MySQL.
3. Import taskbalance_db.sql into phpMyAdmin.
4. Place the project folder inside:

   C:\xampp\htdocs\taskbalance

5.Open your browser and access:

   http://localhost/taskbalance/login.php

Project Structure
* config.php – database connection and session handling
* register.php – user registration
* login.php – authentication
* logout.php – session termination
* dashboard.php – user dashboard
* add_task.php – create task
* view_tasks.php – view, sort, and filter tasks
* edit_task.php – update task
* delete_task.php – remove task
* recommendations.php – workload prioritization module
* style.css – system styling
* taskbalance_db.sql – database schema
* Testing.pdf – testing documentation
