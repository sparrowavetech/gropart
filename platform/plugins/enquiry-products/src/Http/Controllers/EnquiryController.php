<?php
namespace SparroWave\EnquiryProducts\Http\Controllers;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SparroWave\EnquiryProducts\Models\Enquiry;

class EnquiryController extends BaseController
{
    public function index(\SparroWave\EnquiryProducts\Tables\EnquiryTable $table)
    {
        $this->pageTitle(__('plugins/enquiry-products::messages.equipment_enquiries'));
        return $table->renderTable();
    }
    public function sellers(\SparroWave\EnquiryProducts\Tables\EnquirySellerTable $table)
    {
        abort_unless(is_plugin_active('marketplace'), 404);
        $title = __('plugins/enquiry-products::messages.enquiry_sellers');
        $this->pageTitle($title);
        $this->breadcrumb()->add(__('plugins/enquiry-products::messages.product_enquiry'), route('enquiry-products.index'))->add($title);
        return $table->renderTable();
    }
    private function listing(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', Rule::in(Enquiry::STATUSES)]]);
        return Enquiry::with('product')->when($data['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($data['q'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', '%' . $s . '%')->orWhere('code', 'like', '%' . $s . '%')))->latest();
    }
    public function show(int $id)
    {
        $enquiry = Enquiry::with('product.slugable')->findOrFail($id);
        $title = __('plugins/enquiry-products::messages.view_enquiry') . ' ' . ($enquiry->code ?: $enquiry->id);
        $this->pageTitle($title);
        $this->breadcrumb()->add(__('plugins/enquiry-products::messages.product_enquiry'), route('enquiry-products.index'))
            ->add(__('plugins/enquiry-products::messages.enquiries'), route('enquiry-products.index'))->add($title);
        $store = is_plugin_active('marketplace') && $enquiry->store_id
            ? \Botble\Marketplace\Models\Store::find($enquiry->store_id) : null;
        $history = DB::table('ep_enquiry_history')->where('enquiry_id', $id)->orderByDesc('id')->get();
        return view('plugins/enquiry-products::admin.show', compact('enquiry', 'store', 'history'));
    }
    public function update(int $id, Request $request)
    {
        $data = $request->validate(['status' => ['required', Rule::in(Enquiry::STATUSES)], 'admin_notes' => ['nullable', 'string', 'max:10000']]);
        DB::transaction(function () use ($id, $data): void {
            $enquiry = Enquiry::lockForUpdate()->findOrFail($id);
            if ($enquiry->status !== $data['status']) {
                DB::table('ep_enquiry_history')->insert(['enquiry_id' => $id, 'status' => $data['status'], 'actor_id' => auth()->id(), 'actor_type' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
            }
            $enquiry->update($data);
        });
        return back()->with('success_msg', __('plugins/enquiry-products::messages.enquiry_updated'));
    }
    public function destroy(int $id)
    {
        return \Botble\Base\Http\Actions\DeleteResourceAction::make(Enquiry::findOrFail($id));
    }

    private function storeId(): int
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_vendor && $customer->store?->id, 403);
        return (int) $customer->store->id;
    }
    public function vendorIndex(\SparroWave\EnquiryProducts\Tables\VendorEnquiryTable $table)
    {
        $this->storeId();
        $this->pageTitle(__('plugins/enquiry-products::messages.equipment_enquiries'));
        return $table->renderTable();
    }
    public function vendorShow(int $id)
    {
        $enquiry = Enquiry::with('product')->where('store_id', $this->storeId())->findOrFail($id);
        return view('plugins/enquiry-products::vendor.show', compact('enquiry'));
    }
    public function attachment(int $id)
    {
        return $this->download(Enquiry::findOrFail($id));
    }
    public function vendorAttachment(int $id)
    {
        return $this->download(Enquiry::where('store_id', $this->storeId())->findOrFail($id));
    }
    private function download(Enquiry $enquiry)
    {
        $path = $enquiry->attachment;
        if ($path && str_starts_with($path, 'enquiry-products/') && ! str_contains($path, '..') && Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }
        // Legacy attachments remain on the configured media disk, behind the same ownership checks.
        if ($path && ! str_contains($path, '..') && ! str_contains($path, '://') && ! str_starts_with($path, '/')) {
            $disk = Storage::disk(\Botble\Media\Facades\RvMedia::getMediaDriver());
            if ($disk->exists($path)) { return $disk->download($path); }
        }
        abort(404, 'Attachment not found.');
    }
}
