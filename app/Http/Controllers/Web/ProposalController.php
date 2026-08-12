<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventProposal;
use App\Models\EventBudget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'organizer', 'student_development'])) {
                    abort(403);
                }
                return $next($request);
            }),
        ];
    }

    // List all proposals (admin sees all, organizer sees only their events' proposals)
    public function index() {
        $user = Auth::user();
        if ($user->role === 'admin') {
            $proposals = EventProposal::with('event', 'preparedBy')->orderByDesc('created_at')->paginate(15);
        } else {
            $eventIds = Event::where('organizer_id', $user->id)->pluck('id');
            $proposals = EventProposal::with('event', 'preparedBy')->whereIn('event_id', $eventIds)->orderByDesc('created_at')->paginate(15);
        }
        return view('organizer.proposals', compact('proposals'));
    }

    // Show form to create proposal for an event
    public function create($eventId) {
        $event = Event::findOrFail($eventId);
        // Check authorization for non-admin
        if (Auth::user()->role !== 'admin' && $event->organizer_id !== Auth::id()) {
            abort(403);
        }
        $budgetItems = EventBudget::where('event_id', $eventId)->get();
        $estimatedBudget = $budgetItems->sum('estimated_amount');
        return view('organizer.proposal-form', compact('event', 'budgetItems', 'estimatedBudget'));
    }

    // Store new proposal
    public function store(Request $request) {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'event_overview' => 'required|string',
            'objectives' => 'required|string',
            'target_audience' => 'nullable|string',
            'estimated_budget' => 'required|numeric|min:0',
            'venue_details' => 'nullable|string',
            'schedule_details' => 'nullable|string',
            'requirements' => 'nullable|string',
            'expected_outcomes' => 'nullable|string',
        ]);

        $proposal = EventProposal::create([
            ...$validated,
            'prepared_by' => Auth::id(),
            'proposal_number' => EventProposal::generateNumber(),
            'status' => 'draft',
        ]);

        User::log('create_proposal', $proposal, null, $proposal->toArray());
        return redirect()->route('proposal.show', $proposal->id)->with('success', 'Proposal created successfully!');
    }

    // View proposal detail
    public function show($id) {
        $proposal = EventProposal::with('event', 'preparedBy', 'approvedBy')->findOrFail($id);
        return view('organizer.proposal-view', compact('proposal'));
    }

    // Submit proposal for review
    public function submit($id) {
        $proposal = EventProposal::findOrFail($id);
        $old = $proposal->toArray();
        $proposal->update(['status' => 'submitted']);
        User::log('submit_proposal', $proposal, $old, $proposal->toArray());
        return back()->with('success', 'Proposal submitted for review.');
    }

    // Admin approve
    public function approve(Request $request, $id) {
        if (Auth::user()->role !== 'admin') abort(403);
        $proposal = EventProposal::findOrFail($id);
        $old = $proposal->toArray();
        $proposal->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        User::log('approve_proposal', $proposal, $old, $proposal->toArray());
        return back()->with('success', 'Proposal approved.');
    }

    // Admin reject
    public function reject(Request $request, $id) {
        if (Auth::user()->role !== 'admin') abort(403);
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $proposal = EventProposal::findOrFail($id);
        $old = $proposal->toArray();
        $proposal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        User::log('reject_proposal', $proposal, $old, $proposal->toArray());
        return back()->with('success', 'Proposal rejected.');
    }

    // Export as PDF
    public function exportPdf($id) {
        $proposal = EventProposal::with('event', 'preparedBy', 'approvedBy')->findOrFail($id);
        $budgetItems = EventBudget::where('event_id', $proposal->event_id)->get();
        User::log('export_proposal_pdf', $proposal, null, ['format' => 'pdf']);
        $pdf = Pdf::loadView('reports.proposal-pdf', compact('proposal', 'budgetItems'));
        return $pdf->download('proposal-' . $proposal->proposal_number . '.pdf');
    }
}
