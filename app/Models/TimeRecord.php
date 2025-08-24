<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'recorded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the employee that owns the time record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by specific date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('recorded_at', $date);
    }

    /**
     * Scope a query to filter by employee.
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope a query to filter by date range for admin (alias for betweenDates).
     */
    public function scopeFilterByDate($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        return $query->whereDate('recorded_at', $startDate);
    }

    /**
     * Scope a query to filter by specific employee (alias for byEmployee).
     */
    public function scopeFilterByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Get formatted recorded time.
     */
    public function getFormattedRecordedAtAttribute(): string
    {
        return $this->recorded_at->format('d/m/Y H:i:s');
    }
}
