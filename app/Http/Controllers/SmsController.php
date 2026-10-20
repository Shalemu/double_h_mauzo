<?php

namespace App\Http\Controllers;

use App\Actions\Sms\SendBulkSms;
use App\DataTables\SmsLogsDataTable;
use App\DTO\Sms\SmsMessageData;
use App\Enums\SmsStatus;
use App\Models\Customer;
use App\Models\Shops;
use App\Models\SmsLog;
use App\Models\Staff;
use App\Services\SMSService;
use Illuminate\Http\Request;

/**
 * SMS module: send messages to customers, staff or typed-in numbers through
 * Mobishastra, and keep a log of every message.
 */
class SmsController extends Controller
{
    public function index(Request $request, SMSService $sms)
    {
        $tab = $request->query('tab') === 'history' ? 'history' : 'compose';
        $table = SmsLogsDataTable::make($request);

        $customers = Customer::with('shop:id,name')->whereNotNull('phone')->where('phone', '!=', '')
            ->orderBy('name')->get(['id', 'name', 'phone', 'shop_id']);
        $staff = Staff::with('shop:id,name')->whereNotNull('phone')->where('phone', '!=', '')
            ->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone', 'shop_id']);

        return view('pages.sms.index', [
            'tab' => $tab,
            'customers' => $customers,
            'staff' => $staff,
            'shops' => Shops::orderBy('name')->get(['id', 'name']),
            'logs' => $table->paginate(),
            'stats' => $this->stats(),
            'configured' => $sms->isConfigured(),
            'sender' => config('services.mshastra.sender'),
            'status' => $table->status()?->value,
            'search' => trim((string) $request->query('q')),
        ]);
    }

    public function send(Request $request, SMSService $sms, SendBulkSms $sendBulkSms)
    {
        $validated = $request->validate([
            'audience'       => 'required|in:customers,staff,custom',
            'scope'          => 'required_unless:audience,custom|in:all,selected',
            'shop_id'        => 'nullable|exists:shops,id',
            'ids'            => 'array',
            'ids.*'          => 'integer',
            'custom_numbers' => 'required_if:audience,custom|nullable|string|max:5000',
            'message'        => 'required|string|max:918',
        ], [
            'custom_numbers.required_if' => 'Type at least one phone number.',
            'message.max' => 'The message is too long (max 918 characters = 6 SMS).',
        ]);

        if (!$sms->isConfigured()) {
            return back()->withInput()->with('error', 'SMS is not configured. Add the MSHASTRA_* settings to .env.');
        }

        $result = $sendBulkSms->execute(SmsMessageData::fromArray($validated), auth()->id());

        if ($result['total'] === 0) {
            return back()->withInput()->with('error', 'No valid phone numbers to send to.'
                . ($result['invalid'] ? ' Invalid: ' . implode(', ', array_slice($result['invalid'], 0, 5)) : ''));
        }

        $message = "Sent {$result['sent']} of {$result['total']} SMS.";
        if ($result['failed']) {
            $message .= ' Failed: ' . implode(', ', array_slice($result['failed'], 0, 5)) . (count($result['failed']) > 5 ? '…' : '') . '.';
        }
        if ($result['invalid']) {
            $message .= ' Skipped invalid numbers: ' . implode(', ', array_slice($result['invalid'], 0, 5)) . '.';
        }

        return redirect()->route('sms.index', ['tab' => 'history'])
            ->with($result['failed'] && !$result['sent'] ? 'error' : 'success', $message);
    }

    /** Numbers for the four cards at the top of the SMS page. */
    private function stats(): array
    {
        $sent = SmsStatus::Sent->value;
        $today = SmsLog::whereDate('created_at', today());
        $month = SmsLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        $monthMessages = (clone $month)->count();
        $monthSent = (clone $month)->where('status', $sent)->count();

        return [
            'today' => (int) (clone $today)->where('status', $sent)->sum('sms_count'),
            'today_people' => (clone $today)->where('status', $sent)->distinct('recipient')->count('recipient'),
            'month' => (int) (clone $month)->where('status', $sent)->sum('sms_count'),
            'month_messages' => $monthSent,
            'rate' => $monthMessages ? round($monthSent / $monthMessages * 100) : null,
            'failed' => (clone $today)->where('status', SmsStatus::Failed->value)->count(),
            'last_failed' => SmsLog::where('status', SmsStatus::Failed->value)->latest()->value('created_at'),
        ];
    }
}
