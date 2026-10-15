<?php
namespace SparroWave\EnquiryProducts\Tests;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SparroWave\EnquiryProducts\Support\Catalog;
use SparroWave\EnquiryProducts\Providers\EnquiryProductsServiceProvider;

class EnquiryProductsTest
{
    private $app;
    private Catalog $catalog;
    protected function setUp(): void
    {
        $this->app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        \Botble\Setting\Facades\Setting::forceSet('activated_plugins', json_encode(['ecommerce']));
        Product::clearBootedModels();
        $this->app->register(EnquiryProductsServiceProvider::class, true);
        $this->catalog = app(Catalog::class);
        Schema::create('ec_products', function (Blueprint $t): void {
            $t->id(); $t->string('name'); $t->string('status')->default('published');
            $t->integer('is_variation')->default(0); $t->integer('order')->default(0);
        });
        Schema::create('ec_product_categories', function (Blueprint $t): void {
            $t->id(); $t->string('name'); $t->integer('parent_id')->default(0); $t->string('status')->default('published'); $t->integer('order')->default(0);
        });
        Schema::create('ec_product_category_product', function (Blueprint $t): void { $t->integer('product_id'); $t->integer('category_id'); });
        Schema::create('ec_product_variations', function (Blueprint $t): void { $t->id(); $t->integer('product_id'); $t->integer('configurable_product_id'); });
        $this->migration()->up();
        DB::table('ec_product_categories')->insert([
            ['id'=>1,'name'=>'Spares','is_enquiry'=>0], ['id'=>2,'name'=>'Equipment','is_enquiry'=>1], ['id'=>3,'name'=>'Other equipment','is_enquiry'=>1],
        ]);
        DB::table('ec_products')->insert([
            ['id'=>1,'name'=>'Normal pump','is_enquiry'=>0,'status'=>'published','is_variation'=>0],
            ['id'=>2,'name'=>'Tractor pump','is_enquiry'=>1,'status'=>'published','is_variation'=>0],
            ['id'=>3,'name'=>'Combine machine','is_enquiry'=>0,'status'=>'published','is_variation'=>0],
            ['id'=>4,'name'=>'Hidden machine','is_enquiry'=>1,'status'=>'draft','is_variation'=>0],
            ['id'=>5,'name'=>'100% Pump','is_enquiry'=>1,'status'=>'published','is_variation'=>0],
            ['id'=>6,'name'=>'Variant','is_enquiry'=>0,'status'=>'published','is_variation'=>1],
        ]);
        DB::table('ec_product_category_product')->insert([
            ['product_id'=>1,'category_id'=>1], ['product_id'=>2,'category_id'=>1],
            ['product_id'=>3,'category_id'=>2], ['product_id'=>3,'category_id'=>1], ['product_id'=>5,'category_id'=>3],
        ]);
        DB::table('ec_product_variations')->insert(['product_id'=>6,'configurable_product_id'=>2]);
    }
    private function migration()
    {
        return require dirname(__DIR__) . '/database/migrations/2026_10_15_000001_adopt_enquiry_products_schema.php';
    }
    private function route(string $name, string $method='GET'): void
    {
        $request = Request::create('/', $method);
        $route = (new Route([$method], '/', fn () => ''))->name($name);
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);
    }
    public function test_normal_catalog_excludes_flagged_products_and_products_in_enquiry_categories(): void
    {
        $this->route('public.products');
        $this->assertSame([1], Product::where('is_variation',0)->pluck('id')->all());
    }
    public function test_global_search_cannot_reveal_enquiry_products(): void
    {
        $this->route('public.ajax.search-products');
        $this->assertSame([1], Product::where('name','like','%pump%')->pluck('id')->all());
    }
    public function test_normal_categories_exclude_enquiry_categories(): void
    {
        $this->route('public.index');
        $this->assertSame([1], ProductCategory::pluck('id')->all());
    }
    public function test_admin_vendor_and_order_history_queries_are_unfiltered(): void
    {
        foreach (['products.index','marketplace.vendor.products.index','public.customer.orders.show','public.checkout.information'] as $name) {
            $this->route($name);
            $this->assertSame(6, Product::count(), $name);
            $this->assertSame(3, ProductCategory::count(), $name);
        }
    }
    public function test_enquiry_catalog_excludes_normal_draft_and_variation_products(): void
    {
        $this->route('public.enquiry-products.index');
        $this->assertSame([2,3,5], $this->catalog->products()->orderBy('id')->pluck('id')->all());
    }
    public function test_name_search_and_category_filter_combine(): void
    {
        $this->route('public.enquiry-products.index');
        $this->assertSame([3], $this->catalog->listing('machine',2)->pluck('id')->all());
        $this->assertSame([], $this->catalog->listing('pump',2)->pluck('id')->all());
    }
    public function test_percent_in_search_is_literal(): void
    {
        $this->route('public.enquiry-products.index');
        $this->assertSame([5], $this->catalog->listing('%')->pluck('id')->all());
    }
    public function test_category_filter_includes_category_of_individually_flagged_product(): void
    {
        $this->route('public.enquiry-products.index');
        $this->assertSame([1,2,3], $this->catalog->categories()->pluck('id')->sort()->values()->all());
    }
    public function test_variation_inherits_purchase_restriction_from_parent(): void
    {
        $this->route('public.cart.add-to-cart','POST');
        $this->assertTrue($this->catalog->isEnquiry(Product::findOrFail(6)));
        $this->assertFalse($this->catalog->isEnquiry(Product::findOrFail(1)));
    }
    public function test_migration_is_repeat_safe_and_preserves_existing_records(): void
    {
        DB::table('ec_product_enquiry')->insert(['id'=>87,'product_id'=>2,'name'=>'Legacy user','email'=>'test@example.test','phone'=>'1234567890','address'=>'Original','code'=>'OLD-87','status'=>'contacted']);
        $this->migration()->up();
        $this->migration()->down();
        $this->assertSame('contacted', DB::table('ec_product_enquiry')->where('id',87)->value('status'));
        $this->assertSame('OLD-87', DB::table('ec_product_enquiry')->where('id',87)->value('code'));
        $this->assertSame(1, DB::table('ec_products')->where('id',2)->value('is_enquiry'));
    }
    public function test_duplicate_submission_token_cannot_create_two_enquiries(): void
    {
        $row=['product_id'=>2,'name'=>'User','email'=>'test@example.test','phone'=>'1234567890','address'=>'Address','submission_token'=>str_repeat('a',64)];
        DB::table('ec_product_enquiry')->insert($row);
        try { DB::table('ec_product_enquiry')->insert($row); } catch (\Illuminate\Database\UniqueConstraintViolationException) { return; }
        throw new \RuntimeException('Duplicate submission was accepted');
    }
    public function test_submission_uses_server_store_and_is_idempotent(): void
    {
        $notifications = new class extends \SparroWave\EnquiryProducts\Support\Notifications {
            public int $calls = 0;
            public function send(\SparroWave\EnquiryProducts\Models\Enquiry $enquiry): void { $this->calls++; }
        };
        $this->app->instance(\SparroWave\EnquiryProducts\Support\Notifications::class, $notifications);
        $token = str_repeat('b',64);
        $request = Request::create('/equipments-enquiry-products/request/2', 'POST', [
            'token'=>$token,'name'=>'Test User','email'=>'user@example.test','phone'=>'+919876543210',
            'address'=>'Test address','quantity'=>2,'store_id'=>999,'status'=>'contacted',
        ]);
        $request->setLaravelSession(app('session')->driver());
        $request->session()->put('ep_submission.'.$token,2);
        $this->app->instance('request',$request);
        app('router')->getRoutes()->refreshNameLookups();
        $controller = app(\SparroWave\EnquiryProducts\Http\Controllers\CatalogController::class);
        $response = $controller->submit(2,$request,$this->catalog);
        $controller->submit(2,$request,$this->catalog);
        $this->assertSame(1, DB::table('ec_product_enquiry')->count());
        $saved = DB::table('ec_product_enquiry')->first();
        $this->assertSame(null,$saved->store_id);
        $this->assertSame('pending',$saved->status);
        $this->assertSame('#GPEQ-' . $saved->id, $saved->code);
        $this->assertSame(1,$notifications->calls);
        $this->assertTrue(str_contains($response->getTargetUrl(),'/success/'.$token));
    }
    public function test_normal_product_cannot_receive_an_enquiry(): void
    {
        try { app(\SparroWave\EnquiryProducts\Http\Controllers\CatalogController::class)->submit(1,Request::create('/','POST'),$this->catalog); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { $this->assertSame(0,DB::table('ec_product_enquiry')->count()); return; }
        throw new \RuntimeException('Normal product accepted an enquiry');
    }
    public function test_vendor_cannot_read_another_store_enquiry(): void
    {
        DB::table('ec_product_enquiry')->insert(['id'=>9,'product_id'=>2,'store_id'=>20,'name'=>'User','email'=>'test@example.test','phone'=>'1234567890','address'=>'Address']);
        config(['auth.guards.customer'=>['driver'=>'session','provider'=>'users']]);
        auth('customer')->setUser(new \Illuminate\Auth\GenericUser(['id'=>1,'is_vendor'=>true,'store'=>(object)['id'=>10]]));
        try { app(\SparroWave\EnquiryProducts\Http\Controllers\EnquiryController::class)->vendorShow(9); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return; }
        throw new \RuntimeException('Vendor could read another store enquiry');
    }
    public function test_purchase_hook_rejects_enquiry_variation(): void
    {
        $this->route('public.cart.add-to-cart','POST');
        try { do_action('ecommerce_before_add_to_cart',Product::findOrFail(6)); }
        catch (\Illuminate\Validation\ValidationException) { return; }
        throw new \RuntimeException('Enquiry variation could enter cart');
    }
    public function test_lazy_shortcode_post_keeps_normal_products_separate(): void
    {
        $this->route('public.ajax.render-ui-block','POST');
        $this->assertSame([1],Product::where('is_variation',0)->pluck('id')->all());
    }
    public function test_api_catalog_uses_the_same_visibility_rule(): void
    {
        $request = Request::create('/api/v1/ecommerce/products','GET');
        $route = new Route(['GET'],'api/v1/ecommerce/products',['uses'=>'Botble\\Ecommerce\\Http\\Controllers\\API\\ProductController@index']);
        $route->setAction(array_merge($route->getAction(), ['controller' => $route->getAction('uses')]));
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request',$request);
        $this->assertSame([1],Product::where('is_variation',0)->pluck('id')->all());
    }
    public function test_parent_category_filter_includes_children_without_duplicate_products(): void
    {
        DB::table('ec_product_categories')->where('id',3)->update(['parent_id'=>2]);
        $this->route('public.enquiry-products.index');
        $this->assertSame([5,3], $this->catalog->listing(null,2)->pluck('id')->all());
        $this->assertSame('Equipment / Other equipment', $this->catalog->categories()->firstWhere('id',3)->enquiry_label);
    }
    public function test_nested_category_options_include_ancestors_and_unflagged_descendants(): void
    {
        DB::table('ec_product_categories')->insert([
            ['id'=>10,'name'=>'Machines','parent_id'=>0,'is_enquiry'=>0],
            ['id'=>11,'name'=>'Child','parent_id'=>2,'is_enquiry'=>0],
            ['id'=>12,'name'=>'Grandchild','parent_id'=>11,'is_enquiry'=>0],
        ]);
        DB::table('ec_product_categories')->where('id',2)->update(['parent_id'=>10]);
        DB::table('ec_product_category_product')->where('product_id',5)->update(['category_id'=>12]);
        $this->route('public.enquiry-products.index');
        $options = $this->catalog->categories();
        $this->assertTrue($options->contains('id',10));
        $this->assertTrue($options->contains('id',11));
        $this->assertSame('Machines / Equipment / Child / Grandchild', $options->firstWhere('id',12)->enquiry_label);
        $this->assertSame([5,3], $this->catalog->listing(null,10)->pluck('id')->all());
        $this->assertSame([5], $this->catalog->listing(null,11)->pluck('id')->all());
        $this->assertSame([5], $this->catalog->listing(null,12)->pluck('id')->all());
        $this->assertSame([5], $this->catalog->listing('Pump',12)->pluck('id')->all());
    }
    public function test_vendor_table_and_export_query_only_include_own_store(): void
    {
        DB::table('ec_product_enquiry')->insert([
            ['id'=>10,'product_id'=>2,'store_id'=>10,'name'=>'Own','email'=>'own@example.test','phone'=>'1234567890','address'=>'Address'],
            ['id'=>11,'product_id'=>2,'store_id'=>20,'name'=>'Other','email'=>'other@example.test','phone'=>'1234567890','address'=>'Address'],
        ]);
        config(['auth.guards.customer'=>['driver'=>'session','provider'=>'users']]);
        auth('customer')->setUser(new \Illuminate\Auth\GenericUser(['id'=>1,'is_vendor'=>true,'store'=>(object)['id'=>10]]));
        foreach (['bulk-change.save', 'bulk-change.data', 'bulk-action.dispatch', 'filter.input'] as $suffix) {
            app('router')->get('test-table/' . $suffix, fn () => '')->name('marketplace.vendor.table.' . $suffix);
        }
        app('router')->getRoutes()->refreshNameLookups();
        $table = app(\SparroWave\EnquiryProducts\Tables\VendorEnquiryTable::class);
        $this->assertSame([10], $table->query()->pluck('id')->all());
        $this->assertTrue(isset($table->getFilters()['status']));
        $this->assertTrue(in_array('export', $table->getDefaultButtons()));
    }
    public function test_activation_requires_active_ecommerce(): void
    {
        \Botble\Setting\Facades\Setting::forceSet('activated_plugins', '[]');
        try {
            \SparroWave\EnquiryProducts\Plugin::activate();
        } catch (\RuntimeException $exception) {
            $this->assertTrue(str_contains($exception->getMessage(), 'Ecommerce'));
            return;
        }
        throw new \RuntimeException('Activation allowed without Ecommerce');
    }
    public function test_ecommerce_only_install_has_no_marketplace_routes_or_store_links(): void
    {
        $this->assertFalse(is_plugin_active('marketplace'));
        $this->assertSame(null, \SparroWave\EnquiryProducts\Support\StoreUrl::catalogue((object) ['id'=>1]));
        $this->assertFalse(app('router')->has('public.enquiry-products.store'));
        $this->assertFalse(app('router')->has('enquiry-products.vendor.index'));
        \SparroWave\EnquiryProducts\Plugin::activate();
        $this->assertSame('Enquiries', __('plugins/enquiry-products::messages.enquiries'));
    }
    private function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) { throw new \RuntimeException($message . ' Expected ' . json_encode($expected) . ', got ' . json_encode($actual)); }
    }
    private function assertTrue(bool $actual): void { $this->assertSame(true, $actual); }
    private function assertFalse(bool $actual): void { $this->assertSame(false, $actual); }
    public function run(): void
    {
        $failed = 0;
        foreach (get_class_methods($this) as $method) {
            if (! str_starts_with($method, 'test_')) { continue; }
            try { $this->setUp(); $this->$method(); echo "PASS $method\n"; }
            catch (\Throwable $e) { $failed++; echo "FAIL $method: " . $e->getMessage() . "\n"; }
            finally { $this->tearDown(); }
        }
        if ($failed) { exit(1); }
    }
    protected function tearDown(): void
    {
        DB::disconnect();
        $this->app->flush();
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
    }
}
