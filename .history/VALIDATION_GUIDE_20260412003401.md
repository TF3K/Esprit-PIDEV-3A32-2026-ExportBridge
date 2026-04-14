# Form Validation System

## Overview
A comprehensive client-side form validation system has been implemented across all forms in the ExportBridge application. The validation provides real-time feedback to users and prevents form submission when validation fails.

## Features

### 1. **Field-Level Validation**
- **Name Fields**: Only letters, spaces, hyphens, and apostrophes allowed
- **Email Fields**: Valid email format required
- **Password Fields**: Minimum 8 characters required
- **Country Codes**: 2-5 uppercase letters (e.g., FR, DE, IT)
- **HS Codes**: 4-10 digits with optional dots (e.g., 6109.10)
- **Phone Numbers**: Digits, spaces, +, -, and parentheses allowed
- **URLs**: Must start with http:// or https://
- **Alphanumeric**: Letters and numbers only

### 2. **Password Visibility Toggle**
- All password fields automatically get an eye icon button
- Click to show/hide password
- Works with both new and existing password fields

### 3. **Error Display**
- Errors appear below the invalid field in red text
- Field border turns red when invalid
- Field border turns green when valid
- First invalid field gets focus on submit attempt

### 4. **Validation Triggers**
- **On Blur**: Validates when user leaves a field
- **On Submit**: Validates all fields before form submission
- **Real-time**: Shows/hides errors as user types (after first blur)

## Validation Rules Reference

| Rule | Pattern | Description | Example |
|------|---------|-------------|---------|
| `name` | Letters, spaces, hyphens, apostrophes | Personal names, company names | "John Doe", "Acme Corp" |
| `email` | Standard email format | Email addresses | "user@example.com" |
| `password` | Min 8 characters | Passwords | "securepass123" |
| `countryCode` | 2-5 uppercase letters | ISO country codes | "FR", "DE", "IT" |
| `hsCode` | 4-10 digits/dots | Harmonized System codes | "6109.10" |
| `phone` | Digits, +, -, spaces, () | Phone numbers | "+1 (555) 123-4567" |
| `url` | http:// or https:// | URLs | "https://example.com" |
| `alphanumeric` | Letters and numbers | Codes, identifiers | "ABC123" |
| `number` | Numeric values | Quantities, prices | "123.45" |

## How to Use

### Automatic Validation
Forms with `data-validate` attribute are automatically validated:

```twig
<form action="" method="post" data-validate>
    <input type="text" name="first_name" required data-validation="required name">
    <input type="email" name="email" required data-validation="required email">
    <button type="submit">Submit</button>
</form>
```

### Manual Validation Attributes

#### Required Field
```twig
<input type="text" name="field" required data-validation="required">
```

#### Multiple Validations
```twig
<input type="text" name="email" required data-validation="required email">
```

#### Custom Validation
```twig
<input type="text" name="country_code" data-validation="countryCode">
```

### Password Fields
Password fields automatically get a show/hide toggle button:

```twig
<input type="password" name="password" required data-validation="required password">
```

### Confirm Password
Automatically validates against the password field:

```twig
<input type="password" name="password" required data-validation="required password">
<input type="password" name="confirm_password" required data-validation="required confirmPassword">
```

## Files Modified

### JavaScript
- `assets/js/form-validation.js` - Main validation module

### Templates Updated
All form templates now include validation:

#### Admin Forms
- `templates/admin/companies/form.html.twig`
- `templates/admin/managers/form.html.twig`
- `templates/admin/markets/form.html.twig`
- `templates/admin/products/form.html.twig`
- `templates/admin/product_categories/form.html.twig`
- `templates/admin/certificates/form.html.twig`
- `templates/admin/partnerships/form.html.twig`
- `templates/admin/profile/index.html.twig`

#### Authentication Forms
- `templates/auth/register.html.twig`
- `templates/auth/login.html.twig`

#### User Forms
- `templates/user/profile/index.html.twig`

### Base Template
- `templates/base.html.twig` - Added validation script and styles

## CSS Styles

Custom validation styles are included in the base template:

```css
.is-invalid {
    border-color: #dc3545 !important;
}
.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
.is-valid {
    border-color: #198754 !important;
}
.password-toggle {
    border-left: none !important;
}
```

## Behavior

### On Validation Error
1. Field border turns red
2. Error message appears below the field
3. Form submission is prevented
4. Focus moves to first invalid field
5. Page scrolls to show the error

### On Validation Success
1. Field border turns green (optional)
2. No error message shown
3. Form can be submitted

### Password Toggle
1. Eye icon appears next to password field
2. Click to reveal password (changes to eye-slash)
3. Click again to hide password

## Server-Side Validation

**Important**: Client-side validation is for user experience only. Always maintain server-side validation in your controllers for security and data integrity.

Example controller validation:
```php
// In your controller
if (!preg_match('/^[a-zA-Z\s\'-]+$/', $firstName)) {
    $errors[] = 'Invalid name format';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address';
}
```

## Troubleshooting

### Validation Not Working
1. Check that `data-validate` attribute is on the form
2. Ensure `form-validation.js` is loaded (check browser dev tools)
3. Verify `data-validation` attributes on inputs
4. Check browser console for JavaScript errors

### Password Toggle Not Showing
1. Ensure input type is `password`
2. Check that Bootstrap icons are loaded
3. Verify no CSS conflicts with `.password-toggle` class

### Custom Validation Rules
Edit `assets/js/form-validation.js`:

```javascript
patterns: {
    customRule: /^[A-Z]{3}$/,
},
rules: {
    customRule: {
        pattern: 'customRule',
        message: 'Must be 3 uppercase letters'
    }
}
```

## Future Enhancements

Potential improvements:
- [ ] Password strength indicator
- [ ] Real-time username availability check
- [ ] File upload validation
- [ ] Date range validation
- [ ] Cross-field validation (e.g., end date > start date)
- [ ] Custom error message localization
- [ ] Accessibility improvements (ARIA labels)
