<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageSearchElasticService;
use Illuminate\Console\Command;

class ImagesIndexElasticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:images-index-elastic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index images to elasticsearch';

    /**
     * Execute the console command.
     */
    public function handle(ImageSearchElasticService $imageSearchElasticService)
    {
        $this->info("Начинаем индексацию изображений...");
        $this->output->progressStart(Image::query()->count());

        $imageSearchElasticService->createIndexIfNeeded();

        Image::query()->chunk(1000, function ($images) use ($imageSearchElasticService) {
            if (!$imageSearchElasticService->indexImages($images)) {
                $this->output->error("Не удалось проиндексировать изображения");
                exit;
            }

            $this->output->progressAdvance(count($images));
        });

        $this->output->progressFinish();
        $this->info('Индексация завершена!');
    }
}
