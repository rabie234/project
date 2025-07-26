<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\invoice;
use App\Models\invoicedetails;
use Illuminate\Support\Facades\Cache;

class invoiceController extends Controller
{
    public function index(Request $request)
    {
        $db_name = $request->query('db_name');
        $filter = $request->input('filter') == 'all' ? '' : $request->input('filter');
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', 1);


        $cacheKey = 'invoices_' . $filter  . '2_' . '_' . $page . '_' . $perPage . md5($search) . '_' . $db_name;

        // Check if the data is already cached
        $invoices = Cache::remember($cacheKey, 3600, function () use ($filter, $search, $page, $perPage) {
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

            return $sql->paginate($perPage, ['*'], 'page', $page);
        });
        return response()->json($invoices, 200);
    }

    public function summData(Request $request)
    {
        $db_name = $request->query('db_name');
        $response =  Cache::remember('summDatainvoices_' . $db_name, 3600, function () {
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
            return [
                'sumCost' => $sumCost,
                'sumRevenu' => $sumRevenu,
            ];
        });
        return response()->json($response, 200);
    }

    public function invoiceDetails(Request $request)
    {
        $db_name = $request->query('db_name');
        $invoice_id = $request->input('invoice_id');
        $ref = $request->input('ref');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', 1);

        $cacheKey = 'invoices_www'  . $invoice_id . '_' . $ref . '_' . $page . '_' . $perPage .  '_' . $db_name;

        $invoices = Cache::remember($cacheKey, 3600, function () use ($page, $perPage, $invoice_id, $ref) {
            $sql = invoicedetails::select('invoicedetails.*', 'itemfile.description as item_name', 'units.description as unit_name','invoice.Ref as ref')
                ->leftJoin('itemfile', 'itemfile.id', '=', 'invoicedetails.IdItem')
                // ->leftJoin('warehouse', 'warehouse.id', '=', 'invoicedetails.IdWh')
                ->leftJoin('invoice', 'invoice.idOp', '=', 'invoicedetails.idOp')
                ->leftJoin('units', 'units.id', '=', 'invoicedetails.IdUnit')
                ->where('invoicedetails.IdOp', $invoice_id)
                ->where('invoice.Ref', $ref);

            return $sql->paginate($perPage, ['*'], 'page', $page);
        });
        return response()->json($invoices, 200);
    }
}
