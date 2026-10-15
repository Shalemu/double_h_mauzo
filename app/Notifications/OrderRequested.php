<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderRequested extends Notification
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
        $staff = $this->order->staff;

        return [
            'title'    => 'New order request #' . $this->order->id,
            'message'  => ($staff->full_name ?? 'Staff') . ' (' . ($this->order->shop->name ?? 'shop') . ') requested '
                          . $this->order->items()->count() . ' item(s) — Tsh ' . number_format($this->order->total_amount, 2),
            'order_id' => $this->order->id,
            'url'      => route('admin.orders.show', $this->order->id),
        ];
    }
}
