<?php


namespace App\Repositories\API\Admin;


use App\Mail\SendLoginDetailMail;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class BranchRepository
{
    private $branch, $user;

    public function __construct()
    {
        $this->branch = new Branch();
        $this->user   = new User();
    }

    public function addBranch($data)
    {
        $branch = $this->branch->create([
            'branch_name'   => $data['branch_name'],
            'branch_email'  => $data['branch_email'],
            'address'       => $data['address'],
            'customer_type' => $data['customer_type']
        ]);

        $auto_generated_password = random_characters(6);

        $this->user->create([
            'branch_id'  => $branch->id,
            'first_name' => $data['branch_name'],
            'phone'      => $data['branch_contact_number'],
            'password'   => Hash::make($auto_generated_password),
        ]);

        $login_mail_data = ['username' => $data['branch_contact_number'], 'password' => $auto_generated_password];

        Mail::to($data['branch_email'])->send(new SendLoginDetailMail($login_mail_data));
    }
}
