<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueReported extends Notification
{
    public function __construct(public Feedback $issue)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title'    => 'Issue reported: ' . ucfirst($this->issue->type),
            'message'  => ($this->issue->staff->full_name ?? 'Staff') . ' (' . ($this->issue->shop->name ?? 'shop') . '): '
                          . Str::limit($this->issue->message, 90),
            'issue_id' => $this->issue->id,
            'url'      => route('admin.issues'),
        ];
    }
}
