<?php

declare(strict_types=1);

namespace App\Tests\News\Infrastructure\Adapter;

use App\News\Domain\Service\ImportanceScorerInterface;
use App\News\Domain\ValueObject\ImportanceScore;
use App\News\Infrastructure\Adapter\CryptoCompareNewsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class CryptoCompareNewsProviderTest extends TestCase
{
    private ImportanceScorerInterface $mockScorer;

    protected function setUp(): void
    {
        $this->mockScorer = $this->createMock(ImportanceScorerInterface::class);
        $this->mockScorer
            ->method('calculateScore')
            ->willReturn(ImportanceScore::medium());
    }

    public function test_fetches_latest_news_successfully(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '123456',
                    'guid' => 'https://example.com/news/1',
                    'published_on' => time() - 3600,
                    'imageurl' => 'https://example.com/image.jpg',
                    'title' => 'Bitcoin reaches new milestone',
                    'url' => 'https://example.com/news/1',
                    'body' => '<p>Bitcoin has reached a new milestone today as trading volume increases.</p>',
                    'tags' => '',
                    'categories' => 'BTC|Blockchain',
                    'upvotes' => '100',
                    'downvotes' => '10',
                    'lang' => 'EN',
                    'source_info' => [
                        'name' => 'CryptoNews',
                        'img' => 'https://example.com/logo.png'
                    ]
                ],
                [
                    'id' => '123457',
                    'guid' => 'https://example.com/news/2',
                    'published_on' => time() - 7200,
                    'imageurl' => '',
                    'title' => 'Ethereum upgrade scheduled',
                    'url' => 'https://example.com/news/2',
                    'body' => '<p>The next Ethereum upgrade is scheduled for next month.</p>',
                    'tags' => '',
                    'categories' => 'ETH|Technology',
                    'upvotes' => '50',
                    'downvotes' => '5',
                    'lang' => 'EN',
                    'source_info' => [
                        'name' => 'TechCrypto',
                        'img' => 'https://example.com/logo2.png'
                    ]
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(10);

        $this->assertCount(2, $articles);
        $this->assertEquals('Bitcoin reaches new milestone', $articles[0]->getTitle());
        $this->assertEquals('Ethereum upgrade scheduled', $articles[1]->getTitle());
        $this->assertContains('BTC', $articles[0]->getRelatedSymbols());
        $this->assertContains('ETH', $articles[1]->getRelatedSymbols());
    }

    public function test_extracts_symbols_from_categories(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '123',
                    'guid' => 'https://example.com/news/1',
                    'published_on' => time() - 3600,
                    'imageurl' => '',
                    'title' => 'Crypto market update',
                    'url' => 'https://example.com/news/1',
                    'body' => '<p>Market analysis</p>',
                    'tags' => '',
                    'categories' => 'BTC|ETH|USDT|Analysis',
                    'upvotes' => '10',
                    'downvotes' => '1',
                    'lang' => 'EN',
                    'source_info' => [
                        'name' => 'CryptoNews',
                        'img' => ''
                    ]
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(1);

        $this->assertCount(1, $articles);
        $symbols = $articles[0]->getRelatedSymbols();
        $this->assertContains('BTC', $symbols);
        $this->assertContains('ETH', $symbols);
        $this->assertContains('USDT', $symbols);
    }

    public function test_extracts_symbols_from_title(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '123',
                    'guid' => 'https://example.com/news/1',
                    'published_on' => time() - 3600,
                    'imageurl' => '',
                    'title' => 'BTC and ETH show strong momentum',
                    'url' => 'https://example.com/news/1',
                    'body' => '<p>Bitcoin and Ethereum analysis</p>',
                    'tags' => '',
                    'categories' => 'Market',
                    'upvotes' => '10',
                    'downvotes' => '1',
                    'lang' => 'EN',
                    'source_info' => [
                        'name' => 'CryptoNews',
                        'img' => ''
                    ]
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(1);

        $this->assertCount(1, $articles);
        $symbols = $articles[0]->getRelatedSymbols();
        $this->assertContains('BTC', $symbols);
        $this->assertContains('ETH', $symbols);
    }

    public function test_determines_category_correctly(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '1',
                    'guid' => 'https://example.com/1',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Regulation news',
                    'url' => 'https://example.com/1',
                    'body' => '<p>Test</p>',
                    'tags' => '',
                    'categories' => 'Regulation|BTC',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ],
                [
                    'id' => '2',
                    'guid' => 'https://example.com/2',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Technology news',
                    'url' => 'https://example.com/2',
                    'body' => '<p>Test</p>',
                    'tags' => '',
                    'categories' => 'Technology',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ],
                [
                    'id' => '3',
                    'guid' => 'https://example.com/3',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Market analysis',
                    'url' => 'https://example.com/3',
                    'body' => '<p>Test</p>',
                    'tags' => '',
                    'categories' => 'Analysis|Market',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(3);

        $this->assertEquals('REGULATION', $articles[0]->getCategory()->value);
        $this->assertEquals('TECHNOLOGY', $articles[1]->getCategory()->value);
        $this->assertEquals('MARKET_ANALYSIS', $articles[2]->getCategory()->value);
    }

    public function test_generates_summary_from_body(): void
    {
        $longBody = '<p>' . str_repeat('Lorem ipsum dolor sit amet. ', 50) . '</p>';
        
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '123',
                    'guid' => 'https://example.com/news/1',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Test article',
                    'url' => 'https://example.com/news/1',
                    'body' => $longBody,
                    'tags' => '',
                    'categories' => 'BTC',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(1);

        $summary = $articles[0]->getSummary();
        $this->assertLessThanOrEqual(203, strlen($summary)); // 200 chars + "..."
        $this->assertStringEndsWith('...', $summary);
    }

    public function test_filters_news_by_symbols(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => [
                [
                    'id' => '1',
                    'guid' => 'https://example.com/1',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Bitcoin news with BTC',
                    'url' => 'https://example.com/1',
                    'body' => '<p>BTC content</p>',
                    'tags' => '',
                    'categories' => 'BTC',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ],
                [
                    'id' => '2',
                    'guid' => 'https://example.com/2',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Ethereum news with ETH',
                    'url' => 'https://example.com/2',
                    'body' => '<p>ETH content</p>',
                    'tags' => '',
                    'categories' => 'ETH',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ],
                [
                    'id' => '3',
                    'guid' => 'https://example.com/3',
                    'published_on' => time(),
                    'imageurl' => '',
                    'title' => 'Cardano news with ADA',
                    'url' => 'https://example.com/3',
                    'body' => '<p>ADA content</p>',
                    'tags' => '',
                    'categories' => 'ADA',
                    'upvotes' => '1',
                    'downvotes' => '0',
                    'lang' => 'EN',
                    'source_info' => ['name' => 'News', 'img' => '']
                ]
            ]
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchNewsBySymbols(['BTC', 'ETH'], 10);

        $this->assertCount(2, $articles);
        $titles = array_map(fn($a) => $a->getTitle(), $articles);
        $this->assertContains('Bitcoin news with BTC', $titles);
        $this->assertContains('Ethereum news with ETH', $titles);
        $this->assertNotContains('Cardano news with ADA', $titles);
    }

    public function test_handles_empty_response(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'Type' => 1,
            'Message' => 'Success',
            'Data' => []
        ]));

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $articles = $provider->fetchLatestNews(10);

        $this->assertCount(0, $articles);
    }

    public function test_handles_api_error(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);

        $httpClient = new MockHttpClient($mockResponse);
        $provider = new CryptoCompareNewsProvider($httpClient, $this->mockScorer);

        $this->expectException(\RuntimeException::class);
        $provider->fetchLatestNews(10);
    }
}
