<?php

// Standalone script to import customers from Orchid Petal.xlsx
// Bootstraps Laravel framework to use Eloquent models

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

$filePath = __DIR__ . '/Orchid Petal.xlsx';

if (!file_exists($filePath)) {
    die("Excel file not found at: $filePath\n");
}

echo "Loading Excel file...\n";
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

// Skip the first row (headers)
$headers = array_shift($rows);

echo "Processing " . count($rows) . " rows...\n";

function parseExcelDate($value) {
    if (empty($value) || strtolower($value) === 'none' || trim($value) === '') {
        return null;
    }
    if (is_numeric($value)) {
        try {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        } catch (\Exception $e) {
            // fall through
        }
    }
    try {
        return Carbon::parse($value)->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}

$successCount = 0;
$updateCount = 0;

foreach ($rows as $index => $row) {
    // Map columns by index:
    // A: Sl No, B: CRN Number, C: SAP BP ID, D: Customer Name, E: Society, F: Address, G: Mobile, H: Meter No., I: Meter Type, J: Manufacturer
    // K: RFC Contractor, L: RFC Date, M: JMR Contractor, N: JMR Date, O: Mode of Payment, P: Cheque/Ref No., Q: Payment Date, R: Reg. Amount
    // S: Job Card, T: Photo, U: Remarks, V: Registration Date, W: Burner Type, X: LMC Date
    
    $name = trim($row['D'] ?? '');
    $phone = trim($row['G'] ?? '');
    
    // Minimum check: name or phone must not be empty
    if (empty($name) && empty($phone)) {
        continue;
    }
    
    $crn = trim($row['B'] ?? '');
    if (strtolower($crn) === 'none') {
        $crn = null;
    }
    
    $customerData = [
        'name' => $name ?: 'Unnamed Customer',
        'phone' => $phone ?: 'N/A',
        'crn_no' => $crn,
        'sap_bp_id' => ($row['C'] === 'None' || empty($row['C'])) ? null : trim($row['C']),
        'society' => ($row['E'] === 'None' || empty($row['E'])) ? null : trim($row['E']),
        'address' => ($row['F'] === 'None' || empty($row['F'])) ? null : trim($row['F']),
        'meter_no' => ($row['H'] === 'None' || empty($row['H'])) ? null : trim($row['H']),
        'meter_type' => ($row['I'] === 'None' || empty($row['I'])) ? null : trim($row['I']),
        'manufacturer' => ($row['J'] === 'None' || empty($row['J'])) ? null : trim($row['J']),
        'rfc_contractor' => ($row['K'] === 'None' || empty($row['K'])) ? null : trim($row['K']),
        'rfc_date' => parseExcelDate($row['L']),
        'jmr_contractor' => ($row['M'] === 'None' || empty($row['M'])) ? null : trim($row['M']),
        'jmr_date' => parseExcelDate($row['N']),
        'mode_of_payment' => ($row['O'] === 'None' || empty($row['O'])) ? null : trim($row['O']),
        'payment_ref_no' => ($row['P'] === 'None' || empty($row['P'])) ? null : trim($row['P']),
        'payment_date' => parseExcelDate($row['Q']),
        'reg_amount' => (is_numeric($row['R'])) ? floatval($row['R']) : null,
        'job_card' => ($row['S'] === 'None' || empty($row['S'])) ? null : trim($row['S']),
        'photo' => ($row['T'] === 'None' || empty($row['T'])) ? null : trim($row['T']),
        'remarks' => ($row['U'] === 'None' || empty($row['U'])) ? null : trim($row['U']),
        'registration_date' => parseExcelDate($row['V']),
        'burner_type' => ($row['W'] === 'None' || empty($row['W'])) ? null : trim($row['W']),
        'lmc_date' => parseExcelDate($row['X']),
        'source' => 'manual',
    ];
    
    // Check if CRN is provided and customer exists with that CRN
    $existing = null;
    if ($crn) {
        $existing = Customer::where('crn_no', $crn)->first();
    }
    
    if ($existing) {
        $existing->update($customerData);
        $updateCount++;
    } else {
        Customer::create($customerData);
        $successCount++;
    }
}

echo "SUCCESS: Imported $successCount new records, updated $updateCount records.\n";
