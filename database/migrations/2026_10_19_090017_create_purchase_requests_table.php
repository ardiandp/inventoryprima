<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('display_number')->unique();
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->enum('request_type', ['regular', 'project'])->default('regular');
            $table->string('reason', 500)->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('projected_date')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('restrict');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('restrict');
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'revision_requested',
                'sourcing',
                'pending_approval',
                'approved',
                'partially_ordered',
                'fulfilled',
                'closed',
                'rejected',
                'cancelled',
            ])->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('rejection_reason', 500)->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('po_number', 50)->nullable();
            $table->foreignId('attachment_id')->nullable()->constrained('attachments')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('requested_by');
            $table->index('status');
            $table->index(['requested_by', 'status']);
            $table->index(['department_id', 'status']);
            $table->index('projected_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
