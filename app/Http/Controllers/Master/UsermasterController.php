<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
USE RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Validation\ValidationException;


class UsermasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $i = 0;

        $users = User::with(['employee', 'location'])->whereNull('deleted_at')->orderBy('name')->get();

        return view('master.userlist', compact('users', 'i'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();

        return view('master.usercreate', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'        => 'required|string|max:100',
                'username'    => 'required|string|max:50|unique:users,username',
                'email'       => 'required|email|unique:users,email',
                'employee_id' => 'required|exists:employees,id|unique:users,employee_id',
                'password'    => 'required|min:6',
            ]);

            User::create([
                'name'        => $request->name,
                'username'    => $request->username,
                'email'       => $request->email, 
                'employee_id' => $request->employee_id,
                'password'    => Hash::make($request->password),
                'user_type'   => 0,
            ]);

            Alert::toast('User added successfully', 'success')->autoClose(3000);
            return redirect()->route('usermaster.index');

        } catch (ValidationException $e) {
            if (isset($e->errors()['employee_id'])) {
                Alert::error('Not Allowed', 'This employee already has a user account.');
                return redirect()->back()->withInput();
            }
            throw $e;
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $employees = Employee::orderBy('name')->get();

        return view('master.usercreate', compact('user', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username,' . $id,
            'email'       => 'required|email|unique:users,email,' . $id,
            'employee_id' => 'required|exists:employees,id,' .$id,
            'password'    => 'nullable|min:6',
        ]);

        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email; 
        $user->employee_id = $request->employee_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $existing = User::where('employee_id', $request->employee_id)
                ->where('id', '!=', $id)
                ->exists();

        if ($existing) {
            Alert::error(
                'Not Allowed',
                'This employee is already assigned to another user.'
            );
            return redirect()->back()->withInput();
        }

        $user->save();

        Alert::toast('User updated successfully', 'success')->autoClose(3000);

        return redirect()->route('usermaster.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        Alert::toast('User deleted successfully', 'success')->autoClose(3000);

        return redirect()->route('usermaster.index');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);   

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file);

        $inserted = $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {

            $row = array_combine($header, $row);

            $data = [
                'name'        => $row['name'] ?? null,
                'username'    => $row['username'] ?? null,
                'email'       => $row['email'] ?? null,
                'password'    => isset($row['password'])
                                    ? Hash::make($row['password'])
                                    : Hash::make('password@123'),
                'employee_id' => $row['employee_id'] ?? null,
                'branch_id'   => $row['branch_id'] ?? null,
            ];

            $validator = Validator::make($data, [
                'name'        => 'required|string|max:100',
                'username'    => 'required|string|max:50|unique:users,username',
                'email'       => 'nullable|email|unique:users,email',
                'employee_id' => 'nullable|exists:employees,id|unique:users,employee_id',
                'branch_id'   => 'nullable|exists:branches,id',
            ]);


            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            User::updateOrCreate(
            ['username' => $data['username']],
            $data
            );

            $inserted++;
            }

            fclose($file);

            Alert::toast("Imported: $inserted | Skipped: $skipped", 'success');
            return redirect()->route('usermaster.index');
    }

    public function downloadFormat()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'name',
                'username',
                'email',
                'password',
                'employee_id',
                'branch_id'
            ]);

            // Supply your own synthetic rows. No preset account or password.

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=user_format.csv',
        ]);
    }

    public function export()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'name',
                'username',
                'email',
                'employee_id',
                'branch_id'
            ]);

            foreach (User::all() as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->username,
                    $user->email,
                    $user->employee_id,
                    $user->branch_id,
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users.csv',
        ]);
    }
}
