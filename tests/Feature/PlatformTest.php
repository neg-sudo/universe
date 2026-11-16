<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_page_loads_with_live_catalogue_counts(): void
    {
        $tools = Tool::published()->count();
        $articles = Article::published()->count();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('UniVerse');
        $response->assertSee((string) $tools);
        $response->assertSee((string) $articles);
    }

    public function test_tools_index_and_category_filter_work(): void
    {
        $this->get('/tools')->assertOk()->assertSee('Age Calculator');

        $this->get('/tools?category=mathematics')
            ->assertOk()
            ->assertSee('Percentage Calculator')
            ->assertDontSee('Word Counter');
    }

    public function test_tool_page_renders_form_and_calculation_metadata(): void
    {
        $tool = Tool::query()->where('slug', 'percentage-calculator')->firstOrFail();

        $this->get(route('tools.show', $tool->slug))
            ->assertOk()
            ->assertSee($tool->name)
            ->assertSee('data-tool="percentage-calculator"', false)
            ->assertSee('Runs in your browser');
    }

    public function test_knowledge_index_category_and_article_pages_load(): void
    {
        $article = Article::query()->with('category')->firstOrFail();

        $this->get('/knowledge')->assertOk()->assertSee('Knowledge');

        $this->get(route('knowledge.category', $article->category->slug))
            ->assertOk()
            ->assertSee($article->category->name);

        $this->get(route('knowledge.show', [$article->category->slug, $article->slug]))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee('CC BY-SA 4.0')
            ->assertSee($article->source_url);
    }

    public function test_search_returns_tools_and_articles_together(): void
    {
        $article = Article::query()->published()->firstOrFail();

        $response = $this->get('/search?q=percentage');
        $response->assertOk();
        $response->assertSee('Percentage Calculator');

        $this->get('/search?q=' . urlencode($article->title))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee('knowledge/');

        // Keyword-only matches exercise the JSON-cast path used by PostgreSQL.
        $this->get('/search?q=discount')
            ->assertOk()
            ->assertSee('Percentage Calculator');
    }

    public function test_search_requires_at_least_two_characters(): void
    {
        $this->get('/search?q=p')->assertOk()->assertSee('Enter at least two characters');
    }

    public function test_unknown_pages_and_records_return_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound();
        $this->get('/tools/does-not-exist')->assertNotFound();
        $this->get('/knowledge/does-not-exist')->assertNotFound();
    }

    public function test_api_exposes_published_tools_and_articles(): void
    {
        $this->getJson('/api/v1/tools')
            ->assertOk()
            ->assertJsonPath('meta.total', Tool::published()->count())
            ->assertJsonStructure(['data' => [['slug', 'name', 'category', 'url']], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);

        $this->getJson('/api/v1/articles')
            ->assertOk()
            ->assertJsonPath('meta.total', Article::published()->count());

        $this->getJson('/api/v1/search?q=percentage')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['term', 'total']]);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_tool_visit_tracking_increments_usage_and_records_an_event(): void
    {
        $tool = Tool::query()->where('slug', 'percentage-calculator')->firstOrFail();
        $before = $tool->usage_count;

        $this->post(route('tools.track', $tool->slug))->assertNoContent();

        $this->assertSame($before + 1, $tool->fresh()->usage_count);
        $this->assertDatabaseHas('tool_usage_events', ['tool_id' => $tool->id]);
    }
}
