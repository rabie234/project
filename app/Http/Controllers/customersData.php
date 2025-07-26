<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\customers;

use App\Models\invoice;
use App\Models\jvdetails;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class customersData extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter') == 'all' ? '' : $request->input('filter');
        $limit = 100;
        $search = "";
        $db_name = $request->query('db_name');
        if ($request->has('search')) {

            $search = $request->input('search');
        }
        $minutes = 3600; // Cache for 60 minutes, adjust as needed

        $cacheKey = 'customers_' . $filter . '_' . $limit . '_' . md5($search) . '_' . $db_name;

        // Check if the data is already cached
        $items = Cache::remember($cacheKey, 3600, function () use ($filter, $limit, $search) {
            $sql =  customers::select('customers.*');

            if ($search !== "") {
                $sql->where('description', 'like', '%' . $search . '%');
            }
            if ($filter !== "") {
                $sql->where('customer', '=', $filter);
            }


            return $sql->take($limit)->get();
        });

        return response()->json($items, 200);
    }

    public function accountstatments(Request $request)
    {
        // $db_name= $request->query('db_name');
        $id = $request->query('id');

        $data =  jvdetails::select(
            'jvdetails.*',
            'customers.idaccount',
            // 'warehouse.id as warehouse_id',
            // 'warehouse.description as warehouse_description'
        )
            ->join('customers', 'customers.idaccount', '=', 'jvdetails.idacc')
            // ->join('warehouse', 'warehouse.IdBranch', '=', 'jvdetails.IdBranch')
            ->where('jvdetails.idacc', $id)
            ->orderBy('jvdetails.ddate', 'desc')
            ->get();



        $sumFamt = jvdetails::where('idacc', $id)->sum('famt');

        // Return the data with the sum
        return response()->json([
            'data' => $data,
            'sum_famt' => $sumFamt
        ], 200);
    }


    public function TopCustomers(Request $request)
    {
        $rate_lbp = DB::table('currencies')
            ->where('id', 1)
            ->value('USDRate');

        $minutes = 3600;

        $db_name = $request->query('db_name');

        $topSupplier = Cache::remember('top_supplier_' . $db_name, $minutes, function () use ($rate_lbp) {
            return invoice::select('customers.description','customers.idcurrency', 'customers.acc_code')
                ->selectRaw('SUM(CASE WHEN customers.idcurrency=1 THEN invoice.NetAmnt/? ELSE invoice.NetAmnt END) AS amount, MAX(customers.email) AS email, MAX(customers.tel) AS tel, MAX(customers.City) AS City', [$rate_lbp])
                ->join('customers', 'customers.id', '=', 'invoice.iddealer')
                ->where('customers.customer', 0)
                ->groupBy('customers.description', 'customers.acc_code','customers.idcurrency')
                ->orderByDesc('amount')
                ->take(5)
                ->get();
        });

        $topCustomer = Cache::remember('top_customers_' . $db_name, $minutes, function () use ($rate_lbp){
            return invoice::select('customers.description','customers.idcurrency', 'customers.acc_code')
            ->selectRaw('SUM(CASE WHEN customers.idcurrency=1 THEN invoice.NetAmnt/? ELSE invoice.NetAmnt END) AS amount, MAX(customers.email) AS email, MAX(customers.tel) AS tel, MAX(customers.City) AS City', [$rate_lbp])
            ->join('customers', 'customers.id', '=', 'invoice.iddealer')
                ->where('customers.customer', 1)
                ->groupBy('customers.description', 'customers.acc_code','customers.idcurrency')
                ->orderByDesc('amount')
                ->take(5)
                ->get();
        });

        $responseData = [
            'topSupplier' => $topSupplier,
            'topCustomers' => $topCustomer

        ];

        return response()->json($responseData, 200);
    }
}
