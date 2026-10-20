<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueResolved extends Notification
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
            'title'    => 'Your issue was resolved',
            'message'  => Str::limit($this->issue->message, 90),
            'issue_id' => $this->issue->id,
            'url'      => route('staff.report.issue.index'),
        ];
    }
}
