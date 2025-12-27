# Fullstack Product Customization
This project allows product customization using a Laravel backend and Angular frontend.

## Tech Stack
- Backend: Laravel 10
- Frontend: Angular 15
- Database: MySQL (via XAMPP)

## Backend Setup
1. Go to `backend/custmize-item` folder
2. Run `composer install`
3. Copy `.env.example` to `.env` and set your DB credentials
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `php artisan serve` to start the backend

## Frontend Setup
1. Go to `frontend/frontend-customize-product` folder
2. Run `npm install`
3. Run `ng serve` to start the frontend

Access the frontend at http://localhost:4200 and the backend API at http://localhost:8000/api

## Folder Structure
- backend/custmize-item → Laravel backend
- frontend/frontend-customize-product → Angular frontend
## Author
Sumit Devloper

