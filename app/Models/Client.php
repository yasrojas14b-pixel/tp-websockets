<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'email', 'nail_type', 'allergies', 'preferred_designs', 'internal_notes'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * Keep only digits so "+54 9 11 1234-5678" and "5491112345678" match.
     */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    /**
     * @return Attribute<string, string>
     */
    protected function phone(): Attribute
    {
        return Attribute::set(fn (string $value): string => self::normalizePhone($value));
    }

    /**
     * wa.me link for the WhatsApp button in the admin panel.
     *
     * @return Attribute<string, never>
     */
    protected function whatsappUrl(): Attribute
    {
        return Attribute::get(fn (): string => 'https://wa.me/'.$this->phone);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasMany<ClientPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ClientPhoto::class);
    }
}
