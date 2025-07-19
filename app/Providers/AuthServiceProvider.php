<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Village;
use App\Models\Community;
use App\Models\Sme;
use App\Models\Offer;
use App\Models\Category;
use App\Models\Place;
use App\Models\Article;
use App\Models\ExternalLink;
use App\Models\Image;
use App\Models\OfferTag;
use App\Models\OfferImage;
use App\Models\OfferEcommerceLink;
use App\Policies\VillagePolicy;
use App\Policies\CommunityPolicy;
use App\Policies\SmePolicy;
use App\Policies\OfferPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\PlacePolicy;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Village::class => VillagePolicy::class,
        Community::class => CommunityPolicy::class,
        Sme::class => SmePolicy::class,
        Offer::class => OfferPolicy::class,
        Category::class => CategoryPolicy::class,
        Place::class => PlacePolicy::class,
        Article::class => ArticlePolicy::class,
        // Related models inherit the same policies as their parent
        ExternalLink::class => ArticlePolicy::class, // Same scope-based access
        Image::class => ArticlePolicy::class, // Same scope-based access
        OfferTag::class => OfferPolicy::class,
        OfferImage::class => OfferPolicy::class,
        OfferEcommerceLink::class => OfferPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
