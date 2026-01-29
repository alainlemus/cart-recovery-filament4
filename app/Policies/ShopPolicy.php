<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Ver si el usuario puede ver la lista de shops
     */
    public function viewAny(User $user): bool
    {
        return true; // todos los usuarios autenticados pueden listar
    }

    /**
     * Ver si el usuario puede ver un shop específico
     */
    public function view(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id;
    }

    /**
     * Crear un nuevo shop
     */
    public function create(User $user): bool
    {
        return true; // todos los usuarios autenticados pueden crear
    }

    /**
     * Actualizar un shop
     */
    public function update(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id;
    }

    /**
     * Eliminar un shop
     */
    public function delete(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shop $shop): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shop $shop): bool
    {
        return false;
    }
}
