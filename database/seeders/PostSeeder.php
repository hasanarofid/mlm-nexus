<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catBisnis = Category::updateOrCreate(
            ['slug' => 'peluang-bisnis'],
            ['name' => 'Peluang Bisnis']
        );

        $catPanduan = Category::updateOrCreate(
            ['slug' => 'panduan-mitra'],
            ['name' => 'Panduan Mitra']
        );

        $catPromo = Category::updateOrCreate(
            ['slug' => 'promo-reward'],
            ['name' => 'Promo & Reward']
        );

        Post::updateOrCreate(
            ['slug' => 'mengenal-program-kemitraan-dan-komisi-unilevel-talenta52'],
            [
                'category_id' => $catBisnis->id,
                'title' => 'Mengenal Program Kemitraan dan Komisi Unilevel TALENTA52',
                'content' => "Selamat datang di TALENTA52 — Platform kemitraan modern dengan semangat Saling Bantu, Manfaat Bersama.\n\nTALENTA52 dirancang untuk memberikan kesempatan penghasilan yang adil dan transparan bagi setiap mitra. Melalui sistem jaringan Unilevel Multi-Tier Generasi 1 hingga 10, setiap pendaftaran mitra baru memberikan bonus Rp 7.000 untuk setiap tingkatan upline di atasnya.\n\n## Formula Pembagian Bonus:\n- 50% Auto Save (Rp 3.500): Ditabung secara otomatis untuk ketahanan aset masa depan mitra.\n- 50% Saldo WD (Rp 3.500): Saldo siap ditarik (cashout) langsung ke rekening bank mitra.\n\n> Bersama TALENTA52, kita wujudkan kemandirian finansial melalui ekosistem yang saling menguatkan dan berkelanjutan.",
                'image' => null,
                'status' => 'published',
                'is_featured' => true
            ]
        );

        Post::updateOrCreate(
            ['slug' => 'panduan-lengkap-program-repeat-order-dan-purchase-order'],
            [
                'category_id' => $catPanduan->id,
                'title' => 'Panduan Lengkap Program Repeat Order (RO) dan Purchase Order (PO)',
                'content' => "Dapatkan keuntungan berlipat dengan mengikuti program Repeat Order (RO) dan Purchase Order (PO) di TALENTA52.\n\n### 1. Program Repeat Order (RO)\nDengan belanja paket RO senilai Rp 125.000, Anda akan mendapatkan:\n- +1 Poin RO untuk akumulasi hadiah reward tunai.\n- Bonus Sponsor Rp 20.000 ditransfer langsung ke sponsor Anda.\n- Tambahan Matching Bonus Rp 100.000 saat direct sponsor mencapai 35 Poin RO.\n\n### 2. Program Purchase Order (PO)\nPaket khusus Star Seller (Rp 550.000) dan Affiliate (Rp 2.100.000) memberikan:\n- +2 hingga +8 Personal Poin PO.\n- Alokasi 15 Generasi Tier untuk jaringan Anda.\n- Kesempatan meraih Cash Reward hingga Rp 150.000.000!",
                'image' => null,
                'status' => 'published',
                'is_featured' => false
            ]
        );
    }
}
