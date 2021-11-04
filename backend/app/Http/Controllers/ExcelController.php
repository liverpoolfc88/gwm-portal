<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\ProducedTempVin;
use App\ProducedVin;
use Illuminate\Http\Request;
use App\Imports\ProducedVinImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

class ExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return 'sasas';
    }
    public function clear()
    {
        $user = auth('api')->user();
        ProducedTempVin::where('user_id','=', $user->id)->delete();
        return 'clear!';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $productvinimport = new ProducedVinImport();

//        $this->validate($request, [
//            'model_code' => 'required|integer|min:7',
//        ]);
//        $request->validate([
//            'model_code' => 'integer|min:7',
//        ]);

//        return ($request);
//        var_dump($request->file); die();

        $file = $request->file('file');


        Excel::import($productvinimport, $file);


//        return back()->withStatus('Excel import bo`ldi!');
//        Excel::import(new UsersImport(), $file);
        return 'okkkk';
//        return $request->file['data']['data'];
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
    public function producedvinIndex(Request $request)
    {
        $user = auth('api')->user();
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        return ProducedVin::where('user_id','=', $user->id)
        ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);

//        return ProducedVin::all();
//        return ProducedVin::latest();
    }


    public function producedvin(Request $request)
    {
        $user = auth('api')->user();
        $producedtempvin = ProducedTempVin::where('user_id','=', $user->id)->get();

        if ($request) {
            foreach ($producedtempvin as $key => $ptv) {
                $producedvin = new ProducedVin();
                $producedvin->skd_plant = $ptv['skd_plant'];
                $producedvin->vin_gm = $ptv['vin_gm'];
                $producedvin->vin_local = $ptv['vin_local'];
                $producedvin->model_code = $ptv['model_code'];
                $producedvin->model_year = $ptv['model_year'];
                $producedvin->engine = $ptv['engine'];
                $producedvin->full_option = $ptv['full_option'];
                $producedvin->produced_date = $ptv['produced_date'];
                $producedvin->to_dealer = $ptv['to_dealer'];
                $producedvin->sold_date = $ptv['sold_date'];
                $producedvin->user_id = $user->id;
                $producedvin->save();
                $ptv->delete();
            }
            ProducedTempVin::where('user_id','=', $user->id)->delete();
            return 'okkk';
        }
        return 'noooo';
    }

    public function produced()
    {
//        DB::update(
//            'update produced_temp_vins set produced_temp_vins.validate = 1 where
//                   produced_temp_vins.vin_gm in ( select produced_vins.vin_gm from produced_vins) and
//                   produced_temp_vins.vin_local in ( select produced_vins.vin_local from produced_vins)'
//        );
        DB::update(
            'update produced_temp_vins set produced_temp_vins.validate = 1 where
                   produced_temp_vins.vin_gm in ( select produced_vins.vin_gm from produced_vins)'
        );

        DB::update(
            'update produced_temp_vins set produced_temp_vins.validate = 2 where
                   produced_temp_vins.vin_local in ( select produced_vins.vin_local from produced_vins)'
        );

        $user = auth('api')->user();

        $ptv = ProducedTempVin::where('user_id','=', $user->id)->get();

        return $ptv;

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
    public function update(Request $request, ProducedTempVin $producedTempVin)
    {
        $model = ProducedTempVin::find($request->input('id'));
        $model->vin_gm = $request['vin_gm'];
        $model->vin_local = $request['vin_local'];
        $model->validate =null;
        $model->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
