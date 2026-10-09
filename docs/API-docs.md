# Lawen Wasel API

This is a route overview for the Laravel backend. The route definitions in [`backend/routes/api.php`](../backend/routes/api.php) and each controller's validation rules are the source of truth for request fields. There is no mobile client in this repository.

Base URL when running locally: `http://127.0.0.1:8000/api`. Protected routes require `Authorization: Bearer <token>` from Laravel Sanctum. Registration and authentication routes are limited to 10 requests per minute.

## Registration and sign-in

Registration is a three-step flow. With the default `MAIL_MAILER=log`, read the verification code in `backend/storage/logs/laravel.log` during local development.

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/register/start` | Submit `role` (`passenger` or `driver`), `name`, `email`, `gender`, and `phone`; returns `registration_id` |
| POST | `/register/verify-email` | Submit `registration_id` and `code` |
| POST | `/register/resend-verify-email` | Submit `registration_id` for a new email code |
| POST | `/register/finalize` | Submit `registration_id`, `password`, and `password_confirmation`; returns a user and token |
| POST | `/login` | Submit `login` (email or phone) and `password`; returns a user and token |
| POST | `/fpassword/code/send` | Send a password-reset code to an existing user email |
| POST | `/fpassword/code/resend` | Resend the reset code |
| POST | `/fpassword/code/verify` | Verify the reset code |
| POST | `/fpassword/code/reset` | Reset the password after verification |

Example login after seeding a local database:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"login":"passenger@user.com","password":"pass"}'
```

Phone verification has controller code, but its routes are disabled. It is not required by the current registration flow.

## Authenticated routes

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/user` | Get the current user profile |
| PUT | `/user` | Update supported profile fields and uploads |
| POST | `/user/change-password` | Change password using `current_password`, `new_password`, and `new_password_confirmation` |
| POST | `/user/logout` | Revoke the user's tokens |
| GET | `/locations` | List locations |

### Passenger routes

All paths below start with `/passenger` and require a passenger account.

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/search-rides` | Search matching rides |
| GET | `/ride-groups/{rideGroup}` | View a ride group |
| POST | `/ride-requests` | Create a ride request |
| GET | `/ride-requests` | List requests |
| GET | `/ride-requests/{rideRequest}` | View a request |
| PUT | `/ride-requests/{rideRequest}` | Edit a request |
| PATCH | `/ride-requests/{rideRequest}/cencel` | Cancel a request; `cencel` is the current route spelling |
| GET | `/ride-offers` | List offers |
| GET | `/ride-offers/{rideOffer}` | View an offer |
| PATCH | `/ride-offers/{rideOffer}/accept` | Accept an offer |
| PATCH | `/ride-offers/{rideOffer}/reject` | Reject an offer |
| GET | `/bookings` | List bookings |
| GET | `/bookings/{booking}` | View a booking |
| PATCH | `/bookings/{booking}/cencel` | Cancel a booking; `cencel` is the current route spelling |

The station routes are currently placeholders and are omitted from this supported-route list.

### Driver routes

All paths below start with `/driver` and require a driver account. Ride, request, offer, and template routes also require admin verification of that driver.

| Method | Path | Purpose |
| --- | --- | --- |
| GET, POST | `/vehicles` | List or create vehicles |
| GET, PUT, DELETE | `/vehicles/{vehicle}` | View, update, or delete a vehicle |
| GET, POST | `/ride-template-groups` | List or create recurring template groups |
| GET, PUT, DELETE | `/ride-template-groups/{RideTemplateGroup}` | View, update, or delete a template group |
| GET | `/ride-requests` | List incoming requests |
| GET | `/ride-requests/{rideRequest}` | View a request |
| PATCH | `/ride-requests/{rideRequest}/accept` | Accept a request |
| PATCH | `/ride-requests/{rideRequest}/reject` | Reject a request |
| GET, POST | `/ride-offers` | List or send offers |
| GET | `/ride-offers/{rideOffer}` | View an offer |
| GET, POST | `/rides` | List or create rides |
| GET, PUT | `/rides/{ride}` | View or update a ride |
| PATCH | `/rides/{ride}/start` | Start a ride |
| PATCH | `/rides/{ride}/finish` | Finish a ride |

`PUT /driver/ride-offers/{rideOffer}` is registered but its controller method is not implemented; do not depend on it.

## Admin panel

The web admin panel uses a separate session guard under `/admin`. It does not use API bearer tokens. See the [project README](../README.md#admin-accounts) for account management.
