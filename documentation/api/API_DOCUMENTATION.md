# API Documentation

## Base URL
```
http://your-domain.com/api/v1
```

## Endpoints

### Products

#### Get All Products
```http
GET /products
```

**Query Parameters:**
- `search` (optional): Search by name or description
- `sort_by` (optional): Field to sort by (default: name)
- `sort_direction` (optional): Sort direction `asc` or `desc` (default: asc)
- `per_page` (optional): Number of items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/products?search=ayam&per_page=10"
```

**Response:**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Ayam Geprek",
        "description": "Ayam geprek dengan sambal khas",
        "price": "25000.00",
        "image": "ayam-geprek.jpg",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 25,
      "last_page": 3,
      "has_more": true
    }
  }
}
```

#### Get Single Product
```http
GET /products/{id}
```

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/products/1"
```

**Response:**
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "product": {
      "id": 1,
      "name": "Ayam Geprek",
      "description": "Ayam geprek dengan sambal khas",
      "price": "25000.00",
      "image": "ayam-geprek.jpg",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

### Orders

#### Get All Orders
```http
GET /orders
```

**Query Parameters:**
- `status` (optional): Filter by order status
  - Available values: `draft`, `pending`, `confirm`, `processing`, `completed`, `cancelled`
- `date_from` (optional): Filter orders from date (YYYY-MM-DD)
- `date_to` (optional): Filter orders to date (YYYY-MM-DD)
- `customer_name` (optional): Search by customer name
- `sort_by` (optional): Field to sort by (default: order_date_time)
- `sort_direction` (optional): Sort direction `asc` or `desc` (default: desc)
- `per_page` (optional): Number of items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/orders?status=completed&per_page=10"
```

**Response:**
```json
{
  "success": true,
  "message": "Orders retrieved successfully",
  "data": {
    "orders": [
      {
        "id": 1,
        "customer_name": "John Doe",
        "customer_phone": "+62812345678",
        "customer_address": "Jl. Example No. 123",
        "status": "completed",
        "order_date_time": "2024-01-01T12:00:00.000000Z",
        "payment_method": "cash",
        "delivery_method": "dine-in",
        "total_price": "50000.00",
        "device": {
          "id": 1,
          "name": "Device 1"
        },
        "items": [
          {
            "id": 1,
            "product": {
              "id": 1,
              "name": "Ayam Geprek",
              "price": "25000.00"
            },
            "quantity": 2,
            "subtotal": "50000.00"
          }
        ],
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 50,
      "last_page": 5,
      "has_more": true
    },
    "filters": {
      "available_statuses": [
        "draft",
        "pending",
        "confirm",
        "processing",
        "completed",
        "cancelled"
      ]
    }
  }
}
```

#### Get Single Order
```http
GET /orders/{id}
```

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/orders/1"
```

**Response:**
```json
{
  "success": true,
  "message": "Order retrieved successfully",
  "data": {
    "order": {
      "id": 1,
      "customer_name": "John Doe",
      "customer_phone": "+62812345678",
      "customer_address": "Jl. Example No. 123",
      "status": "completed",
      "order_date_time": "2024-01-01T12:00:00.000000Z",
      "payment_method": "cash",
      "delivery_method": "dine-in",
      "total_price": "50000.00",
      "device": {
        "id": 1,
        "name": "Device 1"
      },
      "items": [
        {
          "id": 1,
          "product": {
            "id": 1,
            "name": "Ayam Geprek",
            "description": "Ayam geprek dengan sambal khas",
            "price": "25000.00",
            "image": "ayam-geprek.jpg"
          },
          "quantity": 2,
          "subtotal": "50000.00"
        }
      ],
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:30:00.000000Z"
    }
  }
}
```

## Response Format

All API responses follow this structure:

```json
{
  "success": boolean,
  "message": "string",
  "data": object | null
}
```

## Error Handling

In case of errors, the response will include:

```json
{
  "success": false,
  "message": "Error description",
  "errors": object | null
}
```

## Notes

- All timestamps are in ISO 8601 format (UTC)
- All prices are in decimal format with 2 decimal places
- The API is public and does not require authentication
- Pagination is supported on list endpoints