<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

        $permissions = [
            
            'Products',
            'Products-ajoute',
            'Products-modifier',
            'Products-supprimer',

            'Taxes',
            'Taxes-ajoute',
            'Taxes-modifier',
            'Taxes-supprimer',

            'Fournisseurs',
            'Fournisseurs-ajoute',
            'Fournisseurs-modifier',
            'Fournisseurs-supprimer',

            'Formateurs',
            'Formateurs-ajoute',
            'Formateurs-modifier',
            'Formateurs-supprimer',

            'Categories',
            'Categories-ajoute',
            'Categories-modifier',
            'Categories-supprimer',

            'Local',
            'Local-ajoute',
            'Local-modifier',
            'Local-supprimer',

            'Rayon',
            'Rayon-ajoute',
            'Rayon-modifier',
            'Rayon-supprimer',

            'Famille',
            'Famille-ajoute',
            'Famille-modifier',
            'Famille-supprimer',

            'Achat',
            'Achat-ajoute',
            'Achat-modifier',
            'Achat-supprimer',
            
            'Commande',
            'Commande-ajoute',
            'Commande-modifier',
            'Commande-supprimer',

            'Historique',
            'Historique-Export',
            'Historique-montrer',
            
            'Unité',
            'Unité-ajoute',
            'Unité-modifier',
            'Unité-supprimer',

            'utilisateur',
            'utilisateur-ajoute',
            'utilisateur-modifier',
            'utilisateur-supprimer',

            'rôles',
            'rôles-ajoute',
            'rôles-voir',
            'rôles-modifier',
            'rôles-supprimer',
         ];
 
          // Looping and Inserting Array's Permissions into Permission Table
          foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);

        }
    }
}