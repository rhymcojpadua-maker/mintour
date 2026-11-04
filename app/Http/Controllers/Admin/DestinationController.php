<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestinationRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\DestinationPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Destination::class);

        $q = Destination::with('category');
        if ($s = $request->string('q')->toString()) {
            $q->where('name', 'like', "%$s%");
        }

        return view('admin.destinations.index', [
            'destinations' => $q->latest()->paginate(15)->withQueryString(),
            'search' => $s ?? '',
        ]);
    }

    public function create()
    {
        $this->authorize('create', Destination::class);

        return view('admin.destinations.form', [
            'destination' => new Destination,
            'categories' => Category::active()->ordered()->get(),
        ]);
    }

    public function store(DestinationRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('destinations', 'public');
        }

        $destination = Destination::create($data);

        foreach ($request->file('photos', []) as $i => $photo) {
            $destination->photos()->create([
                'path' => $photo->store('destinations/gallery', 'public'),
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destination created.');
    }

    public function edit(Destination $destination)
    {
        $this->authorize('update', $destination);

        return view('admin.destinations.form', [
            'destination' => $destination->load('photos'),
            'categories' => Category::active()->ordered()->get(),
        ]);
    }

    public function update(DestinationRequest $request, Destination $destination)
    {
        $data = $request->validated();
        if (! empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $destination->id);
        }

        if ($request->hasFile('cover_image')) {
            if ($destination->cover_image) {
                Storage::disk('public')->delete($destination->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('destinations', 'public');
        }

        $destination->update($data);

        foreach ($request->file('photos', []) as $i => $photo) {
            $destination->photos()->create([
                'path' => $photo->store('destinations/gallery', 'public'),
                'sort_order' => $destination->photos()->count() + $i,
            ]);
        }

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destination updated.');
    }

    public function destroy(Destination $destination)
    {
        $this->authorize('delete', $destination);

        if ($destination->cover_image) {
            Storage::disk('public')->delete($destination->cover_image);
        }
        foreach ($destination->photos as $p) {
            Storage::disk('public')->delete($p->path);
        }

        $destination->delete();

        return back()->with('status', 'Destination deleted.');
    }

    public function deletePhoto(Destination $destination, DestinationPhoto $photo)
    {
        $this->authorize('update', $destination);
        $photo = $destination->photos()->whereKey($photo->id)->firstOrFail();

        if (! Str::startsWith($photo->path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($photo->path);
        }

        $photo->delete();

        return back()->with('status', 'Gallery photo removed.');
    }

    public function toggleFeatured(Destination $destination)
    {
        $this->authorize('update', $destination);
        $destination->update(['is_featured' => ! $destination->is_featured]);

        return back()->with('status', 'Destination feature status updated.');
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $slug = Str::slug($source);
        $base = $slug;
        $i = 1;
        while (Destination::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "$base-".(++$i);
        }

        return $slug;
    }
}
