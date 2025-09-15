<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\About;
use App\Models\AboutImage;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\BookReview;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Favorite;
use App\Models\OrderInvoice;
use App\Models\OrderInvoiceItem;
use App\Models\ReturnAttachment;
use App\Models\ReturnInvoice;
use App\Models\ReturnItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserCoupon;
use App\Models\UserPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::statement("SET FOREIGN_KEY_CHECKS = 0");

        User::truncate();
        Category::truncate();
        Article::truncate();
        Transaction::truncate();
        About::truncate();
        AboutImage::truncate();
        Cart::truncate();
        Event::truncate();
        EventImage::truncate();
        Favorite::truncate();
        OrderInvoice::truncate();
        OrderInvoiceItem::truncate();
        DB::table("article_category")->truncate();
        ReturnInvoice::truncate();
        ReturnItem::truncate();
        ReturnAttachment::truncate();

        $userQuantity = 100;
        $useraddressesQuantity = 100;
        $categoriesQuantity = 30;
        $articlesQuantity = 100;
        $transactionsQuantity = 100;
        $aboutQuantity = 1;
        $aboutImageQuantity = 5;
        $cartQuantity = 70;
        $eventQuantity = 10;
        $eventImageQuantity = 30;
        $articleImageQuantity = 50;
        $favoriteQuantity = 50;
        $orderInvoiceQuantity = 30;
        $orderInvoiceItemQuantity = 70;
        $returnInvoiceQuantity = 30;
        $returnItemQuantity = 70;
        $returnattachmentQuantity = 70;
        
        User::factory($userQuantity)->create(); 
        UserAddress::factory($useraddressesQuantity)->create();
        UserPayment::factory($useraddressesQuantity)->create();
        UserCoupon::factory($useraddressesQuantity)->create();
        Coupon::factory($useraddressesQuantity)->create();
        BookReview::factory($aboutImageQuantity)->create();
        Category::factory($categoriesQuantity)->create();
        Article::factory($articlesQuantity)->create()->each(
            function ($article) {
                $categories = Category::all()->random(mt_rand(1, 5))->pluck('id');

                $article->categories()->attach($categories);
            }
        );
        Transaction::factory($transactionsQuantity)->create();
        About::factory($aboutQuantity)->create();
        AboutImage::factory($aboutImageQuantity)->create();
        Cart::factory($cartQuantity)->create();
        Event::factory($eventQuantity)->create();
        EventImage::factory($eventImageQuantity)->create();
        ArticleImage::factory($articleImageQuantity)->create();
        Favorite::factory($favoriteQuantity)->create();
        OrderInvoice::factory($orderInvoiceQuantity)->create();
        OrderInvoiceItem::factory($orderInvoiceItemQuantity)->create();
        ReturnInvoice::factory($returnInvoiceQuantity)->create();
        ReturnItem::factory($returnItemQuantity)->create();
        ReturnAttachment::factory($returnattachmentQuantity)->create();
    }
}
