<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\Feedback;
use App\Models\Users;
use App\Notifications\IssueReported;
use App\Notifications\IssueResolved;

class FeedbackController extends Controller
{

    public function index()
    {
        $staff = \App\Models\Staff::current();
        $issues = Feedback::where('staff_id', $staff->id)
            ->latest()
            ->paginate(10);

        return view('pages.staff.report_issue.index', compact('issues'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
        ]);

        $staff = \App\Models\Staff::current();

        $issue = Feedback::create([
            'staff_id' => $staff->id,
            'shop_id'  => $staff->shop_id,
            'type'     => $request->type,
            'message'  => $request->message,
        ]);

        $admins = Users::notStaff()
            ->where(fn ($q) => $q->whereIn('role_id', [1, 2])->orWhere('super_user', true))
            ->get();
        Notification::send($admins, new IssueReported($issue->load('staff', 'shop')));

        return back()->with('success', 'Issue reported successfully!');
    }

    public function resolve($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => 'resolved']);

        if ($feedback->staff) {
            $feedback->staff->notify(new IssueResolved($feedback));
        }

        return back()->with('success', 'Issue marked as resolved');
    }
}
