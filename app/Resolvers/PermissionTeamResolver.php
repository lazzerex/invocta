<?php

namespace App\Resolvers;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class PermissionTeamResolver implements PermissionsTeamResolver
{
    protected static ?int $overrideTeamId = null;

    public function getPermissionsTeamId(): ?int
    {
        if (static::$overrideTeamId !== null) {
            return static::$overrideTeamId;
        }

        return Tenant::current()?->id;
    }

    public function setPermissionsTeamId(Model|int|string|null $id): void
    {
        if ($id instanceof Model) {
            static::$overrideTeamId = $id->getKey();
        } else {
            static::$overrideTeamId = $id !== null ? (int) $id : null;
        }
    }
}
