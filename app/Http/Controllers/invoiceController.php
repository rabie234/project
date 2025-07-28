<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\invoice;
use App\Models\invoicedetails;

class invoiceController extends Controller
{
    public function index(Request $request)
    {
        $db_name = $request->query('db_name');
        $filter = $request->input('filter') == 'all' ? '' : $request->input('filter');
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', 1);

        // Execute query directly without caching
        $sql = invoice::select('invoice.*', 'customers.description','warehouse.description as warehouse_name')
            ->leftJoin('customers', 'customers.id', '=', 'invoice.iddealer')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'invoice.idwhouse');
        if ($filter !== "") {
            $sql->where('invoice.typedoc', '=', $filter);
        }
        if ($search !== "") {
            $sql->where(function ($query) use ($search) {
                $query->where('invoice.Ref', 'like', '%' . $search . '%')
                    ->orWhere('customers.description', 'like', '%' . $search . '%')
                    ->orWhere('invoice.DocDate', 'like', '%' . $search . '%');
            });
        }

        $sql->orderBy('invoice.DocDate', 'DESC');
        $invoices = $sql->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json($invoices, 200);
    }

    public function summData(Request $request)
    {
        $db_name = $request->query('db_name');
        
        // Execute queries directly without caching
        $sumCost = invoice::where('typedoc', 3)->sum('NetAmnt');

        $sumCostdolar = invoice::select('invoice.*', 'customers.idcurrency')
            ->leftJoin('customers', 'customers.id', '=', 'invoice.iddealer')
            ->where('invoice.typedoc', 3)
            ->where('customers.idcurrency', 2)
            ->sum('invoice.NetAmnt');
        $sumCostuLL = invoice::select('invoice.*', 'customers.idcurrency')
            ->leftJoin('customers', 'customers.id', '=', 'invoice.iddealer')
            ->where('invoice.typedoc', 3)
            ->where('customers.idcurrency', 1)
            ->sum('invoice.NetAmnt');

        $sumCost = [
            'sumdolar' => $sumCostdolar,
            'sumLL' => $sumCostuLL
        ];

        $sumRevenudolar = invoice::select('invoice.*', 'customers.idcurrency')
            ->leftJoin('customers', 'customers.id', '=', 'invoice.iddealer')
            ->where('invoice.typedoc', 1)
            ->where('customers.idcurrency', 2)
            ->sum('invoice.NetAmnt');
        $sumRevenuLL = invoice::select('invoice.*', 'customers.idcurrency')
            ->leftJoin('customers', 'customers.id', '=', 'invoice.iddealer')
            ->where('invoice.typedoc', 1)
            ->where('customers.idcurrency', 1)
            ->sum('invoice.NetAmnt');

        $sumRevenu = [
            'sumdolar' => $sumRevenudolar,
            'sumLL' => $sumRevenuLL
        ];
        
        $response = [
            'sumCost' => $sumCost,
            'sumRevenu' => $sumRevenu,
        ];
        
        return response()->json($response, 200);
    }

    public function invoiceDetails(Request $request)
    {
        $db_name = $request->query('db_name');
        $invoice_id = $request->input('invoice_id');
        $ref = $request->input('ref');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', 1);

        // Execute query directly without caching
        $sql = invoicedetails::select('invoicedetails.*', 'itemfile.description as item_name', 'units.description as unit_name','invoice.Ref as ref')
            ->leftJoin('itemfile', 'itemfile.id', '=', 'invoicedetails.IdItem')
            // ->leftJoin('warehouse', 'warehouse.id', '=', 'invoicedetails.IdWh')
            ->leftJoin('invoice', 'invoice.idOp', '=', 'invoicedetails.idOp')
            ->leftJoin('units', 'units.id', '=', 'invoicedetails.IdUnit')
            ->where('invoicedetails.IdOp', $invoice_id)
            ->where('invoice.Ref', $ref);

        $invoices = $sql->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json($invoices, 200);
    }
}
