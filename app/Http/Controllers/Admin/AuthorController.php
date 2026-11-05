<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $authors = Author::query()
            ->withCount('books')
            ->when($request->string('q')->trim()->toString(), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:authors,name'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        Author::query()->create($data);

        return redirect()->route('admin.authors.index')->with('success', 'Author added successfully.');
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('authors', 'name')->ignore($author->id)],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $author->update($data);

        return redirect()->route('admin.authors.index')->with('success', 'Author updated successfully.');
    }

    public function destroy(Author $author)
    {
        if ($author->books()->withTrashed()->exists()) {
            return back()->with('error', 'This author cannot be deleted because books are still linked to them.');
        }

        $author->delete();

        return back()->with('success', 'Author deleted successfully.');
    }
}
