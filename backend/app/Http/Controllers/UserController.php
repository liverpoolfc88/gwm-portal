<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        $user = auth('api')->user();
        //return $user;
        if ( $user->hasRole(['superadmin', 'admin']) ){
            if ( $user->hasRole('superadmin') ){
                $model = User::with('dealers')->with('roles')
                    ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
                return $model;
            }

            if ( $user->hasRole('admin') ){
                $model = User::with('dealers')->with('roles')
                ->whereDoesntHave('roles', function ($query) {
                    return $query->whereIn('name', ['superadmin', 'admin']);
                })
                     ->orWhere('id', '=', $user->id)
                    ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
                return $model;
            }
        }else{
            $model = User::with('dealers')->with('roles')
                ->where('id', '=' , $user->id)
                ->paginate($itemsPerPage == '-1' ? 1000 : $itemsPerPage, ['*'], 'page name', $page);
            return $model;
        }

    }


    public function role(){
        //return Role::all();
        $user = auth('api')->user();
        if ( $user->hasRole('superadmin') ){
            return Role::all();
        }

        if ( $user->hasRole('admin') ){
            return Role::where('name', '!=', 'superadmin')->where('name', '!=', 'admin')
            ->get();
        }
    }


    public function show()
    {
        return User::with('roles.permissions:name')->with('permissions:name')
        ->where('id', Auth::id())->first();
    }


    public function updateRole(Request $request,  User $user){
        $user = User::find($request->input('id'));
//        return 'user.id='.$request->input('id');
        $user->detachRoles();
        $roles = $request->input('roles');
//        dd($roles);
        foreach ($roles as $rkey => $role) {
            $user->roles()->attach($role['id']);
        }
        return 'ok';

    }



    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'username' => 'required|string|unique:users,username,'.$request['id'].',id',
            'email' => 'required|email|max:100|unique:users,email,'.$request['id'].',id',
        ]);
        $model = User::find($request->input('id'));
        if (!$model) {
            $model = new User();
        }
        $model->name = $request['name'];
        $model->username = $request['username'];
        $model->email = $request['email'];
        $model->role_id = ($request['role_id'] != null) ? $request['role_id']: 3;
        $model->dealer_id = $request['dealer_id'];
        $model->password = Hash::make($request['password']);
        $model->save();
    }
    public function password(Request $request, User $user)
    {
//        return $request;
        $model = User::find($request->id);

        $model->password = Hash::make($request['password']);
        $model->save();
    }
    public function destroy(User $user, $id)
    {
        $model = User::find($id);
        $model->delete();
    }
}











