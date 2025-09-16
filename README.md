# Task Manager

A task management REST API built with Laravel 12 and PHP 8.4. There is also a frontend application to demonstrate the usage of the API.

## Features

- Complete REST API for task management
- User authentication with API keys (token based authentication)
- Admin and user role management
- Docker containerization support
- SQLite or MySQL database integration
- Real-time logging and monitoring
- API documentation in Markdown format - API_DOCUMENTATION.md

## Scope of the project
- Develop REST API with best practices
- Implement indexes for better performance
- Implement validation and error handling
- Implement authentication and authorization
- Implement logging and monitoring
- Implement eager loading for better performance
- Soft delete & restore for tasks
- Audit logs for task with elloquent events

## Requirements

-   PHP 8.2 or higher
-   Composer
-   Docker and Docker Compose (for containerized setup)
-   SQLite or MySQL (Docker has mysql service)

## Postman Collection

You can find a Postman collection for testing the API endpoints at postman directory.


## Testing

Once you have set up the application, you can use the provided Postman collection to test the API endpoints or use the provided test script.

```bash
chmod +x test.sh
./test.sh
#or
bash test.sh
#or
php artisan test
```

For the scope of the project, I have only tested Tag and Task endpoints.

## API Endpoints

### Tag
- GET /tags – list all tags 
- POST /tags – create tag 
- PUT /tags/{id} – update tag 
- PATCH /tags/{id} – update tag partially
- DELETE /tags/{id} – delete tag


### Task

- GET /tasks – list all tasks. Filters: status, priority, assigned_to, due_date_range, tags, keyword (title/description)
- GET /tasks/{id} – get task by ID, including tags and assigned user 
- POST /tasks – create a new task, supports assigning tags and user 
- PUT /tasks/{id} – update a task (use optimistic locking with version) 
- PATCH /tasks/{id}/toggle-status – cycle status: pending → in_progress → completed → pending 
- DELETE /tasks/{id} – soft delete task 
- PATCH /tasks/{id}/restore – restore soft-deleted task


## Installation & Setup

### Option 1: Quick Setup with Docker (Recommended)

1. **Clone the repository**

    ```bash
    git clone <your-repository-url>
    cd task-manager
    ```

2. **Run the automated setup script**

    ```bash
    chmod +x setup.sh
    ./setup.sh
    #or
    bash test.sh
    ```

    The setup script will:

    - Copy environment files
    - Build and start Docker containers
    - Generate application key
    - Run database migrations
    - Set up the application

3. **Access the application**
    - API: http://localhost:8000/api
    - Frontend: http://localhost:8080
    - The application will be ready to use!

### Option 2: Manual Docker Setup

1. **Clone and prepare environment**

    ```bash
    git clone <your-repository-url>
    cd task-manager
    cp .env.example .env
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Start Docker containers**

    ```bash
    docker-compose up -d
    ```

4. **Setup application**
    ```bash
    docker-compose exec php-task-manager php artisan key:generate
    docker-compose exec php-task-manager php artisan migrate
    ```

### Option 3: Local Development Setup

1. **Clone and setup**

    ```bash
    git clone <your-repository-url>
    cd task-manager
    cp .env.example .env
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Configure database**

    - Update `.env` file with your database credentials
    - For SQLite (default): No additional setup needed
    - For MySQL: Update DB_CONNECTION, DB_HOST, DB_DATABASE, etc.
    - By default, SQLite is used
    - uncomment the following lines or adjust them:

    ```bash
    #DB_CONNECTION=mysql
    #DB_HOST=mysqldb
    #DB_DATABASE=task_manager
    ```

4. **Generate application key and migrate**

    ```bash
    php artisan key:generate
    php artisan migrate
    ```

5. **Start the development server**
    ```bash
    php artisan serve
    ```

## Environment Configuration

The application uses environment variables for configuration. Key variables include:

```env
APP_NAME="Task Manager"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite  # or mysql for Docker setup
```

## API Authentication

The application uses API key (token) for authentication:

Include the API key in your requests:

```bash
curl -H "Authorization: Bearer YOUR_API_KEY" http://localhost:8000/api/tasks
```

## Development Commands

### Running Tests

```bash
# Local
php artisan test

# Docker
docker-compose exec php-task-manager php artisan test

# Or use the test script
./test.sh
#or
bash test.sh
```

### Code Formatting

```bash
# Run Laravel Pint for code formatting
./vendor/bin/pint
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## Docker Services

The application runs the following Docker services:

-   **nginx**: Web server (port 8000)
-   **php-task-manager**: PHP-FPM application (port 9000)
-   **mysqldb**: MySQL database (port 3306)

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Execute commands in containers
docker-compose exec php-task-manager php artisan migrate
docker-compose exec mysqldb mysql -u root -p
```

## Project Structure

```
├── app/                  # Application logic
├── config/               # Configuration files
├── database/             # Migrations, factories
├── public/               # Public assets
├── resources/            # Views, frontend assets
├── routes/               # Route definitions
├── tests/                # Test files
├── .docker/              # Docker configuration
├── docker-compose.yml    # Docker services definition
├── setup.sh              # Automated setup script
└── test.sh               # Test runner script
```

## Troubleshooting

### Common Issues

1. **Database connection errors**

    ```bash
    # Wait for MySQL to be ready, then run:
    docker-compose exec php-task-manager php artisan migrate
    ```

2. **Permission issues**

    ```bash
    # Fix storage permissions
    chmod -R 775 storage bootstrap/cache
    ```

3. **Docker build issues**

    ```bash
    # Rebuild containers
    docker-compose down
    docker-compose up -d --build
    ```

4. **Frontend assets not loading**
    ```bash
    # Rebuild frontend assets
    npm run build
    ```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions, please contact me.
