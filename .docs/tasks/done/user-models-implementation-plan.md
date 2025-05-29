# User Models Implementation Plan

> :white_check_mark: Status: Done

## 1. Database Changes

### 1.1. New Migrations

1. Create `user_profiles` table:
```php
Schema::create('user_profiles', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->text('bio')->nullable();
    $table->string('location')->nullable();
    $table->date('birth_date')->nullable();
    $table->string('position')->nullable();
    $table->string('website')->nullable();
    $table->json('social_links')->nullable();
    $table->timestamps();
});
```

2. Create `user_preferences` table:
```php
Schema::create('user_preferences', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('language')->default('en');
    $table->string('decimal_separator')->default('.');
    $table->string('date_format')->default('DD.MM.YYYY');
    $table->string('dark_mode')->default('system');
    $table->boolean('is_sound_enabled')->default(true);
    $table->boolean('is_profile_public')->default(false);
    $table->json('field_visibility')->nullable();
    $table->json('visibility_per_tenant')->nullable();
    $table->timestamps();
});
```

3. Create `user_table_settings` table:
```php
Schema::create('user_table_settings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('entity');
    $table->string('name')->nullable();
    $table->json('config');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    $table->unique(['user_id', 'entity', 'name', 'is_default']);
});
```

4. Create `notification_settings` table:
```php
Schema::create('notification_settings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('channel');
    $table->string('setting_key');
    $table->boolean('enabled')->default(true);
    $table->timestamps();
    
    $table->unique(['user_id', 'channel', 'setting_key']);
});
```

5. Create `trusted_devices` table:
```php
Schema::create('trusted_devices', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('token')->unique();
    $table->string('device_name');
    $table->string('browser');
    $table->string('os');
    $table->string('location')->nullable();
    $table->timestamp('last_active_at');
    $table->string('ip_address', 45);
    $table->timestamp('trusted_until')->nullable();
    $table->timestamps();
    
    $table->unique(['user_id', 'device_name']);
    $table->unique(['user_id', 'token']);
});
```

6. Create `security_events` table:
```php
Schema::create('security_events', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('event_type');
    $table->string('ip_address', 45);
    $table->timestamps();
});
```

### 1.2. Model Changes

1. Update `users` table migration (0001_01_01_000000_create_auth_tables.php):
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->boolean('is_admin')->default(false);
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('phone_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

2. Update `User` model:
- Add relationships to new models
- Add fillable fields
- Add casts for JSON fields
- Add methods for profile management

3. Create new models:
- `UserProfile`
- `UserPreference`
- `UserTableSetting`
- `NotificationSetting`
- `TrustedDevice`
- `SecurityEvent`

## 2. Controllers & Resources

### 2.1. New Controllers

1. `UserProfileController`:
- `show()` - Get user profile
- `update()` - Update profile
- `updatePrivacy()` - Update privacy settings

2. `UserPreferenceController`:
- `show()` - Get preferences
- `update()` - Update preferences
- `reset()` - Reset to defaults

3. `UserTableSettingController`:
- `index()` - List table settings
- `store()` - Create new setting
- `update()` - Update setting
- `destroy()` - Delete setting
- `setDefault()` - Set as default

4. `NotificationSettingController`:
- `index()` - List notification settings
- `update()` - Update settings
- `updateBulk()` - Update multiple settings

5. `TrustedDeviceController`:
- `index()` - List trusted devices
- `destroy()` - Remove device
- `destroyAll()` - Remove all devices

6. `SecurityEventController`:
- `index()` - List security events
- `show()` - Show event details

### 2.2. New Resources

1. `UserProfileResource`:
- Basic profile data
- Privacy settings
- Social links

2. `UserPreferenceResource`:
- Language
- Format settings
- UI preferences
- Privacy settings

3. `UserTableSettingResource`:
- Entity name
- Configuration
- Default status

4. `NotificationSettingResource`:
- Channel
- Setting key
- Enabled status

5. `TrustedDeviceResource`:
- Device info
- Last active
- Location

6. `SecurityEventResource`:
- Event type
- IP address
- Timestamp

## 3. API Routes

```php
// Profile routes
Route::prefix('profile')->group(function () {
    Route::get('/', [UserProfileController::class, 'show']);
    Route::put('/', [UserProfileController::class, 'update']);
    Route::put('/privacy', [UserProfileController::class, 'updatePrivacy']);
});

// Preferences routes
Route::prefix('preferences')->group(function () {
    Route::get('/', [UserPreferenceController::class, 'show']);
    Route::put('/', [UserPreferenceController::class, 'update']);
    Route::post('/reset', [UserPreferenceController::class, 'reset']);
});

// Table settings routes
Route::prefix('table-settings')->group(function () {
    Route::get('/', [UserTableSettingController::class, 'index']);
    Route::post('/', [UserTableSettingController::class, 'store']);
    Route::put('/{setting}', [UserTableSettingController::class, 'update']);
    Route::delete('/{setting}', [UserTableSettingController::class, 'destroy']);
    Route::post('/{setting}/default', [UserTableSettingController::class, 'setDefault']);
});

// Notification settings routes
Route::prefix('notification-settings')->group(function () {
    Route::get('/', [NotificationSettingController::class, 'index']);
    Route::put('/', [NotificationSettingController::class, 'update']);
    Route::put('/bulk', [NotificationSettingController::class, 'updateBulk']);
});

// Trusted devices routes
Route::prefix('trusted-devices')->group(function () {
    Route::get('/', [TrustedDeviceController::class, 'index']);
    Route::delete('/{device}', [TrustedDeviceController::class, 'destroy']);
    Route::delete('/', [TrustedDeviceController::class, 'destroyAll']);
});

// Security events routes
Route::prefix('security-events')->group(function () {
    Route::get('/', [SecurityEventController::class, 'index']);
    Route::get('/{event}', [SecurityEventController::class, 'show']);
});
```

## 4. Implementation Steps

1. Database Setup:
   - Create all new migrations
   - Run migrations
   - Create model classes with relationships

2. Core Models:
   - Implement User model changes
   - Create new model classes
   - Add necessary traits and interfaces

3. Controllers & Resources:
   - Create controller classes
   - Implement resource classes
   - Add request validation classes

4. API Routes:
   - Add all new routes
   - Set up middleware
   - Configure route groups

5. Code Refactoring:
   - Update existing code to use new model relationships
   - Move avatar handling to `UserProfileImageController`
   - Update any code that was using moved fields (description, birth_date, etc.)
   - Update any code that was using the old status field to use is_active
   - Update any code that was using the config JSON field to use specific preference fields

6. Testing:
   - Write unit tests for models
   - Write feature tests for controllers
   - Test API endpoints

7. Documentation:
   - Update API documentation
   - Add model documentation
   - Document new features

## 5. Security Considerations

1. Data Privacy:
   - Implement field visibility controls
   - Add tenant-specific privacy settings
   - Handle sensitive data properly

2. Authentication:
   - Secure API endpoints
   - Implement rate limiting
   - Add request validation

3. Authorization:
   - Add proper permissions
   - Implement role-based access
   - Handle tenant isolation

## 6. Performance Considerations

1. Database:
   - Add proper indexes
   - Optimize queries
   - Use eager loading

2. Caching:
   - Cache user preferences
   - Cache profile data
   - Implement cache invalidation

3. API:
   - Implement pagination
   - Add response compression
   - Optimize response size
