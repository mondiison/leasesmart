<?php

namespace App\Http\Controllers;

use App\Support\Search\GlobalSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearch $search): View
    {
        $term = trim((string) $request->query('q', ''));

        return view('search.index', [
            'term' => $term,
            'groups' => $search->for($request->user(), $term),
        ]);
    }
}
