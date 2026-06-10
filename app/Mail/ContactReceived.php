<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactReceived extends Mailable
{
  use Queueable, SerializesModels;

  public $contactData;

  public function __construct($contactData)
  {
    $this->contactData = $contactData;
  }

  public function build()
  {
    return $this->subject('📩 Nuevo mensaje para Editorial UDB: ' . $this->contactData['subject'])
      ->view('emails.contact');
  }
}