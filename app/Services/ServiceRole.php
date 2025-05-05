<?php

namespace App\Services;

use App\Models\User;

// Service pour gérer les rôles des utilisateurs
class ServiceRole
{
    // Assigner un rôle à un utilisateur
    public function assignerRole(User $utilisateur, string $role)
    {
        $utilisateur->update(['role' => $role]);
    }

    // Vérifier si un utilisateur a un rôle spécifique
    public function aRole(User $utilisateur, string $role): bool
    {
        return $utilisateur->role === $role;
    }
}