# Form Validation System - Server-Side Implementation

## Overview
A comprehensive **server-side** form validation system has been implemented across all controllers in the ExportBridge application. All validation is enforced at the controller level before data is persisted to the database.

## Architecture

### Server-Side Validation (Authoritative)
- **Location**: `src/Traits/FormValidationTrait.php`
- **Usage**: All controllers use the trait for consistent validation
- **Security**: All validation is enforced before data persistence
- **Error Handling**: Validation errors are collected and displayed to users

### Client-Side Validation (UX Enhancement)
- **Location**: `assets/js/form-validation.js`
- **Purpose**: Provides immediate feedback to users
- **Note**: Client-side validation can be bypassed, so server-side validation is authoritative

## Server-Side Validation Methods

### Usage Pattern
```php
// In your controller
use App\Traits\FormValidationTrait;

class YourController extends AbstractController
{
    use FormValidationTrait;
    
    public function add(Request $request): Response
    {
        $this->clearValidationErrors();
        
        // Validate fields
        $this->validateName($firstName, 'First name', true);
        $this->validateEmail($email, 'Email', true);
        $this->validatePassword($password, true);
        
        // Check for errors
        if ($this->hasValidationErrors()) {
            return $this->render('form.html.twig', [
                'error' => implode(' ', $this->getValidationErrors()),
            ]);
        }
        
        // Proceed with valid data
        // ...
    }
}
```

### Available Validation Methods

| Method | Parameters | Description | Example |
|--------|------------|-------------|---------|
| `validateName()` | `$value, $fieldName, $required` | Letters, spaces, hyphens, apostrophes | `$this->validateName($firstName, 'First name', true)` |
| `validateEmail()` | `$value, $fieldName, $required` | Valid email format | `$this->validateEmail($email, 'Email', true)` |
| `validatePassword()` | `$value, $required, $minLength` | Min length (default 8) | `$this->validatePassword($password, true, 8)` |
| `validatePasswordMatch()` | `$password, $confirmPassword` | Passwords match | `$this->validatePasswordMatch($password, $confirm)` |
| `validateCountryCode()` | `$value, $required` | 2-5 uppercase letters | `$this->validateCountryCode($code, true)` |
| `validateHsCode()` | `$value, $required` | 4-10 digits/dots | `$this->validateHsCode($hsCode, false)` |
| `validatePhone()` | `$value, $required` | International phone format | `$this->validatePhone($phone, false)` |
| `validateUrl()` | `$value, $required` | Must start with http(s):// | `$this->validateUrl($url, false)` |
| `validateAlphanumeric()` | `$value, $fieldName, $required` | Letters and numbers only | `$this->validateAlphanumeric($value, 'Field', true)` |
| `validateNumber()` | `$value, $fieldName, $required, $min, $max` | Numeric with optional range | `$this->validateNumber($qty, 'Quantity', false, 0, 100)` |
| `validateRequired()` | `$value, $fieldName, $minLength, $maxLength` | Generic required field | `$this->validateRequired($value, 'Field', true, 2, 100)` |
| `validateDate()` | `$value, $fieldName, $required` | Valid date (YYYY-MM-DD) | `$this->validateDate($date, 'Date', false)` |
| `validateDateRange()` | `$startDate, $endDate` | End date after start date | `$this->validateDateRange($start, $end)` |
| `validateSelection()` | `$value, $allowedOptions, $fieldName, $required` | Value in allowed options | `$this->validateSelection($status, ['active', 'pending'], 'Status', true)` |

### Error Handling Methods

| Method | Description | Example |
|--------|-------------|---------|
| `clearValidationErrors()` | Clear all errors | `$this->clearValidationErrors()` |
| `getValidationErrors()` | Get all errors as array | `$errors = $this->getValidationErrors()` |
| `hasValidationErrors()` | Check if any errors exist | `if ($this->hasValidationErrors())` |
| `getFirstValidationError()` | Get first error message | `$error = $this->getFirstValidationError()` |

## Controllers Updated

All controllers now implement server-side validation:

### Admin Controllers
- ✅ `CompanyController` - Company name, email, phone, country validation
- ✅ `ManagerController` - Name, email, password validation with uniqueness check
- ✅ `MarketController` - Country name, country code, description validation
- ✅ `ProductController` - Product name, HS code, price, quantity validation
- ✅ `ProductCategoryController` - Name, slug, description validation
- ✅ `CertificateController` - Certificate number, dates, URL validation
- ✅ `PartnershipController` - Status selection, date range, notes validation
- ✅ `ProfileController` - Name, email validation with uniqueness check

### User Controllers
- ✅ `UserProfileController` - Name, email validation with uniqueness check

### Auth Controllers
- ✅ `AuthController` - Registration with name, email, password validation

## Validation Rules by Field Type

### Names (Personal, Company, Country)
- **Pattern**: `^[a-zA-Z\s'-]+$`
- **Allowed**: Letters, spaces, hyphens, apostrophes
- **Min Length**: 2 characters
- **Examples**: "John Doe", "Acme Corp", "New Zealand"

### Email Addresses
- **Validation**: PHP `FILTER_VALIDATE_EMAIL`
- **Trimming**: Whitespace removed
- **Uniqueness**: Checked against existing records

### Passwords
- **Min Length**: 8 characters (configurable)
- **Confirmation**: Optional match validation
- **Hashing**: Symfony password hasher

### Country Codes
- **Pattern**: `^[A-Z]{2,5}$`
- **Format**: 2-5 uppercase letters
- **Examples**: "FR", "DE", "IT", "USA"

### HS Codes (Harmonized System)
- **Pattern**: `^[\d\.]{4,10}$`
- **Format**: 4-10 digits with optional dots
- **Examples**: "6109.10", "1234.56.78"

### Phone Numbers
- **Pattern**: `^[\d\s\+\-\(\)]+$`
- **Min Digits**: 7 (excluding non-digit characters)
- **Examples**: "+1 (555) 123-4567", "+44 20 7946 0958"

### URLs
- **Pattern**: Must start with `http://` or `https://`
- **Validation**: PHP `FILTER_VALIDATE_URL`
- **Examples**: "https://example.com", "http://localhost:8080"

### Alphanumeric Fields
- **Pattern**: `^[a-zA-Z0-9\s]+$`
- **Allowed**: Letters, numbers, spaces
- **Examples**: "ABC123", "Tax2024"

### Numbers
- **Validation**: PHP `is_numeric()`
- **Range**: Optional min/max validation
- **Examples**: 123.45, 100, -50

### Dates
- **Format**: YYYY-MM-DD
- **Validation**: `DateTime::createFromFormat()`
- **Range**: Optional start/end date validation

### Selections (Dropdowns)
- **Validation**: Value in allowed options array
- **Examples**: Status fields, types, categories

## Error Display

### In Templates
```twig
{% if error is defined and error %}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
{% endif %}
```

### Error Messages
All error messages are:
- **Descriptive**: Clearly state what's wrong
- **Actionable**: Tell user how to fix
- **Consistent**: Follow same pattern across all forms
- **User-friendly**: No technical jargon

## Security Considerations

### Input Sanitization
- All inputs are trimmed before validation
- HTML entities are preserved (use Twig's `|e` filter in templates)
- SQL injection prevented by Doctrine ORM

### CSRF Protection
- Symfony's built-in CSRF protection is active
- All forms include CSRF tokens

### Email Uniqueness
- Registration checks for existing emails
- Profile updates exclude current user
- Prevents duplicate accounts

### Password Security
- Passwords hashed with Symfony PasswordHasher
- Minimum length enforced
- Password confirmation optional but recommended

## Testing Validation

### Manual Testing Checklist
1. Submit empty required fields → Should show error
2. Enter invalid email → Should show format error
3. Enter name with numbers → Should show pattern error
4. Enter short password → Should show length error
5. Enter invalid country code → Should show format error
6. Submit valid data → Should succeed

### Example Test Cases
```php
// Test name validation
$this->validateName('John123', 'Name', true); // Should fail
$this->validateName('John Doe', 'Name', true); // Should pass

// Test email validation
$this->validateEmail('invalid', 'Email', true); // Should fail
$this->validateEmail('user@example.com', 'Email', true); // Should pass

// Test password validation
$this->validatePassword('short', true); // Should fail
$this->validatePassword('securepass123', true); // Should pass
```

## Migration Notes

### Before (No Validation)
```php
$company->setCompanyName($request->request->get('company_name'));
$em->flush(); // Could persist invalid data
```

### After (With Validation)
```php
$this->clearValidationErrors();
$companyName = trim($request->request->get('company_name') ?? '');
$this->validateName($companyName, 'Company name', true);

if ($this->hasValidationErrors()) {
    return $this->render('form.html.twig', [
        'error' => implode(' ', $this->getValidationErrors()),
    ]);
}

$company->setCompanyName($companyName);
$em->flush(); // Only valid data persisted
```

## Future Enhancements

Potential improvements:
- [ ] Custom validation constraints (Symfony Validator)
- [ ] API endpoint validation
- [ ] Batch validation for bulk operations
- [ ] Custom error message localization (i18n)
- [ ] Validation logging for audit trails
- [ ] Rate limiting on failed validations
