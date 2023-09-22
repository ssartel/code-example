<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    use HasFactory;
    use SoftDeletes;

    private const ENTITIES_TYPES = [
        'courses' => 1,
        'modules' => 2,
        'blocks' => 3,
    ];

    protected $table = 'entities';
    protected $guarded = [];

    public static function getEntityModel(string $entity): Entity
    {
        switch ($entity) {
            case 'blocks':
                return new Block();
            case 'modules':
                return new Module();
            default:
                return new Course();
        }
    }

    public static function getEntityId(string $entity): int
    {
        return self::ENTITIES_TYPES[$entity];
    }

    public static function createEntity(array $data): Entity
    {
        $data['type'] = self::getEntityId(static::$entity);

        return self::create($data);
    }

    public function children()
    {
        $childEntity = self::getChildEntity();

        return $this->belongsToMany(self::getEntityModel($childEntity)::class, 'entity_relationships', 'parent_id', 'child_id');
    }

    public function parents()
    {
        $parentEntity = self::getParentEntity();

        return $this->belongsToMany(self::getEntityModel($parentEntity)::class, 'entity_relationships', 'child_id', 'parent_id');
    }

    public static function parentEntities()
    {
        if ($parentEntity = self::getParentEntity()) {
            return self::getEntityModel($parentEntity)::all();
        }

        return null;
    }
    protected static function booted(): void
    {
        static::addGlobalScope(static::$entity, function (Builder $builder) {
            $builder->where('type', '=', self::getEntityId(static::$entity));
        });
    }

    public static function getEntityName(): string
    {
        return static::$entity;
    }

    public static function isEntity(string $entity): bool
    {
        return array_key_exists($entity, self::ENTITIES_TYPES);
    }

    public static function getChildEntity(): string | bool
    {
        $childId = self::ENTITIES_TYPES[static::$entity] + 1;

        return array_search($childId,self::ENTITIES_TYPES);
    }

    public static function getParentEntity(): string | bool
    {
        $childId = self::ENTITIES_TYPES[static::$entity] - 1;

        return array_search($childId,self::ENTITIES_TYPES);
    }
}
