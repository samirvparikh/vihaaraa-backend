<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\HeroSlide;
use App\Models\HomeItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the catalogue and home page content that previously lived in the React app's
 * src/data/products.js and src/data/site.js, plus one admin and one demo customer.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(['email' => 'admin@vihaaraa.com'], [
            'name' => 'Vihaaraa Admin', 'password' => '123456', 'role' => User::ROLE_ADMIN,
        ]);
        User::query()->updateOrCreate(['email' => 'customer@vihaaraa.com'], [
            'name' => 'Demo Customer', 'phone' => '123456', 'password' => 'Customer@123', 'role' => User::ROLE_CUSTOMER,
        ]);

        $this->categories();
        $this->collections();
        $this->products();
        $this->homeContent();
        $this->pages();

        Coupon::query()->updateOrCreate(['code' => 'VIHAARAA10'], ['label' => '10% off', 'type' => 'percent', 'value' => 10, 'min_order' => 0]);
        Coupon::query()->updateOrCreate(['code' => 'FESTIVE15'], ['label' => '15% off on ₹2,999+', 'type' => 'percent', 'value' => 15, 'min_order' => 2999]);
    }

    private function categories(): void
    {
        $list = [
            'necklaces' => 'Necklace Sets', 'earrings' => 'Earrings', 'bangles' => 'Bangles & Kadas', 'rings' => 'Rings',
            'bracelets' => 'Bracelets', 'maang-tikka' => 'Maang Tikka', 'hair-accessories' => 'Hair Accessories', 'anklets' => 'Anklets',
        ];
        $i = 0;
        foreach ($list as $slug => $name) {
            Category::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name, 'blurb' => 'Shop '.strtolower($name).' in every finish.', 'sort_order' => ++$i,
            ]);
        }
    }

    private function collections(): void
    {
        $list = [
            ['all', 'All Jewellery', 'Every Vihaaraa piece — plated, polished and ready to style.', 'all', null],
            ['new-launches', 'New Launches', 'Fresh off the design table.', 'new', 'style'],
            ['best-sellers', 'Best Sellers', 'The pieces everyone keeps coming back for.', 'bestseller', 'style'],
            ['ethnic', 'Ethnic', 'Kundan, temple and polki looks for every celebration.', 'manual', 'style'],
            ['western', 'Western', 'Statement and minimal pieces for modern wardrobes.', 'manual', 'style'],
            ['demi-fine', 'Demi-Fine', 'Everyday elegance in sterling silver and fine plating.', 'manual', 'style'],
            ['gold-plated', 'Gold Plated', 'Royalty in 18K, 22K and 24K gold plating.', 'manual', 'finish'],
            ['925-silver', '925 Silver', 'Hallmarked sterling silver bases with rhodium, gold or rose-gold plating.', 'manual', 'finish'],
            ['oxidised', 'Oxidised', 'Antique-finish silver tones for festive nights.', 'manual', 'finish'],
            ['kundan', 'Kundan', 'Designs that put you in the spotlight.', 'manual', 'finish'],
            ['temple', 'Temple Jewellery', 'Heritage, auspicious motifs in rich gold plating.', 'manual', 'finish'],
            ['beaded', 'Beaded', 'Pearls and beads styled in trending designs.', 'manual', 'finish'],
        ];
        foreach ($list as $i => [$handle, $title, $blurb, $rule, $group]) {
            Collection::query()->updateOrCreate(['handle' => $handle], [
                'title' => $title, 'blurb' => $blurb, 'rule' => $rule, 'menu_group' => $group, 'sort_order' => $i,
            ]);
        }
    }

    private function products(): void
    {
        // [name, category, shape, collections, baseMetal, plating, stones, stone, bg, price, mrp, rating, reviews, tags, weight, includes, sizes]
        $raw = [
            ['Elegant Red Gold-Plated Short Necklace Set', 'necklaces', 'necklace', ['ethnic', 'temple', 'gold-plated'], 'Brass', '22K Gold Plated', ['Ruby-tone Glass', 'Faux Pearl'], 'ruby', 'blush', 1149, 2299, 4.0, 128, ['bestseller'], '58 g', 'Necklace + pair of earrings'],
            ['Tribal Magic Green & Red Necklace Set', 'necklaces', 'necklace', ['ethnic', 'oxidised'], 'Brass', 'Oxidised Silver Plated', ['Emerald-tone Glass', 'Ruby-tone Glass'], 'emerald', 'beige', 1299, 2599, 3.1, 42, ['bestseller'], '64 g', 'Necklace + pair of earrings'],
            ['Divine Lakshmi Temple Bangles (Set of 2)', 'bangles', 'bangles', ['ethnic', 'temple', 'gold-plated'], 'Copper', '24K Gold Plated', ['Ruby-tone Glass', 'Emerald-tone Glass'], 'ruby', 'maroon', 1299, 2599, 3.4, 67, ['bestseller'], '72 g', '2 bangles', ['2.4', '2.6', '2.8']],
            ['Dazzling Kundan Pearl Hair Brooch', 'hair-accessories', 'brooch', ['ethnic', 'kundan'], 'Alloy', 'Gold Plated', ['Kundan', 'Faux Pearl'], 'kundan', 'stone', 1249, 2499, 3.8, 31, ['bestseller'], '40 g', '1 hair brooch with pin'],
            ['Set of 6 Gold-Plated Pearl & Stone Bangles', 'bangles', 'bangles', ['ethnic', 'beaded', 'gold-plated'], 'Brass', '22K Gold Plated', ['Faux Pearl', 'Cubic Zirconia'], 'pearl', 'blush', 1149, 2299, 4.4, 210, ['bestseller'], '90 g', '6 bangles', ['2.4', '2.6', '2.8']],
            ['White Kundan & Pearl Multi-Strand Necklace Set', 'necklaces', 'necklace', ['ethnic', 'kundan', 'beaded', 'gold-plated'], 'Brass', '22K Gold Plated', ['Kundan', 'Faux Pearl'], 'pearl', 'wine', 1149, 2399, 4.7, 312, ['bestseller', 'new'], '82 g', 'Necklace + pair of earrings'],
            ['Royal Green & Black Statement Earrings', 'earrings', 'earrings', ['western'], 'Brass', 'Gold Plated', ['Emerald-tone Glass', 'Onyx-tone Glass'], 'emerald', 'beige', 1249, 2499, 4.0, 54, ['bestseller'], '22 g', '1 pair'],
            ['Charming Coins Oxidised Statement Necklace Set', 'necklaces', 'necklace', ['ethnic', 'oxidised'], 'Brass', 'Oxidised Silver Plated', ['Glass'], 'none', 'stone', 1299, 2599, 4.3, 96, ['bestseller'], '70 g', 'Necklace + pair of earrings'],
            ['American Diamond Zircon Choker Set', 'necklaces', 'necklace', ['western', 'demi-fine'], 'Brass', 'Rhodium Plated', ['American Diamond'], 'ad', 'midnight', 2306, 4599, 4.6, 88, ['new'], '48 g', 'Choker + pair of earrings'],
            ['Gleaming White Pearl Drop Earrings', 'earrings', 'earrings', ['western', 'beaded'], 'Brass', 'Gold Plated', ['Faux Pearl'], 'pearl', 'blush', 775, 1549, 4.2, 140, ['new'], '14 g', '1 pair'],
            ['Royal Kundan Jhumka Earrings', 'earrings', 'earrings', ['ethnic', 'kundan', 'gold-plated'], 'Brass', '22K Gold Plated', ['Kundan', 'Faux Pearl'], 'kundan', 'maroon', 899, 1799, 4.5, 201, ['bestseller'], '26 g', '1 pair'],
            ['Pearl Beaded Floral Gold-Plated Choker', 'necklaces', 'necklace', ['ethnic', 'beaded', 'gold-plated'], 'Copper', '22K Gold Plated', ['Faux Pearl', 'Glass'], 'pearl', 'beige', 920, 1899, 4.1, 45, ['new'], '44 g', 'Choker + pair of earrings'],
            ['Rhodium-Plated Solitaire CZ Ring', 'rings', 'ring', ['demi-fine', 'western', '925-silver'], '925 Sterling Silver', 'Rhodium Plated', ['Cubic Zirconia'], 'ad', 'blush', 1499, 2499, 4.8, 77, ['new'], '3 g', '1 ring', ['10', '12', '14', '16']],
            ['18K Gold-Plated Minimal Chain Bracelet', 'bracelets', 'bracelet', ['demi-fine', 'western', 'gold-plated', '925-silver'], '925 Sterling Silver', '18K Gold Plated', ['Cubic Zirconia'], 'ad', 'beige', 1016, 1999, 4.4, 63, ['new'], '5 g', '1 bracelet (adjustable)'],
            ['Rose Gold Heart CZ Pendant Necklace', 'necklaces', 'necklace', ['demi-fine', 'western', '925-silver'], '925 Sterling Silver', 'Rose Gold Plated', ['Cubic Zirconia'], 'pink', 'blush', 1199, 1999, 4.6, 58, ['new'], '4 g', 'Pendant with 18" chain'],
            ['Temple Lakshmi Coin Long Haram', 'necklaces', 'necklace', ['ethnic', 'temple', 'gold-plated'], 'Brass', '22K Gold Plated', ['Ruby-tone Glass', 'Emerald-tone Glass'], 'ruby', 'maroon', 1899, 3799, 4.5, 101, [], '96 g', 'Long haram + pair of jhumkas'],
            ['Kundan & Pearl Maang Tikka', 'maang-tikka', 'tikka', ['ethnic', 'kundan'], 'Alloy', 'Gold Plated', ['Kundan', 'Faux Pearl'], 'kundan', 'wine', 649, 1299, 4.3, 89, [], '12 g', '1 maang tikka'],
            ['Oxidised Silver Ghungroo Anklets (Pair)', 'anklets', 'anklet', ['ethnic', 'oxidised'], 'Brass', 'Oxidised Silver Plated', [], 'none', 'beige', 599, 1199, 4.2, 150, ['bestseller'], '38 g', '1 pair'],
            ['Sterling Silver American Diamond Studs', 'earrings', 'earrings', ['demi-fine', 'western', '925-silver'], '925 Sterling Silver', 'Rhodium Plated', ['American Diamond'], 'ad', 'midnight', 849, 1499, 4.7, 190, [], '3 g', '1 pair'],
            ['Emerald-tone Cocktail Ring', 'rings', 'ring', ['western'], 'Brass', 'Gold Plated', ['Emerald-tone Glass', 'American Diamond'], 'emerald', 'stone', 699, 1399, 4.1, 37, [], '6 g', '1 ring (adjustable)'],
            ['Ruby Polki Chandbali Earrings', 'earrings', 'earrings', ['ethnic', 'kundan', 'gold-plated'], 'Brass', '22K Gold Plated', ['Kundan', 'Ruby-tone Glass'], 'ruby', 'blush', 1099, 2199, 4.4, 122, [], '30 g', '1 pair'],
            ['Oxidised Tribal Cuff Bangle', 'bangles', 'bangles', ['ethnic', 'oxidised'], 'Brass', 'Oxidised Silver Plated', [], 'none', 'wine', 749, 1499, 4.0, 40, [], '34 g', '1 cuff (adjustable)'],
            ['Silver-Plated Beaded Layered Necklace', 'necklaces', 'necklace', ['western', 'beaded'], 'Copper', 'Silver Plated', ['Glass Beads'], 'sapphire', 'beige', 849, 1699, 3.9, 28, ['new'], '36 g', '1 necklace'],
            ['Gold-Plated Bridal Kundan Kada Pair', 'bangles', 'bangles', ['ethnic', 'kundan', 'gold-plated'], 'Brass', '22K Gold Plated', ['Kundan', 'American Diamond'], 'kundan', 'maroon', 1599, 3199, 4.6, 73, [], '110 g', '2 kadas', ['2.4', '2.6', '2.8']],
        ];

        $categories = Category::query()->pluck('id', 'slug');
        $collections = Collection::query()->pluck('id', 'handle');

        foreach ($raw as $i => $p) {
            [$name, $cat, $shape, $cols, $metal, $plating, $stones, $stone, $bg, $price, $mrp, $rating, $reviews, $tags, $weight, $includes] = $p;
            $stoneText = $stones ? ' set with '.strtolower(implode(' and ', $stones)) : '';
            $product = Product::query()->updateOrCreate(['sku' => sprintf('VH%03d', $i + 1)], [
                'name' => $name,
                'slug' => Str::slug(str_replace('&', 'and', $name)),
                'category_id' => $categories[$cat],
                'description' => 'Crafted on a '.strtolower($metal).' base with a '.strtolower($plating).' finish'.$stoneText.'. '
                    .'Lightweight, skin-friendly and nickel-free, this piece gives you the look of fine jewellery at a fraction of the price — '
                    .'perfect for festive evenings, weddings and everyday styling.',
                'base_metal' => $metal, 'plating' => $plating, 'stones' => $stones, 'sizes' => $p[16] ?? null,
                'weight' => $weight, 'includes' => $includes, 'price' => $price, 'mrp' => $mrp,
                'rating' => $rating, 'reviews_count' => $reviews, 'shape' => $shape, 'stone' => $stone, 'bg' => $bg,
                'is_new' => in_array('new', $tags), 'is_bestseller' => in_array('bestseller', $tags), 'sort_order' => $i,
            ]);
            $product->collections()->sync(array_map(fn ($h) => $collections[$h], $cols));
        }
    }

    private function homeContent(): void
    {
        $id = fn (string $sku) => Product::query()->where('sku', $sku)->value('id');

        foreach (['BUY 1 GET 1 FREE | SITE WIDE', 'FREE SHIPPING ON ORDERS ABOVE ₹499', 'USE CODE VIHAARAA10 FOR AN EXTRA 10% OFF', 'EASY 7-DAY RETURNS & EXCHANGE'] as $i => $text) {
            Announcement::query()->updateOrCreate(['text' => $text], ['sort_order' => $i]);
        }

        $slides = [
            ['The Festive Edit', 'Kundan & Temple', 'Heirloom looks, hand-finished in 22K gold plating', 'Shop Now', '/collections/ethnic', 'VH006', 'maroon'],
            ['Demi-Fine', 'Sterling Silver Sparkle', 'Rhodium & rose-gold plated 925 silver with cubic zirconia', 'Explore', '/collections/925-silver', 'VH009', 'midnight'],
            ['Site-wide Offer', 'Buy 1 Get 1 Free', 'Add any two pieces — the lower-priced one is on us', 'Shop Best Sellers', '/collections/best-sellers', 'VH011', 'blush'],
        ];
        foreach ($slides as $i => [$eyebrow, $title, $sub, $cta, $link, $sku, $theme]) {
            HeroSlide::query()->updateOrCreate(['title' => $title], [
                'eyebrow' => $eyebrow, 'subtitle' => $sub, 'cta_text' => $cta, 'cta_link' => $link,
                'product_id' => $id($sku), 'theme' => $theme, 'sort_order' => $i,
            ]);
        }

        HomeItem::query()->delete();
        $circles = [
            ['new-launches', 'New Launches', 'VH010'], ['best-sellers', 'Best Sellers', 'VH001'], ['demi-fine', 'Demi-Fine', 'VH015'],
            ['ethnic', 'Ethnic', 'VH006'], ['western', 'Western', 'VH007'], ['925-silver', '925 Silver', 'VH013'],
        ];
        foreach ($circles as $i => [$handle, $label, $sku]) {
            HomeItem::query()->create([
                'section' => 'circles', 'label' => $label, 'link' => '/collections/'.$handle, 'product_id' => $id($sku), 'sort_order' => $i,
            ]);
        }

        $tiles = [
            ['demi-fine', 'Demi Fine', 'Everyday Elegance', 'Redefined', 'VH014', 'maroon', false],
            ['oxidised', 'Oxidised', 'Oxidised', 'for festive nights', 'VH008', 'beige', true],
            ['kundan', 'Kundan', 'Designs that put', 'you in Spotlight', 'VH024', 'maroon', false],
            ['temple', 'Temple', 'Heritage, auspicious,', 'Temple jewellery', 'VH016', 'beige', false],
            ['beaded', 'Beaded', 'Styled', 'in trending designs', 'VH012', 'wine', true],
            ['gold-plated', 'Gold Plated', 'Gold Plated', 'Royalty', 'VH003', 'wine', false],
        ];
        foreach ($tiles as $i => [$handle, $label, $l1, $l2, $sku, $bg, $italic]) {
            HomeItem::query()->create([
                'section' => 'handpicked', 'label' => $label, 'link' => '/collections/'.$handle, 'line1' => $l1, 'line2' => $l2,
                'italic_first' => $italic, 'bg' => $bg, 'product_id' => $id($sku), 'sort_order' => $i,
            ]);
        }

        foreach (['VH009', 'VH010', 'VH021', 'VH012', 'VH005', 'VH014', 'VH017', 'VH019'] as $i => $sku) {
            HomeItem::query()->create(['section' => 'look', 'product_id' => $id($sku), 'sort_order' => $i]);
        }
    }

    private function pages(): void
    {
        $pages = [
            'shipping' => ['Shipping Policy', true, [
                'Orders are dispatched within 24–48 hours and delivered in 3–7 working days across India.',
                'Shipping is free on orders above ₹499 (after offers). A flat ₹79 applies below that.',
                'Cash on Delivery is available on orders up to ₹5,000.',
            ]],
            'terms' => ['Terms And Conditions', true, [
                'All products are imitation / fashion jewellery and do not contain precious metals or natural gemstones unless stated (925 Sterling Silver bases are hallmarked).',
                'Colours may vary slightly due to photography and screen settings.',
                'Offers cannot be clubbed unless explicitly mentioned. Buy 1 Get 1 applies to the lower-priced item in each pair.',
            ]],
            'returns' => ['Return & Exchange', true, [
                'Not in love? Request a return or exchange within 7 days of delivery from My Orders or by WhatsApp.',
                'Items must be unused, with tags and the original Vihaaraa pouch. Earrings and nose pins are non-returnable for hygiene reasons unless damaged.',
                'Refunds are processed to the original payment method within 5–7 working days of pickup.',
            ]],
            'privacy' => ['Privacy Policy', true, [
                'We collect only the details needed to process and deliver your order.',
                'We never sell your personal information. Payments are processed by PCI-DSS compliant partners.',
            ]],
            'care' => ['Jewellery Care', true, [
                'Imitation jewellery is plated over brass, copper or silver — keep it away from perfume, deodorant, sanitiser, water and sweat to preserve the plating.',
                'Put your jewellery on last and take it off first. Wipe gently with a soft dry cloth after each wear.',
                'Store each piece separately in its zip pouch or an airtight box to prevent tarnish and scratches on stones.',
                'Avoid wearing while bathing, swimming, or working out.',
            ]],
            'contact' => ['Contact Us', false, [
                'For any queries, reach out between 10:30 AM and 5:30 PM, Monday to Saturday.',
                'WhatsApp: +91-90000 00000 · Email: care@vihaaraa.com',
            ]],
            'store-locator' => ['Store Locator', false, [
                'Our flagship experience store is coming soon. Until then, shop the full collection online with free shipping above ₹499.',
            ]],
        ];
        $i = 0;
        foreach ($pages as $slug => [$title, $footer, $body]) {
            Page::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title, 'body' => implode("\n\n", $body), 'show_in_footer' => $footer, 'sort_order' => ++$i,
            ]);
        }
    }
}
