<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class ProductController extends Controller
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
    }

    // GET /api/products
    public function index()
    {
        return response()->json(Product::orderBy('id', 'desc')->get(), 200);
    }

    // POST /api/products
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            // Handle Cloudinary Upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $upload = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'products'
                ]);

                $validated['image_url'] = $upload['secure_url'];
                $validated['image_public_id'] = $upload['public_id'];
            }

            $product = Product::create($validated);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], 500);
        }
    }

    // GET /api/products/{product}
    public function show(Product $product)
    {
        return response()->json($product, 200);
    }

    // PUT/PATCH /api/products/{product}
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable',
            ]);

            // Handle Image Update
            if ($request->hasFile('image')) {
                // Delete old image from Cloudinary if it exists
                if ($product->image_public_id) {
                    $this->cloudinary->uploadApi()->destroy($product->image_public_id);
                }

                // Upload new image
                $file = $request->file('image');
                $upload = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'products'
                ]);

                $validated['image_url'] = $upload['secure_url'];
                $validated['image_public_id'] = $upload['public_id'];
            }

            $product->update($validated);

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], 500);
        }
    }

    // DELETE /api/products/{product}
    public function destroy(Product $product)
    {
        try {
            // Delete image from Cloudinary before removing database record
            if ($product->image_public_id) {
                $this->cloudinary->uploadApi()->destroy($product->image_public_id);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ], 500);
        }
    }
}