<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use Modeling;
    
    protected $casts = [
        'seo_content' => 'object'
    ];
    
    
    
        
public function admin()
{
    return $this->belongsTo(Admin::class, 'admin_id', 'id');
}
    

    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Published') . '</span>';
        } else {
            return '<span class="badge text-bg-dark">' . __('Unpublished') . '</span>';
        }
    }

    /**
     * Generate a unique slug from a title, preserving the existing
     * str()->slug() convention and appending a numeric suffix on collision.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = (string) str($title)->slug();
        $slug = $base;
        $suffix = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * SEO meta title: a legacy manual override (seo_content.meta_title) wins
     * if one was saved previously, otherwise it's derived from the title.
     */
    public function seoTitle(): string
    {
        $override = trim((string) ($this->seo_content?->meta_title ?? ''));

        return $override !== '' ? $override : $this->title;
    }

    /**
     * SEO meta description: a legacy manual override wins if present,
     * otherwise a short summary is generated from the post body.
     */
    public function seoDescription(): string
    {
        $override = trim((string) ($this->seo_content?->meta_description ?? ''));

        if ($override !== '') {
            return $override;
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->body)));

        return (string) str($text)->limit(160);
    }

    /**
     * Storage path used for social sharing images: prefers a legacy
     * separately-uploaded SEO image, otherwise falls back to the featured image.
     */
    public function seoImagePath(): ?string
    {
        return $this->seo_image ?: $this->image;
    }

    public function seoImageUrl(): string
    {
        $path = $this->seoImagePath();

        return $path ? asset('storage/' . $path) : asset('sdf_bn.jpeg');
    }

    public function seoImageAlt(): string
    {
        return $this->seoTitle();
    }

    public function canonicalUrl(): string
    {
        return route('site.blog.details', $this->slug);
    }
}
