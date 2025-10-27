# Lawen Wasel API Documentation

**Base URL**: `http://localhost:8000/api`  
**Authentication**: Bearer Token (Laravel Sanctum)

---

## Quick Start

### 1. Register & Login

**Register**:
```bash
curl -X POST http://localhost:8000/api/register/start \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "role": "passenger"
  }'
```

**Login**:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "john@example.com",
    "password": "password123"
  }'
```

### 2. Use Token

Include the token in all requests:
```bash
Authorization: Bearer {your_token}
```

---

## Authentication Endpoints

### POST /register/start
Start user registration process.

**Request**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "role": "passenger",
    "gender": "male"
}
```

**Response** (200):
```json
{
    "message": "Verification code sent to your email",
    "email": "john@example.com"
}
```

---

### POST /register/verify-email
Verify email with code.

**Request**:
```json
{
    "email": "john@example.com",
    "code": "123456"
}
```

---

### POST /register/finalize
Complete registration.

**Request**:
```json
{
    "email": "john@example.com",
    "city_id": 1,
    "latitude": 32.461521,
    "longitude": 35.297096
}
```

**Response** (201):
```json
{
    "message": "Registration completed successfully",
    "token": "1|xxxxxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "passenger"
    }
}
```

---

### POST /login
User login.

**Request**:
```json
{
    "login": "john@example.com",
    "password": "password123",
    "device_token": "optional_fcm_token"
}
```

**Response** (200):
```json
{
    "token": "1|xxxxxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "passenger"
    }
}
```

---

## User Endpoints (Authenticated)

### POST /user
Get current user profile.

**Headers**: `Authorization: Bearer {token}`

**Response** (200):
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "role": "passenger",
        "city": {
            "id": 1,
            "name": "New York"
        }
    }
}
```

---

### PUT /user
Update user profile.

**Request**:
```json
{
    "name": "John Updated",
    "phone": "+1234567891",
    "city_id": 2,
    "latitude": 32.461521,
    "longitude": 35.297096
}
```

---

### POST /user/change-password
Change password.

**Request**:
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

---

### POST /user/logout
Logout user.

---

## Passenger Endpoints

All require: `Authorization: Bearer {token}` + Passenger role

### POST /passenger/search-rides
Search for available rides.

**Request (One-Way)**:
```json
{
    "passenger_latitude": 32.461521,
    "passenger_longitude": 35.297096,
    "institution_location_id": 5,
    "type": "one_way",
    "nb_seats_requested": 1,
    "to_institution_time_window": {
        "start": "2024-01-20 07:00:00",
        "end": "2024-01-20 09:00:00"
    }
}
```

**Response** (200):
```json
{
    "ride_groups": [
        {
            "id": 1,
            "driver": {
                "id": 5,
                "name": "Driver Name",
                "rating": 4.5
            },
            "to_institution_ride": {
                "id": 10,
                "scheduled_time": "2024-01-20 08:00:00",
                "available_seats": 3,
                "price": 5.00
            },
            "locations": {
                "pickup": [
                    {"id": 1, "name": "Station A", "type": "station"}
                ],
                "institution": {
                    "id": 5,
                    "name": "State University"
                }
            }
        }
    ]
}
```

---

### POST /passenger/ride-requests
Submit a ride request.

**Request**:
```json
{
    "passenger_latitude": 32.461521,
    "passenger_longitude": 35.297096,
    "institution_location_id": 5,
    "nb_seats_requested": 1,
    "type": "one_way",
    "notes": "Near the main gate"
}
```

**Response** (201):
```json
{
    "message": "Ride request submitted successfully",
    "ride_request": {
        "id": 1,
        "status": "pending"
    }
}
```

---

### GET /passenger/ride-requests
Get all ride requests.

---

### GET /passenger/ride-offers
Get all received offers.

**Response** (200):
```json
{
    "ride_offers": [
        {
            "id": 1,
            "driver": {
                "id": 5,
                "name": "Driver Name",
                "rating": 4.5
            },
            "offered_price": 15.50,
            "pickup_time": "2024-01-20 08:00:00",
            "driver_message": "I can pick you up from the main square",
            "status": "pending"
        }
    ]
}
```

---

### PATCH /passenger/ride-offers/{offerId}/accept
Accept a ride offer.

**Response** (200):
```json
{
    "message": "Ride offer accepted successfully",
    "bookings": [
        {
            "id": 1,
            "ride_id": 10,
            "price": 15.50,
            "status": "active"
        }
    ]
}
```

---

### GET /passenger/bookings
Get all bookings.

**Response** (200):
```json
{
    "bookings": [
        {
            "id": 1,
            "ride": {
                "id": 10,
                "scheduled_time": "2024-01-20 08:00:00",
                "driver": {
                    "name": "Driver Name"
                }
            },
            "node": {
                "pickup_location": {
                    "name": "Station A"
                },
                "dropoff_location": {
                    "name": "State University"
                }
            },
            "nb_seats": 1,
            "price": 15.50,
            "status": "active"
        }
    ]
}
```

---

### PATCH /passenger/bookings/{bookingId}/cancel
Cancel a booking.

---

## Driver Endpoints

All require: `Authorization: Bearer {token}` + Driver role + Verified

### GET /driver/vehicles
Get all driver vehicles.

**Response** (200):
```json
{
    "vehicles": [
        {
            "id": 1,
            "plate_number": "ABC-1234",
            "brand": "Toyota",
            "color": "White",
            "capacity": 4
        }
    ]
}
```

---

### POST /driver/vehicles
Create new vehicle.

**Request** (multipart/form-data):
```
plate_number: ABC-1234
brand: Toyota
color: White
capacity: 4
images[]: [file1.jpg]
images[]: [file2.jpg]
```

---

### GET /driver/rides
Get all driver rides.

**Query Parameters**:
- `status`: pending, active, completed, canceled
- `date`: YYYY-MM-DD

---

### POST /driver/rides
Create a new ride.

**Request**:
```json
{
    "vehicle_id": 1,
    "scheduled_time": "2024-01-20 08:00:00",
    "type": "to_institution",
    "location_group": {
        "passenger_locations": [1, 2, 3],
        "institution_location": 5
    }
}
```

**Response** (201):
```json
{
    "message": "Ride created successfully",
    "ride": {
        "id": 1,
        "available_seats": 4,
        "status": "pending"
    }
}
```

---

### PATCH /driver/rides/{rideId}/start
Start a ride.

---

### PATCH /driver/rides/{rideId}/finish
Finish a ride.

---

### GET /driver/ride-requests
Get ride requests.

**Response** (200):
```json
{
    "ride_requests": [
        {
            "id": 1,
            "passenger": {
                "name": "John Doe",
                "rating": 4.7
            },
            "nb_seats_requested": 1,
            "status": "pending"
        }
    ]
}
```

---

### PATCH /driver/ride-requests/{requestId}/accept
Accept a ride request.

---

### POST /driver/ride-offers
Send custom offer.

**Request**:
```json
{
    "ride_request_id": 1,
    "offered_price": 15.50,
    "suggested_pickup_latitude": 32.461521,
    "suggested_pickup_longitude": 35.297096,
    "pickup_time": "2024-01-20 08:00:00",
    "driver_message": "I can pick you up from the main square"
}
```

---

### POST /driver/ride-template-groups
Create recurring ride templates.

**Request**:
```json
{
    "name": "Morning Route",
    "location_group": {
        "passenger_locations": [1, 2, 3],
        "institution_location": 5
    },
    "ride_templates": [
        {
            "vehicle_id": 1,
            "scheduled_time": "07:30:00",
            "type": "to_institution",
            "recurring_days": ["monday", "wednesday", "friday"]
        }
    ]
}
```

---

## Location Endpoints

### GET /locations
Get all locations.

**Query Parameters**: `type` (city, station, institution)

**Response** (200):
```json
{
    "locations": [
        {
            "id": 1,
            "name": "New York",
            "type": "city",
            "latitude": 40.712776,
            "longitude": -74.005974
        }
    ]
}
```

---

## Error Responses

### 422 - Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### 401 - Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 - Forbidden
```json
{
    "message": "This action is unauthorized."
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Rate Limiting

- Authenticated: 120 requests/minute
- Guest: 60 requests/minute

Headers:
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 119
```

---

## Notes

- Timestamps: ISO 8601 (YYYY-MM-DD HH:MM:SS)
- Coordinates: decimal degrees
- Prices: decimal with 2 places
- Authorization: `Bearer {token}`

---

**Last Updated**: October 2024
