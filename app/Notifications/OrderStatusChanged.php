<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title'    => 'Order #' . $this->order->id . ' ' . $this->order->status,
            'message'  => $this->order->admin_note ?: 'Your order request was marked ' . $this->order->status . '.',
            'order_id' => $this->order->id,
            'url'      => route('staff.orders.show', $this->order->id),
        ];
    }
}
