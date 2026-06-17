<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Organization;
use App\Services\YandexReviewParser;
use Illuminate\Support\Carbon;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ParseOrganizationJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public $timeout = 180;
    public $tries = 1;

    public function __construct(
        public Organization $organization
    ){}

    public function handle(YandexReviewParser $parser)
    {
        $this->organization->update([
            'status' => 'parsing',
        ]);
        
        try {
            $parsed = $parser->parse($this->organization->url);

            if (empty($parsed['reviews']) && $parsed['reviews_count'] > 0) {
                throw new \Exception('Reviews were not parsed');
            }

            $this->organization->update([
                'title' => $parsed['title'],
                'rating' => $parsed['rating'],
                'ratings_count' => $parsed['ratings_count'],
                'reviews_count' => $parsed['reviews_count'],
            ]);

            $this->organization->reviews()->delete();

            $rows = [];

            foreach ($parsed['reviews'] as $review) {
                $rows[] = [
                    'organization_id' => $this->organization->id,
                    'author' => $review['author'],
                    'text' => $review['text'],
                    'rating' => $review['rating'],
                    'review_date' => $review['date'] ? Carbon::parse($review['date']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows) {
                Review::insert($rows);

                $pages = ceil(count($rows) / 50);

                for ($i = 1; $i <= $pages; $i++) {
                    
                    Cache::forget("organization:{$this->organization->id}:reviews:{$i}");
                }
            }

            $this->organization->update([
                'status' => 'completed'
            ]);

        }catch(\Throwable) {

            $this->organization->update([
                'status' => 'failed'
            ]);
        }
       
    }
}