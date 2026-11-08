<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\BookCopyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->with(['author', 'category'])
            ->withCount('activeBorrowings')
            ->search($request->string('q')->trim()->toString())
            ->when($request->integer('author_id'), fn ($query, $authorId) => $query->where('author_id', $authorId))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->input('availability') === 'available', fn ($query) => $query->where('available_copies', '>', 0))
            ->when($request->input('availability') === 'unavailable', fn ($query) => $query->where('available_copies', '<', 1))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'authors' => Author::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.books.create', [
            'authors' => Author::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BookCopyService $copies)
    {
        $data = $request->validate($this->rules());
        $data['isbn'] = filled($data['isbn'] ?? null) ? $data['isbn'] : null;
        $totalCopies = (int) $data['total_copies'];
        $data['available_copies'] = $totalCopies;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        DB::transaction(function () use ($data, $totalCopies, $copies) {
            $book = Book::query()->create($data);
            $copies->createForBook($book, $totalCopies);
        });

        return redirect()->route('admin.books.index')->with('success', 'Book added to inventory successfully.');
    }

    public function edit(Book $book)
    {
        $book->load([
            'author',
            'category',
            'copies' => fn ($query) => $query->orderBy('id'),
        ])->loadCount('activeBorrowings');

        return view('admin.books.edit', [
            'book' => $book,
            'authors' => Author::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book, BookCopyService $copies)
    {
        $data = $request->validate($this->rules($book));
        $totalCopies = (int) $data['total_copies'];
        unset($data['total_copies']);

        $data['isbn'] = filled($data['isbn'] ?? null) ? $data['isbn'] : null;

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        } elseif ($request->boolean('remove_cover') && $book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = null;
        }

        DB::transaction(function () use ($book, $data, $totalCopies, $copies) {
            $book->update($data);
            $copies->adjustCount($book, $totalCopies);
        });

        return redirect()->route('admin.books.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        if ($book->activeBorrowings()->exists()) {
            return back()->with('error', 'This book cannot be deleted while copies are actively borrowed.');
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Book removed from inventory.');
    }

    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'exists:authors,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'isbn' => ['nullable', 'string', 'max:50', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'shelf_location' => ['nullable', 'string', 'max:80'],
            'total_copies' => ['required', 'integer', 'min:1', 'max:999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover' => ['nullable', 'boolean'],
        ];
    }
}
