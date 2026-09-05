<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    private $rowNumber = 0;

    public function collection()
    {
        return Customer::latest()->get();
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'CRN Number',
            'SAP BP ID',
            'Customer Name',
            'Society',
            'Address',
            'Mobile',
            'Meter No.',
            'Meter Type',
            'Manufacturer',
            'RFC Contractor',
            'RFC Date',
            'JMR Contractor',
            'JMR Date',
            'Mode of Payment',
            'Cheque/Ref No.',
            'Payment Date',
            'Reg. Amount',
            'Job Card',
            'Photo',
            'Remarks',
            'Registration Date',
            'Burner Type',
            'LMC Date'
        ];
    }

    public function map($customer): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $customer->crn_no,
            $customer->sap_bp_id,
            $customer->name,
            $customer->society,
            $customer->address,
            $customer->phone,
            $customer->meter_no,
            $customer->meter_type,
            $customer->manufacturer,
            $customer->rfc_contractor,
            $customer->rfc_date ? $customer->rfc_date->format('Y-m-d') : '',
            $customer->jmr_contractor,
            $customer->jmr_date ? $customer->jmr_date->format('Y-m-d') : '',
            $customer->mode_of_payment,
            $customer->payment_ref_no,
            $customer->payment_date ? $customer->payment_date->format('Y-m-d') : '',
            $customer->reg_amount,
            $customer->job_card ? url('storage/' . $customer->job_card) : '',
            $customer->photo ? url('storage/' . $customer->photo) : '',
            $customer->remarks,
            $customer->registration_date ? $customer->registration_date->format('Y-m-d') : '',
            $customer->burner_type,
            $customer->lmc_date ? $customer->lmc_date->format('Y-m-d') : ''
        ];
    }
}
