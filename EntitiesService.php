<?php

namespace App\Services\Admin;


use App\Models\Entity;

class EntitiesService
{
    public function store(array $data, string $entityName): void
    {
        $parents = null;

        if (!empty($data['parents'])) {
            $parents = $data['parents'];
            unset($data['parents']);
        }

        $model = Entity::getEntityModel($entityName);
        $entity = $model::createEntity($data);

        if ($parents) {
            $entity->parents()->attach($parents);
        }
    }

    public function update(array $data, string $entityName, int $id): void
    {
        $parents = null;

        if (!empty($data['parents'])) {
            $parents = $data['parents'];
            unset($data['parents']);
        }

        $data['status'] = !empty($data['status']);

        $model = Entity::getEntityModel($entityName)::find($id);
        $model->update($data);

        if ($parents) {
            $model->parents()->sync($parents);
        }
    }

    public function find(string $entityName, int $id): Entity
    {
        $model = Entity::getEntityModel($entityName);
        return $model::find($id);
    }
}
