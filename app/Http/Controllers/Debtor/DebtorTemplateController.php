<?php

namespace App\Http\Controllers\Debtor;

use App\Exports\DebtorTemplateExport;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;

class DebtorTemplateController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new DebtorTemplateExport(), 'debtors-import-template.xlsx');
    }
}
