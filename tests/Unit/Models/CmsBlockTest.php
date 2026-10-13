<?php

namespace Tests\Unit\Models;

use App\Models\CmsBlock;
use App\Support\AboutPageSections;
use App\Support\ArticlesPageSections;
use App\Support\ContactPageSections;
use App\Support\HomeSections;
use Tests\TestCase;

class CmsBlockTest extends TestCase
{
    public function test_page_section_keys_aggregates_all_dedicated_page_section_blocks(): void
    {
        $keys = CmsBlock::pageSectionKeys();

        foreach (HomeSections::blockKeys() as $key) {
            $this->assertContains($key, $keys);
        }

        foreach (AboutPageSections::blockKeys() as $key) {
            $this->assertContains($key, $keys);
        }

        foreach (ContactPageSections::blockKeys() as $key) {
            $this->assertContains($key, $keys);
        }

        foreach (ArticlesPageSections::blockKeys() as $key) {
            $this->assertContains($key, $keys);
        }

        $this->assertContains('about_approach', $keys);
        $this->assertContains('contact_hero', $keys);
        $this->assertContains('articles_promo', $keys);
        $this->assertContains('hero', $keys);
    }

    public function test_admin_cms_pages_view_guards_against_array_values(): void
    {
        $viewContent = file_get_contents(resource_path('views/admin/cms/pages/index.blade.php'));

        $this->assertStringContainsString('array_filter', $viewContent);
        $this->assertStringContainsString('! is_array($value)', $viewContent);
        $this->assertStringContainsString('is_scalar($value)', $viewContent);
    }
}
