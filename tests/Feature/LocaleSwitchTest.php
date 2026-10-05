<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_to_burmese_and_see_translated_nav(): void
    {
        $this->get(route('locale.switch', 'my'))
            ->assertRedirect();

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('သင်တန်း', false)
            ->assertSee('ဖိုရမ်', false)
            ->assertSee('locale-my', false);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->get(route('locale.switch', 'fr'))
            ->assertNotFound();
    }

    public function test_english_switch_restores_english_labels(): void
    {
        $this->withSession(['locale' => 'my'])
            ->get(route('locale.switch', 'en'))
            ->assertRedirect();

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Browse', false)
            ->assertDontSee('locale-my', false);
    }
}
