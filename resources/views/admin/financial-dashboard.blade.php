@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 bg-white border-end shadow-sm p-0 min-vh-100">
            @include('layouts.partials.sidebar-admin')
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 col-md-9 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold" style="color: var(--nu-blue);"><i class="bi bi-cash-stack me-2"></i>Financial Management</h3>
            </div>
            
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-primary shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Total Est. Budget</div>
                        <div class="stat-value fs-4 fw-bold" style="color: var(--nu-blue);">₱{{ number_format($stats['total_estimated_budget'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-warning shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Total Actual Spent</div>
                        <div class="stat-value fs-4 fw-bold text-warning">₱{{ number_format($stats['total_actual_spent'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-success shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Total Income</div>
                        <div class="stat-value fs-4 fw-bold text-success">₱{{ number_format($stats['total_income'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-danger shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Total Expenses</div>
                        <div class="stat-value fs-4 fw-bold text-danger">₱{{ number_format($stats['total_expenses'] ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-info shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Net Profit/Loss</div>
                        @php
                            $net = $stats['net_profit_loss'] ?? 0;
                            $netColor = $net >= 0 ? 'color: var(--nu-gold);' : 'color: red;';
                        @endphp
                        <div class="stat-value fs-4 fw-bold" style="{{ $netColor }}">₱{{ number_format($net, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="nu-card stat-card p-3 border-start border-4 border-secondary shadow-sm h-100">
                        <div class="stat-label text-muted small fw-bold text-uppercase">Events with Budget</div>
                        <div class="stat-value fs-4 fw-bold" style="color: purple;">{{ $stats['total_events_with_budget'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="nu-card bg-white shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover nu-table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Est. Budget</th>
                                    <th>Actual Spent</th>
                                    <th>Income</th>
                                    <th>Expenses</th>
                                    <th>P/L</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    @php
                                        $pl = ($event->total_income ?? 0) - ($event->total_expenses ?? 0);
                                        $plColor = $pl >= 0 ? 'text-success' : 'text-danger';
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $event->title }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}</td>
                                        <td>₱{{ number_format($event->total_estimated_budget ?? 0, 2) }}</td>
                                        <td>₱{{ number_format($event->total_actual_spent ?? 0, 2) }}</td>
                                        <td>₱{{ number_format($event->total_income ?? 0, 2) }}</td>
                                        <td>₱{{ number_format($event->total_expenses ?? 0, 2) }}</td>
                                        <td class="fw-bold {{ $plColor }}">₱{{ number_format($pl, 2) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.financial.budget', $event->id) }}" class="btn btn-sm btn-outline-primary" title="Budget">
                                                <i class="bi bi-calculator"></i>
                                            </a>
                                            <a href="{{ route('admin.financial.payments', $event->id) }}" class="btn btn-sm btn-outline-success" title="Payments">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            No events found with financial records.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($events, 'links'))
                    <div class="card-footer bg-white py-3">
                        {{ $events->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
