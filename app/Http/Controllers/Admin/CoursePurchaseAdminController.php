<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Services\CourseAccessNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoursePurchaseAdminController extends Controller
{
    public function __construct(
        private CourseAccessNotifier $accessNotifier,
    ) {}

    public function index(): View
    {
        $purchases = CoursePurchase::query()
            ->with(['user.profile', 'course', 'reviewer'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.purchases.index', compact('purchases'));
    }

    public function approve(Request $request, CoursePurchase $purchase): RedirectResponse
    {
        if (! $purchase->isPending()) {
            return redirect()->route('admin.purchases.index')->with('status', __('Already reviewed.'));
        }

        $purchase->update([
            'status' => PurchaseStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'note' => $request->input('note'),
        ]);

        Enrollment::query()->firstOrCreate([
            'user_id' => $purchase->user_id,
            'course_id' => $purchase->course_id,
        ]);

        if ($purchase->user && $purchase->course) {
            $this->accessNotifier->notifyUser($purchase->user, $purchase->course, 'purchase_approved');
        }

        return redirect()->route('admin.purchases.index')->with('status', __('Purchase approved.'));
    }

    public function reject(Request $request, CoursePurchase $purchase): RedirectResponse
    {
        if (! $purchase->isPending()) {
            return redirect()->route('admin.purchases.index')->with('status', __('Already reviewed.'));
        }

        $purchase->update([
            'status' => PurchaseStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'note' => $request->input('note'),
        ]);

        return redirect()->route('admin.purchases.index')->with('status', __('Purchase rejected.'));
    }

    public function slip(CoursePurchase $purchase): StreamedResponse
    {
        abort_unless($purchase->slipExists(), 404);

        return Storage::disk($purchase->slip_disk)->response(
            $purchase->slip_path,
            basename($purchase->slip_path),
        );
    }
}
