<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    
    public function index()
    {
        return view('dashboard.staff.report_issue.index');
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
        ]);

        $staff = Auth::guard('staff')->user();

        Feedback::create([
            'staff_id' => $staff->id,
            'shop_id'  => $staff->shop_id,
            'type'     => $request->type,
            'message'  => $request->message,
        ]);

        return back()->with('success', 'Issue reported successfully!');
    }

    public function resolve($id)
{
    $feedback = \App\Models\Feedback::findOrFail($id);
    $feedback->update(['status' => 'resolved']);

    return back()->with('success', 'Issue marked as resolved');
}
}