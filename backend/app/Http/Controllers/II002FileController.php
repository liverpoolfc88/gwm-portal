<?php

namespace App\Http\Controllers;

use App\II002File;
use Illuminate\Http\Request;

class II002FileController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        $user = auth('api')->user();


        if ( $user->hasRole(['superadmin', 'admin']) ){
            return II002File::latest()
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
        } else {
            return II002File::latest()
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
        }
    }
}
