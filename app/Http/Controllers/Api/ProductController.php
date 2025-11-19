<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Interface\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductRepositoryInterface $products)
    {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string',
            'sort_dir' => 'sometimes|in:asc,desc',
            'price_min' => 'sometimes|numeric|min:0',
            'price_max' => 'sometimes|numeric|min:0',
            'availability' => 'sometimes|in:in_stock,out_of_stock',
            'q' => 'sometimes|string|max:255',
        ]);
        $perPage = (int)($validated['limit'] ?? 10);
        $filters = [
            'price_min' => $validated['price_min'] ?? null,
            'price_max' => $validated['price_max'] ?? null,
            'availability' => $validated['availability'] ?? null,
            'q' => $validated['q'] ?? null,
        ];
        $sort = [
            'by' => $validated['sort_by'] ?? 'created_at',
            'dir' => $validated['sort_dir'] ?? 'desc',
        ];
        $paginator = $this->products->paginate($perPage, array_filter($filters, fn($v) => $v !== null), $sort);
        return response()->json([
            'data' => ProductResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 200);
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;
        $product = $this->products->create($data);
        return response()->json(new ProductResource($product), 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->products->find($id);
        if ($product === null) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(new ProductResource($product), 200);
    }

    public function update(ProductUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $product = $this->products->update($id, $data);
        if ($product === null) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(new ProductResource($product), 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->products->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['message' => 'Product deleted successfully'], 201);
    }

    public function updateImage(Request $request, $id): JsonResponse
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // max 10MB
        ]);
        $file = $request->file('image');
        $newName = time() . '.' . $file->getClientOriginalExtension();
        $data = ["image" => $newName];
        $product = $this->products->update($id, $data);
        if ($product === null) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $file->move(public_path('uploads'), $newName);
        $path = public_path('uploads') . "/" . $newName;
        return response()->json([
            'success' => true,
            'message' => 'Product image updated successfully',
            'data' => [
                'name' => $newName,
                'image_url' => $path
            ]
        ], 200);
    }

}
