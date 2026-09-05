<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\GstApiService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function verifyGstin(Request $request, $gstin, GstApiService $gstService)
    {
        $gstDetails = $gstService->verifyGstin($gstin);

        if ($gstDetails) {
            $name = $gstDetails['legal_name'] ?: $gstDetails['trade_name'];
            return response()->json([
                'success' => true,
                'source' => 'verified',
                'data' => [
                    'name' => $name,
                    'company_name' => $name,
                    'trade_name' => $gstDetails['trade_name'],
                    'legal_name' => $gstDetails['legal_name'],
                    'address' => $gstDetails['address'],
                    'city' => $gstDetails['city'],
                    'state' => $gstDetails['state'],
                    'zip' => $gstDetails['zip'],
                    'taxpayer_type' => $gstDetails['taxpayer_type'] ?? 'Regular',
                    'status' => $gstDetails['status'] ?? 'Active'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid GSTIN or unable to fetch taxpayer details.'
        ], 404);
    }
}
