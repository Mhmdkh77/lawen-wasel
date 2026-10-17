# Lawen Wasel API

This table covers the currently registered JSON API endpoints. The route definitions in [`backend/routes/api.php`](../backend/routes/api.php) and each controller's validation rules are the source of truth for request fields. There is no mobile client in this repository. To inspect the live route list, run `php artisan route:list --path=api` from `backend/`.

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

Registration verifies email. A phone number is collected, but there is no phone-verification endpoint.

## Authenticated routes

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/user` | Get the current user profile |
| PUT | `/user` | Update supported profile fields and uploads |
| GET | `/user/driver-license` | Return the authenticated driver's private license document; requires a bearer token |
| POST | `/user/change-password` | Change password using `current_password`, `new_password`, and `new_password_confirmation` |
| POST | `/user/logout` | Revoke the user's tokens |
| GET | `/locations` | List locations |

Admin users view a driver's license through the session-protected `/admin/drivers/{user}/license` route. Existing uploaded licenses are moved out of public storage by the privacy migration; bundled demo illustrations remain public.

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

Ride search resolves the supplied pickup coordinates to the nearest supported city center within 10 km. It returns pending rides serving that city (or a station linked to it), the requested institution, time window, and seat count. Coordinates outside that area receive HTTP 422. This is an approximate city match, not a distance check against every pickup stop.

Station and institution records are available through `GET /locations`; there are no separate passenger station endpoints.

### Driver routes

All paths below start with `/driver` and require a driver account. Ride, request, offer, and template routes also require admin verification of that driver.

| Method | Path | Purpose |
| --- | --- | --- |
| GET, POST | `/vehicles` | List or create vehicles |
| GET, PUT, DELETE | `/vehicles/{vehicle}` | View or update a vehicle; deletion returns HTTP 409 while rides or templates reference it |
| GET, POST | `/ride-template-groups` | List or create recurring template groups; creation generates rides for the next seven days |
| GET, PUT, DELETE | `/ride-template-groups/{rideTemplateGroup}` | View, update, or delete a template group; historical ride routes are retained |
| GET | `/ride-requests` | List incoming requests |
| GET | `/ride-requests/{rideRequest}` | View a request |
| PATCH | `/ride-requests/{rideRequest}/accept` | Accept a request |
| PATCH | `/ride-requests/{rideRequest}/reject` | Reject a request |
| GET, POST | `/ride-offers` | List or send offers |
| GET | `/ride-offers/{rideOffer}` | View an offer |
| PUT | `/ride-offers/{rideOffer}` | Edit one of your pending offers |
| GET, POST | `/rides` | List or create rides |
| GET, PUT | `/rides/{ride}` | View or update a ride |
| PATCH | `/rides/{ride}/start` | Start a ride |
| PATCH | `/rides/{ride}/finish` | Finish a ride |

## Admin panel

The web admin panel uses a separate session guard under `/admin`. It does not use API bearer tokens. See the [project README](../README.md#admin-accounts) for account management.
