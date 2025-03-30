# Laravel Passport Authentication API

A robust REST API authentication system built with Laravel and OAuth2 using Laravel Passport. This project provides secure user authentication with access tokens and refresh token functionality.

## Features

-   User registration and authentication
-   OAuth2 token-based authentication using Laravel Passport
-   Secure token refresh mechanism
-   Token revocation on logout
-   JSON response format for all endpoints

## Requirements

-   PHP >= 8.1
-   Laravel 10.x
-   Composer
-   MySQL/PostgreSQL

## Installation

1. Clone the repository

```bash
git clone <your-repository-url>
cd auth-api-laravel-passport
```

2. Install dependencies

```bash
composer install
```

3. Configure environment variables

```bash
cp .env.example .env
# Update database credentials in .env file
```

4. Generate application key

```bash
php artisan key:generate
```

5. Run migrations

```bash
php artisan migrate
```

6. Install Laravel Passport

```bash
php artisan passport:install
```

## API Endpoints

### Authentication

#### Register

```
POST /api/v1/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "your-password"
}
```

#### Login

```
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "your-password"
}
```

#### Logout

```
POST /api/v1/auth/logout
Authorization: Bearer {access_token}
```

#### Refresh Token

```
POST /api/v1/auth/refresh
# Refresh token is automatically handled through HTTP-only cookie
```

#### Get User Profile

```
GET /api/v1/user
Authorization: Bearer {access_token}
```

## Security

-   Access tokens are sent via Authorization header
-   Refresh tokens are handled securely through HTTP-only cookies
-   Passwords are hashed using bcrypt
-   Token revocation on logout

## Error Handling

The API returns appropriate HTTP status codes and error messages in JSON format:

```json
{
    "success": false,
    "message": "Error message here",
    "data": null
}
```
