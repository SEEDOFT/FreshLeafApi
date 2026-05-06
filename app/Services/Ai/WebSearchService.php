<?php

declare(strict_types=1);

namespace App\Services\Ai;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function array_unique;
use function config;
use function count;
use function implode;
use function is_array;
use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function trim;

class WebSearchService
{
    /**
     * Search the web for the given query.
     */
    public function search(string $query): string
    {
        if (! (bool) config('ai.web_search.enabled', true)) {
            return '';
        }

        try {
            // 1. Try DuckDuckGo Instant Answer (Quick API)
            $quickResults = $this->searchQuick($query);
            if ($quickResults !== '') {
                return "Live Search Results:\n\n".$quickResults;
            }

            // 2. Fallback to DuckDuckGo HTML results
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ])
                ->get('https://duckduckgo.com/html/', [
                    'q' => $query,
                ]);

            if ($response->failed()) {
                Log::error('Web search failed', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);

                return '';
            }

            return $this->parseResults($response->body());
        } catch (ConnectionException $e) {
            Log::error('Web search connection failed', [
                'query' => $query,
                'message' => $e->getMessage(),
            ]);

            return 'The internet connection is temporarily unavailable.';
        }
    }

    /**
     * Search using DuckDuckGo API (Instant Answer).
     */
    public function searchQuick(string $query): string
    {
        try {
            $response = Http::connectTimeout((int) config('ai.web_search.connect_timeout', 5))
                ->timeout((int) config('ai.web_search.timeout', 15))
                ->get('https://api.duckduckgo.com/', [
                    'q' => $query,
                    'format' => 'json',
                    'no_html' => 1,
                    'skip_disambig' => 1,
                ]);

            if ($response->failed()) {
                return '';
            }

            $data = $response->json();

            if (! is_array($data)) {
                return '';
            }

            $results = [];

            // Main abstract
            $abstract = trim((string) ($data['AbstractText'] ?? ''));
            if ($abstract !== '') {
                $results[] = $abstract;
            }

            // Related topics
            $topics = $data['RelatedTopics'] ?? [];
            if (! is_array($topics)) {
                return implode("\n", array_slice(array_unique($results), 0, 5));
            }

            foreach ($topics as $topic) {
                if (! is_array($topic)) {
                    continue;
                }

                $text = trim((string) ($topic['Text'] ?? ''));
                $url = trim((string) ($topic['FirstURL'] ?? ''));

                if ($text !== '') {
                    $results[] = $text.($url !== '' ? " ({$url})" : '');
                }

                if (count($results) >= 5) {
                    break;
                }
            }

            return implode("\n", array_slice(array_unique($results), 0, 5));
        } catch (ConnectionException) {
            return '';
        }
    }

    /**
     * Search Wikipedia as a high-quality fallback.
     */
    public function searchWikipedia(string $query): string
    {
        try {
            $response = Http::connectTimeout((int) config('ai.web_search.connect_timeout', 5))
                ->timeout((int) config('ai.web_search.timeout', 15))
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.\urlencode($query));

            if ($response->failed()) {
                return '';
            }

            $data = $response->json();

            return is_array($data) ? trim((string) ($data['extract'] ?? '')) : '';
        } catch (ConnectionException) {
            return '';
        }
    }

    /**
     * Parse the search results from the HTML body.
     */
    private function parseResults(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' result ')]");

        if ($nodes === false) {
            return '';
        }

        $results = [];

        foreach ($nodes as $node) {
            $titleQuery = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' result__a ')]", ($node instanceof \DOMNode) ? $node : null);
            $snippetQuery = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' result__snippet ')]", ($node instanceof \DOMNode) ? $node : null);

            $titleNode = ($titleQuery instanceof \DOMNodeList) ? $titleQuery->item(0) : null;
            $snippetNode = ($snippetQuery instanceof \DOMNodeList) ? $snippetQuery->item(0) : null;

            $title = trim(($titleNode instanceof \DOMNode) ? (string) $titleNode->textContent : '');
            $snippet = trim(($snippetNode instanceof \DOMNode) ? (string) $snippetNode->textContent : '');

            if ($title === '' && $snippet === '') {
                continue;
            }

            $results[] = trim($title.($snippet !== '' ? ': '.$snippet : ''));

            if (count($results) >= 5) {
                break;
            }
        }

        return implode("\n", array_unique($results));
    }
}
