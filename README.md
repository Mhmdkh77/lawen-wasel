# Lawen Wasel - Student Ride Sharing Platform

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)

A comprehensive Laravel-based ride-sharing platform designed specifically for university students, enabling them to find and share rides to and from their institutions. The system features a robust RESTful API, an intuitive admin panel, and advanced route optimization capabilities.

## 🌟 Key Features

### For Passengers
- **Smart Ride Search**: Search for available rides with filters for time windows, seat requirements, and pickup locations
- **Ride Requests**: Submit ride requests with specific pickup locations and institutions
- **Negotiation System**: Receive and accept custom offers from drivers with suggested pickup points and pricing
- **Booking Management**: Track active bookings, view ride details, and cancel reservations
- **Multi-Stop Support**: Automated pickup and dropoff point optimization

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
  - Twilio (SMS — configured, not yet wired into active routes)
  - Firebase Cloud Messaging (push notifications — configured, not yet wired into active routes)

## 📸 Screenshots

### Admin Login
![Admin Login](docs/screenshots/admin_login.png)
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
Add API keys to `.env` (these variables are **not** pre-filled in `.env.example`, so add them manually):
```env
# Google Maps (reverse geocoding + admin route map)
GOOGLE_MAPS_API_KEY=your_google_maps_key

# OpenRouteService (multi-stop route optimization)
ORS_API_KEY=your_ors_api_key

# Twilio (SMS/phone verification — currently wired but disabled in routes)
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=your_twilio_number

# Firebase Cloud Messaging (push notifications — currently disabled in controllers)
FIREBASE_PROJECT_ID=your_firebase_project_id
FIREBASE_CLIENT_EMAIL=your_firebase_service_account_email
FIREBASE_PRIVATE_KEY=your_firebase_service_account_private_key
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Seed Database (Optional)
```bash
php artisan db:seed
```
This creates a demo admin (`admin@admin.com` / `admin`) and two demo accounts (`passenger@user.com` / `driver@user.com`, both password `pass`) you can use to log in right away. Check `database/seeders/DatabaseSeeder.php` and `database/factories/AdminFactory.php` for the exact factory output.

### 8. Link Public Storage
```bash
php artisan storage:link
```
Required for vehicle images, profile pictures, and driver license uploads to be served over HTTP — without this, every `Storage::url()` link in the API/admin panel returns a broken path.

### 9. Build Frontend Assets
```bash
npm run build
# Or for development
npm run dev
```

### 10. Start Development Server
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

### 11. (Optional) Run the Scheduler for Recurring Rides
Ride Templates rely on a daily scheduled command (`rides:generate-daily`) to materialize the next day's rides. In local development you can trigger it manually:
```bash
php artisan rides:generate-daily
```
In production, run Laravel's scheduler via cron:
```bash
* * * * * php artisan schedule:run >> /dev/null 2>&1
```

## 📱 API Documentation

The platform provides a comprehensive RESTful API with 30+ endpoints for seamless integration.

### Quick Start

**Base URL**: `http://localhost:8000/api`

**Authentication**: All protected endpoints require Bearer token authentication
```http
Authorization: Bearer {your_token}
```

### Core Features

- **Multi-step registration** with email verification
- **Dual-role authentication** (passengers and drivers)
- **Ride search and matching** with advanced filters
- **Real-time booking management**
- **Driver offer/request system**
- **Vehicle and location management**

### Endpoint Categories

- 🔐 **Authentication** - Register, login, password reset
- 👤 **User Management** - Profile, preferences, settings
- 🚗 **Passenger Endpoints** - Search rides, bookings, requests
- 🚙 **Driver Endpoints** - Rides, vehicles, offers, templates
- 📍 **Location Endpoints** - Cities, stations, institutions

### Example Request

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "user@example.com",
    "password": "password123"
  }'
```

**For detailed endpoint documentation, request/response examples, and authentication flows, see the [complete API documentation](docs/API-docs.md).**

## 🗄️ Database Schema

### Entity Relationship Diagram

![Database Schema](docs/screenshots/db.png)
*Complete database schema showing all tables and relationships*


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


## 🏗️ Architecture Highlights

### Design Patterns
- **Service Layer**: `LocationService`, `NotificationService`, and `FirebaseService` for external API integration
- **Middleware Authentication**: Role-based access control (driver, passenger, verified-driver guards)
- **Eloquent Relationships**: `hasManyThrough` and many-to-many relations for driver/vehicle/ride ownership chains
- **Database Transactions**: Ensuring data consistency across multi-table writes (bookings, ride creation)

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
