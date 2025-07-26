<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Models\itemfile;

use App\Models\invoicedetails;
use Illuminate\Support\Facades\Cache;

class itemsfile extends Controller
{
    public function index(Request $request)
    {



        $filter = $request->input('filter') == 'all' ? '' : $request->input('filter');
        $limit = 100;
        $search = $request->input('search', '');
        $dbName = $request->input('db_name');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', 1);

        // Generate a unique cache key based on filter and search parameters
        $cacheKey = 'itemsh_f_' . $dbName . $filter . '__' . $limit . '_' . $page . '_' . $perPage . '_' . md5($search);

        // Check if the data is already cached
        $items = Cache::remember($cacheKey, 3600, function () use ($filter, $limit, $search, $page, $perPage) {
            $sql = itemfile::select('itemfile.*', 'itemunit.Price', 'itemunit.idunit', 'units.description as unitName')
                ->leftJoin('itemunit', 'itemfile.id', '=', 'itemunit.iditem')
                ->leftJoin('units', 'units.id', '=', 'itemunit.idunit');

            if ($search !== "") {
                $sql->where('itemfile.description', 'like', '%' . $search . '%');
            }
            if ($filter !== "") {
                $sql->where('itemfile.isActive', '=', $filter);
            }



            return $sql->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json($items, 200);
    }



    public function minMaxItem(Request $request)
    {
        $minutes = 3600;

        $db_name = $request->query('db_name');

        $topItems = Cache::remember('top_items__' . $db_name, $minutes, function () {
            return invoicedetails::select('invoicedetails.IdItem', 'itemfile.Description AS Description', 'itemfile.itemcode AS code', 'itemunit.Price AS price')
                ->selectRaw('SUM(qty) AS sumQty')
                ->join('itemfile', 'itemfile.id', '=', 'invoicedetails.IdItem')
                ->join('itemunit', function ($join) {
                    $join->on('itemfile.id', '=', 'itemunit.iditem')
                        ->on('itemfile.baseunit', '=', 'itemunit.idunit');
                })
                ->groupBy('IdItem', 'Description', 'code', 'price')
                ->orderByDesc('sumQty')
                ->take(15)
                ->get();
        });

        $minItems = Cache::remember('min_items__' . $db_name, $minutes, function () {
            return invoicedetails::select('invoicedetails.IdItem', 'itemfile.Description AS Description', 'itemfile.itemcode AS code', 'itemunit.Price AS price')
                ->selectRaw('SUM(qty) AS sumQty')
                ->join('itemfile', 'itemfile.id', '=', 'invoicedetails.IdItem')
                ->join('itemunit', function ($join) {
                    $join->on('itemfile.id', '=', 'itemunit.iditem')
                        ->on('itemfile.baseunit', '=', 'itemunit.idunit');
                })
                ->groupBy('IdItem', 'Description', 'code', 'price')
                ->orderBy('sumQty')
                ->take(15)
                ->get();
        });

        $responseData = [
            'topItems' => $topItems,
            'minItems' => $minItems,
        ];

        return response()->json($responseData, 200);
    }


    public function itemsCount()
    {
        $itemsCount = itemfile::count();
        return $itemsCount;
    }
}
