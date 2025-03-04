<?php

namespace Database\Seeders;

use App\Models\Debtor;
use Illuminate\Database\Seeder;

class DebtorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $debtors = [
            // First set of debtors (primarily for Business 1)
            [
                'name' => 'Global Suppliers Ltd',
                'kra_pin' => 'P123456789Q',
                'email' => 'accounts@globalsuppliers.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(15),
                'listing_goes_live_at' => now()->addDays(5),
            ],
            [
                'name' => 'Metro Distributors',
                'kra_pin' => 'M987654321N',
                'email' => 'finance@metrodist.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(20),
                'listing_goes_live_at' => now()->addDays(3),
                'listed_at' => now()->subDays(10),
            ],
            [
                'name' => 'Unity Construction',
                'kra_pin' => 'U654321987V',
                'email' => 'payments@unityconstruction.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(5),
                'listing_goes_live_at' => now()->addDays(20),
            ],
            [
                'name' => 'Fast Track Couriers',
                'kra_pin' => 'F555444333G',
                'email' => 'billing@fasttrack.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(2),
                'listing_goes_live_at' => now()->addDays(15),
            ],
            [
                'name' => 'Green Valley Farms',
                'kra_pin' => 'G777888999H',
                'email' => 'accounts@greenvalley.co.ke',
                'status' => 'paid',
                'status_notes' => 'Full payment received',
                'status_updated_at' => now()->subDays(1),
                'listed_at' => now()->subDays(30),
            ],
            [
                'name' => 'Tech Innovations',
                'kra_pin' => 'T111222333J',
                'email' => 'finance@techinnovations.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(10),
                'listing_goes_live_at' => now()->addDays(10),
            ],
            [
                'name' => 'Summit Enterprises',
                'kra_pin' => 'S222333444K',
                'email' => 'ar@summit.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(25),
                'listing_goes_live_at' => now()->subDays(5),
                'listed_at' => now()->subDays(5),
            ],
            [
                'name' => 'Riverside Hospitality',
                'kra_pin' => 'R333444555L',
                'email' => 'accounts@riverside.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(18),
                'listing_goes_live_at' => now()->subDays(3),
                'listed_at' => now()->subDays(3),
            ],
            [
                'name' => 'National Telecoms',
                'kra_pin' => 'N444555666M',
                'email' => 'billing@nationaltele.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Service quality issues',
                'status_updated_at' => now()->subDays(7),
            ],
            [
                'name' => 'City Developers',
                'kra_pin' => 'C555666777N',
                'email' => 'finance@citydevelopers.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(4),
                'listing_goes_live_at' => now()->addDays(25),
            ],

            // Second set of debtors (primarily for Business 2)
            [
                'name' => 'Platinum Logistics',
                'kra_pin' => 'L666777888P',
                'email' => 'ap@platinumlogistics.co.ke',
                'status' => 'paid',
                'status_notes' => 'Partial payment received, balance cleared',
                'status_updated_at' => now()->subDays(1),
                'listed_at' => now()->subDays(22),
            ],
            [
                'name' => 'Eastern Manufacturers',
                'kra_pin' => 'E777888999Q',
                'email' => 'accounts@easternmfg.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(14),
                'listing_goes_live_at' => now()->subDays(2),
                'listed_at' => now()->subDays(2),
            ],
            [
                'name' => 'Western Distributors',
                'kra_pin' => 'W888999000R',
                'email' => 'payables@westerndist.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(3),
                'listing_goes_live_at' => now()->addDays(12),
            ],
            [
                'name' => 'Highland Properties',
                'kra_pin' => 'H999000111S',
                'email' => 'billing@highlandprops.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(30),
                'listing_goes_live_at' => now()->subDays(10),
                'listed_at' => now()->subDays(10),
            ],
            [
                'name' => 'Lakeside Fisheries',
                'kra_pin' => 'L000111222T',
                'email' => 'payments@lakesidefisheries.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Delivery timing issues',
                'status_updated_at' => now()->subDays(5),
            ],
            [
                'name' => 'Valley Fresh Produce',
                'kra_pin' => 'V111222333U',
                'email' => 'accounts@valleyfresh.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(2),
                'listing_goes_live_at' => now()->addDays(18),
            ],
            [
                'name' => 'Royal Hotels Group',
                'kra_pin' => 'R222333444V',
                'email' => 'finance@royalhotels.co.ke',
                'status' => 'paid',
                'status_notes' => 'Account settled in full',
                'status_updated_at' => now()->subDays(1),
                'listed_at' => now()->subDays(45),
            ],
            [
                'name' => 'Central Auto Parts',
                'kra_pin' => 'C333444555W',
                'email' => 'payables@centralauto.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(1),
                'listing_goes_live_at' => now()->addDays(30),
            ],
            [
                'name' => 'Nairobi Electronics',
                'kra_pin' => 'N444555666X',
                'email' => 'accounts@nairobielectronics.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(10),
                'listed_at' => now()->subDays(8),
            ],
            [
                'name' => 'Safari Supplies',
                'kra_pin' => 'S555666777Y',
                'email' => 'finance@safarisupplies.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Product quality dispute',
                'status_updated_at' => now()->subDays(7),
            ],

            // Third set of debtors (primarily for Business 3)
            [
                'name' => 'Mountain View Resorts',
                'kra_pin' => 'M666777888Z',
                'email' => 'billing@mountainviewresorts.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(3),
                'listing_goes_live_at' => now()->addDays(22),
            ],
            [
                'name' => 'Coastal Seafoods',
                'kra_pin' => 'C777888999A',
                'email' => 'accounts@coastalseafoods.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(28),
                'listed_at' => now()->subDays(20),
            ],
            [
                'name' => 'Urban Textiles',
                'kra_pin' => 'U888999000B',
                'email' => 'ap@urbantextiles.co.ke',
                'status' => 'paid',
                'status_notes' => 'Settlement complete',
                'status_updated_at' => now()->subDays(10),
                'listed_at' => now()->subDays(35),
            ],
            [
                'name' => 'Delta Construction',
                'kra_pin' => 'D999000111C',
                'email' => 'finance@deltaconstruction.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Contract terms dispute',
                'status_updated_at' => now()->subDays(12),
            ],
            [
                'name' => 'Sunshine Bakeries',
                'kra_pin' => 'S000111222D',
                'email' => 'accounts@sunshinebakeries.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(15),
                'listed_at' => now()->subDays(10),
            ],
            [
                'name' => 'Premium Motors',
                'kra_pin' => 'P111222333E',
                'email' => 'billing@premiummotors.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(5),
                'listing_goes_live_at' => now()->addDays(15),
            ],
            [
                'name' => 'Golden Harvest Grains',
                'kra_pin' => 'G222333444F',
                'email' => 'finance@goldenharvest.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(20),
                'listed_at' => now()->subDays(15),
            ],
            [
                'name' => 'Star Media Group',
                'kra_pin' => 'S333444555G',
                'email' => 'accounts@starmedia.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Advertising performance dispute',
                'status_updated_at' => now()->subDays(8),
            ],
            [
                'name' => 'Blue Sky Airways',
                'kra_pin' => 'B444555666H',
                'email' => 'payables@blueskyair.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(2),
                'listing_goes_live_at' => now()->addDays(28),
            ],
            [
                'name' => 'Diamond Jewelers',
                'kra_pin' => 'D555666777J',
                'email' => 'finance@diamondjewelers.co.ke',
                'status' => 'paid',
                'status_notes' => 'All invoices cleared',
                'status_updated_at' => now()->subDays(5),
                'listed_at' => now()->subDays(40),
            ],

            // Additional debtors for cross-business relationships
            [
                'name' => 'Acme Corporation', // Same as Business 1
                'kra_pin' => 'A123456789X',
                'email' => 'info@acme.co.ke',
                'status' => 'disputed',
                'status_notes' => 'Customer disputes the amount owed',
                'status_updated_at' => now()->subDays(3),
            ],
            [
                'name' => 'XYZ Enterprises', // Same as Business 2
                'kra_pin' => 'B987654321Y',
                'email' => 'contact@xyz.co.ke',
                'status' => 'pending',
                'status_updated_at' => now()->subDays(1),
                'listing_goes_live_at' => now()->addDays(14),
            ],
            [
                'name' => 'Savanna Solutions', // Same as Business 3
                'kra_pin' => 'C654321987Z',
                'email' => 'hello@savanna.co.ke',
                'status' => 'active',
                'status_updated_at' => now()->subDays(5),
                'listed_at' => now()->subDays(3),
            ],
        ];

        foreach ($debtors as $debtor) {
            Debtor::create($debtor);
        }
    }
}
