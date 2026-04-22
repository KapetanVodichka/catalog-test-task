<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListProductsRequest $request): ProductCollection
    {
        $validated = $request->validated();

        $products = Product::query()
            ->with('category')
            ->when(
                isset($validated['category_id']),
                fn ($query) => $query->where('category_id', $validated['category_id'])
            )
            ->when(
                ! empty($validated['search'] ?? null),
                function ($query) use ($validated): void {
                    $search = mb_strtolower(trim($validated['search']));

                    $query->where(function ($searchQuery) use ($search): void {
                        $searchQuery
                            ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$search}%"]);
                    });
                }
            )
            ->latest()
            ->paginate($validated['per_page'] ?? 12)
            ->withQueryString();

        return ProductCollection::make($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return ProductResource::make($product->load('category'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return ProductResource::make($product->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
