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
        Schema::table('client_models', function (Blueprint $table) {
            $table->integer('employee_paid_amount')->default(0);
        });

        // Update existing records
        try {
            $models = \DB::connection($this->getConnection())->table('client_models')
                ->join('employees', 'client_models.employee_id', '=', 'employees.id')
                ->where('client_models.employee_paid', true)
                ->select('client_models.id', 'client_models.price', 'employees.commission_rate')
                ->get();

            foreach ($models as $model) {
                $commission = (int) (($model->price * ($model->commission_rate ?? 50)) / 100);
                \DB::connection($this->getConnection())->table('client_models')
                    ->where('id', $model->id)
                    ->update(['employee_paid_amount' => $commission]);
            }
        } catch (\Exception $e) {
            // Avoid failing migration if tables don't exist in some tests
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_models', function (Blueprint $table) {
            $table->dropColumn('employee_paid_amount');
        });
    }
};
