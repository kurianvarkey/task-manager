# Task Manager API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All protected endpoints require an API key in the Authorization header:

```
Authorization: Bearer YOUR_API_KEY (token)
```

## Response Format

All API responses follow this standard format:

```json
{
  "status": "success",
  "data":
  {
    ...
 }
}
```

## Error Response Format

```json
{
    "status": "failed",
    "errors": [
        {
            "type": "system",
            "code": 404,
            "message": "User not found."
        }
    ]
}
```

---

## Authentication Endpoints

### Welcome Message

Get API welcome message and version information.

**Endpoint:** `GET /api/`

**Response:**

```json
{
    "code": 200,
    "message": "Welcome to Task Manager 1.0"
}
```

### User Signup

Register a new user account.

**Endpoint:** `POST /api/signup`

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| name | string | Yes | User's full name | Max 100 characters |
| email | string | Yes | User's email address | Valid email format, max 100 characters |
| password | string | Yes | User's password | Min 8 characters |
| role | string | Yes | User role | `admin`, `user` |

**Request Example:**

```json
{
    "email": "test@test.com",
    "name": "Tester",
    "password": "12345678",
    "password_confirmation": "12345678",
    "role": "admin"
}
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "email": "test@test.com",
        "user_created": true
    }
}
```

### User Login

Authenticate user and get access token.

**Endpoint:** `POST /api/login`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| password | string | Yes | User's password |

**Request Example:**

```json
{
    "email": "test@test.com",
    "password": "password123"
}
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "id": 3,
        "name": "Tester",
        "email": "test@test.com",
        "role": "admin",
        "token": "2d8587f3-3fe2-4491-985f-b6846949659b-1758035229"
    }
}
```

---

## Task Management Endpoints

### Create Task

Create a new task.

**Endpoint:** `POST /api/tasks`
**Authentication:** Required

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| title | string | Yes | Task title | Min 5, max 100 characters |
| description | string | No | Task description | Text |
| status | string | No | Task status | `pending`, `inprogress`, `completed` |
| priority | string | No | Task priority | `low`, `medium`, `high` |
| due_date | string | No | Due date | Date format (YYYY-MM-DD), must be today or future |
| assigned_to | object | No | Assigned user | `{"id": user_id}` |
| metadata | object | No | Additional metadata | JSON object |
| tags | array | No | Associated tags | Array of `{"id": tag_id}` |

**Request Example:**

```json
{
    "title": "Task 1",
    "description": "description",
    "status": "pending",
    "priority": "medium",
    "due_date": "2025-10-01",
    "assigned_to": {
        "id": 1
    },
    "metadata": {
        "meta": "test"
    },
    "tags": [
        {
            "id": 2
        }
    ]
}
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "title": "Task 1",
        "description": "description",
        "status": "pending",
        "priority": "medium",
        "due_date": "2025-10-01",
        "assigned_to": {
            "id": 1,
            "name": "Admin User",
            "email": "test@test.com"
        },
        "metadata": {
            "meta": "test"
        },
        "tags": [
            {
                "id": 2,
                "name": "Tag 1",
                "color": "#ff0000"
            }
        ],
        "version": 1,
        "created_at": "2025-09-16T15:14:02.000000Z",
        "updated_at": "2025-09-16T15:14:02.000000Z"
    }
}
```

### Get All Tasks

Retrieve a list of tasks with optional filtering.

**Endpoint:** `GET /api/tasks`
**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| status | string | No | Filter by status | `pending`, `inprogress`, `completed` |
| priority | string | No | Filter by priority | `low`, `medium`, `high` |
| assigned_to | integer | No | Filter by assigned user ID | User ID |
| tags | string | No | Filter by tag IDs | Comma-separated tag IDs (e.g., "1,2,3") |
| due_date_range | string | No | Filter by due date range | Date range format - date1,date2 (e.g., "2025-01-01,2025-12-31") |
| keyword | string | No | Search in title/description | Search term |
| only_deleted | boolean | No | Show only deleted tasks | `true`, `false` |
| limit | integer | No | Number of results per page | Default: 25, Max: 50 |
| page | integer | No | Page number | Default: 1 |

**Request Example:**

```
GET /api/tasks?status=pending&priority=high&limit=10&page=1
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "meta": {
            "total": 1,
            "per_page": 25,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 1
        },
        "results": [
            {
                "id": 1,
                "title": "Task 1",
                "description": "description",
                "status": "pending",
                "priority": "medium",
                "due_date": "2025-10-01",
                "assigned_to": {
                    "id": 1,
                    "name": "Admin User",
                    "email": "test@test.com"
                },
                "metadata": {
                    "meta": "test"
                },
                "tags": [
                    {
                        "id": 2,
                        "name": "Tag 1",
                        "color": "#ff0000"
                    }
                ],
                "version": 1,
                "created_at": "2025-09-16T15:14:02.000000Z",
                "updated_at": "2025-09-16T15:14:02.000000Z"
            }
        ]
    }
}
```

### Get Single Task

Retrieve a specific task by ID.

**Endpoint:** `GET /api/tasks/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

**Response:**

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "title": "Task 1",
        "description": "description",
        "status": "pending",
        "priority": "medium",
        "due_date": "2025-10-01",
        "assigned_to": {
            "id": 1,
            "name": "Admin User",
            "email": "test@test.com"
        },
        "metadata": {
            "meta": "test"
        },
        "tags": [
            {
                "id": 2,
                "name": "Tag 1",
                "color": "#ff0000"
            }
        ],
        "version": 1,
        "created_at": "2025-09-16T15:14:02.000000Z",
        "updated_at": "2025-09-16T15:14:02.000000Z"
    }
}
```

### Update Task (Full Update)

Update all fields of a task.

**Endpoint:** `PUT /api/tasks/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| title | string | Yes | Task title | Min 5, max 100 characters |
| description | string | No | Task description | Text |
| status | string | Yes | Task status | `pending`, `inprogress`, `completed` |
| priority | string | Yes | Task priority | `low`, `medium`, `high` |
| due_date | string | No | Due date | Date format (YYYY-MM-DD) |
| assigned_to | object | No | Assigned user | `{"id": user_id}` |
| metadata | object | No | Additional metadata | JSON object |
| tags | array | No | Associated tags | Array of `{"id": tag_id}` |
| version | integer | Yes | Current version for optimistic locking | Version number |

**Request Example:**

```json
{
    "title": "Task 1- updated",
    "description": "description",
    "status": "pending",
    "priority": "medium",
    "due_date": "2025-10-01",
    "assigned_to": {
        "id": 1
    },
    "metadata": {
        "meta": "test"
    },
    "version": 1,
    "tags": [
        {
            "id": 2
        }
    ]
}
```

### Update Task (Partial Update)

Update specific fields of a task.

**Endpoint:** `PATCH /api/tasks/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| title | string | No | Task title | Min 5, max 100 characters |
| description | string | No | Task description | Text |
| status | string | No | Task status | `pending`, `inprogress`, `completed` |
| priority | string | No | Task priority | `low`, `medium`, `high` |
| due_date | string | No | Due date | Date format (YYYY-MM-DD) |
| assigned_to | object | No | Assigned user | `{"id": user_id}` |
| metadata | object | No | Additional metadata | JSON object |
| tags | array | No | Associated tags | Array of `{"id": tag_id}` |

**Request Example:**

```json
{
    "status": "completed",
    "priority": "medium"
}
```

### Delete Task

Soft delete a task.

**Endpoint:** `DELETE /api/tasks/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

**Response:**

```json
204 No Content
```

### Toggle Task Status

Toggle task status between pending/inprogress/completed.

**Endpoint:** `PATCH /api/tasks/{id}/toggle-status`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

### Restore Task

Restore a soft-deleted task.

**Endpoint:** `PATCH /api/tasks/{id}/restore`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

### Get Task Logs

Retrieve activity logs for a specific task.

**Endpoint:** `GET /api/tasks/{id}/logs`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Task ID |

**Response:**

```json
{
    "status": "success",
    "data": {
        "meta": {
            "total": 3,
            "per_page": 25,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 3
        },
        "results": [
            {
                "task_id": 1,
                "operation_type": "updated",
                "changes": {
                    "status": "inprogress",
                    "updated_at": "2025-09-16 15:16:31"
                },
                "created_by": {
                    "id": 3,
                    "name": "Tester",
                    "email": "test@test.com"
                },
                "created_at": "2025-09-16T15:16:31.000000Z"
            },
            {
                "task_id": 1,
                "operation_type": "updated",
                "changes": {
                    "title": "Task 1- updated",
                    "version": 2,
                    "updated_at": "2025-09-16 15:15:49"
                },
                "created_by": {
                    "id": 3,
                    "name": "Tester",
                    "email": "test@test.com"
                },
                "created_at": "2025-09-16T15:15:49.000000Z"
            },
            {
                "task_id": 1,
                "operation_type": "created",
                "changes": {
                    "status": "pending",
                    "priority": "medium",
                    "version": 1,
                    "title": "Task 1",
                    "description": "description",
                    "due_date": "2025-10-01",
                    "assigned_to": 1,
                    "metadata": {
                        "meta": "test"
                    },
                    "updated_at": "2025-09-16T15:14:02.000000Z",
                    "created_at": "2025-09-16T15:14:02.000000Z",
                    "id": 1
                },
                "created_by": {
                    "id": 3,
                    "name": "Tester",
                    "email": "test@test.com"
                },
                "created_at": "2025-09-16T15:14:02.000000Z"
            }
        ]
    }
}
```

---

## Tag Management Endpoints

### Create Tag

Create a new tag.

**Endpoint:** `POST /api/tags`
**Authentication:** Required

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| name | string | Yes | Tag name | Max 100 characters, must be unique |
| color | string | No | Tag color | Hex color format (e.g., #FF0000) |

**Request Example:**

```json
{
    "name": "Tag 1",
    "color": "#ff0000"
}
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Tag 1",
        "color": "#ff0000",
        "created_at": "2025-09-16T15:09:28.000000Z",
        "updated_at": "2025-09-16T15:09:28.000000Z"
    }
}
```

### Get All Tags

Retrieve a list of tags with optional filtering.

**Endpoint:** `GET /api/tags`
**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------| -------------|
| name | string | No | Filter by tag name | Search term |
| limit | integer | No | Number of results per page | Default: 25, Max: 50 |
| page | integer | No | Page number | Default: 1 |

**Request Example:**

```
GET /api/tags?name=doc&limit=10
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "meta": {
            "total": 1,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 1
        },
        "results": [
            {
                "id": 1,
                "name": "Tag 1",
                "color": "#ff0000",
                "created_at": "2025-09-16T15:09:28.000000Z",
                "updated_at": "2025-09-16T15:09:28.000000Z"
            }
        ]
    }
}
```

### Get Single Tag

Retrieve a specific tag by ID.

**Endpoint:** `GET /api/tags/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Tag ID |

**Response:**

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Tag 1",
        "color": "#ff0000",
        "created_at": "2025-09-16T15:09:28.000000Z",
        "updated_at": "2025-09-16T15:09:28.000000Z"
    }
}
```

### Update Tag (Full Update)

Update all fields of a tag.

**Endpoint:** `PUT /tags/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Tag ID |

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| name | string | Yes | Tag name | Max 100 characters, must be unique |
| color | string | No | Tag color | Hex color format |

**Request Example:**

```json
{
    "name": "API Documentation",
    "color": "#10B981"
}
```

### Update Tag (Partial Update)

Update specific fields of a tag.

**Endpoint:** `PATCH /api/tags/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Tag ID |

**Request Parameters:**
| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| name | string | No | Tag name | Max 100 characters, must be unique |
| color | string | No | Tag color | Hex color format |

**Request Example:**

```json
{
    "color": "#EF4444"
}
```

### Delete Tag

Delete a tag.

**Endpoint:** `DELETE /api/tags/{id}`
**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Tag ID |

**Response:**

```json
204 No Content
```

---

## User Management Endpoints

### Get All Users

Retrieve a list of users with optional filtering.

**Endpoint:** `GET /api/users`
**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | No | Filter by email |
| name | string | No | Filter by name |
| limit | integer | No | Number of results per page |
| page | integer | No | Page number |

**Request Example:**

```
GET /api/users?name=john&limit=10
```

**Response:**

```json
{
    "status": "success",
    "data": {
        "meta": {
            "total": 1,
            "per_page": 25,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 3
        },
        "results": [
            {
                "id": 1,
                "name": "Admin User",
                "email": "test@test.com",
                "role": "admin"
            }
        ]
    }
}
```

---

## Error Codes

| HTTP Status | Code | Description           |
| ----------- | ---- | --------------------- |
| 200         | 200  | Success               |
| 201         | 201  | Created               |
| 204         | 204  | No Content            |
| 400         | 400  | Bad Request           |
| 401         | 401  | Unauthorized          |
| 403         | 403  | Forbidden             |
| 404         | 404  | Not Found             |
| 422         | 422  | Validation Error      |
| 429         | 429  | Too Many Requests     |
| 500         | 500  | Internal Server Error |

---

## Rate Limiting

Authentication endpoints (`/login`, `/signup`) are rate-limited to prevent abuse:

-   **Limit:** 60 requests per minute per IP address
-   **Headers:** Rate limit information is included in response headers

---

## Examples

### Complete Task Creation Flow

1. **Create a tag:**

```bash
curl -X POST http://localhost:8000/api/tags \
  -H "Authorization: Bearer 12345678-1234-1234-1234-123456789012" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Bug Fix",
    "color": "#EF4444"
  }'
```

2. **Create a task with the tag:**

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer 12345678-1234-1234-1234-123456789012" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Fix login validation bug",
    "description": "The login form is not validating email format correctly",
    "status": "pending",
    "priority": "high",
    "due_date": "2024-12-31",
    "tags": [{"id": 1}]
  }'
```

3. **Update task status:**

```bash
curl -X PATCH http://localhost:8000/api/tasks/1/toggle-status \
  -H "Authorization: Bearer 12345678-1234-1234-1234-123456789012"
```

4. **Get task logs:**

```bash
curl -X GET http://localhost:8000/api/tasks/1/logs \
  -H "Authorization: Bearer 12345678-1234-1234-1234-123456789012"
```

---

## Postman Collection

You can import this API documentation into Postman by creating a new collection and adding the endpoints with the examples provided above.

## Support

For API support and questions, please refer to the main README.md file or create an issue in the repository.
