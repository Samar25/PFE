<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Mail;
use App\Mail\AppEmailSender;

class MailController extends Controller {

    public function sendEmail($name = "", $email, $subject = "", $params = [], $template = "mails.mail")
   {

        $emailParams = new \stdClass();
        if(count($params)) {
            foreach($params as $param) {
                $emailParams->{$param['name']} = $param['value'];
            }
        }

        $emailParams->usersName = $name;
        $emailParams->usersEmail = $email;
        $emailParams->template = $template;

        $emailParams->subject = $subject;
        Mail::to($emailParams->usersEmail)->send(new AppEmailSender($emailParams));
    }
}
