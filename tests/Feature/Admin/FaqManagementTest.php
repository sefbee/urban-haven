<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_update_and_delete_a_faq(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        // create
        $this->actingAs($owner)->post(route('admin.faqs.store'), [
            'question' => 'What is the process?',
            'answer' => '<p>We guide you through every step.</p>',
            'group' => 'Buying',
            'is_visible' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $faq = Faq::query()->sole();
        $this->assertTrue($faq->is_visible);
        $this->assertSame('Buying', $faq->group);
        $this->assertStringContainsString('We guide you through every step.', $faq->answer);

        // update
        $this->actingAs($owner)->put(route('admin.faqs.update', $faq), [
            'question' => 'What is the buying process?',
            'answer' => '<p>Updated answer.</p>',
            'group' => 'Buying',
            'is_visible' => '0',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('What is the buying process?', $faq->fresh()->question);
        $this->assertFalse($faq->fresh()->is_visible);

        // delete
        $this->actingAs($owner)->delete(route('admin.faqs.destroy', $faq))->assertRedirect();
        $this->assertModelMissing($faq);
    }

    public function test_editor_added_faq_stays_hidden_and_cannot_be_made_visible(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->post(route('admin.faqs.store'), [
            'question' => 'Can editors publish?',
            'answer' => 'No.',
            'is_visible' => '1',
        ])->assertRedirect();

        $faq = Faq::query()->sole();
        $this->assertFalse($faq->is_visible, 'Editor submissions must start hidden.');
    }

    public function test_editor_updating_a_visible_faq_cannot_hide_it(): void
    {
        $faq = Faq::query()->create([
            'question' => 'Live FAQ?',
            'answer' => 'Yes.',
            'group' => 'General',
            'is_visible' => true,
        ]);

        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->put(route('admin.faqs.update', $faq), [
            'question' => 'Live FAQ?',
            'answer' => 'Updated.',
            'group' => 'General',
            'is_visible' => '0',
        ])->assertRedirect();

        $this->assertTrue($faq->fresh()->is_visible, 'Editor cannot change visibility of existing FAQs.');
    }

    public function test_faq_answer_has_html_sanitised(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.faqs.store'), [
            'question' => 'Is it safe?',
            'answer' => '<p>Yes.</p><script>evil()</script>',
            'is_visible' => '0',
        ])->assertRedirect();

        $answer = Faq::query()->value('answer');
        $this->assertStringNotContainsString('<script>', (string) $answer);
    }

    public function test_faq_validation_requires_question_and_answer(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.faqs.store'), ['question' => '', 'answer' => ''])
            ->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_faq_group_defaults_to_general_when_blank(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.faqs.store'), [
            'question' => 'Generic question?',
            'answer' => 'Generic answer.',
            'group' => '',
        ])->assertRedirect();

        $this->assertSame('General', Faq::query()->value('group'));
    }

    public function test_faq_delete_is_audited(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $faq = Faq::query()->create(['question' => 'Delete me?', 'answer' => 'Yes.', 'group' => 'General', 'is_visible' => false]);

        $this->actingAs($owner)->delete(route('admin.faqs.destroy', $faq))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'faq.deleted', 'subject_id' => $faq->id]);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
