<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Kimlik doğrulama middleware'ini constructor'da tanımlıyoruz.
     * Index ve show metodları hariç tüm metodlar için auth gerekiyor.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Ana sayfa için blog yazılarını listeler.
     * Önbellekleme kullanarak performansı artırır.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = 'posts.page.' . $page;
        
        return view('posts.index', [
            'posts' => Cache::remember($cacheKey, 3600, fn() => 
                Post::with('user')->latest()->paginate(20)
            )
        ]);
    }

    /**
     * Yeni blog yazısı oluşturma formunu gösterir.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Yeni blog yazısını veritabanına kaydeder.
     * Günlük post limitini kontrol eder (maksimum 3).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        // Günlük post limitini kontrol et
        $postCount = Post::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($postCount >= 3) {
            return redirect()
                ->route('posts.index')
                ->with('error', 'Günlük post limitine (3) ulaştınız.');
        }

        $post = Post::create([
            ...$validated,
            'user_id' => Auth::id()
        ]);

        // İlgili önbellekleri temizle
        Cache::forget('posts.page.1');
        Cache::tags(['posts'])->flush();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    /**
     * Blog yazısının detay sayfasını gösterir.
     * Önbellekleme kullanarak performansı artırır.
     */
    public function show(Post $post)
    {
        $cacheKey = 'post.' . $post->slug;
        
        return view('posts.show', [
            'post' => Cache::remember($cacheKey, 3600, fn() => 
                $post->load('user')
            )
        ]);
    }

    /**
     * Blog yazısı düzenleme formunu gösterir.
     * Sadece yazı sahibi düzenleyebilir.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', compact('post'));
    }

    /**
     * Blog yazısını günceller.
     * Sadece yazı sahibi güncelleyebilir.
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $post->update($validated);

        // Önbelleği temizle
        Cache::forget('post.' . $post->slug);
        Cache::tags(['posts'])->flush();

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    /**
     * Blog yazısını siler.
     * Sadece yazı sahibi silebilir.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        $post->delete();

        // Önbelleği temizle
        Cache::forget('post.' . $post->slug);
        Cache::tags(['posts'])->flush();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
