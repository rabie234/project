<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\customers;
use Carbon\Carbon;
use App\Models\invoice;

use Illuminate\Support\Facades\DB;
use App\Models\itemfile;

use Illuminate\Support\Facades\Cache;
class dashboard extends Controller
{
    public function index(Request $request){
        
        $data = $this->Counts($request);
        return response()->json($data, 200);
    }




    public function getMonthlyNetAmountForCurrentYear($db_name,$type)
    {
        $currentYear = Carbon::now()->year;
        // $db_name = $this->request->get("db_name");
    
        // Define a unique cache key
        $cacheKey = 'monthly_net_amount_'.$db_name.'_' . $currentYear.'_'.$type;
    
        // Attempt to retrieve data from the cache
        $results = Cache::remember($cacheKey, 3600, function() use ($currentYear,$type) {
         
    $query = DB::table('invoice')
    ->select(
        DB::raw('SUM(NetAmnt) AS amount'),
        DB::raw('DATE_FORMAT(STR_TO_DATE(docDate, "%Y-%m-%d"), "%M") AS month'),
        DB::raw('typedoc')
    )
    ->where('typedoc','=', $type)
    ->whereYear(DB::raw('STR_TO_DATE(docDate, "%Y-%m-%d")'), $currentYear)
    ->groupBy(DB::raw('typedoc'))
    ->groupBy(DB::raw('DATE_FORMAT(STR_TO_DATE(invoice.docDate, "%Y-%m-%d"), "%M")'))

    // ->orderBy(DB::raw('MONTH(STR_TO_DATE(docDate, "%Y-%m-%d"))'))
    ->get();


          return $query;
        });
    
        // Return the results as JSON
        return $results;
    }

    public function Counts($request)
    {
        // Define a unique cache key
       $db_name= $request->query('db_name');
        $cacheKey = 'dashboard_data_'.$db_name;
       
        // Attempt to retrieve cached data
        $response = Cache::remember($cacheKey, 3600,function() use ($db_name) {
            $formattedDate = Carbon::now()->format('Y-m-d');
            $customersCount = customers::where('customer', 1)->count();
            $suppliersCount = customers::where('customer', 0)->count();
            $revenu = invoice::where('typedoc', 1)->count();
            $cost = invoice::where('typedoc', 3)->count();
            $itemsCount = itemfile::count();
            $transction = invoice::where('DocDate', $formattedDate)->count();
            $costChart = $this->getMonthlyNetAmountForCurrentYear($db_name,3);
             $revenuChart = $this->getMonthlyNetAmountForCurrentYear($db_name,1);
             $chartData = [
                'revenuChart' => $revenuChart,
                'costChart' => $costChart
                
             ];
          
            $countData = [
                'itemsCount' => $itemsCount,
                'customersCount' => $customersCount,
                'suppliersCount' => $suppliersCount,
                'revenu' => $revenu,
                'cost' => $cost,
                'transaction' => $transction,
            ];
            return [
                'countData' => $countData,
                'chartData' => $chartData
            ];
        });
    
        return $response;
    }
    

}
