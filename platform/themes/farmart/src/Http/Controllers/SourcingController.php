<?php

namespace Theme\Farmart\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;

class SourcingController extends BaseController
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:150'], 'category' => ['nullable', 'integer', 'min:1']]);
        $categories = ProductCategory::query()->wherePublished()->with('slugable')->orderBy('name')->get();
        $products = Product::query()->wherePublished()->where('is_variation', false)
            ->with(['slugable', 'store.slugable', 'store.customer', 'defaultVariation.product'])
            ->when($data['q'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%' . $search . '%'))
            ->when($data['category'] ?? null, fn ($query, $id) => $query->whereHas('categories', fn ($category) => $category->where('ec_product_categories.id', $id)))
            ->latest('id')->paginate(12)->withQueryString();
        SeoHelper::setTitle(__('Source products & request quotes'));

        return Theme::scope('marketplace.sourcing', compact('products', 'categories'))->render();
    }

    private function product(string $id): Product
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $product = Product::query()->wherePublished()->where('is_variation', false)
            ->whereHas('store', fn ($store) => $store->wherePublished())
            ->with(['slugable', 'store.slugable', 'store.customer'])->findOrFail($id);
        abort_if((int) $product->store->customer_id === (int) auth('customer')->id(), 403, __('You cannot enquire about your own store.'));

        return $product;
    }

    public function enquiry(string $id, Request $request)
    {
        $product = $this->product($id);
        $mode = $request->query('mode') === 'chat' ? 'chat' : 'quote';
        SeoHelper::setTitle($mode === 'quote' ? __('Request a quote') : __('Chat with supplier'));

        return Theme::scope('marketplace.enquiry', compact('product', 'mode'))->render();
    }

    public function send(string $id, Request $request)
    {
        $product = $this->product($id);
        $data = $request->validate([
            'mode' => ['required', 'in:quote,chat'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'delivery' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:1200', 'regex:/\S/u'],
        ]);
        $customer = auth('customer')->user();
        $content = ($data['mode'] === 'quote' ? 'QUOTE REQUEST' : 'PRODUCT ENQUIRY')
            . "\nProduct: " . $product->name . "\n" . $product->url
            . "\nQuantity: " . $data['quantity'];
        if (isset($data['budget'])) {
            $content .= "\nTarget unit budget: " . $data['budget'] . ' ' . get_application_currency()->title;
        }
        if (! empty($data['delivery'])) {
            $content .= "\nDelivery location: " . $data['delivery'];
        }
        $content .= "\n\n" . trim($data['content']);
        $message = Message::query()->create([
            'store_id' => $product->store_id, 'customer_id' => $customer->getKey(),
            'sender_type' => 'customer', 'name' => $customer->name,
            'email' => $customer->email, 'content' => $content,
        ]);

        return redirect()->route('customer.messages.show', $message);
    }

    public function unread()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $customer = auth('customer')->user();
        $vendor = $customer->is_vendor && $customer->store?->id;
        $count = Message::query()->whereNull('read_at')
            ->when($vendor, fn ($query) => $query->where('store_id', $customer->store->id)->where('sender_type', 'customer'),
                fn ($query) => $query->where('customer_id', $customer->id)->where('sender_type', 'vendor'))->count();

        return response()->json(['unread' => $count])->header('Cache-Control', 'private, no-store');
    }
}
