<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Database\Seeders\Traits\HasProductSeeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends BaseSeeder
{
    use HasProductSeeder;

    public function run(): void
    {
        $this->uploadFiles('products');

        $products = [
            [
                'name' => 'Dual Camera 20MP',
                'price' => 80.25,
                'is_featured' => true,
            ],
            [
                'name' => 'Smart Watches',
                'price' => 40.5,
                'sale_price' => 35,
                'is_featured' => true,
            ],
            [
                'name' => 'Beat Headphone',
                'price' => 20,
                'is_featured' => true,
            ],
            [
                'name' => 'Red & Black Headphone',
                'price' => rand(500, 600),
                'is_featured' => true,
            ],
            [
                'name' => 'Smart Watch External',
                'price' => rand(700, 900),
                'is_featured' => true,
            ],
            [
                'name' => 'Nikon HD camera',
                'price' => rand(400, 500),
                'is_featured' => true,
            ],
            [
                'name' => 'Audio Equipment',
                'price' => rand(500, 600),
                'is_featured' => true,
            ],
            [
                'name' => 'Smart Televisions',
                'price' => rand(1100, 1300),
                'sale_price' => rand(800, 1000),
                'is_featured' => true,
            ],
            [
                'name' => 'Samsung Smart Phone',
                'price' => rand(500, 600),
                'is_featured' => true,
            ],
            [
                'name' => 'Herschel Leather Duffle Bag In Brown Color',
                'price' => rand(1100, 1300),
                'sale_price' => rand(800, 1000),
            ],
            [
                'name' => 'Xbox One Wireless Controller Black Color',
                'price' => rand(1100, 1300),
                'sale_price' => rand(500, 700),
            ],
            [
                'name' => 'EPSION Plaster Printer',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'Sound Intone I65 Earphone White Version',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'B&O Play Mini Bluetooth Speaker',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'Apple MacBook Air Retina 13.3-Inch Laptop',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'Apple MacBook Air Retina 12-Inch Laptop',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'Samsung Gear VR Virtual Reality Headset',
                'price' => rand(500, 600),
            ],
            [
                'name' => 'Aveeno Moisturizing Body Shower 450ml',
                'price' => rand(900, 1300),
                'sale_price' => rand(200, 700),
            ],
            [
                'name' => 'NYX Beauty Couton Pallete Makeup 12',
                'price' => rand(900, 1300),
                'sale_price' => rand(300, 800),
            ],
            [
                'name' => 'NYX Beauty Couton Pallete Makeup 12',
                'price' => rand(700, 1000),
                'sale_price' => rand(400, 700),
            ],
            [
                'name' => 'MVMTH Classical Leather Watch In Black',
                'price' => rand(600, 1000),
                'sale_price' => rand(200, 500),
            ],
            [
                'name' => 'Baxter Care Hair Kit For Bearded Mens',
                'price' => rand(400, 700),
                'sale_price' => rand(100, 300),
            ],
            [
                'name' => 'Ciate Palemore Lipstick Bold Red Color',
                'price' => rand(500, 1300),
                'sale_price' => rand(200, 400),
            ],
            [
                'name' => 'Vimto Squash Remix Apple 1.5 Litres',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Crock Pot Slow Cooker',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Taylors of Harrogate Yorkshire Coffee',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Soft Mochi & Galeto Ice Cream',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Naked Noodle Egg Noodles Singapore',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Saute Pan Silver',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Bar S – Classic Bun Length Franks',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Broccoli Crowns',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Slimming World Vegan Mac Greens',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Häagen-Dazs Salted Caramel',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland 3 Solo Exotic Burst',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Extreme Budweiser Light Can',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland Macaroni Cheese Traybake',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Dolmio Bolognese Pasta Sauce',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Sitema BakeIT Plastic Box',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Wayfair Basics Dinner Plate Storage',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Miko The Panda Water Bottle',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Sesame Seed Bread',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Morrisons The Best Beef',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Avocado, Hass Large',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Italia Beef Lasagne',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Maxwell House Classic Roast Mocha',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Bottled Pure Water 500ml',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Famart Farmhouse Soft White',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Coca-Cola Original Taste',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Casillero Diablo Cabernet Sauvignon',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Arla Organic Free Range Milk',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Aptamil Follow On Baby Milk',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Cuisinart Chef’S Classic Hard-Anodized',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Corn, Yellow Sweet',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Hobnobs The Nobbly Biscuit',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Honest Organic Still Lemonade',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Ice Beck’s Beer 350ml x 24 Pieces',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland 6 Hot Cross Buns',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland Luxury 4 Panini Rolls',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland Soft Scoop Vanilla',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Iceland Spaghetti Bolognese',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Kellogg’s Coco Pops Cereal',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Kit Kat Chunky Milk Chocolate',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Large Green Bell Pepper',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Pice 94w Beasley Journal',
                'price' => rand(500, 1300),
            ],
            [
                'name' => 'Province Piece Glass Drinking Glass',
                'price' => rand(500, 1300),
            ],
        ];

        foreach ($products as $key => &$item) {
            $item['description'] = '<ul><li> Unrestrained and portable active stereo speaker</li>
            <li> Free from the confines of wires and chords</li>
            <li> 20 hours of portable capabilities</li>
            <li> Double-ended Coil Cord with 3.5mm Stereo Plugs Included</li>
            <li> 3/4″ Dome Tweeters: 2X and 4″ Woofer: 1X</li></ul>';
            $item['content'] = '<p>Short Hooded Coat features a straight body, large pockets with button flaps, ventilation air holes, and a string detail along the hemline. The style is completed with a drawstring hood, featuring Rains&rsquo; signature built-in cap. Made from waterproof, matte PU, this lightweight unisex rain jacket is an ode to nostalgia through its classic silhouette and utilitarian design details.</p>
                                <p>- Casual unisex fit</p>

                                <p>- 64% polyester, 36% polyurethane</p>

                                <p>- Water column pressure: 4000 mm</p>

                                <p>- Model is 187cm tall and wearing a size S / M</p>

                                <p>- Unisex fit</p>

                                <p>- Drawstring hood with built-in cap</p>

                                <p>- Front placket with snap buttons</p>

                                <p>- Ventilation under armpit</p>

                                <p>- Adjustable cuffs</p>

                                <p>- Double welted front pockets</p>

                                <p>- Adjustable elastic string at hempen</p>

                                <p>- Ultrasonically welded seams</p>

                                <p>This is a unisex item, please check our clothing &amp; footwear sizing guide for specific Rains jacket sizing information. RAINS comes from the rainy nation of Denmark at the edge of the European continent, close to the ocean and with prevailing westerly winds; all factors that contribute to an average of 121 rain days each year. Arising from these rainy weather conditions comes the attitude that a quick rain shower may be beautiful, as well as moody- but first and foremost requires the right outfit. Rains focus on the whole experience of going outside on rainy days, issuing an invitation to explore even in the most mercurial weather.</p>';

            $images = [
                'products/' . ($key + 1) . '.jpg',
            ];

            for ($i = 1; $i <= 3; $i++) {
                if (File::exists(database_path('seeders/files/products/' . ($key + 1) . '-' . $i . '.jpg'))) {
                    $images[] = 'products/' . ($key + 1) . '-' . $i . '.jpg';
                }
            }

            $item['images'] = $images;
        }

        $this->createProducts($products);
    }
}
