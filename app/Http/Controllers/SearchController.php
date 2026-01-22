<?php

namespace App\Http\Controllers;

use App\Helpers\ManticoreHelper;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function testManticore()
    {
        $results = [];
        $searchQuery = request('q', 'разработчик');

        try {
            $results = ManticoreHelper::select("
                SELECT *
                FROM vacancy
                WHERE MATCH(?)
                LIMIT 10
            ", [$searchQuery]);


        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'connection' => 'Failed to connect to ManticoreSearch'
            ], 500);
        }

        return view('manticore-test', [
            'results' => $results,
            'searchQuery' => $searchQuery
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        dd($query);
        $results = [];

        if ($query) {
            try {
                $results = ManticoreHelper::search('vacancy', $query, 10);
                dd($results);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return response()->json([
            'query' => $query,
            'results' => $results,
            'count' => count($results)
        ]);
    }
}
