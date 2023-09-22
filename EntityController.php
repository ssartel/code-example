<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Models\Entity;
use App\Services\Admin\EntitiesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EntityController extends Controller
{
    protected $service;

    public function __construct(EntitiesService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $entityName): View
    {
        $model = Entity::getEntityModel($entityName);

        $entities = $model::paginate(config('admin.count_on_page'));

        return view('admin.entities.index', compact('entities', 'entityName'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $entityName): View
    {
        $model = Entity::getEntityModel($entityName);
        $parents = $model::parentEntities();

        //$entityName = substr($entityName, 0, -1);

        return view('admin.entities.create', compact('entityName', 'parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EntityRequest $request, string $entityName): RedirectResponse
    {
        $data = $request->validated();

        $this->service->store($data, $entityName);

        return redirect()->route('admin.entities.index', [$entityName]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $entityName, string $id): View
    {
        $entity = $this->service->find($entityName, $id);

        $parents = null;
        $parentEntities = $entity::parentEntities();

        //$entityName = substr($entityName, 0, -1);

        if ($parentEntities) {
            $parents = $entity->parents;
        }

        return view('admin.entities.edit', compact('entityName', 'parents', 'entity', 'parentEntities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EntityRequest $request, string $entityName, int $id): RedirectResponse
    {
        $data = $request->validated();

        $this->service->update($data, $entityName, $id);

        return redirect()->route('admin.entities.index', [$entityName]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $entityName, int $id): RedirectResponse
    {
        $entity = $this->service->find($entityName, $id);
        $entity->parents()->detach();
        $entity->delete();

        return redirect()->route('admin.entities.index', [$entityName]);
    }
}
