<?php

namespace App\Mail;

use App\Models\File;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileTransferredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $file;
    public $fromEmployee;
    public $remarks;

    public function __construct(File $file, Employee $fromEmployee, $remarks = null)
    {
        $this->file = $file;
        $this->fromEmployee = $fromEmployee;
        $this->remarks = $remarks;
    }

    public function build()
    {
        return $this->subject('File transferred: ' . $this->file->official_file_number)
                    ->view('emails.file_transferred');
    }
}
