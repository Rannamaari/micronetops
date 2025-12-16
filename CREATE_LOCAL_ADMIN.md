# Create Local Admin User

## Quick Method (Tinker)

Run this in your terminal:

```bash
cd "/Users/munad/Documents/Websites/Micro Garage Operations/micromoto-ops"
php artisan tinker
```

Then paste this code:

```php
// Create admin user
$user = \App\Models\User::firstOrCreate(
    ['email' => 'admin@micronet.mv'],
    [
        'name' => 'Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123')
    ]
);

// Create admin role if it doesn't exist
$role = \App\Models\Role::firstOrCreate(
    ['name' => 'admin'],
    [
        'slug' => 'admin',
        'description' => 'Administrator role',
        'is_active' => true
    ]
);

// Assign admin role to user
if (!$user->roles()->where('role_id', $role->id)->exists()) {
    $user->roles()->attach($role->id);
}

echo "✅ Admin user created!\n";
echo "Email: admin@micronet.mv\n";
echo "Password: admin123\n";

exit
```

## Default Local Credentials

After running the above:

- **Email**: `admin@micronet.mv`
- **Password**: `admin123`

## Alternative: One-Liner Command

```bash
cd "/Users/munad/Documents/Websites/Micro Garage Operations/micromoto-ops" && php artisan tinker --execute="
\$user = \App\Models\User::firstOrCreate(['email' => 'admin@micronet.mv'], ['name' => 'Admin', 'password' => \Illuminate\Support\Facades\Hash::make('admin123')]);
\$role = \App\Models\Role::firstOrCreate(['name' => 'admin'], ['slug' => 'admin', 'description' => 'Admin', 'is_active' => true]);
if (!\$user->roles()->where('role_id', \$role->id)->exists()) { \$user->roles()->attach(\$role->id); }
echo '✅ Admin created: admin@micronet.mv / admin123';
"
```

## Check Existing Users

To see if any users already exist:

```bash
php artisan tinker
```

Then:
```php
\App\Models\User::all(['id', 'name', 'email']);
exit
```

## Login

After creating the user:

1. Go to: `http://localhost:8000/ops` or `http://localhost:8000/login`
2. Use:
   - Email: `admin@micronet.mv`
   - Password: `admin123`


