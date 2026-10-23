<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Core\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\Modules\Demo\DemoModule;
use Tests\TestCase;

class ModuleToggleTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->registerModule(new DemoModule);
    }

    public function test_an_enabled_module_answers_and_appears_in_the_sidebar(): void
    {
        $this->actingAs($this->userWithPermissions('demo.view'));

        $this->get(route('demo.index'))->assertOk();
        $this->assertContains('Démo', $this->navigationTitles($this->get(route('dashboard'))));
    }

    public function test_a_disabled_module_answers_404_and_leaves_the_sidebar_even_for_the_super_admin(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->patch(route('settings.modules.update', 'demo'), ['enabled' => false])
            ->assertRedirect(route('settings.modules.index'));

        $this->get(route('demo.index'))->assertNotFound();
        $this->assertNotContains('Démo', $this->navigationTitles($this->get(route('dashboard'))));

        $this->patch(route('settings.modules.update', 'demo'), ['enabled' => true]);

        $this->get(route('demo.index'))->assertOk();
    }

    public function test_a_required_module_cannot_be_disabled(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->patch(route('settings.modules.update', 'projects'), ['enabled' => false])
            ->assertSessionHasErrors('enabled');

        $this->assertTrue($this->app->make(ModuleRegistry::class)->isEnabled('projects'));
    }

    public function test_a_module_cannot_be_enabled_while_a_dependency_is_disabled(): void
    {
        $registry = $this->app->make(ModuleRegistry::class);
        $registry->register(new class extends DemoModule
        {
            public function key(): string
            {
                return 'demo-extension';
            }

            public function dependencies(): array
            {
                return ['demo'];
            }
        });

        $this->actingAs(User::factory()->superAdmin()->create());

        $this->patch(route('settings.modules.update', 'demo'), ['enabled' => false])
            ->assertSessionHasErrors('enabled'); // demo-extension, enabled by default, depends on it

        $this->patch(route('settings.modules.update', 'demo-extension'), ['enabled' => false])->assertSessionHasNoErrors();
        $this->patch(route('settings.modules.update', 'demo'), ['enabled' => false])->assertSessionHasNoErrors();

        $this->patch(route('settings.modules.update', 'demo-extension'), ['enabled' => true])
            ->assertSessionHasErrors('enabled');
    }

    public function test_an_unknown_module_answers_404(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->patch(route('settings.modules.update', 'nope'), ['enabled' => true])
            ->assertNotFound();
    }
}
