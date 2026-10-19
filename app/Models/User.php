<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * صلاحية الدخول للوحة الإدارة (/admin).
     * مفيش تسجيل حسابات عام في الموقع، فأي يوزر موجود هو أدمن.
     * (لو حبيت تقيّدها أكتر لاحقاً، اعمل فلتر على الإيميل أو عمود is_admin هنا.)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /** أدمن = كل الصلاحيات (الإعدادات والمستخدمين والـ redirects والميديا). */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** محرر = المحتوى فقط. */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
