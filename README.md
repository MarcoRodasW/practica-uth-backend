# To-Do List API with JWT Authentication

A robust RESTful API built with PHP 8.x, MySQL, and JWT authentication for managing personal to-do lists.

## Features

- User registration and authentication
- JWT-based stateless authentication
- CRUD operations for to-do items
- Input validation and sanitization
- SQL injection protection with prepared statements
- Stored procedures for database operations
- PSR-4 autoloading

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Composer

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your database settings in `.env`

5. Create database and tables:
   ```bash
   mysql -u your_username -p < sql/schema.sql
   ```

6. Start your web server pointing to the `public` directory

## API Endpoints

### Authentication

#### Register User
- **POST** `/api/v1/auth/register`
- **Body**: `{"username": "john_doe", "email": "john@example.com", "password": "SecurePass123!", "confirm_password": "SecurePass123!"}`

#### Login
- **POST** `/api/v1/auth/login`
- **Body**: `{"email": "john@example.com", "password": "SecurePass123!"}`

#### Update Profile
- **PUT** `/api/v1/auth/profile`
- **Headers**: `Authorization: Bearer <token>`
- **Body**: `{"username": "new_username", "email": "new@example.com"}`

#### Logout
- **POST** `/api/v1/auth/logout`
- **Headers**: `Authorization: Bearer <token>`

### To-Do Management

#### Create To-Do
- **POST** `/api/v1/todos`
- **Headers**: `Authorization: Bearer <token>`
- **Body**: `{"title": "Task title", "description": "Task description", "dueDate": "2024-12-31 23:59:59", "status": "Pending"}`

#### Get All To-Dos
- **GET** `/api/v1/todos`
- **Headers**: `Authorization: Bearer <token>`

#### Get Single To-Do
- **GET** `/api/v1/todos/{id}`
- **Headers**: `Authorization: Bearer <token>`

#### Update To-Do
- **PUT** `/api/v1/todos/{id}`
- **Headers**: `Authorization: Bearer <token>`
- **Body**: `{"title": "Updated title", "status": "Completed"}`

#### Delete To-Do
- **DELETE** `/api/v1/todos/{id}`
- **Headers**: `Authorization: Bearer <token>`

### Health Check Endpoints

#### System Health Check
- **GET** `/api/v1/health`
- **Description**: Complete system health check including database, tables, and stored procedures
- **Authentication**: None required

#### Database Connection Test
- **GET** `/api/v1/health/database`
- **Description**: Test database connection and table status
- **Authentication**: None required

## Health Check Response Examples

### Successful Health Check
```json
{
  "success": true,
  "message": "System health check passed",
  "data": {
    "status": "OK",
    "timestamp": "2024-06-06 12:23:05",
    "php_version": "8.2.0",
    "database_connection": "OK",
    "mysql_version": "8.0.33",
    "tables": {
      "users": {
        "exists": true,
        "row_count": 5
      },
      "todos": {
        "exists": true,
        "row_count": 12
      }
    },
    "stored_procedures": {
      "CreateUser": true,
      "GetUserByEmail": true,
      "UpdateUserProfile": true,
      "CreateTodo": true,
      "GetUserTodos": true,
      "GetTodoById": true,
      "UpdateTodo": true,
      "DeleteTodo": true
    }
  }
}
```

### Database Connection Test
```json
{
  "success": true,
  "message": "Database connection successful",
  "data": {
    "database_connection": "OK",
    "tables_status": {
      "users": {
        "exists": true,
        "row_count": 5
      },
      "todos": {
        "exists": true,
        "row_count": 12
      }
    },
    "timestamp": "2024-06-06 12:23:05"
  }
}
```

### Failed Connection Example
```json
{
  "success": false,
  "message": "Database connection failed",
  "errors": [
    "SQLSTATE[HY000] [2002] Connection refused"
  ]
}
```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": ["Detailed error messages"]
}
```

## Security Features

- Password hashing with PHP's `password_hash()`
- JWT tokens for stateless authentication
- Input validation and sanitization
- SQL injection prevention with prepared statements
- CORS headers for cross-origin requests

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error