<?php

declare(strict_types=1);

namespace App\Services\Ai;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebSearchService
{
    /**
     * Perform a web search and return a summary of the results.
     */
    public function search(string $query): string
    {
        if (! (bool) \config('ai.web_search.enabled', true)) {
            return "Internet search is disabled for '{$query}'.";
        }

        Log::info("AI Bridge: Requesting web search for: {$query}");

        try {
            $instantAnswer = $this->searchInstantAnswer($query);

            if ($instantAnswer !== '') {
                return "Live Search Results for '{$query}':\n\n".$instantAnswer;
            }

            $htmlResults = $this->searchDuckDuckGoHtml($query);

            if ($htmlResults !== '') {
                return "Live Search Results for '{$query}':\n\n".$htmlResults;
            }

            return "The search for '{$query}' completed but no direct answer was found. Advise the user to check a trusted live source.";
        } catch (Throwable $e) {
            Log::error('AI Bridge: Web search failed: '.$e->getMessage(), [
                'query' => $query,
            ]);

            return 'The internet connection is temporarily unavailable.';
        }
    }

    private function searchInstantAnswer(string $query): string
    {
        $response = Http::acceptJson()
            ->connectTimeout((int) \config('ai.web_search.connect_timeout', 5))
            ->timeout((int) \config('ai.web_search.timeout', 15))
            ->retry([200, 500, 1000], throw: false)
            ->get('https://api.duckduckgo.com/', [
                'q' => $query,
                'format' => 'json',
                'no_html' => 1,
                'skip_disambig' => 1,
            ]);

        if (! $response->successful()) {
            return '';
        }

        $data = $response->json();

        if (! \is_array($data)) {
            return '';
        }

        $results = [];

        foreach (['AbstractText', 'Answer', 'Definition'] as $key) {
            $value = \trim((string) ($data[$key] ?? ''));

            if ($value !== '') {
                $results[] = $value;
            }
        }

        foreach ($this->extractDuckDuckGoTopics($data['Results'] ?? []) as $topic) {
            $results[] = $topic;
        }

        foreach ($this->extractDuckDuckGoTopics($data['RelatedTopics'] ?? []) as $topic) {
            $results[] = $topic;
        }

        return \implode("\n", \array_slice(\array_unique($results), 0, 5));
    }

    /**
     * @return array<int, string>
     */
    private function extractDuckDuckGoTopics(mixed $topics): array
    {
        if (! \is_array($topics)) {
            return [];
        }

        $results = [];

        foreach ($topics as $topic) {
            if (! \is_array($topic)) {
                continue;
            }

            if (isset($topic['Topics'])) {
                $results = [...$results, ...$this->extractDuckDuckGoTopics($topic['Topics'])];

                continue;
            }

            $text = \trim((string) ($topic['Text'] ?? ''));
            $url = \trim((string) ($topic['FirstURL'] ?? ''));

            if ($text !== '' && $url !== '') {
                $results[] = "{$text} ({$url})";
            } elseif ($text !== '') {
                $results[] = $text;
            }
        }

        return $results;
    }

    private function searchDuckDuckGoHtml(string $query): string
    {
        $response = Http::connectTimeout((int) \config('ai.web_search.connect_timeout', 5))
            ->timeout((int) \config('ai.web_search.timeout', 15))
            ->retry([200, 500, 1000], throw: false)
            ->withHeaders([
                'User-Agent' => 'FreshLeafLocalAiSearch/1.0',
            ])
            ->get('https://duckduckgo.com/html/', [
                'q' => $query,
            ]);

        if (! $response->successful()) {
            return '';
        }

        $html = $response->body();

        if (\trim($html) === '') {
            return '';
        }

        $previous = \libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML($html);
        \libxml_clear_errors();
        \libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' result ')]");

        if ($nodes === false) {
            return '';
        }

        $results = [];

        foreach ($nodes as $node) {
            $titleNode = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' result__a ')]", $node)?->item(0);
            $snippetNode = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' result__snippet ')]", $node)?->item(0);

            $title = \trim((string) $titleNode?->textContent);
            $snippet = \trim((string) $snippetNode?->textContent);

            if ($title === '' && $snippet === '') {
                continue;
            }

            $results[] = \trim($title.($snippet !== '' ? ': '.$snippet : ''));

            if (\count($results) >= 5) {
                break;
            }
        }

        return \implode("\n", \array_unique($results));
    }
}
