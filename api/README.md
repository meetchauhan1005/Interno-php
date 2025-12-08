# INTERNO API Documentation

Base URL: `http://localhost/interno-php/api`

## Authentication

### Login
```
POST /api/auth/index.php?action=login
Body: {
  "email": "admin@interno.com",
  "password": "password"
}
Response: {
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@interno.com",
    "role": "admin"
  }
}
```

### Register
```
POST /api/auth/index.php?action=register
Body: {
  "username": "john",
  "email": "john@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe"
}
Response: {
  "success": true,
  "id": 4
}
```

### Check Auth
```
GET /api/auth/index.php?action=check
Response: {
  "authenticated": true,
  "user": {...}
}
```

### Logout
```
GET /api/auth/index.php?action=logout
Response: {
  "success": true
}
```

---

## Products

### Get All Products
```
GET /api/products/index.php
Query Params:
  - category: Filter by category ID
  - search: Search by name
  - featured: Get featured products (1)
  - limit: Limit results (default: 100)

Example: /api/products/index.php?category=1&limit=10

Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "price": 35000.00,
      "image": "image.jpg",
      "category_name": "Bedroom"
    }
  ]
}
```

### Get Single Product
```
GET /api/products/index.php?id=1
Response: {
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "description": "...",
    "price": 35000.00
  }
}
```

### Create Product (Admin)
```
POST /api/products/index.php
Body: {
  "name": "New Product",
  "description": "Description",
  "price": 25000,
  "category_id": 1,
  "stock_quantity": 10
}
Response: {
  "success": true,
  "id": 9
}
```

### Update Product (Admin)
```
PUT /api/products/index.php
Body: {
  "id": 1,
  "name": "Updated Name",
  "price": 30000
}
Response: {
  "success": true
}
```

### Delete Product (Admin)
```
DELETE /api/products/index.php
Body: {
  "id": 1
}
Response: {
  "success": true
}
```

---

## Categories

### Get All Categories
```
GET /api/categories/index.php
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Bedroom",
      "slug": "bedroom"
    }
  ]
}
```

---

## Cart

### Get Cart
```
GET /api/cart/index.php
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "name": "Product Name",
      "price": 35000,
      "quantity": 2
    }
  ],
  "total": 70000
}
```

### Add to Cart
```
POST /api/cart/index.php
Body: {
  "product_id": 1,
  "quantity": 1
}
Response: {
  "success": true
}
```

### Update Cart
```
PUT /api/cart/index.php
Body: {
  "cart_id": 1,
  "quantity": 3
}
Response: {
  "success": true
}
```

### Remove from Cart
```
DELETE /api/cart/index.php
Body: {
  "cart_id": 1
}
Response: {
  "success": true
}
```

---

## Orders

### Get User Orders
```
GET /api/orders/index.php
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "total_amount": 35000,
      "status": "pending",
      "items": [...]
    }
  ]
}
```

### Create Order
```
POST /api/orders/index.php
Body: {
  "total_amount": 35000,
  "shipping_address": "123 Main St, City",
  "items": [
    {
      "product_id": 1,
      "product_name": "Product",
      "quantity": 1,
      "price": 35000
    }
  ]
}
Response: {
  "success": true,
  "order_id": 4
}
```

---

## Error Responses

All errors return:
```json
{
  "error": "Error message"
}
```

Status Codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 405: Method Not Allowed
- 409: Conflict
- 500: Server Error

---

## Testing with cURL

### Get Products
```bash
curl http://localhost/interno-php/api/products/index.php
```

### Login
```bash
curl -X POST http://localhost/interno-php/api/auth/index.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@interno.com","password":"password"}'
```

### Add to Cart
```bash
curl -X POST http://localhost/interno-php/api/cart/index.php \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":1}'
```
