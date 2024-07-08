<?php

namespace App\Models;

use App\Http\Enums\PaymentStatus;
use App\Mail\MailSendOrder;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class EmailTemplate extends Model
{
    use HasFactory, Filterable;

    protected $guarded = [];

    protected $text_fields = ['name'];
    public $filterFields  = ['name', 'subject', 'json_content', 'html_content', 'type', 'status'];

    public static function sendMailAddOrder($data) {
        $templateMail = EmailTemplate::where('type', 'order-purchase-admin')->orWhere('type', 'order-purchase-customer')->get()->keyBy(function($item) {
            return $item->type;
        });
        
        $email_admin = Config::getOptions('admin_email');
        $email_customer = $data['email'];
        $listProductItem = $data['list_product_item'];
        $product_item = '';
        foreach ($listProductItem as $key => $value) {
            $product_item .= '<br>'.($key + 1).'. Name: '.$value->name.'<br>';
            $product_item .= 'Description: '.$value->description.'<br>';
        }
       
        $replaceArr = array(
            'email' => $data['email'],
            'phone' => $data['phone'],
            'total' => $data['total'],
            'discount' => $data['discount'],
            'payment_status' => $data['payment_status'],
            'product_id' => $data['product_id'],
            'product_name' => $data['product']->name,
            'count_product' => $data['count_product'],
            'user_id' => $data['user_id'],
            'user_name' => $data['user']->full_name,
            'user_amount' => $data['user']->amount,
            'user_email' => $data['user']->email,
            'product_items' => $product_item
        );
        
        if ($templateMail['order-purchase-customer']) {
            $subject = $templateMail['order-purchase-customer']->subject;
            $templateHtml = EmailTemplate::replaceTemplate($templateMail['order-purchase-customer']->html_content, $replaceArr);
            $mail = new MailSendOrder($templateHtml, $subject);
            Mail::to($email_customer)->send($mail);
            
        } 
        if ($templateMail['order-purchase-admin'] && !empty($email_admin)) {
            $subject = $templateMail['order-purchase-admin']->subject;
            $templateHtml = EmailTemplate::replaceTemplate($templateMail['order-purchase-admin']->html_content, $replaceArr);
            $mail = new MailSendOrder($templateHtml, $subject);
            Mail::to($email_admin)->send($mail);
        }
            
    }

    public static function replaceTemplate($template, $replaceArr = array()) {
        $template = str_replace('{{email}}', $replaceArr['email'], $template);
        $template = str_replace('{{phone}}', $replaceArr['phone'], $template);
        $template = str_replace('{{total}}', $replaceArr['total'], $template);
        $template = str_replace('{{discount}}', $replaceArr['discount'], $template);
        $template = str_replace('{{payment_status}}', PaymentStatus::getDisplay($replaceArr['payment_status']), $template);
        $template = str_replace('{{full_name}}', $replaceArr['full_name'], $template);
        $template = str_replace('{{amount}}', $replaceArr['amount'], $template);
        $template = str_replace('{{product_id}}', $replaceArr['product_id'], $template);
        $template = str_replace('{{product_name}}', $replaceArr['product_name'], $template);
        $template = str_replace('{{count_product}}', $replaceArr['count_product'], $template);
        $template = str_replace('{{user_id}}', $replaceArr['user_id'], $template);
        $template = str_replace('{{user_name}}', $replaceArr['user_name'], $template);
        $template = str_replace('{{user_amount}}', $replaceArr['user_amount'], $template);
        $template = str_replace('{{user_email}}', $replaceArr['user_email'], $template);
        $template = str_replace('{{product_items}}', $replaceArr['product_items'], $template);
        return $template;
    }
}
