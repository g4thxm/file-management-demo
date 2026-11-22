<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Call;

class newTicket extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $id;
	
    public function __construct($id)
    {
        //
		$this->id = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$data = Call::
                leftJoin('products',"products.id","=","calls.product_id")
                ->leftJoin('branches as b1',"b1.id","=","calls.branch_id")
                ->join('branches as b2',"b2.id","=","calls.current_branch")
                ->leftJoin('customers',"customers.id","=","calls.customer_id")
                ->leftJoin('callwebs',"calls.id","=","callwebs.call_id")
                ->where('calls.id','=',$this->id)
				->select("calls.*","b1.name as frombranch","b2.name as current_branch_name","customers.name","products.name as pname", "callwebs.customer_name as customer_name", "callwebs.product_name as product_name" )
                ->get();
        
		return $this->subject('New Support ticket created - '.$this->id)->view('mail.newticket')
					->with([
                        'data' => $data,
                    ]);
    }
}
