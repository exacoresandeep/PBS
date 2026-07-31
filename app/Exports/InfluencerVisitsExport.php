<?php

namespace App\Exports;

use App\Models\InfluencerVisit;
use App\Models\Target;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InfluencerVisitsExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    protected $year, $month, $employee_type_id,$row = 1;
    protected $targetsByEmployee = [];
    protected $achievedByEmployee = [];
    protected $employees = [];
protected $product_id;

    public function __construct($year, $month,$employee_type_id,$product_id)
    {
	    $this->employee_type_id = $employee_type_id;
	     $this->product_id = $product_id;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
	    $year=$this->year;
         $month=$this->month;
	    $productID=  $this->product_id ?? \App\Helpers\ProductHelper::getSelectedProductID();
	    $this->employees = Employee::select('id', 'name', 'employee_code')
	   ->when($this->employee_type_id != "", function ($query) {
        $query->where('employee_type_id', $this->employee_type_id);
    })->whereRaw("JSON_CONTAINS(products, ?)", ['["' . $productID . '"]'])->get();

        $this->targetsByEmployee = Target::where('month', $this->month)
	     ->where('year', $this->year)
     ->where('product_id', $productID)
            ->pluck('customer_visit', 'employee_id')
            ->toArray();

    $this->achievedByEmployee = InfluencerVisit::
	    //whereYear('created_at', $this->year)
           // ->whereMonth('created_at', $this->month)
	    whereNotNull('phone')
->where(function ($q) use ($year, $month) {

                $q->where(function ($q1) use ($year, $month) {
                    $q1->whereYear('updated_at', $year)
                    ->whereMonth('updated_at', $month);
                })

                ->orWhere(function ($q2) use ($year, $month) {
                    $q2->whereYear('follow_up_date', $year)
                    ->whereMonth('follow_up_date', $month);
                });

            })
    ->whereHas('order', function ($query) use ($productID) {  //push
                $query->where('product_id', $productID);
            })
            ->select('created_by', DB::raw('COUNT(DISTINCT phone) as count'))
            ->groupBy('created_by')
            ->pluck('count', 'created_by')
            ->toArray();
return $this->employees->filter(function ($employee) {
            $employeeId = $employee->id;
            return ($this->achievedByEmployee[$employeeId] ?? 0) > 0;
        })->values();
       // return $this->employees;
    }

    public function map($employee): array
    {
        $employeeId = $employee->id;

        return [
            $this->row++, 
            $employee->name,
            $employee->employee_code,
            $this->targetsByEmployee[$employeeId] ?? 0,
            $this->achievedByEmployee[$employeeId] ?? 0,
        ];
    }

    public function headings(): array
    {
        return [
            'Sl. No',
            'Employee Name',
            'Employee Code',
            'Target',
            'Achieved'
        ];
    }
}
