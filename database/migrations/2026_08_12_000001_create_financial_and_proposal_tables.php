<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event Categories (DB-driven instead of hard-coded)
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('color', 7)->default('#003087');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default categories
        DB::table('event_categories')->insert([
            ['name' => 'Academic',    'color' => '#003087', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Social',      'color' => '#8b5cf6', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sports',      'color' => '#16a34a', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cultural',    'color' => '#ea580c', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Leadership',  'color' => '#0ea5e9', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technology',  'color' => '#6366f1', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Seminar',     'color' => '#d946ef', 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Workshop',    'color' => '#f59e0b', 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Competition', 'color' => '#ef4444', 'sort_order' => 9, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other',       'color' => '#6b7280', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Event Budgets — line items for event budget planning
        Schema::create('event_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('category', 100); // venue, catering, equipment, materials, etc.
            $table->string('description');
            $table->decimal('estimated_amount', 12, 2)->default(0);
            $table->decimal('actual_amount', 12, 2)->nullable();
            $table->enum('status', ['planned', 'approved', 'spent', 'cancelled'])->default('planned');
            $table->timestamps();

            $table->index(['event_id', 'category']);
        });

        // Event Payments — income and expense tracking
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('payment_type', ['income', 'expense']); // revenue or cost
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->string('payment_method', 100)->nullable(); // cash, bank transfer, gcash, etc.
            $table->date('payment_date');
            $table->string('receipt_path')->nullable(); // uploaded receipt/proof
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'payment_type']);
            $table->index('payment_date');
        });

        // Event Proposals — formal event proposal document
        Schema::create('event_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
            $table->string('proposal_number', 50)->unique();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('event_overview')->nullable();
            $table->text('objectives')->nullable();
            $table->text('target_audience')->nullable();
            $table->decimal('estimated_budget', 12, 2)->default(0);
            $table->text('venue_details')->nullable();
            $table->text('schedule_details')->nullable();
            $table->text('requirements')->nullable();
            $table->text('expected_outcomes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_proposals');
        Schema::dropIfExists('event_payments');
        Schema::dropIfExists('event_budgets');
        Schema::dropIfExists('event_categories');
    }
};
