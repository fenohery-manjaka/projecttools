<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Local test accounts all use the password "password".
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::factory()->superAdmin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $contributor = User::factory()->create([
            'name' => 'Contributeur Test',
            'email' => 'contributeur@example.com',
        ]);
        $contributor->assignRole('Contributeur');

        Project::factory()->for($admin, 'owner')->withMember($contributor)->create([
            'name' => 'Plateforme interne de gestion',
            'description' => 'Outil commun pour organiser les projets, tâches, idées, décisions et réunions.',
        ]);

        Project::factory()->for($admin, 'owner')->create([
            'name' => 'Identité visuelle',
            'description' => 'Logo, couleurs, typographies et supports de communication.',
        ]);
    }
}
