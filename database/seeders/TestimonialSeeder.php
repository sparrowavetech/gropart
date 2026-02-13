<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Testimonial\Models\Testimonial;

class TestimonialSeeder extends BaseSeeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'James Dopli',
                'company' => 'Developer',
                'content' => 'Thanks for all your efforts and teamwork over the last several months!  Thank you so much',
            ],
            [
                'name' => 'Theodore Handle',
                'company' => 'CO Founder',
                'content' => 'How you use the city or town name is up to you. All results may be freely used in any work.',
            ],
            [
                'name' => 'Shahnewaz Sakil',
                'company' => 'UI/UX Designer',
                'content' => 'Very happy with our choice to take our daughter to Brave care. The entire team was great! Thank you!',
            ],
            [
                'name' => 'Albert Flores',
                'company' => 'Bank of America',
                'content' => 'Wedding day savior! 5 stars. Their bridal collection is a game-changer. Made me feel like a star.',
            ],
        ];

        Testimonial::query()->truncate();

        $files = $this->getFilesFromPath('customers');

        foreach ($testimonials as $item) {
            Testimonial::query()->create([
                ...$item,
                'image' => $files->unique()->random(),
            ]);
        }
    }
}
