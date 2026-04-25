<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Ai\WebSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_instant_answer_abstract(): void
    {
        Http::fake([
            'api.duckduckgo.com/*' => Http::response([
                'AbstractText' => 'Phnom Penh is warm today.',
                'Results' => [],
                'RelatedTopics' => [],
            ]),
        ]);

        $result = app(WebSearchService::class)->search('weather in Phnom Penh');

        $this->assertStringContainsString('Live Search Results', $result);
        $this->assertStringContainsString('Phnom Penh is warm today.', $result);
    }

    public function test_search_falls_back_to_related_topics_when_abstract_is_empty(): void
    {
        Http::fake([
            'api.duckduckgo.com/*' => Http::response([
                'AbstractText' => '',
                'Results' => [],
                'RelatedTopics' => [
                    [
                        'Text' => 'Cambodia vegetable market information',
                        'FirstURL' => 'https://example.com/market',
                    ],
                ],
            ]),
        ]);

        $result = app(WebSearchService::class)->search('current carrot price Cambodia');

        $this->assertStringContainsString('Cambodia vegetable market information', $result);
        $this->assertStringContainsString('https://example.com/market', $result);
    }

    public function test_search_falls_back_to_html_results_when_instant_answer_is_empty(): void
    {
        Http::fake([
            'api.duckduckgo.com/*' => Http::response([
                'AbstractText' => '',
                'Results' => [],
                'RelatedTopics' => [],
            ]),
            'duckduckgo.com/html/*' => Http::response(
                '<html><body><div class="result"><a class="result__a">Weather source</a><a class="result__snippet">Warm and cloudy.</a></div></body></html>'
            ),
        ]);

        $result = app(WebSearchService::class)->search('weather in Phnom Penh');

        $this->assertStringContainsString('Weather source', $result);
        $this->assertStringContainsString('Warm and cloudy.', $result);
    }

    public function test_search_returns_unavailable_message_on_connection_failure(): void
    {
        Http::fake(static function (): never {
            throw new ConnectionException('Network unavailable');
        });

        $result = app(WebSearchService::class)->search('weather in Phnom Penh');

        $this->assertSame('The internet connection is temporarily unavailable.', $result);
    }
}
