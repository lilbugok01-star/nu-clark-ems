<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBudget;
use App\Models\EventPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class FinancialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'organizer'])) {
                    abort(403, 'Access denied.');
                }
                return $next($request);
            }),
        ];
    }

    // Financial Dashboard - overview of all events' financial data
    public function dashboard(Request $request)
    {
        $query = Event::with(['budgets', 'payments']);
        
        if (Auth::user()->role === 'organizer') {
            $query->where('organizer_id', Auth::id());
        }

        $events = $query->get();

        $totalEstimatedBudget = 0;
        $totalActualSpent = 0;
        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($events as $event) {
            $totalEstimatedBudget += $event->budgets->sum('estimated_amount');
            $totalActualSpent += $event->budgets->sum('actual_amount');
            
            $totalIncome += $event->payments->where('payment_type', 'income')->sum('amount');
            $totalExpenses += $event->payments->where('payment_type', 'expense')->sum('amount');
        }

        $netProfitLoss = $totalIncome - $totalExpenses;

        return view('admin.financial-dashboard', compact(
            'events', 
            'totalEstimatedBudget', 
            'totalActualSpent', 
            'totalIncome', 
            'totalExpenses', 
            'netProfitLoss'
        ));
    }
    
    // Event Budget Management - view and manage budget for specific event
    public function eventBudget($eventId)
    {
        $query = Event::with(['budgets' => function($q) {
            $q->orderBy('category', 'asc');
        }]);

        if (Auth::user()->role === 'organizer') {
            $query->where('organizer_id', Auth::id());
        }

        $event = $query->findOrFail($eventId);
        $budgetItems = $event->budgets;

        $totals = [
            'estimated' => $budgetItems->sum('estimated_amount'),
            'actual' => $budgetItems->sum('actual_amount'),
            'variance' => $budgetItems->sum('estimated_amount') - $budgetItems->sum('actual_amount')
        ];

        $categories = EventBudget::budgetCategories();

        return view('admin.event-budget', compact('event', 'budgetItems', 'totals', 'categories'));
    }
    
    // Store a budget line item
    public function storeBudgetItem(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        if (Auth::user()->role === 'organizer' && $event->organizer_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:planned,approved,spent,cancelled'
        ]);

        $validated['event_id'] = $eventId;
        
        $budgetItem = EventBudget::create($validated);
        
        User::log('created_budget_item', $budgetItem, null, $validated);

        return back()->with('success', 'Budget item added successfully.');
    }
    
    // Update a budget line item
    public function updateBudgetItem(Request $request, $id)
    {
        $budgetItem = EventBudget::findOrFail($id);
        $event = $budgetItem->event;
        
        if (Auth::user()->role === 'organizer' && $event->organizer_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:planned,approved,spent,cancelled'
        ]);

        $old = $budgetItem->toArray();
        $budgetItem->update($validated);
        
        User::log('updated_budget_item', $budgetItem, $old, $validated);

        return back()->with('success', 'Budget item updated successfully.');
    }
    
    // Delete a budget line item
    public function deleteBudgetItem($id)
    {
        $budgetItem = EventBudget::findOrFail($id);
        $event = $budgetItem->event;
        
        if (Auth::user()->role === 'organizer' && $event->organizer_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $old = $budgetItem->toArray();
        $budgetItem->delete();
        
        User::log('deleted_budget_item', $budgetItem, $old, null);

        return back()->with('success', 'Budget item deleted successfully.');
    }
    
    // Event Payments - view and manage payments for specific event
    public function eventPayments($eventId)
    {
        $query = Event::with(['payments' => function($q) {
            $q->orderBy('payment_date', 'desc');
        }]);

        if (Auth::user()->role === 'organizer') {
            $query->where('organizer_id', Auth::id());
        }

        $event = $query->findOrFail($eventId);
        $payments = $event->payments;

        $totals = [
            'income' => $payments->where('payment_type', 'income')->sum('amount'),
            'expense' => $payments->where('payment_type', 'expense')->sum('amount'),
        ];
        $totals['net'] = $totals['income'] - $totals['expense'];

        $paymentMethods = EventPayment::paymentMethods();

        return view('admin.event-payments', compact('event', 'payments', 'totals', 'paymentMethods'));
    }
    
    // Store a payment record
    public function storePayment(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        if (Auth::user()->role === 'organizer' && $event->organizer_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'payment_method' => 'nullable|string',
            'payment_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,png,pdf|max:5120',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $validated['event_id'] = $eventId;
        $validated['recorded_by'] = Auth::id();

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 's3');
            // If S3 doesn't work locally, could add fallback, but instructions say "same pattern as poster upload"
            $validated['receipt_path'] = $path;
        }

        $payment = EventPayment::create($validated);
        
        User::log('created_payment', $payment, null, $validated);

        return back()->with('success', 'Payment recorded successfully.');
    }
    
    // Delete a payment record
    public function deletePayment($id)
    {
        $payment = EventPayment::findOrFail($id);
        $event = $payment->event;
        
        if (Auth::user()->role === 'organizer' && $event->organizer_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $old = $payment->toArray();
        $payment->delete();
        
        User::log('deleted_payment', $payment, $old, null);

        return back()->with('success', 'Payment deleted successfully.');
    }
    
    // Export financial report PDF for an event
    public function exportPdf($eventId)
    {
        $query = Event::with(['budgets', 'payments']);
        
        if (Auth::user()->role === 'organizer') {
            $query->where('organizer_id', Auth::id());
        }

        $event = $query->findOrFail($eventId);

        $totals = [
            'estimated_budget' => $event->budgets->sum('estimated_amount'),
            'actual_spent' => $event->budgets->sum('actual_amount'),
            'income' => $event->payments->where('payment_type', 'income')->sum('amount'),
            'expense' => $event->payments->where('payment_type', 'expense')->sum('amount')
        ];
        $totals['net'] = $totals['income'] - $totals['expense'];

        $pdf = Pdf::loadView('reports.financial-pdf', compact('event', 'totals'));
        
        User::log('exported_financial_pdf', $event, null, null);

        return $pdf->download("financial-report-{$eventId}.pdf");
    }
}
