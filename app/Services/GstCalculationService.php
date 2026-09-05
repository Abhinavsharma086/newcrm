<?php

namespace App\Services;

class GstCalculationService
{
    public function calculateGst($amount, $taxRate, $customerState, $companyState = null)
    {
        $companyState = $companyState ?? config('app.company_state', 'Delhi');
        
        $taxAmount = ($amount * $taxRate) / 100;
        
        // Interstate - IGST applicable
        if (strtolower($customerState) !== strtolower($companyState)) {
            return [
                'cgst' => 0,
                'sgst' => 0,
                'igst' => $taxAmount,
                'total_tax' => $taxAmount,
            ];
        }
        
        // Same state - CGST + SGST
        $cgst = $taxAmount / 2;
        $sgst = $taxAmount / 2;
        
        return [
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => 0,
            'total_tax' => $taxAmount,
        ];
    }

    public function validateGstin($gstin)
    {
        // GSTIN format: 2 digits (state code) + 10 chars (PAN) + 1 digit + 1 letter + 1 alphanumeric
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
        return preg_match($pattern, $gstin);
    }
}
