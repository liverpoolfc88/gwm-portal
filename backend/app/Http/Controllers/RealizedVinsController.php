<?php

namespace App\Http\Controllers;

use App\User;
use App\RealizedVin;
use App\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SoapController;

class RealizedVinsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        $user = auth('api')->user();
        if ( $user->hasRole(['superadmin', 'admin']) ){
            return RealizedVin::latest()
                ->with('dealervin')
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);

        }else{
            if ( $user->hasRole('distributor') ){
                return RealizedVin::latest()
                    ->with('dealervin')
                    ->where('realized_vins.dealer_id', '=', $user->dealer_id)
                    ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
            }

        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'vin' => 'required|string|min:17|unique:realized_vins',
        ]);
        $bac = Dealer::findOrFail(auth('api')->user()->dealer_id)->bac;
        $SoapController = new SoapController();

        $params = ['IS_DATA' => [
            'item' => [
                'DEALER_CODE' => $bac,
                'LINES' => [
                    'item' => [
                        'SALE_DATE' => $request['sdate'],
                        'VIN' => $request['vin']
                    ]
                ]
            ]
        ]
        ];

        $soapData = $SoapController->index($params);
        $status = $soapData[0]->EV_STAT;
        $status1 = $soapData[0]->ES_LINES->item->STAT;

        if ($status == 'OK') {
            $data = RealizedVin::Create([
                'vin' => $request['vin'],
                'sdate' => $request['sdate'],
                'dealer_id' => auth('api')->user()->dealer_id
            ]);
            return [
                'message' => 'ok',
                'status' => 1,
                'data' => $data
            ];
        } else {
            return [
                'message' => $status . '. ' . $status1,
                'status' => 0,
                'data' => []
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $realizedVin = RealizedVin::findOrFail($request->input('id'));
        $realizedVin->update($request->all());
//         if (!$model) {
//             $model = new RealizedVin();
//         }
//         $model->vin = $request['vin'];
//         $model->sdate = $request['sdate'];
// //        $user = User::where('id', Auth::id())->first();
//         $user = auth('api')->user()->dealers_id;
//         $model->dealer_id = $user;
//         $model->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $realizedVin = RealizedVin::findOrFail($id);
        $realizedVin->delete();
    }
}
