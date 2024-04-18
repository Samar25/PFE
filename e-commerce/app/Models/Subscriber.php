<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Subscriber;

class Subscriber extends Model
{
    protected $table = 'subscribers';

    public static function isSubscribed($email) 
    {
        return !is_null(self::where('email', $email)->first());
    }

    public static function sendSubscriptionEmail($email)
    {
        app('App\Http\Controllers\MailController')->sendEmail(
            "",
            $email,
            "User Newsletter subscribed",
            [],
            "mails.user-subscription"
        );
    }
}
