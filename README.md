# Lawen Wasel - Student Ride Sharing Platform

A comprehensive Laravel-based ride-sharing platform designed specifically for university students, enabling them to find and share rides to and from their institutions. The system features a robust RESTful API, an intuitive admin panel, and advanced route optimization capabilities.

## 🌟 Key Features

### For Passengers
- **Smart Ride Search**: Search for available rides with filters for time windows, seat requirements, and pickup locations
- **Ride Requests**: Submit ride requests with specific pickup locations and institutions
- **Negotiation System**: Receive and accept custom offers from drivers with suggested pickup points and pricing
- **Booking Management**: Track active bookings, view ride details, and cancel reservations
- **Multi-Stop Support**: Automated pickup and dropoff point optimization
- **Rating System**: Rate drivers after completed rides to ensure service quality

### For Drivers
- **Vehicle Management**: Register and manage multiple vehicles with image galleries
- **Ride Templates**: Create recurring ride schedules for regular routes (daily, weekly patterns)
- **Dynamic Ride Creation**: Set up one-time or recurring rides with customizable capacity and pricing
- **Ride Request Handling**: Review incoming passenger requests and accept/reject based on route compatibility
- **Custom Offers**: Send personalized ride offers with suggested pickup locations and pricing
- **Route Optimization**: Automatic multi-stop route optimization using OpenRouteService API
- **Driver Verification**: Verification system to ensure driver authenticity

### Admin Panel
- **Dashboard Analytics**: Comprehensive overview of users, rides, bookings, and locations
- **User Management**: Monitor and manage both passengers and drivers
- **Driver Verification**: Toggle driver verification status with document review
- **Location Management**: CRUD operations for cities, stations, and institutions
- **Ride Monitoring**: View ride details, routes, and booking information
- **Vehicle Oversight**: Review registered vehicles and their documentation
- **Booking Tracking**: Monitor active and historical bookings

### Technical Features
- **Multi-Step Registration**: Secure registration with email verification codes
- **Role-Based Access Control**: Separate authentication guards for users and admins
- **API Authentication**: Laravel Sanctum for secure token-based authentication
- **Geolocation Support**: GPS coordinates for accurate pickup/dropoff locations
- **Google Maps Integration**: Reverse geocoding for address resolution
- **Route Optimization**: OpenRouteService integration for multi-stop route planning
- **Conflict Prevention**: Automatic detection of overlapping ride requests
- **Transaction Safety**: Database transactions for data integrity
- **Queue Support**: Background job processing for heavy operations
- **Email Notifications**: Password reset and verification code delivery

## 🛠️ Tech Stack

- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Authentication**: Laravel Sanctum (API), Session-based (Admin)
- **Frontend**: Livewire 3.6 (Admin Panel)
- **Database**: MySQL/PostgreSQL
- **External APIs**:
  - Google Maps Geocoding API
  - OpenRouteService Optimization API

## 📸 Screenshots

### Admin Login
![Admin Login](docs/screenshots/admin-login.png)
*Secure admin authentication interface*

### Dashboard Overview
![Dashboard](docs/screenshots/dashboard.png)
*Admin dashboard with real-time analytics and system statistics*

### User Management
![Users List](docs/screenshots/drivers_list.png)
*Comprehensive user management showing drivers and passengers with verification status*

### User Profile
![User Profile](docs/screenshots/user_page.png)
*Detailed user profile with activity history and rating information*

### Ride Details & Route Visualization
![Ride Details](docs/screenshots/ride_page.png)
*Ride management interface with real-time route visualization using Google Maps and optimized waypoints*




## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL database
- Node.js and NPM (for frontend assets)
- Google Maps API Key
- OpenRouteService API Key

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Mhmdkh77/lawen-wasel.git
cd lawen-wasel/backend
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Configure External Services
Add API keys to `.env`:
```env
# Google Maps
GOOGLE_MAPS_API_KEY=your_google_maps_key

# OpenRouteService
ORS_API_KEY=your_ors_api_key
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Seed Database (Optional)
```bash
php artisan db:seed
```

### 8. Build Frontend Assets
```bash
npm run build
# Or for development
npm run dev
```

### 9. Start Development Server
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

## 📱 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication

#### Register (Multi-Step Process)

**Step 1: Start Registration**
```http
POST /register/start
Content-Type: application/json

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

**Step 2: Verify Email**
```http
POST /register/verify-email
Content-Type: application/json

{
    "email": "john@example.com",
    "code": "123456"
}
```

**Step 3: Finalize Registration**
```http
POST /register/finalize
Content-Type: application/json

{
    "email": "john@example.com",
    "city_id": 1,
    "latitude": 32.461521,
    "longitude": 35.297096
}
```

#### Login
```http
POST /login
Content-Type: application/json

{
    "login": "john@example.com",
    "password": "password123",
    "device_token": "optional_fcm_token"
}
```

**Response:**
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

#### Password Reset

**Step 1: Send Reset Code**
```http
POST /fpassword/code/send
Content-Type: application/json

{
    "email": "john@example.com"
}
```

**Step 2: Verify Code**
```http
POST /fpassword/code/verify
Content-Type: application/json

{
    "email": "john@example.com",
    "code": "123456"
}
```

**Step 3: Reset Password**
```http
POST /fpassword/code/reset
Content-Type: application/json

{
    "email": "john@example.com",
    "code": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Passenger Endpoints

All passenger endpoints require authentication:
```http
Authorization: Bearer {token}
```

#### Search Rides
```http
POST /passenger/search-rides
Content-Type: application/json

{
    "passenger_latitude": 32.461521,
    "passenger_longitude": 35.297096,
    "institution_location_id": 5,
    "type": "round_trip",
    "nb_seats_requested": 2,
    "to_institution_time_window": {
        "start": "2024-01-20 07:00:00",
        "end": "2024-01-20 09:00:00"
    },
    "from_institution_time_window": {
        "start": "2024-01-20 15:00:00",
        "end": "2024-01-20 17:00:00"
    }
}
```

#### Submit Ride Request
```http
POST /passenger/ride-requests
Content-Type: application/json

{
    "passenger_latitude": 32.461521,
    "passenger_longitude": 35.297096,
    "institution_location_id": 5,
    "nb_seats_requested": 1,
    "type": "one_way",
    "notes": "Near the main gate"
}
```

#### Get Bookings
```http
GET /passenger/bookings
```

#### Accept Ride Offer
```http
PATCH /passenger/ride-offers/{offerId}/accept
```

#### Cancel Booking
```http
PATCH /passenger/bookings/{bookingId}/cancel
```

### Driver Endpoints

All driver endpoints require driver authentication and verification:
```http
Authorization: Bearer {token}
```

#### Vehicle Management

**Create Vehicle**
```http
POST /driver/vehicles
Content-Type: multipart/form-data

{
    "plate_number": "ABC-1234",
    "brand": "Toyota",
    "color": "White",
    "capacity": 4,
    "images[]": [file1, file2]
}
```

**Get All Vehicles**
```http
GET /driver/vehicles
```

**Update Vehicle**
```http
PUT /driver/vehicles/{vehicleId}
Content-Type: application/json

{
    "brand": "Honda",
    "color": "Blue",
    "capacity": 5
}
```

#### Ride Template Groups

**Create Recurring Ride Template**
```http
POST /driver/ride-template-groups
Content-Type: application/json

{
    "name": "Morning University Route",
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
        },
        {
            "vehicle_id": 1,
            "scheduled_time": "16:00:00",
            "type": "from_institution",
            "recurring_days": ["monday", "wednesday", "friday"]
        }
    ]
}
```

#### Ride Management

**Create One-Time Ride**
```http
POST /driver/rides
Content-Type: application/json

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

**Start Ride**
```http
PATCH /driver/rides/{rideId}/start
```

**Finish Ride**
```http
PATCH /driver/rides/{rideId}/finish
```

#### Ride Request Management

**Get Ride Requests**
```http
GET /driver/ride-requests
```

**Accept Ride Request**
```http
PATCH /driver/ride-requests/{requestId}/accept
```

**Send Custom Offer**
```http
POST /driver/ride-offers
Content-Type: application/json

{
    "ride_request_id": 1,
    "offered_price": 15.50,
    "suggested_pickup_latitude": 32.461521,
    "suggested_pickup_longitude": 35.297096,
    "pickup_time": "2024-01-20 08:00:00",
    "driver_message": "I can pick you up from the main square"
}
```

### Location Endpoints

#### Get Locations
```http
GET /locations?type=city
GET /locations?type=station
GET /locations?type=institution
```

## 🗄️ Database Schema

### Core Tables

- **users**: User accounts (passengers and drivers)
- **admins**: Admin panel users
- **passengers**: Passenger-specific data
- **drivers**: Driver profiles and verification status
- **vehicles**: Driver vehicles with specifications
- **locations**: Cities, stations, and institutions with GPS coordinates
- **rides**: Individual ride instances
- **ride_groups**: Groups rides by route and driver
- **ride_templates**: Recurring ride schedules
- **bookings**: Passenger seat reservations
- **nodes**: Multi-stop pickup/dropoff points
- **ride_requests**: Passenger ride requests
- **ride_offers**: Driver custom offers to passengers
- **ratings**: User ratings and reviews

## 🏗️ Architecture Highlights

### Design Patterns
- **Repository Pattern**: Clean separation of business logic
- **Service Layer**: LocationService for external API integration
- **Middleware Authentication**: Role-based access control
- **Eloquent Relationships**: Complex many-to-many and polymorphic relations
- **Database Transactions**: Ensuring data consistency

### Key Algorithms
- **Ride Matching**: Time-window based filtering with geospatial proximity
- **Route Optimization**: Shipment-based TSP solving via OpenRouteService
- **Conflict Detection**: Prevents overlapping bookings for passengers
- **Dynamic Seat Calculation**: Real-time availability tracking

### Security Features
- Password hashing (bcrypt)
- API token authentication
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS protection
- Email verification






## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # API Controllers
│   │   └── Admin/            # Admin Panel Controllers
│   └── Middleware/           # Custom Middleware
├── Models/                   # Eloquent Models
├── Services/                 # Business Logic Services
└── Mail/                     # Email Templates

database/
├── migrations/               # Database Migrations
├── seeders/                  # Database Seeders
└── factories/                # Model Factories

routes/
├── api.php                   # API Routes
└── web.php                   # Admin Panel Routes

resources/
└── views/                    # Blade Templates (Admin Panel)
```
