# Lawen Wasel — portfolio overview

Lawen Wasel is a Laravel backend for coordinating shared student rides to and from institutions. This repository contains a JSON API for passenger and driver clients and a Livewire admin panel. A mobile client is not part of this repository.

## What the project demonstrates

- **Booking workflow:** passengers search rides and request seats; drivers can accept requests or send offers with a proposed pickup point and price. Seat counters and bookings are updated in database transactions.
- **Two directions of travel:** outbound trips pick up near the passenger and drop off at the institution; return trips reverse those stops. A connected round-trip example is available in the showcase seed.
- **Driver operations:** verified drivers manage vehicles, rides, and recurring templates. A scheduled command generates rides for the current day from active templates.
- **Route display:** OpenRouteService can optimize multi-stop order. The admin ride page shows numbered checkpoints and uses Google Maps to display a road route when configured. The driver API can return optimized waypoints.
- **Admin operations:** the dashboard summarizes rides and requests; admins inspect bookings, drivers, passengers, vehicles, and locations. Super admins manage admin accounts and access.

## Technology

Laravel 12, PHP, Eloquent, Sanctum, Livewire, Blade, Tailwind CSS, and SQLite for local development. The app also has configuration for Google Maps and OpenRouteService. The migrations support other Laravel database connections, but deployment on a different database should be tested separately.

## Current boundaries

- The repository has no passenger or driver mobile app.
- Google Maps and OpenRouteService require your own keys. Without them, route display and optimization are limited.
- Twilio phone-verification routes are disabled. Firebase push calls are currently commented out. Neither integration has been verified end to end.
- The automated suite covers key admin flows, route checkpoints, seeding, and selected booking paths. It does not provide complete API coverage.
- The local seed uses published demo credentials and is intended for development, not a public deployment without changing access and data handling.

## Explore the code

Start with the [README](../README.md) for local setup and current [screenshots](../README.md#screenshots), [API route overview](API-docs.md), [web routes](../backend/routes/web.php), [API routes](../backend/routes/api.php), and [feature tests](../backend/tests/Feature). The schema is defined by [Laravel migrations](../backend/database/migrations).
