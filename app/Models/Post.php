<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\User;

class Post extends Model
{
    use HasFactory;

    /**
     * Toplu atama için izin verilen alanlar
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'slug'
    ];

    /**
     * Model oluşturulurken çalışacak olaylar
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            // Başlıktan benzersiz slug oluştur
            $slug = Str::slug($post->title);
            $count = static::where('slug', 'like', $slug . '%')->count();
            
            $post->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }

    /**
     * Blog yazısının sahibi olan kullanıcı ilişkisi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Route model binding için kullanılacak anahtar
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
