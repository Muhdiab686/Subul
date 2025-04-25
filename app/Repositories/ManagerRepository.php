<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManagerRepository
{
    public function createCustomer(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function getAllCustomers()
    {
        return User::whereIn('role', ['customer', 'company_client'])->get();
    }


    public function findCustomer($id)
    {
        return User::find($id);
    }

    public function deleteCustomer(User $user)
    {
        return $user->delete();
    }
}
