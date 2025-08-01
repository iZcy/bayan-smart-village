<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'village_id',
        'community_id',
        'sme_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_active' => 'boolean',
        ];
    }

    // Role constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_VILLAGE_ADMIN = 'village_admin';
    const ROLE_COMMUNITY_ADMIN = 'community_admin';
    const ROLE_SME_ADMIN = 'sme_admin';

    public static function getRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_VILLAGE_ADMIN => 'Village Admin',
            self::ROLE_COMMUNITY_ADMIN => 'Community Admin',
            self::ROLE_SME_ADMIN => 'SME Admin',
        ];
    }

    // Relationships
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function sme(): BelongsTo
    {
        return $this->belongsTo(Sme::class);
    }

    // Role checking methods
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isVillageAdmin(): bool
    {
        return $this->role === self::ROLE_VILLAGE_ADMIN;
    }

    public function isCommunityAdmin(): bool
    {
        return $this->role === self::ROLE_COMMUNITY_ADMIN;
    }

    public function isSmeAdmin(): bool
    {
        return $this->role === self::ROLE_SME_ADMIN;
    }

    // Scope checking methods
    public function canManageVillage(Village $village): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isVillageAdmin() && $this->village_id === $village->id) {
            return true;
        }

        return false;
    }

    public function canManageCommunity(Community $community): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isVillageAdmin() && $this->village_id === $community->village_id) {
            return true;
        }

        if ($this->isCommunityAdmin() && $this->community_id === $community->id) {
            return true;
        }

        return false;
    }

    public function canManageSme(Sme $sme): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isVillageAdmin() && $this->village_id === $sme->community->village_id) {
            return true;
        }

        if ($this->isCommunityAdmin() && $this->community_id === $sme->community_id) {
            return true;
        }

        if ($this->isSmeAdmin() && $this->sme_id === $sme->id) {
            return true;
        }

        return false;
    }

    public function canManageOffer(Offer $offer): bool
    {
        return $this->canManageSme($offer->sme);
    }

    // Get accessible villages for this user
    public function getAccessibleVillages()
    {
        if ($this->isSuperAdmin()) {
            return Village::query();
        }

        if ($this->isVillageAdmin()) {
            return Village::where('id', $this->village_id);
        }

        if ($this->isCommunityAdmin()) {
            return Village::where('id', $this->community->village_id);
        }

        if ($this->isSmeAdmin()) {
            return Village::where('id', $this->sme->community->village_id);
        }

        return Village::whereRaw('1 = 0'); // No access
    }

    // Get accessible communities for this user
    public function getAccessibleCommunities()
    {
        if ($this->isSuperAdmin()) {
            return Community::query();
        }

        if ($this->isVillageAdmin()) {
            return Community::where('village_id', $this->village_id);
        }

        if ($this->isCommunityAdmin()) {
            return Community::where('id', $this->community_id);
        }

        if ($this->isSmeAdmin()) {
            return Community::where('id', $this->sme->community_id);
        }

        return Community::whereRaw('1 = 0'); // No access
    }

    // Get accessible SMEs for this user
    public function getAccessibleSmes()
    {
        if ($this->isSuperAdmin()) {
            return Sme::query();
        }

        if ($this->isVillageAdmin()) {
            return Sme::whereHas('community', function ($query) {
                $query->where('village_id', $this->village_id);
            });
        }

        if ($this->isCommunityAdmin()) {
            return Sme::where('community_id', $this->community_id);
        }

        if ($this->isSmeAdmin()) {
            return Sme::where('id', $this->sme_id);
        }

        return Sme::whereRaw('1 = 0'); // No access
    }

    // Get accessible offers for this user
    public function getAccessibleOffers()
    {
        if ($this->isSuperAdmin()) {
            return Offer::query();
        }

        if ($this->isVillageAdmin()) {
            return Offer::whereHas('sme.community', function ($query) {
                $query->where('village_id', $this->village_id);
            });
        }

        if ($this->isCommunityAdmin()) {
            return Offer::whereHas('sme', function ($query) {
                $query->where('community_id', $this->community_id);
            });
        }

        if ($this->isSmeAdmin()) {
            return Offer::where('sme_id', $this->sme_id);
        }

        return Offer::whereRaw('1 = 0'); // No access
    }

    // Get role display name
    public function getRoleDisplayAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Unknown';
    }

    // Get scope display name
    public function getScopeDisplayAttribute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'All System';
        }

        if ($this->isVillageAdmin()) {
            return 'Village: ' . $this->village->name;
        }

        if ($this->isCommunityAdmin()) {
            return 'Community: ' . $this->community->name;
        }

        if ($this->isSmeAdmin()) {
            return 'SME: ' . $this->sme->name;
        }

        return 'No Scope';
    }

    // Filament panel access control
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Only allow active users to access admin panel
        if (!$this->is_active) {
            return false;
        }

        // Allow all roles to access admin panel (they'll be filtered by resource-level permissions)
        return $this->isSuperAdmin() || $this->isVillageAdmin() || $this->isCommunityAdmin() || $this->isSmeAdmin();
    }

    /**
     * Check if user can access the main domain (only super admin)
     */
    public function canAccessMainDomain(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can access a specific village domain
     */
    public function canAccessVillageDomain(Village $village): bool
    {
        // Super admin cannot access village domains
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Village admin can only access their village's domain
        if ($this->isVillageAdmin()) {
            return $this->village_id === $village->id;
        }

        // Community admin can access their village's domain
        if ($this->isCommunityAdmin() && $this->community) {
            return $this->community->village_id === $village->id;
        }

        // SME admin can access their village's domain
        if ($this->isSmeAdmin() && $this->sme && $this->sme->community) {
            return $this->sme->community->village_id === $village->id;
        }

        return false;
    }

    /**
     * Get the villages this user can access as domains
     */
    public function getAccessibleDomainVillages()
    {
        if ($this->isSuperAdmin()) {
            // Super admin cannot access any village domains
            return collect();
        }

        if ($this->isVillageAdmin()) {
            return collect([$this->village]);
        }

        if ($this->isCommunityAdmin() && $this->community) {
            return collect([$this->community->village]);
        }

        if ($this->isSmeAdmin() && $this->sme && $this->sme->community) {
            return collect([$this->sme->community->village]);
        }

        return collect();
    }
}
