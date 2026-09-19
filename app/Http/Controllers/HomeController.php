<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use App\Models\Post;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display the dynamic frontend homepage or redirect if desired.
     */
    public function index()
    {
        $settingsRaw = Setting::all();
        $settings = $settingsRaw->pluck('value', 'key')->toArray();
        $logoSetting = $settingsRaw->firstWhere('key', 'site_logo');
        $settings['site_logo_url'] = $logoSetting ? $logoSetting->image_url : null;

        $navigation = Page::where('is_active', true)
            ->select('id', 'title', 'slug')
            ->get();

        $homePage = Page::where('slug', 'home')
            ->where('is_active', true)
            ->with(['sections' => function ($query) {
                $query->where('is_active', true);
            }])
            ->first();

        $posts = Post::where('status', 'published')
            ->with('category')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'content' => $post->content,
                    'image_url' => $post->image_url,
                    'category' => $post->category ? ['name' => $post->category->name, 'slug' => $post->category->slug] : null,
                    'created_at' => $post->created_at ? $post->created_at->toISOString() : null,
                ];
            });

        $products = Product::where('is_active', true)
            ->orderBy('type')
            ->take(8)
            ->get();

        return Inertia::render('Welcome', [
            'settings' => $settings,
            'navigation' => $navigation,
            'page' => $homePage,
            'posts' => $posts,
            'products' => $products,
        ]);
    }

    /**
     * Display single news / article detail.
     */
    public function postDetail(string $slug)
    {
        $query = Post::where('slug', $slug);
        
        // If not admin, only show published
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            $query->where('status', 'published');
        }

        $post = $query->with('category')->firstOrFail();

        $relatedPosts = Post::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'content' => $p->content,
                    'image_url' => $p->image_url,
                    'category' => $p->category ? ['name' => $p->category->name] : null,
                    'created_at' => $p->created_at ? $p->created_at->toISOString() : null,
                ];
            });

        $settingsRaw = Setting::all();
        $settings = $settingsRaw->pluck('value', 'key')->toArray();

        $featuredProducts = Product::where('is_active', true)->take(3)->get();

        return Inertia::render('NewsDetail', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'image_url' => $post->image_url,
                'status' => $post->status,
                'category' => $post->category ? ['name' => $post->category->name, 'slug' => $post->category->slug] : null,
                'created_at' => $post->created_at ? $post->created_at->toISOString() : null,
                'updated_at' => $post->updated_at ? $post->updated_at->toISOString() : null,
            ],
            'related_posts' => $relatedPosts,
            'featured_products' => $featuredProducts,
            'settings' => $settings,
        ]);
    }

    /**
     * Display catalog / product detail.
     */
    public function catalogDetail(Product $product)
    {
        $relatedProducts = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('type', $product->type)
            ->take(4)
            ->get();

        $settingsRaw = Setting::all();
        $settings = $settingsRaw->pluck('value', 'key')->toArray();

        return Inertia::render('CatalogDetail', [
            'product' => $product,
            'related_products' => $relatedProducts,
            'settings' => $settings,
        ]);
    }
}

