<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'cep',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
    ];

    /**
     * Get the employee that owns the address
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Set the CEP attribute
     */
    public function setCepAttribute($value): void
    {
        $this->attributes['cep'] = preg_replace('/[^0-9]/', '', $value ?? '');
    }

    /**
     * Get formatted CEP
     */
    public function getFormattedCepAttribute(): string
    {
        $cep = $this->cep;
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    /**
     * Get complete address as single string
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->street;

        if ($this->number) {
            $address .= ', ' . $this->number;
        }

        if ($this->complement) {
            $address .= ', ' . $this->complement;
        }

        $address .= ' - ' . $this->neighborhood;
        $address .= ', ' . $this->city . ' - ' . $this->state;
        $address .= ' CEP: ' . $this->formatted_cep;

        return $address;
    }

    /**
     * Scope a query to filter by CEP
     */
    public function scopeByCep($query, $cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return $query->where('cep', $cep);
    }

    /**
     * Scope a query to filter by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    /**
     * Scope a query to filter by state
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }
}
