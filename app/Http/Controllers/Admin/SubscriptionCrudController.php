<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionRequest;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SubscriptionCrudController extends Controller
{
    /**
     * Display a listing of the resource with merged filters.
     */
    public function index(Request $request)
    {
        // 1. Query Start Karein
        $query = Subscription::with(['user', 'plan']);

        // 2. Search Code Filter
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        // 3. Search Payment ID Filter
        if ($request->filled('payment_id')) {
            $query->where('payment_id', 'like', '%' . $request->payment_id . '%');
        }

        // 4. Search Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 5. Search Plan Filter
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        // 6. Pagination & Ordering
        $subscriptions = $query->latest()->paginate(10);

        // 7. Filter Dropdown ke liye Plans fetch karein
        $plans = Plan::where('is_active', 1)->select('id', 'name')->get();

        // 8. Agar AJAX Request hai (Search/Filter ke waqt), to sirf Table Partial return karein
        if ($request->ajax()) {
            return view('admin.subscriptions.partials.table', compact('subscriptions'))->render();
        }

        // 9. Normal Page Load
        return view('admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ✅ FIX: 'name' column hata kar 'first_name', 'last_name' use kiya
        // Agar aapke table me columns kuch aur hain to unhe yahan update karein
        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereNull('deleted_at') // Optional safety
            ->latest()
            ->limit(100)
            ->get();

        $plans = Plan::where('is_active', 1)->select('id', 'name', 'price', 'duration')->get();

        if (request()->ajax()) {
            return view('admin.subscriptions.partials.create-form', compact('users', 'plans'))->render();
        }

        return view('admin.subscriptions.create', compact('users', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $user = User::findOrFail($request->user_id);

        // Check Existing Active Subscription
        $exists = $user->subscriptions()
            ->where('category_id', $plan->category_id)
            ->where('ends_at', '>', now())
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already has an active subscription for this category.');
        }

        $subscription = new Subscription();
        $subscription->plan_id = $plan->id;
        $subscription->user_id = $user->id;
        $subscription->category_type = $plan->category_type ?? 'App\Models\SubCategory';
        $subscription->category_id = $plan->category_id;
        $subscription->starts_at = $request->starts_at ? Carbon::parse($request->starts_at) : Carbon::now();
        $subscription->ends_at = Carbon::now()->addMonths($plan->duration);
        $subscription->status = $request->status;
        $subscription->save();

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);

        // ✅ FIX: Agar AJAX request hai (Drawer ke liye), to sirf 'partials.edit-form' bhejo
        if (request()->ajax()) {
            return view('admin.subscriptions.partials.edit-form', compact('subscription'))->render();
        }

        // Agar normal page open kiya to poora page
        return view('admin.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionRequest $request, $id)
    {
        if (config('qwiktest.demo_mode')) return back()->with('error', 'Demo Mode Enabled.');

        $subscription = Subscription::findOrFail($id);
        $subscription->update($request->validated());

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (config('qwiktest.demo_mode')) return response()->json(['success' => false, 'message' => 'Demo Mode.'], 403);

        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return response()->json(['success' => true, 'message' => 'Subscription deleted successfully.']);
    }

    public function downloadInvoice($paymentId)
    {
        // Subscription dhundo payment_id se
        $subscription = Subscription::with(['user', 'plan'])->where('payment_id', $paymentId)->firstOrFail();

        // Data jo invoice view me bhejna hai
        $data = [
            'subscription' => $subscription,
            'company_name' => 'Exam Babu', // Apni app ka naam
            'company_address' => '123, Tech Street, India',
            'date' => now()->format('d M, Y')
        ];

        // PDF Generate karo
        $pdf = Pdf::loadView('admin.invoices.template', $data);

        // Download karwa do
        return $pdf->download('invoice_' . $paymentId . '.pdf');
    }
}
