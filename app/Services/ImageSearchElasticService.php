<?php

namespace App\Services;

use App\Models\Image;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ImageSearchElasticService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->setBasicAuthentication('elastic', config('elasticsearch.password'))
            ->setCABundle("/home/ikudinov/sis.pics/elastic.crt")
            ->build();
    }

    public function createIndexIfNeeded(): void
    {
        $params = [
            'index' => 'images',
        ];

        try {
            $response = $this->client->indices()->exists($params);

            if ($response->getStatusCode() === 404) {
                $this->client->indices()->create([
                    'index' => 'images',
                    'body' => [
                        /*'settings' => [
                            'analysis' => [
                                'tokenizer' => [
                                    'standard_tokenizer' => [
                                        'type' => 'standard',
                                    ],
                                ],
                                'filter' => [
                                    'english_stemmer' => [
                                        'type' => 'stemmer',
                                        'language' => 'english',
                                    ],
                                ],
                                'analyzer' => [
                                    'default' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'standard_tokenizer',
                                        'filter' => ['lowercase', 'english_stemmer'],
                                    ],
                                ],
                            ],
                        ],*/
                        'mappings' => [
                            'properties' => [
                                'tags' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'tag' => [
                                            'type' => 'text',
                                            /*'analyzer' => 'default'*/
                                        ],
                                        'order' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                ],
                                'thumb_file' => [
                                    'type' => 'text'
                                ],
                            ],
                        ],
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("createIndexIfNeeded: " . $e->getMessage());
        }
    }

    public function indexImages(Collection $images): bool
    {
        try {
            $bulkParams = [];
            foreach ($images as $image) {
                /** @var Image $image */
                if (!$image->tags) {
                    continue;
                }

                $tags = explode(',', $image->tags);
                $boostedTags = [];

                foreach ($tags as $index => $tag) {
                    $boostedTags[] = [
                        'tag' => trim($tag),
                        'order' => $index + 1, // Порядок начинается с 1
                    ];
                }

                $bulkParams[] = [
                    'index' => [
                        '_index' => 'images',
                        '_id' => $image->id,
                    ],
                ];

                $bulkParams[] = [
                    'tags' => $boostedTags,
                    'thumb_file' => $image->thumb_file
                ];
            }

            if (!empty($bulkParams)) {
                $response = $this->client->bulk(['body' => $bulkParams]);

                if (isset($response['errors']) && $response['errors']) {
                    Log::error('indexImages bulk errors: ' . json_encode($response['items']));
                }
            }
        } catch (\Throwable $ex) {
            Log::error("indexImages: " . $ex->getMessage());
            return false;
        }

        return true;
    }

    public function searchImagesByTags(string $searchQuery, int $page = 1, int $perPage = 10): array
    {
        /*$searchTags[] = $searchQuery;
        $searchTags = array_merge($searchTags, explode(" ", $searchQuery));*/

        $searchTags = explode(" ", $searchQuery);

        /*$searchTags = $this->generatePhraseArray($searchQuery);
        $searchTags = array_merge($searchTags, explode(" ", $searchQuery));
        $searchTags = array_unique($searchTags);

        $singleTags = [];
        $compoundPhrases = [];

        foreach ($searchTags as $tag) {
            if (strpos($tag, " ") === false) {
                $singleTags[] = $tag;
            } else {
                $compoundPhrases[] = $tag;
            }
        }

        $searchTags = array_merge($singleTags, $compoundPhrases);*/

        $shouldParts = [];
        foreach ($searchTags as $tag) {
            $shouldParts[] = [
                'nested' => [
                    'path' => 'tags',
                    'query' => [
                        'bool' => [
                            'must' => [
                                [ 'match' => [ 'tags.tag' => $tag ] ],
                                //[ 'term' => [ 'tags.tag' => $tag ] ],
                            ],
                            /*'should' => [
                                [
                                    'match_phrase' => [
                                        'tags.tag' => [
                                            'query' => $tag,
                                            'boost' => 2.0,
                                        ],
                                    ],
                                ],
                                [
                                    'term' => [
                                        'tags.tag' => [
                                            'value' => $tag,
                                        ],
                                    ],
                                ],
                            ],*/
                        ],
                    ],
                    'score_mode' => 'max',
                ],
            ];
        }

        $query = [
            'track_total_hits' => 10000, // точное количество считать или нет
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'query' => [
                'bool' => [
                    'must' => $shouldParts,
                    /*'minimum_should_match' => 1,*/
                ],
            ],
            'sort' => [
                '_score' => [ 'order' => 'desc' ],
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'source' => "
                        double score = 0;
                        for (tag in params['_source']['tags']) {
                            if (params.searchTags.contains(tag.tag)) {
                                score += 1.0 / (tag.order + 1);
                            }
                        }
                        return score;
                    ",
                        'params' => [
                            'searchTags' => $searchTags,
                        ],
                    ],
                    'order' => 'desc',
                ],
            ],
        ];

        try {
            $response = $this->client->search([
                'index' => 'images',
                'body' => $query,
            ]);

            return [
                'data' => $response['hits']['hits'],
                'total' => $response['hits']['total']['value'],
                'perPage' => $perPage,
                'currentPage' => $page,
            ];
        } catch (\Throwable $ex) {
            Log::error("searchImagesByTags: " . $ex->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'perPage' => $perPage,
                'currentPage' => 1,
            ];
        }
    }

    private function generatePhraseArray($phrase): array
    {
        $result = [];
        $words = explode(" ", $phrase);
        while (!empty($words)) {
            $result[] = implode(" ", $words);
            array_pop($words);
        }

        return $result;
    }
}
