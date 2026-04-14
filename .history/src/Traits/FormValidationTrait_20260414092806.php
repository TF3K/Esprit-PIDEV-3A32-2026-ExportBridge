<?php

namespace App\Traits;

/**
 * Trait FormValidationTrait
 * 
 * Provides server-side validation methods for form inputs
 */
trait FormValidationTrait
{
    /**
     * Validation errors array
     */
    protected array $validationErrors = [];

    /**
     * Validate a name field (letters, spaces, hyphens, apostrophes only)
     */
    protected function validateName(?string $value, string $fieldName, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);
        
        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $value)) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must contain only letters, spaces, hyphens, and apostrophes.';
            return false;
        }

        if (strlen($value) < 2) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must be at least 2 characters long.';
            return false;
        }

        return true;
    }

    /**
     * Validate an email address
     */
    protected function validateEmail(?string $value, string $fieldName = 'Email', bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = $fieldName . ' is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->validationErrors[] = 'Please enter a valid ' . strtolower($fieldName) . ' address.';
            return false;
        }

        return true;
    }

    /**
     * Validate a password
     */
    protected function validatePassword(?string $value, bool $required = false, int $minLength = 8): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = 'Password is required.';
                return false;
            }
            return true;
        }

        if (strlen($value) < $minLength) {
            $this->validationErrors[] = 'Password must be at least ' . $minLength . ' characters long.';
            return false;
        }

        return true;
    }

    /**
     * Validate password confirmation matches
     */
    protected function validatePasswordMatch(?string $password, ?string $confirmPassword): bool
    {
        if (empty($password) && empty($confirmPassword)) {
            return true;
        }

        if ($password !== $confirmPassword) {
            $this->validationErrors[] = 'Passwords do not match.';
            return false;
        }

        return true;
    }

    /**
     * Validate a country code (2-5 uppercase letters)
     */
    protected function validateCountryCode(?string $value, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = 'Country code is required.';
                return false;
            }
            return true;
        }

        $value = strtoupper(trim($value));

        if (!preg_match('/^[A-Z]{2,5}$/', $value)) {
            $this->validationErrors[] = 'Country code must be 2-5 uppercase letters (e.g., FR, DE, IT).';
            return false;
        }

        return true;
    }

    /**
     * Validate an HS Code (Harmonized System code)
     */
    protected function validateHsCode(?string $value, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = 'HS Code is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);

        if (!preg_match('/^[\d\.]{4,10}$/', $value)) {
            $this->validationErrors[] = 'HS Code must be 4-10 digits (e.g., 6109.10).';
            return false;
        }

        return true;
    }

    /**
     * Validate a phone number
     */
    protected function validatePhone(?string $value, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = 'Phone number is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);

        if (!preg_match('/^[\d\s\+\-\(\)]+$/', $value)) {
            $this->validationErrors[] = 'Please enter a valid phone number.';
            return false;
        }

        if (strlen(preg_replace('/\D/', '', $value)) < 7) {
            $this->validationErrors[] = 'Phone number must have at least 7 digits.';
            return false;
        }

        return true;
    }

    /**
     * Validate a URL
     */
    protected function validateUrl(?string $value, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = 'URL is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);

        if (!preg_match('/^https?:\/\//', $value)) {
            $this->validationErrors[] = 'URL must start with http:// or https://.';
            return false;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->validationErrors[] = 'Please enter a valid URL.';
            return false;
        }

        return true;
    }

    /**
     * Validate alphanumeric input
     */
    protected function validateAlphanumeric(?string $value, string $fieldName, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                return false;
            }
            return true;
        }

        $value = trim($value);

        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $value)) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must contain only letters and numbers.';
            return false;
        }

        return true;
    }

    /**
     * Validate a number
     */
    protected function validateNumber($value, string $fieldName, bool $required = false, ?float $min = null, ?float $max = null): bool
    {
        if ($value === null || $value === '') {
            if ($required) {
                $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                return false;
            }
            return true;
        }

        if (!is_numeric($value)) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must be a valid number.';
            return false;
        }

        $numValue = (float) $value;

        if ($min !== null && $numValue < $min) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must be at least ' . $min . '.';
            return false;
        }

        if ($max !== null && $numValue > $max) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must not exceed ' . $max . '.';
            return false;
        }

        return true;
    }

    /**
     * Validate a required field (generic string)
     */
    protected function validateRequired(?string $value, string $fieldName, int $minLength = 1, ?int $maxLength = null): bool
    {
        if (empty($value)) {
            $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
            return false;
        }

        $value = trim($value);

        if (strlen($value) < $minLength) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must be at least ' . $minLength . ' characters long.';
            return false;
        }

        if ($maxLength !== null && strlen($value) > $maxLength) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must not exceed ' . $maxLength . ' characters.';
            return false;
        }

        return true;
    }

    /**
     * Validate a date
     */
    protected function validateDate(?string $value, string $fieldName, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                return false;
            }
            return true;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->validationErrors[] = ucfirst($fieldName) . ' must be a valid date (YYYY-MM-DD).';
            return false;
        }

        return true;
    }

    /**
     * Validate that end date is after start date
     */
    protected function validateDateRange(?string $startDate, ?string $endDate): bool
    {
        if (empty($startDate) || empty($endDate)) {
            return true;
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        if ($end < $start) {
            $this->validationErrors[] = 'End date must be after start date.';
            return false;
        }

        return true;
    }

    /**
     * Validate a selection (dropdown/radio)
     */
    protected function validateSelection(?string $value, array $allowedOptions, string $fieldName, bool $required = false): bool
    {
        if (empty($value)) {
            if ($required) {
                $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                return false;
            }
            return true;
        }

        if (!in_array($value, $allowedOptions)) {
            $this->validationErrors[] = 'Please select a valid ' . strtolower($fieldName) . '.';
            return false;
        }

        return true;
    }

    /**
     * Get all validation errors
     */
    protected function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Check if there are any validation errors
     */
    protected function hasValidationErrors(): bool
    {
        return !empty($this->validationErrors);
    }

    /**
     * Clear validation errors
     */
    protected function clearValidationErrors(): void
    {
        $this->validationErrors = [];
    }

    /**
     * Get first validation error (for backward compatibility)
     */
    protected function getFirstValidationError(): ?string
    {
        return $this->validationErrors[0] ?? null;
    }
}