<?php


namespace App\Repositories\API\Admin;


use App\Mail\SendLoginDetailMail;
use App\Models\Branch;
use App\Models\User;
use Exception;
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

    public function branches()
    {
        $branches = $this->branch->get();

        if (empty($branches->toArray())) {
            throw new Exception("There is no branch available right now");
        }

        $branch_data = [];

        foreach ($branches as $branch) {
            $branch_data[] = [
                'branch_name'  => $branch->branch_name,
                'branch_email' => $branch->branch_email,
                'address'      => $branch->address,
                'gst_number'   => $branch->gst_number,
                'is_active'    => $branch->is_active
            ];
        }

        return $branch_data;
    }

    public function addBranch($data)
    {
        $branch = $this->branch->create([
            'branch_name'  => $data['branch_name'],
            'branch_email' => $data['branch_email'],
            'address'      => $data['address'],
            'gst_number'   => $data['gst_number']
        ]);

        $auto_generated_password = random_characters(6);

        $this->user->create([
            'branch_id'  => $branch->id,
            'first_name' => $data['branch_name'],
            'phone'      => $data['branch_contact_number'],
            'password'   => Hash::make($auto_generated_password),
        ]);

        $login_mail_data = ['username' => $data['branch_contact_number'], 'password' => $auto_generated_password];

        Mail::send('emails.send_login_detail', $login_mail_data, function ($message) use ($data) {
            $message->to($data['branch_email'])->subject('Login Details.!');
        });
        return;
    }

    public function editBranch($branchId, $data)
    {
        $branch = $this->branch->where("id", $branchId)->first();

        if (empty($branch)) {
            throw new Exception("Invalid branch id");
        }

        $branch->branch_name  = $data['branch_name'];
        $branch->gst_number   = $data['gst_number'];
        $branch->branch_email = $data['branch_email'];
        $branch->address      = $data['address'];
        $branch->save();
    }
}
