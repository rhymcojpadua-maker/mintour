<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Destination;
use App\Models\DestinationPhoto;
use App\Models\EmergencyContact;
use App\Models\Event;
use App\Models\Faq;
use App\Models\KnowledgeArticle;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

/**
 * Demo data for Victoria, Oriental Mindoro.
 *
 * Mapped place names and photo sources are noted in the destination records.
 * Confirm operating status, access, rates, and opening hours locally.
 */
class VictoriaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedDestinations();
        $this->seedAccommodations();
        $this->seedRestaurants();
        $this->seedProducts();
        $this->seedEvents();
        $this->seedAnnouncements();
        $this->seedFaqs();
        $this->seedEmergencyContacts();
        $this->seedKnowledgeBase();
        $this->command->info('✔ Victoria demo data seeded.');
    }

    private function seedCategories(): void
    {
        $data = [
            ['name' => 'Beaches',   'slug' => 'beaches',   'icon' => '🌊', 'color' => '#06b6d4', 'sort_order' => 1],
            ['name' => 'Nature',    'slug' => 'nature',    'icon' => '🌿', 'color' => '#0f766e', 'sort_order' => 2],
            ['name' => 'Culture',   'slug' => 'culture',   'icon' => '⛪', 'color' => '#f59e0b', 'sort_order' => 3],
            ['name' => 'Food',      'slug' => 'food',      'icon' => '🍽️', 'color' => '#fb7185', 'sort_order' => 4],
            ['name' => 'Adventure', 'slug' => 'adventure', 'icon' => '⛰️', 'color' => '#8b5cf6', 'sort_order' => 5],
        ];

        foreach ($data as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c + ['is_active' => true]);
        }

        $this->command->line('  → '.count($data).' categories');
    }

    private function seedDestinations(): void
    {
        $map = Category::pluck('id', 'name');
        $legacySlugs = [
            'silonay-mangrove-eco-park',
            'puting-buhangin-cove',
            'victoria-parish-church',
            'victoria-public-market',
            'victoria-port-pier',
            'malasimbo-viewpoint',
        ];

        Destination::whereIn('slug', $legacySlugs)->delete();
        DestinationPhoto::where('path', 'like', 'destinations/gallery/placeholder-%')->delete();

        $rows = [
            [
                'category_id' => $map['Culture'],
                'name' => 'Victoria Municipal Hall & Plaza',
                'slug' => 'victoria-municipal-hall-plaza',
                'tagline' => 'Town center',
                'short_description' => 'A lively stop in the town proper, especially after dark when the municipal hall is illuminated.',
                'long_description' => "The municipal hall and its public plaza are a convenient first stop in Victoria's town center. The existing local photo shows the hall illuminated at night. Check locally for public access, events, and current opening hours.",
                'latitude' => 13.1733849, 'longitude' => 121.2787746,
                'address' => 'Poblacion, Victoria, Oriental Mindoro',
                'hours' => null,
                'entrance_fee' => null,
                'best_time' => 'Evening for the illuminated facade',
                'distance_km' => 0,
                'is_featured' => true,
                'cover_image' => url('/images/victoria-municipality.jpg'),
            ],
            [
                'category_id' => $map['Nature'],
                'name' => 'Naujan Lake National Park',
                'slug' => 'naujan-lake-national-park',
                'tagline' => 'Nearby day trip',
                'short_description' => 'A scenic protected lake outside Victoria; check the route and access conditions before setting out.',
                'long_description' => 'Naujan Lake National Park is a nearby nature destination in Oriental Mindoro, outside the municipality of Victoria. Plan it as a separate day trip and confirm the best access point and local conditions before travelling. Photo: Orlan Rovira Frane, “Naujan Lake National Park View 3,” Wikimedia Commons, CC BY-SA 4.0 (https://commons.wikimedia.org/wiki/File:Naujan_Lake_National_Park_View_3.jpg).',
                'latitude' => 13.109494, 'longitude' => 121.336533,
                'address' => 'Naujan Lake National Park, Oriental Mindoro (nearby; not in Victoria)',
                'hours' => null,
                'entrance_fee' => null,
                'best_time' => null,
                'distance_km' => 9.5,
                'is_featured' => true,
                'cover_image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/ec/Naujan_Lake_National_Park_View_3.jpg/1280px-Naujan_Lake_National_Park_View_3.jpg',
            ],
            [
                'category_id' => $map['Food'],
                'name' => 'Bread and Butter Bakery',
                'slug' => 'bread-and-butter-bakery',
                'tagline' => 'Local bakery',
                'short_description' => 'A Victoria bakery pictured in a locally tagged Wikimedia Commons photo; check whether it is currently operating.',
                'long_description' => 'Bread and Butter Bakery is mapped on S. Garong Street in Poblacion II, Victoria. Confirm current operating status and hours before visiting. Photo taken in 2011 by BakerBetty, “Bread & Butter - Victoria,Mindoro,” Wikimedia Commons, CC BY-SA 4.0 (https://commons.wikimedia.org/wiki/File:Bread_%26_Butter_-_Victoria,Mindoro.JPG).',
                'latitude' => 13.1746208, 'longitude' => 121.2779454,
                'address' => 'S. Garong Street, Poblacion II, Victoria',
                'hours' => null,
                'entrance_fee' => null,
                'best_time' => null,
                'distance_km' => 0.2,
                'is_featured' => false,
                'cover_image' => 'https://upload.wikimedia.org/wikipedia/commons/1/11/Bread_%26_Butter_-_Victoria%2CMindoro.JPG',
            ],
        ];

        foreach ($rows as $row) {
            Destination::updateOrCreate(['slug' => $row['slug']], $row + ['is_published' => true]);
        }

        foreach ($rows as $row) {
            $destination = Destination::where('slug', $row['slug'])->firstOrFail();
            $destination->photos()->updateOrCreate(
                ['sort_order' => 0],
                ['path' => $row['cover_image'], 'caption' => $row['name'].' — photo source noted in the description'],
            );
        }

        $this->command->line('  → '.count($rows).' destinations with sourced photos');
    }

    private function seedAccommodations(): void
    {
        Accommodation::whereIn('slug', [
            'victoria-bay-resort',
            'silonay-eco-lodge',
            'jj-pensionne-house',
            'sunset-view-homestay',
            'eightyeight-hotel-and-restaurant',
            'riz-diamond-hotel-restaurant',
            'matala-resort-hotel',
        ])->delete();

        $rows = [];

        foreach ($rows as $r) {
            Accommodation::updateOrCreate(['slug' => $r['slug']], $r + ['is_published' => true]);
        }

        $this->command->line('  → '.count($rows).' accommodations');
    }

    private function seedRestaurants(): void
    {
        Restaurant::whereIn('slug', [
            'lola-isings-carinderia',
            'seaside-grill-victoria',
            'bakawan-cafe',
            'wandel-food-park',
        ])->delete();

        $rows = [
            [
                'name' => "Maxine's Restaurant", 'slug' => 'maxines-restaurant',
                'cuisine' => 'Restaurant', 'price_range' => null, 'rating_avg' => 0,
                'address' => 'Calapan South Road, Poblacion II, Victoria',
                'latitude' => 13.1838254, 'longitude' => 121.2758526,
                'hours' => null,
                'description' => 'Restaurant listed in OpenStreetMap. Check current hours, menu, and prices before visiting.',
                'cover_image' => null,
            ],
            [
                'name' => 'Bread and Butter Bakery', 'slug' => 'bread-and-butter-bakery-food',
                'cuisine' => 'Bakery', 'price_range' => null, 'rating_avg' => 0,
                'address' => 'S. Garong Street, Poblacion II, Victoria',
                'latitude' => 13.1746208, 'longitude' => 121.2779454,
                'hours' => null,
                'description' => 'Local bakery with a photo from 2011 on Wikimedia Commons. Confirm it is currently operating and check prices before visiting.',
                'cover_image' => 'https://upload.wikimedia.org/wikipedia/commons/1/11/Bread_%26_Butter_-_Victoria%2CMindoro.JPG',
            ],
        ];

        foreach ($rows as $r) {
            Restaurant::updateOrCreate(['slug' => $r['slug']], $r + ['is_published' => true]);
        }

        $this->command->line('  → '.count($rows).' restaurants');
    }

    private function seedProducts(): void
    {
        $img = fn ($id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=800&q=80";

        $rows = [
            ['name' => 'Mindoro Wild Honey', 'slug' => 'mindoro-wild-honey',
                'maker' => 'Silonay Beekeepers Coop', 'price' => 280,
                'description' => 'Raw, unfiltered honey harvested from mangrove and forest hives.',
                'cover_image' => $img('photo-1587049352846-4a222e784d38')],
            ['name' => 'Hand-woven Banig', 'slug' => 'hand-woven-banig',
                'maker' => 'Victoria Weavers Guild', 'price' => 650,
                'description' => 'Traditional sleeping mat hand-woven from pandan leaves.',
                'cover_image' => $img('photo-1595428774223-ef52624120d2')],
            ['name' => 'Calamansi Marmalade', 'slug' => 'calamansi-marmalade',
                'maker' => "Lola Nena's Kitchen", 'price' => 180,
                'description' => 'Homemade marmalade from locally grown calamansi.',
                'cover_image' => $img('photo-1582284540020-8acbe03f4924')],
            ['name' => 'Bagoong (Fermented Shrimp Paste)', 'slug' => 'bagoong-fermented-shrimp-paste',
                'maker' => 'Port Fishermen Association', 'price' => 150,
                'description' => 'Traditional Mindoro bagoong, perfect with green mango or kare-kare.',
                'cover_image' => $img('photo-1583258292688-d0213dc5a3a8')],
        ];

        foreach ($rows as $r) {
            Product::updateOrCreate(['slug' => $r['slug']], $r + ['is_published' => true]);
        }

        $this->command->line('  → '.count($rows).' products');
    }

    private function seedEvents(): void
    {
        $festivalDate = now()->startOfYear()->addMonths(8)->addDays(17);
        if ($festivalDate->isPast()) {
            $festivalDate->addYear();
        }
        $year = $festivalDate->year;

        $rows = [
            ['title' => 'Kapakyanan Festival', 'slug' => 'kapakyanan-festival',
                'starts_at' => $festivalDate->format('Y-m-d 00:00:00'), 'ends_at' => $festivalDate->format('Y-m-d 23:59:59'),
                'location' => 'Victoria, Oriental Mindoro (venue to be confirmed)', 'category' => 'Cultural',
                'description' => "Kapakyanan Festival is listed for its annual September 18 observance. The {$year} programme, venue, and visitor arrangements have not been independently confirmed; check with the Municipality of Victoria before travelling."],
        ];

        Event::whereIn('slug', [
            'feast-of-st-joseph',
            'mangrove-eco-fest',
            'pahiyas-sa-victoria',
            'bangka-race-festival',
        ])->delete();

        foreach ($rows as $r) {
            Event::updateOrCreate(['slug' => $r['slug']], $r + ['is_published' => true]);
        }

        $this->command->line('  → '.count($rows).' events');
    }

    private function seedAnnouncements(): void
    {
        Announcement::whereIn('title', [
            'Mangrove boardwalk temporarily closed for maintenance',
            'New tricycle routes to Silonay Eco-Park',
        ])->delete();

        $this->command->line('  → removed unverified sample announcements');
    }

    private function seedFaqs(): void
    {
        Faq::where('question', 'Do I need a guide for the mangrove tour?')->delete();

        $rows = [
            ['question' => 'How do I get to Victoria from Manila?',
                'answer' => 'Take a bus from Cubao or Pasay to Batangas Pier (2.5 hours), then a ferry to Calapan (1 hour). From Calapan, a van or jeepney to Victoria takes about 45 minutes. Total travel time is around 5 hours.',
                'category' => 'Transport', 'sort_order' => 1],
            ['question' => 'Is the AI chatbot free to use?',
                'answer' => 'Yes — completely free for all visitors, no sign-up required. Just tap the chat button in the bottom-right corner.',
                'category' => 'General', 'sort_order' => 2],
            ['question' => 'What is the best time to visit?',
                'answer' => 'The annual Kapakyanan Festival is held on October 15. Confirm event details locally before travelling; weather and operating hours can vary.',
                'category' => 'General', 'sort_order' => 3],
            ['question' => 'Are there accommodations in Victoria?',
                'answer' => 'The tourism portal lists Matala Resort And Catering Services in Victoria. Contact the property to confirm availability, rates, and amenities.',
                'category' => 'Accommodation', 'sort_order' => 4],
            ['question' => 'Where can I find places to eat in Victoria?',
                'answer' => 'Map listings include Maxine’s Restaurant and Bread and Butter Bakery. Check current opening hours and prices before visiting.',
                'category' => 'Food', 'sort_order' => 5],
            ['question' => 'Is the area safe for solo travelers?',
                'answer' => 'Yes. Victoria is a small, peaceful town. Standard precautions apply — avoid unlit areas at night and keep valuables secure.',
                'category' => 'Safety', 'sort_order' => 6],
        ];

        foreach ($rows as $r) {
            Faq::updateOrCreate(['question' => $r['question']], $r + ['is_published' => true]);
        }

        $this->command->line('  → '.count($rows).' FAQs');
    }

    private function seedEmergencyContacts(): void
    {
        EmergencyContact::whereIn('name', [
            'Victoria Police Station',
            'Victoria Fire Station',
            'Victoria Rural Health Unit',
            'Oriental Mindoro Provincial Hospital',
            'Coast Guard Victoria',
            'Tourism Office',
        ])->delete();

        $rows = [
            ['name' => 'Philippine Emergency Hotline', 'phone' => '911', 'category' => 'Emergency', 'sort_order' => 1],
        ];

        foreach ($rows as $r) {
            EmergencyContact::updateOrCreate(['name' => $r['name']], $r);
        }

        $this->command->line('  → '.count($rows).' emergency contacts');
    }

    private function seedKnowledgeBase(): void
    {
        KnowledgeArticle::whereIn('title', [
            'Best beaches in Victoria',
            'Best sunset spots in Victoria',
            'Silonay Mangrove Eco-Park visitor guide',
            'Malasimbo Viewpoint hiking guide',
            'Emergency contacts in Victoria',
        ])->delete();

        $rows = [
            ['title' => 'Places to visit in and near Victoria',
                'category' => 'Destinations',
                'content' => 'In Victoria, visit the municipal hall and plaza in the town center. Naujan Lake National Park is a nearby day trip outside the municipality; confirm the access point and travel conditions before going. Bread and Butter Bakery is a locally mapped food stop; check whether it is currently operating.',
                'tags' => ['Victoria', 'places', 'Naujan Lake', 'town center']],

            ['title' => 'How to reach Victoria from Manila',
                'category' => 'Transport',
                'content' => 'Bus to Batangas Pier (2.5 hours) → ferry to Calapan (1 hour) → van to Victoria (45 minutes). Total travel time is about 5 hours. Buses from Cubao and Pasay leave hourly. Ferries from Batangas run every 1–2 hours. Cost roughly ₱600–900 total.',
                'tags' => ['manila', 'ferry', 'bus', 'transport']],

            ['title' => 'Recommended accommodations and price ranges',
                'category' => 'Hotels',
                'content' => 'The Travel Oriental Mindoro tourism portal lists Matala Resort And Catering Services in Victoria. Rates, amenities, and availability are not verified here; contact the property directly before booking.',
                'tags' => ['hotel', 'resort', 'stay', 'booking']],

            ['title' => 'Annual festivals and events in Victoria',
                'category' => 'Events',
                'content' => 'Victoria celebrates the annual Kapakyanan Festival on October 15. Confirm the current programme, schedule, and visitor arrangements with the local government before travelling.',
                'tags' => ['festival', 'event', 'pista', 'fiesta']],

            ['title' => 'Where to eat local delicacies',
                'category' => 'Food',
                'content' => 'OpenStreetMap listings include Maxine’s Restaurant and Bread and Butter Bakery in Victoria. Check current operating status, opening hours, menus, and prices directly before visiting.',
                'tags' => ['food', 'eat', 'restaurant', 'carinderia']],

            ['title' => 'Getting around Victoria and local transport fares',
                'category' => 'Transport',
                'content' => 'Ask local transport operators for the current route and fare before travelling. No fares or schedules are verified in this guide.',
                'tags' => ['tricycle', 'transport', 'fare', 'bangka']],

            ['title' => 'Emergency help in the Philippines',
                'category' => 'Safety',
                'content' => 'Call 911 for emergencies in the Philippines. For other local agency numbers, check an official government source before relying on a listing.',
                'tags' => ['emergency', '911', 'safety', 'help']],

        ];

        foreach ($rows as $r) {
            KnowledgeArticle::updateOrCreate(
                ['title' => $r['title']],
                $r + ['source' => 'MinTour directory; confirm current details locally', 'is_published' => true],
            );
        }

        $this->command->line('  → '.count($rows).' KB articles');
    }
}
