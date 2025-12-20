# RC Gestion - Sports Complex Management System

## Architecture Overview

This is a **Laravel 10** application for managing sports complex reservations in Algeria. The system uses **Arabic RTL UI** with multi-user types (Person, Club, Company, Admin) and implements a subscription-based pricing model.

### Core Domain Models

-   **Complex**: Sports facilities (complexes)
-   **Activity**: Sports activities offered at complexes
-   **ComplexActivity**: Junction table linking activities to specific complexes with capacity and pricing
-   **Schedule**: Time slots for activities (stored as JSON `time_slots` with day_number, start, end)
-   **PricingPlan**: Flexible pricing schemes (session-based, weekly, monthly, seasonal) with age/gender/client-type filters
-   **Season**: Time periods for subscriptions (monthly or yearly), used to calculate total costs
-   **Reservation**: User subscriptions to schedules, linking user → schedule → pricing plan → season

### Key Business Logic

**Pricing Calculation** (`ReservationController`):

-   **Schedule-based subscriptions**: Users subscribe to predefined schedules (not free slot selection)
-   **Plan matching**: System finds eligible `PricingPlan` records filtered by:
    -   Activity, age category, gender, client type (person/club/company)
    -   `sessions_per_week` must match schedule's slot count
-   **Price computation**:
    -   **Plan-based**: Uses `calculatePlanPrice()` - multiplies base price by season duration (months/weeks) according to `duration_unit`
    -   **Fixed-price**: Uses `calculateFixedSchedulePrice()` - caps at 12 months max (yearly = 12× monthly rate)
-   Frontend mirrors backend logic in JavaScript using season date ranges for real-time updates

**User Type Routing**:

-   Separate auth controllers: `AdminAuthController`, `PersonAuthController`, `ClubAuthController`, `CompanyAuthController`
-   Type-specific dashboards: `admin.dashboard`, `person.dashboard`, `club.dashboard`, `entreprise.dashboard`
-   Middleware guards routes: `middleware(['auth','admin'])`, `middleware(['auth','person'])`, etc.

## Developer Workflows

**Local Development**:

```bash
php artisan serve          # Start dev server at http://127.0.0.1:8000
npm run dev                # Vite asset compilation
php artisan migrate        # Run migrations
```

**Common Tasks**:

-   Controllers in `app/Http/Controllers/` (Admin subfolder for admin-specific)
-   Blade views: `resources/views/` with role-based folders (`admin/`, `person/`, `club/`, `entreprise/`)
-   Routes: `routes/web.php` organized by user type sections
-   Models: `app/Models/` - use Eloquent relationships extensively

## Project Conventions

**Arabic RTL UI**:

-   All views use `direction: rtl; text-align:right` and Arabic labels
-   Date formats: `Y-m-d` for inputs, `format('Y-m-d')` for Carbon dates
-   Numbers: Use `number_format()` for prices with "دج" suffix

**Blade Patterns**:

-   Use `optional()` helper for nested nullable relations: `optional($user->person)->ageCategory`
-   Null-safe operator for plan properties: `$plan?->duration_unit ?? ''`
-   JSON decoding: Cast `time_slots` as `array` in model, decode manually in controllers using `json_decode($schedule->time_slots, true)`

**Controller Helpers** (ReservationController):

-   `eligiblePricingPlans()`: Filters plans by activity, client type, age, gender, active status
-   `normalizeGender()`: Converts gender values to 'H'/'F' codes
-   `scheduleMatchesUserProfile()`: Validates age/gender compatibility
-   `matchPlanForSchedule()`: Finds plan matching schedule's session count
-   `calculatePlanPrice()` / `calculateFixedSchedulePrice()`: Compute totals based on season

**Frontend Data Flow**:

-   Controllers decorate schedules with `formatted_slots` (Arabic day labels) and `calculated_price`
-   JavaScript receives plans/schedules via `@json()` directives
-   Season changes trigger price recalculation without page reload using `getSeasonMetrics()` and `calculatePlanPrice()`

## Integration Points

**Dependencies**:

-   Laravel UI (`laravel/ui`) for auth scaffolding
-   QR Code generation: `simplesoftwareio/simple-qrcode`
-   Carbon for date calculations (month/week counting)
-   Bootstrap 5 for styling (RTL compatible)

**External Systems**:

-   No external APIs currently integrated
-   Payment tracking via `payment_status` field (pending implementation)

## Critical Patterns

**Match Expression Usage**:

-   Use PHP 8.1 `match` for type mappings (gender codes, pricing types, user types)
-   Example: `match ($user->type) { 'club' => ..., 'company' => ..., default => 'person' }`

**Collection Helpers**:

-   Heavy use of Laravel Collections: `->map()`, `->filter()`, `->first()`, `->pluck()`
-   Decorate results before passing to views rather than logic in Blade

**Validation**:

-   Always validate schedule compatibility before storing reservations
-   Check dossier approval status for clubs/companies before allowing reservations
-   Verify available capacity: `$schedule->nbr - sum(qty_places)`

**Database Structure**:

-   Many polymorphic relationships via `user_type` field
-   JSON columns: `time_slots` (schedule slots), `casts` in models for auto-conversion
-   Soft deletes not implemented, use `active` flags instead

## Common Gotchas

-   Season month calculation: Use `diffInMonths()` + 1 if `end_day >= start_day` to get inclusive count
-   Schedule `day_number` is 0-indexed (Sunday=0)
-   Price display: Always cap yearly calculations at 12 months to avoid over-charging
-   Nullsafe operators required when accessing plan properties from schedules (plan may be null if no match)
-   User dossier approval required before reservations - check `etat === 'approved'`

## File References

-   Reservation logic: `app/Http/Controllers/ReservationController.php`
-   Pricing calculation: `resources/views/reservation/form.blade.php` (frontend JS mirrors backend)
-   Pricing plans admin: `app/Http/Controllers/Admin/PricingsPlanController.php`
-   Models: `app/Models/{Reservation,PricingPlan,Schedule,Season,Person}.php`
