<?php

namespace App\Http\Controllers;

use App\Dealer;
use Illuminate\Http\Request;

class DealersController extends Controller
{
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
            return Dealer::latest()
            ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
        }else{
            return Dealer::latest()
            ->where('id', '=', $user->dealer_id)
            ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dealer $dealers)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:dealers,name,'.$request['id'].',id',
            'bac' => 'required|string|unique:dealers,name,bac'.$request['id'].',id',
        ]);
        $model = Dealer::find($request->input('id'));
        if (!$model) {
            $model = new Dealer();
        }
        $model->name = $request['name'];
        $model->address = $request['address'];
        $model->bac = $request['bac'];
        $model->country = $request['country'];
        $model->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dealer $dealer, $id)
    {
        $model = Dealer::find($id);
        $model->delete();
    }
}
