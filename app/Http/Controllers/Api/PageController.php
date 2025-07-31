<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        return response()->json(Page::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'body' => 'required',
            'body_en' => 'required',
            'active' => 'required|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title_en']);
        $validated['user_id'] = auth()->id() ?? 0;

        $page = Page::create($validated);
        return response()->json(['message' => 'Role created', 'page' => $page], 201);
    }

    public function show($id)
    {
        return response()->json(Page::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'body' => 'required',
            'body_en' => 'required',
            'active' => 'required|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title_en']);
        $page->update($validated);

        return response()->json(['message' => 'Page updated', 'page' => $page]);
    }

    public function destroy($id)
    {
        if (in_array($id, [1, 2, 3])) {
            return response()->json(['message' => 'The page cannot be deleted'], 403);
        }

        $page = Page::findOrFail($id);
        $page->delete();
        return response()->json(['message' => 'Page deleted']);
    }

    public function showBySlug($slug)
    {
        $page = Page::where('slug', $slug)->where('active', 1)->firstOrFail();
        return response()->json($page);
    }
}
