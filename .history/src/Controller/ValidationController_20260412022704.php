<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/api')]
class ValidationController extends AbstractController
{
    use FormValidationTrait;

    #[Route('/validate', name: 'app_api_validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $this->clearValidationErrors();
        
        $fieldType = $request->request->get('field_type');
        $value = trim($request->request->get('value') ?? '');
        $fieldName = $request->request->get('field_name', 'Field');
        $required = $request->request->getBoolean('required', false);
        
        // Additional parameters for specific validations
        $minLength = $request->request->getInt('min_length', 1);
        $maxLength = $request->request->getInt('max_length');
        $min = $request->request->getFloat('min');
        $max = $request->request->getFloat('max');
        $allowedOptions = $request->request->all('allowed_options');
        
        // Perform validation based on field type
        $valid = true;
        $errorMessage = null;
        
        switch ($fieldType) {
            case 'name':
                $valid = $this->validateName($value, $fieldName, $required);
                break;
                
            case 'email':
                $valid = $this->validateEmail($value, $fieldName, $required);
                // Check for existing email if needed
                if ($valid && $request->request->getBoolean('check_existing', false)) {
                    $existingEntity = $request->request->get('existing_entity');
                    $existingId = $request->request->get('existing_id');
                    
                    if ($existingEntity) {
                        $existing = $this->getDoctrine()
                            ->getRepository($existingEntity)
                            ->findOneBy(['email' => $value]);
                        
                        if ($existing && (!$existingId || $existing->getId() != $existingId)) {
                            $valid = false;
                            $this->validationErrors[] = 'This email address is already in use.';
                        }
                    }
                }
                break;
                
            case 'password':
                $valid = $this->validatePassword($value, $required, $minLength);
                break;
                
            case 'confirm_password':
                $password = $request->request->get('password');
                $valid = $this->validatePasswordMatch($password, $value);
                break;
                
            case 'country_code':
                $valid = $this->validateCountryCode($value, $required);
                break;
                
            case 'hs_code':
                $valid = $this->validateHsCode($value, $required);
                break;
                
            case 'phone':
                $valid = $this->validatePhone($value, $required);
                break;
                
            case 'url':
                $valid = $this->validateUrl($value, $required);
                break;
                
            case 'alphanumeric':
                $valid = $this->validateAlphanumeric($value, $fieldName, $required);
                break;
                
            case 'number':
                $valid = $this->validateNumber($value, $fieldName, $required, $min, $max);
                break;
                
            case 'required':
                $valid = $this->validateRequired($value, $fieldName, $minLength, $maxLength ?: null);
                break;
                
            case 'date':
                $valid = $this->validateDate($value, $fieldName, $required);
                break;
                
            case 'selection':
                $valid = $this->validateSelection($value, $allowedOptions, $fieldName, $required);
                break;
                
            default:
                // Generic required validation
                if ($required && empty($value)) {
                    $valid = false;
                    $this->validationErrors[] = ucfirst($fieldName) . ' is required.';
                }
        }
        
        return new JsonResponse([
            'valid' => $valid,
            'errors' => $this->getValidationErrors(),
            'message' => $valid ? '' : ($this->getFirstValidationError() ?? 'Invalid input'),
        ]);
    }
    
    #[Route('/validate/date-range', name: 'app_api_validate_date_range', methods: ['POST'])]
    public function validateDateRange(Request $request): JsonResponse
    {
        $startDate = $request->request->get('start_date');
        $endDate = $request->request->get('end_date');
        
        $this->clearValidationErrors();
        $valid = $this->validateDateRange($startDate, $endDate);
        
        return new JsonResponse([
            'valid' => $valid,
            'errors' => $this->getValidationErrors(),
            'message' => $valid ? '' : ($this->getFirstValidationError() ?? 'Invalid date range'),
        ]);
    }
}
